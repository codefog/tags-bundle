(function ($, window, document, undefined) {
    'use strict';

    // Plugin name
    var pluginName = 'cfgTags';

    // Defaults
    var defaults = {
        addLabel: 'Add',
        allowCreate: true,
        delimiter: ',',
        maxItems: null,
        selectizeConfig: {}
    };

    /**
     * Plugin constructor
     *
     * @param element
     * @param allTags
     * @param valueTags
     * @param options
     * @constructor
     */
    function Plugin(element, allTags, valueTags, options) {
        this.element = $(element);
        this.settings = $.extend({}, defaults, options);
        this.allTags = allTags;
        this.valueTags = valueTags;
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
        bindEvents: function () {
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
        initSelectize: function (el) {
            var options = {
                delimiter: this.settings.delimiter,
                options: this.allTags,
                items: this.valueTags,
                persist: false,
                render: {
                    option_create: function(data, escape) {
                        return '<div class="create">' + this.settings.addLabel + ' <strong>' + escape(data.input) + '</strong>&hellip;</div>';
                    }.bind(this)
                }
            };

            // Allow to create the tags
            if (this.settings.allowCreate) {
                options.create = function (input) {
                    return {
                        value: input,
                        text: input
                    }
                };
            }

            // Set the maximum number of items
            if (typeof this.settings.maxItems === 'number') {
                options.maxItems = this.settings.maxItems;
            }

            // Make the tags sortable
            if (this.settings.sortable) {
                options.plugins = ['drag_drop'];
            }

            el.selectize($.extend(options, this.settings.selectizeConfig));

            this.selectize = el[0].selectize;
        }
    });

    // Plugin wrapper around the constructor preventing against multiple instantiations
    $.fn[pluginName] = function (allTags, valueTags, options) {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + pluginName, new Plugin(this, allTags, valueTags, options));
            }
        });
    };
})(jQuery, window, document);
