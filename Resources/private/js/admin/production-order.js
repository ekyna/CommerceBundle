define(
    ['jquery'],
    function ($) {
        "use strict";

        $(document).on('click', '#production-item-list a.toggle-all-assignments', function (e) {
            e.stopPropagation();
            e.preventDefault();

            let $link = $(e.currentTarget),
                $view = $link.closest('#production-item-list'),
                $rows = $view.find('tr.stock-assignments'),
                hidden = $rows.filter(':not(:visible)').length,
                visible = $rows.filter(':visible').length;

            if (hidden > visible) {
                $rows.show();
            } else {
                $rows.hide();
            }

            return false;
        });
    }
);
