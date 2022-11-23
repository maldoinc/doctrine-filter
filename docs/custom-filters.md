# Creating custom filters

To add a new filter that is not part of the built-in set you must implement `FilterExtensionInterface` and
return a list of filters to be exposed from the `getUnaryOperators` and `getBinaryOperators` methods 
using a map with filter names as keys and an operation as the value.

The return of the operation's callback can be anything that can be passed to the query builder's `andWhere` method, 
including complex expressions.

Example below: An `is_empty_or_null` filter which returns all rows where the value is either blank or null.

```php
    /**
     * @return array<string, \Maldoinc\Doctrine\Filter\Operations\AbstractFilterOperation> 
     */
    public function getUnaryOperators(): array
    {
        return [
            'is_empty_or_null' => new UnaryFilterOperation(function ($field) {
                $expr = new Expr();

                return $expr->orX($expr->length($field), $expr->isNull($field));
            }),
            
            // Hide logic from the url and expose a filter 
            // only for things that implement the right interface.
            'is_subscribed' => new UnaryFilterOperation(function ($field) {
                return (new Expr())->isNotNull('subscribedAt'); 
            }, function (string $className) {
                return class_implements($className, SubscribableEntityInterface::class);
            })
        ];
    }
```

Then on `DoctrineFilter` instantiation pass this class name alongside 
any other filter extensions (such as the preset one) to the constructor.

Now you can use the new filter as such `GET /resource?name[is_empty_or_null]`
