# Managers – Tags Bundle

1. [Installation](01-installation.md)
2. [Configuration](02-config.md)
3. [Usage](03-usage.md)
4. [**Managers**](04-managers.md)
5. [Backend interface](05-backend.md)
6. [Insert tags](06-insert-tags.md)


## Role of the managers

The tag managers provide necessary features and/or services for fetching and managing the tags. Behind the scenes
they are also responsible for handling the backend widgets, the DCA fields and the insert tags.

Currently available managers:

1. **Default manager**

The default tags manager stores the tags in the `tl_cfg_tag` table and uses Contao models with accompaniment 
of [codefog/contao-haste](https://github.com/codefog/contao-haste) behind the scenes.

## Creating managers

In order to provide your own tags manager you have to implement the `Codefog\TagsBundle\Manager\ManagerInterface` interface.
A good example is the default manager which can be found in `Codefog\TagsBundle\Manager\DefaultManager`.

Once you define a custom tags manager service, you can reference it in the configuration as shown below:

```yaml
# services.yml
app.page_tags_manager:
    class: App\PageTagsManager
    arguments:
        - '@app.my_service'

# config/config.yml
codefog_tags:
    managers:
        page:
            source: 'tl_page.app_tags'
            service: 'app.page_tags_manager'
```

### DCA field update

If you would like to handle every DCA field that uses your manager, make sure that your class implements the
`Codefog\TagsBundle\Manager\DcaAwareInterface` interface. Once that's done the built-in event listener
will trigger the interface methods each time the data container is loaded. 

### Insert tags

To make your manager support the insert tags replacement, make sure to implement the 
`Codefog\TagsBundle\Manager\InsertTagsAwareInterface` interface. The interface methods will be called whenever
the insert tag referencing your manager is to be replaced, e.g. `{{tag::my_manager::tag_value::name}}`.
