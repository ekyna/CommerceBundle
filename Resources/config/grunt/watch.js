module.exports = function (grunt, options) {
    return {
        commerce_css: {
            files: ['src/Ekyna/Bundle/CommerceBundle/Resources/private/css/**/*.css'],
            tasks: ['cssmin:commerce_css', 'copy:commerce_css_web'],
            options: {
                spawn: false
            }
        },
        commerce_less: {
            files: ['src/Ekyna/Bundle/CommerceBundle/Resources/private/less/**/*.less'],
            tasks: ['less:commerce', 'copy:commerce_less', 'clean:commerce_less', 'copy:commerce_css_web'],
            options: {
                spawn: false
            }
        },
        commerce_js: {
            files: ['src/Ekyna/Bundle/CommerceBundle/Resources/private/js/**/*.js'],
            tasks: ['copy:commerce_js', 'copy:commerce_js_web'],
            options: {
                spawn: false
            }
        },
        commerce_ts: {
            files: ['src/Ekyna/Bundle/CommerceBundle/Resources/private/ts/**/*.ts'],
            tasks: ['ts:commerce', 'copy:commerce_ts', 'clean:commerce_ts', 'copy:commerce_js_web'],
            options: {
                spawn: false
            }
        }
    }
};
