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
				
				$idArray 			= array();
				
				$firstName 			= NULL;
				$lastName 			= NULL;
				$email 				= NULL;
				$street 				= NULL;
				$zipCode				= NULL;
				$city 				= NULL;
				$departmentID		= NULL;
				
				$errorFirstName 	= NULL;
				$errorLastName 	= NULL;
				$errorEmail 		= NULL;
				$errorStreet 		= NULL;
				$errorZipCode		= NULL;
				$errorCity 			= NULL;
				
				$dbError 			= NULL;				
				$dbSuccess 			= NULL;				


#*************************************************************************************#

				
				#***********************************#
				#********** DB CONNECTION **********#
				#***********************************#
				
				$PDO = dbConnect('company');				
				

#*************************************************************************************#


				#**************************************************#
				#********** FETCH DEPARTMENT IDS FROM DB **********#
				#**************************************************#

if(DEBUG)	echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Lese Abteilungen aus DB aus... <i>(" . basename(__FILE__) . ")</i></p>\n";
				
				
				// Schritt 1 DB: DB-Verbindung herstellen
				// ist bereits geschehen
					
				$sql 		= 'SELECT * FROM departments';
					
				$params 	= array();
					
				// Schritt 2 DB: SQL-Statement vorbereiten
				$PDOStatement = $PDO->prepare($sql);
				
				// Schritt 3 DB: SQL-Statement ausführen und ggf. Platzhalter füllen
				try {	
					$PDOStatement->execute($params);						
				} catch(PDOException $error) {
if(DEBUG)		echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";										
					$dbError = 'Fehler beim Zugriff auf die Datenbank!';
				}
				
				// Schritt 4 DB: Daten weiterverarbeiten
				$departmentsArray = $PDOStatement->fetchAll(PDO::FETCH_ASSOC);

/*
if(DEBUG_V)	echo "<pre class='debug value'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG_V)	print_r($departmentsArray);					
if(DEBUG_V)	echo "</pre>";
*/

#*************************************************************************************#
				
				
				#***********************************************#
				#********** PROCESS FORM NEW EMPLOYEE **********#
				#***********************************************#

/*
if(DEBUG_V)	echo "<pre class='debug value'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG_V)	print_r($_POST);					
if(DEBUG_V)	echo "</pre>";
*/

				// Schritt 1 FORM: Prüfen, ob Formular abgeschickt wurde
				if( isset($_POST['newEmployee']) ) {
if(DEBUG)		echo "<p class='debug'>🧻 <b>Line " . __LINE__ . "</b>: Formular 'New Employee' wurde abgeschickt. <i>(" . basename(__FILE__) . ")</i></p>\n";										

					// Schritt 2: Werte auslesen, entschärfen, DEBUG-Ausgabe
if(DEBUG)		echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Werte auslesen und entschärfen... <i>(" . basename(__FILE__) . ")</i></p>\n";

					$firstName 		= sanitizeString($_POST['firstName']);
					$lastName 		= sanitizeString($_POST['lastName']);
					$email 			= sanitizeString($_POST['email']);
					$street 			= sanitizeString($_POST['street']);
					$zipCode			= sanitizeString($_POST['zipCode']);
					$city 			= sanitizeString($_POST['city']);
					$departmentID 	= sanitizeString($_POST['departmentID']);		
if(DEBUG_V)		echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$firstName: $firstName <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_V)		echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$lastName: $lastName <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_V)		echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$email: $email <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_V)		echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$street: $street <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_V)		echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$zipCode: $zipCode <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_V)		echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$city: $city <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_V)		echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$departmentID: $departmentID <i>(" . basename(__FILE__) . ")</i></p>\n";
				
					// Schritt 3 FORM: Daten validieren
if(DEBUG)		echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Feldvalidierung... <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					$errorFirstName 	= validateInputString($firstName);
					$errorLastName 	= validateInputString($lastName);
					$errorEmail 		= validateEmail($email);
					$errorStreet 		= validateInputString($street);
					$errorZipCode		= validateInputString($zipCode);
					$errorCity 			= validateInputString($city);
										
					
					#********** FINAL FORM VALIDATION **********#
					if( $errorFirstName OR $errorLastName OR $errorEmail OR $errorStreet OR $errorZipCode OR $errorCity ) {
						// Fehlerfall
if(DEBUG)			echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Das Formular enthält noch Fehler! <i>(" . basename(__FILE__) . ")</i></p>\n";				
					
					} else {
						// Erfolgsfall
if(DEBUG)			echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Das Formular ist formal fehlerfrei und wird nun verarbeitet... <i>(" . basename(__FILE__) . ")</i></p>\n";				
						
						// Schritt 4 FORM: Daten weiterverarbeiten
						
							
						#***********************************#
						#********** DB OPERATIONS **********#
						#***********************************#
					
						// Schritt 1 DB: DB-Verbindung herstellen
						// Ist bereits geschehen
						
						
						#********** TRANSACTION START **********#
						
						if( !$PDO->beginTransaction() ) {
							// Fehlerfall
if(DEBUG)				echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER beim Starten der Transaction! <i>(" . basename(__FILE__) . ")</i></p>\r\n";				
							$dbError = 'Es ist ein Fehler aufgetreten! Bitte versuchen Sie es später noch einmal.';
							
						} else {
							// Erfolgsfall
if(DEBUG)				echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Transaction erfolgreich gestartet. <i>(" . basename(__FILE__) . ")</i></p>\r\n";				
					
											
							#********** 1. ADD NEW EMPLOYEE **********#				
							$sql 		= 'INSERT INTO employees
											(empFirstName, empLastName, empEmail, empStreet, empZipCode, empCity, depID)
											VALUES
											(:ph_empFirstName, :ph_empLastName, :ph_empEmail, :ph_empStreet, :ph_empZipCode, :ph_empCity, :ph_depID)
											';
									  
							$params 	= array(
													'ph_empFirstName' 	=> $firstName,
													'ph_empLastName' 		=> $lastName,
													'ph_empEmail' 			=> $email,
													'ph_empStreet' 		=> $street,
													'ph_empZipCode' 		=> $zipCode,
													'ph_empCity' 			=> $city,
													'ph_depID' 				=> $departmentID
													);
							
							// Schritt 2 DB: SQL-Statement vorbereiten
							$PDOStatement = $PDO->prepare($sql);
							
							// Schritt 3 DB: SQL-Statement ausführen und ggf. Platzhalter füllen
							try {	
								$PDOStatement->execute($params);						
							} catch(PDOException $error) {
if(DEBUG)					echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";										
								$dbError = 'Fehler beim Zugriff auf die Datenbank!';
							}
															
							// Schritt 4 DB: Daten weiterverarbeiten
							// Bei schreibendem Zugriff: Schreiberfolg prüfen
							$rowCount = $PDOStatement->rowCount();
if(DEBUG_V)				echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$rowCount: $rowCount <i>(" . basename(__FILE__) . ")</i></p>\n";
								
							if( !$rowCount ) {
								// Fehlerfall
if(DEBUG)					echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER beim Speichern des neuen Mitarbeiters! <i>(" . basename(__FILE__) . ")</i></p>\n";				
								$dbError = 'Es ist ein Fehler aufgetreten! Bitte versuchen Sie es später noch einmal.';
								
							} else {
								// Erfolgsfall
								// Nach dem erfolgreichen Schreiben in die DB die letzte vergebene ID auslesen
								$newEmployeeId = $PDO->lastInsertId();
if(DEBUG)					echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Neuer Mitarbeiter erfolgreich unter ID$newEmployeeId in der DB gespeichert. <i>(" . basename(__FILE__) . ")</i></p>\n";				
									
									
								#********** 2. ADD ENTRY DAYS OFF **********#							
								$sql 		= 'INSERT INTO missingDays
												(misNumber, empID)
												VALUES
												(:ph_misNumber, :ph_empID)';
								
								$params 	= array(
														'ph_misNumber'	=> 0,
														'ph_empID'			=> $newEmployeeId
														);
								
								// Schritt 2 DB: SQL-Statement vorbereiten
								$PDOStatement = $PDO->prepare($sql);
																
								// Schritt 3 DB: SQL-Statement ausführen und ggf. Platzhalter füllen
								try {	
									$PDOStatement->execute($params);						
								} catch(PDOException $error) {
if(DEBUG)						echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";										
									$dbError = 'Fehler beim Zugriff auf die Datenbank!';
								}	
								
								// Schritt 4 DB: Daten weiterverarbeiten
								// Bei schreibendem Zugriff: Schreiberfolg prüfen
								$rowCount = $PDOStatement->rowCount();
if(DEBUG_V)					echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$rowCount: $rowCount <i>(" . basename(__FILE__) . ")</i></p>\n";
								
								if( !$rowCount ) {
									// Fehlerfall
if(DEBUG)						echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER beim Speichern des neuen Fehltagedatensatzes! <i>(" . basename(__FILE__) . ")</i></p>\n";				
									$dbError = 'Es ist ein Fehler aufgetreten! Bitte versuchen Sie es später noch einmal.';
									
									
									#********** ROLLBACK DB CHANGES **********#
									if( !$PDO->rollBack() ) {
										// Fehlerfall
if(DEBUG)							echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER beim Durchführen des Rollbacks! <i>(" . basename(__FILE__) . ")</i></p>\n";				
										
									} else {
										// Erfolgsfall
if(DEBUG)							echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Rollback erfolgreich durchgeführt. <i>(" . basename(__FILE__) . ")</i></p>\n";																
									}
									#*****************************************#
									
								} else {
									// Erfolgsfall
									// Nach dem erfolgreichen Schreiben in die DB die letzte vergebene ID auslesen
									$newDaysOffID = $PDO->lastInsertId();
if(DEBUG)						echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Fehltagedatensatz erfolgreich unter ID$newDaysOffID in der DB gespeichert. <i>(" . basename(__FILE__) . ")</i></p>\n";				
									
									
									#********** COMMIT DB CHANGES **********#
									if( !$PDO->commit() ) {
										// Fehlerfall
if(DEBUG)							echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER beim Durchführen des Commits! <i>(" . basename(__FILE__) . ")</i></p>\n";				
										$dbError = 'Es ist ein Fehler aufgetreten! Bitte versuchen Sie es später noch einmal.';
										
									} else {
										// Erfolgsfall
if(DEBUG)							echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Commit erfolgreich durchgeführt. <i>(" . basename(__FILE__) . ")</i></p>\n";				
																													
										$dbSuccess = 'Der neue Mitarbeiter wurde erfolgreich in der Datenbank angelegt.';
										
										// Vorbelegungen der Formularfelder wieder löschen
										$firstName 		= NULL;
										$lastName 		= NULL;
										$email 			= NULL;
										$street 			= NULL;
										$zipCode			= NULL;
										$city 			= NULL;
										$departmentID	= NULL;
										
									} // COMMIT DB CHANGES END
									
								} // 2. ADD ENTRY DAYS OF END
								
							} // 1. ADD NEW EMPLOYEE END
							
						} // TRANSACTION END

					} // FINAL FORM VALIDATION END
					
				} // PROCESS FORM NEW EMPLOYEE EN			
				
			
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
		<h1>Abfragen und Schreiben über mehrere Tabellen - Seite 1"</h1>
		<br>
		<p><a href="seite2.php">Seite 2 >>></a></p>
		
		<br>
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
		
		<h3>Einen neuen Datensatz in die Tabelle schreiben:</h3>
		
		<!-- ---------- FORM NEW EMPLOYEE START ---------- -->
		<form action="" method="POST">
		
				<input type="hidden" name="newEmployee">
				
				<label>Abteilung:</label>
				<select name="departmentID">
					<?php foreach( $departmentsArray AS $department ): ?> {
						<option value='<?= $department['depID'] ?>' <?php if($department['depID'] === $departmentID) echo 'selected'?>><?= $department['depLabel'] ?></option>\r\n";
					<?php endforeach ?>
				</select>
				<br>
				<br>
				<span class="error"><?php echo $errorFirstName ?></span>
				<input type="text" name="firstName" value="<?php echo $firstName ?>" placeholder="Vorname"><span class="marker">*</span>
				<br>
				<span class="error"><?php echo $errorLastName ?></span>
				<input type="text" name="lastName" value="<?php echo $lastName ?>" placeholder="Nachname"><span class="marker">*</span>
				<br>
				<span class="error"><?php echo $errorEmail ?></span>
				<input type="text" name="email" value="<?php echo $email ?>" placeholder="Email-Adresse"><span class="marker">*</span>
				<br>
				<span class="error"><?php echo $errorStreet ?></span>
				<input type="text" name="street" value="<?php echo $street ?>" placeholder="Straße"><span class="marker">*</span>
				<br>
				<span class="error"><?php echo $errorZipCode ?></span>
				<input type="text" name="zipCode" value="<?php echo $zipCode ?>" placeholder="PLZ"><span class="marker">*</span>
				<br>
				<span class="error"><?php echo $errorCity ?></span>
				<input type="text" name="city" value="<?php echo $city ?>" placeholder="Ort"><span class="marker">*</span>
				<br>
				<input type="submit" value="Neuen Mitarbeiter anlegen">
			</form>
			<!-- ---------- FORM NEW EMPLOYEE END ---------- -->
			
			
			<br>
			<br>
			<br>
	</body>
	
</html>































