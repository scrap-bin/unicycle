## Переводы

### Используемые интерфейсы

`R2\Translation\TranslatorInterface`

`R2\Translation\LoaderInterface`


### Основные сведения

    Этот файл явняется заготовкой статьи. Спецификация сервиса Переводчик уточняется.

Сервис "Переводчик" это вариация на тему gettext. Переводимые фразы из разных предметных областей (доменов)
заменяются на соответствующие локализованные варианты. Особенность моего класса в том, что для загрузки
переводов могут использоваться различные форматы файлов. В приложении может использоваться кеширующий декоратор чтобы
компенсировать медленный парсинг "удобных" форматов.

Сам класс Переводчик не зависит явно от параметров HTTP запроса или от других классов. За установку подходящей
локали отвечает Фронт-контроллер. На старте приложения вычисляется локаль и подставляется в параметры конфигурации.
Сервис Переводчик рождается с этой локалью.

Использование Переводчика в Контроллере:
```php
$greeting = $this->i18n->t('Welcome');
$errors[] = $this->i18n->t('Invalid parameter', 'validators');
```
(в первом случае используется домен по умолчанию "common". во втором явно указан "validators")

Использование Переводчика в шаблоне (через расширение шаблонизатора):
```php
<?= t('Username') ?>
```

### Конфигурация

Пример описания перевода в формате YAML:  
```yaml
'Forgotten pass':   'Забыли пароль?'
'Login redirect':   'Успешный вход. Переадресация &hellip;'
'Logout redirect':  'Выход произведён. Переадресация &hellip;'
'No email match':   'Нет пользователя с e-mail %s.'
```

Пример описания сервиса:
```yaml
services:
    i18n:
        class: R2\Translation\Translator
        arguments: ["@i18n_loader", "%fallback_lang%"]
        settings: { setLocale: ["%default_lang%"] }
```
