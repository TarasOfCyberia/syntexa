<?php

declare(strict_types=1);

namespace Syntexa\Frontend\Layout;

use Syntexa\Frontend\View\TwigFactory;

class LayoutRenderer
{
    public static function renderHandle(string $handle, array $context = []): string
    {
        $xml = LayoutLoader::loadHandle($handle);
        if (!$xml) {
            // Fallback minimal HTML to avoid empty responses
            return '<!doctype html><html><head><meta charset="utf-8"><title>'
                . htmlspecialchars($context['title'] ?? 'Layout')
                . '</title></head><body><main></main></body></html>';
        }
        return self::renderNode($xml, $context);
    }

    private static function renderNode(\SimpleXMLElement $node, array $context): string
    {
        $name = $node->getName();
        if ($name === 'container') {
            // Collect regions by child container name; concatenate blocks into content
            $regions = [];
            $content = '';
            foreach ($node->children() as $child) {
                if ($child->getName() === 'container') {
                    $regionName = (string)($child['name'] ?? 'content');
                    $regions[$regionName] = ($regions[$regionName] ?? '') . self::renderNode($child, $context);
                } else {
                    $content .= self::renderNode($child, $context);
                }
            }
            $template = (string)($node['template'] ?? '');
            if ($template !== '') {
                return TwigFactory::get()->render((string)$template, ['content' => $content] + $regions + $context);
            }
            return $content;
        }

        if ($name === 'block') {
            $template = (string)$node['template'];
            $data = $context;
            // Collect <arg name="...">value</arg>
            foreach ($node->children() as $child) {
                if ($child->getName() === 'arg' && isset($child['name'])) {
                    $data[(string)$child['name']] = (string)$child;
                }
            }
            return TwigFactory::get()->render($template, $data);
        }

        // Unknown node
        return '';
    }
}


