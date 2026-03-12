import './tags-widget.scss';

import {Application, Controller} from '@hotwired/stimulus';
import TomSelect from 'tom-select';

const application = Application.start();
application.debug = process.env.NODE_ENV === 'development';
application.register('codefog--tags-widget', class extends Controller {
    static targets = ['input']

    static values = {
        config: {
            type: Object,
            default: {},
        }
    }

    connect() {
        this.tomSelect = new TomSelect(this.inputTarget, this.#getOptions())

        // TODO: remove items on click
    }

    disconnect() {
        this.tomSelect.destroy()
    }

    select(event) {
        this.tomSelect.addItem(event.target.value)
    }

    #getOptions() {
        let config = {};

        try {
            config = JSON.parse(this.configValue);
        } catch {
            console.error(`Could not parse JSON options for Tags widget: ${this.configValue}`);

            return {};
        }

        const options = {
            delimiter: ',',
            options: config.allTags,
            items: config.valueTags,
            persist: false,
            render: {
                option_create: (data, escape) => `<div class="create">${config.addLabel} <strong>${escape(data.input)}</strong>&hellip;</div>`,
            }
        };

        if (config.allowCreate) {
            options.create = input => ({ value: input, text: input });
        }

        if (config.maxItems) {
            options.maxItems = config.maxItems;
        }

        if (config.sortable) {
            options.plugins = ['drag_drop'];
        }

        return options;
    }
});
