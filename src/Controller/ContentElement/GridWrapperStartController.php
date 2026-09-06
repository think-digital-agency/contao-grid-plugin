<?php

declare(strict_types=1);

namespace ThinkDigital\ContaoGridPlugin\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\StringUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ThinkDigital\ContaoGridPlugin\Grid\GridClasses;

/**
 * Grid wrapper (start) — native replacement for the legacy
 * `dma_simplegrid_wrapper_start` content element. Type name kept unchanged so no
 * content migration is needed (see docs/DECISIONS.md).
 *
 * Reproduces the markup of the previous theme template
 * `templates/theme-design/ce_dma_simplegrid_wrapperstart.html.twig` byte-for-byte:
 * the legacy element exposed `class = "ce_wrapper <cssID[1]> <grid classes>"`
 * (bootstrap4 wrapper-class is empty, so `type` became "wrapper ").
 */
#[AsContentElement('dma_simplegrid_wrapper_start', category: 'designplusPlugins', template: 'content_element/grid_wrapper_start')]
class GridWrapperStartController extends AbstractContentElementController
{
    private const GRID_PREFIXES = ['col-', 'offset-', 'order-', 'u-hidden'];

    public function __construct(private readonly GridClasses $gridClasses)
    {
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $cssId = StringUtil::deserialize($model->cssID, true);

        // Legacy `class` = "ce_wrapper " . cssID[1], then the parseTemplate hook
        // appended the grid classes.
        $classes = array_values(array_filter(
            [...explode(' ', 'ce_wrapper '.($cssId[1] ?? '')), ...explode(' ', $this->gridClasses->forRow($model->row()))],
            static fn (string $c): bool => '' !== $c,
        ));

        $isGrid = static fn (string $c): bool => array_filter(
            self::GRID_PREFIXES,
            static fn (string $p): bool => str_starts_with($c, $p),
        ) !== [];

        $gridClasses = array_values(array_filter($classes, $isGrid));
        $innerClasses = array_map(
            static fn (string $c): string => str_starts_with($c, 'ce_') ? 'c-'.substr($c, 3) : $c,
            array_values(array_filter($classes, static fn (string $c): bool => !$isGrid($c))),
        );

        $template->set('wrapper_grid_class', implode(' ', $gridClasses) ?: 'col-12');
        $template->set('wrapper_inner_class', implode(' ', $innerClasses));
        $template->set('wrapper_id', ($cssId[0] ?? '') !== '' ? ' id="'.$cssId[0].'"' : '');

        return $template->getResponse();
    }
}
