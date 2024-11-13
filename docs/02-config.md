# Configuration – Tags Bundle

1. [Installation](01-installation.md)
2. [**Configuration**](02-config.md)
3. [Usage](03-usage.md)
4. [Managers](04-managers.md)
5. [Backend interface](05-backend.md)
6. [Insert tags](06-insert-tags.md)

## Basic configuration 

The steps below describe the basic configuration that is required to set up the tags feature in your project.

### Configure the managers

In the first place you have to configure the available managers. This can be done in your app configuration as follows:

```yml
# config/config.yml
codefog_tags:
    managers:
        my_manager:
            source: 'tl_table.tags' # in format <table>.<field>, or an array of such
            service: '' # optional, manager service to use (defaults to "codefog_tags.default_manager")
            locale: '' # optional, locale to use for alias generation (defaults to "en")
            validChars: '' # optional, validChars to use for alias generation (defaults to "0-9a-z")
            alias: '' # optional, alias of the newly created service
```

Afterwards your manager will be available as `codefog_tags.manager.my_manager` public service. 

You can read more about available manager services [here](04-managers.md).  

### Adjust the DCA files

Once the manager is registered, you can create a new field in the desired DCA table as follows:

```php
// dca/tl_table.php
'tags' => [
    'exclude' => true,
    'inputType' => 'cfgTags',
    'eval' => [
        'tagsManager' => 'my_manager', // Manager name, required
        'tagsCreate' => false, // Allow to create tags, optional (true by default)
        'tagsSource' => 'tl_table.tags', // Tag source, optional (defaults to current table and current field)
        'maxItems' => 5, // Maximum number of tags allowed
        'hideList' => true, // Hide the list of tags; the input field will be still visible
        'tl_class' => 'clr'
    ],
],
```

The last step is to set the source label for the tags backend module:

```php
// languages/en/tl_cfg_tag.php
$GLOBALS['TL_LANG']['tl_cfg_tag']['sourceRef']['my_manager'] = 'Table tags';
```

### Update the database

Each manager takes care of loading and saving the data from the widget itself. The default manager internally uses 
`Haste-ManyToMany` field relation to store the data, so you need to update the database before using it.

You can read more about available manager services [here](04-managers.md).  

## Use tags widget in content element / frontend module settings 

To provide a read-only tags widget that allows to select certain tags e.g. for filtering the source records in the output,
you can still use the default manager. There are a few important things you have to set though:    

```php
// dca/tl_content.php
$GLOBALS['TL_DCA']['tl_content']['fields']['app_tags'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['app_tags'],
    'exclude' => true,
    'inputType' => 'cfgTags',
    'eval' => [
        'tagsManager' => 'app',
        'tagsCreate' => false, # Do not create new tags
        'tagsSource' => 'tl_table.tags', # Set the source if you have multiple of them
        'tagsSortable' => true, # Make the tags sortable. Works only with the tag values saved directly in the field (see below).
        'tl_class' => 'clr',
    ],
    # Save the tag values directly in the field and do not use the Haste-ManyToMany relation
    'sql' => ['type' => 'blob', 'notnull' => false],
];
```

## Overriding Selectize.js settings

In case you would like to override the [Selectize.js settings](https://github.com/selectize/selectize.js/blob/master/docs/usage.md) 
directly you can always do that by passing the `selectizeConfig` property of the widget configuration, as shown on example:

```php
<script>
    $('#cfg-tags-<?= $this->id ?>').cfgTags(
        <?= json_encode($this->allTags) ?>, 
        <?= json_encode($this->valueTags) ?>, 
        $.extend(<?= json_encode($this->config) ?>, { 
            selectizeConfig: {
                hideSelected: true
            }
        })
    );
</script>
```
