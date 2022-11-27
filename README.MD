# doctrine-filter

Quickly add advanced filtering and sorting capabilities to any resource in your APIs or Web apps that use
Doctrine.

<a href="https://travis-ci.com/github/maldoinc/doctrine-filter"><img src='https://api.travis-ci.com/maldoinc/doctrine-filter.svg?branch=master'  alt="Build status"/></a>
<img src='https://img.shields.io/travis/com/maldoinc/doctrine-filter' />
<img src='https://img.shields.io/github/license/maldoinc/doctrine-filter' />

###### Requirements

* PHP >= 7.4
* Doctrine >= 2.0

---

#### Examples

The following are some query examples showcasing the different filtering and sorting capabilities of the library.

```http request
// Products with a price range between 100 and 200
GET /products?price[gte]=100&price[lte]=200

// Users with a birthday after 1 Jan 2000
GET /users?birthday[gte]=2000-01-01

// Users with an email ending with gmail.com
GET /users?email[ends_with]=gmail.com

// Customers whose subscription field is null
GET /customers?subscription[is_null]

// Users ordered by their last name in a descending order
GET /users?orderBy[lastName]=desc

// For this query only the status filter and order by id desc will be applied.
// Any keys not in the specified filter or oderBy format will be ignored
GET /todos?status[eq]=done&page=1&perPage=10&orderBy[id]=desc
``` 

## Documentation

[Read the documentation here](docs/index.md)
