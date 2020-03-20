# Usage – Tags Bundle

1. [Installation](01-installation.md)
2. [Configuration](02-config.md)
3. [**Usage**](03-usage.md)
4. [Managers](04-managers.md)
5. [Backend interface](05-backend.md)
6. [Insert tags](06-insert-tags.md)

## Usage

The defined managers can be injected like any other services into your classes:

```yaml
# services.yml
services:
    App\MyService:
        arguments:
            - '@codefog_tags.manager.my_manager'
```

Then simply assign it to the class property and you are good to go: 

```php
<?php

namespace App;

use Codefog\TagsBundle\Manager\DefaultManager;

class MyService
{
    private $tagsManager;

    public function __construct(DefaultManager $tagsManager)
    {
        $this->tagsManager = $tagsManager;    
    }
}
```

### Finding the source records

The source records can be obtained using the `SourceFinder` service obtained from the tag manager. The default service 
does not know what kind of records you are expecting, so by default it always returns the source record IDs.

```php
use Codefog\TagsBundle\Tag;

// Set some dummy criteria
$criteria = $this->tagsManager->createSourceCriteria()->setTag(new Tag('foo', 'bar'));

// Get total number of records
$this->tagsManager->getSourceFinder()->count($criteria);

// Find the source record IDs
$this->tagsManager->getSourceFinder()->findMultiple($criteria);

// Find the source record IDs related to IDs set in criteria 
$this->tagsManager->getSourceFinder()->findRelatedSourceRecords($criteria->setIds([1, 2, 3]));
``` 

### Finding the tag records

The tags can be obtained using the `TagFinder` service obtained from the tag manager.

```php
use Codefog\TagsBundle\Tag;

// Set some dummy criteria
$criteria = $this->tagsManager->createTagCriteria()->setSourceIds([1, 2, 3]);

// Get total number of records
$this->tagsManager->getTagFinder()->count($criteria);

// Find multiple tags
$this->tagsManager->getTagFinder()->findMultiple($criteria);

// Find the top 10 tag IDs 
$this->tagsManager->getTagFinder()->getTopTagIds($criteria, 10, true);

// Find a single tag
$this->tagsManager->getTagFinder()->findSingle($criteria->setValue('foo'));
``` 
