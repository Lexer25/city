# Модуль Config для Kohana 3.3

Модуль для редактирования конфигурационных файлов через веб-интерфейс.

## Установка

1. Скопируйте папку `config` в `modules/`
2. Подключите модуль в `bootstrap.php`:
   ```php
   Kohana::modules(array(
       'config' => MODPATH . 'config',
       // другие модули
   ));# city_setting
