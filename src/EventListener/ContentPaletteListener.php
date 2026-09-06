<?php

declare(strict_types=1);

namespace ThinkDigital\ContaoGridPlugin\EventListener;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\Database;
use Contao\DataContainer;
use Contao\Input;
use Contao\StringUtil;
use ThinkDigital\ContaoGridPlugin\Grid\GridClasses;

/**
 * Injects the grid column / offset settings into every content-element palette
 * that has a `cssID` field, shows the resulting grid classes in the element
 * list, and auto-creates the matching wrapper stop element — the jobs the
 * predecessor grid solution's DcaCallbacks did, reduced to columns + offset
 * and the wrapper pair (see docs/DECISIONS.md).
 */
class ContentPaletteListener
{
    private const GRID_FIELDS = ['dma_simplegrid_columnsettings', 'dma_simplegrid_offsetsettings'];

    /** Palettes that never get the grid fields. */
    private const SKIP = ['module', 'dma_simplegrid_wrapper_start', 'dma_simplegrid_wrapper_stop'];

    public function __construct(private readonly GridClasses $gridClasses)
    {
    }

    #[AsCallback(table: 'tl_content', target: 'config.onload')]
    public function adjustPalettes(): void
    {
        $manipulator = PaletteManipulator::create()
            ->addLegend('grid_legend', 'expert_legend', PaletteManipulator::POSITION_AFTER)
            ->addField(self::GRID_FIELDS, 'grid_legend', PaletteManipulator::POSITION_APPEND)
        ;

        foreach ($GLOBALS['TL_DCA']['tl_content']['palettes'] as $key => $palette) {
            if (!\is_string($palette) || \in_array($key, self::SKIP, true) || !str_contains($palette, 'cssID')) {
                continue;
            }

            $manipulator->applyToPalette($key, 'tl_content');
        }
    }

    /**
     * Grey grid-class hint in front of the row operations. The operation is
     * registered in the DCA file.
     *
     * @param array<string, mixed> $row
     */
    #[AsCallback(table: 'tl_content', target: 'list.operations.grid_info.button_callback')]
    public function gridInfoButton(array $row): string
    {
        $classes = $this->gridClasses->forRow($row);

        if ('' === $classes) {
            return '';
        }

        return sprintf(
            '<span class="tl_gray" style="padding-right:6px;white-space:nowrap" title="%s">%s</span> ',
            StringUtil::specialchars($GLOBALS['TL_LANG']['tl_content']['grid_info'][1] ?? 'Grid'),
            StringUtil::specialchars($classes),
        );
    }

    #[AsCallback(table: 'tl_content', target: 'config.onsubmit')]
    public function createWrapperStop(DataContainer $dc): void
    {
        $record = $dc->activeRecord;

        if (!$record || 'auto' === Input::post('SUBMIT_TYPE') || 'dma_simplegrid_wrapper_start' !== $record->type) {
            return;
        }

        $db = Database::getInstance();
        $ptable = $record->ptable ?: 'tl_article';

        $next = $db
            ->prepare("SELECT type FROM tl_content WHERE pid = ? AND (ptable = ? OR ptable = ?) AND type = 'dma_simplegrid_wrapper_stop' AND sorting > ? ORDER BY sorting ASC LIMIT 1")
            ->execute($record->pid, $ptable, 'tl_article' === $ptable ? '' : $ptable, $record->sorting)
        ;

        if ($next->type) {
            return;
        }

        $db
            ->prepare('INSERT INTO tl_content %s')
            ->set([
                'pid' => $record->pid,
                'ptable' => $ptable,
                'type' => 'dma_simplegrid_wrapper_stop',
                'sorting' => $record->sorting + 1,
                'tstamp' => time(),
            ])
            ->execute()
        ;
    }
}
