# doctrine-filter

Quickly add advanced filtering/searching and sorting capabilities to any resource in your APIs or Web apps
that use Doctrine.

---

## Purpose

This library allows you to apply filters to a Doctrine Query Builder instance based on the input from the query string,
allowing your application or Api to implement a common filtering scheme for all the exposed resources.

Note that this library provides only filtering of the Query Builder. 
Those looking for pagination can use this library in conjunction with [BabDev/Pagerfanta](https://github.com/BabDev/Pagerfanta).

## Filtering and Sorting

The library provides two methods, `applyFromQueryString` and `applyFromArray` which modify the Query Builder
according to the current request.


When using the `applyFromQueryString` method the query string is parsed via php's [parse_str](https://www.php.net/manual/en/function.parse-str.php) method.

The syntax to construct the query string is as follows: `field[operator]=value` or `field[operator][]=value`
for operations such as `in` or `not in` which work with arrays.

The syntax for the unary operators such as `is null` or `is not null` is `field[is_null]` or `field[is_null]=1` the value
part of the query string will be discarded as it cannot be used.

You can also use `applyFromArray` when using some component to access the current request, such as Symfony's `Request`, 
as long as the query string is parsed via `parse_str`.

Symfony example: `DoctrineFilter::applyFromArray($qb, $request->query->all());`

### Filtering

The supported filter operators are listed below

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

### Ordering

Ordering is applied via the `orderBy` query string key, which also means that it is not a valid field name to use 
for ordering your data. Ordering is applied via the following syntax: `orderBy[fieldName]=direction` where direction
can be either `asc` or `desc`. 

The `orderBy` key can be used multiple times to allow sorting by multiple fields.
E.g: `orderBy[id]=desc&orderBy[lastName]=asc`

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

// Returns all users belonging to the USER role
// Ordered by their last name in a descending order
GET /users?role[in][]=USER&orderBy[lastName]=desc

// For this query only the status filter and order by id desc will be applied.
// Any keys not in the specified filter or oderBy format will be ignored
GET /todos?status[eq]=complete&page=1&perPage=10&orderBy[id]=desc
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
    
    // Further process or paginate the data as necessary.
    return ...
}
```

