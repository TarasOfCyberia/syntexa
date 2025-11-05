<?php

declare(strict_types=1);

namespace Syntexa\Frontend\Layout;

use Syntexa\Frontend\Block\BlockHandlerInterface;
use Syntexa\Frontend\Block\BlockContext;
use Syntexa\Frontend\Block\RenderState;

class BlockHandlerRegistry
{
    private static array $byBlock = [];
    private static bool $initialized = false;

    public static function reset(): void
    {
        self::$byBlock = [];
        self::$initialized = false;
    }

    public static function register(string $blockClass, string $handlerClass, int $priority = 100): void
    {
        self::$byBlock[$blockClass][] = ['class' => $handlerClass, 'priority' => $priority];
        // Keep highest priority first
        usort(self::$byBlock[$blockClass], fn($a, $b) => $b['priority'] <=> $a['priority']);
    }

    /**
     * @return array<int, array{class:string,priority:int}>
     */
    public static function getHandlers(string $blockClass): array
    {
        return self::$byBlock[$blockClass] ?? [];
    }
}


