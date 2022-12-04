## Filter Joined Entities

To allow filtering on joined entities simply annotate your entity with the `Expose` annotation and manually add the 
join in the query builder with the same join alias as the entity field name or the `serializedName` if you provided it
to the annotation. Specifying `operators` for a joined entity is a no-op as the joined entity field will have its 
own operator list.

Now filter joins are available to use via the `name.field` syntax. 

* `/books?author.id=1` (Simple Equality also works with joined fields if enabled.)
* `/books?author.name[contains]=Bob`


> **Why do I have to manually join?** <br />
> This is a design choice. It is assumed that any entity you're filtering is already present as a sub-resource in
> your response so there's no need for the library to do the join for you. 


---
Next chapter: [Custom Filters](custom-filters.md)
