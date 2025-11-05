<?php

declare(strict_types=1);

namespace Syntexa\Frontend\Layout;

use Syntexa\Core\ModuleRegistry;

class LayoutLoader
{
    public static function loadHandle(string $handle): ?\SimpleXMLElement
    {
        $files = self::findLayoutFiles($handle);
        if (empty($files)) {
            return null;
        }

        // Start with first file as base
        $baseXml = simplexml_load_file($files[0]['path']);
        if (!$baseXml) { return null; }

        // Merge subsequent files in order
        for ($i = 1; $i < count($files); $i++) {
            $next = simplexml_load_file($files[$i]['path']);
            if ($next) {
                $baseXml = self::mergeContainers($baseXml, $next);
            }
        }

        // Support extends="..." at the final stage (child can extend a base)
        $extends = (string)($baseXml['extends'] ?? '');
        if ($extends !== '') {
            $parent = self::loadHandle($extends);
            if ($parent) {
                $baseXml = self::mergeContainers($parent, $baseXml);
            }
        }

        return $baseXml;
    }

    private static function findLayoutFiles(string $handle): array
    {
        $found = [];
        foreach (ModuleRegistry::getModules() as $module) {
            $dir = $module['path'] . '/src/Application/View/templates/layout';
            $file = $dir . '/' . $handle . '.xml';
            if (is_file($file)) {
                $priority = 10; // default for modules
                $composerType = $module['composerType'] ?? 'syntexa-module';
                if (($module['name'] ?? '') === 'module-core-frontend') { $priority = 0; }
                if ($composerType === 'syntexa-theme') { $priority = 20; }
                $found[] = [
                    'path' => $file,
                    'priority' => $priority,
                ];
            }
        }
        // Sort by priority (low first -> base), keep stable order within same priority
        usort($found, fn($a, $b) => $a['priority'] <=> $b['priority']);
        return $found;
    }

    private static function mergeContainers(\SimpleXMLElement $base, \SimpleXMLElement $child): \SimpleXMLElement
    {
        // Process structural operations first: remove / move
        foreach ($child->children() as $op) {
            if ($op->getName() === 'remove' && isset($op['name'])) {
                self::removeNodeByName($base, (string)$op['name']);
            }
            if ($op->getName() === 'move' && isset($op['name']) && isset($op['into'])) {
                self::moveNode($base, (string)$op['name'], (string)$op['into'], (string)($op['before'] ?? ''), (string)($op['after'] ?? ''));
            }
        }

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
            } elseif ($name === 'block') {
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

    private static function removeNodeByName(\SimpleXMLElement $root, string $name): void
    {
        $idx = 0;
        foreach ($root->children() as $node) {
            $isMatch = ((string)($node['name'] ?? '') === $name)
                || ((string)($node['template'] ?? '') === $name);
            if ($isMatch) {
                unset($root->children()[$idx]);
                return;
            }
            $idx++;
        }
        // Recurse containers
        foreach ($root->children() as $node) {
            if ($node->getName() === 'container') {
                self::removeNodeByName($node, $name);
            }
        }
    }

    private static function moveNode(\SimpleXMLElement $root, string $name, string $into, string $before = '', string $after = ''): void
    {
        $nodeRef = self::detachNodeByName($root, $name);
        if (!$nodeRef) { return; }
        $dest = self::findContainerByName($root, $into);
        if (!$dest) { $dest = $root; }
        // Append at end for now; positioning (before/after) could be handled later
        self::appendNode($dest, $nodeRef);
    }

    private static function detachNodeByName(\SimpleXMLElement $root, string $name): ?\SimpleXMLElement
    {
        $idx = 0;
        foreach ($root->children() as $node) {
            $isMatch = ((string)($node['name'] ?? '') === $name)
                || ((string)($node['template'] ?? '') === $name);
            if ($isMatch) {
                // Clone node before unsetting
                $cloned = new \SimpleXMLElement($node->asXML() ?: '<block />');
                unset($root->children()[$idx]);
                return $cloned;
            }
            $idx++;
        }
        foreach ($root->children() as $node) {
            if ($node->getName() === 'container') {
                $found = self::detachNodeByName($node, $name);
                if ($found) { return $found; }
            }
        }
        return null;
    }
}


