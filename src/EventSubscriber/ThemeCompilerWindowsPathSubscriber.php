<?php declare(strict_types=1);

namespace App\EventSubscriber;

use Shopware\Storefront\Event\ThemeCompilerConcatenatedStylesEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * On Windows, ThemeCompiler::concatenateStyles() embeds absolute file paths using
 * DIRECTORY_SEPARATOR (backslash) into `@import '...'` statements. Backslash is a
 * string-escape character in SCSS, so those paths get corrupted during compilation.
 * Normalizing to forward slashes here fixes theme compilation on Windows.
 */
class ThemeCompilerWindowsPathSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ThemeCompilerConcatenatedStylesEvent::class => 'onConcatenatedStyles',
        ];
    }

    public function onConcatenatedStyles(ThemeCompilerConcatenatedStylesEvent $event): void
    {
        $event->setConcatenatedStyles(
            str_replace('\\', '/', $event->getConcatenatedStyles())
        );
    }
}
