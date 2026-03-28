<?php
/**
 * Карты, срок действия которых скоро истекает (people / find_card_late_next_week).
 * Общая разметка — modules/people/views/People/_table.php
 */
$people_table_variant = 'card_late_next_week';
$title = __('card_late_next_week_info');
$custom_info = '<p><strong>Примечание:</strong> Перечень карт, у которых окончание срока действия попадает в окно от «сейчас» до горизонта, заданного параметром <code>count_day_befor_end_time</code>.</p>';

include '_table.php';
