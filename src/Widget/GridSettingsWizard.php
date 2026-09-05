<?php

declare(strict_types=1);

namespace ThinkDigital\ContaoGridPlugin\Widget;

use Contao\StringUtil;
use Contao\Widget;
use ThinkDigital\ContaoGridPlugin\Grid\GridConfig;

/**
 * Back-end widget: one row of five breakpoint <select>s (xs–xl) for the grid
 * column resp. offset setting, plus a live preview (12-column bars, click/drag
 * editable) contributed by grid-settings.js.
 *
 * Drop-in replacement for the single-row MultiColumnWizard the predecessor
 * grid solution used, so `menatwork/contao-multicolumnwizard-bundle` can be
 * removed. The value is stored in the same format:
 * a:1:{i:0;a:5:{s:2:"xs";…}}.
 */
class GridSettingsWizard extends Widget
{
    protected $blnSubmitInput = true;

    protected $strTemplate = 'be_widget';

    /** Bootstrap-4 breakpoint lower bounds, for the select tooltips. */
    private const BREAKPOINT_HINT = [
        'xs' => 'gilt für alle Größen · Standard: col-12 (volle Breite)',
        'sm' => 'ab 576 px',
        'md' => 'ab 768 px',
        'lg' => 'ab 992 px',
        'xl' => 'ab 1200 px',
    ];

    public function __construct($arrAttributes = null)
    {
        parent::__construct($arrAttributes);

        $GLOBALS['TL_CSS']['contao-grid-plugin'] = 'bundles/contaogridplugin/backend/grid-settings.css|static';
        $GLOBALS['TL_JAVASCRIPT']['contao-grid-plugin'] = 'bundles/contaogridplugin/backend/grid-settings.js|static';
    }

    public function __set($strKey, $varValue): void
    {
        if ('value' === $strKey) {
            $this->varValue = \is_array($varValue) ? $varValue : StringUtil::deserialize($varValue, true);

            return;
        }

        parent::__set($strKey, $varValue);
    }

    /**
     * @param mixed $varInput
     *
     * @return array<int, array<string, string>>
     */
    protected function validator($varInput)
    {
        $row = [];
        $hasValue = false;

        foreach (GridConfig::BREAKPOINTS as $breakpoint) {
            $value = \is_array($varInput) ? (string) ($varInput[$breakpoint] ?? '') : '';
            $row[$breakpoint] = $value;
            $hasValue = $hasValue || '' !== $value;
        }

        return $hasValue ? [$row] : [];
    }

    public function generate(): string
    {
        $isOffset = 'dma_simplegrid_offsetsettings' === $this->strField;

        $special = $isOffset
            ? ['reset' => '0']
            : ['hide' => $GLOBALS['TL_LANG']['MSC']['dma_simplegrid_hidden'] ?? 'hidden'];

        $current = (\is_array($this->varValue) && \is_array($this->varValue[0] ?? null)) ? $this->varValue[0] : [];

        $selects = '';

        foreach (GridConfig::BREAKPOINTS as $breakpoint) {
            $selected = (string) ($current[$breakpoint] ?? '');
            $label = 'xs' === $breakpoint ? 'XS · alle' : strtoupper($breakpoint);
            $options = '<option value="">–</option>';

            foreach ($special as $value => $optLabel) {
                $options .= sprintf(
                    '<option value="%s"%s>%s</option>',
                    $value,
                    $value === $selected ? ' selected' : '',
                    StringUtil::specialchars((string) $optLabel),
                );
            }

            foreach (GridConfig::SIZES as $size) {
                $options .= sprintf(
                    '<option value="%s"%s>%s</option>',
                    $size,
                    $size === $selected ? ' selected' : '',
                    $size,
                );
            }

            $selects .= sprintf(
                '<span class="w-gs__bp" title="%s"><strong>%s</strong>'
                .'<select name="%s[%s]" data-bp="%s" class="tl_select">%s</select></span>',
                StringUtil::specialchars(strtoupper($breakpoint).' · '.self::BREAKPOINT_HINT[$breakpoint]),
                $label,
                $this->strName,
                $breakpoint,
                $breakpoint,
                $options,
            );
        }

        // Only the columns widget owns the visual preview; grid-settings.js reads
        // the sibling offset selects from the same form.
        $preview = $isOffset ? '' : '<div class="w-gs__preview" data-td-grid-bars></div>';

        return sprintf(
            '<div id="ctrl_%s" class="w-gs" data-td-grid data-td-grid-kind="%s"><div class="w-gs__row">%s</div>%s</div>',
            $this->strId,
            $isOffset ? 'offset' : 'columns',
            $selects,
            $preview,
        );
    }
}
