module.exports = function (grunt, options) {
    return {
        commerce: {
            files: [
                {
                    src: 'src/Ekyna/Bundle/CommerceBundle/Resources/private/ts/**/*.ts',
                    dest: 'src/Ekyna/Bundle/CommerceBundle/Resources/public/tmp/js'
                }
            ],
            options: {
                fast: 'never',
                module: 'amd',
                rootDir: 'src/Ekyna/Bundle/CommerceBundle/Resources/private/ts',
                noImplicitAny: false,
                removeComments: true,
                preserveConstEnums: true,
                sourceMap: false
            }
        }
    }
};
