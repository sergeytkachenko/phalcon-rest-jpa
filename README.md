# phalcon-rest-jpa


Есть большой смысл реализовать REST на основе описаной документации *Java Spring JPA REST* [[http://docs.spring.io/spring-data/jpa/docs/current/reference/html/#jpa.query-methods.query-creation]]

* Присутствие приставки **/s/**, говорим  о том, что необходимо выбрать массив записей.
* Например: **/api/ppa/s/brands** - вернет все бренды, а **/api/ppa/brands** - вернет первый найденный бренд.

###### Что необходимо реализовать, в порядке приоритетов:

| Keyword | Sample | JPQL snippet|
|---------|--------|-------------|
| *Fetch all* | /rest/brands/s/  |<pre> without where</pre> |
| *Fetch first* | /rest/brands/  | <pre>without where and first entity</pre> |
| *Is,Equals* | findByFirstname,findByFirstnameIs,findByFirstnameEquals  | <pre>… where x.firstname = 1?</pre> |
| *And* | findByLastnameAndFirstname  | <pre>… where x.lastname = ?1 and x.firstname = ?2</pre> |
| *Or* | findByLastnameOrFirstname  | <pre>… where x.lastname = ?1 or x.firstname = ?2</pre>|
| *In* | findByAgeIn  | <pre>… where x.age in ?1</pre> |
| *Like* | findByFirstNameLike | <pre>… where x.firstname like ?1</pre> |

## Список реализованого PPA (Phalcon persistence api) 

|Keyword |Sample |JPQL snippet|
|---------|--------|-------------|
| *Fetch all* | /api/ppa/brands/s/  | Вернет все бренды |
| *Fetch first* | /api/ppa/brands/  | Вернет первый, попавшийся бренд |
| *And* | /api/ppa/brands/findByIdAndTitleOrOldId  | <pre>… where (x.id = :id:) AND (x.title = :title: OR x.old_id = :oldId:)</pre> |
| *Or* | /api/ppa/brands/findByIdAndTitle | <pre>… where x.id = :id: OR x.title = :title:</pre>  |
| *Like* | /api/ppa/clients/findByLastNameLikeOrNameLike | <pre>… where x.lastName LIKE :lastName: OR x.name LIKE :name:</pre>  |

<pre>
Передаваемые параметры должны быть переданы в camelCase и методом GET
/api/ppa/findByIdAndTitleOrOldId?oldId=693&id=1&title=chery
</pre>

### Для выбора не только полей модели, но и ее связей, нужно указать в request параметрах fetchRelations=1
Например 
> /api/ppa/brands/findByTitle?**fetchRelations=1**&title=any
