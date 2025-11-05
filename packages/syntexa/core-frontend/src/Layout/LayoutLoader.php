<?php

declare(strict_types=1);

namespace Syntexa\Frontend\Layout;

use Syntexa\Core\ModuleRegistry;

class LayoutLoader
{
    public static function loadHandle(string $handle): ?\SimpleXMLElement
    {
        $paths = self::findLayoutFiles($handle);
        if (empty($paths)) {
            return null;
        }
        $xml = simplexml_load_file($paths[0]);
        if (!$xml) { return null; }

        // Handle extends="...": merge base first
        $extends = (string)($xml['extends'] ?? '');
        if ($extends !== '') {
            $base = self::loadHandle($extends);
            if ($base) {
                $xml = self::mergeContainers($base, $xml);
            }
        }
        return $xml;
    }

    private static function findLayoutFiles(string $handle): array
    {
        $files = [];
        foreach (ModuleRegistry::getModules() as $module) {
            $dir = $module['path'] . '/src/Application/View/templates/layout';
            $file = $dir . '/' . $handle . '.xml';
            if (is_file($file)) {
                $files[] = $file;
            }
        }
        return $files;
    }

    private static function mergeContainers(\SimpleXMLElement $base, \SimpleXMLElement $child): \SimpleXMLElement
    {
        // Merge child containers/blocks into base by container name
        foreach ($child->children() as $node) {
            $name = $node->getName();
            if ($name === 'container' && isset($node['name'])) {
                $targetName = (string)$node['name'];
                $target = self::findContainerByName($base, $targetName);
                if ($target) {
                    self::appendChildren($target, $node);
                } else {
                    self::appendNode($base, $node);
                }
            } else {
                self::appendNode($base, $node);
            }
        }
        return $base;
    }

    private static function findContainerByName(\SimpleXMLElement $root, string $name): ?\SimpleXMLElement
    {
        foreach ($root->children() as $node) {
            if ($node->getName() === 'container' && (string)($node['name'] ?? '') === $name) {
                return $node;
            }
        }
        return null;
    }

    private static function appendChildren(\SimpleXMLElement $target, \SimpleXMLElement $source): void
    {
        foreach ($source->children() as $child) {
            self::appendNode($target, $child);
        }
    }

    private static function appendNode(\SimpleXMLElement $target, \SimpleXMLElement $node): void
    {
        $new = $target->addChild($node->getName());
        foreach ($node->attributes() as $k => $v) {
            $new->addAttribute((string)$k, (string)$v);
        }
        foreach ($node->children() as $c) {
            self::appendNode($new, $c);
        }
        // text content for <arg>
        $text = (string)$node;
        if ($text !== '' && count($node->children()) === 0) {
            $new[0] = $text;
        }
    }
}


