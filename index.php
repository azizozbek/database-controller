<?php
//Abrufen die Datei zum Datenbank verbinden
require('connect.php');
?>
<!DOCTYPE html>
<html>
<head>
	<title>Mysql Editor</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
	<style type="text/css">
 		.suchen {
	        background: initial;
		    border: none;
		    font-size: 2em;
		    font-weight: 200;
		    float: left;
		    margin: 10px 10px 10px 0px;
		    padding-left: 10px;
		    box-shadow: 0px 1px 5px #d4d4d4;
		    display: block;
		    width: 100%
		}
	</style>
</head>
<body>
	<header class="container mt-5"><h1><a href="index.php">Extern/Lokal Database Connector</a></h1></header>
	<div class="container">
		<div class="md-1">
			<!-- HTML FORM Erstellen-->
			<form method="post" action="index.php">
			  <div class="form-group">
			    <!-- Beschreibung des Inputs -->
			    <label for="username">Username</label>
			    <input type="text" name="username" class="form-control" placeholder="Datenbank Benutzer" value="<?php if(isset($_SESSION)) { echo @$_SESSION['dbsettings']['dbUser']; } //Überprüfen ob das Session gestartet ist, wenn ja, dann verwenden den Benutzername von Session ?>">
			    <!-- Kleine Hinweis für Input -->
			    <small class="form-text text-muted">Datenbank Username.</small>
			  </div>
			  <div class="row">
				  <div class="col md-9">
					  <div class="form-group">
					    <label for="host">Host</label>
					    <input type="text" name="host" class="form-control" placeholder="Als Standard: localhost" value="<?php if(isset($_SESSION)) { echo @$_SESSION['dbsettings']['dbHost']; } //Überprüfen ob das Session gestartet ist, wenn ja, dann verwenden den Host von Session ?>">
					    <small class="form-text text-muted">Als Standard: localhost</small>
					  </div>
				  </div>
				  <div class="col md-3">
					  <div class="form-group">
					    <label for="port">Port</label>
					    <input type="text" name="port" class="form-control" placeholder="Als Standard: 3306" value="<?php if(isset($_SESSION)) { echo @$_SESSION['dbsettings']['dbPort']; } //Überprüfen ob das Session gestartet ist, wenn ja, dann verwenden den Port von Session ?>">
					    <small class="form-text text-muted">Als Standard: 3306</small>
					  </div>
				  </div>
			  </div>
			  <div class="form-group">
			    <label for="password">Password</label>
			    <input type="password" name="password" class="form-control" placeholder="Password" value="<?php if(isset($_SESSION)) { echo @$_SESSION['dbsettings']['dbPasswd']; } //Überprüfen ob das Session gestartet ist, wenn ja, dann verwenden den Password von Session ?>">
			  </div>
			  <input type="submit" name="submit" class="btn btn-outline-primary btn-block" value="Datenbanken" /> 
			</form>
		</div>
		<div class="clearfix"></div>
		<div class="md-1">
			<!-- Suchbox der Seite-->
			<input type="text" class="suchen" placeholder="Suchen...">
			<?php 
				//Datenbank Einstellungen von der externen Datei
  				@$db = getDB($dbSettings);

  				//Die Datensätze in den Spalten einfügen
				function getHTMLrowForm(array $row, $type) : string {
					$myHTML = '';
 					foreach($row as $columnName => $value) {
						if($type == "column"){
							$prmkey = @$_SESSION['spkey'];
							$myHTML .= '<td id="'. @$row[$prmkey] .'">'. htmlentities($value) .'</td>';
 	 					}else{
	 						$myHTML .= '<td><a href="'.  $type .'='. htmlentities($value) .'">'.htmlentities($value).'</a></td>';
 	 					}
	 				}
					return '<tr id ="' . @$_SESSION['spkey'] . '">'.$myHTML.'</tr>';
				}
				// Die Datensätze von der Datenbank auslesen und in eine Tabelle einfügen
				function getQuery($dbs, $query, $type, $tbl = NULL) {
	 				try {
						$getQuery = $dbs->prepare($query);
						$getQuery->execute();
						$showQuery = $getQuery->fetchAll(PDO::FETCH_ASSOC);
 	 					if(!empty($tbl)){
		 					$shwclmn = $dbs->prepare("describe $tbl");
		 					$shwclmn->execute();
							$getClmn = $shwclmn->fetchAll(PDO::FETCH_ASSOC);
	 					}
						$ausgabeHTML = '<table class="table table-striped" id="tbl"><thead>';
						if(!empty($tbl)){ 
								$head = '';
 		 						foreach($getClmn as $columnName => $value) {
 				 						$head .= '<th>'.$value['Field'].'</th>';
 				 				}
 								$ausgabeHTML .= $head . "</thead><tbody>";
						} 
						foreach($showQuery as $rowData) {
								$ausgabeHTML .= getHTMLrowForm($rowData, $type); 
						}
						$ausgabeHTML .= '</tbody></table>';
						echo $ausgabeHTML;
					}catch (Throwable $e) {
						$eCode = $e->getCode();
						echo "Guck mal welche Fehlermeldung ist das: " . $eCode . "</br>Tipp: Nicht herumspielen! ";
					}
	 			}		
	 			// Überprüfen ob das Form geschickt wurde
				if(isset($_POST['submit'])){
					getQuery($db, "SHOW DATABASES", "?db");
				}
				//Überprüfen ob der Paramater db in URL beinhaltet
				if (isset($_GET['db'])) {

				 	$datenbank = $_GET['db'];
				 	// Den Wert vom Parameter db in Session speichern
				 	$_SESSION['db'] = $datenbank;
	 			 	if(databaseExist($db, $datenbank)){
	 			 		//Ausgewählte DB verwenden
						$selectDB = $db->exec('USE ' . $datenbank);
					}
					//Die Tabellen von der Datenbank auslesen
	 			 	getQuery($db, "SHOW TABLES", "?table");
	 			//Überprüfen ob der Parameter table in der URL gibt
				}elseif (isset($_GET['table'])) {
					$sdb = $_SESSION['db'];
					//Überprüfen ob die Datenbankname in der Datenbankverzeichnis existiert
				 	if(databaseExist($db, $_SESSION['db'])){
						$selectDB = $db->exec('USE ' . $sdb);
					} 

					$tblName = $_GET['table'];
					if(tableExists($db, $_GET['table'])){
		 			 	// HOl PRIMARY KEY
				 		$PrimKey = $db->prepare("SHOW KEYS FROM $tblName WHERE Key_name = 'PRIMARY'");
						$PrimKey->execute();
						$getPkey = $PrimKey->fetch(PDO::FETCH_ASSOC);
 					}
 					//echo "<script>alert('". . "')</script>";
	  			 	// Den Primär Schlüssel und Tabellenname in SESSION hinfügen
	  			 	@$_SESSION['spkey'] = $getPkey['Column_name'];
	 			 	@$_SESSION['table'] = $tblName;
	 				//Die Datensätze von der Datenbank ablesen
	 			 	$query = "SELECT * FROM $tblName";
	   			 	getQuery($db, $query, "column", $tblName);
 				 } 
	  		?>
		</div>
	</div>
</body>
<script>
	$(document).ready(function(){
		// Wenn eine Eingabe eingegeben wird, tabelle wird gefiltert.
	    $('.suchen').on('keyup',function(){
	        var searchTerm = $(this).val().toLowerCase();
	        $('#tbl  tr').each(function(){
	            var lineStr = $(this).text().toLowerCase();
	            if(lineStr.indexOf(searchTerm) === -1){
	                $(this).hide();
	            }else{
	                $(this).show();
	            }
	        });
	    });
 	});
<?php 
	if(isset($_GET['table'])){ ?>
	 	let table = document.getElementById('tbl');
		let editingTd;
		//Bei einem Klick wird ein Eingabefeld angezeigt 
		table.onclick = function(event) {
		  // 3 mögliche ziele
		  let target = event.target.closest('.edit-delete,.edit-cancel,.edit-ok,td');
		  if (!table.contains(target)) return;
		  if (target.className == 'edit-cancel') {
		    finishTdEdit(editingTd.elem, false, false);
		  } else if (target.className == 'edit-delete') {
		    finishTdEdit(editingTd.elem, true, true);
		  } else if (target.className == 'edit-ok') {
		    finishTdEdit(editingTd.elem, true, false);
		  } else if (target.nodeName == 'TD') {
		    if (editingTd) return; // already editing
		    makeTdEditable(target);
		  }
		};
		function makeTdEditable(td) {
		  editingTd = {
		    elem: td,
		    data: td.innerHTML
		  };
		  td.classList.add('edit-td'); // td is in edit state, CSS also styles the area inside
		  let textArea = document.createElement('textarea');
		  textArea.style.width = td.clientWidth + 'px';
		  textArea.style.height = td.clientHeight + 'px';
		  textArea.className = 'edit-area';
		  textArea.value = td.innerHTML;
		  td.innerHTML = '';
		  td.appendChild(textArea);
		  textArea.focus();
		  td.insertAdjacentHTML("beforeEnd",
		    '<div class="edit-controls"><button class="edit-ok">OK</button><button class="edit-cancel">CANCEL</button><button class="edit-delete">Delete</button></div>'
		  );
		}
		// Mit AJAX werden die Formfelder mit POST geschickt.
		function finishTdEdit(td, isOk, del) {
		  if (isOk) {
		    td.innerHTML = td.firstChild.value;
			    var $td = $(td),
			        $th = $td.closest('table').find('th').eq($td.index());
			    let tr 	= $td.closest('table').find('tr').eq($td.index());
		    var formData = {
					'column' 			: $th[0].innerHTML,
					'cell' 				: td.innerHTML,
					'id' 				: td.getAttribute("id"),
					'db'				: '<?php echo $_SESSION['db']; ?>',
					'table'				: '<?php echo $_SESSION['table']; ?>',
					'pkey'				: '<?php echo $_SESSION['spkey']; ?>'
				};
			if(del){
				var delData = {
					'del'				: true
				}
				$.extend(true, formData, delData);
			}
			$.ajax({
				type 		: 'POST', // Definiere den Typ des HTTP-Verbs, den wir verwenden möchten (POST für unser Formular).
				url 		: 'update.php', // die URL, an die wir den POST senden möchten
				data 		: formData, // die Formfelder
				dataType 	: 'json', // Welche Art von Daten erwarten wir vom Server
				encode 		: true
			})
				// Rückmeldung vom Server überprüfen
				.done(function(data) {
					// Die Rückmeldung in Console-Log anzeigen
					if(del){
						console.log(tr);
						tr.addClass('d-none');
					}
					console.log(data); 
				})
				.fail(function(data) {
						// alle Fehlern anzeigen
						console.log(data);
					});
		  } else {
		    td.innerHTML = editingTd.data;
		  }
		  td.classList.remove('edit-td');
		  editingTd = null;
	}
<?php } ?>
</script>
</html>