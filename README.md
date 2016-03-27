# phalcon-rest-jpa

![alt tag](http://stkachenko.org.ua/wp-content/uploads/2016/03/ppa.png)

Данный REST подход основываеться на описаной документации *Java Spring JPA REST* http://docs.spring.io/spring-data/jpa/docs/current/reference/html/#jpa.query-methods.query-creation

###Примеры использования - http://stkachenko.org.ua/?p=865###

Смысл данной библиотеки очень прост - pеализовать полный доступ к CRUD операциям основываясь на построении запросов в url строке.

#### Для работы данной библиотеки необходимо настроить все запросы, содержащие в себе **".\*/ppa/.\*"** на <pre>PPA\Rest\PpaController->crudAction()</pre>

* Присутствие приставки **/s/**, говорим  о том, что необходимо выбрать массив записей.
* Например: **/api/ppa/s/brands** - вернет все бренды, а **/api/ppa/brands** - вернет первый найденный бренд.

## Список реализованого PPA (Phalcon persistence api) 

|Keyword |Sample |JPQL snippet|
|---------|--------|-------------|
| *Fetch all* | /api/ppa/brands/s/  | Вернет все бренды |
| *Fetch first* | /api/ppa/brands/  | Вернет первый, попавшийся бренд |
| *And* | /api/ppa/brands/findByIdAndTitle  | <pre>… where x.id = :id: AND x.title = :title:</pre> |
| *Or* | /api/ppa/brands/findByIdOrTitle | <pre>… where x.id = :id: OR x.title = :title:</pre>  |
| *Like* | /api/ppa/clients/findByNameLike | <pre>… where x.name LIKE :name:</pre>  |
| *Containing* | /api/ppa/clients/findByTitleContaining | <pre>… where x.title LIKE :lastName: (where :lastName: =  %lastName%)</pre>  |
| *StartingWith* | /api/ppa/clients/findByTitleStartingWith | <pre>… where x.title LIKE :lastName: (where :lastName:  = lastName%)</pre>  |
| *search* (without columns)| /api/ppa/clients/search | <pre>… where x.* LIKE :lastName: (where :lastName:  = %lastName%)</pre>  |
| *search* (with columns) | /api/ppa/clients/search | <pre>… where x.column1 LIKE :lastName: OR .column2 LIKE :lastName: и тд..</pre>  |
<pre>
Передаваемые параметры должны быть переданы в camelCase
/api/ppa/findByIdAndTitleOrOldId?oldId=693&id=1&title=chery
</pre>

### Для выбора не только полей модели, но и ее связей, нужно указать в request параметрах fetchRelations=1
Например 
> /api/ppa/brands/findByTitle?**fetchRelations=1**&title=any


### Сохранение/Создание 

Сохранение записи ничем не отличаеться от ее выборки. Запрос формируется по принципу выборки еденичной сущности + суфикс /save

Например <pre>/api/ppa/targetGroups/save</pre>

Сами атрибуты сущности (данные для сохранения) можно передавать любым из способов (GET, POST, PUT, JSON RAW BODY)
```json
{
    "id" : 1,
    "title": "changed title"
}
```
*Если поле id не было указано то будет создана новая запись, в обратном случае - обновиться существующая.*

#### Сохранение/Создание связей сущности (многие ко многим)

Пример <pre>/api/ppa/targetGroups/save</pre>

```php
/**
 * Initialize method for model.
 */
public function initialize()
{
    $this->hasMany('id', 'ActivitiesTargetGroups', 'target_group_id', array('alias' => 'ActivitiesTargetGroups'));
    $this->hasMany('id', 'BrandsTargetGroups', 'target_group_id', array('alias' => 'BrandsTargetGroups'));
}
```

```json
{
    "id" : 1,
    "title": "changed title",
    "relations": {
        "ActivitiesTargetGroups": [
            {
                "activity_id": 1, 
                "target_group_id": 1
            },
            {
                "activity_id": 2, 
                "target_group_id": 1
            }
        ],
        "BrandsTargetGroups": [
           
        ]
    }
}
```
В результате такаго запроса обновиться сущность *targetGroups*, у которой id = 1. А также ее связи **ActivitiesTargetGroups** и **BrandsTargetGroups**. Причем связь BrandsTargetGroups полностью очиститься. 

#### Для выбора связанных записей один ко многим передайте параметр joinedRelations ####

##### Сортировка выбранных записей ####
для сортировки нужно указать в request параметре &orderBy=title, или &orderBy=title|Desc, или  &orderBy[]=title|Desc&orderBy[]=id
