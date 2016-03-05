<?php

use Phalcon\Di;
use Phalcon\Di\FactoryDefault;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;

ini_set('display_errors',1);
error_reporting(E_ALL);

define('ROOT_PATH', __DIR__);
define('PATH_LIBRARY', __DIR__ . '/../src/');
define('PATH_SERVICES', __DIR__ . '/../src/services/');
define('PATH_RESOURCES', __DIR__ . '/../src/resources/');

set_include_path(
	ROOT_PATH . PATH_SEPARATOR . get_include_path()
);

// требуется для phalcon/incubator
include __DIR__ . "/../vendor/autoload.php";
include 'models/TestModel.php';

// Используем автозагрузчик приложений для автозагрузки классов.
// Автозагрузка зависимостей, найденных в composer.
$loader = new \Phalcon\Loader();

$loader->registerDirs(
	array(
		ROOT_PATH,
		PATH_LIBRARY
	)
);

$loader->register();

$di = new FactoryDefault();
Di::reset();

$di->set('db', function () {
	return new DbAdapter(array(
		'host'     => 'localhost',
		'username' => 'root',
		'password' => '1665017',
		'dbname'   => 'phalcon-rest-jpa',
		"charset"  => 'utf8'
	));
});

Di::setDefault($di);