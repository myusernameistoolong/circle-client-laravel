const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */


mix.webpackConfig({ resolve: { fallback: {
    crypto: require.resolve("crypto-browserify"),
    stream: require.resolve("stream-browserify")
    }, }, })

mix.js('resources/js/socket/rsaIntegrityHandler.js', 'public/js/socket')
mix.js('resources/js/socket/socketConnection.js', 'public/js/socket')
mix.js('resources/js/loginAuth.js', 'public/js')
mix.js('resources/js/script.js', 'public/js')
mix.js('resources/js/thumbnail.js', 'public/js')
mix.js('resources/js/app.js', 'public/js')
    .vue()
    .sass('resources/sass/app.scss', 'public/css');
