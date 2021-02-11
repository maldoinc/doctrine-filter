# doctrine-filter

Quickly add advanced filtering/searching and sorting capabilities to any resource in your APIs or Web apps that use
Doctrine.

<a href="https://travis-ci.com/github/maldoinc/doctrine-filter"><img src='https://api.travis-ci.com/maldoinc/doctrine-filter.svg?branch=master'  alt="Build status"/></a>

###### Requirements

PHP >= 7.3 <br />
Doctrine >= 2.0

---

#### Examples

The following are some query examples showcasing the different filtering and sorting capabilities of the library.

```http request
// Returns products with a price range between 100 and 200
GET /products?price[gte]=100&price[lte]=200

// Returns all users with a birthday after 1 Jan 2000
GET /users?birthday[gte]=2000-01-01

// Returns all users with an email ending with gmail.com
GET /users?email[ends_with]=gmail.com

// Returns customers whose subscription field is null
GET /customers?subscription[is_null]

// Returns all users ordered by their last name in a descending order
GET /users?orderBy[lastName]=desc

// For this query only the status filter and order by id desc will be applied.
// Any keys not in the specified filter or oderBy format will be ignored
GET /todos?status[eq]=complete&page=1&perPage=10&orderBy[id]=desc
``` 

## Purpose

This library allows you to apply filters to a Doctrine Query Builder instance based on the input from the query string,
allowing your application or Api to implement a common filtering scheme for all the exposed resources.

Note that this library provides only filtering of the Query Builder. Those looking for pagination can use this library
in conjunction with [BabDev/Pagerfanta](https://github.com/BabDev/Pagerfanta).

## Filtering and Sorting

### Filtering

The clients need to send the request in PHP's own query string syntax which differs from the standard CGI query string
format. Below is a complete list of the supported operators.

| Operator        | Description           | Example
| --------------- | --------------------- | -----------------
| `eq`            | Equality              | `name[eq]=Jimothy`
| `neq`           | Inequality            | `status[neq]=backlog`
| `gt`            | Greater than          | `price[gt]=10`
| `gte`           | Greater than or equal | `price[gte]=10`
| `lt`            | Less than             | `stock[lt]=100`
| `lte`           | Less than or equal    | `stock[lte]=100`
| `in`            | In                    | `id[in][]=1&id[in][]=2`
| `not_in`        | Not in                | `roles[not_in][]=ROLE_ADMIN`
| `is_null`       | Is null               | `subscribedAt[is_null]`
| `is_not_null`   | Is not null           | `subscribedAt[is_not_null]`
| `starts_with`   | Starts with           | `name[starts_with]=a`
| `ends_with`     | Ends with             | `email[ends_with]=@gmail.com`
| `contains`      | Contains              | `name[containts]=d`

### Sorting

Sorting is applied via the `orderBy` query string key, which also means that it is not a valid field name to use for
ordering your data. It is applied via the following syntax: `orderBy[fieldName]=direction` where direction can be
either `asc` or `desc`.

The `orderBy` key can be used multiple times to allow sorting by multiple fields.
E.g: `orderBy[id]=desc&orderBy[lastName]=asc`

## Installation

The recommended way to install the library is via composer:

```
composer require maldoinc/doctrine-filters
```

## Guide

This section will guide you with the implementation of this library into your project. On the most basic level, you will
create an instance of `DoctrineFilter` and apply it to your entity query builders before the query is executed.

You can achieve that by using a service or implementing a method in a base controller which will filter the query
builder.

`DoctrineFilter` operates on the query builder using a whitelisted array of fields for the entities present in the
query builder. The library provides the `@Expose` annotation to mark exposed fields as such and includes the
`ExposedFieldsReader` class which is able to generate the whitelist from the query builder. The following example uses
this method.

### Using the library without @Expose annotation.

If using attributes is not appropriate for your project you are free to retreive the list of exposed fields in any
fashion and simply pass them as an argument to the `DoctrineFilter` constructor.

DoctrineFilter expects a dictionary for each entity containing a list of the exposed fields

```php
$fields = [
    SomeEntity::class => [
        'id' => 'id',
        'public_serialized_name' => 'classFieldName',
    ],
    OtherEntity::class => ['id' => 'id', 'name' => 'name']
]
```

### Complete implementation in a Symfony project

#### 1. Annotate your entities with the `@Expose` annotation
```php
# src/Entity/Book.php

namespace App\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Maldoinc\Doctrine\Filter\Annotation\Expose as FilterExpose;

/**
 * @ORM\Entity
 */
class Book
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @FilterExpose
     */
    public $id;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     * @FilterExpose
     */
    public $name;

    /**
     * @ORM\Column(type="json")
     */
    public $notMappedForFiltering;

    /**
     * @ORM\Column(type="datetime")
     * @FilterExpose(serializedName="published_at")
     */
    public $publishedAt;
}
```

#### 2. Create a service which accepts a query builder and applies filters from the current request

```yaml
# Service definition
services:
    App\Service\FilteredQueryBuilder:
        arguments:
            $cacheDir: "%kernel.cache_dir%"
            $isDebug: "%kernel.debug%"
```

```php
# src/Service/FilteredQueryBuilder.php

namespace App\Service;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\ORM\QueryBuilder;
use Maldoinc\Doctrine\Filter\DoctrineFilter;
use Maldoinc\Doctrine\Filter\ExposedFieldsReader;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class FilteredQueryBuilder
{
    public function __construct(private RequestStack $requestStack,
                                private string $cacheDir,
                                private bool $isDebug)
    {
    }

    public function createEntityJsonResponse(QueryBuilder $queryBuilder): JsonResponse
    {
        // Create a cached annotation reader in order to read the @Expose annotations
        // from the entities appearing in the query builder
        $reader = new CachedReader(
            new AnnotationReader(),
            // Example uses Filesystem cache but feel free to adapt
            // to any of the supported doctrine caching mechanisms
            new FilesystemCache($this->cacheDir),
            $this->isDebug
        );

        // Use the built-in reader based on the doctrine annotations reader.
        // You are free however to use something else if annotations
        // are not appropriate for your project.
        $fieldReader = new ExposedFieldsReader($reader);

        $filter = new DoctrineFilter($queryBuilder, $fieldReader->readExposedFields($queryBuilder));
        $filter->applyFromArray($this->requestStack->getCurrentRequest()->query->all());

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

