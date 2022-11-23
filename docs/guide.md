## Guide

This section will guide you with the implementation of this library into your project. On the most basic level, you will
create an instance of `DoctrineFilter` and apply it to your entity query builders before the query is executed.

You can achieve that by using a service or implementing a method in a base controller which will filter the query
builder.

`DoctrineFilter` operates on the query builder using a whitelisted array of fields for the entities present in the
query builder and the operations allowed for each of them. 

The library provides the `Expose` annotation to mark exposed fields as such and includes the
`ExposedFieldsReader` class which is able to generate the whitelist from the query builder. The following example uses
this method.

### Using the library without the `Expose` annotation.

If using attributes is not appropriate for your project you are free to retrieve the list of exposed fields in any
fashion and simply pass them as an argument to the `DoctrineFilter` constructor.

DoctrineFilter expects a dictionary for each entity containing a list of the exposed fields

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

