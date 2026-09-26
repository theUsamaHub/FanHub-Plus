<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/** Small, explicit rich-text vocabulary for public articles and fan submissions. */
class SafeArticleHtml
{
    public static function render(?string $html): string
    {
        if (! $html) return '';
        if ($html === strip_tags($html)) return nl2br(e($html));
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8"><html><body>'.$html.'</body></html>', LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $body = $document->getElementsByTagName('body')->item(0);
        return $body ? self::children($body) : '';
    }

    private static function children(DOMNode $node): string
    {
        $result = '';
        foreach ($node->childNodes as $child) {
            if (! $child instanceof DOMElement) {
                if ($child->nodeType === XML_TEXT_NODE) $result .= e($child->textContent);
                continue;
            }
            $tag = strtolower($child->tagName);
            if (in_array($tag, ['script', 'style', 'iframe', 'object', 'svg', 'math', 'form', 'input', 'button', 'template'])) continue;
            $content = self::children($child);
            if (! in_array($tag, ['p', 'h2', 'h3', 'h4', 'strong', 'b', 'em', 'i', 'u', 's', 'ul', 'ol', 'li', 'blockquote', 'br', 'hr', 'a', 'img', 'figure', 'figcaption', 'code', 'pre'])) {
                $result .= $content;
                continue;
            }
            $attributes = '';
            if ($tag === 'a') {
                $url = $child->getAttribute('href');
                if (self::safeUrl($url)) $attributes = ' href="'.e($url).'" rel="nofollow noopener"';
            }
            if ($tag === 'img') {
                $url = $child->getAttribute('src');
                if (! self::safeUrl($url)) continue;
                $attributes = ' src="'.e($url).'" alt="'.e($child->getAttribute('alt')).'" loading="lazy"';
            }
            $result .= '<'.$tag.$attributes.'>'.(in_array($tag, ['br', 'hr', 'img']) ? '' : $content.'</'.$tag.'>');
        }
        return $result;
    }

    private static function safeUrl(string $url): bool
    {
        return (bool) preg_match('~^https?://~i', $url) || (str_starts_with($url, '/') && ! str_starts_with($url, '//') && ! str_contains($url, '\\'));
    }
}
