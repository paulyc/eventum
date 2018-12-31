<?php

/*
 * This file is part of the Eventum (Issue Tracking System) package.
 *
 * @copyright (c) Eventum Team
 * @license GNU General Public License, version 2 or later (GPL-2+)
 *
 * For the full copyright and license information,
 * please see the COPYING and AUTHORS files
 * that were distributed with this source code.
 */

use Eventum\Db\AbstractMigration;
use Eventum\Event\SystemEvents;
use Eventum\EventDispatcher\EventManager;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Configuration proxy for sphix
 *
 * @see http://docs.phinx.org/en/latest/commands.html#configuration-file-parameter
 */

// init minimal constants needed for some classes to work
require_once __DIR__ . '/globals.php';
$define = function ($name, $value): void {
    if (defined($name)) {
        return;
    }
    define($name, $value);
};
$define('APP_LOCAL_PATH', APP_CONFIG_PATH);
if (file_exists(APP_CONFIG_PATH . '/config.php')) {
    require_once APP_CONFIG_PATH . '/config.php';
}

// workflow may use this in constructor
Eventum\Monolog\Logger::initialize();

// TODO: use "connection" => $pdo_instance once PEAR DB support is dropped
// http://docs.phinx.org/en/latest/commands.html#configuration-file-parameter

$config = DB_Helper::getConfig();

$phinx = [
    'paths' => [
        'migrations' => [
            'db/migrations',
        ],
        'seeds' => [
            'db/seeds',
        ],
    ],

    // http://docs.phinx.org/en/latest/configuration.html#custom-migration-base
    'migration_base_class' => AbstractMigration::class,

    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_database' => 'production',
        'production' => [
            'adapter' => 'mysql',
            'host' => $config['hostname'],
            'name' => $config['database'],
            'user' => $config['username'],
            'pass' => $config['password'],
            'port' => $config['port'],
            'unix_socket' => $config['socket'] ?? null,

            // Specify MySQL storage engine
            // if not specified mysql server default will be used
            // Examples: 'MyISAM', 'InnoDB'
            'engine' => 'MyISAM',

            // charset and collation must be utf8 compatible
            'charset' => $config['charset'],
            'collation' => $config['collation'] ?? 'utf8_general_ci',

            // set SQL_MODE
            // http://dev.mysql.com/doc/refman/5.7/en/sql-mode.html
            // https://github.com/robmorgan/phinx/blob/v0.8.0/src/Phinx/Db/Adapter/MysqlAdapter.php#L104-L110
            'mysql_attr_init_command' => "SET SQL_MODE = ''",
        ],
    ],
];

// create "test" environment
$phinx['environments']['test'] = $phinx['environments']['production'];
$phinx['environments']['test']['name'] = 'e_test';

$event = new GenericEvent(null, $phinx);
EventManager::getEventDispatcher()->dispatch(SystemEvents::PHINX_CONFIG, $event);

return $event->getArguments();
