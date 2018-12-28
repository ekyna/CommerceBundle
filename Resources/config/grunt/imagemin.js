module.exports = function (grunt, options) {
    return {
        commerce: {
            options: {
                optimizationLevel: 6
            },
            files: [{
                expand: true,
                cwd: 'src/Ekyna/Bundle/CommerceBundle/Resources/private/img/',
                src: ['**/*.{png,jpg,gif,svg}'],
                dest: 'src/Ekyna/Bundle/CommerceBundle/Resources/public/img/'
            }]
        }
    }
};