<?php

declare(strict_types=1);

namespace ThinkDigital\ContaoGridPlugin\Grid;

/**
 * The single, hard-coded grid definition (CONTAO6_MIGRATION.md ADR-14).
 *
 * Mirrors the `bootstrap4` grid preset the theme actually runs, reduced to the
 * two features in use: columns + offset.
 *
 *   xs             -> col-%d      / offset-%d
 *   sm|md|lg|xl     -> col-{bp}-%d / offset-{bp}-%d
 *   column value "hide"   -> d-{bp}-none
 *   offset value  "reset" -> offset[-{bp}]-0   (hasColumnOffsetReset)
 */
final class GridConfig
{
    /** Breakpoints in output order. */
    public const BREAKPOINTS = ['xs', 'sm', 'md', 'lg', 'xl'];

    /** Selectable column / offset sizes. */
    public const SIZES = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'];

    public const HAS_OFFSET_RESET = true;

    public static function columnClass(string $breakpoint, string $value): string
    {
        return 'xs' === $breakpoint ? 'col-'.$value : 'col-'.$breakpoint.'-'.$value;
    }

    public static function offsetClass(string $breakpoint, string $value): string
    {
        return 'xs' === $breakpoint ? 'offset-'.$value : 'offset-'.$breakpoint.'-'.$value;
    }

    public static function hideClass(string $breakpoint): string
    {
        return 'd-'.$breakpoint.'-none';
    }
}
