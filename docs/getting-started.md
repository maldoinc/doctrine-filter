## Getting started

In its basic form, you will annotate entity fields, and using an instance of DoctrineFilter you will apply 
on a Query Builder operations from the current request.
This will modify your Query Builder's where section to add the filters
and include the new parameters with their corresponding values
without removing any current where conditions or parameters.

### The `Expose` annotation

The first step is to mark the exposed entity fields with the provided `Expose` annotation.
This can only be applied to properties of entities and contains two parameters:

#### `serializedName`
This is the public name of your field. If using a serializer set this to whatever the serializer
is producing. E.g: a field `createdAt` is most likely serialized as `created_at`.

> If not provided this will use the field name as the value.

#### `operators`
By default, no operations can be executed on a field. You must choose what operations to allow on it,
by passing a list of strings, which are the operator names.
The preset filter provider contains a `ALL_PRESETS` constant which lists all the preset filters available.

> For DB performance reasons it is recommended that you only enable certain fields for filtering depending on the
> field and indexing.

### Creating `DoctrineFilter`

To create an instance of `DoctrineFilter` you need the following:

* A query builder with an entity in it (the resource you're filtering)
* An instance of a `FilterReaderInterface` such as the provided `ExposedFieldsReader`
    * If you are using the provided reader you need to annotate your entities with the `Expose` annotation.
    * Otherwise, feel free to provide any implementation such as reading the data from a YAML or XML file.
* A list of providers, which are the ones that provide the actual filtering capabilities.
    * Use the default `Maldoinc\Doctrine\Filter\Provider\PresetFilters` filters
    * Create custom filter classes and include them during instantiation to make any new filters available.

```php
use Maldoinc\Doctrine\Filter\Action\ActionList;
use Maldoinc\Doctrine\Filter\DoctrineFilter;
use Maldoinc\Doctrine\Filter\Provider\PresetFilterProvider;
use Maldoinc\Doctrine\Filter\Reader\AttributeReader\NativeAttributeReader;
use Maldoinc\Doctrine\Filter\Reader\ExposedFieldsReader;

$queryBuilder = $doctrine->getRepository(Book::class)->createQueryBuilder('b');

// ExposedFieldsReader is responsible from extracting any annotated fields 
// from the repository entities.
$exposedFieldsReader = new ExposedFieldsReader(new NativeAttributeReader());

$doctrineFilter = new DoctrineFilter($queryBuilder, $exposedFieldsReader, [new PresetFilterProvider()]);

// Now that we have a DoctrineFilter instance we need to tell it what filtering actions to take
// and to look for sort actions under the orderBy key.
$actions = ActionList::fromQueryString($_SERVER['QUERY_STRING'], 'orderBy');

// Finally apply the actions retrieved from the current request to the query builder.
$doctrineFilter->apply($actions);
```

---
Next chapter: [Symfony guide](guide-symfony.md)

