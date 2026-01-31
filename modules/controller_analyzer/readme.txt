modules/controller_analyzer/
├── bootstrap.php
├── init.php
├── config/
│   └── analyzer.php
├── classes/
│   ├── Controller/
│   │   ├── Analyzer.php
│   │   └── Map.php
│   ├── Helper/
│   │   └── ControllerScan.php
│   └── Model/
│       └── ControllerInfo.php
├── views/
│   ├── analyzer/
│   │   ├── index.php
│   │   ├── map.php
│   │   └── details.php
│   └── map/
│       ├── html.php
│       └── json.php
└── README.md


Использование
Web-интерфейс

    http://ваш-сайт/controller-analyzer - основная страница со статистикой

    http://ваш-сайт/controller-map - полная карта контроллеров в HTML

    http://ваш-сайт/controller-map/json - карта в формате JSON

    http://ваш-сайт/controller-map/xml - карта в формате XML
	
	
	Программное использование
	// Получить полную карту
$map = Helper_ControllerScan::get_map();

// Получить статистику
$stats = Helper_ControllerScan::get_statistics();

// Получить информацию о конкретном контроллере
$controller_info = Helper_ControllerScan::get_controller_details('User');

// Использование модели
$controller = Model_ControllerInfo::factory('Product');
$views = $controller->get_views();

Конфигурация

Настройки находятся в modules/controller_analyzer/config/analyzer.php:

    cache.enabled - включить/выключить кэширование

    cache.lifetime - время жизни кэша (секунды)

    scan.paths - пути для сканирования контроллеров

    scan.exclude - классы контроллеров для исключения

Особенности

    Автоматическое кэширование результатов

    Поддержка рекурсивного сканирования модулей

    Экспорт в различные форматы (HTML, JSON, XML)

    Детальная информация о методах и параметрах

    Извлечение документации из комментариев
	
	
	
## 10. **Использование в проекте**

После установки модуля добавьте в ваш `bootstrap.php`:

```php
// application/bootstrap.php
Kohana::modules(array(
    // ... ваши модули
    'controller_analyzer' => MODPATH.'controller_analyzer',
));


Теперь вы можете получить доступ:

    http://yoursite.com/controller-analyzer - веб-интерфейс

    http://yoursite.com/controller-map - карта контроллеров

Для получения данных программно:


// Получить полную карту
$controller_map = Helper_ControllerScan::get_map();

// Вывести информацию
foreach ($controller_map as $controller => $data) {
    echo "Controller: $controller\n";
    foreach ($data['actions'] as $action => $action_data) {
        echo "  Action: $action\n";
        echo "    Method: {$action_data['method']}\n";
        echo "    View: {$action_data['view']}\n";
    }
}



Этот модуль предоставляет полную информацию о всех контроллерах проекта, их методах и используемых представлениях с возможностью кэширования и экспорта в различные форматы.
===============
modules/controller_analyzer/
├── bootstrap.php
├── init.php
├── classes/
│   ├── Controller/
│   │   ├── Analyzer.php      # Главный контроллер
│   │   └── Camap.php         # Контроллер для карты
│   ├── Helper/
│   └── Model/
└── views/

Корректные URL для доступа:

    http://ваш-сайт/controller-analyzer → Controller_Analyzer::action_index()

    http://ваш-сайт/controller-analyzer/map → Controller_Analyzer::action_map()

    http://ваш-сайт/controller-map → Controller_Camap::action_index()

    http://ваш-сайт/controller-map/json → Controller_Camap::action_index() с форматом json

Альтернатива: Использовать один контроллер:

<?php
// modules/controller_analyzer/bootstrap.php
Route::set('controller_analyzer', 'controller-analyzer(/<action>(/<format>))')
    ->defaults(array(
        'controller' => 'analyzer',
        'action'     => 'index',
        'format'     => 'html'
    ));
	
	Тогда:

    http://ваш-сайт/controller-analyzer - статистика

    http://ваш-сайт/controller-analyzer/map - карта в HTML

    http://ваш-сайт/controller-analyzer/map/json - карта в JSON

Вывод: Ваше замечание абсолютно верно. В Kohana 3.3 directory в роутах используется для подкаталогов внутри classes/Controller/, а не для изоляции модулей. Лучше использовать префиксы в именах контроллеров или отдельные простые имена.


========================
modules/controller_analyzer/
├── classes/
│   ├── Controller/
│   │   └── Analyzer.php
│   └── ControllerAnalyzer/
│       └── ControllerScanner.php
