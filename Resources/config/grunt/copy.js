module.exports = function (grunt, options) {
    return {
        commerce_less: { // For watch:commerce_less
            expand: true,
            cwd: 'src/Ekyna/Bundle/CommerceBundle/Resources/public/tmp/css',
            src: ['**'],
            dest: 'src/Ekyna/Bundle/CommerceBundle/Resources/public/css'
        },
        commerce_ts: { // For watch:commerce_ts
            expand: true,
            cwd: 'src/Ekyna/Bundle/CommerceBundle/Resources/public/tmp/js',
            src: ['**/*.js'],
            dest: 'src/Ekyna/Bundle/CommerceBundle/Resources/public/js'
        },
        commerce_js: { // For watch:commerce_js
            expand: true,
            cwd: 'src/Ekyna/Bundle/CommerceBundle/Resources/private/js',
            src: ['**/*.js'],
            dest: 'src/Ekyna/Bundle/CommerceBundle/Resources/public/js'
        }
    }
};
