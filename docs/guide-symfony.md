## Symfony Guide

#### 1. Annotate your entities with the `@Expose` attribute either via php attributes or doctrine annotations.

```php
# src/Entity/Book.php

namespace App\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Maldoinc\Doctrine\Filter\Annotation\Expose as FilterExpose;
use Maldoinc\Doctrine\Filter\Provider\PresetFilterProvider;

#[ORM\Entity]
class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[FilterExpose(operators: PresetFilterProvider::ALL_PRESETS)]
    public int $id;

    #[ORM\Column]
    #[FilterExpose(operators: PresetFilterProvider::ALL_PRESETS)]
    public string $name;

    #[ORM\Column]
    public int $notMappedForFiltering;

    #[ORM\Column]
    #[FilterExpose(operators: PresetFilterProvider::ALL_PRESETS)]
    public \DateTime $publishedAt;
}
```

#### 2. Create a service which accepts a query builder and applies filters from the current request

```php
# src/Service/FilteredQueryBuilder.php

namespace App\Service;

use Doctrine\ORM\QueryBuilder;
use Maldoinc\Doctrine\Filter\Action\ActionList;
use Maldoinc\Doctrine\Filter\DoctrineFilter;
use Maldoinc\Doctrine\Filter\Provider\PresetFilterProvider;
use Maldoinc\Doctrine\Filter\Reader\AttributeReader\NativeAttributeReader;
use Maldoinc\Doctrine\Filter\Reader\ExposedFieldsReader;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class FilteredQueryBuilder
{
    public function __construct(private RequestStack $requestStack){}

    public function createEntityJsonResponse(QueryBuilder $queryBuilder): JsonResponse
    {
        // Use the built-in reader based on php attribute reader.
        // Doctrine annotation reader is also available.
        $fieldReader = new ExposedFieldsReader(new NativeAttributeReader());

        $filter = new DoctrineFilter($queryBuilder, $fieldReader, [new PresetFilterProvider()]);
        $actions = ActionList::fromArray(
            // DO NOT USE $request->query->all() here.
            // See ActionList::fromArray documentation for more details.
            data: HeaderUtils::parseQuery($request->getQueryString()),
            
            // The key under which to look for sorting actions
            orderByKey: 'orderBy', 
            
            // Should the field=value syntax be treated as an equality operation?
            // Makes it equivalent to field[eq]=value.
            simpleEquality: true 
        );
        
        $filter->apply($actions);
        
        // ... Any additional processing

        return new JsonResponse(['data' => $queryBuilder->getQuery()->getResult()]);
    }
}
```

#### 3. Call service from your controllers

```php
# src/Controller/BookController.php

class BookController extends Controller
{
    #[Route("/book")]
    public function getList(BookRepository $repository, FilteredQueryBuilder $filteredQueryBuilder)
    { 
        // All that there's left to do is create a query builder based on the current
        //resource or entity being exposed and pass it to the service we just created.
        
        // Create a new query builder or otherwise call a method that returns one
        // from the entity repository
        $qb = $repository->createQueryBuilder('b');
        
        return $filteredQueryBuilder->createEntityJsonResponse($qb);
    }
}
```

---
Next chapter: [Filter Joined Entities](joined-entities.md)
