<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Allow-list HTML sanitizer for article content.
 *
 * Bài viết được soạn bằng trình soạn thảo trong admin hoặc do MCP client
 * (ChatGPT) gửi lên, nên nội dung luôn phải đi qua đây trước khi lưu/hiển thị:
 * chỉ giữ lại thẻ định dạng cơ bản, ảnh, link và iframe video từ nhà cung cấp
 * được duyệt.
 */
class HtmlSanitizer
{
    /** tag => attributes kept */
    private const ALLOWED = [
        'p'          => [],
        'br'         => [],
        'strong'     => [],
        'b'          => [],
        'em'         => [],
        'i'          => [],
        'u'          => [],
        'h2'         => [],
        'h3'         => [],
        'h4'         => [],
        'ul'         => [],
        'ol'         => [],
        'li'         => [],
        'blockquote' => [],
        'hr'         => [],
        'figure'     => [],
        'figcaption' => [],
        'table'      => [],
        'thead'      => [],
        'tbody'      => [],
        'tfoot'      => [],
        'tr'         => [],
        'th'         => ['colspan', 'rowspan'],
        'td'         => ['colspan', 'rowspan'],
        'caption'    => [],
        'a'          => ['href', 'title', 'target', 'rel'],
        'img'        => ['src', 'alt'],
        'div'        => ['class'],
        'iframe'     => ['src', 'title', 'allow', 'allowfullscreen', 'frameborder', 'loading'],
    ];

    /** Tags whose content is dropped entirely, not unwrapped. */
    private const DROP = ['script', 'style', 'head', 'meta', 'link', 'object', 'embed', 'form', 'input', 'svg'];

    private const SPAN_ATTRIBUTES = ['colspan', 'rowspan'];

    /** Only these hosts may appear in an <iframe src>. */
    private const EMBED_HOSTS = [
        'www.youtube.com', 'youtube.com', 'www.youtube-nocookie.com', 'youtube-nocookie.com',
        'player.vimeo.com', 'www.tiktok.com', 'tiktok.com', 'www.facebook.com', 'facebook.com',
    ];

    private const ALLOWED_DIV_CLASSES = ['news-embed', 'table-scroll'];

    /** Nội dung đã lưu -> HTML an toàn để in ra trang. Bài cũ dạng text thuần vẫn giữ xuống dòng. */
    public static function render(?string $content): string
    {
        $content = (string) $content;

        if (trim($content) === '') {
            return '';
        }

        if (!static::looksLikeHtml($content)) {
            return nl2br(e($content));
        }

        $html = static::clean($content);

        // Nội dung chỉ có thẻ inline (không có <p>, <br>...) thì vẫn phải giữ xuống dòng.
        return preg_match('/<(p|div|h2|h3|h4|ul|ol|li|br|figure|blockquote|iframe|img|hr|table)\b/i', $html)
            ? $html
            : nl2br($html);
    }

    public static function looksLikeHtml(string $content): bool
    {
        return (bool) preg_match('/<(p|br|div|h2|h3|h4|ul|ol|li|img|a|iframe|figure|blockquote|strong|em|span|b|i|u|table)\b[^>]*>/i', $content);
    }

    /** Lọc HTML theo allow-list. Text thuần đi qua đây vẫn là text thuần. */
    public static function clean(?string $html): string
    {
        $html = (string) $html;

        if (trim($html) === '') {
            return '';
        }

        $dom = new DOMDocument('1.0', 'UTF-8');

        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="UTF-8"?><div id="lx-root">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $dom->getElementById('lx-root') ?: $dom->documentElement;

        if (!$root) {
            return '';
        }

        static::cleanChildren($root);
        static::wrapTables($dom, $root);

        $out = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $out .= $dom->saveHTML($child);
        }

        return trim($out);
    }

    /** Bọc bảng trong div cuộn ngang để không phá vỡ bố cục trên điện thoại. */
    private static function wrapTables(DOMDocument $dom, DOMNode $root): void
    {
        $tables = iterator_to_array((new \DOMXPath($dom))->query('.//table', $root));

        foreach ($tables as $table) {
            $parent = $table->parentNode;

            if (!$parent instanceof DOMElement && !$parent instanceof DOMNode) {
                continue;
            }

            if ($parent instanceof DOMElement
                && strtolower($parent->nodeName) === 'div'
                && str_contains($parent->getAttribute('class'), 'table-scroll')) {
                continue;
            }

            $wrapper = $dom->createElement('div');
            $wrapper->setAttribute('class', 'table-scroll');
            $parent->replaceChild($wrapper, $table);
            $wrapper->appendChild($table);
        }
    }

    private static function cleanChildren(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMElement) {
                static::cleanElement($child);
            } elseif ($child->nodeType === XML_COMMENT_NODE || $child->nodeType === XML_PI_NODE) {
                $child->parentNode?->removeChild($child);
            }
        }
    }

    private static function cleanElement(DOMElement $el): void
    {
        $tag = strtolower($el->nodeName);

        if (in_array($tag, self::DROP, true)) {
            $el->parentNode?->removeChild($el);

            return;
        }

        if (!array_key_exists($tag, self::ALLOWED)) {
            static::cleanChildren($el);
            static::unwrap($el);

            return;
        }

        foreach (iterator_to_array($el->attributes) as $attr) {
            if (!in_array(strtolower($attr->nodeName), self::ALLOWED[$tag], true)) {
                $el->removeAttribute($attr->nodeName);
            }
        }

        if (!static::cleanAttributes($el, $tag)) {
            return;
        }

        static::cleanChildren($el);
    }

    /** @return bool false nếu phần tử đã bị gỡ bỏ */
    private static function cleanAttributes(DOMElement $el, string $tag): bool
    {
        if ($tag === 'a') {
            $href = static::safeUrl($el->getAttribute('href'), ['http', 'https', 'mailto', 'tel']);

            if ($href === null) {
                static::unwrap($el);

                return false;
            }

            $el->setAttribute('href', $href);

            if (static::isExternal($href)) {
                $el->setAttribute('target', '_blank');
                $el->setAttribute('rel', 'noopener noreferrer');
            } else {
                $el->removeAttribute('target');
                $el->removeAttribute('rel');
            }
        }

        if ($tag === 'img') {
            $src = static::safeUrl($el->getAttribute('src'), ['http', 'https']);

            if ($src === null) {
                $el->parentNode?->removeChild($el);

                return false;
            }

            $el->setAttribute('src', $src);
            $el->setAttribute('loading', 'lazy');
        }

        if ($tag === 'iframe') {
            $src  = static::safeUrl($el->getAttribute('src'), ['https']);
            $host = $src ? strtolower((string) parse_url($src, PHP_URL_HOST)) : '';

            if ($src === null || !in_array($host, self::EMBED_HOSTS, true)) {
                $el->parentNode?->removeChild($el);

                return false;
            }

            $el->setAttribute('src', $src);
            $el->setAttribute('allowfullscreen', 'true');
            $el->setAttribute('loading', 'lazy');
            $el->setAttribute('frameborder', '0');
        }

        if ($tag === 'th' || $tag === 'td') {
            foreach (self::SPAN_ATTRIBUTES as $attr) {
                $span = (int) $el->getAttribute($attr);

                if ($span < 2 || $span > 20) {
                    $el->removeAttribute($attr);
                } else {
                    $el->setAttribute($attr, (string) $span);
                }
            }
        }

        if ($tag === 'div') {
            $classes = array_intersect(
                preg_split('/\s+/', trim($el->getAttribute('class'))) ?: [],
                self::ALLOWED_DIV_CLASSES
            );

            if ($classes === []) {
                $el->removeAttribute('class');
            } else {
                $el->setAttribute('class', implode(' ', $classes));
            }
        }

        return true;
    }

    /** Giữ lại nội dung bên trong, bỏ thẻ bao ngoài. */
    private static function unwrap(DOMElement $el): void
    {
        $parent = $el->parentNode;

        if (!$parent) {
            return;
        }

        foreach (iterator_to_array($el->childNodes) as $child) {
            $parent->insertBefore($child, $el);
        }

        $parent->removeChild($el);
    }

    /** @param string[] $schemes */
    private static function safeUrl(string $url, array $schemes): ?string
    {
        $url = trim($url);

        if ($url === '') {
            return null;
        }

        // Đường dẫn nội bộ (/tin-tuc/..., /storage/...) luôn hợp lệ.
        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            return $url;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, $schemes, true) ? $url : null;
    }

    private static function isExternal(string $url): bool
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        if ($host === '') {
            return false;
        }

        return $host !== strtolower((string) parse_url(config('app.url'), PHP_URL_HOST));
    }
}
