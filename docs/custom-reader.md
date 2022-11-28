## Custom Reader

If using attributes is not appropriate for your project you are free to retrieve the list of exposed fields in any
fashion (such as loading from a yaml file) and simply pass the reader as an argument to the `DoctrineFilter` 
constructor.

The return must be a map for each root entity in the query builder containing a list of the exposed fields.

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

