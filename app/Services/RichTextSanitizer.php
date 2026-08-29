<?php

declare(strict_types=1);

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;

final class RichTextSanitizer
{
    /** @var list<string> */
    private const TAGS = ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'ul', 'ol', 'li', 'blockquote', 'h2', 'h3', 'h4', 'a', 'code', 'pre'];

    public function sanitize(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return $html;
        }

        if (! class_exists(DOMDocument::class)) {
            // Safe degradation when ext-dom is unavailable: show escaped text
            // rather than preserving potentially executable attributes.
            return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="UTF-8"><div id="rythme-rich-root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('rythme-rich-root');
        if (! $root instanceof DOMElement) {
            return '';
        }

        $this->cleanChildren($root);

        $result = '';
        foreach ($root->childNodes as $child) {
            $result .= $document->saveHTML($child);
        }

        return trim($result);
    }

    private function cleanChildren(DOMNode $parent): void
    {
        for ($node = $parent->lastChild; $node !== null; $node = $previous) {
            $previous = $node->previousSibling;

            if ($node instanceof DOMElement) {
                $this->cleanChildren($node);
                $tag = strtolower($node->tagName);

                if (! in_array($tag, self::TAGS, true)) {
                    while ($node->firstChild !== null) {
                        $parent->insertBefore($node->firstChild, $node);
                    }
                    $parent->removeChild($node);
                    continue;
                }

                foreach (iterator_to_array($node->attributes) as $attribute) {
                    $name = strtolower($attribute->name);
                    if ($tag !== 'a' || ! in_array($name, ['href', 'target', 'rel'], true)) {
                        $node->removeAttribute($attribute->name);
                    }
                }

                if ($tag === 'a') {
                    $href = trim($node->getAttribute('href'));
                    if (! preg_match('~^(https?://|mailto:|tel:|/|#)~i', $href)) {
                        $node->removeAttribute('href');
                    }
                    if ($node->getAttribute('target') === '_blank') {
                        $node->setAttribute('rel', 'noopener noreferrer');
                    } else {
                        $node->removeAttribute('target');
                        $node->removeAttribute('rel');
                    }
                }
            }
        }
    }
}
