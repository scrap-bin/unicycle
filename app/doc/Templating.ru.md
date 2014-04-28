## Движок шаблонов PhpEngine

### Используемые интерфейсы

`R2\Templating\EngineInterface`


### Основные сведения

Unicycle использует "нативные" шаблоны. Задача класса `R2\Templating\PhpEngine` выделить код представления
и при этом не слишком нагрузить систему. Движок сохраняет эффективность и удобство отладки php-файлов. 
Данные, передаваемые в шаблон, доступны внутри как локальные переменные. Движок расширяется за счет подключения 
функций в глобальном пространстве имен. Поэтому текст шаблона остается достаточно чистым и легко читаемым.

**Примеры:**

Обычный вывод переменной в тексте:
```php
Username: <?= $username ?>
```

То же самое, но с применением переводов и экранированием вывода
```php
<?= t('Username') ?>: <?= e($username) ?>
```

Конечно, так как это PHP, допустимы любые операторы:
```php
<ul>
<?php foreach ($rows as $_r): ?>
  <li><?= e($_r) ?></li>
<?php endforeach; ?>
</ul>
```
здесь используется соглашение, что внутренние переменные, т.е. которые не переданы при вызове шаблона, должны
начинаться с символа подчеркивания. Так мы избегаем случайного наложения имен.

Генерация ссылки по имени маршрута:
```php
<a href="<?= url('logout', ['token' => $user->getCsrfToken()]) ?>">Logout</a>
```


### Подключение других шаблонов

Типичный вызов под-шаблона:
```php
<?php require $this->prepare('Items/navbar') ?>
```
метод prepare добавляет базовый путь и расширение файла шаблона. В унаследованных от PhpEngine классах возможна
более продвинутая логика, например поиск в нескольких папках (стиль, пакет, общие) или кеширование результата.

Под-шаблон будет использовать ту же область видимости, что и вызывающий. Не понадобится дополнительно 
передавать переменные.

### Блоки и наследование

Так назваемое наследование шаблонов на самом деле не имеет отношения к объектному программированию. И реализуется
без использования классов PHP. Речь идет об иерархии элементов страницы. "Родительский" шаблон задает общий
каркас страницы, постоянные ее части, а "дочерний" определяет один или несколько кусков контента для заполнения 
этого каркаса. 

Логически можно представить, что дочерний шаблон "перекрывает" какие-то свойства родителя.  
Физически это работает так:

* при вызове представления мы указываем дочерний шаблон:
```php
$view->render('Demo/child')
```
* в начале шаблона стоит указание от какого шаблона он наследует. это имя встает в очередь на исполнение
```php
$this->extend('parent')
```
* после того как дочерний шаблон выполнен, весь его вывод буферизуется и помещается в блок по умолчанию
с именем *"content"*. в самом шаблоне для этого ничего не надо делать, это предопределенное поведение.
открывается родительский шаблон, он может вывести сохраненный блок там где нужно: 
```php
<?= isset($this->blocks['content']) ? $this->blocks['content'] : null ?>
```
или то же самое, но короче:
```php
<?= $this->block('content') ?>
```
* дочерний шаблон может определить дополнительные именованные блоки. для этого есть пара вызовов:  
```php
<?php $this->beginBlock('foo') ?>
    ... 
    content of block foo
    ...
<?php $this->endBlock() ?>  
```


### Расширение движка

Выше упоминались функции-помошники вроде url() или t(). Они не являются частью движка, это часть расширений,
доступных в Unicycle. Вы можете определить свои расширения. Нет никаких специальных требований к расширениям,
оформляйте их как угодно. 

Чтобы нужный модуль был подключен к моменту использования, мы используем уловку в конфигурации сервисов:

```yaml
services:
#
# Templates Engine and its Extensions
#
    templating:
        class: R2\Templating\PhpEngine
        arguments:
            -
                template_dir: "%root_dir%/views"
                template_ext: ".html.php"
                extensions:
                    - "@tpl_standard_extension"
                    - "@tpl_routing_extension"
```

Здесь *tpl_standard_extension* и *tpl_routing_extension* это имена сервисов, которые автоматически будут созданы ДО
первого использования сервиса *templating*. Так работает фабрика сервисов. Сам класс PhpEngine никак не использует
ключ "extensions".
Эти дополнительные сервисы подключают файлы с функциями и инжектят нужные зависимости.

**Доступные в настоящий момент функции-помошники:**

  * `url($name)` или `url($name, [parameters])` — создание ссылки по имени [маршрута](Routing.ru.md);

  * `baseUrl()` — префикс, путь до стартовой папки сайта;

  * `e($string)` — экранирование "опасных" символов HTML;

  * `t($token)` или `t($token, $domain)` — перевод. используется текущий язык, а домен по умолчанию равен 'common';

  * `currentLanguage()` и `currentLanguageLabel()` — возвращают текущий язык (локаль) в виде "en_US" и "en" соответственно;

  * `languageDescriptions()` — массив описателей языков. просто набор названий, которые можно использовать для 
    формирования меню выбора языка, например. загляните в parameters.yml чтобы узнать больше об этом массиве.