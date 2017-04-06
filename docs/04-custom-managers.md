# Custom Managers – Tags Bundle

1. [Installation](01-installation.md)
2. [Configuration](02-config.md)
3. [Backend interface](03-backend.md)
4. [**Custom managers**](04-custom-managers.md)


## Creating custom managers

The tags manager is a simple service that works as a repository for tags. You can simple set up a new one by
creating a class that implements the `Codefog\TagsBundle\Manager\ManagerInterface`. 

### DCA field update

If you would like to update every field that implements your manager, make sure that your class implements the
`Codefog\TagsBundle\Manager\DcaAwareInterface` interface. Once that's done the built-in event listener
will trigger the interface methods each time the data container is loaded. 
