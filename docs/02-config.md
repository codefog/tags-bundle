# Configuration – Tags Bundle

1. [Installation](01-installation.md)
2. [**Configuration**](02-config.md)
3. [Backend interface](03-backend.md)
4. [Custom managers](04-custom-managers.md)


## Add the service

First of all you need to add your own service. By default you can use the default manager which stores
the tags in the `tl_cfg_tag` table and uses Contao models behind the scenes.

Note that for any manager you register you have to explicitly specify the service tags!

```yml
services:
    app.article_tags_manager:
        class: Codefog\TagsBundle\Manager\DefaultManager
        arguments:
            - "@contao.framework"
            - "tl_app_article"
            - "tags"
        tags:
            - { name: codefog_tags.manager, alias: app.article }
```


## Adjust the DCA files

Once the service is ready to use you can create a new field in the DCA and register it there. Make
sure that you register it with the *alias* of the service and not the service *name*!

```php
// dca/tl_app_article.php
'tags' => [
    'label'     => &$GLOBALS['TL_LANG']['tl_app_article']['tags'],
    'exclude'   => true,
    'inputType' => 'cfgTags',
    'eval'      => [
        'tagsManager' => 'app.article', // Manager, required
        'tagsCreate'  => false, // Allow to create tags, optional (true by default)
        'maxItems' => 5', // Maximum number of tags allowed
        'hideList' => true, // Hide the list of tags; the input field will be still visible
        'tl_class'    => 'clr'
    ],
],
````

Do not forget to set the source label for the tags backend module: 

```php
// languages/en/tl_cfg_tags.php
$GLOBALS['TL_LANG']['tl_cfg_tag']['sourceRef']['app.article'] = 'Article';
```


## Update the database (optional)

Each manager takes care of loading and saving the data from the widget itself. The default manager
internally uses `Haste-ManyToMany` field relation to store the data, so you need to update the datbaase
before using it.


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
