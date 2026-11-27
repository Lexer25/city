# ТЕХНИЧЕСКОЕ ЗАДАНИЕ

## Разработка endpoint `Stat` в Artonit REST API v2 и перевод панели управления Artonit City на REST API

| Параметр | Значение |
|---|---|
| Шифр документа | TZ-STAT-2026-001 |
| Версия | 1.0 |
| Дата | 24.11.2026 |
| Статус | На согласование |
| Заказчик | (заполняется) |
| Исполнитель | (заполняется) |
| Объект 1 | Artonit REST API v2, `D:\source\repos\ArtonitRESTAPI`, .NET 8, версия 1.6.0 |
| Объект 2 | Artonit City, `C:\xampp\htdocs\city`, Kohana 3.3, PHP 5.6 |
| Основание | Задача «Endpoint Stat» от 24.11.2026 |
| Связанные документы | `REST_API_v2.md`, `docs/ORGANIZATIONS_API_v2.md`, `docs/ACCESS_API_v2.md`, `docs/PEOPLE_API_v2.md` |

**История изменений**

| Версия | Дата | Автор | Изменения |
|---|---|---|---|
| 1.0 | 24.11.2026 | (заполняется) | Первая редакция: обследование панели, gap-анализ API v2, контракт 9 методов `Stat`, требования к реализации и приёмке |

---

## 0. Краткое резюме

Требуется перенести панель управления Artonit City (`/city/index.php/dashboard`) с прямых SQL-запросов к Firebird на Artonit REST API v2. В текущем API v2 (22 маршрута, 31 операция) отсутствуют методы, отдающие агрегированные показатели панели: количество сотрудников и карт, гостей, оборудования, очередь загрузки, системные сведения и аналитику по кодам событий. Постраничные методы `Person/GetList`, `Identifier/GetList`, `Event/pass*` не позволяют получить эти цифры без полного перебора и не покрывают таблицы `SERVER`, `DEVICE`, `CARDINDEV`, `EVENTS.ANALIT`.

Предлагается добавить в API v2 новый контроллер `StatV2Controller` с тегом Swagger `StatV2` и **9 методами** (7 приоритета P0, 2 — P1):

| № | Метод | Маршрут | Назначение | Блок панели | Приоритет |
|---|---|---|---|---|---|
| 1 | GET | `/api/v2/Stat/Dashboard` | Сводка всей панели одним запросом | 1–5 | P0 |
| 2 | GET | `/api/v2/Stat/People` | Сотрудники и карты | 1 | P0 |
| 3 | GET | `/api/v2/Stat/Guest` | Бюро пропусков (гости и архив) | 1 | P0 |
| 4 | GET | `/api/v2/Stat/Equipment` | Транспортные серверы и контроллеры | 2 | P0 |
| 5 | GET | `/api/v2/Stat/LoadQueue` | Очередь загрузки карт | 3 | P0 |
| 6 | GET | `/api/v2/Stat/System` | Системная информация и статистика БД | 4 | P0 |
| 7 | GET | `/api/v2/Stat/Analytics` | Аналитика событий по кодам `ANALIT` | 5 | P0 |
| 8 | GET | `/api/v2/Stat/AnalyticsEvents` | Детализация событий по коду `ANALIT` | 5 (ссылка «Подробнее») | P1 |
| 9 | GET | `/api/v2/Stat/RfidFormatErrors` | Карты с неверным форматом RFID | 1 (ссылка) | P1 |

Документ содержит полный контракт методов, правила расчёта, требования к реализации серверной и клиентской частей, тесты и приёмку. Все технические решения проверены на работающем экземпляре API (`http://localhost:5056`) и на реальной базе `shieldpro_rest.GDB` 24.11.2026.

---

## 1. Общие сведения

### 1.1. Наименование работ

«Разработка endpoint `Stat` в Artonit REST API v2 и интеграция панели управления Artonit City с REST API».

### 1.2. Цель

Обеспечить панель управления Artonit City теми же показателями, что она показывает сейчас, получая их через Artonit REST API v2, без прямых SQL-запросов из PHP к Firebird.

### 1.3. Задачи

1. Обследовать текущую реализацию панели управления и зафиксировать перечень показателей, их источники и правила расчёта.
2. Выполнить gap-анализ Artonit REST API v2 и обосновать новый endpoint `Stat`.
3. Разработать контракт 9 методов `Stat` в стиле существующего API v2.
4. Реализовать серверную часть в `ArtonitRESTAPI` (контроллер, слой БД, DTO, Swagger, тесты).
5. Реализовать клиентскую часть в `Artonit City` (HTTP-клиент, сервис, конфигурация, деградация на старый SQL).
6. Провести приёмочные испытания и подтвердить совпадение показателей панели с ответами API.

### 1.4. Границы работ

**Входит в объём:**

* новые read-only методы `/api/v2/Stat/*`;
* DTO и схемы Swagger для них;
* SQL-запросы к тем же таблицам, что использует панель;
* клиент REST API в модуле `dashboard` и перевод панели на него;
* исправление дефектов панели, перечисленных в п. 3.5, в объёме, необходимом для сопоставимости показателей;
* документация, smoke-тесты, приёмка.

**Не входит в объём:**

* изменение существующих 22 маршрутов и 31 операции API v2 (кроме добавления новых маршрутов);
* методы записи (создание/изменение/удаление) для `Stat`;
* экран `device/stat` и экспорт состояния СКУД `dashboard/saveStateSkud` (см. п. 13, Q-9);
* замена аутентификации API v2 (см. п. 12, R-5);
* изменение схемы БД, кроме необязательного скрипта индексов (п. 8.6).

### 1.5. Термины и сокращения

| Термин | Определение |
|---|---|
| Панель управления | Страница `/city/index.php/dashboard`, контроллер `Controller_dashboard::action_index` |
| СКУД | Система контроля и управления доступом |
| ТС | Транспортный сервер, таблица `SERVER` |
| Контроллер | Устройство `DEVICE` с `ID_READER IS NULL` |
| Точка прохода (считыватель) | Устройство `DEVICE` с `ID_READER IS NOT NULL` |
| Карта, идентификатор | Запись таблицы `CARD` (домен `ID_CARD`) |
| Гость | Сотрудник с `PEOPLE.ACTIVE = 1` в организации `dv_Guest` (исторически `ID_ORG = 2`) |
| Архив | Сотрудник с `PEOPLE.ACTIVE = 1` в организации `dv_Archive` (исторически `ID_ORG = 3`) |
| Код аналитики | Поле `EVENTS.ANALIT` |
| JWT | JSON Web Token, выдаётся `GET /login` |
| P0 / P1 | Обязательный к релизу / следующий релиз |

---

## 2. Исходные данные и условия

| Параметр | Значение | Источник |
|---|---|---|
| Сервис API | `http://localhost:5056` | `appsettings.json`, Kestrel |
| Swagger v2 | `http://localhost:5056/swagger/index.html?urls.primaryName=Artonit+REST+API+v2` | `Program.cs` |
| Версия API | 1.6.0 | `ArtonitRESTAPI.csproj` |
| Платформа API | .NET 8, ASP.NET Core, FirebirdSql.Data.FirebirdClient 10.3.1 | `ArtonitRESTAPI.csproj` |
| База данных | `C:\Program Files (x86)\Cardsoft\DuoSE\Access\shieldpro_rest.GDB` | `appsettings.json` |
| Сервер БД | `127.0.0.1:3050`, `Charset=win1251`, `Dialect=3` | `appsettings.json` |
| Клиент | Kohana 3.3, PHP 5.6, расширения cURL и JSON | `C:\xampp\htdocs\city` |
| DSN клиента | `odbc:SDuo`, `CharacterSet=win_1251` | реестр ODBC, `application/config/database.php` |
| Версия модуля dashboard | 2.0.6 | `modules/dashboard/init.php` |

**Важно:** Artonit City и Artonit REST API v2 работают с одной и той же базой `shieldpro_rest.GDB`, но через разные каналы (ODBC и прямой Firebird-протокол). Это позволяет сравнивать показатели панели и ответы API «в одной точке времени».

---

## 3. Обследование текущей панели управления

### 3.1. Архитектура

Маршрут: `/city/index.php/dashboard` (модуль `dashboard`, версия 2.0.6). На стенде обследования Apache слушает порт 8080, страница открывалась по `http://localhost:8080/city/index.php/dashboard`; в задании указан адрес `https://localhost/city/index.php/dashboard` (внешний фронт).

Поток данных:

```
Браузер
  → Controller_dashboard::action_index()            modules/dashboard/classes/Controller/Dashboard.php
    → Service_Dashboard::getDashboardData()         modules/dashboard/classes/Service/Dashboard.php
      → Model_Summary::peopleCounts()               модуль dashboard
      → Model_Stat::getEquipment()
      → Model_Stat::getLoadOrder()
      → Model_Stat::analyt_result()
      → Model_People / Model_identifier             гости
      → Model_Parkdb::aboutDB()                     системная информация
    → View::factory('dashboard/dashboard')          modules/dashboard/views/dashboard/dashboard.php
  → HTML (Bootstrap 3)
```

Особенности:

* блоки панели включаются конфигом `main_windows` (`windows1`…`windows5`);
* данные окон 1–3 и 5 собираются в `Service_Dashboard`, но часть счётчиков осталась в контроллере;
* окно 4 (`about`) собирается в контроллере через `Model_Parkdb`;
* представление ожидает данные в «плоских» массивах с историческими именами ключей (`people_count`, `getcardexpired`, `ANALIT` и т. п.), что затрудняет прямой переход на JSON API.

### 3.2. Блоки панели и показатели

Значения зафиксированы при обращении к панели 24.11.2026 в 22:46:51 (MSK).

| № | Блок | Показатель на экране | Ключ / источник | Фактическое значение |
|---|---|---|---|---|
| 1 | Информация по сотрудникам и картам | Количество пользователей | `people_count` ← `PEOPLE_TOTAL` | 79 |
| 1 | | Количество удалённых пользователей | `key_people_delete` ← `PEOPLE_INACTIVE` | 0 |
| 1 | | Всего карт у сотрудников | `getPeopleCardCount` ← `CARD_TYPE1_TOTAL` | 0 |
| 1 | | Срок действия завершится до 24.12.2026 | `count_card_late_next_week` ← `CARD_EXPIRED_ON_DATE` | 0 |
| 1 | | Срок карты закончился | `getcardexpired` ← `CARD_EXPIRED` | 0 |
| 1 | | Сотрудники без карты | `getPeopleWithoutCard` ← `PEOPLE_WITHOUT_CARD` | 79 |
| 1 | | Количество неактивных карт | `getCardNotActive` ← `CARD_INACTIVE` | 0 |
| 1 | Бюро пропусков | Количество гостей | `guestCount` | не отображается (дефект D-1) |
| 1 | | Количество гостей в архиве | `guestArchiveCount` | не отображается (дефект D-1) |
| 1 | | Выдано гостевых карт | `guestCardCount` | не отображается (дефект D-1) |
| 1 | | Карты в архиве | `guestArchiveCardCount` | не отображается (дефект D-1) |
| 1 | RFID | Неправильный формат RFID | `countErrKeyFormatRfid` | 0 |
| 2 | Оборудование | Количество транспортных серверов | `SERVER` count | 2 |
| 2 | | Количество контроллеров | `DEVICE` where `ID_READER IS NULL` | 0 |
| 3 | Очередь загрузок | Очередь RFID-карт для загрузки | `CARDINDEV` join | 0 |
| 4 | Системная информация | Имя соединения | `about.connectName` | `fb` |
| 4 | | Тип | `about.dsn` | `odbc:SDuo` |
| 4 | | БД | `about.pathDB` | `...\shieldpro_rest.gdb` |
| 4 | | IP | `about.Server` | `127.0.0.1` |
| 4 | | Событий | `about.countTable.EVENTS` | 0 (в БД 846 — дефект D-4) |
| 4 | | Min_date | `about.minEventDate` | 2026-11-22 12:06:31 |
| 5 | Аналитика | Результат анализа событий за 24 часа | `analyt_result` | «Нет данных» (все `ANALIT = 0`) |

### 3.3. Правила расчёта (текущая реализация)

**Окно 1, сотрудники и карты** — `Model_Summary::peopleCounts()`:

```sql
SELECT
    COUNT(DISTINCT P.ID_PEP) AS PEOPLE_TOTAL,
    SUM(CASE WHEN P."ACTIVE" = 0 THEN 1 ELSE 0 END) AS PEOPLE_INACTIVE,
    SUM(CASE WHEN P."ACTIVE" > 0 THEN 1 ELSE 0 END) AS PEOPLE_ACTIVE,
    SUM(CASE WHEN C.ID_PEP IS NULL THEN 1 ELSE 0 END) AS PEOPLE_WITHOUT_CARD,
    (SELECT COUNT(*) FROM CARD WHERE ID_CARDTYPE = 1) AS CARD_TYPE1_TOTAL,
    (SELECT COUNT(*) FROM CARD WHERE TIMEEND < CURRENT_TIMESTAMP AND "ACTIVE" > 0) AS CARD_EXPIRED,
    (SELECT COUNT(*) FROM CARD WHERE TIMEEND < '24.12.2026') AS CARD_EXPIRED_ON_DATE,
    (SELECT COUNT(*) FROM CARD WHERE "ACTIVE" = 0) AS CARD_INACTIVE
FROM PEOPLE P
LEFT JOIN CARD C ON C.ID_PEP = P.ID_PEP AND C.ID_DB = P.ID_DB
```

Дата `24.12.2026` в примере — фактическое значение `today + count_day_befor_end_time` на 24.11.2026.

**Окно 1, гости** — `Service_Dashboard::getWin1Guest()`:

```sql
-- гости
SELECT COUNT(*) FROM PEOPLE P WHERE P."ACTIVE" = 1 AND P.ID_ORG = 2;
-- гости в архиве
SELECT COUNT(*) FROM PEOPLE P WHERE P."ACTIVE" = 1 AND P.ID_ORG = 3;
-- карты гостей
SELECT COUNT(*) FROM CARD C JOIN PEOPLE P ON P.ID_PEP = C.ID_PEP WHERE P.ID_ORG = 2;
-- карты в архиве
SELECT COUNT(*) FROM CARD C JOIN PEOPLE P ON P.ID_PEP = C.ID_PEP WHERE P.ID_ORG = 3;
```

**Окно 1, RFID** — `Service_Dashboard::checkRfidKeyFormat()`:

```sql
SELECT C.ID_CARD FROM CARD C
WHERE (C.ID_CARD LIKE '%a%' OR C.ID_CARD LIKE '%b%' OR C.ID_CARD LIKE '%c%'
    OR C.ID_CARD LIKE '%d%' OR C.ID_CARD LIKE '%e%' OR C.ID_CARD LIKE '%f%')
  AND C.ID_CARDTYPE = 1
```

**Окно 2, оборудование** — `Model_Stat::getEquipment()`:

```sql
SELECT COUNT(*) FROM SERVER;
SELECT COUNT(*) FROM DEVICE D WHERE D.ID_READER IS NULL;
```

**Окно 3, очередь загрузки** — `Model_Stat::getLoadOrder()`:

```sql
SELECT COUNT(*) FROM CARDINDEV CD
JOIN DEVTYPE_CARDTYPE DC ON DC.ID_CARDTYPE = CD.ID_CARDTYPE
JOIN DEVICE D  ON D.ID_DEV = CD.ID_DEV AND D.ID_DEVTYPE = DC.ID_DEVTYPE
JOIN DEVICE D2 ON D2.ID_CTRL = D.ID_CTRL AND D2.ID_READER IS NULL AND D2.ID_DEVTYPE = DC.ID_DEVTYPE
JOIN DEVTYPE DT ON DT.ID_DEVTYPE = DC.ID_DEVTYPE
WHERE D."ACTIVE" > 0 AND D2."ACTIVE" > 0 AND DT.STANDALONE IN (0, 1)
  AND CD.ID_CARDTYPE = 1
```

**Окно 4, система** — `Model_Parkdb::aboutDB()`:
имя соединения, DSN, путь к БД и `Server` читаются из конфигурации и реестра ODBC (`reg.exe`);
`countTable.EVENTS` — оценка по статистике первичных индексов `RDB$INDICES`;
`minEventDate` — `SELECT MIN(E.DATETIME) FROM EVENTS E`.

**Окно 5, аналитика** — `Model_Stat::analyt_result()`:

```sql
SELECT DISTINCT E.ANALIT, COUNT(*) FROM EVENTS E
WHERE E.ANALIT IS NOT NULL AND E.ANALIT > 0
  AND E.DATETIME > CURRENT_TIMESTAMP - 1
GROUP BY E.ANALIT
```

Цвет строки и название кода определяются конфигом `artonitcity_config` (`analit_ok`, `analit_err`, `analit_transit`) и i18n-ключами вида `507a`.

### 3.4. Конфигурация, влияющая на панель

| Ключ | Значение | Влияние |
|---|---|---|
| `main_windows.windows1`…`windows5` | все `true` | видимость блоков |
| `count_day_befor_end_time` | 30 | горизонт «срок действия завершится до» |
| `city_name` | `ЖК Хедлайнер` | имя объекта |
| `analit_ok`, `analit_err`, `analit_transit` | наборы кодов | классификация и цвет аналитики |
| `stat_day_befor` | 2 | очистка статистики (не влияет на панель) |

### 3.5. Выявленные дефекты текущей реализации

Дефекты важны потому, что при переносе на API нужно либо воспроизвести поведение, либо сознательно исправить его и синхронно поправить панель. Решение по каждому дефекту должно быть зафиксировано до начала разработки (см. п. 13).

| ID | Дефект | Проявление | Предлагаемое решение |
|---|---|---|---|
| D-1 | Блок «Бюро пропусков» не получает данные | `Service_Dashboard::getWin1Guest()` не вызывается из `getDashboardData()`; представление использует неопределённую переменную `$list_windowsGuest`, поэтому блок пуст | Метод `Stat/Guest` обязателен; в контроллере передать `list_windowsGuest`; добавить тест |
| D-2 | `PEOPLE_INACTIVE` / `PEOPLE_ACTIVE` считают строки, а не людей | при нескольких картах у одного сотрудника `SUM` по `LEFT JOIN` учитывает его несколько раз | в `Stat/People` считать `COUNT(*)` по `PEOPLE` без join; панель привести к тем же значениям |
| D-3 | Граница `CARD_EXPIRED_ON_DATE` | строка `'24.12.2026'` приводится к `24.12.2026 00:00:00`, карты, истекающие в этот день, не учитываются; фильтр `ACTIVE` отсутствует | в `Stat/People` использовать конец дня `23:59:59` и явный фильтр `ACTIVE > 0`; зафиксировать в контракте |
| D-4 | «Событий: 0» при 846 событиях | `countTable.EVENTS` — оценка по устаревшей статистике индексов | в `Stat/System` отдавать точный `COUNT(*)` и отдельно оценку; панель показывать точное значение |
| D-5 | Очередь загрузки считает записи, а не карты | `COUNT(*)` по `CARDINDEV`; при нескольких устройствах одна карта учитывается несколько раз | в `Stat/LoadQueue` отдавать `cardsPendingLoad` (`COUNT(DISTINCT ID_CARD)`) и `recordsPendingLoad` |
| D-6 | Жёстко заданы `ID_ORG = 2/3` и `ID_CARDTYPE = 1` | значения зависят от конкретной базы | в API использовать `DIVCODE` (`dv_Guest`, `dv_Archive`) и параметр `cardType` с конфигом и fallback на исторические ID |
| D-7 | Оборудование считает неактивные объекты | `SERVER` = 2, хотя активен 1; контроллеры без фильтра `ACTIVE` | в `Stat/Equipment` отдавать всего и активных; панель оставить на «всего» для совместимости |
| D-8 | Представление зависит от числовых ключей | `list_windows2['device'][3]`, `[4]`, `['order']['card_for_load']` | API отдаёт именованные поля; маппинг выполняется в `Service_Dashboard`, представление не меняет структуру |
| D-9 | Аналитика не видит события с `ANALIT = 0` | в базе 846 событий с `ANALIT = 0`, панель показывает «Нет данных» | сохранить фильтр `ANALIT > 0` для панели; в `Stat/AnalyticsEvents` разрешить `analit = 0` для диагностики |
| D-10 | «Всего карт у сотрудников» считает все карты типа 1, включая гостей и архив | название не соответствует смыслу | в контракте явно назвать `cardsTotal` и указать фильтр; при необходимости добавить `cardType` |

---

## 4. Анализ Artonit REST API v2

### 4.1. Паспорт API

| Параметр | Значение |
|---|---|
| Заголовок Swagger | `Artonit Rest API v2` |
| Версия | 1.6.0 |
| Префикс | `/api/v2` |
| Маршрутов / операций | 22 / 31 |
| Теги | `AccessV2`, `EventV2`, `IdentifierTypeV2`, `IdentifierV2`, `OrganizationV2`, `PersonV2` |
| Аутентификация | `GET /login?username=...&pasword=...` → JWT; далее `Authorization: Bearer <token>` |
| Роли | `admin` (изменяющие команды), `user` (чтение) |
| Формат ошибок v2 | `{"error":"...","message":"...","details":[...]}` |
| Формат списков | `{"context":[...],"pageIndex":0,"pageSize":10,"pageCount":N}`; `pageCount` — число страниц |
| Формат списка организаций | `{"items":[...],"page":1,"pageSize":20,"total":N}` (1-based) |
| Кодировка | UTF-8 |
| Swagger JSON | `/swagger/v2/swagger.json` |

Замечания по фактическому поведению (проверено 24.11.2026):

* `GET /login` возвращает `{"status":true,"message":"success","data":{"token":"..."}}`;
* часть старых методов при ошибке отдаёт `text/plain` (например, `404` для `Person`), а не `ApiError`; новые методы `Stat` обязаны отдавать только `ApiError`;
* `Person/GetList` возвращает `context`, `pageIndex`, `pageSize`, `pageCount`; `pageCount` — именно число страниц (27 при 79 сотрудниках и `pageSize=3`);
* `Identifier/GetList` соединяет `CARD` с `PEOPLE`, поэтому карты без владельца в список не попадают;
* список организаций отдаёт `total`, но не отдаёт количество сотрудников.

### 4.2. Существующие методы, релевантные панели

| Метод | Что даёт | Чего не хватает для панели |
|---|---|---|
| `GET /api/v2/Person/GetList` | страница сотрудников (`tabnum`, ФИО) | нет агрегатов, фильтров по картам/организации/активности; только перебор страниц |
| `GET /api/v2/Person?tabnum=` | один сотрудник | не подходит для счётчиков |
| `GET /api/v2/Identifier/GetList` | страница карт (`id_card`, `id_cardtype`, `tabnum`) | нет фильтра по типу, сроку, активности, формату; нет `COUNT`; карты без владельца скрыты |
| `GET /api/v2/Access/GetList`, `GetPeople` | категории доступа и их сотрудники | не относятся к показателям панели |
| `GET /api/v2/organizations`, `/{divcode}` | организации, в т. ч. `dv_Guest`, `dv_Archive` | нет количества сотрудников и карт в организации |
| `GET /api/v2/Event/passAllAp`, `passAllDG` | события за период по точке/группе | нет группировки по `ANALIT`, нет счётчиков, обязательны имя точки/группы и период; в базе нет устройств |
| `GET /api/v2/Event/passIdAp`, `passIdDg` | события сотрудника | не подходит для сводки |
| — | `SERVER`, `DEVICE`, `CARDINDEV`, `ST_DATA` | методов нет вообще |
| — | системная информация о БД | методов нет |

### 4.3. Gap-анализ

| Показатель панели | Ближайший существующий метод | Вердикт | Что нужно |
|---|---|---|---|
| Количество пользователей | `Person/GetList` | Не подходит | агрегат `COUNT(*)` |
| Удалённые пользователи | — | Нет | агрегат по `PEOPLE.ACTIVE` |
| Всего карт у сотрудников | `Identifier/GetList` | Не подходит | `COUNT` по `CARD` с фильтром типа |
| Срок действия завершится до даты | — | Нет | `COUNT` по `CARD.TIMEEND` |
| Срок карты закончился | — | Нет | `COUNT` по `CARD.TIMEEND` |
| Сотрудники без карты | — | Нет | anti-join `PEOPLE`/`CARD` |
| Неактивные карты | `Identifier/GetList` | Не подходит | `COUNT` по `CARD.ACTIVE` |
| Гости и архив | `organizations/{divcode}` | Частично | `COUNT` по `PEOPLE`/`CARD` в организации |
| RFID-формат | `Identifier/GetList` | Не подходит | фильтр по формату и `COUNT` |
| Транспортные серверы | — | Нет | `COUNT` по `SERVER` |
| Контроллеры | — | Нет | `COUNT` по `DEVICE` |
| Очередь загрузки | — | Нет | `COUNT` по `CARDINDEV` |
| Системная информация | — | Нет | метаданные + `COUNT`/`MIN` по `EVENTS` |
| Аналитика по кодам | `Event/pass*` | Не подходит | `GROUP BY ANALIT` + период |
| Детализация по коду | `Event/pass*` | Частично | фильтр по `ANALIT`, пагинация |
| Список карт с неверным форматом | `Identifier/GetList` | Не подходит | фильтр + пагинация |

### 4.4. Вывод

Ни один существующий метод v2 не покрывает показатели панели. Требуется новый read-only endpoint `Stat` с агрегатными методами и двумя постраничными списками. Добавление новых маршрутов не ломает существующий контракт, поэтому выпуск возможен в рамках версии 1.7.0 без изменения поведения v1 и ранее выпущенных методов v2.

---

## 5. Требования к endpoint Stat

### 5.1. Размещение и именование

| Элемент | Значение |
|---|---|
| Базовый маршрут | `/api/v2/Stat` |
| Контроллер | `ArtonitRESTAPI.APIControllers.StatV2Controller` |
| Тег Swagger | `StatV2` |
| Группа Swagger | `v2` (`[ApiExplorerSettings(GroupName = "v2")]`) |
| Слой БД | `ArtonitRESTAPI.DBControllers.StatDBController` |
| DTO | `ArtonitRESTAPI.Model.Stat*` |
| Авторизация | `[Authorize]`; `System` — только роль `admin` |
| Формат | `application/json; charset=utf-8` |
| Побочные эффекты | отсутствуют, только `SELECT` |

Имена методов — PascalCase, как в существующих маршрутах (`/Access/GetList`, `/Person/GetList`): `Dashboard`, `People`, `Guest`, `Equipment`, `LoadQueue`, `System`, `Analytics`, `AnalyticsEvents`, `RfidFormatErrors`.

### 5.2. Общие правила контракта

| ID | Требование |
|---|---|
| О-01 | Все методы — `GET`, идемпотентны, не изменяют данные. |
| О-02 | Все методы требуют заголовок `Authorization: Bearer <JWT>`; без него — `401` с `ApiError{UNAUTHORIZED}`. |
| О-03 | `Stat/System` и секция `system` в `Stat/Dashboard` требуют роль `admin`; иначе — `403` `FORBIDDEN`. |
| О-04 | Ответы — `application/json; charset=utf-8`, UTF-8 без BOM. |
| О-05 | Ошибки — только `ApiError` (`error`, `message`, `details`), коды в `UPPER_SNAKE_CASE`. |
| О-06 | Пустые данные — это `200` и нули/пустые массивы, а не `404`. |
| О-07 | Все счётчики — целые числа, тип `long` (JSON number). |
| О-08 | Даты и время на входе — `yyyy-MM-dd HH:mm:ss` или ISO 8601; на выходе — ISO 8601 с смещением, например `2026-11-24T22:46:51+03:00`. |
| О-09 | `asOf` — единая точка времени снимка, полученная из БД (`SELECT CURRENT_TIMESTAMP FROM RDB$DATABASE`), а не из часов API-хоста. Один `asOf` используется для всех секций агрегированного ответа. |
| О-10 | Верхняя граница периода — исключающая: `[from, to)`. |
| О-11 | `pageIndex` — с нуля; `pageSize` — от 1 до 1000, по умолчанию 50. |
| О-12 | Постраничный ответ: `context`, `pageIndex`, `pageSize`, `pageCount` (число страниц), `total` (всего записей). `total` — дополнительное поле к существующему формату, существующие методы не меняются. |
| О-13 | Неизвестные query-параметры игнорируются; неизвестные значения перечислений — `400`. |
| О-14 | `idDb` (необязательный) ограничивает выборку `ID_DB`; по умолчанию фильтр не применяется — совместимость с панелью. |
| О-15 | Строковые поля обрезаются от пробелов справа на стороне API (в БД нет `TRIM`/`RTRIM`). |
| О-16 | API не выполняет `iconv`; строки из БД уже приходят в UTF-8 благодаря `Charset=win1251`. |
| О-17 | Пароли, полные строки подключения и имена пользователей БД никогда не возвращаются и не логируются. |
| О-18 | Новые маршруты не меняют поведение v1 и существующих методов v2; версия API повышается до 1.7.0. |

### 5.3. Перечень необходимых методов

| № | Метод | Маршрут | Назначение | Секция Dashboard | Приоритет |
|---|---|---|---|---|---|
| Ф-01 | GET | `/api/v2/Stat/Dashboard` | Агрегированная сводка панели | все | P0 |
| Ф-02 | GET | `/api/v2/Stat/People` | Сотрудники и карты, RFID-формат | `people` | P0 |
| Ф-03 | GET | `/api/v2/Stat/Guest` | Гости и архив | `guest` | P0 |
| Ф-04 | GET | `/api/v2/Stat/Equipment` | Транспортные серверы, контроллеры, считыватели | `equipment` | P0 |
| Ф-05 | GET | `/api/v2/Stat/LoadQueue` | Очередь загрузки карт | `loadQueue` | P0 |
| Ф-06 | GET | `/api/v2/Stat/System` | Системная информация и статистика БД | `system` | P0 |
| Ф-07 | GET | `/api/v2/Stat/Analytics` | Сводка событий по кодам `ANALIT` | `analytics` | P0 |
| Ф-08 | GET | `/api/v2/Stat/AnalyticsEvents` | Детализация событий по коду | — | P1 |
| Ф-09 | GET | `/api/v2/Stat/RfidFormatErrors` | Карты с неверным форматом RFID | — | P1 |

**Обоснование разделения.** Агрегированный `Dashboard` нужен, чтобы страница делала один запрос и получала согласованный снимок. Отдельные методы нужны для: (а) обновления одной панели без перезагрузки всей страницы; (б) глубоких ссылок и списков; (в) тестируемости и повторного использования другими клиентами; (г) разграничения прав (`System` — только администратор).

### 5.4. Матрица соответствия «блок панели → метод → поле»

| Блок панели | Метод | Поле ответа | Ключ представления |
|---|---|---|---|
| 1. Количество пользователей | `People` | `peopleTotal` | `people_count` |
| 1. Удалённые пользователи | `People` | `peopleInactive` | `key_people_delete` |
| 1. Всего карт у сотрудников | `People` | `cardsTotal` | `getPeopleCardCount` |
| 1. Срок действия завершится до | `People` | `cardsExpiring`, `expiringBefore` | `count_card_late_next_week`, `timeExpired` |
| 1. Срок карты закончился | `People` | `cardsExpired` | `getcardexpired` |
| 1. Сотрудники без карты | `People` | `peopleWithoutCard` | `getPeopleWithoutCard` |
| 1. Неактивные карты | `People` | `cardsInactive` | `getCardNotActive` |
| 1. Неправильный формат RFID | `People` | `rfidFormatErrors` | `countErrKeyFormatRfid` |
| 1. Гости | `Guest` | `guestCount` | `guestCount` |
| 1. Гости в архиве | `Guest` | `guestArchiveCount` | `guestArchiveCount` |
| 1. Гостевые карты | `Guest` | `guestCardCount` | `guestCardCount` |
| 1. Карты в архиве | `Guest` | `guestArchiveCardCount` | `guestArchiveCardCount` |
| 2. Транспортные серверы | `Equipment` | `transportServers` | `list_windows2['device'][3]` |
| 2. Контроллеры | `Equipment` | `controllers` | `list_windows2['device'][4]` |
| 3. Очередь загрузки | `LoadQueue` | `cardsPendingLoad` | `list_windows3['order']['card_for_load']` |
| 4. Системная информация | `System` | `connectionName`, `databasePath`, `server`, `eventsCount`, `minEventDate` | `about` |
| 5. Аналитика | `Analytics` | `items[].code`, `items[].count` | `ANALIT`, `COUNT` |
| 5. «Подробнее» | `AnalyticsEvents` | `context[]` | ссылка `event/event_analyt/{code}` |
| 1. Ссылка на список RFID | `RfidFormatErrors` | `context[]` | ссылка `dashboard/ErrKeyFormatRfid` |

### 5.5. Общие ограничения и значения по умолчанию

| Параметр | Значение по умолчанию | Допустимый диапазон |
|---|---|---|
| `expiringDays` | 30 (ключ `Stat:DefaultExpiringDays`) | 0…365 |
| `analyticsHours` | 24 | 1…8760 |
| `pageSize` | 50 | 1…1000 |
| Максимальный период аналитики | 24 ч по умолчанию | не более 365 суток |
| `cardType` | 1 | 1…2 147 483 647 |
| `idDb` | не задан (все базы) | 1…2 147 483 647 |
| `sections` | `people,guest,equipment,loadQueue,analytics` | подмножество + `system` |

---

### 5.6. Ф-01. GET `/api/v2/Stat/Dashboard`

**Назначение.** Агрегированная сводка панели управления одним запросом. Возвращает согласованный снимок: все секции рассчитаны на одну точку времени `asOf`.

**Авторизация.** Любая аутентифицированная роль. Если в `sections` указана `system` — требуется роль `admin`.

**Параметры.**

| Имя | Тип | Обяз. | Допустимые значения | По умолчанию | Описание |
|---|---|---|---|---|---|
| `sections` | string | Нет | CSV из `people`, `guest`, `equipment`, `loadQueue`, `analytics`, `system`; регистр не важен | `people,guest,equipment,loadQueue,analytics` | Какие секции вернуть |
| `expiringDays` | int | Нет | 0…365 | 30 | Горизонт «срок действия завершится до» |
| `analyticsHours` | int | Нет | 1…8760 | 24 | Глубина аналитики |
| `cardType` | int | Нет | ≥ 1 | 1 | Тип карт для секций `people` и `loadQueue` |
| `idDb` | int | Нет | ≥ 1 | — | Фильтр по `ID_DB` |

**Ответ 200.**

```json
{
  "asOf": "2026-11-24T22:46:51+03:00",
  "expiringDays": 30,
  "analyticsHours": 24,
  "people": {
    "cardType": 1,
    "expiringBefore": "2026-12-24T23:59:59+03:00",
    "peopleTotal": 79,
    "peopleActive": 79,
    "peopleInactive": 0,
    "peopleWithoutCard": 79,
    "cardsTotal": 0,
    "cardsActive": 0,
    "cardsInactive": 0,
    "cardsExpired": 0,
    "cardsExpiring": 0,
    "rfidFormatErrors": 0
  },
  "guest": {
    "guestDivcode": "dv_Guest",
    "archiveDivcode": "dv_Archive",
    "guestCount": 3,
    "guestArchiveCount": 3,
    "guestCardCount": 0,
    "guestArchiveCardCount": 0
  },
  "equipment": {
    "transportServers": 2,
    "transportServersActive": 1,
    "controllers": 0,
    "controllersActive": 0,
    "readers": 0
  },
  "loadQueue": {
    "cardType": 1,
    "cardsPendingLoad": 0,
    "recordsPendingLoad": 0,
    "devicesInQueue": 0
  },
  "analytics": {
    "period": {
      "from": "2026-11-23T22:46:51+03:00",
      "to": "2026-11-24T22:46:51+03:00",
      "hours": 24
    },
    "total": 0,
    "items": []
  },
  "system": null,
  "warnings": []
}
```

**Поля ответа.**

| Поле | Тип | Описание |
|---|---|---|
| `asOf` | string (date-time) | Время снимка по часам БД |
| `expiringDays` | int | Применённый горизонт |
| `analyticsHours` | int | Применённая глубина аналитики |
| `people` | object \| null | Секция `Stat/People` без `asOf`, `expiringDays` и `warnings`; `null`, если не запрошена |
| `guest` | object \| null | Секция `Stat/Guest` без `asOf` и `warnings` |
| `equipment` | object \| null | Секция `Stat/Equipment` |
| `loadQueue` | object \| null | Секция `Stat/LoadQueue` |
| `analytics` | object \| null | Секция `Stat/Analytics` без `asOf` и `warnings` |
| `system` | object \| null | Секция `Stat/System`; `null`, если не запрошена |
| `warnings` | array | Предупреждения по секциям |

**Правила.**

1. `asOf` берётся один раз: `SELECT CURRENT_TIMESTAMP FROM RDB$DATABASE` в начале обработки. Это же значение передаётся параметром `@now` во все запросы секций — расхождение между секциями недопустимо.
2. Секции считаются независимо. Ошибка БД в одной секции не роняет ответ: поле секции = `null`, в `warnings` добавляется `{ "code": "SECTION_UNAVAILABLE", "section": "<имя>", "message": "..." }`, HTTP-код остаётся `200`.
3. Если `sections` содержит `system`, а роль не `admin` — `403 FORBIDDEN` целиком, без частичного ответа.
4. Если `sections` содержит неизвестное имя — `400 UNSUPPORTED_SECTION`.
5. Пустой `sections` (`sections=`) трактуется как значение по умолчанию.
6. Значения секций должны совпадать со значениями соответствующих индивидуальных методов при тех же параметрах и том же `asOf` (проверяется тестом Т-12).

**Ошибки.**

| HTTP | `error` | Когда |
|---|---|---|
| 400 | `UNSUPPORTED_SECTION` | Неизвестное имя секции |
| 400 | `VALIDATION_ERROR` | Некорректные `expiringDays`, `analyticsHours`, `cardType`, `idDb` |
| 401 | `UNAUTHORIZED` | Нет/просрочен токен |
| 403 | `FORBIDDEN` | Запрошена `system` без роли `admin` |
| 500 | `INTERNAL_ERROR` | Не удалось получить время БД или иную обязательную метаинформацию |

---

### 5.7. Ф-02. GET `/api/v2/Stat/People`

**Назначение.** Показатели блока 1 «Информация по сотрудникам и картам» и счётчик RFID-ошибок.

**Авторизация.** Любая аутентифицированная роль.

**Параметры.**

| Имя | Тип | Обяз. | Допустимые значения | По умолчанию | Описание |
|---|---|---|---|---|---|
| `expiringDays` | int | Нет | 0…365 | 30 | Горизонт «срок действия завершится до» |
| `cardType` | int | Нет | ≥ 1 | 1 | Тип карт для карточных счётчиков и RFID |
| `idDb` | int | Нет | ≥ 1 | — | Фильтр по `ID_DB` |

**Ответ 200.**

```json
{
  "asOf": "2026-11-24T22:46:51+03:00",
  "cardType": 1,
  "expiringDays": 30,
  "expiringBefore": "2026-12-24T23:59:59+03:00",
  "peopleTotal": 79,
  "peopleActive": 79,
  "peopleInactive": 0,
  "peopleWithoutCard": 79,
  "cardsTotal": 0,
  "cardsActive": 0,
  "cardsInactive": 0,
  "cardsExpired": 0,
  "cardsExpiring": 0,
  "rfidFormatErrors": 0,
  "warnings": []
}
```

**Поля и правила расчёта.**

| Поле | Тип | Правило |
|---|---|---|
| `asOf` | date-time | Время снимка из БД |
| `cardType` | int | Применённый тип карт |
| `expiringDays` | int | Применённый горизонт |
| `expiringBefore` | date-time | Конец дня `asOf.Date + expiringDays`, время `23:59:59` |
| `peopleTotal` | long | `COUNT(*) FROM PEOPLE` (с учётом `idDb`) |
| `peopleActive` | long | `COUNT(*) FROM PEOPLE WHERE "ACTIVE" > 0` |
| `peopleInactive` | long | `COUNT(*) FROM PEOPLE WHERE "ACTIVE" = 0` |
| `peopleWithoutCard` | long | люди, у которых нет ни одной карты: `NOT EXISTS (SELECT 1 FROM CARD C WHERE C.ID_PEP = P.ID_PEP AND C.ID_DB = P.ID_DB)`; тип карты не учитывается |
| `cardsTotal` | long | `COUNT(*) FROM CARD WHERE ID_CARDTYPE = @cardType` |
| `cardsActive` | long | то же + `"ACTIVE" > 0` |
| `cardsInactive` | long | то же + `"ACTIVE" = 0` |
| `cardsExpired` | long | то же + `"ACTIVE" > 0 AND TIMEEND < @now` |
| `cardsExpiring` | long | то же + `"ACTIVE" > 0 AND TIMEEND >= @now AND TIMEEND < @expiringBefore` |
| `rfidFormatErrors` | long | `COUNT(*) FROM CARD WHERE ID_CARDTYPE = @cardType AND (ID_CARD LIKE '%a%' OR … OR ID_CARD LIKE '%f%')` |

Инварианты:

* `peopleActive + peopleInactive = peopleTotal`;
* `cardsActive + cardsInactive = cardsTotal`;
* `cardsExpired` и `cardsExpiring` не пересекаются;
* `rfidFormatErrors` равен `total` метода `Stat/RfidFormatErrors` при том же `cardType` (тест Т-10).

**SQL (эталон).**

```sql
SELECT
    (SELECT COUNT(*) FROM PEOPLE)                                      AS PEOPLE_TOTAL,
    (SELECT COUNT(*) FROM PEOPLE WHERE "ACTIVE" > 0)                   AS PEOPLE_ACTIVE,
    (SELECT COUNT(*) FROM PEOPLE WHERE "ACTIVE" = 0)                   AS PEOPLE_INACTIVE,
    (SELECT COUNT(*) FROM PEOPLE P
      WHERE NOT EXISTS (SELECT 1 FROM CARD C
                        WHERE C.ID_PEP = P.ID_PEP
                          AND C.ID_DB  = P.ID_DB))                     AS PEOPLE_WITHOUT_CARD,
    (SELECT COUNT(*) FROM CARD
      WHERE ID_CARDTYPE = @cardType)                                   AS CARDS_TOTAL,
    (SELECT COUNT(*) FROM CARD
      WHERE ID_CARDTYPE = @cardType AND "ACTIVE" > 0)                  AS CARDS_ACTIVE,
    (SELECT COUNT(*) FROM CARD
      WHERE ID_CARDTYPE = @cardType AND "ACTIVE" = 0)                  AS CARDS_INACTIVE,
    (SELECT COUNT(*) FROM CARD
      WHERE ID_CARDTYPE = @cardType AND "ACTIVE" > 0
        AND TIMEEND < @now)                                            AS CARDS_EXPIRED,
    (SELECT COUNT(*) FROM CARD
      WHERE ID_CARDTYPE = @cardType AND "ACTIVE" > 0
        AND TIMEEND >= @now AND TIMEEND < @expiringBefore)             AS CARDS_EXPIRING,
    (SELECT COUNT(*) FROM CARD
      WHERE ID_CARDTYPE = @cardType
        AND (ID_CARD LIKE '%a%' OR ID_CARD LIKE '%b%' OR ID_CARD LIKE '%c%'
          OR ID_CARD LIKE '%d%' OR ID_CARD LIKE '%e%' OR ID_CARD LIKE '%f%')) AS RFID_FORMAT_ERRORS
FROM RDB$DATABASE
```

При `idDb` в каждый подзапрос добавляется `AND ID_DB = @idDb` (для `PEOPLE` — `P.ID_DB`, для `CARD` — `C.ID_DB`).

**Ошибки.** `400 VALIDATION_ERROR` при `expiringDays` вне 0…365, `cardType < 1`, `idDb < 1`; `401 UNAUTHORIZED`; `500 INTERNAL_ERROR`.

**Примечания.**

* В отличие от текущей панели, `peopleActive`/`peopleInactive` считаются по `PEOPLE` без `LEFT JOIN CARD`, поэтому не «размножаются» при нескольких картах (D-2).
* `cardsExpiring` считает только активные карты, срок которых ещё не истёк; текущая панель учитывала все карты до даты, включая просроченные (D-3). После внедрения панель должна показывать значение API.
* `expiringDays = 0` означает «до конца текущего дня», а не «ноль карт».

---

### 5.8. Ф-03. GET `/api/v2/Stat/Guest`

**Назначение.** Показатели блока «Бюро пропусков».

**Авторизация.** Любая аутентифицированная роль.

**Параметры.**

| Имя | Тип | Обяз. | Допустимые значения | По умолчанию | Описание |
|---|---|---|---|---|---|
| `idDb` | int | Нет | ≥ 1 | — | Фильтр по `ID_DB` |

Организации гостей и архива определяются конфигом `Stat:GuestDivcode` (по умолчанию `dv_Guest`) и `Stat:ArchiveDivcode` (по умолчанию `dv_Archive`). Если организация с таким `DIVCODE` не найдена, используется `Stat:GuestFallbackId` (2) и `Stat:ArchiveFallbackId` (3); в ответ добавляется предупреждение. Если не найдена и fallback-организация, соответствующие счётчики равны `0`, а предупреждение сохраняется.

**Ответ 200.**

```json
{
  "asOf": "2026-11-24T22:46:51+03:00",
  "guestDivcode": "dv_Guest",
  "archiveDivcode": "dv_Archive",
  "guestCount": 3,
  "guestArchiveCount": 3,
  "guestCardCount": 0,
  "guestArchiveCardCount": 0,
  "warnings": []
}
```

**Поля и правила.**

| Поле | Тип | Правило |
|---|---|---|
| `guestDivcode` | string \| null | Фактический `DIVCODE` организации гостей; `null`, если организация найдена только по `ID_ORG` |
| `archiveDivcode` | string \| null | Фактический `DIVCODE` организации архива; `null`, если организация найдена только по `ID_ORG` |
| `guestCount` | long | `COUNT(*) FROM PEOPLE P JOIN ORGANIZATION O ON O.ID_ORG = P.ID_ORG WHERE P."ACTIVE" = 1 AND O.DIVCODE = @guestDivcode` |
| `guestArchiveCount` | long | то же для `@archiveDivcode` |
| `guestCardCount` | long | `COUNT(*) FROM CARD C JOIN PEOPLE P ON P.ID_PEP = C.ID_PEP JOIN ORGANIZATION O ON O.ID_ORG = P.ID_ORG WHERE O.DIVCODE = @guestDivcode` |
| `guestArchiveCardCount` | long | то же для `@archiveDivcode` |

**SQL (эталон).**

```sql
SELECT
  (SELECT COUNT(*) FROM PEOPLE P
     JOIN ORGANIZATION O ON O.ID_ORG = P.ID_ORG
   WHERE P."ACTIVE" = 1 AND O.DIVCODE = @guestDivcode)                 AS GUEST_COUNT,
  (SELECT COUNT(*) FROM PEOPLE P
     JOIN ORGANIZATION O ON O.ID_ORG = P.ID_ORG
   WHERE P."ACTIVE" = 1 AND O.DIVCODE = @archiveDivcode)               AS GUEST_ARCHIVE_COUNT,
  (SELECT COUNT(*) FROM CARD C
     JOIN PEOPLE P ON P.ID_PEP = C.ID_PEP
     JOIN ORGANIZATION O ON O.ID_ORG = P.ID_ORG
   WHERE O.DIVCODE = @guestDivcode)                                    AS GUEST_CARD_COUNT,
  (SELECT COUNT(*) FROM CARD C
     JOIN PEOPLE P ON P.ID_PEP = C.ID_PEP
     JOIN ORGANIZATION O ON O.ID_ORG = P.ID_ORG
   WHERE O.DIVCODE = @archiveDivcode)                                  AS GUEST_ARCHIVE_CARD_COUNT
FROM RDB$DATABASE
```

При `idDb` фильтр применяется к `PEOPLE.ID_DB`, `CARD.ID_DB` и `ORGANIZATION.ID_DB`.

**Предупреждения.**

| `code` | Когда |
|---|---|
| `GUEST_ORG_NOT_FOUND` | Не найдена организация гостей; использован fallback |
| `ARCHIVE_ORG_NOT_FOUND` | Не найдена организация архива; использован fallback |

**Ошибки.** `400 VALIDATION_ERROR` при `idDb < 1`; `401`; `500 INTERNAL_ERROR`.

**Примечания.**

* Дефект D-1: сейчас блок не отображается. После внедрения контроллер обязан передать `list_windowsGuest` в представление.
* `DIVCODE` предпочтительнее исторических `ID_ORG = 2/3` (D-6): идентификаторы могут отличаться в другой базе.

---

### 5.9. Ф-04. GET `/api/v2/Stat/Equipment`

**Назначение.** Показатели блока 2 «Оборудование».

**Авторизация.** Любая аутентифицированная роль.

**Параметры.**

| Имя | Тип | Обяз. | Допустимые значения | По умолчанию | Описание |
|---|---|---|---|---|---|
| `idDb` | int | Нет | ≥ 1 | — | Фильтр по `ID_DB` |

**Ответ 200.**

```json
{
  "asOf": "2026-11-24T22:46:51+03:00",
  "transportServers": 2,
  "transportServersActive": 1,
  "controllers": 0,
  "controllersActive": 0,
  "readers": 0,
  "warnings": []
}
```

**Поля и правила.**

| Поле | Тип | Правило |
|---|---|---|
| `transportServers` | long | `COUNT(*) FROM SERVER` (все, включая неактивные — совместимость с панелью) |
| `transportServersActive` | long | `COUNT(*) FROM SERVER WHERE "ACTIVE" > 0` |
| `controllers` | long | `COUNT(*) FROM DEVICE WHERE ID_READER IS NULL` |
| `controllersActive` | long | то же + `"ACTIVE" > 0` |
| `readers` | long | `COUNT(*) FROM DEVICE WHERE ID_READER IS NOT NULL` |

**SQL (эталон).**

```sql
SELECT
  (SELECT COUNT(*) FROM SERVER)                                        AS TRANSPORT_SERVERS,
  (SELECT COUNT(*) FROM SERVER WHERE "ACTIVE" > 0)                     AS TRANSPORT_SERVERS_ACTIVE,
  (SELECT COUNT(*) FROM DEVICE WHERE ID_READER IS NULL)                AS CONTROLLERS,
  (SELECT COUNT(*) FROM DEVICE WHERE ID_READER IS NULL AND "ACTIVE" > 0) AS CONTROLLERS_ACTIVE,
  (SELECT COUNT(*) FROM DEVICE WHERE ID_READER IS NOT NULL)            AS READERS
FROM RDB$DATABASE
```

При `idDb` фильтр применяется к `SERVER.ID_DB` и `DEVICE.ID_DB`.

**Ошибки.** `400 VALIDATION_ERROR`, `401`, `500 INTERNAL_ERROR`.

**Примечание.** Панель использует `transportServers` и `controllers`; поля `*Active` и `readers` добавлены для диагностики и не ломают совместимость (D-7).

---

### 5.10. Ф-05. GET `/api/v2/Stat/LoadQueue`

**Назначение.** Показатель блока 3 «Очередь загрузок».

**Авторизация.** Любая аутентифицированная роль.

**Параметры.**

| Имя | Тип | Обяз. | Допустимые значения | По умолчанию | Описание |
|---|---|---|---|---|---|
| `cardType` | int | Нет | ≥ 1 | 1 | Тип карт |
| `idDb` | int | Нет | ≥ 1 | — | Фильтр по `ID_DB` |

**Ответ 200.**

```json
{
  "asOf": "2026-11-24T22:46:51+03:00",
  "cardType": 1,
  "cardsPendingLoad": 0,
  "recordsPendingLoad": 0,
  "devicesInQueue": 0,
  "warnings": []
}
```

**Поля и правила.**

| Поле | Тип | Правило |
|---|---|---|
| `cardsPendingLoad` | long | `COUNT(DISTINCT CD.ID_CARD)` по запросу ниже |
| `recordsPendingLoad` | long | `COUNT(*)` по тому же запросу |
| `devicesInQueue` | long | `COUNT(DISTINCT CD.ID_DEV)` по тому же запросу |

**SQL (эталон).**

```sql
SELECT
    COUNT(*)                    AS RECORDS_PENDING_LOAD,
    COUNT(DISTINCT CD.ID_CARD)  AS CARDS_PENDING_LOAD,
    COUNT(DISTINCT CD.ID_DEV)   AS DEVICES_IN_QUEUE
FROM CARDINDEV CD
JOIN DEVTYPE_CARDTYPE DC ON DC.ID_CARDTYPE = CD.ID_CARDTYPE
JOIN DEVICE D  ON D.ID_DEV = CD.ID_DEV  AND D.ID_DEVTYPE = DC.ID_DEVTYPE
JOIN DEVICE D2 ON D2.ID_CTRL = D.ID_CTRL AND D2.ID_READER IS NULL
              AND D2.ID_DEVTYPE = DC.ID_DEVTYPE
JOIN DEVTYPE DT ON DT.ID_DEVTYPE = DC.ID_DEVTYPE
WHERE D."ACTIVE" > 0
  AND D2."ACTIVE" > 0
  AND DT.STANDALONE IN (0, 1)
  AND CD.ID_CARDTYPE = @cardType
```

При `idDb` фильтр применяется к `CARDINDEV.ID_DB`, `DEVICE.ID_DB` (`D` и `D2`) и `DEVTYPE_CARDTYPE.ID_DB`.

**Ошибки.** `400 VALIDATION_ERROR`, `401`, `500 INTERNAL_ERROR`.

**Примечания.**

* Панель показывает `cardsPendingLoad`; текущая реализация показывала `COUNT(*)` (D-5). Для совместимости можно временно выводить `recordsPendingLoad`, но корректным является `cardsPendingLoad`.
* `recordsPendingLoad` может быть больше `cardsPendingLoad`, если одна карта адресована нескольким устройствам.

---

### 5.11. Ф-06. GET `/api/v2/Stat/System`

**Назначение.** Системная информация блока 4 и статистика БД.

**Авторизация.** Только роль `admin`.

**Параметры.**

| Имя | Тип | Обяз. | Допустимые значения | По умолчанию | Описание |
|---|---|---|---|---|---|
| `counts` | string | Нет | `both`, `exact`, `estimated` | `both` | Какие оценки количества событий вернуть |
| `idDb` | int | Нет | ≥ 1 | — | Фильтр по `ID_DB` |

**Ответ 200.**

```json
{
  "asOf": "2026-11-24T22:46:51+03:00",
  "connectionName": "DatabaseConnectionString",
  "databasePath": "C:\\Program Files (x86)\\Cardsoft\\DuoSE\\Access\\shieldpro_rest.GDB",
  "server": "127.0.0.1",
  "port": 3050,
  "charset": "win1251",
  "apiVersion": "1.7.0",
  "eventsCount": 846,
  "eventsCountEstimated": 0,
  "minEventDate": "2026-11-22T12:06:31+03:00",
  "warnings": []
}
```

**Поля и правила.**

| Поле | Тип | Правило |
|---|---|---|
| `connectionName` | string | Имя строки подключения в конфигурации (не сама строка) |
| `databasePath` | string | `Database` из строки подключения; без пользователя и пароля |
| `server` | string | `DataSource` |
| `port` | int | `Port` |
| `charset` | string | `Charset` |
| `apiVersion` | string | Версия сборки API |
| `eventsCount` | long \| null | Точный `COUNT(*) FROM EVENTS`; `null`, если `counts=estimated` |
| `eventsCountEstimated` | long \| null | Оценка по `RDB$INDICES` для всей таблицы; `null`, если `counts=exact` или задан `idDb` |
| `minEventDate` | date-time \| null | `MIN(DATETIME)` |

**SQL (эталон).**

```sql
-- точное количество и минимум
SELECT COUNT(*) AS EVENTS_COUNT, MIN(E.DATETIME) AS MIN_EVENT_DATE
FROM EVENTS E;

-- оценка по статистике первичного индекса
SELECT CAST(1 / I.RDB$STATISTICS AS INTEGER) AS RECORD_COUNT
FROM RDB$INDICES I
JOIN RDB$RELATION_CONSTRAINTS C ON C.RDB$INDEX_NAME = I.RDB$INDEX_NAME
WHERE C.RDB$CONSTRAINT_TYPE = 'PRIMARY KEY'
  AND I.RDB$RELATION_NAME = 'EVENTS'
  AND I.RDB$STATISTICS > 0;
```

При `idDb` точный подсчёт и `minEventDate` ограничиваются `EVENTS.ID_DB`; оценка по индексам всегда относится ко всей таблице и при заданном `idDb` возвращается `null` (отфильтровать статистику индексов невозможно).

**Кэширование.** Результат точного подсчёта кэшируется в памяти API на `Stat:SystemCacheSeconds` (по умолчанию 60 с) отдельно для каждого `idDb`; параметр `counts` не должен приводить к повторному тяжёлому запросу.

**Предупреждения.**

| `code` | Когда |
|---|---|
| `ESTIMATE_UNAVAILABLE` | Не удалось прочитать статистику индексов |
| `SYSTEM_METADATA_UNAVAILABLE` | Не удалось разобрать строку подключения |

**Ошибки.**

| HTTP | `error` | Когда |
|---|---|---|
| 400 | `VALIDATION_ERROR` | Некорректный `counts`/`idDb` |
| 401 | `UNAUTHORIZED` | Нет токена |
| 403 | `FORBIDDEN` | Роль не `admin` |
| 500 | `INTERNAL_ERROR` | Ошибка БД |

**Примечания.**

* `databasePath`, `server`, `port` — инфраструктурные сведения; доступ только администратору. Пароль и полная строка подключения не возвращаются никогда (О-17).
* Панель показывает DSN клиента (`odbc:SDuo`), которого API не знает. В блоке 4 после перехода следует показывать `databasePath`/`server` API, а «Тип» — строкой `REST API v2`. Это изменение отображения согласовать с заказчиком (Q-4).
* Оценка `eventsCountEstimated` на текущей базе равна 0 при фактических 846 событиях — статистика индексов устарела (D-4). Точное значение обязательно.

---

### 5.12. Ф-07. GET `/api/v2/Stat/Analytics`

**Назначение.** Сводка событий по кодам `ANALIT` за период — блок 5.

**Авторизация.** Любая аутентифицированная роль.

**Параметры.**

| Имя | Тип | Обяз. | Допустимые значения | По умолчанию | Описание |
|---|---|---|---|---|---|
| `fromDate` | string | Нет | дата/дата-время | `to - hours` | Начало периода включительно |
| `toDate` | string | Нет | дата/дата-время | `asOf` | Конец периода исключительно |
| `hours` | int | Нет | 1…8760 | 24 | Глубина, если даты не заданы |
| `analit` | string | Нет | CSV целых ≥ 1 | — | Оставить только указанные коды |
| `category` | string | Нет | `ok`, `error`, `transit`, `unknown` | — | Фильтр по классификации |
| `idDb` | int | Нет | ≥ 1 | — | Фильтр по `ID_DB` |

Правила периода:

* если заданы и `fromDate`, и `toDate` — используются они; `hours` игнорируется;
* если задана только одна дата — `400 INVALID_PERIOD`;
* если не задано ничего — `to = asOf`, `from = asOf - hours`;
* `from` должно быть меньше `to`;
* максимальная длина периода — 365 суток, иначе `400 PERIOD_TOO_LONG`;
* дата без времени: `fromDate` → `00:00:00`, `toDate` → `23:59:59`.

**Ответ 200.**

```json
{
  "asOf": "2026-11-24T22:46:51+03:00",
  "period": {
    "from": "2026-11-23T22:46:51+03:00",
    "to": "2026-11-24T22:46:51+03:00",
    "hours": 24
  },
  "total": 0,
  "items": [],
  "warnings": []
}
```

Пример с данными (иллюстрация структуры, не фактические значения):

```json
{
  "asOf": "2026-11-24T22:46:51+03:00",
  "period": {
    "from": "2026-11-23T22:46:51+03:00",
    "to": "2026-11-24T22:46:51+03:00",
    "hours": 24
  },
  "total": 12,
  "items": [
    { "code": 507, "name": "Проход разрешён", "category": "ok", "count": 9 },
    { "code": 501, "name": "Карта не найдена", "category": "error", "count": 3 }
  ],
  "warnings": []
}
```

**Поля.**

| Поле | Тип | Описание |
|---|---|---|
| `period.from` | date-time | Начало (включительно) |
| `period.to` | date-time | Конец (исключительно) |
| `period.hours` | int | Фактическая длина периода в часах; для явных дат — целое число, округление вниз |
| `total` | long | Сумма `items[].count` |
| `items[].code` | int | `EVENTS.ANALIT` |
| `items[].name` | string \| null | Название из конфига `Stat:Analytics:Names`; `null`, если не задано |
| `items[].category` | string | `ok`, `error`, `transit`, `unknown` по конфигу |
| `items[].count` | long | Количество событий |

**Правила расчёта.**

```sql
SELECT E.ANALIT AS CODE, COUNT(*) AS CNT
FROM EVENTS E
WHERE E.ANALIT IS NOT NULL
  AND E.ANALIT > 0
  AND E.DATETIME >= @fromDate
  AND E.DATETIME <  @toDate
GROUP BY E.ANALIT
ORDER BY 2 DESC, 1 ASC
```

* сортировка: по убыванию `count`, затем по возрастанию `code`;
* коды `<= 0` в выборку не попадают (совместимость с панелью, D-9);
* классификация: `ok` — `Stat:Analytics:OkCodes`, `error` — `ErrorCodes`, `transit` — `TransitCodes`, иначе `unknown`;
* если задан `analit`, в SQL добавляется `AND E.ANALIT IN (@a1, @a2, …)`; значения проверяются как целые ≥ 1;
* `ORDER BY` — по номеру колонки: устаревший движок не поддерживает сортировку по алиасу (см. п. 7.2).

**Ошибки.**

| HTTP | `error` | Когда |
|---|---|---|
| 400 | `VALIDATION_ERROR` | Некорректные `hours`, `analit`, `idDb` |
| 400 | `INVALID_PERIOD` | Задана одна дата, `from >= to`, неразбираемая дата |
| 400 | `PERIOD_TOO_LONG` | Период больше 365 суток |
| 400 | `UNSUPPORTED_CATEGORY` | Неизвестная `category` |
| 401 | `UNAUTHORIZED` | Нет токена |
| 500 | `INTERNAL_ERROR` | Ошибка БД |

---

### 5.13. Ф-08. GET `/api/v2/Stat/AnalyticsEvents`

**Назначение.** Детализация событий по коду `ANALIT` — целевой экран ссылки «Подробнее» в блоке 5.

**Авторизация.** Любая аутентифицированная роль.

**Параметры.**

| Имя | Тип | Обяз. | Допустимые значения | По умолчанию | Описание |
|---|---|---|---|---|---|
| `analit` | int | Да | ≥ 0 | — | Код аналитики; `0` разрешён для диагностики |
| `fromDate` | string | Нет | дата/дата-время | `to - hours` | Начало периода |
| `toDate` | string | Нет | дата/дата-время | `asOf` | Конец периода |
| `hours` | int | Нет | 1…8760 | 24 | Глубина, если даты не заданы |
| `pageIndex` | int | Нет | ≥ 0 | 0 | Номер страницы с нуля |
| `pageSize` | int | Нет | 1…1000 | 50 | Размер страницы |
| `order` | string | Нет | `asc`, `desc` | `desc` | Порядок по времени |
| `idDb` | int | Нет | ≥ 1 | — | Фильтр по `ID_DB` |

Правила периода — как в п. 5.12; дополнительно: отсутствие ключа `analit` в запросе — `400 VALIDATION_ERROR` (значение `0` допустимо, но должно быть передано явно).

**Ответ 200.**

```json
{
  "asOf": "2026-11-24T22:46:51+03:00",
  "analit": 0,
  "period": {
    "from": "2026-11-22T00:00:00+03:00",
    "to": "2026-11-25T00:00:00+03:00",
    "hours": 72
  },
  "context": [
    {
      "idEvent": 848,
      "datetime": "2026-11-24T22:04:24+03:00",
      "analit": 0,
      "eventTypeId": 30,
      "eventTypeName": null,
      "tabnum": "tn_SysAdmin",
      "surname": "системный администратор",
      "name": "",
      "patronymic": "",
      "idCard": null,
      "deviceId": null,
      "deviceName": null,
      "note": "tmp-update-root-220424"
    },
    {
      "idEvent": 847,
      "datetime": "2026-11-24T22:04:24+03:00",
      "analit": 0,
      "eventTypeId": 31,
      "eventTypeName": null,
      "tabnum": "tn_SysAdmin",
      "surname": "системный администратор",
      "name": "",
      "patronymic": "",
      "idCard": null,
      "deviceId": null,
      "deviceName": null,
      "note": "tmp-update-root-220424"
    }
  ],
  "pageIndex": 0,
  "pageSize": 2,
  "pageCount": 423,
  "total": 846,
  "warnings": []
}
```

**Поля элемента `context`.**

| Поле | Тип | Источник |
|---|---|---|
| `idEvent` | long | `EVENTS.ID_EVENT` |
| `datetime` | date-time | `EVENTS.DATETIME` |
| `analit` | int | `EVENTS.ANALIT` |
| `eventTypeId` | int \| null | `EVENTS.ID_EVENTTYPE` |
| `eventTypeName` | string \| null | `EVENTTYPE.NAME` |
| `tabnum` | string \| null | `PEOPLE.TABNUM` |
| `surname` | string \| null | `PEOPLE.SURNAME` |
| `name` | string \| null | `PEOPLE.NAME` |
| `patronymic` | string \| null | `PEOPLE.PATRONYMIC` |
| `idCard` | string \| null | `EVENTS.ID_CARD` |
| `deviceId` | int \| null | `EVENTS.ID_DEV` |
| `deviceName` | string \| null | `DEVICE.NAME` |
| `note` | string \| null | `EVENTS.NOTE` |

**SQL (эталон).**

```sql
-- total
SELECT COUNT(*) AS CNT
FROM EVENTS E
WHERE E.ANALIT = @analit
  AND E.DATETIME >= @fromDate
  AND E.DATETIME <  @toDate;

-- страница
SELECT FIRST @pageSize SKIP @offset
    E.ID_EVENT, E.DATETIME, E.ANALIT, E.ID_EVENTTYPE, E.ID_CARD, E.ID_DEV, E.NOTE,
    P.TABNUM, P.SURNAME, P.NAME, P.PATRONYMIC,
    ET.NAME AS EVENTTYPE_NAME,
    D.NAME  AS DEVICE_NAME
FROM EVENTS E
LEFT JOIN PEOPLE P ON P.ID_PEP = CASE WHEN E.ID_PEP > 0 THEN E.ID_PEP ELSE E.ESS1 END
LEFT JOIN EVENTTYPE ET ON ET.ID_EVENTTYPE = E.ID_EVENTTYPE
LEFT JOIN DEVICE D ON D.ID_DEV = E.ID_DEV
WHERE E.ANALIT = @analit
  AND E.DATETIME >= @fromDate
  AND E.DATETIME <  @toDate
ORDER BY E.DATETIME DESC, E.ID_EVENT DESC;
```

При `order=asc` — `ORDER BY E.DATETIME ASC, E.ID_EVENT ASC`.

**Правила.**

* `pageCount = ceil(total / pageSize)`; при `total = 0` — `0`;
* `total` считается тем же `WHERE`, что и страница, отдельным запросом;
* `offset = pageIndex * pageSize`; параметры `FIRST`/`SKIP` — целые, проверенные; подстановка строк недопустима;
* `FIRST/SKIP` используется только вместе с `ORDER BY`; без него страницы будут «плавать»;
* `CASE WHEN E.ID_PEP > 0 THEN E.ID_PEP ELSE E.ESS1 END` — совместимость со старыми событиями, где заполнен только `ESS1`; `COALESCE` не подходит, если `ID_PEP = 0`;
* строковые поля обрезаются справа; пустые строки возвращаются как `""`, отсутствующие — как `null`.

**Ошибки.** `400 VALIDATION_ERROR` (в т. ч. отсутствует `analit`, `pageSize` вне диапазона); `400 INVALID_PERIOD`; `400 PERIOD_TOO_LONG`; `400 UNSUPPORTED_ORDER`; `401`; `500 INTERNAL_ERROR`.

**Примечания.**

* Метод отдаёт «сырые» события, а не сгруппированный отчёт `event/event_analyt` (там группировка по устройству, `st_data` и разбор конфигурации). Полный аналог отчёта — вне объёма (Q-7).
* Для кодов, у которых в периоде нет событий, возвращается пустой `context` и `total = 0`; `404` не используется.

---

### 5.14. Ф-09. GET `/api/v2/Stat/RfidFormatErrors`

**Назначение.** Постраничный список карт с неверным форматом RFID — цель ссылки со счётчика `rfidFormatErrors`.

**Авторизация.** Любая аутентифицированная роль.

**Параметры.**

| Имя | Тип | Обяз. | Допустимые значения | По умолчанию | Описание |
|---|---|---|---|---|---|
| `cardType` | int | Нет | ≥ 1 | 1 | Тип карт |
| `pageIndex` | int | Нет | ≥ 0 | 0 | Номер страницы с нуля |
| `pageSize` | int | Нет | 1…1000 | 50 | Размер страницы |
| `idDb` | int | Нет | ≥ 1 | — | Фильтр по `ID_DB` |

**Ответ 200.**

```json
{
  "asOf": "2026-11-24T22:46:51+03:00",
  "cardType": 1,
  "context": [],
  "pageIndex": 0,
  "pageSize": 50,
  "pageCount": 0,
  "total": 0,
  "warnings": []
}
```

**Поля элемента `context`.**

| Поле | Тип | Источник |
|---|---|---|
| `idCard` | string | `CARD.ID_CARD` |
| `tabnum` | string \| null | `PEOPLE.TABNUM` |
| `surname` | string \| null | `PEOPLE.SURNAME` |
| `name` | string \| null | `PEOPLE.NAME` |
| `patronymic` | string \| null | `PEOPLE.PATRONYMIC` |
| `cardType` | int | `CARD.ID_CARDTYPE` |
| `active` | bool | `CARD."ACTIVE" > 0` |
| `timeEnd` | date-time \| null | `CARD.TIMEEND` |

**Правило отбора (совместимость с панелью).**

```sql
SELECT COUNT(*) FROM CARD C
WHERE C.ID_CARDTYPE = @cardType
  AND (C.ID_CARD LIKE '%a%' OR C.ID_CARD LIKE '%b%' OR C.ID_CARD LIKE '%c%'
    OR C.ID_CARD LIKE '%d%' OR C.ID_CARD LIKE '%e%' OR C.ID_CARD LIKE '%f%')
```

Постраничная выборка:

```sql
SELECT FIRST @pageSize SKIP @offset
    C.ID_CARD, C.ID_CARDTYPE, C."ACTIVE", C.TIMEEND,
    P.TABNUM, P.SURNAME, P.NAME, P.PATRONYMIC
FROM CARD C
LEFT JOIN PEOPLE P ON P.ID_PEP = C.ID_PEP AND P.ID_DB = C.ID_DB
WHERE C.ID_CARDTYPE = @cardType
  AND (C.ID_CARD LIKE '%a%' OR C.ID_CARD LIKE '%b%' OR C.ID_CARD LIKE '%c%'
    OR C.ID_CARD LIKE '%d%' OR C.ID_CARD LIKE '%e%' OR C.ID_CARD LIKE '%f%')
ORDER BY C.ID_CARD ASC
```

**Правила.**

* `total` равен `rfidFormatErrors` метода `Stat/People` при том же `cardType` и `idDb`;
* проверяются только строчные латинские `a`…`f` (совместимость с текущей панелью); цифры и `A`…`F` считаются корректными;
* «строгий» режим (любой символ вне `0-9A-F`) в MVP не реализуется: устаревший движок не поддерживает `SIMILAR TO` и `CHAR_LENGTH` (см. п. 7.2); при необходимости — отдельная задача (Q-8).

**Ошибки.** `400 VALIDATION_ERROR`, `401`, `500 INTERNAL_ERROR`.

---

## 6. Сводные модели, ошибки и предупреждения

### 6.1. Публичные DTO

| Класс | Назначение |
|---|---|
| `StatDashboardResponse` | Ответ `Dashboard` |
| `StatPeopleSection` | Секция `people` |
| `StatGuestSection` | Секция `guest` |
| `StatEquipmentSection` | Секция `equipment` |
| `StatLoadQueueSection` | Секция `loadQueue` |
| `StatAnalyticsSection` | Секция `analytics` |
| `StatSystemSection` | Секция `system` |
| `StatPeopleResponse : StatPeopleSection` | Ответ `People` (+ `asOf`, `expiringDays`, `warnings`) |
| `StatGuestResponse : StatGuestSection` | Ответ `Guest` |
| `StatEquipmentResponse : StatEquipmentSection` | Ответ `Equipment` |
| `StatLoadQueueResponse : StatLoadQueueSection` | Ответ `LoadQueue` |
| `StatAnalyticsResponse : StatAnalyticsSection` | Ответ `Analytics` |
| `StatSystemResponse : StatSystemSection` | Ответ `System` |
| `StatAnalyticsEventsResponse` | Ответ `AnalyticsEvents` |
| `StatAnalyticsEventItem` | Элемент `context` |
| `StatRfidFormatErrorsResponse` | Ответ `RfidFormatErrors` |
| `StatRfidFormatErrorItem` | Элемент `context` |
| `StatPeriod` | `from`, `to`, `hours` |
| `StatAnalyticsItem` | `code`, `name`, `category`, `count` |
| `StatWarning` | `code`, `section`, `message` |
| `StatOptions` | Конфигурация (см. п. 8.2) |

Требования к DTO:

* `System.Text.Json`, camelCase по умолчанию;
* `DateTimeOffset` для всех дат — сериализуется с смещением;
* `long` для счётчиков;
* `string?` для необязательных строк; пустые строки не заменяются на `null` без основания;
* `[JsonPropertyName]` — только при необходимости (например, `tabnum`);
* XML-комментарии на русском — для Swagger.

### 6.2. Коды ошибок

| `error` | HTTP | Назначение |
|---|---|---|
| `VALIDATION_ERROR` | 400 | Некорректный параметр/тело |
| `INVALID_PERIOD` | 400 | Неверный период дат |
| `PERIOD_TOO_LONG` | 400 | Период больше 365 суток |
| `UNSUPPORTED_SECTION` | 400 | Неизвестная секция `Dashboard` |
| `UNSUPPORTED_CATEGORY` | 400 | Неизвестная категория аналитики |
| `UNSUPPORTED_ORDER` | 400 | Неизвестный порядок сортировки |
| `UNAUTHORIZED` | 401 | Нет/просрочен токен |
| `FORBIDDEN` | 403 | Нет роли `admin` для `System` |
| `INTERNAL_ERROR` | 500 | Ошибка БД/внутренняя ошибка |

Формат:

```json
{
  "error": "VALIDATION_ERROR",
  "message": "Параметр pageSize должен быть целым числом от 1 до 1000",
  "details": [
    { "field": "pageSize", "issue": "invalid_value" }
  ]
}
```

### 6.3. Предупреждения (HTTP 200)

| `code` | Где | Значение |
|---|---|---|
| `SECTION_UNAVAILABLE` | `Dashboard` | Секция не рассчитана; поле = `null` |
| `GUEST_ORG_NOT_FOUND` | `Guest`, `Dashboard` | Организация гостей не найдена, использован fallback |
| `ARCHIVE_ORG_NOT_FOUND` | `Guest`, `Dashboard` | Организация архива не найдена, использован fallback |
| `ESTIMATE_UNAVAILABLE` | `System` | Не удалось получить оценку по индексам |
| `SYSTEM_METADATA_UNAVAILABLE` | `System` | Не удалось разобрать параметры подключения |

---

## 7. Нефункциональные требования

### 7.1. Производительность

| ID | Требование |
|---|---|
| НФ-01 | `Stat/People`, `Guest`, `Equipment`, `LoadQueue`, `RfidFormatErrors` — p95 ≤ 500 мс на эталонной базе (79 сотрудников, 846 событий) и ≤ 1,5 с на проектной (100 000 сотрудников, 10 млн событий) при наличии индексов п. 8.6. |
| НФ-02 | `Stat/Analytics` — p95 ≤ 700 мс на эталонной и ≤ 2 с на проектной базе. |
| НФ-03 | `Stat/Dashboard` без `system` — p95 ≤ 1,5 с на эталонной и ≤ 3 с на проектной базе. |
| НФ-04 | `Stat/System` с `counts=exact` — ≤ 5 с; при превышении — кэш 60 с и запись в лог. |
| НФ-05 | `CommandTimeout` запросов — 10 с; по истечении — `500 INTERNAL_ERROR` с записью в лог. |
| НФ-06 | Каждая секция/метод — не более одного SQL-запроса на показатель; запрещены запросы в цикле (N+1). |
| НФ-07 | Тяжёлые запросы не блокируют пул: используются отдельные `FbConnection` на запрос, как в `DatabaseService`. |

### 7.2. Совместимость с устаревшим SQL-движком

База `shieldpro_rest.GDB` обслуживается устаревшим InterBase/Firebird-совместимым движком (dialect 3). Проверено на реальной базе 24.11.2026:

| Конструкция | Поддержка | Следствие |
|---|---|---|
| `TRIM`, `RTRIM` | Нет (`Function unknown`) | Обрезка строк — в C# (`TrimEnd`) |
| `SIMILAR TO` | Нет (`Token unknown`) | Регулярные проверки — только `LIKE` |
| `CHAR_LENGTH` | Нет | Не использовать |
| `RDB$GET_CONTEXT` | Нет | Версию движка не запрашивать |
| `ROWS … TO …` | Нет | Только `FIRST … SKIP …` |
| `FIRST`/`SKIP` с параметрами | Да | `FIRST @pageSize SKIP @offset` допустим |
| `ORDER BY` по алиасу | Нет (`Column unknown`) | Сортировка по номеру колонки/выражению |
| `COALESCE`, `CASE`, `SUBSTRING`, `CAST`, `UPPER`, `COUNT(DISTINCT)` | Да | Использовать |
| `CURRENT_TIMESTAMP` | Да | Но для согласованности передавать `@now` |

Дополнительно:

* `DatabaseService.Get<T>()` выполняет `query.ToUpper()` — для запросов `Stat` его использовать нельзя (портит строковые литералы и регистр). Использовать `GetListParameterized<T>()`, `GetParameterized<T>()` или прямой `FbCommand`.
* SQL-параметры обязательны; конкатенация пользовательских значений недопустима (кроме проверенных целых `pageSize`/`offset`, если движок требует литерал).
* `ORDER BY` — только по номерам колонок или выражениям, не по алиасам.
* `FIRST/SKIP` без `ORDER BY` возвращает строки в произвольном порядке; для пагинации обязателен детерминированный `ORDER BY` (например, `E.DATETIME DESC, E.ID_EVENT DESC`).

### 7.3. Безопасность

| ID | Требование |
|---|---|
| НФ-08 | Все методы — `[Authorize]`; `System` — `[Authorize(Policy = "Admin")]`. |
| НФ-09 | Пароль БД, полная строка подключения, логины БД не возвращаются и не пишутся в лог. |
| НФ-10 | Только параметризованные запросы; входные целые проверяются по диапазону. |
| НФ-11 | Рекомендуется отдельный пользователь БД с правом только `SELECT` на используемые таблицы. |
| НФ-12 | Персональные данные (ФИО, `tabnum`, номер карты) не логируются; в лог — только количество строк. |
| НФ-13 | `System` не должен раскрывать инфраструктуру неадминистраторам. |
| НФ-14 | CORS-политика не меняется (`AllowAll` уже настроена в `Program.cs`). |

### 7.4. Кодировки

| ID | Требование |
|---|---|
| НФ-15 | Подключение к БД — `Charset=win1251`; строки в .NET уже в Unicode. |
| НФ-16 | Ответ — UTF-8; API не выполняет `iconv`. |
| НФ-17 | Экранирование не-ASCII в JSON (`\uXXXX`) допустимо; клиент обязан декодировать в UTF-8. |
| НФ-18 | Клиент Artonit City не должен повторно применять `iconv` к данным API (см. п. 9.5). |

### 7.5. Логирование и наблюдаемость

| ID | Требование |
|---|---|
| НФ-19 | Serilog: метод, длительность, количество строк, `idDb`, `asOf`; без ПДн. |
| НФ-20 | Ошибки БД — с текстом `FbException` и именем метода; SQL с параметрами — на уровне Debug. |
| НФ-21 | Предупреждения секций (`SECTION_UNAVAILABLE` и др.) логируются как Warning. |

### 7.6. Надёжность и деградация

| ID | Требование |
|---|---|
| НФ-22 | `Dashboard` устойчив к отказу отдельной секции (частичный ответ + `warnings`). |
| НФ-23 | Клиент при недоступности API переходит на старый SQL-путь (флаг конфигурации), панель не отдаёт 500. |
| НФ-24 | Клиент кэширует ответ `Dashboard` на 30 с; кэш — файловый, ключ включает параметры. |
| НФ-25 | Таймауты клиента: соединение 2 с, ответ 5 с; одна повторная попытка после `401`. |

### 7.7. Документирование

| ID | Требование |
|---|---|
| НФ-26 | Swagger v2 содержит тег `StatV2` и 9 операций с описаниями, параметрами и примерами. |
| НФ-27 | `docs/STAT_API_v2.md` — пользовательская документация с примерами. |
| НФ-28 | `REST_API_v2.md` — строка о новой версии 1.7.0 и ссылка на `STAT_API_v2.md`. |
| НФ-29 | `docs/smoke-tests/stat.ps1` — дымовой тест в стиле существующих. |

---

## 8. Требования к реализации серверной части

### 8.1. Состав файлов

| Файл | Содержимое |
|---|---|
| `APIControllers/StatV2Controller.cs` | 9 методов, валидация параметров, маппинг в DTO, коды ответов |
| `DBControllers/StatDBController.cs` | SQL-запросы, параметризация, расчёт, `StatResult<T>` |
| `DBControllers/StatRecords.cs` | Внутренние записи БД (если нужны) |
| `Model/Stat.cs` | Публичные DTO и `StatOptions` |
| `Model/StatAnalyticsOptions.cs` | Классификация кодов (или в `StatOptions`) |
| `docs/STAT_API_v2.md` | Документация |
| `docs/smoke-tests/stat.ps1` | Дымовой тест |
| `docs/migrations/stat_indexes.sql` | Необязательный скрипт индексов |

### 8.2. Конфигурация

В `appsettings.json`:

```json
{
  "Stat": {
    "DefaultExpiringDays": 30,
    "DefaultAnalyticsHours": 24,
    "MaxPeriodDays": 365,
    "MaxPageSize": 1000,
    "DefaultPageSize": 50,
    "GuestDivcode": "dv_Guest",
    "ArchiveDivcode": "dv_Archive",
    "GuestFallbackId": 2,
    "ArchiveFallbackId": 3,
    "RfidCardType": 1,
    "SystemCacheSeconds": 60,
    "Analytics": {
      "OkCodes": [507, 509, 650, 651, 652, 653, 654, 655, 656, 480],
      "ErrorCodes": [500, 501, 502, 503, 504, 505, 506, 508, 657, 658, 46, 481, 5071, 5091],
      "TransitCodes": [5001, 5011, 5021, 5031, 5041, 5051, 5061],
      "Names": {}
    }
  }
}
```

Требования:

* значения по умолчанию дублируются в коде, чтобы API работал без секции `Stat`;
* `Names` — необязательный словарь `"507": "Проход разрешён"`; при отсутствии `name = null`;
* конфигурация валидируется при старте: отрицательные значения и `MaxPageSize > 1000` — ошибка запуска (fail fast);
* секреты в `Stat` не хранятся.

### 8.3. Структура контроллера

```csharp
[ApiController]
[ApiExplorerSettings(GroupName = "v2")]
[Route("api/v2/Stat")]
[Authorize]
[Produces("application/json")]
public sealed class StatV2Controller : V2ControllerBase
{
    private readonly StatOptions _options;
    private readonly ILogger<StatV2Controller> _logger;

    public StatV2Controller(IOptions<StatOptions> options, ILogger<StatV2Controller> logger)
    {
        _options = options.Value;
        _logger = logger;
    }

    [HttpGet("People")]
    [ProducesResponseType(typeof(StatPeopleResponse), StatusCodes.Status200OK)]
    [ProducesResponseType(typeof(ApiError), StatusCodes.Status400BadRequest)]
    [ProducesResponseType(typeof(ApiError), StatusCodes.Status401Unauthorized)]
    [ProducesResponseType(typeof(ApiError), StatusCodes.Status500InternalServerError)]
    public IActionResult GetPeople(
        [FromQuery] int? expiringDays,
        [FromQuery] int? cardType,
        [FromQuery] int? idDb)
    {
        // валидация → StatDBController.GetPeople(...) → Ok(response)
    }

    // Guest, Equipment, LoadQueue, Analytics, AnalyticsEvents, RfidFormatErrors, Dashboard, System
}
```

Требования к контроллеру:

* валидация — до обращения к БД; сообщения — на русском, как в существующих методах;
* для `System` — `[Authorize(Policy = AdminPolicy)]` (константа `BaseAPIController.AdminPolicy`);
* `asOf` запрашивается один раз в начале метода и передаётся в слой БД;
* ошибки БД → `ApiErrorResults.Internal()`; ошибки валидации → `ApiErrorResults.Validation(...)`;
* новые коды ошибок добавляются в `ApiErrorResults` константами (не «магическими» строками);
* XML-комментарии обязательны: summary, param, response.

### 8.4. Слой БД

```csharp
public static class StatDBController
{
    public static StatResult<StatPeopleData> GetPeople(
        DateTime now, int expiringDays, int cardType, int? idDb) { ... }

    public static StatResult<StatGuestData> GetGuest(DateTime now, int? idDb) { ... }

    public static StatResult<StatEquipmentData> GetEquipment(DateTime now, int? idDb) { ... }

    public static StatResult<StatLoadQueueData> GetLoadQueue(DateTime now, int cardType, int? idDb) { ... }

    public static StatResult<StatSystemData> GetSystem(DateTime now, bool exact, bool estimated, int? idDb) { ... }

    public static StatResult<List<StatAnalyticsItem>> GetAnalytics(
        DateTime from, DateTime to, IReadOnlyCollection<int>? codes, int? idDb) { ... }

    public static StatResult<StatAnalyticsEventsData> GetAnalyticsEvents(
        DateTime from, DateTime to, int analit, int pageIndex, int pageSize, bool desc, int? idDb) { ... }

    public static StatResult<StatRfidErrorsData> GetRfidErrors(
        int cardType, int pageIndex, int pageSize, int? idDb) { ... }
}
```

Требования:

* один `FbConnection` на вызов; `using`;
* параметры — `cmd.Parameters.AddWithValue("@name", value)`; `null` → `DBNull.Value`;
* результат — `StatResult<T>` со статусом (`Success`, `DatabaseError`) и сообщением; контроллер преобразует в HTTP;
* никаких `SELECT *`; только перечисленные колонки;
* `DatabaseService.GetListParameterized<T>` допустим, если DTO совместим с `[DatabaseName]`; иначе — ручной `FbDataReader`;
* `DateTime` из БД — `DateTimeKind.Unspecified`; преобразование в `DateTimeOffset` с локальным смещением сервера БД (см. п. 8.5).

### 8.5. Время и часовые пояса

1. `asOf` — `SELECT CURRENT_TIMESTAMP FROM RDB$DATABASE` (тип `DateTime`).
2. Смещение берётся из `TimeZoneInfo.Local` хоста API; предполагается, что хост API и сервер БД находятся в одном часовом поясе (иначе — Q-5).
3. Все SQL-сравнения используют `@now` = `asOf`, а не `CURRENT_TIMESTAMP`, чтобы избежать расхождения между секциями.
4. `expiringBefore = asOf.Date.AddDays(expiringDays).AddHours(23).AddMinutes(59).AddSeconds(59)`.
5. В JSON — `DateTimeOffset`; при сериализации сохраняется смещение.

### 8.6. Индексы (рекомендация DBA)

Скрипт `docs/migrations/stat_indexes.sql` не выполняется автоматически. Перед применением — согласовать с администратором БД и проверить на копии.

```sql
CREATE INDEX IDX_PEOPLE_ID_ORG      ON PEOPLE (ID_ORG);
CREATE INDEX IDX_CARD_ID_PEP        ON CARD (ID_PEP);
CREATE INDEX IDX_CARD_TIMEEND       ON CARD (TIMEEND);
CREATE INDEX IDX_CARD_ID_CARDTYPE   ON CARD (ID_CARDTYPE);
CREATE INDEX IDX_EVENTS_DT_ANALIT   ON EVENTS (DATETIME, ANALIT);
CREATE INDEX IDX_EVENTS_ANALIT_DT   ON EVENTS (ANALIT, DATETIME);
CREATE INDEX IDX_EVENTS_ID_PEP      ON EVENTS (ID_PEP);
CREATE INDEX IDX_CARDINDEV_ID_CARD  ON CARDINDEV (ID_CARD);
```

Примечания:

* индексы создаются только если их ещё нет; проверка — по `RDB$INDICES`;
* на устаревшем движке `CREATE INDEX` может требовать монопольного доступа; выполнять в окно обслуживания;
* `IDX_EVENTS_DT_ANALIT` и `IDX_EVENTS_ANALIT_DT` перекрываются по назначению; выбрать один вариант по фактическим планам (рекомендуется `(ANALIT, DATETIME)` для `Analytics` и `(DATETIME, ANALIT)` для `AnalyticsEvents` — при необходимости оставить оба).

### 8.7. Версионирование и выпуск

| ID | Требование |
|---|---|
| С-01 | Версия сборки и `FileVersion` — 1.7.0. |
| С-02 | Swagger v2: заголовок версии 1.7.0, новый тег `StatV2`. |
| С-03 | Обратная совместимость: существующие маршруты и их ответы не меняются. |
| С-04 | Изменения только в новых файлах + `Program.cs` (регистрация `StatOptions`) + `appsettings.json`. |
| С-05 | Релиз — после прохождения smoke-тестов `stat.ps1`, `access.ps1`, `organizations.ps1`. |

---

## 9. Требования к клиентской части Artonit City

### 9.1. Архитектура клиента

```
Controller_dashboard
  → Service_Dashboard
      → Model_StatApi (cURL, JWT, кэш)  ──HTTP──→  Artonit REST API v2
      ↘ fallback: Model_Summary / Model_Stat / Model_People / Model_Parkdb → Firebird (ODBC)
  → View dashboard/dashboard
```

### 9.2. Конфигурация модуля

Новый файл `modules/dashboard/config/dashboard.php`:

```php
<?php defined('SYSPATH') or die('No direct script access.');

return array(
    'stat_api' => array(
        'enabled'              => getenv('DASHBOARD_STAT_API') === '1',
        'base_url'             => getenv('DASHBOARD_STAT_API_URL') ?: 'http://localhost:5056',
        'login_path'           => '/login',
        'login_username'       => getenv('DASHBOARD_STAT_API_USER') ?: 'ADMIN',
        'login_password'       => getenv('DASHBOARD_STAT_API_PASSWORD') ?: '333',
        'login_password_field' => 'pasword',
        'token_ttl'            => 3600,
        'connect_timeout'      => 2,
        'timeout'              => 5,
        'cache_lifetime'       => 30,
        'fallback_to_sql'      => true,
        'sections'             => 'people,guest,equipment,loadQueue,analytics',
        'expiring_days'        => 30,
        'analytics_hours'      => 24,
    ),
);
```

Требования:

* учётные данные — только через переменные окружения или локальный конфиг, не в git (значения по умолчанию `ADMIN`/`333` — только для локального стенда);
* `enabled` по умолчанию `false` — включение после приёмки;
* `fallback_to_sql = true` — при ошибке API панель работает как раньше;
* таймауты обязательны, иначе панель «зависает» при недоступном API.

### 9.3. HTTP-клиент `Model_StatApi`

Требования:

| ID | Требование |
|---|---|
| К-01 | Класс `Model_StatApi` в `modules/dashboard/classes/Model/StatApi.php`. |
| К-02 | Логин `GET /login?username=…&pasword=…`, извлечение `data.token`; кэш токена в файловом кэше Kohana на `token_ttl`. |
| К-03 | Повтор запроса один раз после `401` (сброс токена и перелогин). |
| К-04 | cURL: `RETURNTRANSFER`, `CONNECTTIMEOUT`, `TIMEOUT`, `HTTPHEADER` (`Accept: application/json`, `Authorization: Bearer …`). |
| К-05 | `json_decode($raw, TRUE)`; при ошибке JSON — исключение `Kohana_Exception` с URL и статусом. |
| К-06 | Методы-обёртки: `people()`, `guest()`, `equipment()`, `loadQueue()`, `system()`, `analytics()`, `analyticsEvents()`, `rfidErrors()`, `dashboard()`. |
| К-07 | Параметры query — `http_build_query`, без ручной конкатенации. |
| К-08 | Кэш ответов — `Cache::instance('file')`, ключ `dashboard_stat_<sha1(параметры)>`, TTL 30 с; сброс при `?refresh=1`. |
| К-09 | Ошибки API не пробрасываются в HTML; клиент возвращает `NULL`/`FALSE` и пишет `Log::instance()->add(Log::ERROR, …)`. |
| К-10 | PHP 5.6: без `??`, скалярных тайп-хинтов, `return type`, анонимных классов; массивы — `array()`. |

### 9.4. Изменения `Service_Dashboard`

| ID | Требование |
|---|---|
| К-11 | `getDashboardData($config_windows)` при `stat_api.enabled` вызывает `dashboard()` один раз (состав секций зависит от прав пользователя City: `system` — только для администратора) и маппит секции в существующие ключи представления. |
| К-12 | При `fallback_to_sql` и ошибке API — вызывать текущие модели (`Model_Summary`, `Model_Stat`, `Model_People`, `Model_identifier`, `Model_Parkdb`). |
| К-13 | Добавить в результат `guest` и передавать его в представление как `list_windowsGuest` (исправление D-1). |
| К-14 | `checkRfidKeyFormat()` при работе через API не выполнять; счётчик брать из `people.rfidFormatErrors`. |
| К-15 | Маппинг полей — по таблице п. 5.4; имена ключей представления не меняются. |
| К-16 | `about` для окна 4: при API — из `system`, при fallback — из `Model_Parkdb`. |
| К-17 | Логировать факт fallback и причину (без ПДн). |
| К-18 | Не менять сигнатуру `getDashboardData()` и других публичных методов, чтобы не затронуть другие точки вызова. |

Пример маппинга (имена ключей — существующие, представление не меняется):

```php
$windows1 = array(
    'people_count'              => $people['peopleTotal'],
    'key_people_delete'         => $people['peopleInactive'],
    'getPeopleCardCount'        => $people['cardsTotal'],
    'count_card_late_next_week' => $people['cardsExpiring'],
    'timeExpired'               => date('d.m.Y', strtotime($people['expiringBefore'])),
    'getcardexpired'            => $people['cardsExpired'],
    'getPeopleWithoutCard'      => $people['peopleWithoutCard'],
    'getCardNotActive'          => $people['cardsInactive'],
);

$windowsGuest = array(
    'guestCount'            => $guest['guestCount'],
    'guestArchiveCount'     => $guest['guestArchiveCount'],
    'guestCardCount'        => $guest['guestCardCount'],
    'guestArchiveCardCount' => $guest['guestArchiveCardCount'],
);

$about = array(
    'connectName'  => 'REST API v2',
    'dsn'          => 'rest:' . $system['server'] . ':' . $system['port'],
    'pathDB'       => $system['databasePath'],
    'Server'       => $system['server'],
    'countTable'   => array('EVENTS' => $system['eventsCount']),
    'minEventDate' => $system['minEventDate'],
);
```

### 9.5. Изменения представления

| ID | Требование |
|---|---|
| К-19 | Блок «Бюро пропусков»: использовать переданный `list_windowsGuest`; при отсутствии данных — `0`, а не пустой блок. |
| К-20 | Окно 4: убрать двойной `iconv`. Данные API уже в UTF-8; для fallback `Model_Parkdb` привести к UTF-8 на стороне модели/сервиса, а не в представлении. |
| К-21 | Окно 5: при наличии `items[].name` использовать его, иначе — текущий i18n-ключ `__($analit.'a')`; цвет — по `category` или по конфигу `analit_err` (сохранить текущее поведение). |
| К-22 | Ссылки «Подробнее» и «ErrKeyFormatRfid» не менять; они ведут на существующие экраны. |
| К-23 | Никаких новых JavaScript-зависимостей. |
| К-24 | При отключённом API представление работает без изменений (обратная совместимость). |

### 9.6. Обработка ошибок и деградация

| ID | Требование |
|---|---|
| К-25 | Ошибка API не должна приводить к белой странице или `HTTP 500`. |
| К-26 | Если API вернул `null`-секцию с предупреждением, панель показывает `0`/«нет данных» и продолжает работу. |
| К-27 | В `application/logs` — запись с методом, URL, HTTP-кодом и текстом ошибки; без ФИО и номеров карт. |
| К-28 | При `fallback_to_sql = false` и недоступном API — панель показывает сообщение «Данные временно недоступны», а не падает. |
| К-29 | Секция `system` запрашивается только при наличии у пользователя City прав администратора; для обычных пользователей она не запрашивается, и блок 4 показывает только данные City. Разграничение прав выполняет City, а не API. |

---

## 10. Тестирование и приёмка

### 10.1. Виды испытаний

| Вид | Что проверяется |
|---|---|
| Модульные (C#) | расчёт `expiringBefore`, классификация кодов, валидация параметров, `pageCount` |
| Интеграционные | SQL на реальной БД, кодировка, типы, пустые данные |
| Контрактные (smoke) | HTTP-коды, схема JSON, обязательные поля, права |
| Согласованность | `Dashboard` = индивидуальные методы; `rfidFormatErrors` = `total`; `Analytics.total` = сумма `items` |
| Регрессия | существующие 31 операция v2 и панель |
| Клиентские | панель в браузере, отсутствие PHP notice/warning, время загрузки |

### 10.2. Дымовой тест `docs/smoke-tests/stat.ps1`

Стиль — как в `access.ps1`/`organizations.ps1`; тест только читает данные и не создаёт записей.

| ID | Проверка | Ожидание |
|---|---|---|
| Т-01 | `GET /login` с `ADMIN/333` | `200`, есть `data.token` |
| Т-02 | `GET /Stat/People` без токена | `401`, `error=UNAUTHORIZED` |
| Т-03 | `GET /Stat/People` с токеном | `200`, `application/json`, обязательные поля, типы |
| Т-04 | `GET /Stat/People?expiringDays=30&cardType=1` | значения совпадают с SQL-эталоном |
| Т-05 | `GET /Stat/Guest` | `guestCount/guestArchiveCount` совпадают с SQL |
| Т-06 | `GET /Stat/Equipment` | `transportServers=2`, `controllers=0` на эталонной базе |
| Т-07 | `GET /Stat/LoadQueue` | `cardsPendingLoad`, `recordsPendingLoad`, `devicesInQueue` — целые ≥ 0 |
| Т-08 | `GET /Stat/System` под `admin` | `200`, `eventsCount` равен `SELECT COUNT(*) FROM EVENTS` на момент проверки, `minEventDate` не пусто |
| Т-09 | `GET /Stat/System` под `user` | `403`, `error=FORBIDDEN` |
| Т-10 | `rfidFormatErrors` vs `RfidFormatErrors.total` | равны при одинаковых `cardType`/`idDb` |
| Т-11 | `Analytics.total` vs сумма `items[].count` | равны |
| Т-12 | `Dashboard.people` vs `People` | равны при одинаковых параметрах |
| Т-13 | `Dashboard.guest/equipment/loadQueue/analytics` vs соответствующие методы | равны |
| Т-14 | `AnalyticsEvents` для `analit=0`, период 22.11.2026 00:00 — 24.11.2026 00:00 | `total=817`, `pageSize=2` → `pageCount=409`, 2 элемента |
| Т-15 | `?pageSize=0`, `?fromDate=2026-11-25&toDate=2026-11-24`, `?sections=foo` | `400` с соответствующим `error` |
| Т-16 | Кириллица в `surname`/`name` | корректные символы, без `?` и кракозябр |
| Т-17 | `Content-Type` | `application/json; charset=utf-8` |
| Т-18 | Swagger v2 | содержит 9 операций `StatV2`, все с описаниями |

### 10.3. Матрица приёмки (эталонная база, 24.11.2026)

| Показатель | Панель (факт) | API (ожидание) | Допуск |
|---|---|---|---|
| Пользователи | 79 | `peopleTotal=79` | точно |
| Активные | 79 | `peopleActive=79` | точно |
| Удалённые | 0 | `peopleInactive=0` | точно |
| Без карты | 79 | `peopleWithoutCard=79` | точно |
| Карты типа 1 | 0 | `cardsTotal=0` | точно |
| Просроченные | 0 | `cardsExpired=0` | точно |
| Истекают до 24.12.2026 | 0 | `cardsExpiring=0` | точно |
| Неактивные карты | 0 | `cardsInactive=0` | точно |
| RFID-ошибки | 0 | `rfidFormatErrors=0` | точно |
| Гости | (не видно) | `guestCount=3` | точно |
| Гости в архиве | (не видно) | `guestArchiveCount=3` | точно |
| Гостевые карты | (не видно) | `guestCardCount=0` | точно |
| Карты в архиве | (не видно) | `guestArchiveCardCount=0` | точно |
| ТС | 2 | `transportServers=2` | точно |
| ТС активные | — | `transportServersActive=1` | точно |
| Контроллеры | 0 | `controllers=0` | точно |
| Очередь (карты) | 0 | `cardsPendingLoad=0` | точно |
| Очередь (записи) | 0 | `recordsPendingLoad=0` | точно |
| События | 0 (дефект) | `eventsCount` = `COUNT(*)` (846 на 24.11.2026 22:46) | точно |
| Оценка событий | 0 | `eventsCountEstimated=0` | точно |
| Min дата события | 2026-11-22 12:06:31 | `2026-11-22T12:06:31+03:00` | точно |
| Аналитика за 24 ч | «Нет данных» | `total=0`, `items=[]` | точно |

Примечания:

* значения зафиксированы на 24.11.2026 22:46:51; при приёмке сравнивать панель и API в одном окне (не более 1 минуты) либо использовать фиксированный период в прошлом;
* таблица `EVENTS` пополняется, поэтому `eventsCount` и `Analytics` проверять на фиксированном периоде в прошлом (например, 22.11.2026 00:00 — 24.11.2026 00:00, 817 событий на момент обследования);
* для проверки непустой аналитики допускается подготовить тестовые события с `ANALIT > 0` в отдельной копии базы (не в продуктивной).

### 10.4. Регрессия

| ID | Проверка |
|---|---|
| Т-19 | `docs/smoke-tests/access.ps1` — без ошибок |
| Т-20 | `docs/smoke-tests/organizations.ps1` — без ошибок |
| Т-21 | Swagger v1 и v2 открываются, v1 не изменился |
| Т-22 | Все существующие методы v2 отвечают как до изменений (выборочно: `Person/GetList`, `Identifier/GetList`, `organizations`, `Access/GetList`) |
| Т-23 | Панель `/city/index.php/dashboard` открывается, все 5 блоков отображаются |
| Т-24 | В `application/logs` нет `PHP Notice`/`Warning`/`Fatal` по модулю dashboard |
| Т-25 | Время загрузки панели ≤ 3 с при включённом API |

### 10.5. Критерии приёмки (Definition of Done)

1. 9 методов реализованы и доступны в Swagger v2.
2. Все тесты Т-01…Т-18 проходят; регрессия Т-19…Т-25 — без ошибок.
3. Показатели панели совпадают с API по матрице п. 10.3 (с учётом согласованных исправлений D-1…D-10).
4. `docs/STAT_API_v2.md`, `docs/smoke-tests/stat.ps1`, `REST_API_v2.md` обновлены.
5. Версия API — 1.7.0; релиз собран и развёрнут на тестовом стенде.
6. Клиент работает при включённом API; при отключённом — прежнее поведение.
7. Нет ПДн и секретов в логах; `System` недоступен неадминистратору.
8. Проведена демонстрация и подписан акт приёмки.

---

## 11. Этапы, приоритеты и оценка

| Этап | Содержание | Результат | Оценка, чел.-дн. |
|---|---|---|---|
| 1 | Согласование ТЗ, ответы на Q-1…Q-10 | Утверждённое ТЗ | 1–2 |
| 2 | Сервер: DTO, `StatV2Controller`, `StatDBController`, 7 методов P0 | Swagger + код | 4–5 |
| 3 | Сервер: `AnalyticsEvents`, `RfidFormatErrors` (P1), тесты | Код + smoke | 2 |
| 4 | Клиент: `StatApi`, конфиг, `Service_Dashboard` | Панель на API | 2–3 |
| 5 | Клиент: представления, fallback, исправление D-1…D-4 и D-10 | Панель без дефектов | 1–2 |
| 6 | Приёмочные испытания, документация, релиз | Акт приёмки | 1–2 |
| **Итого** | | | **11–16** |

Приоритеты:

* **P0 (релиз 1.7.0):** `Dashboard`, `People`, `Guest`, `Equipment`, `LoadQueue`, `System`, `Analytics`.
* **P1 (1.7.x):** `AnalyticsEvents`, `RfidFormatErrors`; до их готовности ссылки «Подробнее» и счётчик RFID остаются на старых экранах.

---

## 12. Риски

| ID | Риск | Вероятность | Влияние | Меры |
|---|---|---|---|---|
| R-1 | Устаревший SQL-движок не поддержит запланированные конструкции | Средняя | Высокое | Ограничения зафиксированы в п. 7.2 и проверены на реальной базе; сложные проверки — в C# |
| R-2 | Различия схемы на других установках (`DIVCODE`, `SS_ACCESSORG`) | Средняя | Среднее | Конфиг + fallback + предупреждения; feature detection |
| R-3 | Точный `COUNT(*)` по `EVENTS` медленный | Средняя | Среднее | Индексы, кэш 60 с, `counts=estimated` |
| R-4 | Расхождение часов API и БД | Низкая | Среднее | `asOf` из БД; проверка TZ при старте |
| R-5 | Слабая аутентификация API v2 (любой пользователь с паролем `333` либо `ADMIN` с любым паролем) | Высокая | Высокое | Вне объёма; рекомендуется отдельный аудит и смена пароля; `System` — только admin |
| R-6 | Данные неполные (`ANALIT=0`, нет устройств) | Высокая | Низкое | Тесты на пустых данных; фиксированные периоды |
| R-7 | Недоступность API | Средняя | Высокое | Fallback на SQL, таймауты, кэш |
| R-8 | Двойное перекодирование на клиенте | Средняя | Среднее | Убрать `iconv` для данных API (К-20) |
| R-9 | Изменение чисел на панели после исправлений D-1…D-10 | Высокая | Среднее | Согласовать решения до разработки; отразить в акте |
| R-10 | Раскрытие инфраструктуры через `System` | Низкая | Среднее | Только admin, без секретов |

---

## 13. Открытые вопросы

| ID | Вопрос | Вариант по умолчанию |
|---|---|---|
| Q-1 | Включать ли блок «Бюро пропусков» в панель и считать ли его показатели обязательными? | Да, включать; `Stat/Guest` — P0 |
| Q-2 | Организации гостей/архива определять по `DIVCODE` (`dv_Guest`, `dv_Archive`) или по `ID_ORG` 2/3? | По `DIVCODE`, fallback на 2/3 |
| Q-3 | Нужен ли фильтр `idDb` во всех методах? | Да, необязательный; по умолчанию все базы |
| Q-4 | Что показывать в окне 4 вместо DSN клиента (`odbc:SDuo`)? | `databasePath`/`server` API, «Тип» — `REST API v2` |
| Q-5 | Гарантируется ли единый часовой пояс API и БД? | Да; иначе требуется параметр `timeZone` |
| Q-6 | Достаточно ли точного `COUNT(*)` или нужна только оценка? | Точный + оценка; кэш 60 с |
| Q-7 | Нужен ли полный аналог сгруппированного отчёта `event/event_analyt` (с `st_data` и разбором конфигурации)? | Нет в MVP; `AnalyticsEvents` — сырые события |
| Q-8 | Нужен ли «строгий» режим проверки RFID (любой символ вне `0-9A-F`)? | Нет в MVP; отдельная задача |
| Q-9 | Входят ли в объём экран `device/stat` и экспорт `saveStateSkud`? | Нет; отдельная задача |
| Q-10 | Допустимо ли изменить числа панели согласно D-1…D-10? | Да, после согласования |

---

## Приложение А. Существующие методы Artonit REST API v2 (1.6.0)

| № | Метод | Маршрут | Назначение |
|---|---|---|---|
| 1 | GET | `/api/v2/Access/GetList` | Список категорий доступа |
| 2 | POST | `/api/v2/Access` | Добавить категорию |
| 3 | DELETE | `/api/v2/Access/{id_accessname}` | Удалить категорию |
| 4 | GET | `/api/v2/Access/GetPeople` | Сотрудники с категорией |
| 5 | GET | `/api/v2/Event/passIdDg` | События сотрудника по группе устройств |
| 6 | GET | `/api/v2/Event/passIdAp` | События сотрудника по точке прохода |
| 7 | GET | `/api/v2/Event/passAllDG` | Все события по группе устройств |
| 8 | GET | `/api/v2/Event/passAllAp` | Все события по точке прохода |
| 9 | GET | `/api/v2/IdentifierType/GetList` | Типы идентификаторов |
| 10 | GET | `/api/v2/Identifier/GetList` | Список идентификаторов |
| 11 | POST | `/api/v2/Identifier` | Добавить идентификатор |
| 12 | DELETE | `/api/v2/Identifier/DeleteIdentifier` | Удалить идентификатор |
| 13 | GET | `/api/v2/organizations` | Список организаций |
| 14 | POST | `/api/v2/organizations` | Добавить организацию |
| 15 | GET | `/api/v2/organizations/{divcode}` | Организация по коду |
| 16 | PUT | `/api/v2/organizations/{divcode}` | Полная замена полей |
| 17 | PATCH | `/api/v2/organizations/{divcode}` | Частичное изменение |
| 18 | DELETE | `/api/v2/organizations/{divcode}` | Удалить организацию |
| 19 | GET | `/api/v2/organizations/{divcode}/accessnames` | Категории организации |
| 20 | PUT | `/api/v2/organizations/{divcode}/accessnames` | Заменить категории организации |
| 21 | GET | `/api/v2/Person` | Сотрудник по `tabnum` |
| 22 | POST | `/api/v2/Person` | Добавить сотрудника |
| 23 | PATCH | `/api/v2/Person` | Изменить сотрудника |
| 24 | PUT | `/api/v2/Person` | Заменить ФИО |
| 25 | DELETE | `/api/v2/Person` | Удалить сотрудника |
| 26 | GET | `/api/v2/Person/GetList` | Список сотрудников |
| 27 | POST | `/api/v2/Person/AddAccess` | Добавить категорию сотруднику |
| 28 | GET | `/api/v2/Person/Access` | Категории сотрудника |
| 29 | PUT | `/api/v2/Person/Access` | Заменить категории сотрудника |
| 30 | POST | `/api/v2/Person/AddIdentifier` | Добавить идентификатор сотруднику |
| 31 | DELETE | `/api/v2/Person/DeleteIdentifier` | Удалить идентификатор сотрудника |

---

## Приложение Б. Используемые таблицы и колонки

| Таблица | Колонки | Где используется |
|---|---|---|
| `PEOPLE` | `ID_PEP`, `ID_DB`, `ID_ORG`, `TABNUM`, `SURNAME`, `NAME`, `PATRONYMIC`, `ACTIVE` | `People`, `Guest`, `AnalyticsEvents`, `RfidFormatErrors` |
| `CARD` | `ID_CARD`, `ID_DB`, `ID_PEP`, `ID_CARDTYPE`, `TIMEEND`, `ACTIVE` | `People`, `Guest`, `RfidFormatErrors` |
| `ORGANIZATION` | `ID_ORG`, `ID_DB`, `DIVCODE`, `NAME` | `Guest` |
| `EVENTS` | `ID_EVENT`, `ID_DB`, `ID_EVENTTYPE`, `ID_DEV`, `DATETIME`, `ID_CARD`, `NOTE`, `ID_PEP`, `ESS1`, `ANALIT` | `Analytics`, `AnalyticsEvents`, `System` |
| `EVENTTYPE` | `ID_EVENTTYPE`, `NAME` | `AnalyticsEvents` |
| `DEVICE` | `ID_DEV`, `ID_DB`, `ID_CTRL`, `ID_READER`, `ID_DEVTYPE`, `NAME`, `ACTIVE` | `Equipment`, `LoadQueue`, `AnalyticsEvents` |
| `SERVER` | `ID_SERVER`, `ID_DB`, `NAME`, `ACTIVE` | `Equipment` |
| `CARDINDEV` | `ID_CARD`, `ID_DEV`, `ID_CARDTYPE`, `ID_DB` | `LoadQueue` |
| `DEVTYPE` | `ID_DEVTYPE`, `STANDALONE` | `LoadQueue` |
| `DEVTYPE_CARDTYPE` | `ID_DEVTYPE`, `ID_CARDTYPE` | `LoadQueue` |
| `RDB$DATABASE` | — | Получение `asOf`, скалярные запросы |
| `RDB$INDICES`, `RDB$RELATION_CONSTRAINTS` | `RDB$STATISTICS`, `RDB$RELATION_NAME` | `System` (оценка) |

---

## Приложение В. Контрольные значения (24.11.2026, 22:46:51 MSK)

| Проверка | Значение |
|---|---|
| `PEOPLE` | 79 |
| `PEOPLE."ACTIVE" > 0` | 79 |
| `PEOPLE."ACTIVE" = 0` | 0 |
| `CARD` | 0 |
| `CARD` типа 1 | 0 |
| `EVENTS` | 846 |
| `MIN(EVENTS.DATETIME)` | 2026-11-22 12:06:31 |
| `EVENTS` за 24 ч | 57 |
| `EVENTS` с `ANALIT > 0` | 0 |
| `SERVER` | 2 |
| `SERVER."ACTIVE" > 0` | 1 |
| `DEVICE` | 0 |
| `CARDINDEV` | 0 |
| `ORGANIZATION` | 26 |
| Гости (`ID_ORG=2`, `ACTIVE=1`) | 3 |
| Архив (`ID_ORG=3`, `ACTIVE=1`) | 3 |
| Гостевые карты | 0 |
| Карты в архиве | 0 |
| RFID-ошибки (тип 1) | 0 |
| `dv_Guest` | `ID_ORG=2`, «гости» |
| `dv_Archive` | `ID_ORG=3`, «архив» |

---

## Приложение Г. Примеры запросов

### Bash + curl

```bash
TOKEN=$(curl -s "http://localhost:5056/login?username=ADMIN&pasword=333" \
  | python -c "import sys,json; print(json.load(sys.stdin)['data']['token'])")

curl -s -H "Authorization: Bearer $TOKEN" \
  "http://localhost:5056/api/v2/Stat/People?expiringDays=30&cardType=1"

curl -s -H "Authorization: Bearer $TOKEN" \
  "http://localhost:5056/api/v2/Stat/Dashboard?sections=people,guest,equipment,loadQueue,analytics"

curl -s -H "Authorization: Bearer $TOKEN" \
  "http://localhost:5056/api/v2/Stat/Analytics?hours=24"

curl -s -H "Authorization: Bearer $TOKEN" \
  "http://localhost:5056/api/v2/Stat/AnalyticsEvents?analit=0&fromDate=2026-11-22T00:00:00&toDate=2026-11-25T00:00:00&pageSize=2"
```

### PowerShell

```powershell
$login = Invoke-RestMethod "http://localhost:5056/login?username=ADMIN&pasword=333"
$h = @{ Authorization = "Bearer $($login.data.token)" }

Invoke-RestMethod "http://localhost:5056/api/v2/Stat/People?expiringDays=30" -Headers $h |
    ConvertTo-Json -Depth 6

Invoke-RestMethod "http://localhost:5056/api/v2/Stat/Dashboard" -Headers $h |
    ConvertTo-Json -Depth 6
```

### Ожидаемые коды

| Ситуация | Код |
|---|---|
| Успех | 200 |
| Нет токена | 401 |
| `System` без admin | 403 |
| Неверный параметр | 400 |
| Ошибка БД | 500 |

---

## Приложение Д. Чек-лист разработчика

- [ ] `StatOptions` добавлены в `appsettings.json` и зарегистрированы в `Program.cs`.
- [ ] `StatV2Controller` содержит 9 методов с XML-комментариями.
- [ ] `StatDBController` не использует `DatabaseService.Get<T>()` (из-за `ToUpper`).
- [ ] Все SQL-параметры именованные; `pageSize`/`offset` — проверенные целые.
- [ ] `asOf` один на запрос; `@now` передаётся во все секции.
- [ ] `Dashboard` устойчив к отказу секции.
- [ ] `System` — только admin; секреты не возвращаются.
- [ ] Swagger v2: тег `StatV2`, 9 операций, примеры.
- [ ] `stat.ps1` проходит; `access.ps1`/`organizations.ps1` не сломаны.
- [ ] Версия 1.7.0.
- [ ] `Model_StatApi` и конфиг клиента добавлены.
- [ ] `Service_Dashboard` маппит поля по таблице п. 5.4.
- [ ] Блок «Бюро пропусков» отображается.
- [ ] `iconv` для данных API не применяется.
- [ ] Fallback на SQL работает при остановленном API.
- [ ] В логах нет ПДн и секретов.
- [ ] Панель и API показывают одинаковые числа на эталонной базе.

---

## Лист согласования

| Роль | ФИО | Подпись | Дата |
|---|---|---|---|
| Заказчик | | | |
| Исполнитель | | | |
| Владелец Artonit REST API | | | |
| Владелец Artonit City | | | |

