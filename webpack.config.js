const Encore = require('@terminal42/contao-build-tools');

module.exports = Encore()
    .setOutputPath('public/')
    .setPublicPath('/bundles/codefogtags')
    .addEntry('tags-widget', './assets/tags-widget.js')
    .getWebpackConfig()
;
