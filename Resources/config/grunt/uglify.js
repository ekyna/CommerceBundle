module.exports = function (grunt, options) {
    return {
        commerce_ts: {
            files: [{
                expand: true,
                cwd: 'src/Ekyna/Bundle/CommerceBundle/Resources/public/tmp/js',
                src: '**/*.js',
                dest: 'src/Ekyna/Bundle/CommerceBundle/Resources/public/js'
            }]
        },
        commerce_js: {
            files: [{
                expand: true,
                cwd: 'src/Ekyna/Bundle/CommerceBundle/Resources/private/js',
                src: ['*.js', '**/*.js'],
                dest: 'src/Ekyna/Bundle/CommerceBundle/Resources/public/js'
            }]
        }
    }
};
