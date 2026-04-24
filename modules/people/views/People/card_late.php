<?php
// people/views/People/card_late.php
/**
 * Отображение просроченных карт (people / find_card_late и др.).
 */
$people_table_variant = 'card_late';

// Определение заголовков таблицы (резерв под динамические колонки)
$headers = array(
    'ID_CARD' => 'ID карты',
    'TIMESTART' => 'Начало действия',
    'TIMEEND' => 'Окончание действия',
    'ACTIVE' => 'Активна',
    'IDTYPE' => 'Тип карты',
    'CREATEDAT' => 'Дата создания',
    'ID_PEP' => 'ID сотрудника',
    'FIO' => 'ФИО',
    'ID_ORG' => 'ID организации',
    'ORGNAME' => 'Название организации',
    'ID_PARENT' => 'ID родительской организации',
    'ORGPARENTNAME' => 'Родительская организация',
    'lastevent' => 'Последнее событие'
);

$title = __('Список неактивных карт.');
$custom_info = '<p><strong>Примечание:</strong> В данный список включены карты, по которым в базе данных отсутствуют любые отметки о проходе.</p>';

include '_table.php';
?>