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
        // Merge simple: take first for now (extend later)
        $xml = simplexml_load_file($paths[0]);
        return $xml ?: null;
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
}


