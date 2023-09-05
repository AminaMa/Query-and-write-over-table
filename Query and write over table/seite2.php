<?php
#*************************************************************************************#


				#****************************************#
				#********** PAGE CONFIGURATION **********#
				#****************************************#
				
				require_once('../include/config.inc.php');
				require_once('../include/db.inc.php');
				require_once('../include/form.inc.php');

				
#*************************************************************************************#


				#******************************************#
				#********** INITIALIZE VARIABLES **********#
				#******************************************#
				
				$empID			= NULL;
				$misNumber		= NULL;
					
				$errorID			= NULL;
				$errorNumber	= NULL;
				
				$dbError 		= NULL;
				$dbSuccess 		= NULL;
				

#*************************************************************************************#

				
				#***********************************#
				#********** DB CONNECTION **********#
				#***********************************#
				
				$PDO = dbConnect('company');	
				
				
#*************************************************************************************#


				#***********************************************#
				#********** PROCESS FORM ADD DAYS OFF **********#
				#***********************************************#				

/*
if(DEBUG_V)	echo "<pre class='debug value'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG_V)	print_r($_POST);					
if(DEBUG_V)	echo "</pre>";
*/
				
				// Schritt 1 FORM: Prüfen, ob Formular abgeschickt wurde
				if( isset($_POST['addDaysOff']) ) {
if(DEBUG)		echo "<p class='debug'>🧻 <b>Line " . __LINE__ . "</b>: Formular 'Add Days Off' wurde abgeschickt. <i>(" . basename(__FILE__) . ")</i></p>\n";										

					// Schritt 2: Werte abholsen, entschärfen, DEBUG-Ausgabe
if(DEBUG)		echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Werte auslesen und entschärfen... <i>(" . basename(__FILE__) . ")</i></p>\n";

					$empID 		= sanitizeString($_POST['empID']);
					$misNumber 	= sanitizeString($_POST['misNumber']);
if(DEBUG_V)		echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$empID: $empID <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_V)		echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$misNumber: $misNumber <i>(" . basename(__FILE__) . ")</i></p>\n";

				
					// Schritt 3 FORM: Daten validieren
if(DEBUG)		echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Feldvalidierung... <i>(" . basename(__FILE__) . ")</i></p>\n";				

					$errorID 		= validateInputString($empID);
					$errorNumber 	= validateInputString($misNumber);
					
					
					#********** FINAL FORM VALIDATION **********#
					if( $errorID OR $errorNumber ) {
						// Fehlerfall
if(DEBUG)			echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Das Formular enthält noch Fehler! <i>(" . basename(__FILE__) . ")</i></p>\n";				
						
					} else {
						// Erfolgsfall
if(DEBUG)			echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Das Formular ist formal fahlerfrei und wird nun verarbeitet... <i>(" . basename(__FILE__) . ")</i></p>\n";				
						
						// Schritt 4 FORM: Daten weiterverarbeiten


						#***********************************#
						#********** DB OPERATIONS **********#
						#***********************************#
					
						// Schritt 1 DB: DB-Verbindung herstellen
						// Ist bereits geschehen
						
						
						#********** UPDATE DAYS OFF TO DB **********#
if(DEBUG)			echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Speichere Fehltage in DB... <i>(" . basename(__FILE__) . ")</i></p>\n";

						$sql 		= 'UPDATE missingDays
										SET misNumber = misNumber + :ph_misNumber
										WHERE empID = :ph_empID';
						
						$params 	= array(
												'ph_misNumber' => $misNumber,
												'ph_empID' 		=> $empID
												);
						
						
						// Schritt 2 DB: SQL-Statement vorbereiten
						$PDOStatement = $PDO->prepare($sql);
															
						// Schritt 3 DB: SQL-Statement ausführen und ggf. Platzhalter füllen
						try {	
							$PDOStatement->execute($params);						
						} catch(PDOException $error) {
if(DEBUG)				echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";										
							$dbError = 'Fehler beim Zugriff auf die Datenbank!';
						}
						
						// Schritt 4 DB: Daten weiterverarbeiten
						// Bei schreibendem Zugriff: Prüfen, ob Schreibvorgang erfolgreich war
						$rowCount = $PDOStatement->rowCount();
if(DEBUG_V)			echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$rowCount: $rowCount <i>(" . basename(__FILE__) . ")</i></p>\n";
							
						// Wenn der rowCount einen anderen Wert als 0 hat, war das Schreiben erfolgreich
						if( $rowCount === 0 ) {
							// Fehlerfall
if(DEBUG)				echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER beim Erfassen der Fehltag! <i>(" . basename(__FILE__) . ")</i></p>\n";				
							$dbError = 'Es ist ein Fehler aufgetreten! Bitte versuchen Sie es später noch einmal.';
							
						} else {
							// Erfolgsfall
if(DEBUG)				echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Fehltage für den Mitarbeiter mit der ID$empID wurden erfolgreich erfasst. <i>(" . basename(__FILE__) . ")</i></p>\n";				
							$dbMessage = "<p class='success'>Fehltage für den Mitarbeiter mit der ID$empID wurden erfolgreich erfasst.</p>";	
							
							// Formularvorbelegung löschen
							$empID		= NULL;
							$misNumber	= NULL;							
							
						} // UPDATE DAYS OFF TO DB END
						
					} // FINAL FORM VALIDATION END
											
				} // PROCESS FORM ADD DAYS OFF END
				
				
#*************************************************************************************#


				#***********************************************************#
				#********** FETCH EMPLOYEES PLUS DAYS OFF FROM DB **********#
				#***********************************************************#

if(DEBUG)	echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Lese Mitarbeiter plus Fehltage aus DB aus... <i>(" . basename(__FILE__) . ")</i></p>\n";
				
				// Schritt 1 DB: DB-Verbidung herstellen
				// Ist bereits geschehen
				
				$sql 		= 'SELECT * FROM employees
								INNER JOIN departments USING(depID)
								INNER JOIN missingDays USING(empID)';
				
				$params 	= array();
				
				// Schritt 2 DB: SQL-Vorbereiten
				$PDOStatement = $PDO->prepare($sql);

				// Schritt 3 DB: SQL-Statement ausführen und ggf. Platzhalter füllen
				try {	
					$PDOStatement->execute($params);						
				} catch(PDOException $error) {
if(DEBUG)		echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";										
					$dbError = 'Fehler beim Zugriff auf die Datenbank!';
				}
				
				// Schritt 4 DB: Daten weiterverarbeiten
				// Bei lesendem Zugriff: Datensätze abholen
				$dataSetEmployees = $PDOStatement->fetchAll(PDO::FETCH_ASSOC);

/*																
if(DEBUG_V)	echo "<pre class='debug value'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG_V)	print_r($dataSetEmployees);					
if(DEBUG_V)	echo "</pre>";
*/
													
#*************************************************************************************#


				#**********************************************************#
				#********** FETCH ALL MISSING DAYS BY DEPARTMENT **********#
				#**********************************************************#

if(DEBUG)	echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Lese Fehltage nach Abteilungen aus... <i>(" . basename(__FILE__) . ")</i></p>\n";
				
				// Schritt 1 DB: DB-Verbindung herstellen
				// Ist bereits geschehen
				
				$sql 		= 'SELECT depLabel, sum(misNumber) AS misSum
								FROM employees 
								INNER JOIN missingDays USING(empID)
								INNER JOIN departments USING(depID)
								GROUP BY depLabel';
				
				$params 	= array();
				
				// Schritt 2 DB: SQL-Vorbereiten
				$PDOStatement = $PDO->prepare($sql);

				// Schritt 3 DB: SQL-Statement ausführen und ggf. Platzhalter füllen
				try {	
					$PDOStatement->execute($params);						
				} catch(PDOException $error) {
if(DEBUG)		echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";										
					$dbError = 'Fehler beim Zugriff auf die Datenbank!';
				}
				
				// Schritt 4 DB: Daten weiterverarbeiten
				// Bei lesendem Zugriff: Datensätze abholen
				$dataSetMissingDays = $PDOStatement->fetchAll(PDO::FETCH_ASSOC);

/*
if(DEBUG_V)	echo "<pre class='debug value'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG_V)	print_r($dataSetMissingDays);					
if(DEBUG_V)	echo "</pre>";
*/
													
																										
#*************************************************************************************#				
?>

<!doctype html>

<html>

	<head>
		<meta charset="utf-8">
		<title>Abfragen und Schreiben über mehrere Tabellen</title>
		<link rel="stylesheet" href="../css/main.css">
		<link rel="stylesheet" href="../css/debug.css">
		
	</head>

	<body>
		<h1>Abfragen und Schreiben über mehrere Tabellen - Seite 2</h1>
		<br>
		<p><a href="seite1.php"><<< Seite 1</a></p>
		
		<br>
		
		
		<!-- ---------- SHOW EMPLOYEES AND DAYS OFF START ---------->
		<h3>Erfasste Mitarbeiter inklusive Fehltage:</h3>
		<table>
			<tr>
				<th>ID</th>
				<th>Vorname</th>
				<th>Nachnamename</th>
				<th>Email</th>
				<th>Straße</th>
				<th>PLZ</th>
				<th>Ort</th>
				<th>Abteilung</th>
				<th>Fehltage</th>
			</tr>
			<?php foreach( $dataSetEmployees AS $employee ): ?>
				<tr>
					<td><?php echo $employee['empID'] ?></td>
					<td><?php echo $employee['empFirstName'] ?></td>
					<td><?php echo $employee['empLastName'] ?></td>
					<td><?php echo $employee['empEmail'] ?></td>
					<td><?php echo $employee['empStreet'] ?></td>
					<td><?php echo $employee['empZipCode'] ?></td>
					<td><?php echo $employee['empCity'] ?></td>
					<td><?php echo $employee['depLabel'] ?></td>
					<td><?php echo $employee['misNumber'] ?></td>
				</tr>
			<?php endforeach ?>
		</table>
		<!-- ---------- SHOW EMPLOYEES AND DAYS OFF END ---------->
		
		<br>
		<hr>
		<br>
		
		<!-- ---------- USER MESSAGES START ---------- -->
		<?php if($dbError): ?>
			<h3 class="error"><?= $dbError ?></h3>
		<?php elseif($dbSuccess): ?>
			<h3 class="success"><?= $dbSuccess ?></h3>
		<?php endif ?>
		<!-- ---------- USER MESSAGES END ---------- -->
		
		<br>
		<br>
		
		<h3>Neue Fehltage erfassen:</h3>

		<!-- ---------- FORM SET DAYS OFF START ---------->
		<form action="" method="post">
		
			<input type="hidden" name="addDaysOff">
			
			<span class="error"><?= $errorID ?></span>
			<select name="empID">
				<?php foreach( $dataSetEmployees AS $employee ): ?>
				<option value="<?= $employee['empID'] ?>"><?= $employee['empLastName'] ?>, <?= $employee['empFirstName'] ?></option>
				<?php endforeach ?>
			</select>
			<input class="short" type="text" name="misNumber" value="<?= $misNumber ?>" placeholder="Anzahl Fehltage"><span class="error"><?= $errorNumber ?></span>
			<input class="short" type="submit" value="Fehltage eintragen"><br>
		</form>
		<!-- ---------- FORM SET DAYS OFF END ---------->
		
		<br>
		<hr>
		<br>
		
		<h3> Alle in den Abteilungen angefallenen Fehltage:</h3>
		<!-- ---------- SHOW DAYS OFF BY DEPARTMENT START ---------->
		<table>
			<tr>
				<th>Abteilung</th>
				<th>Summe der Fehltage</th>
			</tr>
			<?php foreach( $dataSetMissingDays AS $missingDays ): ?>
				<tr>
					<td><?php echo $missingDays['depLabel'] ?></td>
					<td><?php echo $missingDays['misSum'] ?></td>
				</tr>
			<?php endforeach ?>
		</table>
		<!-- ---------- SHOW DAYS OFF BY DEPARTMENT END ---------->
		
		<br>
		<br>
		<br>
		<br>
		<br>
	</body>
	
</html>































