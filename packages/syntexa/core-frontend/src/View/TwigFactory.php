<?php

declare(strict_types=1);

namespace Syntexa\Frontend\View;

use Syntexa\Core\ModuleRegistry;
use Syntexa\Core\Environment;
use Twig\Environment as TwigEnvironment;
use Twig\Loader\FilesystemLoader;

class TwigFactory
{
    private static ?TwigEnvironment $twig = null;

    public static function get(): TwigEnvironment
    {
        if (self::$twig instanceof TwigEnvironment) {
            return self::$twig;
        }

        $loader = new FilesystemLoader();

        $modules = ModuleRegistry::getModules();
        $env = Environment::create();
        $activeTheme = $env->get('THEME', '');

        // Register themes first (override), then regular modules
        $themes = array_filter($modules, fn($m) => ($m['composerType'] ?? '') === 'syntexa-theme');
        $others = array_filter($modules, fn($m) => ($m['composerType'] ?? '') !== 'syntexa-theme');

        // If active theme specified, keep only matching themes (by alias or name)
        if ($activeTheme !== '') {
            $themes = array_filter($themes, function($m) use ($activeTheme) {
                $aliases = $m['aliases'] ?? [];
                return in_array($activeTheme, $aliases, true) || ($m['name'] ?? '') === $activeTheme;
            });
        }

        $ordered = array_merge($themes, $others);

        foreach ($ordered as $module) {
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

        self::$twig = new TwigEnvironment($loader, [
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


