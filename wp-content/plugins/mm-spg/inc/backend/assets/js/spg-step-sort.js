jQuery(function ($) {

    const $tbody = $('#the-list');
    if (!$tbody.length) return;

    // Attach phase + interest as data attributes
    $tbody.find('tr').each(function () {
        const $row = $(this);
        const phase = $row.find('.column-spg_phase').text()?.trim();
        const interest = $row.find('.column-spg_interest').text()?.trim();

        $row.attr('data-phase', phase || '');
        $row.attr('data-interest', interest || '');
    });

    let draggedPhase = null;
    let draggedInterest = null;

    $tbody.sortable({
        items: '> tr',
        axis: 'y',
        handle: '.spg-drag-handle',
        tolerance: 'pointer',

        start: function (e, ui) {
            draggedPhase = ui.item.data('phase');
            draggedInterest = ui.item.data('interest');
        },

        sort: function (e, ui) {
            const $prev = ui.placeholder.prev();
            const $next = ui.placeholder.next();

            if (
                ($prev.length && $prev.data('phase') !== draggedPhase) ||
                ($next.length && $next.data('phase') !== draggedPhase)
            ) {
                ui.placeholder.hide();
            } else if (
                draggedPhase === '3' &&
                (
                    ($prev.length && $prev.data('interest') !== draggedInterest) ||
                    ($next.length && $next.data('interest') !== draggedInterest)
                )
            ) {
                ui.placeholder.hide();
            } else {
                ui.placeholder.show();
            }
        },

        update: function () {
            const order = [];

            $tbody.find('tr').each(function (index) {
                const id = $(this).attr('id');
                if (!id) return;

                order.push({
                    id: id.replace('post-', ''),
                    position: index
                });
            });

            $.post(ajaxurl, {
                action: 'spg_save_step_order',
                nonce: SPG_STEP_SORT.nonce,
                order: order
            });
        }
    });

    $tbody.disableSelection();
});
