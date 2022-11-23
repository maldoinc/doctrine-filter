# doctrine-filter

Quickly add advanced filtering/searching and sorting capabilities to any resource in your APIs or Web apps that use
Doctrine.

<a href="https://travis-ci.com/github/maldoinc/doctrine-filter"><img src='https://api.travis-ci.com/maldoinc/doctrine-filter.svg?branch=master'  alt="Build status"/></a>

###### Requirements

* PHP >= 7.4 
* Doctrine >= 2.0

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
GET /todos?status[eq]=done&page=1&perPage=10&orderBy[id]=desc
``` 

## Purpose

This library allows you to apply filters to a Doctrine Query Builder instance based on the input from the query string,
allowing your application or Api to implement a common filtering scheme for all the exposed resources.

Note that this library provides only filtering of the Query Builder. Those looking for pagination can use this library
in conjunction with [BabDev/Pagerfanta](https://github.com/BabDev/Pagerfanta).

## Filtering and Sorting

### Filtering

The clients need to send the request in PHP's own query string syntax which differs from the standard CGI query string
format. Below is a complete list of the built-in operators. For custom operators refer to the documentation.

| Operator      | Description           | Example                       |
|---------------|-----------------------|-------------------------------|
| `eq`          | Equality              | `name[eq]=Jimothy`            |
| `neq`         | Inequality            | `status[neq]=backlog`         |
| `gt`          | Greater than          | `price[gt]=10`                |
| `gte`         | Greater than or equal | `price[gte]=10`               |
| `lt`          | Less than             | `stock[lt]=100`               |
| `lte`         | Less than or equal    | `stock[lte]=100`              |
| `in`          | In                    | `id[in][]=1&id[in][]=2`       |
| `not_in`      | Not in                | `roles[not_in][]=ROLE_ADMIN`  |
| `is_null`     | Is null               | `subscribedAt[is_null]`       |
| `is_not_null` | Is not null           | `subscribedAt[is_not_null]`   |
| `starts_with` | Starts with           | `name[starts_with]=a`         |
| `ends_with`   | Ends with             | `email[ends_with]=@gmail.com` |
| `contains`    | Contains              | `name[containts]=d`           |

### Sorting

Sorting is applied via a query string key (e.g. `orderBy`) specified during instantiation, which also means that it is not a valid field name to use for
ordering your data. It is applied via the following syntax: `orderBy[fieldName]=direction` where direction can be
either `asc` or `desc`.

The `orderBy` key can be used multiple times to allow sorting by multiple fields.
E.g: `orderBy[id]=desc&orderBy[lastName]=asc`

## Installation

The recommended way to install the library is via composer:

```
composer require maldoinc/doctrine-filter
```

## Documentation

[Read the documentation here](docs/index.md)
