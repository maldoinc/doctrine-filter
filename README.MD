# doctrine-filter

Quickly add advanced filtering/searching and sorting capabilities to any resource in your APIs or Web apps
that use Doctrine.

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

// Returns users with ids 1 or 2
GET /users?id[in][]=1&id[in][]=2

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

Note that this library provides only filtering of the Query Builder. 
Those looking for pagination can use this library in conjunction with [BabDev/Pagerfanta](https://github.com/BabDev/Pagerfanta).

## Filtering and Sorting

```php

use Maldoinc\Doctrine\Filter\DoctrineFilter;

... 

// When using a framework such as Symfony you may use the Request class
// The applyFromArray function is compatible with any request class
// which internally parses the query string via php's parse_str method.
DoctrineFilter::applyFromArray($qb, $request->query->all());

// Additionally, you can also pass the query string directly
// although the applyFromArray is the preferred method.
DoctrineFilter::applyFromQueryString($qb, $_SERVER['QUERY_STRING']);
```

### Filtering

The clients need to send the request in PHP's own query string syntax which differs from the
standard CGI query string format. Below is a complete list of the supported operators.

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

### Ordering

Ordering is applied via the `orderBy` query string key, which also means that it is not a valid field name to use 
for ordering your data. Ordering is applied via the following syntax: `orderBy[fieldName]=direction` where direction
can be either `asc` or `desc`. 

The `orderBy` key can be used multiple times to allow sorting by multiple fields.
E.g: `orderBy[id]=desc&orderBy[lastName]=asc`

## Installation

The recommended way to install the library is via composer:

```
composer require maldoinc/doctrine-filters
```

## Usage in a controller

You may wish to call this library in a base EntityRepository or Controller class to ensure that it is applied to all
resources and avoiding code duplication. An example implementation on Symfony 5 may look as follows

```php
abstract class ApiController extends Controller
{
    #[Required]
    public RequestStack $requestStack;
    
    /**
    * Filters, sorts and paginates the query builder from the query request parameters
    * Using maldoinc/doctrine-filter for the filtering and sorting 
    * and BabDev/Pagerfanta for pagination
    */
    public function createEntityJsonResponse(QueryBuilder $queryBuilder): JsonResponse
    {
        $query = $this->requestStack->getCurrentRequest()->query;

        DoctrineFilter::applyFromArray($queryBuilder, $query->all());

        $pagerfanta = new Pagerfanta(new QueryAdapter($queryBuilder));
        $pagerfanta->setMaxPerPage($query->getInt('perPage', 10));
        $pagerfanta->setCurrentPage($query->getInt('page', 1));

        return $this->json([
            'data' => $pagerfanta->getCurrentPageResults(),
            'pagination' => [
                'current_page' => $pagerfanta->getCurrentPage(),
                'total_pages' => $pagerfanta->getNbPages(),
                'per_page' => $pagerfanta->getMaxPerPage(),
                'count' => $pagerfanta->getNbResults()
            ]
        ]);
    }
}


class UsersController extends ApiController
{
    #[Route("/users")]
    public function getUsers(UserRepository $userRepository)
    { 
        return $this->createEntityJsonResponse($userRepository->createQueryBuilder('u'))
    }
}
```

