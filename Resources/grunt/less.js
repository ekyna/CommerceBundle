module.exports = function (grunt, options) {
    // @see https://github.com/gruntjs/grunt-contrib-less
    return {
        commerce: {
            files: {
                'src/Ekyna/Bundle/CommerceBundle/Resources/public/tmp/css/editor.css':
                    'src/Ekyna/Bundle/CommerceBundle/Resources/private/less/editor.less',
                'src/Ekyna/Bundle/CommerceBundle/Resources/public/tmp/css/editor-document.css':
                    'src/Ekyna/Bundle/CommerceBundle/Resources/private/less/editor-document.less',
                'src/Ekyna/Bundle/CommerceBundle/Resources/public/tmp/css/bootstrap-content.css':
                    'src/Ekyna/Bundle/CommerceBundle/Resources/private/less/bootstrap-content.less'
            }
        }
    }
};
