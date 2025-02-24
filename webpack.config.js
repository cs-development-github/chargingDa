const Encore = require('@symfony/webpack-encore');
const BrowserSyncPlugin = require('browser-sync-webpack-plugin');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')

    .addEntry('app', './assets/app.js')
    .addEntry('carousel', './assets/carousel.js')
    .addEntry('landing', './assets/landing.js')
    .enableSingleRuntimeChunk()

    // Activer Sass/SCSS pour Bootstrap
    .enableSassLoader()

    .copyFiles({
        from: './assets/images',
        to: 'images/[path][name].[ext]'
    })

    // Activer les sources maps en dev
    .enableSourceMaps(!Encore.isProduction())

    // Activer la versioning (cache busting)
    .enableVersioning(Encore.isProduction())

    // Activer BrowserSync pour rafraîchir la page automatiquement
    .addPlugin(new BrowserSyncPlugin({
        host: 'localhost',
        port: 3000,
        proxy: 'http://127.0.0.1:8000',
        files: [
            'templates/**/*.twig',
            'assets/**/*.scss',
            'assets/**/*.js',
        ],
        notify: false,
        open: false
    }, { reload: true }))
;

// Exporter la config Webpack
module.exports = Encore.getWebpackConfig();