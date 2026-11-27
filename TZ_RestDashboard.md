# ТЕХНИЧЕСКОЕ ЗАДАНИЕ

## Разработка модуля `restdashboard` — аналог раздела «Информация по сотрудникам и картам» панели Artonit City на Artonit REST API v2

| Параметр | Значение |
|---|---|
| Шифр документа | TZ-RESTDASHBOARD-2026-001 |
| Версия | 1.0 |
| Дата | 25.11.2026 |
| Статус | На согласование |
| Заказчик | (заполняется) |
| Исполнитель | (заполняется) |
| Объект разработки | Artonit City, `C:\xampp\htdocs\city`, Kohana 3.3, PHP 5.6 |
| Источник данных | Artonit REST API v2, `http://localhost:5056`, версия 1.8.0 |
| Основание | Задача «Endpoint Stat» / «restdashboard» от 25.11.2026 |
| Связанные документы | `TZ_Stat_API_v2.md`, `REST_API_v2.md`, `docs/PEOPLE_API_v2.md`, `docs/IDENTIFIERS_API_v2.md` |

**История изменений**

| Версия | Дата | Автор | Изменения |
|---|---|---|---|
| 1.0 | 25.11.2026 | (заполняется) | Первая редакция: обследование раздела, контракт API v2 1.8.0, расчёт показателей, требования к модулю, тесты и приёмка |

---

## 0. Краткое резюме

Требуется разработать новый модуль Artonit City `restdashboard`, который отображает раздел **«Информация по сотрудникам и картам»** панели управления `/city/index.php/dashboard`, но получает данные не прямыми SQL-запросами к Firebird, а через **существующие методы Artonit REST API v2 версии 1.8.0**.

Новые методы API не разрабатываются. Модуль использует пять GET-операций:

| № | Метод | Назначение |
|---|---|---|
| 1 | `GET /api/v2/Person/GetCount` | всего / активных / неактивных сотрудников |
| 2 | `GET /api/v2/Identifier/GetCount` | всего карт (для оценки объёма и сверки) |
| 3 | `GET /api/v2/Identifier/GetCount?id_cardtype=1` | всего карт типа 1 |
| 4 | `GET /api/v2/Identifier/GetCount?active=0` | неактивных карт |
| 5 | `GET /api/v2/Identifier/GetList?pageIndex=N&pageSize=1000` | постраничное чтение карт для расчёта сроков, «без карты» и RFID |

Ключевое ограничение: API 1.8.0 **не умеет** фильтровать `CARD.TIMEEND` по диапазону («меньше даты»), не имеет anti-join «сотрудники без карты» и не ищет по формату `ID_CARD`. Поэтому модуль один раз постранично читает все доступные API карты и агрегирует показатели в PHP. Это осознанное проектное решение; оно описано в п. 4.6 и 5.6.

Следствия, которые нужно принять до разработки:

* карты без владельца (`CARD` без строки `PEOPLE`) API не возвращает — они не попадут в счётчики;
* при дублях `TABNUM` показатель «Сотрудники без карты» приблизительный;
* при большом `CARD` обход ограничивается `max_pages`/`max_scan_seconds`, производные показатели могут быть `null`;
* API и City должны находиться в одном часовом поясе или иметь корректный `api_timezone`.

Контрольные значения на стенде 25.11.2026:

| Показатель | Значение |
|---|---|
| Количество пользователей | 79 |
| Количество удалённых пользователей | 0 |
| Всего карт у сотрудников (тип 1) | 0 |
| Срок действия завершится до 25.12.2026 | 0 |
| Срок карты закончился | 0 |
| Сотрудники без карты | 79 |
| Количество неактивных карт | 0 |
| Неправильный формат RFID | 0 |

Оценка трудозатрат — **5–6 человеко-дней** (п. 8).

---

## 1. Общие сведения

### 1.1. Наименование работ

«Разработка модуля `restdashboard` для Artonit City — отображение раздела „Информация по сотрудникам и картам“ на данных Artonit REST API v2».

### 1.2. Цель

Дать пользователю Artonit City тот же набор показателей по сотрудникам и картам, что и текущая панель `/city/index.php/dashboard`, но без прямых SQL-запросов из PHP к Firebird — только через документированный контракт Artonit REST API v2.

### 1.3. Задачи

1. Обследовать текущую реализацию раздела «Информация по сотрудникам и картам»: показатели, SQL-формулы, конфигурацию, ссылки, дефекты.
2. Зафиксировать фактические возможности Artonit REST API v2 версии 1.8.0 применительно к каждому показателю.
3. Спроектировать модуль `restdashboard`: маршрут, меню, конфигурация, HTTP-клиент, сервис расчёта, представление, кеш, обработка ошибок.
4. Определить точные правила расчёта и граничные случаи, включая `TIMEEND = NULL`, `ACTIVE = NULL`, дубли `TABNUM`, карты без владельца.
5. Реализовать модуль в `C:\xampp\htdocs\city` на PHP 5.6 / Kohana 3.3.
6. Подготовить тесты, приёмочную матрицу, документацию и инструкцию по настройке.
7. Провести приёмку на стенде и подтвердить контрольные значения.

### 1.4. Границы работ

**Входит в объём:**

* новый модуль `modules/restdashboard`;
* страница `/city/index.php/restdashboard` с разделом «Информация по сотрудникам и картам»;
* страница детализации `/city/index.php/restdashboard/rfid` для карт с неверным форматом RFID;
* HTTP-клиент REST API v2 (GET, JSON, таймауты, ошибки);
* сервис сбора и расчёта показателей;
* серверный кеш результатов (файловый кеш Kohana);
* пункт меню, маршрут, конфигурация, i18n, README и guide;
* тесты и приёмочная документация.

**Не входит в объём:**

* изменение Artonit REST API v2 и добавление новых методов (в том числе endpoint `Stat`);
* блоки «Оборудование», «Очередь загрузок», «Системная информация», «Аналитика», «Бюро пропусков»;
* замена старых страниц детализации `identifier`, `people/find_card_late*`, `people/people_without_card`, `people/find_unActiveCard`;
* изменение схемы Firebird, индексов и триггеров;
* аутентификация и авторизация API (используются анонимные GET-методы);
* автоматическое обновление страницы по таймеру;
* экспорт показателей в CSV/Excel;
* мобильное приложение и публичный API модуля.

### 1.5. Термины и сокращения

| Термин | Определение |
|---|---|
| Панель управления | Страница `/city/index.php/dashboard`, контроллер `Controller_dashboard::action_index` |
| Раздел «Информация по сотрудникам и картам» | Блок `windows1` панели, представление `modules/dashboard/views/dashboard/dashboard.php` |
| СКУД | Система контроля и управления доступом |
| Карта, идентификатор | Запись таблицы `CARD` (поле `ID_CARD`) |
| Сотрудник, пользователь | Запись таблицы `PEOPLE` |
| `tabnum` | Табельный номер сотрудника, `PEOPLE.TABNUM`; в API v2 — внешний ключ сотрудника |
| Активная карта | `CARD.ACTIVE = 1` |
| Неактивная карта | `CARD.ACTIVE = 0` |
| Срок действия карты | `CARD.TIMEEND`; `NULL` — без ограничения |
| Горизонт | Дата «сегодня + `count_day_befor_end_time` дней», до которой показывается срок действия |
| REST API v2 | Artonit REST API v2, префикс `/api/v2`, версия 1.8.0 |
| JWT | JSON Web Token; для используемых GET-методов не требуется |
| Stale-кеш | Резервная копия последнего успешного результата; показывается при недоступности API |
| P0 / P1 | Обязательно к релизу / допустимо в следующей версии |

---

## 2. Исходные данные и условия

| Параметр | Значение | Источник |
|---|---|---|
| Сервис API | `http://localhost:5056` | `appsettings.json`, Kestrel |
| Swagger v2 | `http://localhost:5056/swagger/index.html?urls.primaryName=Artonit+REST+API+v2` | `Program.cs` |
| Swagger JSON | `http://localhost:5056/swagger/v2/swagger.json` | API |
| Версия API | 1.8.0 | `ArtonitRESTAPI.csproj`, `info.version` Swagger |
| Платформа API | .NET 8, ASP.NET Core, FirebirdSql.Data.FirebirdClient 10.3.1 | `ArtonitRESTAPI.csproj` |
| База данных API | `C:\Program Files (x86)\Cardsoft\DuoSE\Access\shieldpro_rest.GDB` | `appsettings.json` |
| Сервер БД | `127.0.0.1:3050`, `Charset=win1251`, `Dialect=3` | `appsettings.json` |
| Клиент | Kohana 3.3, PHP 5.6.40, расширения cURL, JSON, mbstring | `C:\xampp\htdocs\city` |
| DSN клиента | `odbc:SDuo`, `CharacterSet=win_1251` | `application/config/database.php` |
| Версия модуля dashboard | 2.0.6 | `modules/dashboard/init.php` |
| Горизонт по умолчанию | 30 дней | `application/config/artonitcity_config.php` |
| Часовой пояс City | `Europe/Minsk` (UTC+3, без перехода на летнее время) | `application/bootstrap.php` |
| Часовой пояс API | локальное время сервера; на стенде совпадает с City (UTC+3) | проверено по ответам API |

**Важно:** Artonit City и Artonit REST API v2 работают с одной и той же базой `shieldpro_rest.GDB`, но через разные каналы (ODBC и прямой Firebird-протокол). Это позволяет сравнивать показатели старой панели и ответы API «в одной точке времени».

**Проверено на стенде 25.11.2026:**

* API доступен по `http://localhost:5056`;
* Swagger v2 содержит 23 маршрута и 33 операции;
* `GET /api/v2/Person/GetCount` возвращает `{"total":79,"active":79,"inactive":0}`;
* `GET /api/v2/Identifier/GetCount` возвращает `{"total":0,"active":0,"inactive":0}`;
* `GET /api/v2/Identifier/GetList?pageIndex=0&pageSize=2` возвращает пустой `context` и `pageCount = 0`;
* используемые GET-методы работают **без токена**.

---

## 3. Обследование текущей панели

### 3.1. Архитектура текущего раздела

Маршрут: `/city/index.php/dashboard` (модуль `dashboard`, версия 2.0.6).

Поток данных:

```
Браузер
  → Controller_dashboard::action_index()             modules/dashboard/classes/Controller/Dashboard.php
    → Service_Dashboard::getDashboardData()          modules/dashboard/classes/Service/Dashboard.php
      → Service_Dashboard::getWin1()
        → Model_Summary::peopleCounts()              modules/dashboard/classes/Model/Summary.php
        → Service_Dashboard::checkRfidKeyFormat()
    → View::factory('dashboard/dashboard')           modules/dashboard/views/dashboard/dashboard.php
  → HTML (Bootstrap 3)
```

Раздел включается конфигом `main_windows.windows1`. Данные приходят в «плоский» массив с историческими ключами (`people_count`, `getcardexpired`, `getPeopleCardCount` и т. п.), которые напрямую связаны с SQL-алиасами.

### 3.2. Состав раздела и текущие значения

Значения зафиксированы на стенде 25.11.2026 (база `shieldpro_rest.GDB`).

| № | Показатель на экране | Ключ | Источник (SQL-алиас) | Ссылка | Значение |
|---|---|---|---|---|---|
| 1 | Количество пользователей | `people_count` | `PEOPLE_TOTAL` | — | 79 |
| 2 | Количество удалённых пользователей | `key_people_delete` | `PEOPLE_INACTIVE` | — | 0 |
| 3 | Всего карт у сотрудников | `getPeopleCardCount` | `CARD_TYPE1_TOTAL` | `identifier` | 0 |
| 4 | Срок действия завершится до 25.12.2026 | `count_card_late_next_week` | `CARD_EXPIRED_ON_DATE` | `people/find_card_late_next_week` | 0 |
| 5 | Срок карты закончился | `getcardexpired` | `CARD_EXPIRED` | `people/find_card_late` | 0 |
| 6 | Сотрудники без карты | `getPeopleWithoutCard` | `PEOPLE_WITHOUT_CARD` | `people/people_without_card` | 79 |
| 7 | Количество неактивных карт | `getCardNotActive` | `CARD_INACTIVE` | `people/find_unActiveCard` | 0 |
| 8 | Неправильный формат RFID | `countErrKeyFormatRfid` | `checkRfidKeyFormat()` | `dashboard/ErrKeyFormatRfid` | 0 |

Примечания:

* ключ `key_people` присутствует в порядке вывода, но не заполняется и поэтому не отображается;
* блок «Бюро пропусков» в текущем представлении не отображается (используется неопределённая переменная `$list_windowsGuest`), в объём настоящего ТЗ не входит;
* ссылки ведут на старые страницы модулей `identifier` и `people`, работающие напрямую с Firebird; их замена в объём не входит.

### 3.3. Правила расчёта текущей реализации

**Сотрудники и карты** — `Model_Summary::peopleCounts()`:

```sql
SELECT
    COUNT(DISTINCT P.ID_PEP) AS PEOPLE_TOTAL,
    SUM(CASE WHEN P."ACTIVE" = 0 THEN 1 ELSE 0 END) AS PEOPLE_INACTIVE,
    SUM(CASE WHEN P."ACTIVE" > 0 THEN 1 ELSE 0 END) AS PEOPLE_ACTIVE,
    SUM(CASE WHEN C.ID_PEP IS NULL THEN 1 ELSE 0 END) AS PEOPLE_WITHOUT_CARD,
    (SELECT COUNT(*) FROM CARD WHERE ID_CARDTYPE = 1) AS CARD_TYPE1_TOTAL,
    (SELECT COUNT(*) FROM CARD WHERE TIMEEND < CURRENT_TIMESTAMP AND "ACTIVE" > 0) AS CARD_EXPIRED,
    (SELECT COUNT(*) FROM CARD WHERE TIMEEND < '25.12.2026') AS CARD_EXPIRED_ON_DATE,
    (SELECT COUNT(*) FROM CARD WHERE "ACTIVE" = 0) AS CARD_INACTIVE
FROM PEOPLE P
LEFT JOIN CARD C ON C.ID_PEP = P.ID_PEP AND C.ID_DB = P.ID_DB
```

Дата `25.12.2026` — фактическое значение `today + count_day_befor_end_time` на 25.11.2026.

**RFID** — `Service_Dashboard::checkRfidKeyFormat()`:

```sql
SELECT C.ID_CARD
FROM CARD C
WHERE (C.ID_CARD LIKE '%a%' OR C.ID_CARD LIKE '%b%' OR C.ID_CARD LIKE '%c%'
    OR C.ID_CARD LIKE '%d%' OR C.ID_CARD LIKE '%e%' OR C.ID_CARD LIKE '%f%')
  AND C.ID_CARDTYPE = 1
```

### 3.4. Конфигурация, влияющая на раздел

| Ключ | Значение на стенде | Влияние |
|---|---|---|
| `main_windows.windows1` | `true` | видимость раздела в старой панели |
| `count_day_befor_end_time` | `30` | горизонт «срок действия завершится до» |
| `city_name` | `ЖК Хедлайнер` | не влияет на раздел |

### 3.5. Выявленные дефекты текущей реализации

Дефекты важны потому, что при переносе нужно либо воспроизвести поведение, либо сознательно исправить его и зафиксировать в приёмке.

| ID | Дефект | Проявление | Решение в `restdashboard` |
|---|---|---|---|
| D-1 | Блок «Бюро пропусков» не получает данные | `getWin1Guest()` не вызывается, представление использует неопределённую переменную | вне объёма |
| D-2 | `PEOPLE_INACTIVE` считает строки, а не людей | при нескольких картах у сотрудника `SUM` по `LEFT JOIN` учитывает его несколько раз | исправляется: `Person/GetCount.inactive` |
| D-3 | Граница `CARD_EXPIRED_ON_DATE` | `TIMEEND < '25.12.2026'` означает «до 00:00», карты, истекающие в этот день, не учитываются; фильтр `ACTIVE` отсутствует | исправляется: активные карты в интервале `[сейчас; 25.12.2026 23:59:59]` |
| D-4 | `people_count` имеет fallback `22` | при ошибке SQL показывается 22 вместо ошибки | не воспроизводится: при отсутствии данных показывается ошибка/`—` |
| D-5 | Ссылки ведут на страницы с прямым SQL | новая панель зависит от старого стека | сохраняются как переходы к детализации; замена вне объёма |
| D-6 | Жёстко задан `ID_CARDTYPE = 1` | значение зависит от базы | выносится в конфиг `card_type_employee`, по умолчанию 1 |
| D-7 | RFID-проверка ищет только строчные `a–f` | карты с `A–F` не выявляются; сообщение говорит о «ABCDEF» | сохраняется паритет; расширение правила — отдельный вопрос Q-4 |
| D-8 | Учитываются карты без владельца | `CARD` без строки `PEOPLE` попадает в старые счётчики | API такие карты не видит; ограничение зафиксировано в п. 4.5 и Q-2 |

### 3.6. Вывод по обследованию

Раздел состоит из восьми показателей. Четыре из них API 1.8.0 отдаёт готовыми агрегатами (`Person/GetCount`, `Identifier/GetCount`), ещё четыре требуют обхода карт:

* срок действия (два показателя);
* сотрудники без карты;
* неправильный формат RFID.

Это не недостаток реализации модуля, а следствие контракта API: фильтр `timeend` в `Identifier/GetCount` работает как «точная дата/день», а не как «меньше даты»; anti-join и поиск по шаблону `ID_CARD` в API отсутствуют.

---

## 4. Анализ Artonit REST API v2

### 4.1. Паспорт API

| Параметр | Значение |
|---|---|
| Заголовок Swagger | `Artonit Rest API v2` |
| Версия | 1.8.0 |
| Префикс | `/api/v2` |
| Маршрутов / операций | 23 / 33 |
| Теги | `AccessV2`, `EventV2`, `IdentifierTypeV2`, `IdentifierV2`, `OrganizationV2`, `PersonV2` |
| Аутентификация | `GET /login?username=...&pasword=...` → JWT; заголовок `Authorization: Bearer <token>` |
| Используемые методы | анонимные GET (без `[Authorize]`) |
| Формат успешных ответов | JSON, UTF-8 |
| Формат списков | `{"context":[...],"pageIndex":0,"pageSize":10,"pageCount":N}`; `pageCount` — число страниц |
| Формат ошибок | `{"error":"...","message":"...","details":[{"field":"...","issue":"..."}]}` |
| Кодировка | UTF-8 |

Версии методов:

* `Person/GetCount` появился в API 1.7.0;
* `Identifier/GetCount` и расширенные фильтры `Identifier/GetList` (`timeend`, `active`, `id_cardtype`, `createdat`) появились в API 1.8.0.

Модуль требует API **не ниже 1.8.0**. На 1.7.0 метод `Identifier/GetCount` вернёт `404`, и модуль покажет ошибку — это ожидаемое поведение, а не деградация на SQL.

### 4.2. Методы, используемые модулем

| № | Метод | Маршрут | Параметры | Назначение |
|---|---|---|---|---|
| 1 | GET | `/api/v2/Person/GetCount` | нет | всего/активных/неактивных сотрудников |
| 2 | GET | `/api/v2/Identifier/GetCount` | нет | всего карт (оценка объёма, сверка) |
| 3 | GET | `/api/v2/Identifier/GetCount` | `id_cardtype=1` | всего карт типа 1 |
| 4 | GET | `/api/v2/Identifier/GetCount` | `active=0` | неактивных карт |
| 5 | GET | `/api/v2/Identifier/GetList` | `pageIndex`, `pageSize=1000` | постраничное чтение карт |

Все методы идемпотентны, только читают данные, не требуют тела запроса и токена.

### 4.3. Контракт используемых методов

#### 4.3.1. `GET /api/v2/Person/GetCount`

Параметров нет. Ответ:

```json
{
  "total": 79,
  "active": 79,
  "inactive": 0
}
```

| Поле | Тип | Смысл |
|---|---|---|
| `total` | integer | `COUNT(*)` по `PEOPLE` |
| `active` | integer | `PEOPLE.ACTIVE = 1` |
| `inactive` | integer | `PEOPLE.ACTIVE = 0` |

Правила:

* считается вся таблица `PEOPLE`, без фильтра по `ID_DB`, организации и пагинации;
* запись с `ACTIVE IS NULL` входит в `total`, но не входит в `active`/`inactive`;
* ошибка БД — `500 INTERNAL_ERROR`;
* токен не требуется.

#### 4.3.2. `GET /api/v2/Identifier/GetCount`

Параметры (все необязательные):

| Параметр | Поле | Правило |
|---|---|---|
| `tabnum` | `PEOPLE.TABNUM` | точное совпадение; пробелы обрезаются; до 50 символов |
| `timestart` | `CARD.TIMESTART` | дата/дата-время; только дата — весь день; дата-время — точное совпадение |
| `timeend` | `CARD.TIMEEND` | дата/дата-время; только дата — весь день; дата-время — точное совпадение; `NULL` не попадает |
| `active` | `CARD.ACTIVE` | `1/true/active`, `0/false/inactive`, пусто/`all`/`any` — все |
| `id_cardtype` | `CARD.ID_CARDTYPE` | целое неотрицательное |
| `createdat` | `CARD.CREATEDAT` | дата/дата-время; аналогично `timeend` |

Ответ:

```json
{
  "total": 0,
  "active": 0,
  "inactive": 0
}
```

| Поле | Смысл |
|---|---|
| `total` | количество записей `CARD` после всех фильтров |
| `active` | из них `ACTIVE = 1` |
| `inactive` | из них `ACTIVE = 0` |

Правила:

* все три числа считаются после применения всех фильтров, включая `active`;
* без фильтров — статистика по всей таблице `CARD`;
* записи с `ACTIVE IS NULL` входят в `total`, но не в `active`/`inactive`;
* используется соединение `CARD c JOIN PEOPLE p ON p.ID_PEP = c.ID_PEP AND p.ID_DB = c.ID_DB`;
* **карты без владельца в выборку не попадают**;
* `timeend` — не диапазон: `timeend=2026-12-25` означает «весь день 25.12.2026», а не «все, что раньше 25.12.2026»;
* ошибка БД — `500 INTERNAL_ERROR`;
* токен не требуется.

#### 4.3.3. `GET /api/v2/Identifier/GetList`

Параметры:

| Параметр | Тип | Правило |
|---|---|---|
| `pageIndex` | integer | с 0; отрицательное → 0 |
| `pageSize` | integer | 1–1000; 0/отрицательное/нет → 10; >1000 → 1000 |
| `identifire` | string | точное совпадение с `CARD.ID_CARD` |
| `tabnum` | string | точное совпадение с `PEOPLE.TABNUM` |
| `timestart` | string | как в `GetCount` |
| `timeend` | string | как в `GetCount` |
| `active` | string | как в `GetCount` |
| `id_cardtype` | string | как в `GetCount` |
| `createdat` | string | как в `GetCount` |

Ответ:

```json
{
  "context": [
    {
      "id_card": "A1B2C3D4",
      "id_cardtype": "1",
      "tabnum": "T-100",
      "timestart": "2026-11-25T19:19:53.3811",
      "timeend": null,
      "active": 1,
      "createdat": "2026-11-20T10:00:00"
    }
  ],
  "pageIndex": 0,
  "pageSize": 10,
  "pageCount": 1
}
```

| Поле элемента | Тип | Источник |
|---|---|---|
| `id_card` | string | `CARD.ID_CARD` |
| `id_cardtype` | string | `CARD.ID_CARDTYPE` (строка для совместимости) |
| `tabnum` | string | `PEOPLE.TABNUM` |
| `timestart` | string (ISO 8601) | `CARD.TIMESTART` |
| `timeend` | string \| null | `CARD.TIMEEND` |
| `active` | number \| null | `CARD.ACTIVE` |
| `createdat` | string \| null | `CARD.CREATEDAT` |

Правила:

* `pageCount` считается от количества записей после фильтров;
* записи упорядочены по `CARD.ID_CARD`, затем `CARD.ID_DB`; страницы не пересекаются;
* `id_pep` не возвращается;
* без фильтров метод возвращает все карты, доступные через соединение с `PEOPLE`;
* ошибка БД — `500 INTERNAL_ERROR`;
* токен не требуется.

### 4.4. Аутентификация

Используемые GET-методы не имеют атрибута `[Authorize]` и проверены без токена. Модуль работает **анонимно**.

Если в конкретной установке API закроют авторизацией, предусмотреть необязательный статический токен:

* конфиг `api.token` (по умолчанию пусто);
* переменная окружения `RESTDASHBOARD_API_TOKEN`;
* при непустом значении добавлять `Authorization: Bearer <token>`;
* логин-форму и хранение пароля в модуле не реализовывать.

### 4.5. Gap-анализ: что API не умеет

| Показатель | Ближайший метод | Почему недостаточно | Решение в модуле |
|---|---|---|---|
| Количество пользователей | `Person/GetCount.total` | — | готовый агрегат |
| Удалённые пользователи | `Person/GetCount.inactive` | — | готовый агрегат |
| Всего карт типа 1 | `Identifier/GetCount?id_cardtype=1` | — | готовый агрегат |
| Неактивные карты | `Identifier/GetCount?active=0` | — | готовый агрегат |
| Срок действия завершится до даты | `Identifier/GetCount?timeend=...` | фильтр `timeend` — точная дата/день, не «меньше даты»; нельзя выразить интервал `[сейчас; горизонт]` | обход карт, фильтрация в PHP |
| Срок карты закончился | `Identifier/GetCount?timeend=...` | нельзя выразить «меньше текущего момента» | обход карт, фильтрация в PHP |
| Сотрудники без карты | `Identifier/GetCount?tabnum=...` | нет anti-join; перебор по каждому сотруднику — N+1 запросов | обход карт + множество `tabnum` |
| Неправильный формат RFID | `Identifier/GetList` | нет фильтра по шаблону `ID_CARD` | обход карт типа 1 + regex в PHP |
| Карты без владельца | — | `JOIN PEOPLE` скрывает их | ограничение API, зафиксировано в Q-2 |

Дополнительно:

* `Identifier/GetList` не отдаёт `id_pep`, поэтому связать карту с сотрудником можно только по `tabnum`;
* `Person/GetList` не отдаёт `ID_ORG`, но для раздела это не требуется;
* `Identifier/GetCount` не отдаёт `pageCount`, поэтому для оценки объёма используется `total`.

### 4.6. Рассмотренные альтернативы

| Вариант | Оценка | Решение |
|---|---|---|
| Добавить endpoint `Stat/People` в API | требует изменения C#-сервиса, выпуска новой версии и согласования контракта; в текущей задаче запрещено | отклонён |
| For each employee: `Identifier/GetCount?tabnum=...` | 79 запросов сейчас, тысячи в промышленной эксплуатации; квадратичная сложность | отклонён |
| For each day: `Identifier/GetCount?timeend=YYYY-MM-DD` | 30 запросов на горизонт, но не покрывает «просроченные» за все прошлые даты; не учитывает точное время | отклонён |
| Один обход `Identifier/GetList` + агрегация в PHP | 1 запрос на 1000 карт; покрывает все четыре «недостающих» показателя; контролируемые таймауты и кеш | принят |
| Прямой SQL из модуля | противоречит цели «через REST API» | отклонён |

### 4.7. Вывод

API 1.8.0 покрывает четыре показателя готовыми агрегатами и не покрывает четыре других. Полный обход карт — единственный способ получить все восемь показателей без изменения API. При текущем объёме (0 карт) обход стоит один запрос; при 10 000 карт — 10 запросов; при 50 000 — 50 запросов. Модуль обязан ограничивать объём, кешировать результат и корректно деградировать при превышении лимитов.

Endpoint `Stat`, предложенный в `TZ_Stat_API_v2.md`, для данного раздела **не требуется**: агрегаты по сотрудникам и картам уже есть в API 1.8.0, а оставшиеся показатели рассчитываются на клиенте. Это позволяет выпустить модуль без изменения C#-сервиса.

---

## 5. Требования к модулю `restdashboard`

### 5.1. Назначение, размещение, маршрут, меню

| Элемент | Значение |
|---|---|
| Модуль | `modules/restdashboard` |
| Контроллер | `Controller_Restdashboard` |
| Основной маршрут | `/city/index.php/restdashboard` (и `/city/restdashboard` при включённом rewrite) |
| Маршрут детализации | `/city/index.php/restdashboard/rfid` |
| Пункт меню | «Панель REST», иконка `fa-tachometer`, порядок 4 |
| Заголовок страницы | «Информация по сотрудникам и картам (REST)» |
| Шаблон | `Controller_Template`, `template` по умолчанию, полная ширина как в `restmancard` |
| Доступ | те же механизмы авторизации City, что и у остальных страниц; отдельная ACL не вводится |
| Подсветка меню | `$_SESSION['menu_active'] = 'restdashboard'` |
| Версия модуля | `RESTDASHBOARD_VERSION = '1.0.0'` |

Модуль не заменяет старую панель и не изменяет её. Обе страницы существуют параллельно. Старый переключатель `main_windows.windows1` не используется: у модуля свой `dashboard.enabled`.

Маршрут и меню в `modules/restdashboard/init.php`:

```php
Route::set('restdashboard', 'restdashboard(/<action>)')
    ->defaults(array(
        'controller' => 'Restdashboard',
        'action'     => 'index',
    ));

Kohana::$config->load('menu')->set('restdashboard', array(
    'title'    => 'Панель REST',
    'url'      => 'restdashboard',
    'icon'     => 'fa-tachometer',
    'order'    => 4,
    'disabled' => false,
));
```

### 5.2. Архитектура

```
Controller_Restdashboard
  ├─ action_index()  → Service_Restdashboard::getSummary()
  └─ action_rfid()   → Service_Restdashboard::getSummary()
        │
        ├─ Model_Restdashboard_Person::getCount()
        ├─ Model_Restdashboard_Card::getCount($filters)
        ├─ Model_Restdashboard_Card::eachPage($callback)
        │     └─ Model_Restdashboard_Api::get()
        └─ Cache (file)
  → View::factory('restdashboard/index' | 'restdashboard/rfid')
```

Слои:

| Слой | Класс | Ответственность |
|---|---|---|
| Контроллер | `Controller_Restdashboard` | query-параметры, вызов сервиса, обработка исключений, выбор view |
| Сервис | `Service_Restdashboard` | оркестрация, расчёт показателей, кеш, stale, предупреждения, тайминги |
| Модель сотрудников | `Model_Restdashboard_Person` | `Person/GetCount`, нормализация |
| Модель карт | `Model_Restdashboard_Card` | `Identifier/GetCount`, постраничный обход, нормализация строк |
| HTTP-клиент | `Model_Restdashboard_Api` | URL, GET, таймауты, JSON, ошибки, последняя ошибка |
| Представление | `views/restdashboard/index.php`, `rfid.php` | HTML, экранирование, ссылки, состояния |

### 5.3. Состав файлов

| Файл | Назначение |
|---|---|
| `modules/restdashboard/init.php` | версия, маршрут, пункт меню |
| `modules/restdashboard/config/restdashboard.php` | конфигурация API, расчётов, кеша, лимитов |
| `modules/restdashboard/classes/Controller/Restdashboard.php` | контроллер |
| `modules/restdashboard/classes/Service/Restdashboard.php` | сервис расчёта |
| `modules/restdashboard/classes/Model/Restdashboard/Api.php` | HTTP-клиент |
| `modules/restdashboard/classes/Model/Restdashboard/Person.php` | сотрудники |
| `modules/restdashboard/classes/Model/Restdashboard/Card.php` | карты |
| `modules/restdashboard/views/restdashboard/index.php` | основная панель |
| `modules/restdashboard/views/restdashboard/rfid.php` | список карт с неверным RFID |
| `modules/restdashboard/i18n/ru.php` | переводы (при необходимости) |
| `modules/restdashboard/README.md` | описание, установка, настройка, ограничения |
| `modules/restdashboard/guide/restdashboard/index.md` | справка пользователя |
| `modules/restdashboard/tests/` | unit/integration-тесты (P0 — unit, P1 — mock-сервер) |

Дополнительно изменить:

* `application/bootstrap.php` — добавить `'restdashboard' => MODPATH.'restdashboard'` (после `restmancard`);
* при необходимости — `modules/restdashboard/config/userguide.php` для справки.

### 5.4. Конфигурация

Файл `modules/restdashboard/config/restdashboard.php`:

```php
<?php defined('SYSPATH') or die('No direct script access.');

return array(

    'api' => array(
        'base_url'        => getenv('RESTDASHBOARD_API_URL') ?: 'http://localhost:5056',
        'token'           => getenv('RESTDASHBOARD_API_TOKEN') ?: '',
        'timeout'         => 10,
        'connect_timeout' => 3,
        'verify_ssl'      => false,
        'user_agent'      => 'ArtonitCity-RestDashboard/1.0.0',
    ),

    'dashboard' => array(
        // Выключатель раздела без удаления модуля
        'enabled' => true,

        // Тип карты, который считается «картой сотрудника»
        'card_type_employee' => 1,

        // null — взять count_day_befor_end_time из artonitcity_config
        'expiring_days'      => null,

        // null — использовать date_default_timezone_get()
        'timezone'           => null,

        // Часовой пояс, в котором API отдаёт «наивные» даты без смещения
        'api_timezone'       => null,

        // Кеш
        'cache_lifetime'     => 300,
        'stale_lifetime'     => 86400,

        // Обход карт
        'page_size'          => 1000,
        'max_pages'          => 50,
        'max_scan_seconds'   => 30,

        // Список карт с неверным RFID (для страницы детализации)
        'max_rfid_list'      => 1000,
        'rfid_pattern'       => '/[a-f]/',
    ),

    'links' => array(
        'cards'      => 'identifier',
        'expiring'   => 'people/find_card_late_next_week',
        'expired'    => 'people/find_card_late',
        'without'    => 'people/people_without_card',
        'inactive'   => 'people/find_unActiveCard',
        'rfid'       => 'restdashboard/rfid',
    ),
);
```

| Ключ | Тип | По умолчанию | Описание |
|---|---|---|---|
| `api.base_url` | string | `http://localhost:5056` | базовый URL API без завершающего `/` |
| `api.token` | string | пусто | необязательный Bearer-токен |
| `api.timeout` | int | 10 | таймаут одного запроса, секунды |
| `api.connect_timeout` | int | 3 | таймаут соединения, секунды |
| `api.verify_ssl` | bool | false | проверка SSL для https |
| `api.user_agent` | string | `ArtonitCity-RestDashboard/1.0.0` | User-Agent |
| `dashboard.enabled` | bool | true | выключатель раздела; false — сообщение без запросов к API |
| `dashboard.card_type_employee` | int | 1 | `ID_CARDTYPE` «карты сотрудника» |
| `dashboard.expiring_days` | int\|null | null | горизонт; null — из `artonitcity_config`, при отсутствии ключа — 30 |
| `dashboard.timezone` | string\|null | null | часовой пояс расчётов; null — системный |
| `dashboard.api_timezone` | string\|null | null | часовой пояс «наивных» дат API; null — как `timezone` |
| `dashboard.cache_lifetime` | int | 300 | TTL свежего кеша, секунды |
| `dashboard.stale_lifetime` | int | 86400 | TTL резервного кеша, секунды |
| `dashboard.page_size` | int | 1000 | размер страницы `GetList` (1–1000) |
| `dashboard.max_pages` | int | 50 | предохранитель обхода |
| `dashboard.max_scan_seconds` | int | 30 | предохранитель времени обхода |
| `dashboard.max_rfid_list` | int | 1000 | сколько карт хранить для детализации |
| `dashboard.rfid_pattern` | string | `/[a-f]/` | regex неверного формата |
| `links.*` | string | см. пример | маршруты детализации |

Валидация конфига:

* `enabled` — bool;
* `card_type_employee >= 0`;
* `expiring_days` в диапазоне 0–3650;
* `cache_lifetime` 0–86400 (0 — без кеша);
* `stale_lifetime >= cache_lifetime`;
* `page_size` 1–1000;
* `max_pages` 1–1000;
* `max_scan_seconds` 1–300;
* `max_rfid_list` 0–10000;
* `rfid_pattern` — корректный regex (проверять `@preg_match`);
* `timezone`/`api_timezone` — корректный идентификатор IANA или null; при ошибке — fallback на `date_default_timezone_get()` и предупреждение.

### 5.5. Требования к HTTP-клиенту

`Model_Restdashboard_Api`:

| Требование | Значение |
|---|---|
| Метод | только GET |
| URL | `rtrim(base_url, '/') . '/' . ltrim(path, '/')`, query через `http_build_query` |
| Заголовки | `Accept: application/json`, `User-Agent`, при токене — `Authorization: Bearer ...` |
| cURL | `CURLOPT_RETURNTRANSFER`, `CURLOPT_HTTPGET`, `CURLOPT_CONNECTTIMEOUT`, `CURLOPT_TIMEOUT`, `CURLOPT_FOLLOWLOCATION = false` |
| SSL | `CURLOPT_SSL_VERIFYPEER` / `VERIFYHOST` по конфигу |
| Кодировка | не выполнять `iconv`; API отдаёт UTF-8 |
| Разбор | `json_decode($raw, TRUE)`, проверка `json_last_error()`; UTF-8 BOM удалить перед разбором |
| Ошибки | `curl_errno`, HTTP вне 2xx, пустое тело, невалидный JSON |
| Сообщение | `message` → `error` → очищенный текст ответа (не более 300 символов) |
| Последняя ошибка | `lastError()`: `status`, `data`, `url` |
| Повторы | не более одного повтора при сетевой ошибке/502/503/504 (P1); 401 — без повтора, показать ошибку |
| Логирование | URL, статус, длительность; без токена и номеров карт |

Клиент не должен бросать исключение на «пустой» успешный ответ списка: пустой `context` — валидные данные.

Ответы `GetCount` валидировать перед использованием:

* обязательные ключи `total`, `active`, `inactive` (для `Identifier/GetCount`) и `total`, `active`, `inactive` (для `Person/GetCount`);
* значения — целые неотрицательные числа; числовые строки допустимы, приводятся к `int`;
* отсутствие ключа, `null`, отрицательное значение или нечисловая строка — ошибка соответствующего агрегата, а не `0`.

### 5.6. Алгоритм сбора данных

#### 5.6.1. Общая схема

0. Если `dashboard.enabled = false`, вернуть признак «раздел выключен» без запросов к API.
1. Прочитать `Person/GetCount` → `people_total`, `people_inactive`.
2. Прочитать `Identifier/GetCount` (без фильтров) → `cards_total_all` (для оценки объёма и сверки).
3. Прочитать `Identifier/GetCount?id_cardtype={T}` → `cards_total_type1`.
4. Прочитать `Identifier/GetCount?active=0` → `cards_inactive`.
5. Если `cards_total_all` известен и `ceil(cards_total_all / page_size) > max_pages` — обход не выполнять, `scan_complete = false`.
6. Иначе постранично читать `Identifier/GetList` и агрегировать:
   * множество `tabnum` карт;
   * `cards_expired`, `cards_expiring`;
   * `rfid_invalid` и список карт для детализации;
   * счётчики для сверки: `type1`, `inactive`, `empty_tabnum`, `invalid_timeend`.
7. Проверить полноту обхода и сверки.
8. Рассчитать `people_without_card`.
9. Собрать предупреждения, закешировать результат.

#### 5.6.2. Псевдокод

```php
$now = time();
$tz = ...; // DateTimeZone
$horizon_date = date('Y-m-d', strtotime('today +' . $days . ' days'));
$horizon_end  = strtotime($horizon_date . ' 23:59:59');

$people = $person->getCount();          // total, active, inactive
$all    = $card->getCount(array());     // total, active, inactive
$type1  = $card->getCount(array('id_cardtype' => $card_type));
$inact  = $card->getCount(array('active' => 0));

$all_total      = isset($all['total']) ? (int) $all['total'] : null;
$type1_total    = isset($type1['total']) ? (int) $type1['total'] : null;
$inactive_total = isset($inact['inactive']) ? (int) $inact['inactive'] : null;

$warnings = array();

$scan = array(
    'pages' => 0,
    'rows' => 0,
    'tabnums' => array(),
    'expired' => 0,
    'expiring' => 0,
    'rfid' => 0,
    'rfid_cards' => array(),
    'type1' => 0,
    'inactive' => 0,
    'empty_tabnum' => 0,
    'invalid_timeend' => 0,
);

$page = 0;
$page_count = null;
$has_more = true;
$scan_complete = false;
$started = microtime(true);
$rows = array();

$expected_pages = ($all_total === null) ? null : (int) ceil($all_total / $page_size);
$skip_scan = ($expected_pages !== null && $expected_pages > $max_pages);

if ($skip_scan)
{
    $warnings[] = 'Карт больше, чем позволяет обойти лимит max_pages; показатели по срокам не рассчитаны.';
}
else
{
do {
    $response = $api->get('/api/v2/Identifier/GetList', array(
        'pageIndex' => $page,
        'pageSize'  => $page_size,
    ));

    $rows = isset($response['context']) && is_array($response['context'])
        ? $response['context'] : array();

    foreach ($rows as $row) {
        if ( ! is_array($row))
        {
            $warnings[] = 'API вернул строку карты не в виде объекта; строка пропущена.';
            continue;
        }

        $scan['rows']++;
        $tabnum = trim((string) Arr::get($row, 'tabnum', ''));
        $id_card = trim((string) Arr::get($row, 'id_card', ''));
        $type = (int) Arr::get($row, 'id_cardtype', 0);
        $active = Arr::get($row, 'active', null);
        $timeend_raw = Arr::get($row, 'timeend', null);

        $is_active   = ($active === 1 || $active === '1' || $active === true);
        $is_inactive = ($active === 0 || $active === '0' || $active === false);

        if ($tabnum === '') {
            $scan['empty_tabnum']++;
        } else {
            $scan['tabnums'][$tabnum] = true;
        }

        if ($is_inactive) {
            $scan['inactive']++;
        }

        // Срок действия считается по картам ВСЕХ типов (паритет со старым SQL).
        if ($is_active && $timeend_raw !== null) {
            $timeend = parse_api_datetime($timeend_raw, $tz);
            if ($timeend === null) {
                $scan['invalid_timeend']++;
            } elseif ($timeend < $now) {
                $scan['expired']++;
            } elseif ($timeend <= $horizon_end) {
                $scan['expiring']++;
            }
        }

        // RFID — только карты типа T.
        if ($type === $card_type) {
            $scan['type1']++;

            if ($id_card !== '' && preg_match($rfid_pattern, $id_card)) {
                $scan['rfid']++;
                if (count($scan['rfid_cards']) < $max_rfid_list) {
                    $scan['rfid_cards'][] = normalize_rfid_row($row);
                }
            }
        }
    }

    $scan['pages']++;

    if (array_key_exists('pageCount', $response) && is_numeric($response['pageCount'])) {
        $page_count = max(0, (int) $response['pageCount']);
    } else {
        $page_count = null;
        $warnings[] = 'API не вернул pageCount; обход продолжен до пустой страницы.';
    }

    if ($page_count === 0 && ! empty($rows)) {
        // Противоречие: страница не пуста, но pageCount = 0.
        $page_count = null;
        $warnings[] = 'API вернул непустую страницу при pageCount = 0; обход продолжен до пустой страницы.';
    }

    $page++;
    $elapsed = microtime(true) - $started;
    $stopped_by_limit = ($page >= $max_pages || $elapsed >= $max_scan_seconds);
    $has_more = ! empty($rows) && ($page_count === null || $page < $page_count);
} while ($has_more && ! $stopped_by_limit);
}

if ( ! $skip_scan)
{
    if ($page_count === null)
    {
        $scan_complete = empty($rows);
    }
    else
    {
        $scan_complete = ($page >= $page_count);
    }

    if ($stopped_by_limit && $has_more)
    {
        $scan_complete = false;
    }

    if ($page_count !== null && $page < $page_count && empty($rows))
    {
        $warnings[] = 'API вернул пустую страницу раньше pageCount; обход считается неполным.';
    }
}

$scan_consistent = ( ! $skip_scan && ($all_total === null || $scan['rows'] === $all_total));

if ( ! $skip_scan)
{
    if ( ! $scan_consistent)
    {
        $warnings[] = 'Количество карт изменилось во время обхода; показатели приблизительные.';
    }

    if ($type1_total !== null && $scan['type1'] !== $type1_total)
    {
        $warnings[] = 'Сверка по картам типа ' . $card_type . ' не совпала: '
            . $scan['type1'] . ' из ' . $type1_total . '.';
    }

    if ($inactive_total !== null && $scan['inactive'] !== $inactive_total)
    {
        $warnings[] = 'Сверка по неактивным картам не совпала: '
            . $scan['inactive'] . ' из ' . $inactive_total . '.';
    }
}
```

Примечания:

* для `active` использовать строгую нормализацию: `1/'1'/true` — активна, `0/'0'/false` — неактивна, `null` — неизвестно;
* для `timeend` сравнивать timestamp в одном часовом поясе;
* `pageCount = 0` при непустом `context` — аномалия: продолжать до пустой страницы, но не более `max_pages`, и пометить предупреждением;
* если `context` пуст, обход завершён;
* срок действия (`expired`, `expiring`) считается по всем типам карт, RFID — только по `card_type_employee`.

#### 5.6.3. Полнота и согласованность обхода

`scan_complete` — признак того, что пагинация пройдена до конца:

* обход вообще начинался (если `ceil(cards_total_all / page_size) > max_pages`, он не начинается);
* цикл завершился не по `max_pages` и не по `max_scan_seconds`;
* `pageCount` известен и `page >= pageCount`, либо `pageCount` неизвестен и последняя страница пуста.

`scan_consistent` — признак того, что число прочитанных строк совпало с агрегатом:

* `cards_total_all` неизвестен, или `scan.rows == cards_total_all`.

Если `scan_complete = true`, а `scan_consistent = false`:

* добавить предупреждение «Количество карт изменилось во время обхода; показатели приблизительные»;
* показатели не обнулять: они отражают состояние на момент обхода.

Если `scan_complete = false`:

* `cards_expiring`, `cards_expired`, `people_without_card`, `rfid_invalid` = `null`;
* в UI показать `—` и предупреждение;
* заголовочные агрегаты из `GetCount` оставить.

#### 5.6.4. Расчёт «сотрудники без карты»

```php
if ($people_total !== null && $scan_complete) {
    $with_card = count($scan['tabnums']);
    $people_without_card = max(0, $people_total - $with_card);

    if ($with_card > $people_total) {
        $warnings[] = 'Количество сотрудников с картами превышает общее количество сотрудников; проверьте уникальность TABNUM.';
    }
    if ($scan['empty_tabnum'] > 0) {
        $warnings[] = 'Найдены карты без табельного номера; показатель «Сотрудники без карты» может быть завышен.';
    }
} else {
    $people_without_card = null;
}
```

### 5.7. Правила расчёта показателей

| № | Ключ | Отображаемое название | Формула | Источник |
|---|---|---|---|---|
| 1 | `people_total` | Количество пользователей | `Person/GetCount.total` | API |
| 2 | `people_inactive` | Количество удалённых пользователей | `Person/GetCount.inactive` | API |
| 3 | `cards_total_type1` | Всего карт у сотрудников | `GET Identifier/GetCount?id_cardtype=T` → `.total` | API |
| 4 | `cards_expiring` | Срок действия завершится до `H` | количество карт **всех типов**: `TIMEEND != NULL`, `ACTIVE = 1`, `now <= TIMEEND <= H 23:59:59` | обход |
| 5 | `cards_expired` | Срок карты закончился | количество карт **всех типов**: `TIMEEND != NULL`, `ACTIVE = 1`, `TIMEEND < now` | обход |
| 6 | `people_without_card` | Сотрудники без карты | `people_total - count(distinct tabnum с любой картой)` | API + обход |
| 7 | `cards_inactive` | Количество неактивных карт | `GET Identifier/GetCount?active=0` → `.inactive` | API |
| 8 | `rfid_invalid` | Неправильный формат RFID | количество карт типа `T`, у которых `ID_CARD` соответствует `rfid_pattern` | обход |

Обозначения:

* `T` — `card_type_employee` (по умолчанию 1);
* `H` — дата «сегодня + `expiring_days`», отображается `d.m.Y`;
* `now` — один timestamp, зафиксированный в начале расчёта;
* `TIMEEND` — локальное время API в часовом поясе `api_timezone`/`timezone`.

Порядок вывода на экран — как в старой панели: 1, 2, 3, 4, 5, 6, 7, затем RFID.

Если по Q-1 будет принят вариант «как в старом SQL», меняется только формула 4: `TIMEEND < H 00:00` без фильтра `ACTIVE`; формула 5 остаётся `TIMEEND < now AND ACTIVE = 1`. Это должно быть отражено в тестах U-06…U-09 и в приёмке.

#### 5.7.1. Отличия от старой панели

| Показатель | Старая панель | `restdashboard` | Причина |
|---|---|---|---|
| Количество пользователей | `COUNT(DISTINCT ID_PEP)` | `Person/GetCount.total` | то же самое |
| Удалённые пользователи | `SUM` по `LEFT JOIN`, мог завышаться | `Person/GetCount.inactive` | исправление D-2 |
| Всего карт типа 1 | все `CARD` типа 1 | `Identifier/GetCount` по `PEOPLE` | API не видит карты без владельца |
| Срок действия завершится до | `< H 00:00`, без `ACTIVE` | `[now; H 23:59:59]`, только `ACTIVE=1` | исправление D-3, Q-1 |
| Срок карты закончился | `TIMEEND < now`, `ACTIVE > 0` | `TIMEEND < now`, `ACTIVE=1` | в типовой схеме 0/1; карты без владельца не видны |
| Сотрудники без карты | anti-join `PEOPLE`/`CARD` | `people_total - distinct tabnum` | API не умеет anti-join |
| Неактивные карты | все `CARD.ACTIVE=0` | `Identifier/GetCount?active=0` | карты без владельца не видны |
| RFID | тип 1, строчные `a–f` | тот же regex | паритет, Q-4 |

На текущей базе (0 карт) контрольные значения совпадают; перечисленные отличия проявятся при наличии карт и должны быть учтены в приёмке.

### 5.8. Граничные случаи

| Случай | Правило |
|---|---|
| `TIMEEND = NULL` | не истекает и не попадает в «завершится»; карта учитывается в общем количестве |
| `ACTIVE = NULL` | не считается активной; в `cards_expired`/`cards_expiring` не попадает; в `cards_total_type1` попадает (как `total`) |
| `ACTIVE = 0` и `TIMEEND < now` | не считается «закончился» (старая формула требует `ACTIVE > 0`), но считается неактивной |
| `TIMEEND` ровно `now` | не «закончился» (`<`), попадает в «завершится» (`>=`) |
| `TIMEEND` ровно `H 23:59:59` | попадает в «завершится» (`<=`) |
| `TIMEEND` после `H 23:59:59` | не попадает |
| Невалидная дата `TIMEEND` | карта пропускается в расчёте сроков, счётчик `invalid_timeend`, предупреждение |
| Пустой `TABNUM` у карты | карта не добавляет `tabnum` в множество; счётчик `empty_tabnum`, предупреждение |
| Дубли `TABNUM` у сотрудников | показатель «без карты» приблизительный; предупреждение, требуется уникальный индекс |
| Несколько `ID_DB` | `Person/GetCount` считает все `PEOPLE`, `Identifier/GetList` — все `CARD` с join по `ID_DB`; при нескольких базах возможны расхождения; на стенде одна база |
| Карта без владельца | API не возвращает; расхождение со старым SQL, ограничение Q-2 |
| Карт больше `max_pages * page_size` | обход не выполняется/обрывается, производные показатели `null` |
| Карты изменились во время обхода | `scan.rows != cards_total_all` → предупреждение, показатели приблизительные |
| `pageCount` отсутствует/0 при непустом `context` | продолжать до пустой страницы, но не более `max_pages`; предупреждение |
| `Person/GetCount` недоступен | `people_total`, `people_inactive`, `people_without_card` = `null`; карты показываются |
| `Identifier/GetCount` недоступен | соответствующий агрегат = `null`; обход может дать производные |
| Обход недоступен | производные = `null`; агрегаты показываются |
| API недоступен полностью | stale-кеш или ошибка без цифр |

Перед внедрением рекомендуется проверить дубли `TABNUM` (вне модуля, силами DBA):

```sql
SELECT TABNUM, COUNT(*)
FROM PEOPLE
GROUP BY TABNUM
HAVING COUNT(*) > 1;
```

Если дубли есть, показатель «Сотрудники без карты» будет приблизительным; API v2 для одиночных методов в таком случае возвращает `409 AMBIGUOUS_TABNUM`.

Проверка карт без владельца (для объяснения расхождения со старой панелью):

```sql
SELECT COUNT(*)
FROM CARD c
LEFT JOIN PEOPLE p
  ON p.ID_PEP = c.ID_PEP AND p.ID_DB = c.ID_DB
WHERE p.ID_PEP IS NULL;
```

#### 5.8.1. Часовой пояс и разбор дат

API отдаёт даты в ISO 8601. Для `CARD.TIMEEND` используется формат без смещения (см. пример ответа `GetList`):

```
2026-12-31T23:59:59
2026-11-25T19:19:53.3811
```

Правила:

1. Если строка содержит `Z` или смещение (`+03:00`), использовать его и привести время к часовому поясу City.
2. Если смещения нет, считать строку локальным временем API в часовом поясе `api_timezone`; если он не задан — в `timezone`; если не задан и он — в `date_default_timezone_get()`.
3. Разбор выполнять через `DateTime`/`DateTimeZone`, а не через `date()`/`strtotime()` без пояса; для сравнения использовать Unix timestamp.
4. `now` фиксировать один раз в начале расчёта, чтобы все карты сравнивались с одним моментом.
5. Горизонт `H` считать как `today + expiring_days` в часовом поясе City, конец дня — `23:59:59`.
6. Если API и City находятся в разных часовых поясах и `api_timezone` не настроен, граница срока сдвинется на разницу поясов. Это ошибка конфигурации, а не данных.

Пример:

```php
$tz = new DateTimeZone($timezone);          // часовой пояс City
$api_tz = new DateTimeZone($api_timezone);  // часовой пояс «наивных» дат API

$dt = new DateTime($timeend_raw, $api_tz);  // смещение в строке имеет приоритет
$dt->setTimezone($tz);
$timeend = $dt->getTimestamp();
```

Для `TIMEEND = NULL` разбор не выполняется: карта не истекает.

### 5.9. Кеш, stale и обновление

| Параметр | Значение |
|---|---|
| Хранилище | `Cache::instance('file')`, каталог `application/cache` |
| Ключ | `restdashboard_summary_` + `sha1(version, base_url, card_type, days, tz, api_tz, page_size, max_pages, max_scan_seconds, max_rfid_list, rfid_pattern)` |
| TTL свежего | `cache_lifetime` (300 с) |
| TTL резервного | `stale_lifetime` (86400 с) |
| Инвалидация | `?refresh=1` удаляет оба ключа; смена версии/конфига меняет ключ |
| При ошибке API | читается резервный ключ; в ответе `stale = true`, `error`, `generated_at` |
| При отсутствии кеша | страница показывает ошибку, HTTP 200 |
| Кешируются | агрегаты, предупреждения, время, список RFID (не более `max_rfid_list`) |
| Не кешируются | токен, сырые страницы API, ФИО сотрудников |

Алгоритм:

```php
$cached = cache_get($key);
if (!$refresh && $cached && $cached['generated_ts'] + $cache_lifetime > time()) {
    return $cached;
}

$stale = cache_get($stale_key);

try {
    $fresh = compute();
    cache_set($key, $fresh, $cache_lifetime);
    cache_set($stale_key, $fresh, $stale_lifetime);
    return $fresh;
} catch (Exception $e) {
    if ($stale) {
        $stale['stale'] = true;
        $stale['error'] = $e->getMessage();
        return $stale;
    }
    throw $e;
}
```

Требования:

* не перезаписывать хороший кеш результатом с ошибкой;
* при `cache_lifetime = 0` свежий кеш не используется; резервный кеш можно не писать;
* `refresh=1` не должен приводить к повторному обходу чаще, чем раз в 10 секунд (P1, защита от спама);
* при недоступном кеше модуль работает без него, в лог пишется предупреждение;
* `generated_at` — `d.m.Y H:i:s` в часовом поясе City.

### 5.10. Обработка ошибок и деградация

| Ситуация | Поведение |
|---|---|
| HTTP 400 `VALIDATION_ERROR` | ошибка конфигурации/параметров; в UI «Ошибка параметров запроса к API», детали в лог |
| HTTP 401/403 | «API требует авторизации»; проверить `api.token`; без цифр |
| HTTP 404 | для используемых методов не ожидается; показать общую ошибку |
| HTTP 500 `INTERNAL_ERROR` | «Ошибка базы данных API»; stale или ошибка |
| Сетевая ошибка/таймаут | «REST API недоступен»; stale или ошибка |
| Невалидный JSON | «Некорректный ответ API»; stale или ошибка |
| Пустой ответ `GetCount` | считать ошибкой; агрегат `null` |
| Пустой `context` | валидные данные, 0 карт |
| Ошибка одной страницы обхода | прервать обход, производные `null`, агрегаты сохранить |
| Ошибка `Person/GetCount` | сотрудники `null`, карты считать |
| Ошибка `Identifier/GetCount` | соответствующий агрегат `null`, обход по возможности |
| Превышение лимитов | предупреждение, производные `null` |
| Ошибка шаблона/фатальная | HTTP 500, стандартная страница Kohana |

Требования:

* ошибка API не должна приводить к белой странице или HTTP 500;
* сообщение пользователю — короткое, без HTML и stack trace;
* полный текст — в `Kohana::$log` (без токена и номеров карт);
* при stale показывать возраст данных: «Данные на 25.11.2026 22:31:05, API недоступен».

### 5.11. Требования к представлению

#### 5.11.1. Компоновка

Одна панель Bootstrap 3:

```
┌─────────────────────────────────────────────────────────────┐
│ Информация по сотрудникам и картам  [REST API v2]           │
│ Источник: localhost:5056   Обновлено: 25.11.2026 22:31:05  [Обновить] │
├─────────────────────────────────────────────────────────────┤
│ СКУД                                                        │
│ Количество пользователей                        79          │
│ Количество удалённых пользователей              0           │
│ Всего карт у сотрудников                        0           │
│ Срок действия завершится до 25.12.2026          0           │
│ Срок карты закончился                           0           │
│ Сотрудники без карты                            79          │
│ Количество неактивных карт                      0           │
│ ─────────────────────────────────────────────────────────── │
│ [Неправильный формат RFID]                      0           │
└─────────────────────────────────────────────────────────────┘
```

Требования:

* ширина: `col-md-6 col-lg-5`, максимум 600 px, на мобильных — 100%;
* заголовок — `panel-title`, badge `REST API v2`;
* справа — источник (host), время обновления, кнопка «Обновить» (`?refresh=1`);
* тело — `<b>СКУД</b><br>`, строки в порядке п. 5.7;
* после строк — `<hr>` и RFID-метка;
* внизу — `small text-muted`: версия модуля, источник, предупреждения;
* без JavaScript обязательна работоспособность; `title`-подсказки нативные.

#### 5.11.2. Строки и ссылки

| Ключ | Ссылка | Условие |
|---|---|---|
| `people_total` | — | — |
| `people_inactive` | — | — |
| `cards_total_type1` | `links.cards` (`identifier`) | если значение не `null` |
| `cards_expiring` | `links.expiring` | если значение не `null` |
| `cards_expired` | `links.expired` | если значение не `null` |
| `people_without_card` | `links.without` | если значение не `null` |
| `cards_inactive` | `links.inactive` | если значение не `null` |
| `rfid_invalid` | `links.rfid` (`restdashboard/rfid`) | если значение не `null` |

Ссылки открываются в том же окне. Если значение `null`, ссылка не выводится, показывается `—`.

Ссылки ведут на старые страницы `identifier`/`people`, которые считают по прежним SQL-правилам. Поэтому число в счётчике и число строк в открывшемся списке могут отличаться (D-3, карты без владельца). Это осознанное ограничение v1.0: замена страниц детализации на REST-аналоги в объём не входит (Q-5). В README/guide добавить пояснение.

#### 5.11.3. Состояния

| Состояние | Отображение |
|---|---|
| Успех | все значения, зелёная/серая RFID-метка, время обновления |
| Частичный успех | значения доступных показателей, `—` для `null`, жёлтый alert с предупреждениями |
| Stale | значения из кеша, жёлтый alert «Данные устарели», время |
| Ошибка без кеша | красный alert с текстом, кнопка «Повторить», цифры не показываются |
| Обход неполный | заголовочные агрегаты, `—` для производных, предупреждение |
| Нет карт | все счётчики 0, RFID 0, без ошибок |
| Раздел выключен (`enabled = false`) | сообщение «Вывод информации запрещён в настройках», ссылка на `setting`, запросы к API не выполняются |

#### 5.11.4. Цвета и подсказки

* RFID: `label-danger` при `> 0`, `label-success` при `0`, `label-default` при `null`;
* остальные значения — обычный текст (паритет со старой панелью);
* `title` для каждого показателя с формулой и источником (см. таблицу ниже);
* числа форматировать `number_format($value, 0, '', ' ')` (пробел как разделитель тысяч);
* все строки экранировать `HTML::chars`.

| Показатель | Подсказка |
|---|---|
| Количество пользователей | «Все записи PEOPLE, включая служебные, гостей и архив. Источник: Person/GetCount.total» |
| Количество удалённых пользователей | «PEOPLE.ACTIVE = 0. Источник: Person/GetCount.inactive» |
| Всего карт у сотрудников | «CARD.ID_CARDTYPE = T (по умолчанию 1), все статусы; как в старой панели, включая гостей/архив. Карты без владельца API не видит» |
| Срок действия завершится до | «Активные карты всех типов с TIMEEND в интервале [сейчас; дата 23:59:59]» |
| Срок карты закончился | «Активные карты всех типов с TIMEEND < сейчас» |
| Сотрудники без карты | «PEOPLE без ни одной CARD, все организации; оценка по TABNUM» |
| Количество неактивных карт | «CARD.ACTIVE = 0, все типы» |
| Неправильный формат RFID | «Карты типа 1, содержащие строчные латинские a–f» |

### 5.12. Страница детализации RFID

Маршрут: `/city/index.php/restdashboard/rfid`.

| Элемент | Значение |
|---|---|
| Заголовок | «Карты с неправильным форматом RFID» |
| Источник | тот же кеш/расчёт, что и панель |
| Таблица | №, `ID_CARD`, `TABNUM`, `ID_CARDTYPE`, `TIMEEND`, `ACTIVE` |
| Сортировка | по `ID_CARD` (как в API) |
| Лимит | `max_rfid_list` (1000); при превышении — предупреждение «показаны первые N» |
| Пусто | «Карт с неправильным форматом не найдено» |
| Неполный обход | предупреждение, таблица не выводится (данные неполные) |
| Ошибка API | alert-danger, кнопка «Повторить» |
| Обновление | `?refresh=1` |
| Ссылки | «Назад к панели» → `restdashboard` |
| Экспорт | не входит в объём |

### 5.13. Локализация и кодировка

* все подписи — через `__()`;
* использовать существующие ключи `modules/basis/i18n/ru.php`, где они совпадают (`key_people_delete`, `count_card_late_next_week` и т. п.);
* новые ключи — в `modules/restdashboard/i18n/ru.php`;
* язык по умолчанию — `ru-ru` (как в `application/bootstrap.php`);
* вход/выход — UTF-8; API — UTF-8; `iconv` не применять;
* числа — `number_format`, даты — `d.m.Y H:i:s`;
* не вставлять в HTML неэкранированные значения.

### 5.14. Логирование и наблюдаемость

| Событие | Уровень | Данные |
|---|---|---|
| Ошибка API | `Log::ERROR` | URL без query-токена, HTTP-статус, длительность, текст (без карт) |
| Обход прерван | `Log::WARNING` | причина, страница, обработано карт, время |
| Расхождение сверки | `Log::WARNING` | `rows`, `cards_total_all`, `type1`, `inactive` |
| Медленный расчёт | `Log::INFO` | длительность > 5 с, число страниц |
| Успех | не логировать | — |

Требования:

* не логировать токен, пароли, полные списки карт;
* номера карт в сообщениях маскировать (`A1B2****`) или не указывать;
* использовать `Kohana::$log`, файлы — `application/logs`;
* в UI не показывать stack trace.

### 5.15. Безопасность

* базовый URL и токен — только из конфига/окружения, не из query;
* `refresh` — boolean, не URL и не путь;
* все выходные строки экранировать;
* ссылки строить через `URL::site`, не из пользовательского ввода;
* API-вызовы выполняются на сервере, браузер не обращается к API напрямую;
* не хранить пароль API в репозитории; токен — только env/config;
* кеш содержит агрегаты и (опционально) номера карт; каталог `application/cache` защищён `.htaccess`/rewrite;
* при https включить `verify_ssl = true`;
* не добавлять публичных JSON-эндпоинтов модуля без отдельной авторизации;
* CSRF не требуется: все действия — GET, побочный эффект только очистка кеша.

### 5.16. Производительность

| Сценарий | Требование |
|---|---|
| Тёплый кеш | страница отдаётся без запросов к API, ≤ 200 мс |
| Холодный кеш, 0–1000 карт | ≤ 2 с |
| Холодный кеш, 10 000 карт | ≤ 5 с |
| Холодный кеш, 50 000 карт | ≤ 20 с или корректное предупреждение |
| Память | ≤ 64 МБ при 100 000 `tabnum`; не хранить все строки карт |
| Запросы | 4 count + `ceil(C / 1000)` страниц, где `C` — карт, видимых API |
| Кеш | 300 с; не более 12 холодных расчётов в час на один конфиг |
| Таймауты | 10 с на запрос, 30 с на обход; не превышать `max_execution_time` |

Оптимизации:

* не накапливать строки карт, агрегировать на лету;
* хранить только множество `tabnum` и ограниченный список RFID;
* при `cards_total_all > max_pages * page_size` не начинать обход;
* использовать `pageSize=1000` (максимум API);
* кешировать итог, а не страницы.

### 5.17. Совместимость PHP 5.6 / Kohana 3.3

* синтаксис PHP 5.6: без `??`, `<=>`, скалярных type hint, return type, anonymous classes, `void`;
* массивы — `array()`, допустим короткий синтаксис `[]` (PHP 5.4+), но для единообразия — `array()`;
* классы — `Controller_*`, `Model_*`, `Service_*` в соответствии с автозагрузчиком Kohana;
* запросы — `Request`, `Arr`, `HTML`, `URL`, `Cache`, `Log`, `Kohana::$config`;
* не использовать `PDO`/`DB` для данных раздела;
* cURL — расширение `curl` (проверено);
* JSON — `json_decode`/`json_encode` с `JSON_UNESCAPED_UNICODE` при необходимости;
* не использовать `str_contains`, `array_key_first`, `mb_str_split` и другие функции PHP 7+;
* не добавлять Composer-зависимости и новые расширения PHP;
* проверить `php -l` всех файлов.

### 5.18. Документация

`modules/restdashboard/README.md`:

* назначение и границы;
* установка (включение модуля, меню, маршрут);
* конфигурация (таблица ключей);
* правила расчёта и отличия от старой панели;
* ограничения API (карты без владельца, дубли `TABNUM`, часовой пояс);
* кеш и обновление;
* диагностика ошибок;
* версия и история изменений.

`guide/restdashboard/index.md`:

* как открыть страницу;
* что означает каждый показатель;
* почему цифры могут отличаться от старой панели;
* что делать при ошибке.

### 5.19. Установка и откат

Установка:

1. Разместить каталог `modules/restdashboard` в `C:\xampp\htdocs\city\modules`.
2. Добавить в `application/bootstrap.php` строку:

   ```php
   'restdashboard' => MODPATH.'restdashboard',
   ```

   после `'restmancard' => MODPATH.'restmancard',`.
3. Проверить `modules/restdashboard/config/restdashboard.php`: `api.base_url`, при необходимости `api.token`, `timezone`.
4. Очистить `application/cache` (или дать права на запись).
5. Открыть `/city/index.php/restdashboard`; пункт меню появляется из `init.php`.
6. Проверить `?refresh=1` и страницу `/city/index.php/restdashboard/rfid`.

Откат:

1. Удалить строку `restdashboard` из `application/bootstrap.php`.
2. Очистить `application/cache`.
3. При необходимости удалить каталог модуля.

Откат не требует изменения схемы БД и не влияет на старую панель.

---

## 6. Модель данных

### 6.1. Результат сервиса

`Service_Restdashboard::getSummary($refresh = false)` возвращает массив:

| Поле | Тип | Nullable | Описание |
|---|---|---|---|
| `generated_at` | string | нет | `d.m.Y H:i:s` |
| `generated_ts` | int | нет | Unix timestamp расчёта |
| `disabled` | bool | нет | раздел выключен конфигом |
| `stale` | bool | нет | данные из резервного кеша |
| `stale_age` | int | да | возраст stale, секунды |
| `error` | string | да | краткий текст ошибки для UI |
| `warnings` | array | нет | список предупреждений |
| `scan_complete` | bool | нет | обход карт полный |
| `scan_consistent` | bool | нет | число строк совпало с `cards_total_all` |
| `metrics` | array | нет | 8 показателей (int\|null) |
| `derived` | array | нет | горизонт, счётчики, сверка |
| `rfid_cards` | array | нет | карты для детализации |

`metrics`:

```php
array(
    'people_total'         => 79,
    'people_inactive'      => 0,
    'cards_total_type1'    => 0,
    'cards_expiring'       => 0,
    'cards_expired'        => 0,
    'people_without_card'  => 79,
    'cards_inactive'       => 0,
    'rfid_invalid'         => 0,
)
```

`derived`:

```php
array(
    'horizon_date'      => '25.12.2026',
    'horizon_end'       => '2026-12-25 23:59:59',
    'cards_total_all'   => 0,
    'cards_scanned'     => 0,
    'card_pages'        => 1,
    'type1_scanned'     => 0,
    'inactive_scanned'  => 0,
    'distinct_tabnums'  => 0,
    'empty_tabnum'      => 0,
    'invalid_timeend'   => 0,
    'scan_consistent'   => true,
    'scan_reason'       => null,
)
```

`rfid_cards` — массив:

```php
array(
    array(
        'id_card'    => 'a1b2c3d4',
        'tabnum'     => 'T-100',
        'id_cardtype'=> '1',
        'timeend'    => '2026-12-31T23:59:59',
        'active'     => 1,
    ),
)
```

### 6.2. Внутренние ошибки

Сервис не бросает исключение на частичную ошибку. Он возвращает `metrics[...] = null` и добавляет предупреждение. Исключение — только если не удалось получить ни один показатель и нет stale-кеша.

---

## 7. Тестирование и приёмка

### 7.1. Виды испытаний

| Вид | Что проверяет |
|---|---|
| Модульные | парсинг дат, классификация сроков, RFID, расчёт «без карты», пагинация |
| Интеграционные | реальный API 1.8.0, mock-сервер, ошибки 400/401/500, таймаут |
| UI/ручные | отображение, ссылки, состояния, экранирование, адаптивность |
| Регрессионные | старая панель, другие модули, API v1/v2 |
| Приёмочные | контрольные значения, дефекты, производительность |

### 7.2. Модульные тесты

| ID | Проверка | Ожидание |
|---|---|---|
| U-01 | `parse_api_datetime('2026-11-25T19:19:53.3811')` | timestamp, не `null` |
| U-02 | `parse_api_datetime('2026-11-25 19:19:53')` | timestamp |
| U-03 | `parse_api_datetime(null)`, `''`, `'bad'` | `null` |
| U-04 | `TIMEEND = null` | не expired, не expiring |
| U-05 | `TIMEEND < now`, `ACTIVE=1` | expired |
| U-06 | `TIMEEND = now`, `ACTIVE=1` | expiring |
| U-07 | `TIMEEND = H 23:59:59`, `ACTIVE=1` | expiring |
| U-08 | `TIMEEND = H + 1 сек` | не expiring |
| U-09 | `ACTIVE=0` при истёкшем `TIMEEND` | не expired, не expiring |
| U-10 | `ACTIVE=null` | не expired, не expiring |
| U-11 | `people_total=79`, карт с `tabnum` нет | `people_without_card=79` |
| U-12 | `people_total=10`, 3 уникальных `tabnum` | `7` |
| U-13 | `tabnums > people_total` | `0` + предупреждение |
| U-14 | `ID_CARD='a1b2'`, тип 1 | RFID invalid |
| U-15 | `ID_CARD='A1B2'`, тип 1 | не invalid (паритет) |
| U-16 | `ID_CARD='1234'` | не invalid |
| U-17 | `ID_CARD='A1B2G'` | не invalid (паритет) |
| U-18 | тип 2 с `a1b2` | не invalid |
| U-19 | `pageCount=2`, страницы 0/1 | 2 запроса, полный обход |
| U-20 | `pageCount=0`, `context=[]` | полный обход, 0 карт |
| U-21 | `pageCount` отсутствует, `context` непустой | продолжение до пустой страницы/лимита |
| U-22 | `max_pages=1`, `pageCount=3` | `scan_complete=false`, производные `null` |
| U-23 | `cards_total_all=5000`, `page_size=1000`, `max_pages=3` | обход не начинается, `scan_complete=false` |
| U-24 | HTTP 500 | исключение/ошибка, без фатала |
| U-25 | невалидный JSON или отсутствует `total` в `GetCount` | ошибка агрегата, не `0` |
| U-26 | `context` не массив или строка не объект | ошибка/пропуск строки + предупреждение |
| U-27 | кеш hit | повторный вызов не делает HTTP |
| U-28 | `refresh=1` | кеш удаляется, HTTP выполняется |
| U-29 | API недоступен, есть stale | `stale=true`, цифры из кеша |
| U-30 | API недоступен, нет stale | исключение, UI-ошибка |

### 7.3. Интеграционные и mock-тесты

Mock-сервер (P1) должен отдавать:

* `Person/GetCount` 79/79/0;
* `Identifier/GetCount` 0/0/0;
* `Identifier/GetList` пустой;
* сценарий с 5 картами (см. Приложение В);
* сценарии 400, 401, 500, таймаут, невалидный JSON;
* сценарий `pageCount=0` при непустом `context`;
* сценарий с `TIMEEND = null`, `ACTIVE = null`, дублем `TABNUM`.

Реальный API:

* проверить коды 200 и структуру ответов;
* сверить `Person/GetCount` и `Identifier/GetCount` с прямым SQL (вне модуля, для приёмки);
* проверить отсутствие токена.

### 7.4. Матрица приёмки

| ID | Проверка | Ожидание |
|---|---|---|
| A-01 | `GET /city/index.php/restdashboard` | HTTP 200, панель отображается |
| A-02 | Количество пользователей | 79 |
| A-03 | Количество удалённых пользователей | 0 |
| A-04 | Всего карт у сотрудников | 0 |
| A-05 | Срок действия завершится до 25.12.2026 | 0 |
| A-06 | Срок карты закончился | 0 |
| A-07 | Сотрудники без карты | 79 |
| A-08 | Количество неактивных карт | 0 |
| A-09 | Неправильный формат RFID | 0 |
| A-10 | Ссылки | соответствуют п. 5.11.2, ведут на существующие страницы |
| A-11 | `?refresh=1` | данные пересчитаны, время обновилось |
| A-12 | Повторная загрузка | нет HTTP-запросов к API (кеш) |
| A-13 | Остановка API | stale-предупреждение или понятная ошибка, без HTTP 500 |
| A-14 | `restdashboard/rfid` | HTTP 200, «карт не найдено» |
| A-15 | Экранирование | `<script>` в `ID_CARD` не исполняется |
| A-16 | Отсутствие SQL | в модуле нет `DB::`, `Database::`, `Model_Summary` |
| A-17 | PHP 5.6 | `php -l` без ошибок, страница работает |
| A-18 | Старая панель | `/dashboard` работает, значения не изменились |
| A-19 | Меню | пункт «Панель REST» виден, подсвечивается |
| A-20 | Мобильная ширина | панель не ломает вёрстку |
| A-21 | `dashboard.enabled = false` | сообщение «Вывод информации запрещён», запросов к API нет |

### 7.5. Контрольные значения и сверка

На 25.11.2026:

| Источник | Люди | Удалённые | Карты типа 1 | Истекают до 25.12 | Истекли | Без карты | Неактивные | RFID |
|---|---|---|---|---|---|---|---|---|
| Старая панель | 79 | 0 | 0 | 0 | 0 | 79 | 0 | 0 |
| `restdashboard` | 79 | 0 | 0 | 0 | 0 | 79 | 0 | 0 |
| API | 79 | 0 | 0 | — | — | — | 0 | — |

Для базы с картами (тестовый стенд) приёмка дополняется:

* сравнением `cards_total_type1` и `cards_inactive` с `Identifier/GetCount`;
* сверкой `cards_expired`/`cards_expiring` с прямым SQL (вне модуля);
* проверкой карт без владельца: если они есть, зафиксировать расхождение и подтвердить, что оно ожидаемо (Q-2);
* проверкой дублей `TABNUM`: если есть, зафиксировать предупреждение.

### 7.6. Регрессия

* `/city/index.php/dashboard` — 200, все блоки на месте;
* `identifier`, `people/find_card_late`, `people/people_without_card`, `people/find_unActiveCard` — 200;
* API v1 и v2 — без изменений;
* `application/bootstrap.php` — модуль добавлен, порядок не ломает маршруты;
* меню — без дублей;
* логи — без новых fatal.

### 7.7. Критерии приёмки (Definition of Done)

1. Модуль включён, страница и меню работают.
2. Все 8 показателей отображаются и совпадают с контрольными значениями.
3. Нет прямых SQL-запросов к Firebird в модуле.
4. Ошибки API не приводят к HTTP 500/белой странице.
5. Кеш и `refresh` работают.
6. RFID-детализация работает.
7. Тесты U-01…U-30 (P0) пройдены; A-01…A-21 пройдены.
8. README и guide заполнены.
9. Код проверен `php -l`, соответствует PHP 5.6.
10. Старая панель не сломана.

---

## 8. Этапы, приоритеты, оценка

| Этап | Содержание | Результат | Оценка, ч/д |
|---|---|---|---|
| 1 | Уточнение семантики, согласование Q-1…Q-4, Q-8 | решения зафиксированы | 0.5 |
| 2 | Каркас модуля, init, route, menu, config, API-клиент | страница-заглушка, клиент | 1.0 |
| 3 | Модели `Person`/`Card`, сервис, обход, расчёты, кеш, ошибки | рабочие показатели | 1.5 |
| 4 | Представление, RFID-страница, i18n, стили | UI | 1.0 |
| 5 | Тесты, mock, приёмка, README/guide | отчёт, документация | 1.0 |
| 6 | Буфер (20%) | — | 1.0 |
| **Итого** | | | **6.0** |

Приоритеты:

* P0: все 8 показателей, RFID-детализация, кеш, ошибки, приёмка;
* P1: mock-сервер, повторные запросы, защита `refresh` от спама.

---

## 9. Риски

| ID | Риск | Вероятность | Влияние | Митигация |
|---|---|---|---|---|
| R-1 | API не отдаёт диапазон `TIMEEND` | высокая | высокое | обход карт, кеш, лимиты; в будущем — endpoint `Stat` |
| R-2 | Большая таблица `CARD` → медленный обход | средняя | высокое | `max_pages`, `max_scan_seconds`, кеш, предупреждение |
| R-3 | Карты без владельца не видны API | средняя | среднее | зафиксировать в документации; при необходимости — новый метод API |
| R-4 | Дубли/пустые `TABNUM` | средняя | среднее | предупреждения, рекомендация уникального индекса |
| R-5 | Часовой пояс API ≠ City | низкая | высокое | `api_timezone`, проверка на стенде |
| R-6 | Изменение контракта API 1.8.0 | низкая | высокое | версия в конфиге/README, контрактные тесты |
| R-7 | API закрыли авторизацией | низкая | среднее | optional Bearer token |
| R-8 | Кеш недоступен/переполнен | низкая | низкое | работа без кеша, лог |
| R-9 | PHP 5.6 EOL, cURL/OpenSSL | средняя | среднее | не добавлять новых зависимостей, тесты на стенде |
| R-10 | Спам `?refresh=1` | низкая | низкое | троттлинг (P1) |
| R-11 | Расхождение со старой панелью из-за D-3 | высокая | среднее | согласовать Q-1, зафиксировать в приёмке |
| R-12 | Утечка номеров карт через кеш/логи | низкая | среднее | не логировать, защитить cache, маскировать |

---

## 10. Открытые вопросы

| ID | Вопрос | Варианты | Рекомендация |
|---|---|---|---|
| Q-1 | Семантика «Срок действия завершится до»: строгая или как в старом SQL? | строгая `[now; H 23:59:59]`, только `ACTIVE=1`; старая `< H 00:00`, без `ACTIVE` | строгая; на текущей базе значения совпадают |
| Q-2 | Учитывать карты без владельца? | API их не видит; принять или доработать API | принять для v1.0, зафиксировать |
| Q-3 | Дубли `TABNUM`: что считать? | приблизительный расчёт или требовать уникальность | требовать уникальность, показывать предупреждение |
| Q-4 | RFID: только строчные `a–f` или полная проверка hex? | паритет или строгая `^[0-9A-F]+$` | паритет в v1.0, расширение отдельной задачей |
| Q-5 | Ссылки детализации: старые SQL-страницы или REST-аналоги? | оставить старые; заменить | оставить старые, замена вне объёма |
| Q-6 | Нужен ли токен в production? | анонимно; Bearer | анонимно в локальной сети; токен опционально |
| Q-7 | Автообновление? | нет; 60 с | нет, ручное обновление |
| Q-8 | Максимальный объём `CARD` | 10k/50k/100k | уточнить; по умолчанию 50 страниц |
| Q-9 | Заменять ли блок `windows1` в старой панели? | нет; заменить | нет, параллельная страница |
| Q-10 | Часовой пояс API | совпадает с City; иной | подтвердить; `api_timezone` |
| Q-11 | Нужна ли страница RFID в v1.0? | да; нет | да, P0, простая таблица |
| Q-12 | Кто владелец пункта меню и ACL? | администратор; все | как у dashboard |

---

## Приложение А. Используемые операции API и примеры

### А.1. Таблица операций

| № | Метод | Путь | Query | Ответ |
|---|---|---|---|---|
| 1 | GET | `/api/v2/Person/GetCount` | — | `{total, active, inactive}` |
| 2 | GET | `/api/v2/Identifier/GetCount` | — | `{total, active, inactive}` |
| 3 | GET | `/api/v2/Identifier/GetCount` | `id_cardtype=1` | `{total, active, inactive}` |
| 4 | GET | `/api/v2/Identifier/GetCount` | `active=0` | `{total, active, inactive}` |
| 5 | GET | `/api/v2/Identifier/GetList` | `pageIndex=0&pageSize=1000` | `{context, pageIndex, pageSize, pageCount}` |

### А.2. curl

```bash
# Сотрудники
curl -s "http://localhost:5056/api/v2/Person/GetCount"

# Все карты
curl -s "http://localhost:5056/api/v2/Identifier/GetCount"

# Карты типа 1
curl -s "http://localhost:5056/api/v2/Identifier/GetCount?id_cardtype=1"

# Неактивные карты
curl -s "http://localhost:5056/api/v2/Identifier/GetCount?active=0"

# Страница карт
curl -s "http://localhost:5056/api/v2/Identifier/GetList?pageIndex=0&pageSize=1000"
```

### А.3. Примеры ответов (стенд 25.11.2026)

```json
// Person/GetCount
{"total":79,"active":79,"inactive":0}

// Identifier/GetCount
{"total":0,"active":0,"inactive":0}

// Identifier/GetList
{
  "context": [],
  "pageIndex": 0,
  "pageSize": 2,
  "pageCount": 0
}
```

### А.4. Пример ошибки

```json
{
  "error": "VALIDATION_ERROR",
  "message": "Параметр active должен быть одним из значений: all, active, inactive, 1, 0, true, false",
  "details": [
    { "field": "active", "issue": "invalid_value" }
  ]
}
```

---

## Приложение Б. Пример конфигурации

```php
<?php defined('SYSPATH') or die('No direct script access.');

return array(
    'api' => array(
        'base_url'        => 'http://localhost:5056',
        'token'           => '',
        'timeout'         => 10,
        'connect_timeout' => 3,
        'verify_ssl'      => false,
        'user_agent'      => 'ArtonitCity-RestDashboard/1.0.0',
    ),
    'dashboard' => array(
        'enabled'            => true,
        'card_type_employee' => 1,
        'expiring_days'      => null,
        'timezone'           => null,
        'api_timezone'       => null,
        'cache_lifetime'     => 300,
        'stale_lifetime'     => 86400,
        'page_size'          => 1000,
        'max_pages'          => 50,
        'max_scan_seconds'   => 30,
        'max_rfid_list'      => 1000,
        'rfid_pattern'       => '/[a-f]/',
    ),
    'links' => array(
        'cards'    => 'identifier',
        'expiring' => 'people/find_card_late_next_week',
        'expired'  => 'people/find_card_late',
        'without'  => 'people/people_without_card',
        'inactive' => 'people/find_unActiveCard',
        'rfid'     => 'restdashboard/rfid',
    ),
);
```

---

## Приложение В. Пример расчёта на синтетическом наборе

Дано:

* `now = 2026-11-25 12:00:00`;
* `H = 2026-12-25 23:59:59`;
* `people_total = 5` (`T1`…`T5`).

Карты:

| ID_CARD | TYPE | ACTIVE | TIMEEND | TABNUM | Комментарий |
|---|---|---|---|---|---|
| `C1` | 1 | 1 | 2026-12-01 10:00 | T1 | попадёт в «завершится» |
| `C2` | 1 | 1 | 2026-11-20 10:00 | T2 | «закончился» |
| `C3` | 1 | 0 | 2026-11-20 10:00 | T2 | неактивная, не «закончился» |
| `C4` | 2 | 1 | null | T3 | не истекает, не типа 1 |
| `C5` | 1 | 1 | 2027-01-01 00:00 | T4 | позже горизонта |

Расчёт:

| Показатель | Значение | Пояснение |
|---|---|---|
| `people_total` | 5 | API |
| `people_inactive` | 0 | API |
| `cards_total_type1` | 4 | C1, C2, C3, C5 |
| `cards_expiring` | 1 | C1 |
| `cards_expired` | 1 | C2 |
| `people_without_card` | 1 | T5; T1–T4 имеют карты |
| `cards_inactive` | 1 | C3 |
| `rfid_invalid` | 1 | если `C1 = 'a1b2'`; иначе 0 |

---

## Приложение Г. Чек-лист разработчика

- [ ] Модуль `restdashboard` создан и включён в `application/bootstrap.php`.
- [ ] Маршрут `/restdashboard` и `/restdashboard/rfid` работают.
- [ ] Пункт меню «Панель REST» отображается и подсвечивается.
- [ ] `Model_Restdashboard_Api` не логирует токен и карты.
- [ ] `Person/GetCount` и `Identifier/GetCount` вызываются с правильными query.
- [ ] Обход `Identifier/GetList` использует `pageSize=1000` и лимиты.
- [ ] `TIMEEND` сравнивается в одном часовом поясе.
- [ ] `ACTIVE=NULL` не считается активным.
- [ ] `people_without_card` не уходит в минус.
- [ ] RFID-паттерн соответствует согласованному правилу.
- [ ] Производные показатели = `null` при неполном обходе.
- [ ] Кеш 300 с, stale 86400 с, `refresh=1`.
- [ ] Ошибки API не дают HTTP 500.
- [ ] Все выходные строки экранированы.
- [ ] Нет `DB::`/`Database::` в модуле.
- [ ] `php -l` всех файлов.
- [ ] README и guide заполнены.
- [ ] Старая панель не сломана.

---

## Приложение Д. Источники

| Источник | Что использовано |
|---|---|
| `C:\xampp\htdocs\city\modules\dashboard\classes\Controller\Dashboard.php` | маршрут, вызов сервиса, представление |
| `C:\xampp\htdocs\city\modules\dashboard\classes\Service\Dashboard.php` | `getWin1`, `checkRfidKeyFormat` |
| `C:\xampp\htdocs\city\modules\dashboard\classes\Model\Summary.php` | SQL-формулы |
| `C:\xampp\htdocs\city\modules\dashboard\views\dashboard\dashboard.php` | состав, порядок, ссылки |
| `C:\xampp\htdocs\city\application\config\artonitcity_config.php` | `main_windows`, `count_day_befor_end_time` |
| `C:\xampp\htdocs\city\application\bootstrap.php` | часовой пояс, список модулей |
| `C:\xampp\htdocs\city\modules\restmancard\classes\model\Restmancard\Api.php` | образец HTTP-клиента и кеша |
| `http://localhost:5056/swagger/v2/swagger.json` | контракт API 1.8.0 |
| `D:\source\repos\ArtonitRESTAPI\REST_API_v2.md` | общее описание API v2 |
| `D:\source\repos\ArtonitRESTAPI\docs\PEOPLE_API_v2.md` | `Person/GetCount`, `GetList` |
| `D:\source\repos\ArtonitRESTAPI\docs\IDENTIFIERS_API_v2.md` | `Identifier/GetCount`, `GetList` |
| `D:\source\repos\ArtonitRESTAPI\APIControllers\IdentifierV2Controller.cs` | фактические параметры и разбор дат |
| `D:\source\repos\ArtonitRESTAPI\DBControllers\IdentifierV2DBController.cs` | SQL и семантика фильтров |
| `C:\xampp\htdocs\city\TZ_Stat_API_v2.md` | предыдущее обследование панели, дефекты, контрольные значения |

---

## Лист согласования

| Роль | ФИО | Подпись | Дата |
|---|---|---|---|
| Заказчик | | | |
| Бизнес-аналитик | | | |
| Разработчик PHP | | | |
| Тестировщик | | | |
| Администратор БД | | | |

**Решения, требующие согласования до начала разработки:** Q-1 (семантика срока), Q-2 (карты без владельца), Q-3 (дубли `TABNUM`), Q-4 (правило RFID), Q-8 (объём `CARD`).
