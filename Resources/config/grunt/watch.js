module.exports = function (grunt, options) {
    return {
        commerce_less: {
            files: ['src/Ekyna/Bundle/CommerceBundle/Resources/private/less/**/*.less'],
            tasks: ['less:commerce', 'copy:commerce_less', 'clean:commerce_less'],
            options: {
                spawn: false
            }
        },
        commerce_js: {
            files: ['src/Ekyna/Bundle/CommerceBundle/Resources/private/js/**/*.js'],
            tasks: ['copy:commerce_js'],
            options: {
                spawn: false
            }
        },
        commerce_ts: {
            files: ['src/Ekyna/Bundle/CommerceBundle/Resources/private/ts/**/*.ts'],
            tasks: ['ts:commerce', 'copy:commerce_ts', 'clean:commerce_ts'],
            options: {
                spawn: false
            }
        },
        commerce_twig: {
            files: ['src/Ekyna/Bundle/CommerceBundle/Resources/views/**/*.twig'],
            tasks: ['twig:commerce'],
            options: {
                spawn: false
            }
        }
    }
};
