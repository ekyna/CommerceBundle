module.exports = {
    'build:commerce_css': [
        //'less:commerce',
        'cssmin:commerce_css'
        //'clean:commerce_less'
    ],
    //'build:commerce_js': [
    //    'ts:commerce',
    //    'uglify:commerce_ts',
    //    'uglify:commerce_js',
    //    'clean:commerce_ts'
    //],
    'build:commerce': [
        'clean:commerce_pre',
        //'copy:commerce_img',
        'build:commerce_css',
        //'build:commerce_js',
        //'clean:commerce_post'
    ]
};
