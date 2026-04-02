<?php defined('SYSPATH') or die('No direct script access.');

return array(
    // Available ODBC DSNs for selection
    'odbc_dsns' => array(
        'SDUO' => 'odbc:SDUO',
        'Kalibr' => 'odbc:Kalibr',
        'Kalibr_25' => 'odbc:Kalibr_25',
        'HL' => 'odbc:HL',
    ),
    // Path to Firebird bin directory (gbak, isql, etc.)
    'firebird_bin' => 'C:\\Program Files\\Firebird\\Firebird_1_5_6\\bin\\',
    // Default database file path (used for backup/restore)
    'database_path' => 'C:\\Program Files\\Firebird\\Firebird_1_5_6\\data\\CITY.FDB',
    // Backup directory
    'backup_dir' => 'C:\\backup\\',
    // Firebird service name
    'service_name' => 'FirebirdServerDefault',
);