<?php

declare(strict_types=1);

namespace ThinkDigital\ContaoGridPlugin\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Template;
use ThinkDigital\ContaoGridPlugin\Grid\GridClasses;

/**
 * Appends the grid classes to `$template->class` for every legacy template that
 * carries grid settings — the same job dma/dma_simple_grid's `parseTemplate`
 * hook did.
 *
 * The theme relies on this for its legacy `.html.twig` templates that read the
 * flat `class` variable: block_searchable, block_unsearchable, mod_calendar and
 * the grid wrapper template. Modern Twig fragments do not trigger this hook and
 * use the `simple_grid_classes()` function instead (GridClassesExtension).
 */
#[AsHook('parseTemplate')]
final class ParseTemplateListener
{
    public function __construct(private readonly GridClasses $gridClasses)
    {
    }

    public function __invoke(Template $template): void
    {
        $data = $template->getData();

        if (!$this->gridClasses->hasGridInfo($data)) {
            return;
        }

        $template->class = ($template->class ?? '').' '.$this->gridClasses->forRow($data);
    }
}
