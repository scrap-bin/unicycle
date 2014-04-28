## Валидация данных

### Используемые классы

`R2\Validator\Validator`


### Основные сведения

    Класс Validator и примеры его типового использования в настоящий момент недоделаны. Эта документация
    явняется заготовкой статьи. Например в будущем предполагается, что правила валидации будут действовать и
    на стороне клиента с использованием расширения jQuery Validation.

Все данные, приходящие от пользователя, должны проходить проверку. По возможности, проверка производится
классом Validator на основании единых правил валидации.
```php
$validator = $this->get('validator');
$errors = $validator->validate($form, 'login');
```


### Конфигурация

Пример описания правил:
```yaml
validation:
    rules:
        NotGuest: '/^(?!guest$).*$/i'
        ValidCharsOnly: '/^[^\[\]''"@]+$/'
    groups:
        login:
            username: { NotBlank: ~, message: 'Invalid username' }
            password: { NotBlank: ~, message: 'Invalid password' }
        register:
            username:  { MinLength: 2, MaxLength: 25, NotGuest: ~, ValidCharsOnly: ~, message: 'Bad username' }
            password:  { MinLength: 6, message: 'Bad password' }
            password2: { TheSame: password, message: 'Pass not match' }
            email:     { Email: ~, message: 'Invalid email' }
```
Некоторые правила встроены в класс Validator, другие описаны в конфигурации с помощью регулярных выражений.
Правила привязываются к полям проверяемого объекта в "группах валидации". В разных случая один и тот же объект
проходит разный набор проверок. 

Сообщение об ошибке из правила валидации проходит через Переводчик, используется домен переводов "validators".
