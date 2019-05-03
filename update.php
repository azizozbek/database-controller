<?php
//Abrufen von DB-Verbindung
require('connect.php');
@$db = getDB();

$errors         = array();  	// Eine Array für Fehlern
$data 			= array(); 		// Eine Array für Informationen
//Die Formfelder in die Variablen einfügen
$datenbank 	= 	$_POST['db'];
$table 		= 	$_POST['table'];
$column		= 	$_POST['column'];
$cell 		= 	$_POST['cell'];
$pkey 		= 	$_POST['pkey'];
$id 		= 	$_POST['id'];
$del 		=	@$_POST['del'];

if(databaseExist($db, $datenbank)){
	$selectDB = $db->exec('USE ' . $datenbank);
}
//Gewählte Datensätze löschen
if($del) {
	$shwclmn = $db->prepare("DELETE FROM $table WHERE $pkey=?");
	$shwclmn->execute([$id]);
}
//Die Datensätze aktualisieren
$shwclmn = $db->prepare("UPDATE $table SET $column=? WHERE $pkey=?");
$shwclmn->execute([$cell, $id]);
//Rückgabe zur Form schicken
if($shwclmn){
	$data['success'] = true;
	$data['message'] = 'Success!';
}else{
	$data['success'] = false;
	$data['message'] = 'Etwas ging schief!';
}
echo json_encode($data);
?>