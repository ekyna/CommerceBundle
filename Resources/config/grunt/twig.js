module.exports = function (grunt, options) {
    return {
        commerce: {
            options: {
                amd_wrapper: true,
                amd_define: 'ekyna-commerce/templates',
                variable: 'templates',
                template_key: function(path) {
                    var split = path.split('/');
                    return split[split.length-1];
                }
            },
            files: {
                'src/Ekyna/Bundle/CommerceBundle/Resources/public/js/templates.js': [
                    'src/Ekyna/Bundle/CommerceBundle/Resources/views/Js/widget.html.twig',
                    'src/Ekyna/Bundle/CommerceBundle/Resources/views/Js/stock_unit_rows.html.twig',
                    'src/Ekyna/Bundle/CommerceBundle/Resources/views/Js/relay_point.html.twig',
                    'src/Ekyna/Bundle/CommerceBundle/Resources/views/Js/pick_relay_point.html.twig',
                    'src/Ekyna/Bundle/CommerceBundle/Resources/views/Js/relay_point_list.html.twig'
                ]
            }
        }
    }
};
