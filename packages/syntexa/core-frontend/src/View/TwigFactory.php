<?php

declare(strict_types=1);

namespace Syntexa\Frontend\View;

use Syntexa\Core\ModuleRegistry;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigFactory
{
    private static ?Environment $twig = null;

    public static function get(): Environment
    {
        if (self::$twig instanceof Environment) {
            return self::$twig;
        }

        $loader = new FilesystemLoader();

        foreach (ModuleRegistry::getModules() as $module) {
            $paths = $module['templatePaths'] ?? [];
            $aliases = $module['aliases'] ?? [$module['name']];
            foreach ($paths as $p) {
                if (!is_dir($p)) { continue; }
                foreach ($aliases as $alias) {
                    $loader->addPath($p, (string)$alias);
                }
            }
        }

        $cacheDir = self::getCacheDir();
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0777, true);
        }

        self::$twig = new Environment($loader, [
            'cache' => $cacheDir,
            'auto_reload' => true,
            'strict_variables' => false,
        ]);

        return self::$twig;
    }

    private static function getCacheDir(): string
    {
        $root = dirname(__DIR__, 5);
        return $root . '/var/cache/twig';
    }
}


