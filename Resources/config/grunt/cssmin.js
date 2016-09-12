module.exports = function (grunt, options) {
    return {
        commerce_css: {
            files: [
                {
                    expand: true,
                    cwd: 'src/Ekyna/Bundle/CommerceBundle/Resources/private/css',
                    src: ['*.css'],
                    dest: 'src/Ekyna/Bundle/CommerceBundle/Resources/public/css',
                    ext: '.css'
                }
            ]
        },
        commerce_less: {
            files: [
                {
                    expand: true,
                    cwd: 'src/Ekyna/Bundle/CommerceBundle/Resources/public/tmp/css',
                    src: ['*.css'],
                    dest: 'src/Ekyna/Bundle/CommerceBundle/Resources/public/css',
                    ext: '.css'
                }
            ]
        }
    }
};
