<?php

declare(strict_types=1);

namespace ThinkDigital\ContaoGridPlugin\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Grid wrapper (stop) — closes the four <div>s opened by
 * GridWrapperStartController. Type name `dma_simplegrid_wrapper_stop` unchanged
 * (CONTAO6_MIGRATION.md ADR-3 / ADR-14).
 */
#[AsContentElement('dma_simplegrid_wrapper_stop', category: 'designplusPlugins', template: 'content_element/grid_wrapper_stop')]
class GridWrapperStopController extends AbstractContentElementController
{
    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        return $template->getResponse();
    }
}
