## Creating custom filters

To add a new filter that is not part of the built-in set you must implement `FilterExtensionInterface` and
return a list of filters to be exposed from the `getOperators` method using a map with filter names
as keys and an operation as the value.

The return of the operation's callback can be anything that can be passed to the query builder's `andWhere` method,
including complex expressions.

### Examples

```php
use Maldoinc\Doctrine\Filter\Provider\FilterProviderInterface;
use Maldoinc\Doctrine\Filter\Operations\AbstractFilterOperation;

class CustomFilterProvider implements FilterProviderInterface
{
    /**
     * @return array<string, AbstractFilterOperation> 
     */
    public function getOperators(): array
    {
        return [
            // Filter which matches all blank or null values. 
            'is_empty' => new UnaryFilterOperation(function ($field) {
                $expr = new Expr();

                return $expr->orX(
                    $expr->eq($expr->length($field), 0), 
                    $expr->isNull($field)
                );
            })
        ];
    }
}
```

Then on `DoctrineFilter` instantiation pass this class name alongside
any other filter extensions (such as the preset one) to the constructor.

Now you can use the new filter as such `GET /resource?name[is_empty]`

---
Next chapter: [Custom Reader](custom-reader.md)
