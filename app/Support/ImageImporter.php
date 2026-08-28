<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Đưa ảnh từ bên ngoài vào thư viện media của website.
 *
 * Dùng cho MCP client (ChatGPT) — nó chỉ gửi được JSON nên không upload file
 * theo kiểu form được: hoặc đưa link để server tự tải (fromUrl), hoặc nhét
 * thẳng dữ liệu ảnh dạng base64 vào tool (fromBase64).
 */
class ImageImporter
{
    public const MAX_BYTES = 8388608; // 8 MB

    /** Base64 phình ~33%, mà post_max_size của PHP là 8M nên chặn sớm hơn. */
    public const MAX_BASE64_BYTES = 4194304; // 4 MB

    private const EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    /**
     * @return array{url: string, path: string, mime: string, bytes: int, width: int, height: int}
     */
    public static function fromUrl(string $url, string $folder = 'news', ?string $filename = null): array
    {
        static::assertPublicUrl($url);

        $response = Http::timeout(20)
            ->withHeaders(['User-Agent' => 'LuxNestBot/1.0'])
            ->withOptions([
                'allow_redirects' => [
                    'max'         => 3,
                    'strict'      => true,
                    'on_redirect' => fn ($request, $response, $uri) => static::assertPublicUrl((string) $uri),
                ],
            ])
            ->get($url);

        if (!$response->successful()) {
            throw new RuntimeException("Không tải được ảnh (HTTP {$response->status()}).");
        }

        return static::store(
            $response->body(),
            $folder,
            $filename ?: basename((string) parse_url($url, PHP_URL_PATH)),
            self::MAX_BYTES
        );
    }

    /**
     * Ảnh gửi thẳng dưới dạng base64 (chấp nhận cả data URI).
     *
     * @return array{url: string, path: string, mime: string, bytes: int, width: int, height: int}
     */
    public static function fromBase64(string $data, string $folder = 'news', ?string $filename = null): array
    {
        $data = preg_replace('/^data:image\/[a-z.+-]+;base64,/i', '', trim($data)) ?? '';
        $data = preg_replace('/\s+/', '', $data) ?? '';

        if ($data === '') {
            throw new RuntimeException('Thiếu dữ liệu ảnh base64.');
        }

        $binary = base64_decode($data, true);

        if ($binary === false) {
            throw new RuntimeException('Chuỗi base64 không hợp lệ (có thể bị cắt giữa chừng — thử ảnh nhỏ hơn).');
        }

        return static::store($binary, $folder, $filename ?: 'anh', self::MAX_BASE64_BYTES);
    }

    /** @return array{url: string, path: string, mime: string, bytes: int, width: int, height: int} */
    private static function store(string $body, string $folder, string $name, int $maxBytes): array
    {
        $bytes = strlen($body);

        if ($bytes === 0) {
            throw new RuntimeException('Dữ liệu ảnh rỗng.');
        }

        if ($bytes > $maxBytes) {
            throw new RuntimeException('Ảnh nặng hơn ' . round($maxBytes / 1048576) . ' MB, hãy dùng ảnh nhỏ hơn.');
        }

        $info = @getimagesizefromstring($body);
        $mime = $info['mime'] ?? '';

        if (!$info || !isset(self::EXTENSIONS[$mime])) {
            throw new RuntimeException('File không phải ảnh hợp lệ (chỉ nhận JPG, PNG, WEBP, GIF).');
        }

        $path = static::buildPath($folder, $name, self::EXTENSIONS[$mime]);

        Storage::disk('public')->put($path, $body);

        return [
            'url'    => asset('storage/' . $path),
            'path'   => $path,
            'mime'   => $mime,
            'bytes'  => $bytes,
            'width'  => (int) $info[0],
            'height' => (int) $info[1],
        ];
    }

    private static function buildPath(string $folder, string $name, string $ext): string
    {
        $folder = in_array($folder, ['news', 'rooms', 'villas', 'gallery'], true) ? $folder : 'news';
        $base   = Str::slug(pathinfo($name, PATHINFO_FILENAME)) ?: 'anh';
        $base   = Str::limit($base, 60, '');

        do {
            $path = $folder . '/' . $base . '-' . Str::lower(Str::random(8)) . '.' . $ext;
        } while (Storage::disk('public')->exists($path));

        return $path;
    }

    /** Chặn SSRF: chỉ cho http(s) tới địa chỉ public. */
    private static function assertPublicUrl(string $url): void
    {
        $parts  = parse_url($url);
        $scheme = strtolower($parts['scheme'] ?? '');
        $host   = $parts['host'] ?? '';

        if (!in_array($scheme, ['http', 'https'], true) || $host === '') {
            throw new RuntimeException('Link ảnh phải là http hoặc https.');
        }

        $ips = filter_var($host, FILTER_VALIDATE_IP) ? [$host] : (gethostbynamel($host) ?: []);

        if ($ips === []) {
            throw new RuntimeException("Không phân giải được tên miền: {$host}");
        }

        foreach ($ips as $ip) {
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                throw new RuntimeException('Link ảnh trỏ vào địa chỉ nội bộ, không được phép.');
            }
        }
    }
}
