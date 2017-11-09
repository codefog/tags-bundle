# Insert Tags – Tags Bundle

1. [Installation](01-installation.md)
2. [Configuration](02-config.md)
3. [Backend interface](03-backend.md)
4. [Custom managers](04-custom-managers.md)
5. [**Insert tags**](05-insert-tags.md)


## Supported insert tags

You can display the tag name and all of its properties using the `{{tag::$source::$value::$property}}` insert tag. 

Example:

```
{{tag::app_tags::123::name}} –> Foo Bar
{{tag::app_tags::123::alias}} –> foo-bar
```
