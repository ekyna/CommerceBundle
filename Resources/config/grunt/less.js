module.exports = function (grunt, options) {
    // @see https://github.com/gruntjs/grunt-contrib-less
    return {
        commerce: {
            files: {
                'src/Ekyna/Bundle/CommerceBundle/Resources/public/tmp/css/checkout.css':
                    'src/Ekyna/Bundle/CommerceBundle/Resources/private/less/checkout.less',
                'src/Ekyna/Bundle/CommerceBundle/Resources/public/tmp/css/document.css':
                    'src/Ekyna/Bundle/CommerceBundle/Resources/private/less/document.less',
                'src/Ekyna/Bundle/CommerceBundle/Resources/public/tmp/css/shipment-form.css':
                    'src/Ekyna/Bundle/CommerceBundle/Resources/private/less/shipment-form.less',
                'src/Ekyna/Bundle/CommerceBundle/Resources/public/tmp/css/form.css':
                    'src/Ekyna/Bundle/CommerceBundle/Resources/private/less/form.less',
                'src/Ekyna/Bundle/CommerceBundle/Resources/public/tmp/css/sale-view.css':
                    'src/Ekyna/Bundle/CommerceBundle/Resources/private/less/sale-view.less',
                'src/Ekyna/Bundle/CommerceBundle/Resources/public/tmp/css/admin-dashboard.css':
                    'src/Ekyna/Bundle/CommerceBundle/Resources/private/less/admin-dashboard.less',
                'src/Ekyna/Bundle/CommerceBundle/Resources/public/tmp/css/support.css':
                    'src/Ekyna/Bundle/CommerceBundle/Resources/private/less/support.less'
            }
        }
    }
};
