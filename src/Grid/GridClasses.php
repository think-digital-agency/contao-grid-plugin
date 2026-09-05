<?php

declare(strict_types=1);

namespace ThinkDigital\ContaoGridPlugin\Grid;

use Contao\StringUtil;

/**
 * Builds the grid CSS classes for a tl_content / tl_form_field row from its
 * `dma_simplegrid_columnsettings` + `dma_simplegrid_offsetsettings` values.
 *
 * Behaviourally identical to DMA\DmaSimpleGrid::getColumnClasses() for the
 * `bootstrap4` preset with columns + offset enabled (CONTAO6_MIGRATION.md
 * ADR-14): all column classes first, then all offset classes, space-joined.
 */
final class GridClasses
{
    /**
     * @param array<string, mixed> $row
     */
    public function hasGridInfo(array $row): bool
    {
        foreach (['dma_simplegrid_columnsettings', 'dma_simplegrid_offsetsettings'] as $key) {
            if (!empty($row[$key])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $row
     */
    public function forRow(array $row): string
    {
        if (!$this->hasGridInfo($row)) {
            return '';
        }

        $classes = [];

        foreach ($this->firstSetting($row['dma_simplegrid_columnsettings'] ?? null) as $breakpoint => $value) {
            if (!\in_array($breakpoint, GridConfig::BREAKPOINTS, true) || !$this->isSet($value)) {
                continue;
            }

            $classes[] = 'hide' === $value
                ? GridConfig::hideClass($breakpoint)
                : GridConfig::columnClass($breakpoint, (string) $value);
        }

        foreach ($this->firstSetting($row['dma_simplegrid_offsetsettings'] ?? null) as $breakpoint => $value) {
            if (!\in_array($breakpoint, GridConfig::BREAKPOINTS, true)) {
                continue;
            }

            if ('reset' === $value && GridConfig::HAS_OFFSET_RESET) {
                $classes[] = GridConfig::offsetClass($breakpoint, '0');
            } elseif ($this->isSet($value)) {
                $classes[] = GridConfig::offsetClass($breakpoint, (string) $value);
            }
        }

        return implode(' ', $classes);
    }

    /**
     * DMA stores a single-row MultiColumnWizard value: a:1:{i:0;a:5:{bp=>val}}.
     * Returns the inner breakpoint=>value map, or [] for anything else.
     *
     * @return array<string, mixed>
     */
    private function firstSetting(mixed $value): array
    {
        if (\is_string($value)) {
            $value = StringUtil::deserialize($value, true);
        }

        if (!\is_array($value) || 1 !== \count($value) || !isset($value[0]) || !\is_array($value[0])) {
            return [];
        }

        return $value[0];
    }

    private function isSet(mixed $value): bool
    {
        return null !== $value && '' !== $value && '0' !== $value;
    }
}
