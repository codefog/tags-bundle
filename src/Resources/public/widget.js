(function ($, window, document, undefined) {
    'use strict';

    // Plugin name
    var pluginName = 'cfgTags';

    // Defaults
    var defaults = {
        delimiter: ','
    };

    /**
     * Plugin constructor
     *
     * @param element
     * @param options
     * @constructor
     */
    function Plugin(element, options) {
        this.element = $(element);
        this.settings = $.extend({}, defaults, options);
        this.init();
    }

    // Avoid Plugin.prototype conflicts
    $.extend(Plugin.prototype, {
        /**
         * Initialize the plugin
         */
        init: function () {
            this.initSelectize(this.element.find('[data-cfg-tags-input]').eq(0));
            this.bindEvents();
        },

        /**
         * Bind the events
         */
        bindEvents: function() {
            // Add the tags to Selectize on click
            this.element.find('[data-cfg-tags-tag]').on('click', function (e) {
                this.selectize.addItem($(e.currentTarget).data('cfg-tags-tag'));
            }.bind(this));

            // Remove the Selectize tags on click
            this.element.on('click', '.selectize-input > div', function (e) {
                this.selectize.removeItem($(e.currentTarget).data('value'));
                this.selectize.refreshOptions();
            }.bind(this));
        },

        /**
         * Initialize the Selectize
         * @param {Element} el
         */
        initSelectize: function(el) {
            el.selectize({
                delimiter: this.settings.delimiter,
                options: this.parseJson(this.element.find('[data-cfg-tags-all]').eq(0)),
                items: this.parseJson(this.element.find('[data-cfg-tags-value]').eq(0)),
                persist: false,
                create: function (input) {
                    return {
                        value: input,
                        text: input
                    }
                }
            });

            this.selectize = el[0].selectize;
        },

        /**
         * Parse the JSON data
         * @param {Element} el
         * @return {Array}
         */
        parseJson: function (el) {
            var data = [];

            try {
                data = JSON.parse(el.text());
            } catch (err) {
                return [];
            }

            return data;
        }
    });

    // Plugin wrapper around the constructor preventing against multiple instantiations
    $.fn[pluginName] = function (options) {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + pluginName, new Plugin(this, options));
            }
        });
    };
})(jQuery, window, document);
