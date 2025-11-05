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
            return '';
        }
        return self::renderNode($xml, $context);
    }

    private static function renderNode(\SimpleXMLElement $node, array $context): string
    {
        $name = $node->getName();
        if ($name === 'container') {
            $output = '';
            foreach ($node->children() as $child) {
                $output .= self::renderNode($child, $context);
            }
            // Optional container template
            $template = (string)($node['template'] ?? '');
            if ($template !== '') {
                return TwigFactory::get()->render((string)$template, ['content' => $output] + $context);
            }
            return $output;
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


