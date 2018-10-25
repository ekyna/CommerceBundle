define(['jquery', 'routing', 'tinymce'], function ($, router) {
    "use strict";

    if (typeof tinymce === 'undefined') {
        throw 'Tinymce is not available.';
    }

    /**
     * Supplier order template widget
     */
    $.fn.supplierOrderTemplateWidget = function () {

        this.each(function () {
            var $this = $(this),
                $form = $this.closest('form'),
                $template = $this.find('.template-choice'),
                $locale = $this.find('.locale-choice'),
                $subject = $form.find('.notify-subject'),
                $message = $form.find('.notify-message');

            $this.on('change', 'select', function () {
                $.getJSON(
                    router.generate('ekyna_commerce_supplier_order_admin_template', {
                        'supplierOrderId': $this.data('order-id'),
                        'name': $template.val(),
                        '_locale': $locale.val()
                    }),
                    function (data) {
                        if (data.hasOwnProperty('subject')) {
                            $subject.val(data.subject);
                        }
                        if (data.hasOwnProperty('message')) {
                            var editor, id = $message.attr('id');
                            editor = tinymce.get(id);
                            if (editor) {
                                editor.setContent(data.message);
                            } else {
                                $message.val(data.message);
                            }
                        }
                    }
                );
            });

            return this;
        });
    };

    return {
        init: function ($element) {
            $element.supplierOrderTemplateWidget();
        }
    }
});
