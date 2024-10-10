rem очистка таблицы со статическими данными.
c:\xampp\php\php.exe c:\xampp\htdocs\city\modules\minion\minion --task=DeleteStatData

rem сбор данных по контроллерам (версия, состояние связи, количество карт по каналам)/ Параметр - ID сервера

c:\xampp\php\php.exe c:\xampp\htdocs\city\modules\minion\minion --task=fixKeyOnDBCount

rem c:\xampp\php\php.exe c:\xampp\htdocs\city\modules\minion\minion --task=StatDevice --id_ts=2
rem C:\xampp\htdocs\city\application\classes\Task\test_1.bat


rem выборка id_dev из базы данных и опрос их состояния
c:\xampp\php\php.exe c:\xampp\htdocs\city\modules\minion\minion --task=collectStatFromDevice





