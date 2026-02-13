<? //http://itchief.ru/lessons/bootstrap-3/30-bootstrap-3-tables;
// страница отображения данных по парковочной системе
echo Form::open('parsec/parsec_control', array('method' => 'post', 'id' => 'parsec-form'));
?>
<fieldset>
    <legend><?php echo __('parsec_about'); ?></legend>
    <?php echo __('parsec_legend'); ?>
</fieldset>

<?php
$e_mess = Validation::Factory(Session::instance()->as_array())
    ->rule('e_mess', 'is_array')
    ->rule('e_mess', 'not_empty');

if ($e_mess->check()) {
    $param = 'Yes message<br>';
    foreach (Arr::get($e_mess, 'e_mess') as $key => $value) {
        $param .= $value . '<br>';
    }
    ?>
    <div id="my-alert" class="alert alert-danger alert-dismissible" role="alert">
        <?php echo $param; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php
}
Session::instance()->delete('e_mess');
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">
            <?php echo __('Список задач интегратора парсек :count', array(':count' => count($task_list))); ?>
        </h3>
    </div>
    <div class="panel-body">

        <?php
        // Массив операций
        $operatiion_name = array(
            '1' => 'add_card',
            '2' => 'del_card',
            '3' => 'add_people',
            '4' => 'del_card',
            '5' => 'add_org',
            '6' => 'del_org',
            '7' => 'add_access',
            '8' => 'del_access',
        );

        // Подготовка данных
        $raw_data = array();
        if (isset($task_list) && is_array($task_list)) {
            foreach ($task_list as $item) {
                $raw_data[] = array(
                    'id'          => Arr::get($item, 'ID', ''),
                    'id_card'     => Arr::get($item, 'ID_CARD', ''),
                    'id_pep'      => Arr::get($item, 'ID_PEP', ''),
                    'operation'   => Arr::get($item, 'OPERATION', ''),
                    'operation_name' => Arr::get($operatiion_name, Arr::get($item, 'OPERATION', ''), 'unknown'),
                    'org_name'    => iconv('windows-1251', 'UTF-8', Arr::get($item, 'ORG_NAME', '')),
                    'attempts'    => Arr::get($item, 'ATTEMPTS', ''),
                    'dest'        => iconv('windows-1251', 'UTF-8', Arr::get($item, 'DEST', '')),
                    'timestamp'   => Arr::get($item, 'TIME_STAMP', ''),
                    'hex'         => (Arr::get($item, 'OPERATION', '') == 2) ? ' (0x' . dechex(Arr::get($item, 'ID_CARD', 0)) . ')' : ''
                );
            }
        }
        ?>

        <?php if (!empty($raw_data)): ?>

        <!-- Панель инструментов -->
        <div class="row" style="margin-bottom: 15px;">
            <div class="col-md-12">
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-warning" id="reset-filters">
                        <span class="glyphicon glyphicon-remove-circle"></span> Сбросить фильтры
                    </button>
                    <button type="button" class="btn btn-sm btn-success" id="export-csv">
                        <span class="glyphicon glyphicon-export"></span> CSV
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" id="export-pdf">
                        <span class="glyphicon glyphicon-file"></span> PDF
                    </button>
                </div>
                <span class="text-muted" style="margin-left: 15px;">
                    <span id="filter-counter">
                        <?php echo __('Показано записей: :count из :total', array(':count' => count($raw_data), ':total' => count($raw_data))); ?>
                    </span>
                </span>
            </div>
        </div>

        <!-- Поле поиска организации с автодополнением -->
        <div class="row" style="margin-bottom: 15px;">
            <div class="col-md-6">
                <div class="input-group" style="position: relative;">
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-building"></span>
                    </span>
                    <input type="text" 
                           id="org-search" 
                           class="form-control" 
                           placeholder="Введите название организации для поиска..."
                           autocomplete="off">
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="button" id="clear-org-search">
                            <span class="glyphicon glyphicon-remove"></span>
                        </button>
                    </span>
                </div>
                <div id="org-autocomplete-list" class="list-group" style="position: absolute; z-index: 1000; width: 50%; max-height: 200px; overflow-y: auto; display: none;"></div>
            </div>
            <div class="col-md-6">
                <small class="text-muted" id="org-search-info">
                    Введите 2+ символа для поиска организации
                </small>
            </div>
        </div>

       <!-- Скрытое поле для имени пользователя -->
<span data-username="<?php 
    // Получаем имя пользователя из сессии или ставим 'Администратор'
    $username = Session::instance()->get('username', 'Администратор');
    
    // Если в сессии пусто, пробуем Auth
    if (empty($username) || $username === 'Администратор') {
        if (class_exists('Auth') && method_exists(Auth::instance(), 'get_user')) {
            $user = Auth::instance()->get_user();
            if (is_object($user) && isset($user->username)) {
                $username = $user->username;
            } elseif (is_array($user) && isset($user['username'])) {
                $username = $user['username'];
            }
        }
    }
    
    echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); 
?>" style="display: none;"></span>

        <!-- Таблица -->
        <div class="table-responsive">
            <table class="table table-striped table-hover table-condensed" id="parsec-table">
                <thead>
                    <!-- Заголовки с сортировкой -->
                    <tr class="active">
                        <th data-sort="id" class="sortable">ID <span class="sort-icon"></span></th>
                        <th data-sort="id_card" class="sortable">GUID <span class="sort-icon"></span></th>
                        <th data-sort="id_pep" class="sortable">ID_PEP <span class="sort-icon"></span></th>
                        <th data-sort="operation" class="sortable">Операция <span class="sort-icon"></span></th>
                        <th data-sort="org_name" class="sortable">Организация <span class="sort-icon"></span></th>
                        <th data-sort="attempts" class="sortable">Попытки <span class="sort-icon"></span></th>
                        <th data-sort="dest" class="sortable">Для кого <span class="sort-icon"></span></th>
                        <th data-sort="timestamp" class="sortable">Дата <span class="sort-icon"></span></th>
                        <th>Действия</th>
                    </tr>
                    <!-- Строка фильтров -->
                    <tr class="filters-row">
                        <th><div class="filter-wrapper"><input type="text" class="form-control input-sm column-filter" data-column="0" placeholder="ID"><span class="clear-filter">×</span></div></th>
                        <th><div class="filter-wrapper"><input type="text" class="form-control input-sm column-filter" data-column="1" placeholder="GUID"><span class="clear-filter">×</span></div></th>
                        <th><div class="filter-wrapper"><input type="text" class="form-control input-sm column-filter" data-column="2" placeholder="ID_PEP"><span class="clear-filter">×</span></div></th>
                        <th><div class="filter-wrapper">
                            <select class="form-control input-sm column-filter" data-column="3" data-type="select">
                                <option value="">Все операции</option>
                                <?php foreach ($operatiion_name as $op_id => $op_name): ?>
                                <option value="<?php echo $op_id; ?>"><?php echo $op_name; ?> (<?php echo $op_id; ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <span class="clear-filter">×</span>
                        </div></th>
                        <th><div class="filter-wrapper"><input type="text" class="form-control input-sm column-filter" data-column="4" placeholder="Организация"><span class="clear-filter">×</span></div></th>
                        <th><div class="filter-wrapper"><input type="text" class="form-control input-sm column-filter" data-column="5" placeholder="Попытки"><span class="clear-filter">×</span></div></th>
                        <th><div class="filter-wrapper"><input type="text" class="form-control input-sm column-filter" data-column="6" placeholder="Получатель"><span class="clear-filter">×</span></div></th>
                        <th><div class="filter-wrapper"><input type="text" class="form-control input-sm column-filter" data-column="7" placeholder="ГГГГ-ММ-ДД"><span class="clear-filter">×</span></div></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="table-body">
                    <?php foreach ($raw_data as $row): ?>
                    <tr>
                        <td><?php echo Form::hidden('id_cardindev[' . $row['id'] . ']', $row['id']); ?><?php echo $row['id']; ?></td>
                        <td><?php echo $row['id_card'] . $row['hex']; ?></td>
                        <td><?php echo $row['id_pep']; ?></td>
                        <td><?php echo $row['operation_name'] . ' (' . $row['operation'] . ')'; ?></td>
                        <td><?php echo htmlspecialchars($row['org_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo $row['attempts']; ?></td>
                        <td><?php echo htmlspecialchars($row['dest'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo $row['timestamp']; ?></td>
                        <td>
                            <a href="parsec/repeat/<?php echo $row['id']; ?>" class="btn btn-xs btn-success">Repeat</a>
                            <a href="parsec/delete/<?php echo $row['id']; ?>" class="btn btn-xs btn-danger" onclick="return confirm('<?php echo __('Вы уверены?'); ?>') ? true : false;">delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php else: ?>
            <div class="alert alert-info"><?php echo __('Список задач пуст.'); ?></div>
        <?php endif; ?>

        <div class="form-group" style="margin-top: 15px;">
            <?php
            echo Form::button('todo', 'RESTART ALL TASK', array(
                'value' => 'set_attempt',
                'class' => 'btn btn-warning',
                'type' => 'submit',
                'onclick' => 'return confirm(\'' . __('restart_all_task_parsec') . '\') ? true : false;'
            ));
            echo Form::button('todo', 'DELETE ALL TASKS', array(
                'value' => 'delAllTasks',
                'class' => 'btn btn-danger',
                'type' => 'submit',
                'style' => 'margin-left: 10px;',
                'onclick' => 'return confirm(\'' . __('delete_all_task_parsec') . '\') ? true : false;'
            ));
            ?>
        </div>
    </div>
</div>

<?php echo Form::close(); ?>

<!-- Подключение библиотек для PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.min.js"></script>

<style>
/* ===== СТИЛИ ===== */
.filters-row th {
    vertical-align: top !important;
    background-color: #fafafa !important;
    padding: 8px 5px !important;
}

.filter-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.column-filter {
    width: 100%;
    font-size: 12px;
    padding: 5px 25px 5px 8px !important;
    height: 32px;
    border-radius: 4px;
    border: 1px solid #ccc;
    transition: all 0.2s ease;
}

.column-filter:focus {
    border-color: #337ab7;
    box-shadow: 0 0 5px rgba(51, 122, 183, 0.3);
    background-color: #fff;
}

.column-filter:not(:placeholder-shown) {
    background-color: #fff3cd;
    border-color: #ffc107;
}

.clear-filter {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
    font-size: 18px;
    font-weight: bold;
    cursor: pointer;
    display: none;
    line-height: 1;
    padding: 0 4px;
    z-index: 5;
}

.clear-filter:hover {
    color: #d9534f;
}

.filter-wrapper:hover .clear-filter,
.column-filter:not(:placeholder-shown) + .clear-filter,
.column-filter:focus + .clear-filter {
    display: block;
}

/* Сортировка */
.sortable {
    cursor: pointer;
    position: relative;
    padding-right: 20px !important;
    white-space: nowrap;
}

.sortable:hover {
    background-color: #e6f7ff !important;
}

.sort-icon {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
    font-size: 12px;
}

.sort-asc .sort-icon::after {
    content: "▲";
    color: #337ab7;
}

.sort-desc .sort-icon::after {
    content: "▼";
    color: #337ab7;
}

.sortable:not(.sort-asc):not(.sort-desc) .sort-icon::after {
    content: "⇕";
    color: #aaa;
}

/* Подсветка строк */
.highlight-row {
    background-color: #d9edf7 !important;
    border-left: 3px solid #31708f;
}

/* Автодополнение для организаций */
#org-autocomplete-list {
    background-color: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 6px 12px rgba(0,0,0,.175);
    margin-top: 2px;
    padding: 5px 0;
}

#org-autocomplete-list .list-group-item {
    border: none;
    border-bottom: 1px solid #f5f5f5;
    margin-bottom: 0;
    padding: 8px 15px;
    cursor: pointer;
    font-size: 13px;
}

#org-autocomplete-list .list-group-item:hover {
    background-color: #337ab7;
    color: white;
}

#org-autocomplete-list .list-group-item:last-child {
    border-bottom: none;
}

#org-autocomplete-list .highlight {
    background-color: #337ab7;
    color: white;
}

#org-search {
    border-radius: 4px 0 0 4px;
}

#org-search:focus {
    border-color: #337ab7;
    box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(51, 122, 183, 0.6);
}

/* Адаптивность */
.table-responsive {
    border: none;
    overflow-x: auto;
}

.btn-xs {
    margin-right: 3px;
}

/* Авто-закрытие алерта */
#my-alert {
    transition: opacity 0.5s ease;
}
</style>

<script>
(function() {
    'use strict';

    // ========== ГЛОБАЛЬНЫЕ ПЕРЕМЕННЫЕ ==========
    const STORAGE_KEY = 'parsec_table_filters';
    let currentSort = {
        column: null,
        direction: 'asc'
    };

    // ========== ЗАГРУЗКА СОХРАНЁННЫХ ФИЛЬТРОВ ==========
    function loadSavedFilters() {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (saved) {
                const filters = JSON.parse(saved);
                const filterInputs = document.querySelectorAll('.column-filter');
                
                filterInputs.forEach((input, index) => {
                    const colIndex = input.dataset.column;
                    if (colIndex !== undefined && filters[colIndex]) {
                        if (input.tagName === 'SELECT') {
                            input.value = filters[colIndex];
                        } else {
                            input.value = filters[colIndex];
                        }
                    }
                });
            }
        } catch (e) {
            console.warn('Не удалось загрузить фильтры');
        }
    }

    // ========== СОХРАНЕНИЕ ФИЛЬТРОВ ==========
    function saveFilters() {
        const filters = {};
        const filterInputs = document.querySelectorAll('.column-filter');
        
        filterInputs.forEach((input) => {
            const colIndex = input.dataset.column;
            if (colIndex !== undefined) {
                filters[colIndex] = input.value;
            }
        });
        
        localStorage.setItem(STORAGE_KEY, JSON.stringify(filters));
    }

    // ========== ФИЛЬТРАЦИЯ ТАБЛИЦЫ ==========
    function filterTable() {
        const filters = [];
        const filterInputs = document.querySelectorAll('.column-filter');
        
        filterInputs.forEach(input => {
            const value = input.tagName === 'SELECT' 
                ? input.value 
                : input.value.toLowerCase().trim();
            filters[input.dataset.column] = value;
        });

        const rows = document.querySelectorAll('#table-body tr');
        let visibleCount = 0;

        rows.forEach(row => {
            let show = true;
            const cells = row.cells;

            for (let i = 0; i < filters.length; i++) {
                const filterValue = filters[i];
                if (!filterValue || filterValue === '') continue;

                let cellValue = cells[i] ? (cells[i].textContent || cells[i].innerText || '').toLowerCase() : '';

                if (i == 3) {
                    const opMatch = cellValue.match(/\((\d+)\)/);
                    const opCode = opMatch ? opMatch[1] : '';
                    if (filterValue !== opCode) {
                        show = false;
                        break;
                    }
                } else {
                    if (cellValue.indexOf(filterValue) === -1) {
                        show = false;
                        break;
                    }
                }
            }

            row.style.display = show ? '' : 'none';
            if (show) visibleCount++;
        });

        const counter = document.getElementById('filter-counter');
        const total = rows.length;
        if (counter) {
            counter.innerHTML = `Показано записей: ${visibleCount} из ${total}`;
        }

        saveFilters();
        applySorting();
        updateOrgSearchInfo(); // Обновляем информацию о поиске
    }

    // ========== СОРТИРОВКА ==========
    function sortTable(column, direction) {
        const tbody = document.getElementById('table-body');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        const visibleRows = rows.filter(row => row.style.display !== 'none');
        const hiddenRows = rows.filter(row => row.style.display === 'none');

        visibleRows.sort((a, b) => {
            let aVal = a.cells[column].textContent.trim();
            let bVal = b.cells[column].textContent.trim();

            if (column === 1) {
                aVal = aVal.replace(/\s\(0x[0-9a-f]+\)/i, '');
                bVal = bVal.replace(/\s\(0x[0-9a-f]+\)/i, '');
            }

            if (column === 3) {
                const aMatch = aVal.match(/\((\d+)\)/);
                const bMatch = bVal.match(/\((\d+)\)/);
                aVal = aMatch ? aMatch[1] : aVal;
                bVal = bMatch ? bMatch[1] : bVal;
            }

            if ([0, 2, 5].includes(column)) {
                aVal = parseInt(aVal) || 0;
                bVal = parseInt(bVal) || 0;
                return direction === 'asc' ? aVal - bVal : bVal - aVal;
            }

            if (column === 7) {
                return direction === 'asc' 
                    ? aVal.localeCompare(bVal) 
                    : bVal.localeCompare(aVal);
            }

            aVal = aVal.toLowerCase();
            bVal = bVal.toLowerCase();
            return direction === 'asc' 
                ? aVal.localeCompare(bVal, 'ru') 
                : bVal.localeCompare(aVal, 'ru');
        });

        tbody.innerHTML = '';
        visibleRows.forEach(row => tbody.appendChild(row));
        hiddenRows.forEach(row => tbody.appendChild(row));

        document.querySelectorAll('.sortable').forEach(el => {
            el.classList.remove('sort-asc', 'sort-desc');
        });

        const header = document.querySelector(`.sortable:nth-child(${column + 1})`);
        if (header) {
            header.classList.add(direction === 'asc' ? 'sort-asc' : 'sort-desc');
        }
    }

    function applySorting() {
        if (currentSort.column !== null) {
            sortTable(currentSort.column, currentSort.direction);
        }
    }

    // ========== ЭКСПОРТ В CSV (ТАБУЛЯЦИЯ, БЕЗ КАВЫЧЕК) ==========
    function exportToCSV() {
        const rows = Array.from(document.querySelectorAll('#table-body tr'));
        const visibleRows = rows.filter(row => row.style.display !== 'none');
        
        if (visibleRows.length === 0) {
            alert('Нет данных для экспорта');
            return;
        }

        const headers = ['ID', 'GUID', 'ID_PEP', 'Операция', 'Организация', 'Попытки', 'Для кого', 'Дата'];
        
        const data = visibleRows.map(row => {
            const cells = row.cells;
            return [
                cells[0].textContent.trim(),
                cells[1].textContent.trim(),
                cells[2].textContent.trim(),
                cells[3].textContent.trim(),
                cells[4].textContent.trim(),
                cells[5].textContent.trim(),
                cells[6].textContent.trim(),
                cells[7].textContent.trim()
            ];
        });

        const tsvContent = [
            headers.join('\t'),
            ...data.map(row => 
                row.map(cell => 
                    cell
                        .replace(/\t/g, ' ')
                        .replace(/\n/g, ' ')
                        .replace(/\r/g, ' ')
                ).join('\t')
            )
        ].join('\n');

        const blob = new Blob(["\ufeff" + tsvContent], { type: 'text/tab-separated-values;charset=utf-8' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `parsec_export_${new Date().toISOString().slice(0,10)}.txt`;
        link.click();
    }

    // ========== ЭКСПОРТ В PDF (pdfmake, кириллица, ПОРТРЕТ, ПОЛНЫЙ ЗАГОЛОВОК) ==========
    function exportToPDF() {
        const rows = Array.from(document.querySelectorAll('#table-body tr'));
        const visibleRows = rows.filter(row => row.style.display !== 'none');

        if (visibleRows.length === 0) {
            alert('Нет данных для экспорта');
            return;
        }

        const headers = [
            'ID',
            'GUID',
            'ID PEP',
            'Операция',
            'Организация',
            'Попытки',
            'Для кого',
            'Дата'
        ];

        const bodyData = visibleRows.map(row => {
            const cells = row.cells;
            return [
                cells[0].textContent.trim(),
                cells[1].textContent.trim(),
                cells[2].textContent.trim(),
                cells[3].textContent.trim(),
                cells[4].textContent.trim(),
                cells[5].textContent.trim(),
                cells[6].textContent.trim(),
                cells[7].textContent.trim()
            ];
        });

        const now = new Date();
        const dateStr = now.toLocaleDateString('ru-RU');
        const timeStr = now.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        
        let userName = 'Администратор';
        const userElement = document.querySelector('[data-username]');
        if (userElement) {
            userName = userElement.dataset.username;
        }

        function getActiveFilters() {
            const filters = [];
            const filterInputs = document.querySelectorAll('.column-filter');
            
            filterInputs.forEach(input => {
                if (input.value && input.value !== '') {
                    let label = '';
                    const colIndex = input.dataset.column;
                    
                    switch(colIndex) {
                        case '0': label = 'ID'; break;
                        case '1': label = 'GUID'; break;
                        case '2': label = 'ID PEP'; break;
                        case '3': label = 'Операция'; break;
                        case '4': label = 'Организация'; break;
                        case '5': label = 'Попытки'; break;
                        case '6': label = 'Для кого'; break;
                        case '7': label = 'Дата'; break;
                    }
                    
                    let value = input.value;
                    if (input.tagName === 'SELECT') {
                        const selected = input.options[input.selectedIndex];
                        value = selected ? selected.text : value;
                    }
                    
                    filters.push(label + ': ' + value);
                }
            });
            
            return filters.length > 0 ? filters.join('; ') : 'нет';
        }

        const docDefinition = {
            pageOrientation: 'portrait',
            pageSize: 'A4',
            pageMargins: [15, 50, 15, 30],
            
            header: function(currentPage, pageCount) {
                return {
                    text: 'Страница ' + currentPage + ' из ' + pageCount,
                    alignment: 'right',
                    margin: [0, 10, 15, 0],
                    fontSize: 8,
                    color: '#666666'
                };
            },
            
            footer: function(currentPage, pageCount) {
                return {
                    columns: [
                        {
                            text: 'Отчёт сгенерирован: ' + dateStr + ' ' + timeStr,
                            alignment: 'left',
                            margin: [15, 0, 0, 0],
                            fontSize: 8,
                            color: '#666666'
                        },
                        {
                            text: 'Парсек - система управления доступом',
                            alignment: 'right',
                            margin: [0, 0, 15, 0],
                            fontSize: 8,
                            color: '#666666'
                        }
                    ]
                };
            },

            content: [
                {
                    text: 'ОТЧЁТ ПО ЗАДАЧАМ ИНТЕГРАТОРА ПАРСЕК',
                    style: 'reportTitle',
                    alignment: 'center',
                    margin: [0, 0, 0, 15]
                },
                
                {
                    stack: [
                        {
                            text: 'ИНФОРМАЦИЯ ОБ ОТЧЁТЕ',
                            style: 'infoBoxTitle',
                            alignment: 'center',
                            margin: [0, 0, 0, 8]
                        },
                        {
                            table: {
                                widths: ['*', '*'],
                                body: [
                                    [
                                        { text: '📌 Название отчёта:', style: 'infoLabel' },
                                        { text: 'Задачи интегратора Парсек', style: 'infoValue' }
                                    ],
                                    [
                                        { text: '👤 Подготовил:', style: 'infoLabel' },
                                        { text: userName, style: 'infoValue' }
                                    ],
                                    [
                                        { text: '🕒 Дата подготовки:', style: 'infoLabel' },
                                        { text: dateStr + ' в ' + timeStr, style: 'infoValue' }
                                    ],
                                    [
                                        { text: '📊 Всего записей:', style: 'infoLabel' },
                                        { text: visibleRows.length + ' шт.', style: 'infoValue', bold: true }
                                    ],
                                    [
                                        { text: '🔍 Фильтры:', style: 'infoLabel' },
                                        { text: getActiveFilters(), style: 'infoValue', italics: true }
                                    ]
                                ]
                            },
                            layout: 'noBorders',
                            margin: [5, 0, 5, 15]
                        }
                    ],
                    style: 'infoBox',
                    margin: [0, 0, 0, 20]
                },
                
                {
                    canvas: [
                        {
                            type: 'line',
                            x1: 0,
                            y1: 0,
                            x2: 515,
                            y2: 0,
                            lineWidth: 0.5,
                            lineColor: '#2c3e50'
                        }
                    ],
                    margin: [0, 0, 0, 15]
                },
                
                {
                    text: 'ДЕТАЛИЗАЦИЯ ЗАДАЧ',
                    style: 'tableTitle',
                    alignment: 'left',
                    margin: [0, 0, 0, 10]
                },
                
                {
                    table: {
                        headerRows: 1,
                        widths: [18, 40, 18, 35, 45, 18, 45, 35],
                        body: [
                            headers.map(h => ({
                                text: h,
                                style: 'tableHeader',
                                alignment: 'center'
                            })),
                            ...bodyData.map(row => 
                                row.map((cell, index) => {
                                    let align = 'left';
                                    if ([0, 2, 5].includes(index)) align = 'center';
                                    if (index === 7) align = 'center';
                                    
                                    return {
                                        text: cell,
                                        alignment: align,
                                        fontSize: 7,
                                        margin: [1, 2, 1, 2]
                                    };
                                })
                            )
                        ]
                    },
                    layout: {
                        fillColor: function(rowIndex, node, columnIndex) {
                            if (rowIndex === 0) return '#2c3e50';
                            return (rowIndex % 2 === 0) ? '#f8f9fa' : null;
                        },
                        hLineWidth: function(i, node) {
                            return (i === 0 || i === node.table.body.length) ? 0.5 : 0.3;
                        },
                        vLineWidth: function(i, node) {
                            return 0.2;
                        },
                        hLineColor: function(i, node) {
                            return '#aaaaaa';
                        },
                        vLineColor: function(i, node) {
                            return '#aaaaaa';
                        },
                        paddingLeft: function(i, node) { return 3; },
                        paddingRight: function(i, node) { return 3; },
                        paddingTop: function(i, node) { return 2; },
                        paddingBottom: function(i, node) { return 2; }
                    }
                },
                
                {
                    text: 'Всего записей в отчёте: ' + visibleRows.length,
                    style: 'summary',
                    alignment: 'right',
                    margin: [0, 15, 0, 0]
                },
                
                {
                    columns: [
                        {
                            width: '*',
                            text: ''
                        },
                        {
                            width: 'auto',
                            stack: [
                                {
                                    text: '____________________',
                                    alignment: 'center',
                                    margin: [0, 20, 0, 5]
                                },
                                {
                                    text: 'Подпись ответственного',
                                    alignment: 'center',
                                    fontSize: 8,
                                    color: '#666666'
                                }
                            ]
                        },
                        {
                            width: '*',
                            text: ''
                        }
                    ],
                    margin: [0, 30, 0, 0]
                }
            ],
            
            styles: {
                reportTitle: {
                    fontSize: 16,
                    bold: true,
                    color: '#2c3e50',
                    decoration: 'underline',
                    margin: [0, 0, 0, 10]
                },
                infoBox: {
                    fillColor: '#f5f7fa',
                    color: '#333333',
                    fontSize: 10,
                    padding: [10, 10, 10, 10],
                    border: true,
                    borderColor: '#d0d7de',
                    borderWidth: 1
                },
                infoBoxTitle: {
                    fontSize: 11,
                    bold: true,
                    color: '#2c3e50',
                    background: '#e9ecef',
                    padding: [5, 5, 5, 5]
                },
                infoLabel: {
                    fontSize: 9,
                    bold: true,
                    color: '#495057',
                    alignment: 'right',
                    margin: [0, 2, 5, 2]
                },
                infoValue: {
                    fontSize: 9,
                    color: '#212529',
                    alignment: 'left',
                    margin: [5, 2, 0, 2]
                },
                tableTitle: {
                    fontSize: 12,
                    bold: true,
                    color: '#2c3e50'
                },
                tableHeader: {
                    bold: true,
                    fontSize: 8,
                    color: 'white',
                    fillColor: '#2c3e50',
                    alignment: 'center',
                    margin: [2, 2, 2, 2]
                },
                summary: {
                    fontSize: 10,
                    bold: true,
                    color: '#2c3e50'
                }
            },
            
            defaultStyle: {
                font: 'Roboto'
            }
        };

        const filename = `parsec_report_${dateStr.replace(/\./g, '_')}.pdf`;
        pdfMake.createPdf(docDefinition).download(filename);
    }

    // ========== ПОДСВЕТКА СТРОК ==========
    function enableRowHighlight() {
        document.querySelectorAll('#table-body tr').forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.classList.add('highlight-row');
            });
            row.addEventListener('mouseleave', function() {
                this.classList.remove('highlight-row');
            });
        });
    }

    // ========== ПОИСК ОРГАНИЗАЦИИ С АВТОДОПОЛНЕНИЕМ ==========
    function getUniqueOrganizations() {
        const orgs = new Set();
        const rows = document.querySelectorAll('#table-body tr');
        
        rows.forEach(row => {
            const cells = row.cells;
            if (cells[4]) {
                const orgName = cells[4].textContent.trim();
                if (orgName && orgName !== '') {
                    orgs.add(orgName);
                }
            }
        });
        
        return Array.from(orgs).sort((a, b) => a.localeCompare(b, 'ru'));
    }

    function searchOrganizations(query) {
        const orgs = getUniqueOrganizations();
        query = query.toLowerCase().trim();
        
        if (query.length < 2) {
            return [];
        }
        
        return orgs.filter(org => 
            org.toLowerCase().includes(query)
        ).slice(0, 10);
    }

    function showAutocompleteList(matches, query) {
        const list = document.getElementById('org-autocomplete-list');
        const searchInput = document.getElementById('org-search');
        
        if (matches.length === 0 || query.length < 2) {
            list.style.display = 'none';
            return;
        }
        
        list.innerHTML = '';
        
        matches.forEach(match => {
            const item = document.createElement('a');
            item.className = 'list-group-item';
            item.href = '#';
            
            const regex = new RegExp(`(${query})`, 'gi');
            const highlightedText = match.replace(regex, '<strong>$1</strong>');
            item.innerHTML = highlightedText;
            
            item.addEventListener('click', function(e) {
                e.preventDefault();
                searchInput.value = match;
                list.style.display = 'none';
                
                const orgFilter = document.querySelector('.column-filter[data-column="4"]');
                if (orgFilter) {
                    orgFilter.value = match;
                    filterTable();
                }
                
                updateOrgSearchInfo(match);
            });
            
            list.appendChild(item);
        });
        
        list.style.display = 'block';
    }

    function updateOrgSearchInfo(orgName) {
        const info = document.getElementById('org-search-info');
        const rows = Array.from(document.querySelectorAll('#table-body tr'));
        const visibleRows = rows.filter(row => row.style.display !== 'none');
        
        if (orgName && orgName !== '') {
            const count = visibleRows.length;
            info.innerHTML = `🔍 Найдено организаций: <strong>${orgName}</strong> (${count} записей)`;
            info.className = 'text-success';
        } else {
            info.innerHTML = 'Введите 2+ символа для поиска организации';
            info.className = 'text-muted';
        }
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    function initOrgSearch() {
        const searchInput = document.getElementById('org-search');
        const clearBtn = document.getElementById('clear-org-search');
        const list = document.getElementById('org-autocomplete-list');
        
        if (!searchInput) return;
        
        const debouncedSearch = debounce(function() {
            const query = searchInput.value;
            const matches = searchOrganizations(query);
            showAutocompleteList(matches, query);
        }, 300);
        
        searchInput.addEventListener('input', debouncedSearch);
        
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                searchInput.value = '';
                list.style.display = 'none';
                
                const orgFilter = document.querySelector('.column-filter[data-column="4"]');
                if (orgFilter) {
                    orgFilter.value = '';
                    filterTable();
                }
                
                updateOrgSearchInfo('');
            });
        }
        
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !list.contains(e.target)) {
                list.style.display = 'none';
            }
        });
        
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                list.style.display = 'none';
            }
        });
    }

    // ========== ИНИЦИАЛИЗАЦИЯ ==========
    document.addEventListener('DOMContentLoaded', function() {
        loadSavedFilters();
        
        const filterInputs = document.querySelectorAll('.column-filter');
        filterInputs.forEach(input => {
            input.addEventListener('keyup', filterTable);
            input.addEventListener('change', filterTable);
        });

        document.querySelectorAll('.sortable').forEach((th, index) => {
            th.addEventListener('click', function() {
                const column = index;
                
                if (currentSort.column === column) {
                    currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
                } else {
                    currentSort.column = column;
                    currentSort.direction = 'asc';
                }
                
                sortTable(column, currentSort.direction);
            });
        });

        document.querySelectorAll('.clear-filter').forEach(clear => {
            clear.addEventListener('click', function(e) {
                e.stopPropagation();
                const wrapper = this.closest('.filter-wrapper');
                const input = wrapper.querySelector('.column-filter');
                if (input) {
                    if (input.tagName === 'SELECT') {
                        input.selectedIndex = 0;
                    } else {
                        input.value = '';
                    }
                    filterTable();
                }
            });
        });

        const resetBtn = document.getElementById('reset-filters');
        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                filterInputs.forEach(input => {
                    if (input.tagName === 'SELECT') {
                        input.selectedIndex = 0;
                    } else {
                        input.value = '';
                    }
                });
                filterTable();
                localStorage.removeItem(STORAGE_KEY);
                
                const orgSearch = document.getElementById('org-search');
                if (orgSearch) orgSearch.value = '';
                updateOrgSearchInfo('');
            });
        }

        const exportBtn = document.getElementById('export-csv');
        if (exportBtn) {
            exportBtn.addEventListener('click', exportToCSV);
        }

        const pdfBtn = document.getElementById('export-pdf');
        if (pdfBtn) {
            pdfBtn.addEventListener('click', exportToPDF);
        }

        enableRowHighlight();
        initOrgSearch();
        filterTable();
    });

})();
</script>