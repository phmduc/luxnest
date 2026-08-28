<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Support\HtmlSanitizer;
use App\Support\ImageImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * MCP (Model Context Protocol) server over Streamable HTTP.
 *
 * Lets an MCP client (ChatGPT custom connector, Claude, ...) read and edit
 * the blog (bảng `news`). Authenticated with a shared secret: either
 * `Authorization: Bearer <MCP_TOKEN>` or the token in the URL path
 * (/mcp/<token>) for clients that only support "no authentication".
 */
class McpController extends Controller
{
    private const PROTOCOL_VERSION = '2025-06-18';

    public function handle(Request $request, ?string $token = null)
    {
        $secret = (string) config('mcp.token');

        if ($secret === '' || !hash_equals($secret, (string) $this->presentedToken($request, $token))) {
            return response()->json([
                'jsonrpc' => '2.0',
                'id'      => null,
                'error'   => ['code' => -32001, 'message' => 'Unauthorized'],
            ], 401)->header('WWW-Authenticate', 'Bearer');
        }

        $payload = $request->json()->all();

        if (!is_array($payload) || $payload === []) {
            return $this->respond($request, $this->error(null, -32700, 'Parse error'));
        }

        // Batch request.
        if (array_is_list($payload)) {
            $results = array_values(array_filter(array_map(fn ($m) => $this->dispatch($m), $payload)));

            return $results === []
                ? response()->noContent(202)
                : $this->respond($request, $results);
        }

        $this->logCall($request, $payload);

        $result = $this->dispatch($payload);

        // Notifications get no body.
        return $result === null
            ? response()->noContent(202)
            : $this->respond($request, $result);
    }

    /** Ghi lại client nào gọi method nào — để soi khi client báo thiếu tool. */
    private function logCall(Request $request, array $payload): void
    {
        $method = $payload['method'] ?? '?';

        Log::channel('mcp')->info('mcp', [
            'method'   => $method,
            'tool'     => $payload['params']['name'] ?? null,
            'protocol' => $payload['params']['protocolVersion'] ?? null,
            'client'   => $payload['params']['clientInfo']['name'] ?? $request->userAgent(),
            'tools'    => $method === 'tools/list' ? count($this->tools()) : null,
        ]);
    }

    private function presentedToken(Request $request, ?string $fromPath): ?string
    {
        if ($fromPath) {
            return $fromPath;
        }

        $header = (string) $request->header('Authorization', '');
        if (str_starts_with(strtolower($header), 'bearer ')) {
            return trim(substr($header, 7));
        }

        return $request->header('X-MCP-Token') ?: $request->query('token');
    }

    // ---------------------------------------------------------------
    // JSON-RPC plumbing
    // ---------------------------------------------------------------

    private function dispatch(array $message): ?array
    {
        $method = $message['method'] ?? null;
        $id     = $message['id'] ?? null;
        $params = $message['params'] ?? [];

        // Notifications (no id) never get a response.
        if (!array_key_exists('id', $message)) {
            return null;
        }

        return match ($method) {
            'initialize' => $this->result($id, [
                'protocolVersion' => $params['protocolVersion'] ?? self::PROTOCOL_VERSION,
                'capabilities'    => ['tools' => ['listChanged' => false]],
                'serverInfo'      => [
                    'name'    => config('mcp.name'),
                    'version' => config('mcp.version'),
                ],
                'instructions' => 'Quản lý bài viết blog (tin tức) của website. Nội dung bài viết viết bằng HTML đơn giản; dùng list_images để lấy URL ảnh có sẵn, upload_image_from_url để tải ảnh mới từ link về server, hoặc upload_image_base64 để gửi thẳng ảnh tự tạo, rồi chèn <img> vào bài.',
            ]),
            'ping'       => $this->result($id, (object) []),
            'tools/list' => $this->result($id, ['tools' => $this->tools()]),
            'tools/call' => $this->callTool($id, $params),
            default      => $this->error($id, -32601, "Method not found: {$method}"),
        };
    }

    private function result(mixed $id, mixed $result): array
    {
        return ['jsonrpc' => '2.0', 'id' => $id, 'result' => $result];
    }

    private function error(mixed $id, int $code, string $message): array
    {
        return ['jsonrpc' => '2.0', 'id' => $id, 'error' => ['code' => $code, 'message' => $message]];
    }

    /** Streamable HTTP: SSE frame when the client asks for it, plain JSON otherwise. */
    private function respond(Request $request, array $body)
    {
        if (str_contains((string) $request->header('Accept', ''), 'text/event-stream')) {
            return response(
                "event: message\ndata: " . json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\n",
                200,
                [
                    'Content-Type'  => 'text/event-stream; charset=utf-8',
                    'Cache-Control' => 'no-cache',
                    'X-Accel-Buffering' => 'no',
                ]
            );
        }

        return response()->json($body, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    // ---------------------------------------------------------------
    // Tools
    // ---------------------------------------------------------------

    private function tools(): array
    {
        $postFields = [
            'title'        => ['type' => 'string', 'description' => 'Tiêu đề bài viết'],
            'content'      => ['type' => 'string', 'description' => 'Nội dung bài viết dạng HTML đơn giản. Thẻ được phép: <p> <h2> <h3> <h4> <ul>/<ol>/<li> <strong> <em> <blockquote> <hr> <br> <figure>/<figcaption>, link <a href="..."> , ảnh <img src="URL ảnh lấy từ list_images"> và video nhúng <div class="news-embed"><iframe src="https://www.youtube.com/embed/VIDEO_ID"></iframe></div>. Thẻ ngoài danh sách này sẽ bị loại bỏ khi lưu.'],
            'excerpt'      => ['type' => 'string', 'description' => 'Tóm tắt ngắn, tối đa 1000 ký tự'],
            'tag'          => ['type' => 'string', 'description' => 'Nhãn/chuyên mục, ví dụ: Khuyến mãi'],
            'image'        => ['type' => 'string', 'description' => 'URL ảnh đại diện (ảnh phải upload sẵn trong admin)'],
            'slug'         => ['type' => 'string', 'description' => 'Đường dẫn; bỏ trống sẽ tự sinh từ tiêu đề'],
            'published_at' => ['type' => 'string', 'description' => 'Ngày đăng, định dạng YYYY-MM-DD'],
            'status'       => ['type' => 'string', 'enum' => ['active', 'draft'], 'description' => 'active = hiển thị trên web, draft = bản nháp'],
        ];

        return [
            [
                'name'        => 'search',
                'description' => 'Tìm bài viết blog theo từ khóa. Trả về danh sách id/tiêu đề/URL.',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => ['query' => ['type' => 'string', 'description' => 'Từ khóa tìm trong tiêu đề và nội dung']],
                    'required'   => ['query'],
                ],
            ],
            [
                'name'        => 'fetch',
                'description' => 'Lấy toàn bộ nội dung của một bài viết theo id.',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => ['id' => ['type' => 'string', 'description' => 'ID bài viết']],
                    'required'   => ['id'],
                ],
            ],
            [
                'name'        => 'list_posts',
                'description' => 'Liệt kê bài viết blog mới nhất, có phân trang và lọc trạng thái.',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => [
                        'query'  => ['type' => 'string', 'description' => 'Lọc theo tiêu đề (tùy chọn)'],
                        'status' => ['type' => 'string', 'enum' => ['active', 'draft'], 'description' => 'Lọc theo trạng thái (tùy chọn)'],
                        'limit'  => ['type' => 'integer', 'description' => 'Số bài mỗi trang, mặc định 20, tối đa 50'],
                        'page'   => ['type' => 'integer', 'description' => 'Trang, mặc định 1'],
                    ],
                ],
            ],
            [
                'name'        => 'get_post',
                'description' => 'Xem chi tiết một bài viết theo id hoặc slug.',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => [
                        'id'   => ['type' => 'integer', 'description' => 'ID bài viết'],
                        'slug' => ['type' => 'string', 'description' => 'Slug bài viết'],
                    ],
                ],
            ],
            [
                'name'        => 'list_images',
                'description' => 'Liệt kê ảnh có sẵn trong thư viện media của website để chèn vào bài viết (trả về URL đầy đủ).',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => [
                        'query'  => ['type' => 'string', 'description' => 'Lọc theo tên file (tùy chọn)'],
                        'folder' => ['type' => 'string', 'enum' => ['all', 'news', 'rooms', 'villas', 'gallery', 'settings'], 'description' => 'Thư mục, mặc định all'],
                        'limit'  => ['type' => 'integer', 'description' => 'Số ảnh trả về, mặc định 24, tối đa 60'],
                    ],
                ],
            ],
            [
                'name'        => 'upload_image_from_url',
                'description' => 'Tải một ảnh từ link bên ngoài về thư viện media của website và trả về URL trên luxnest.vn để chèn vào bài viết. Dùng khi cần ảnh mới chưa có trong list_images. Chỉ nhận JPG/PNG/WEBP/GIF, tối đa 8 MB.',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => [
                        'url'      => ['type' => 'string', 'description' => 'Link ảnh công khai (http/https)'],
                        'filename' => ['type' => 'string', 'description' => 'Tên file gợi ý, không bắt buộc (vd: da-lat-mua-mua)'],
                        'folder'   => ['type' => 'string', 'enum' => ['news', 'rooms', 'villas', 'gallery'], 'description' => 'Thư mục lưu, mặc định news'],
                    ],
                    'required'   => ['url'],
                ],
            ],
            [
                'name'        => 'upload_image_base64',
                'description' => 'Đưa một ảnh vào thư viện media bằng dữ liệu base64 và trả về URL trên luxnest.vn để chèn vào bài viết. Dùng cho ảnh tự tạo hoặc ảnh không có link công khai. Chỉ nhận JPG/PNG/WEBP/GIF, tối đa 4 MB sau khi giải mã — ảnh lớn hơn hãy nén hoặc thu nhỏ trước khi gửi.',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => [
                        'data'     => ['type' => 'string', 'description' => 'Dữ liệu ảnh mã hóa base64 (chấp nhận cả dạng data:image/png;base64,...)'],
                        'filename' => ['type' => 'string', 'description' => 'Tên file gợi ý, không bắt buộc (vd: da-lat-mua-mua)'],
                        'folder'   => ['type' => 'string', 'enum' => ['news', 'rooms', 'villas', 'gallery'], 'description' => 'Thư mục lưu, mặc định news'],
                    ],
                    'required'   => ['data'],
                ],
            ],
            [
                'name'        => 'create_post',
                'description' => 'Tạo bài viết blog mới. Mặc định lưu ở trạng thái draft (bản nháp) nếu không truyền status.',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => $postFields,
                    'required'   => ['title'],
                ],
            ],
            [
                'name'        => 'update_post',
                'description' => 'Cập nhật bài viết đã có. Chỉ những trường được truyền vào mới bị thay đổi.',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => array_merge([
                        'id'   => ['type' => 'integer', 'description' => 'ID bài viết cần sửa'],
                        'slug' => ['type' => 'string', 'description' => 'Hoặc slug bài viết cần sửa'],
                    ], $postFields),
                ],
            ],
            [
                'name'        => 'delete_post',
                'description' => 'Xóa vĩnh viễn một bài viết. Chỉ dùng khi người dùng yêu cầu rõ ràng.',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => ['id' => ['type' => 'integer', 'description' => 'ID bài viết cần xóa']],
                    'required'   => ['id'],
                ],
            ],
        ];
    }

    private function callTool(mixed $id, array $params): array
    {
        $name = $params['name'] ?? '';
        $args = $params['arguments'] ?? [];

        try {
            $payload = match ($name) {
                'search'      => $this->toolSearch($args),
                'fetch'       => $this->toolFetch($args),
                'list_posts'  => $this->toolListPosts($args),
                'list_images' => $this->toolListImages($args),
                'upload_image_from_url' => $this->toolUploadImageFromUrl($args),
                'upload_image_base64'   => $this->toolUploadImageBase64($args),
                'get_post'    => $this->toolGetPost($args),
                'create_post' => $this->toolCreatePost($args),
                'update_post' => $this->toolUpdatePost($args),
                'delete_post' => $this->toolDeletePost($args),
                default       => throw new \InvalidArgumentException("Unknown tool: {$name}"),
            };
        } catch (ValidationException $e) {
            return $this->result($id, $this->toolError('Dữ liệu không hợp lệ: ' . implode(' ', $e->validator->errors()->all())));
        } catch (\Throwable $e) {
            report($e);

            return $this->result($id, $this->toolError($e->getMessage()));
        }

        return $this->result($id, [
            'content'           => [['type' => 'text', 'text' => $this->json($payload)]],
            'structuredContent' => $payload,
            'isError'           => false,
        ]);
    }

    private function toolError(string $message): array
    {
        return [
            'content' => [['type' => 'text', 'text' => $message]],
            'isError' => true,
        ];
    }

    private function json(mixed $value): string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    // ---------------------------------------------------------------
    // Tool implementations
    // ---------------------------------------------------------------

    private function toolSearch(array $args): array
    {
        $query = trim((string) ($args['query'] ?? ''));

        $posts = News::query()
            ->when($query !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('title', 'like', "%{$query}%")
                ->orWhere('content', 'like', "%{$query}%")))
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        return [
            'results' => $posts->map(fn (News $p) => [
                'id'    => (string) $p->id,
                'title' => $p->title,
                'url'   => route('news.show', $p->slug),
            ])->all(),
        ];
    }

    private function toolFetch(array $args): array
    {
        return $this->toolGetPost(['id' => (int) ($args['id'] ?? 0)]);
    }

    private function toolListPosts(array $args): array
    {
        $limit = min(50, max(1, (int) ($args['limit'] ?? 20)));
        $page  = max(1, (int) ($args['page'] ?? 1));
        $query = trim((string) ($args['query'] ?? ''));

        $builder = News::query()
            ->when($query !== '', fn ($q) => $q->where('title', 'like', "%{$query}%"))
            ->when(!empty($args['status']), fn ($q) => $q->where('status', $args['status']))
            ->orderByDesc('published_at')
            ->orderByDesc('id');

        $total = (clone $builder)->count();
        $posts = $builder->forPage($page, $limit)->get();

        return [
            'total' => $total,
            'page'  => $page,
            'posts' => $posts->map(fn (News $p) => $this->summary($p))->all(),
        ];
    }

    private function toolGetPost(array $args): array
    {
        return $this->present($this->findPost($args));
    }

    private function toolListImages(array $args): array
    {
        $folders = ['news', 'rooms', 'villas', 'gallery', 'settings'];
        $folder  = (string) ($args['folder'] ?? 'all');
        $query   = strtolower(trim((string) ($args['query'] ?? '')));
        $limit   = min(60, max(1, (int) ($args['limit'] ?? 24)));

        $images = collect(in_array($folder, $folders, true) ? [$folder] : $folders)
            ->flatMap(fn ($f) => Storage::disk('public')->allFiles($f))
            ->filter(fn ($path) => in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'gif'], true))
            ->filter(fn ($path) => $query === '' || str_contains(strtolower(basename($path)), $query))
            ->reverse()
            ->values();

        return [
            'total'  => $images->count(),
            'images' => $images->take($limit)
                ->map(fn ($path) => ['url' => asset('storage/' . $path), 'folder' => explode('/', $path)[0]])
                ->values()
                ->all(),
        ];
    }

    private function toolUploadImageBase64(array $args): array
    {
        return ImageImporter::fromBase64(
            (string) ($args['data'] ?? ''),
            (string) ($args['folder'] ?? 'news'),
            $args['filename'] ?? null
        );
    }

    private function toolUploadImageFromUrl(array $args): array
    {
        $url = trim((string) ($args['url'] ?? ''));

        if ($url === '') {
            throw new \InvalidArgumentException('Thiếu url của ảnh cần tải.');
        }

        return ImageImporter::fromUrl($url, (string) ($args['folder'] ?? 'news'), $args['filename'] ?? null);
    }

    private function toolCreatePost(array $args): array
    {
        $data = $this->validatePost($args, null);

        $data['status']       = $data['status'] ?? 'draft';
        $data['published_at'] = $data['published_at'] ?? now()->toDateString();
        $data['slug']         = News::uniqueSlug($data['slug'] ?? null, $data['title']);
        $data['content']      = HtmlSanitizer::clean($data['content'] ?? '');

        return $this->present(News::create($data));
    }

    private function toolUpdatePost(array $args): array
    {
        $post = $this->findPost($args);
        $data = $this->validatePost($args, $post->id);

        if (array_key_exists('content', $data)) {
            $data['content'] = HtmlSanitizer::clean($data['content']);
        }

        if (array_key_exists('slug', $data) || array_key_exists('title', $data)) {
            $data['slug'] = News::uniqueSlug($data['slug'] ?? $post->slug, $data['title'] ?? $post->title, $post->id);
        }

        $post->update($data);

        return $this->present($post->fresh());
    }

    private function toolDeletePost(array $args): array
    {
        $post = $this->findPost($args);
        $id   = $post->id;
        $post->delete();

        return ['deleted' => true, 'id' => $id];
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function findPost(array $args): News
    {
        $post = null;

        if (!empty($args['id'])) {
            $post = News::find((int) $args['id']);
        } elseif (!empty($args['slug'])) {
            $post = News::where('slug', $args['slug'])->first();
        }

        if (!$post) {
            throw new \RuntimeException('Không tìm thấy bài viết (cần truyền id hoặc slug hợp lệ).');
        }

        return $post;
    }

    /** @return array<string, mixed> only the keys actually supplied by the client */
    private function validatePost(array $args, ?int $excludeId): array
    {
        $required = $excludeId === null ? 'required' : 'sometimes|required';

        $rules = [
            'title'        => "{$required}|string|max:255",
            'slug'         => 'nullable|string|max:255',
            'excerpt'      => 'nullable|string|max:1000',
            'content'      => 'nullable|string',
            'tag'          => 'nullable|string|max:100',
            'image'        => 'nullable|string|max:1000',
            'published_at' => 'nullable|date',
            'status'       => 'nullable|in:active,draft',
        ];

        $input = array_intersect_key($args, $rules);

        return Validator::make($input, $rules)->validate();
    }

    private function summary(News $post): array
    {
        return [
            'id'           => $post->id,
            'title'        => $post->title,
            'slug'         => $post->slug,
            'tag'          => $post->tag,
            'status'       => $post->status,
            'published_at' => optional($post->published_at)->toDateString(),
            'url'          => route('news.show', $post->slug),
        ];
    }

    private function present(News $post): array
    {
        return $this->summary($post) + [
            'excerpt' => $post->excerpt,
            'image'   => $post->image,
            'text'    => $post->content,
            'content' => $post->content,
        ];
    }
}
