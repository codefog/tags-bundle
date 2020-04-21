# Insert Tags – Tags Bundle

1. [Installation](01-installation.md)
2. [Configuration](02-config.md)
3. [Usage](03-usage.md)
4. [Managers](04-managers.md)
5. [Backend interface](05-backend.md)
6. [**Insert tags**](06-insert-tags.md)

## Supported insert tags

You can display the tag name and all of its properties using the `{{tag::$manager::$value::$property}}` insert tag. 

Example:

```
{{tag::my_manager::123::name}} –> Foo Bar
{{tag::my_manager::123::alias}} –> foo-bar
{{tag::my_manager::123::foobar}} –> anything specified in tag's data
```
