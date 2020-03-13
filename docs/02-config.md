# Configuration – Tags Bundle

1. [Installation](01-installation.md)
2. [**Configuration**](02-config.md)
3. [Backend interface](03-backend.md)
4. [Managers](04-managers.md)
5. [Insert tags](05-insert-tags.md)

## Configure the managers

In the first place you have to configure the available managers. This can be done in your app configuration as follows:

```yml
# config/config.yml
codefog_tags:
    managers:
        my_manager:
            table: 'tl_table'
            field: 'tags'
            service: '' # optional, manager service to use (defaults to "codefog_tags.default_manager")
            alias: '' # optional, alias of the newly created service
```

Afterwards your manager will be available as `codefog_tags.manager.my_manager` public service. 

You can read more about available manager services [here](04-managers.md).  

## Adjust the DCA files

Once the manager is registered, you can create a new field in the desired DCA table as follows:

```php
// dca/tl_table.php
'tags' => [
    'exclude' => true,
    'inputType' => 'cfgTags',
    'eval' => [
        'tagsManager' => 'my_manager', // Manager name, required
        'tagsCreate'  => false, // Allow to create tags, optional (true by default)
        'maxItems' => 5, // Maximum number of tags allowed
        'hideList' => true, // Hide the list of tags; the input field will be still visible
        'tl_class' => 'clr'
    ],
],
````

The last step is to set the source label for the tags backend module:

```php
// languages/en/tl_cfg_tag.php
$GLOBALS['TL_LANG']['tl_cfg_tag']['sourceRef']['my_manager'] = 'Table tags';
```

## Update the database

Each manager takes care of loading and saving the data from the widget itself. The default manager internally uses 
`Haste-ManyToMany` field relation to store the data, so you need to update the database before using it.

You can read more about available manager services [here](04-managers.md).  

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
