<?php

declare(strict_types=1);

namespace ThinkDigital\ContaoGridPlugin\Twig;

use ThinkDigital\ContaoGridPlugin\Grid\GridClasses;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Provides the `simple_grid_classes()` Twig function.
 *
 * Name and behaviour kept identical to dma/dma_simple_grid so the theme's native
 * `designplus_*` fragment templates (WP-3) keep working unchanged: it reads the
 * current element row from the template context (`data`) and returns the grid
 * classes, or '' when the element has no grid settings.
 */
final class GridClassesExtension extends AbstractExtension
{
    public function __construct(private readonly GridClasses $gridClasses)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('simple_grid_classes', $this->getGridClasses(...), ['needs_context' => true]),
        ];
    }

    /**
     * @param array<string, mixed> $context
     */
    public function getGridClasses(array $context): string
    {
        $data = $context['data'] ?? null;

        return \is_array($data) ? $this->gridClasses->forRow($data) : '';
    }
}
