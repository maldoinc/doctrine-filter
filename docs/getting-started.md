## Getting started

In its basic form you will have a query builder and using an instance of `DoctrineFilter` you will apply on it
operations from the current request.

This will modify your query builder's where section to add the filters from the query string
and include the new parameters with their corresponding values without removing any current where conditions or
parameters.

To create an instance of `DoctrineFilter` you need the following:

* A query builder with an entity in it (the resource you're filtering)
* An instance of a `FilterReaderInterface` such as the provided `ExposedFieldsReader`
    * If you are using the provided reader you need to annotate your entities with the `Expose` annotation.
    * Otherwise, feel free to provide any implementation such as reading the data from a yaml or xml file.
* A list of providers, which are the ones that provide the actual filtering capabilities.
    * Use the default `Maldoinc\Doctrine\Filter\Provider\PresetFilters` filters
    * Create your own filters and include an instance of it during instantiation to make your own custom filters
      available.

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

### Using the library without the `Expose` annotation.

If using attributes is not appropriate for your project you are free to retrieve the list of exposed fields in any
fashion (such as loading from a yaml file) and simply pass them as an argument to the `DoctrineFilter` constructor.

DoctrineFilter expects a map for each root entity in the query builder containing a list of the exposed fields.

```php
$fields = [
    SomeEntity::class => [
        'id' => ExposedField(fieldName: 'id', exposedOperators: ['eq']),
        'public_serialized_name' => ExposeField(
            fieldName: 'classFieldName', 
            exposedOperators: ['eq', 'neq', 'starts_with', 'contains', 'ends_with']
        ),
    ],
    OtherEntity::class => [...]
]
```

---
Next chapter: [Symfony example](guide-symfony.md)

