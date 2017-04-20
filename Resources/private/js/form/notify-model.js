define(['jquery', 'routing', 'tinymce'], function ($, router) {
    "use strict";

    if (typeof tinymce === 'undefined') {
        throw 'Tinymce is not available.';
    }


    /**
     * Notify model choice widget
     */
    $.fn.notifyModelChoiceWidget = function () {

        this.each(function () {
            var $this = $(this),
                $form = $this.closest('form'),
                $model = $this.find('.model-choice'),
                $locale = $this.find('.locale-choice'),
                $subject = $form.find('.notify-subject'),
                $message = $form.find('.notify-message'),
                type = $this.data('sale-type');

            var editor, id = $message.attr('id');

            $this.on('change', 'select', function () {
                var model = $model.val();
                if (!model) {
                    $model.parent().addClass('has-warning');
                    return;
                }

                $model.parent().removeClass('has-warning');

                var parameters = {
                    'id': model,
                    '_locale': $locale.val()
                };
                parameters[type + 'Id'] = $this.data('sale-id');

                $.getJSON(
                    router.generate('admin_ekyna_commerce_' + type + '_notify_model', parameters),
                    function (data) {
                        if (data.hasOwnProperty('subject')) {
                            $subject.val(data.subject);
                        }
                        if (data.hasOwnProperty('message')) {
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
            $element.notifyModelChoiceWidget();
        }
    }
});
