import './tags-widget.scss';

import { Application, Controller } from '@hotwired/stimulus';
import TomSelect from 'tom-select';

const application = Application.start();
application.debug = process.env.NODE_ENV === 'development';
application.register(
    'codefog--tags-widget',
    class extends Controller {
        static targets = ['input'];

        static values = {
            config: {
                type: Object,
                default: {},
            },
        };

        connect() {
            this.tomSelect = new TomSelect(this.inputTarget, this.#getOptions());
        }

        disconnect() {
            this.tomSelect.destroy();
        }

        add(event) {
            this.tomSelect.addItem(event.target.value);
        }

        remove(event) {
            this.tomSelect.removeItem(event.target.value);
        }

        #getOptions() {
            const config = this.configValue;

            const options = {
                delimiter: ',',
                options: config.allTags,
                items: config.valueTags,
                persist: false,
                render: {
                    // biome-ignore format: long line
                    option_create: (data, escape) => `<div class="create">${config.addLabel} <strong>${escape(data.input)}</strong>&hellip;</div>`,
                    // biome-ignore format: long line
                    item: (data, escape) => `<div>${escape(data.text)}<button type="button" class="cfg-tags-widget__remove" value="${data.value}" aria-label="${config.removeLabel} ${escape(data.text)}" data-action="click->codefog--tags-widget#remove:prevent">${config.removeLabel}</button></div>`,
                    no_results: () => `<div class="no-results">${config.noResultsLabel}</div>`,
                },
            };

            if (config.allowCreate) {
                options.create = (input) => ({ value: input, text: input });
            }

            if (config.maxItems) {
                options.maxItems = config.maxItems;
            }

            if (config.sortable) {
                options.plugins = ['drag_drop'];
            }

            return options;
        }
    },
);
