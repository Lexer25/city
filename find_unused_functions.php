<?php
// find_unused_functions.php - Проверка конкретных папок

// Определяем окружение
$is_cli = (PHP_SAPI === 'cli');

if ($is_cli) {
    // Для консоли
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        system('chcp 65001 > nul');
    }
    setlocale(LC_ALL, 'ru_RU.UTF-8', 'Russian_Russia.1251');
} else {
    header('Content-Type: text/html; charset=utf-8');
    echo '<pre>';
}

// Функция для безопасного вывода
function print_message($text) {
    global $is_cli;
    if ($is_cli) {
        echo $text . "\n";
    } else {
        echo htmlspecialchars($text) . "\n";
    }
}

// === НАСТРОЙКИ ===
$project_root = 'C:\\xampp\\htdocs\\city'; // Корень проекта

// Папки для сканирования (только эти!)
$scan_folders = [
    $project_root . '\\application',
    $project_root . '\\modules\\dev',
    $project_root . '\\modules\\door',
    $project_root . '\\modules\\apb',
    $project_root . '\\modules\\events',
    $project_root . '\\modules\\eximdata',
    $project_root . '\\modules\\identifier',
    $project_root . '\\modules\\parsec',
    $project_root . '\\modules\\people',
];

$scan_folders = [
	$project_root . '\\application',
    $project_root . '\\modules\\door',
];



// Папки для исключения внутри каждой сканируемой
$exclude_dirs = ['.git', 'cache', 'logs', 'vendor', 'tests'];

// Файлы для исключения
$exclude_files = ['bootstrap.php', 'Kohana.php'];

print_message("🔍 Сканирование папок:");
foreach ($scan_folders as $folder) {
    print_message("   📁 " . str_replace($project_root, '', $folder));
}
print_message("");

$defined_functions = [];
$used_functions = [];

// 1. Собираем все PHP-файлы из указанных папок
$all_files = [];
foreach ($scan_folders as $folder) {
    if (is_dir($folder)) {
        $files = getPhpFiles($folder, $exclude_dirs, $exclude_files);
        $all_files = array_merge($all_files, $files);
        print_message("   Найдено " . count($files) . " файлов в " . basename($folder));
    } else {
        print_message("⚠️ Папка не найдена: " . $folder);
    }
}

$total_files = count($all_files);
print_message("\n📊 Всего PHP-файлов для анализа: $total_files\n");

// 2. Ищем объявления функций
print_message("🔍 Сканирование объявлений функций...");
$current = 0;

foreach ($all_files as $file) {
    $current++;
    if ($current % 20 == 0) {
        print_message("   Обработано $current из $total_files файлов");
    }
    
    $content = file_get_contents($file);
    
    // Ищем объявления функций
    preg_match_all('/function\s+([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s*\(/', $content, $matches);
    foreach ($matches[1] as $function) {
        // Исключаем магические методы и конструкторы классов
        if (!preg_match('/^__/', $function) && !preg_match('/^[A-Z]/', $function)) {
            $defined_functions[$function][] = $file;
        }
    }
}

print_message("✅ Найдено " . count($defined_functions) . " уникальных функций\n");

// 3. Ищем вызовы функций (во всех файлах проекта, не только в указанных папках)
// Это нужно, чтобы понять, вызываются ли функции из других мест
print_message("🔍 Сканирование вызовов функций...");

$all_project_files = getPhpFiles($project_root, $exclude_dirs, $exclude_files);
$current = 0;

foreach ($all_project_files as $file) {
    $current++;
    if ($current % 50 == 0) {
        print_message("   Проверено $current из " . count($all_project_files) . " файлов");
    }
    
    $content = file_get_contents($file);
    
    // Ищем вызовы функций
    preg_match_all('/(?<!function\s)(?<!->)(?<!::)([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s*\(/', $content, $matches);
    foreach ($matches[1] as $function) {
        $used_functions[$function] = true;
    }
}

// 4. Находим неиспользуемые функции
print_message("\n📊 РЕЗУЛЬТАТЫ:");
print_message("====================================");

$unused = [];
foreach ($defined_functions as $function => $files) {
    if (!isset($used_functions[$function]) && !in_array($function, ['_', '__'])) {
        $unused[$function] = $files;
    }
}

// Сортируем по имени
ksort($unused);

if (empty($unused)) {
    print_message("✅ Поздравляю! Все функции используются!");
} else {
    print_message("❌ Найдено " . count($unused) . " потенциально неиспользуемых функций:\n");
    
    $i = 1;
    foreach ($unused as $function => $files) {
        print_message("$i. Функция: $function");
        foreach (array_unique($files) as $file) {
            $relative_path = str_replace($project_root, '', $file);
            print_message("   📁 $relative_path");
        }
        print_message("");
        $i++;
    }
}

// 5. Сохраняем подробный отчет
$report_file = $project_root . '\\unused_functions_report.txt';
$report = "ОТЧЕТ О НЕИСПОЛЬЗУЕМЫХ ФУНКЦИЯХ\n";
$report .= "Сгенерировано: " . date('Y-m-d H:i:s') . "\n";
$report .= "====================================\n\n";
$report .= "Сканировались папки:\n";
foreach ($scan_folders as $folder) {
    $report .= "  " . str_replace($project_root, '', $folder) . "\n";
}
$report .= "\n";
$report .= "Всего найдено функций: " . count($defined_functions) . "\n";
$report .= "Неиспользуемых: " . count($unused) . "\n\n";

if (!empty($unused)) {
    $report .= "СПИСОК НЕИСПОЛЬЗУЕМЫХ ФУНКЦИЙ:\n";
    foreach ($unused as $function => $files) {
        $report .= "\nФункция: $function\n";
        $report .= "Файлы:\n";
        foreach (array_unique($files) as $file) {
            $report .= "  $file\n";
        }
    }
}

file_put_contents($report_file, $report);
print_message("\n📄 Подробный отчет сохранен в: $report_file");

// Вспомогательная функция для рекурсивного обхода папок
function getPhpFiles($dir, $exclude_dirs = [], $exclude_files = []) {
    $result = [];
    if (!is_dir($dir)) return $result;
    
    $items = scandir($dir);
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        if (in_array($item, $exclude_dirs)) continue;
        if (in_array($item, $exclude_files)) continue;
        
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        
        if (is_dir($path)) {
            $result = array_merge($result, getPhpFiles($path, $exclude_dirs, $exclude_files));
        } else {
            // Берем только PHP файлы
            if (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
                $result[] = $path;
            }
        }
    }
    
    return $result;
}

print_message("\n✨ Анализ завершен!");

if (!$is_cli) {
    echo '</pre>';
}