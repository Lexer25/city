# Controller Analyzer Module for Kohana 3.3

Модуль для анализа структуры контроллеров, их методов и используемых представлений.

## Установка

1. Поместите папку `controller_analyzer` в директорию `modules/`
2. Активируйте модуль в `application/bootstrap.php`:

```php
Kohana::modules(array(
    // ... другие модули
    'controller_analyzer' => MODPATH.'controller_analyzer',
));