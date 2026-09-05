/* Contao Grid Plugin — back-end widget (gridSettingsWizard)
 *
 * Live preview + click/drag editable bars for the column / offset selects.
 * Survives Turbo navigation and Contao's partial DOM swaps via a MutationObserver.
 */
(function () {
    'use strict';

    if (window.__tdGridPreview) {
        return;
    }
    window.__tdGridPreview = true;

    var BP = ['xs', 'sm', 'md', 'lg', 'xl'];

    function scopeOf(el) {
        return el.closest('form') || document;
    }

    function readRow(container) {
        var out = {};
        BP.forEach(function (bp) {
            var s = container && container.querySelector('select[data-bp="' + bp + '"]');
            out[bp] = s ? s.value : '';
        });
        return out;
    }

    function classFor(kind, bp, v) {
        if (!v) {
            return '';
        }
        var infix = bp === 'xs' ? '' : bp + '-';
        if (kind === 'columns') {
            return v === 'hide' ? 'd-' + bp + '-none' : 'col-' + infix + v;
        }
        return v === 'reset' ? 'offset-' + infix + '0' : 'offset-' + infix + v;
    }

    // Effective span/offset per breakpoint after the upward cascade.
    function effective(cols, offs) {
        var span = 12, off = 0, out = [];
        BP.forEach(function (bp) {
            var cv = cols[bp], hidden = false;
            if (cv === 'hide') {
                hidden = true;
            } else if (cv) {
                span = parseInt(cv, 10);
            }
            var ov = offs[bp];
            if (ov === 'reset') {
                off = 0;
            } else if (ov) {
                off = parseInt(ov, 10);
            }
            out.push({ bp: bp, span: hidden ? 0 : span, off: off, hidden: hidden });
        });
        return out;
    }

    function setSelect(container, bp, value) {
        var s = container.querySelector('select[data-bp="' + bp + '"]');
        if (!s || s.value === String(value)) {
            return;
        }
        s.value = String(value);
        s.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function classString(cols, offs) {
        var classes = [];
        // The theme falls back to col-12 (full width) when no xs column is set —
        // show it so the hint reflects what actually renders.
        if (cols.xs !== 'hide' && !cols.xs) {
            classes.push('col-12');
        }
        BP.forEach(function (bp) { var c = classFor('columns', bp, cols[bp]); if (c) { classes.push(c); } });
        BP.forEach(function (bp) { var c = classFor('offset', bp, offs[bp]); if (c) { classes.push(c); } });
        return classes.join(' ') || 'col-12';
    }

    // Class string, right-floated into the CSS ID / class field's own tl_help.
    function updateCssHint(root, str) {
        var css = root.querySelector('#ctrl_cssID, [id^="ctrl_cssID"]');
        if (!css) {
            return;
        }
        var box = css.closest('.widget') || css;
        var help = box.querySelector('p.tl_help');
        var host = help || box;

        var hint = host.querySelector('.w-gs-csshint');
        if (!hint) {
            hint = document.createElement('span');
            hint.className = 'w-gs-csshint';
            if (help) {
                help.insertBefore(hint, help.firstChild);
            } else {
                host.appendChild(hint);
            }
        }

        hint.textContent = 'Grid: ';
        var code = document.createElement('code');
        code.textContent = str;
        hint.appendChild(code);
    }

    function buildRow() {
        var row = document.createElement('div');
        row.className = 'w-gs__brow';
        row.appendChild(document.createElement('b'));
        var track = document.createElement('div');
        track.className = 'w-gs__track';
        var hover = document.createElement('div');
        hover.className = 'w-gs__hover';
        track.appendChild(hover);
        var fill = document.createElement('div');
        fill.className = 'w-gs__fill';
        track.appendChild(fill);
        row.appendChild(track);
        row.appendChild(document.createElement('i'));
        return row;
    }

    // Updates the existing rows in place so drag references stay valid.
    function renderTracks(colC, eff) {
        var wrap = colC.querySelector('[data-td-grid-bars]');
        if (!wrap) {
            return;
        }
        var rows = wrap.querySelectorAll('.w-gs__brow');
        if (rows.length !== eff.length) {
            wrap.textContent = '';
            eff.forEach(function () { wrap.appendChild(buildRow()); });
            rows = wrap.querySelectorAll('.w-gs__brow');
        }

        eff.forEach(function (e, i) {
            var row = rows[i];
            row.className = 'w-gs__brow' + (e.hidden ? ' is-hidden' : '');
            row.querySelector('b').textContent = e.bp.toUpperCase();

            var track = row.querySelector('.w-gs__track');
            track.dataset.bp = e.bp;
            track.dataset.off = String(e.off);

            var fill = row.querySelector('.w-gs__fill');
            fill.style.left = e.hidden ? '0' : (e.off / 12 * 100) + '%';
            fill.style.width = e.hidden ? '100%' : (e.span / 12 * 100) + '%';

            row.querySelector('i').textContent = e.hidden
                ? 'ausgeblendet'
                : (e.off ? e.off + ' + ' : '') + e.span + ' / 12';
        });
    }

    function render(colC) {
        var root = scopeOf(colC);
        var offC = root.querySelector('[data-td-grid][data-td-grid-kind="offset"]');
        var cols = readRow(colC);
        var offs = offC ? readRow(offC) : {};

        updateCssHint(root, classString(cols, offs));
        renderTracks(colC, effective(cols, offs));
    }

    function columnsWidgetFor(el) {
        return scopeOf(el).querySelector('[data-td-grid][data-td-grid-kind="columns"]');
    }

    // --- click / drag on the tracks ----------------------------------------

    var drag = null;

    function columnAt(track, clientX) {
        var rect = track.getBoundingClientRect();
        var col = Math.ceil((clientX - rect.left) / rect.width * 12);
        return Math.max(1, Math.min(12, col));
    }

    function targetSpan(track, clientX) {
        var off = parseInt(track.dataset.off || '0', 10);
        return Math.max(1, Math.min(12 - off, columnAt(track, clientX) - off));
    }

    function applyDrag(clientX) {
        setSelect(drag.colC, drag.bp, targetSpan(drag.track, clientX));
    }

    function showHover(track, clientX) {
        var off = parseInt(track.dataset.off || '0', 10);
        var span = targetSpan(track, clientX);
        var hover = track.querySelector('.w-gs__hover');
        if (!hover) {
            return;
        }
        hover.style.left = (off / 12 * 100) + '%';
        hover.style.width = (span / 12 * 100) + '%';
        hover.classList.add('is-active');
    }

    function clearHovers(except) {
        document.querySelectorAll('.w-gs__hover.is-active').forEach(function (h) {
            if (h.parentNode !== except) {
                h.classList.remove('is-active');
            }
        });
    }

    document.addEventListener('mousedown', function (e) {
        var track = e.target.closest && e.target.closest('.w-gs__track');
        if (!track || track.closest('.w-gs__brow').classList.contains('is-hidden')) {
            return;
        }
        drag = { track: track, bp: track.dataset.bp, colC: columnsWidgetFor(track) };
        applyDrag(e.clientX);
        e.preventDefault();
    });

    document.addEventListener('mousemove', function (e) {
        if (drag) {
            applyDrag(e.clientX);
        }
        var track = e.target.closest && e.target.closest('.w-gs__track');
        var live = track && !track.closest('.w-gs__brow').classList.contains('is-hidden');
        clearHovers(live ? track : null);
        if (live) {
            showHover(track, e.clientX);
        }
    });

    document.addEventListener('mouseup', function () {
        drag = null;
    });

    // --- select change + (re)init ----------------------------------------

    document.addEventListener('change', function (e) {
        if (e.target.closest && e.target.closest('[data-td-grid]')) {
            var colC = columnsWidgetFor(e.target);
            if (colC) {
                render(colC);
            }
        }
    });

    function init() {
        document.querySelectorAll('[data-td-grid][data-td-grid-kind="columns"]').forEach(render);
    }

    if (document.readyState !== 'loading') {
        init();
    } else {
        document.addEventListener('DOMContentLoaded', init);
    }
    document.addEventListener('turbo:load', init);
    document.addEventListener('turbo:render', init);
    document.addEventListener('turbo:frame-load', init);

    var pending = false;
    new MutationObserver(function (mutations) {
        if (pending) {
            return;
        }
        for (var i = 0; i < mutations.length; i++) {
            for (var j = 0; j < mutations[i].addedNodes.length; j++) {
                var n = mutations[i].addedNodes[j];
                if (n.nodeType === 1 && ((n.matches && n.matches('[data-td-grid]')) || (n.querySelector && n.querySelector('[data-td-grid]')))) {
                    pending = true;
                    setTimeout(function () { pending = false; init(); }, 30);
                    return;
                }
            }
        }
    }).observe(document.documentElement, { childList: true, subtree: true });
})();
