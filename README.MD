# doctrine-filter

Quickly add advanced filtering and sorting capabilities to any resource in your APIs or Web apps that use
Doctrine.

[![License](https://img.shields.io/github/license/maldoinc/doctrine-filter)](https://github.com/maldoinc/doctrine-filter)
[![Unit tests](https://github.com/maldoinc/doctrine-filter/actions/workflows/tests.yml/badge.svg)](https://github.com/maldoinc/doctrine-filter)
[![Lint](https://github.com/maldoinc/doctrine-filter/actions/workflows/quality-tools.yml/badge.svg)](https://github.com/maldoinc/doctrine-filter)

###### Requirements

* PHP >= 7.4
* Doctrine >= 2.0

---

#### Examples

Products with a price range between 100 and 200

```http 
GET /products?price[gte]=100&price[lte]=200
```

Users with a birthday after 1 Jan 2000

```http
GET /users?birthday[gte]=2000-01-01
```

Users with an email ending with gmail.com

```http
GET /users?email[ends_with]=gmail.com
```

Customers whose subscription field is null

```http
GET /customers?subscription[is_null]
```

Users ordered by their last name in descending order

```http
GET /users?orderBy[lastName]=desc
```

For this query only the status filter and order by id desc will be applied.
Any keys not in the specified filter or orderBy format will be ignored

```http
GET /todos?status[eq]=done&page=1&perPage=10&orderBy[id]=desc
``` 

## Documentation

[Read the documentation here](docs/index.md)
