## Отображение данных


### Используемые интерфейсы

`R2\DBAL\DBALInterface`

`R2\ORM\EntityManagerInterface`

`R2\ORM\EntityRepositoryInterface`

`Iterator`


### Основные понятия

Во фреймворке используются два уровня абстракции данных:
* **Database Abstraction Layer** (DBAL) — слой доступа. Скрывает используемое расширение доступа к базе, 
  добавляет удобство работы с "плейсхолдерами". 
* **Data Mapper**. По принципу разделения ответственности, класс не должен одновременно знать о предметной области 
  (Domain Logic) и о способе сохранения состояния (Persistence). Сохранение состояние выделяется в Data Mapper. 
  А целевой объект остается "чистым".


### DBAL

Сейчас интерфейс DBAL реализован для расширения mysqli и для PDO с драйвером mysql. Смена драйвера при сохранении 
типа БД теоретически не должна повлиять на работу приложения. Чего не скажешь про смену диалекта SQL. 
DBAL использует именованные нетипизованные плейсхолдеры. Можно использовать значения null, строки, числа и массивы.
Есть особый случай плейсхолдера — префикс таблицы.

Пример:
```php
$rows = $db->query(
    "SELECT * FROM `:p_users` WHERE `group_id` IN(:ids)",
    ['ids' => [2, 3]]
)->fetchAssocAll();
```


### Data Mapper

Любой ORM это компромис между удобством использования и производительностью. Мой Data Mapper создается под 
влиянием Doctrine2. Поэтому его интерфейсы во многом повторяют доктриновские. Но я попытаюсь не дать ему 
превратиться в прожорливого монстра. Он не будет решать всё за вас. Он не будет применять рефлексию и 
сканировать исходники в поисках аннотаций. Автоматической поддержки внешних ключей не предвидится. 
Из простых радостей: для типичных случаев работает автоматическое соответствие имен поле-колонка и обновление 
автоинкрементного ключа. Но можно всё перекрыть в конфигурации.

Отправной точкой мапера служит объект `EntityManager`. Из него мы можем получить "репозитории", соответствующие 
классам предметной области. 

```php
$repo = $em->getRepository('R2\\Model\\User');
$user = $repo->find($userId); // raises exception on missing object
//...
$user->lastVisit = time();
$repo->persist($user);
```
Можно создать свой класс репозитория, например для хитрых выборок. Но репозиторий по умолчанию тоже неплохо 
справляется с типичными запросами. Если соответствие класс => репозиторий не задано в конфигурации, то поиск 
происходит по следующему алгоритму:

1. к имени класса добавляется суффикс 'Repository': User => UserRepository. Если такой класс существует, 
   это искомый репозиторий;
2. если репозиторий не найден, от исходного класса получаем класс-родитель и возвращаемся к шагу 1;
3. если вся иерархия пройдена и ничего не найдено, используем предопределенный класс `EntityRepository`.

Методы семейства `EntityRepository::find*` делятся на возврщающие одну запись и набор записей. 
Набор реализован через интефрейс `Iterator`, а точнее через перекрытие класса `ArrayIterator`. 
```php
$userList = $em->getRepository($userClass)->findAll();
foreach ($userList as $user) {
    echo $user->username."\n";
}
```
Важная особенность: итератор не порождает новый объект на каждой итерации, а перегружает один и тот же! 
Это должно дать некоторый выигрыш в памяти и скорости. 

`EntityManager` (как и его аналог в Doctrine) умеет посредством магии сокращать цепочку вызовов при добыче 
целевого объекта. Шаг getRepository можно сократить:

```php
$userList = $em->getRepository($userClass)->findBy(['groupId' => 3]);
```
превращается в
```php
$userList = $em->findBy($userClass, ['groupId' => 3]);
```
