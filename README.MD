# doctrine-filter

Quickly apply filters to a Doctrine Query Builder from the query string

---

## Purpose

This library allows you to apply filters to a Doctrine Query Builder instance based on the input from the query string
allowing your application or Api to implement a common filtering scheme for all the exposed resources.

Note that this library provides only filtering of the Query Builder. 
Those looking for pagination can use this library in conjunction with [BabDev/Pagerfanta](https://github.com/BabDev/Pagerfanta).

### Filtering

Filters are applied via query string which then are parsed via php's `parse_str` method.

The syntax to construct the query string is as follows: `field[operator]=value`

| Operator | Description
| --------------- | --------------------- |
| `eq`            | Equality              |
| `neq`           | Inequality            |
| `gt`            | Greater than          |
| `gte`           | Greater than or equal |
| `lt`            | Less than             |
| `lte`           | Less than or equal    |
| `in`            | In                    |
| `not_in`        | Not in                |
| `is_null`       | Is null               |
| `is_not_null`   | Is not null           |

#### Examples

```http request
// Returns products with a price range between 100 and 200
GET /products?price[gte]=100&price[lte]=200

// Returns all users with a birthday after 1 Jan 2000
GET /users?birthday[gte]=2000-01-01

// Returns users with "tag" property being either red or yellow
GET /users?tag[in][]=red&tag[in][]=yellow

// Returns customers whose subscription field is null
GET /customers?subscription[is_null]
``` 

## Installation

The recommended way to install the library is via composer:

```
composer require maldoinc/doctrine-filters
```

## Usage in a controller

```php
# GET /users?age[gt]=18&age[lt]=35

#[Route("/users")]
public function getUsers(Request $request, UserRepository $userRepository)
{
    $qb = $userRepository->createQueryBuilder('u');
    DoctrineFilter::applyFromArray($qb, $request->query->all());
    
    // ...
    
    // Hypothetical method which paginates the query builder 
    // based on the current request and returns the retreived results
    return $this->createPaginatedResponse($qb);
}
```

