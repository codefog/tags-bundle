const Encore = require('@terminal42/contao-build-tools');

module.exports = Encore()
    .setOutputPath('public/')
    .setPublicPath('/bundles/codefogtags')
    .addEntry('dcawizard', './assets/dcawizard.js')
    .getWebpackConfig()
;
o
