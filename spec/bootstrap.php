<?php

/**
 * Part of the tueena lib
 *
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @link http://tueena.org/
 * @author Bastian Fenske <bastian.fenske@tueena.org>
 * @file
 */

declare(strict_types=1);
spl_autoload_register(function ($className) {

	$namespaceParts = explode('\\', $className);
	$firstNamespacePart = array_shift($namespaceParts);
	$secondNamespacePart = array_shift($namespaceParts);
	if ($firstNamespacePart !== 'tueenaLib' || $secondNamespacePart !== 'sql')
		return false;

	$basePath = __DIR__ . '/../';
	if ($namespaceParts[0] !== 'spec')
		$basePath .= 'source/';
	$path = $basePath . implode('/', $namespaceParts) . '.php';
	if (!is_readable($path))
		return false;

	include $path;
	return true;
});

$path = __DIR__ . '/mySqlConnectionData.php';
if (file_exists($path)) {

	$connectionData = include $path;
	list($hostName, $port, $userName, $password, $databaseName) = $connectionData;

} else {

	echo "To be able to run these tests, you have to setup an empty mySql database. Do you want to continue? [yes]\n> ";
	$handle = fopen ("php://stdin","r");
	$input = trim(fgets($handle));
	if($input !== 'yes' && $input !== '')
		exit;

	echo "Enter the hostname: [localhost]\n> ";
	$hostName = trim(fgets($handle));
	if ($hostName === '')
		$hostName = 'localhost';

	echo "Enter the port: [3306]\n> ";
	$port = trim(fgets($handle));
	if ($port === '')
		$port = 3306;
	else
		$port = (int) $port;

	echo "Enter the user name: [root]\n> ";
	$userName = trim(fgets($handle));
	if ($userName === '')
		$userName = 'root';

	echo "Enter the password (it will not be hidden):\n> ";
	$password = trim(fgets($handle));
	echo "Enter the database name:\n> ";
	$databaseName = trim(fgets($handle));

	echo "Are those values correct? [yes]\n\n Host name:     $hostName\n port:          $port\n user name:     $userName\n password:      $password\n database name: $databaseName\n\n> ";
	$input = trim(fgets($handle));
	if($input !== 'yes' && $input !== '')
		exit;

	echo "Testing the database connection... ";
	$errorReportingLevel = error_reporting(0);
	$mysqli = new \mysqli($hostName, $userName, $password, $databaseName, $port);
	if (!is_null($mysqli->connect_error)) {
		echo "error\n" . $mysqli->connect_error . "\n";
		exit;
	}
	error_reporting($errorReportingLevel);
	echo "ok\n";

	echo "Shall these data be stored to $path? [yes]\n> ";
	$input = trim(fgets($handle));
	if ($input === 'yes' || $input === '') {
		$content = "<?php\n\nreturn ['$hostName', $port, '$userName', '$password', '$databaseName'];\n";
		file_put_contents($path, $content);
	}
	fclose($handle);
}

define('TEST_DATABASE_HOST_NAME', $hostName);
define('TEST_DATABASE_PORT', $port);
define('TEST_DATABASE_USER_NAME', $userName);
define('TEST_DATABASE_PASSWORD', $password);
define('TEST_DATABASE_DATABASE_NAME', $databaseName);
