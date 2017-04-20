module.exports = function (grunt, options) {
    return {
        commerce: {
            options: {
                amd_wrapper: true,
                amd_define: 'ekyna-commerce/templates',
                variable: 'templates',
                each_template: '{{ variable }}["{{ filepath }}"] = Twig.twig({ allowInlineIncludes: true, id: "{{ filepath }}", data: {{ compiled }} });',
                template_key: function(path) {
                    var split = path.split('/');
                    return '@EkynaCommerce/Js/' + split[split.length-1];
                }
            },
            files: {
                'src/Ekyna/Bundle/CommerceBundle/Resources/public/js/templates.js': [
                    'src/Ekyna/Bundle/CommerceBundle/Resources/views/Js/customer_balance_rows.html.twig',
                    'src/Ekyna/Bundle/CommerceBundle/Resources/views/Js/pick_relay_point.html.twig',
                    'src/Ekyna/Bundle/CommerceBundle/Resources/views/Js/relay_point.html.twig',
                    'src/Ekyna/Bundle/CommerceBundle/Resources/views/Js/relay_point_list.html.twig',
                    'src/Ekyna/Bundle/CommerceBundle/Resources/views/Js/stock_unit_rows.html.twig',
                    'src/Ekyna/Bundle/CommerceBundle/Resources/views/Js/ticket.html.twig',
                    'src/Ekyna/Bundle/CommerceBundle/Resources/views/Js/ticket_attachment.html.twig',
                    'src/Ekyna/Bundle/CommerceBundle/Resources/views/Js/ticket_body.html.twig',
                    'src/Ekyna/Bundle/CommerceBundle/Resources/views/Js/ticket_footer.html.twig',
                    'src/Ekyna/Bundle/CommerceBundle/Resources/views/Js/ticket_header.html.twig',
                    'src/Ekyna/Bundle/CommerceBundle/Resources/views/Js/ticket_message.html.twig',
                    'src/Ekyna/Bundle/CommerceBundle/Resources/views/Js/widget.html.twig'
                ]
            }
        }
    }
};
