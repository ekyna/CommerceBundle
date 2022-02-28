define(['jquery', 'select2'], function($) {
    "use strict";

    /**
     * Supplier product widget
     */
    $.fn.supplierProductWidget = function() {

        this.each(function() {
            const $this = $(this),
                  $designation = $this.find('input[name="supplier_product[designation]"]');

            if ('' !== $designation.val()) {
                return;
            }

            const $subject = $this.find('.commerce-subject-choice select.subject');

            $subject.on('select2:select select2:unselect select2:clear', () => {
                let data = $subject.select2('data');

                $designation.val('');

                if (1 === data.length) {
                    $designation.val(data[0].text);
                }
            });
        });

        return this;
    };

    return {
        init: function($element) {
            $element.supplierProductWidget();
        }
    };
});
