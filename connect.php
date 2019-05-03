<?php
//Ein Session starten
 if (!isset($_SESSION)) {
	if (session_start() === false) {
		die('Session konnte nicht gestartet werden.');
	}
}
//Mit folgenden Konditionen werden überprfüt, ob die Datensätze in SESSIONs schon existieren. 
if (isset($_POST['host'])) {
	$_SESSION['dbsettings']['dbHost'] = trim($_POST['host']);
}
if (isset($_POST['port'])) {
	$_SESSION['dbsettings']['dbPort'] = trim($_POST['port']);
}
if (isset($_POST['username'])) {
	$_SESSION['dbsettings']['dbUser'] = trim($_POST['username']);
}
if (isset($_POST['password'])) {
	$_SESSION['dbsettings']['dbPasswd'] = trim($_POST['password']);
}
$_SESSION['dbsettings']['isDEV'] = true;

// MYSQL Verbinding mit PDO Funktion von PHP erstellen
function getDB(): PDO
{
	if (!isset($_SESSION['dbsettings']) || !isset($_SESSION['dbsettings']['dbHost']) || !isset($_SESSION['dbsettings']['dbPort']) || !isset($_SESSION['dbsettings']['dbUser']) || !isset($_SESSION['dbsettings']['dbPasswd'])) {
		die('Missing DB settings.');
	}
	$dbSettings = $_SESSION['dbsettings'];
	$dsn = 'mysql:host=' . $dbSettings['dbHost'] . ';port=' .$dbSettings['dbPort'] . ';charset=UTF8';
	// Anleitung: http://php.net/manual/de/ref.pdo-mysql.php
	$attributeOptions = [
		PDO::ATTR_ERRMODE          => PDO::ERRMODE_EXCEPTION, // alle Fehlern werden in ein Exception gespeichert
		PDO::ATTR_EMULATE_PREPARES => false, // Prepare funktion fehlgeschlagen
	];
	try {
		//Verbindung erstellen
		$db = new PDO($dsn, $dbSettings['dbUser'], $dbSettings['dbPasswd'], $attributeOptions);
	//Rückgabe vom Server listen
	} catch (Throwable $e) {
		$eCode = $e->getCode();
		echo 'PDO could not connect to the database ' . $dbSettings['dbHost'] . ':' . $dbSettings['dbPort'] . $dbSettings['dbUser'] . '@' . $dbSettings['dbHost'] . ' ; error code = ' . $eCode . PHP_EOL;
		exit;
	}
	if ($dbSettings['isDEV']) {
		$db->exec('SET sql_safe_updates=1');
		// Anleitung: https://dev.mysql.com/doc/refman/8.0/en/mysql-tips.html
	}
	return $db;
}
//Gewählte Datenbank vom Benutzer wird überprüft, ob in der Datenbank existiert
function databaseExist(PDO $db, string $dbName) : bool {
	//Die Datenbankname wird in Cache gespeichert
	//if(in_array($dbName,$_SESSION['validDatabases'])) {
	//	return true;
	//}
	$stmt = $db->prepare('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?');
	$stmt->execute([$dbName]);
	$result = $stmt->fetchAll();
	if(isset($result[0]) &&  $result[0]['SCHEMA_NAME']===$dbName) {
		// $_SESSION['validDatabases'][]=$dbName;
		return true;
	}
	return false;
}
//Gewählte Tabelle vom Benutzer wird überprüft, ob in der Datenbank existiert
function tableExists(PDO $db, string $tableName) : bool {
	$stmt = $db->prepare('SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = ?');
	$stmt->execute([$tableName]);
	$result = $stmt->fetchAll();
	if(isset($result[0]) &&  $result[0]['TABLE_NAME']===$tableName) {
		return true;
	}
	return false;
}