<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Tải ảnh từ URL bên ngoài về thư viện media của website.
 *
 * Dùng cho MCP client (ChatGPT) — nó chỉ gửi được JSON nên không tự upload
 * file được; đưa link ảnh vào đây thì ảnh nằm hẳn trên server mình, không
 * phụ thuộc host bên ngoài.
 */
class RemoteImage
{
    public const MAX_BYTES = 8388608; // 8 MB

    private const EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    /**
     * @return array{url: string, path: string, mime: string, bytes: int, width: int, height: int}
     */
    public static function download(string $url, string $folder = 'news', ?string $filename = null): array
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

        $body  = $response->body();
        $bytes = strlen($body);

        if ($bytes === 0) {
            throw new RuntimeException('File tải về rỗng.');
        }

        if ($bytes > self::MAX_BYTES) {
            throw new RuntimeException('Ảnh nặng hơn 8 MB, hãy dùng ảnh nhỏ hơn.');
        }

        $info = @getimagesizefromstring($body);
        $mime = $info['mime'] ?? '';

        if (!$info || !isset(self::EXTENSIONS[$mime])) {
            throw new RuntimeException('File không phải ảnh hợp lệ (chỉ nhận JPG, PNG, WEBP, GIF).');
        }

        $path = static::buildPath($folder, $filename ?: basename((string) parse_url($url, PHP_URL_PATH)), self::EXTENSIONS[$mime]);

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
