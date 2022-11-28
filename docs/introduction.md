## Purpose

This library allows you to apply filters to a Doctrine Query Builder instance based on the input from the query string,
allowing your application or Api to implement a common filtering scheme for all the exposed resources.

Note that this library provides only filtering of the Query Builder. Those looking for pagination can use this library
in conjunction with [BabDev/Pagerfanta](https://github.com/BabDev/Pagerfanta).

## Filtering and Sorting

### Filtering

Use any of the built-in filters described below or create custom filters which generate arbitrarily complex
expressions and can be constrained on which entities it should be possible to execute them.

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

Sorting is applied via a query string key (e.g. `orderBy`) specified during instantiation, which also means that it is
not a valid field name to use for
ordering your data. It is applied via the following syntax: `orderBy[fieldName]=direction` where direction can be
either `asc` or `desc`.

The `orderBy` key can be used multiple times to allow sorting by multiple fields.
E.g: `orderBy[id]=desc&orderBy[lastName]=asc`

---
Next chapter: [Getting started](getting-started.md)
