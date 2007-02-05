<?php
/**
 * Bekommt per POST Daten zugesannt und speichert diese in die DB
 * Als Ergebnis wird ein RDF File geliefert mit return=true/false und errormessage
 *
 * Aufruf mit POST Parameter:
 * type=mitarbeiter
 * 		anrede, titelpre, titelpost, vorname, vornamen, nachname, ...
 *
 */
	include("../../include/fas/person.class.php");
	include("../../include/fas/mitarbeiter.class.php");
	include("../../include/fas/funktion.class.php");
	include("../../include/fas/adresse.class.php");
	include("../../include/fas/email.class.php");
	include("../../include/fas/telefonnummer.class.php");
	include("../../include/fas/bankverbindung.class.php");
	include("../../include/fas/benutzer.class.php");
	include("../../include/fas/functions.inc.php");
	include("../../include/fas/lehreinheit.class.php");
	include("../../include/fas/lehrveranstaltung.class.php");
	include("../../include/benutzerberechtigung.class.php");
	include("../../include/functions.inc.php");
	include("../../vilesci/config.inc.php");

	//Header Schicken
	header("Cache-Control: no-cache");
	header("Cache-Control: post-check=0, pre-check=0",false);
	header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
	header("Pragma: no-cache");
	// content type setzen
	header("Content-type: application/vnd.mozilla.xul+xml");

	function convertdate($date)
	{
		list($d,$m,$y) = explode(".",$date);
		return $y."-".$m."-".$d;
	}

	// xml
	echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
	$rdf_url='http://www.technikum-wien.at/dbdml';
	$error=false;
	$errormsg = 'Funktion noch nicht implementiert';
	$return = 'false';

	//UID holen
	$user=get_uid();

	//Sollte eigentlich nie vorkommen
	if($user=='')
	{
		$error = true;
		$return = 'false';
		$errormsg = 'User konnte nicht ermittelt werden';
	}

	//VILESCI Datenbankverbindung herstellen
	if(!$conn = pg_pconnect(CONN_STRING))
	{
		$error = true;
		$return = 'false';
		$errormsg = 'Verbindung zur Datenbank fehlgeschlagen';
	}

	//FAS Datenbankverbindung herstellen
	if(!$conn_fas = pg_pconnect(CONN_STRING_FAS))
	{
		$error = true;
		$return = 'false';
		$errormsg = 'Verbindung zur Datenbank fehlgeschlagen';
	}
	$rechte = new benutzerberechtigung($conn);
	$rechte->getBerechtigungen($user);
	$benutzer = new benutzer($conn);
	$benutzer->loadVariables($user);
?>
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:NC="http://home.netscape.com/NC-rdf#"
	xmlns:DBDML="<?php echo $rdf_url; ?>/rdf#"
>


  <RDF:Seq RDF:about="<?php echo $rdf_url ?>/msg">
<?php
	if(!$error)
	{
		//Speichern eines Mitarbeiters
		if(isset($_POST['type']) && $_POST['type']=='mitarbeiter') /***********Mitarbeiter Stammdaten***********/
		{
			if($rechte->isBerechtigt('admin', 0, 'i')
		    || $rechte->isBerechtigt('admin', 0, 'u')
		    || $rechte->isBerechtigt('mitarbeiter', 0, 'i')
		    || $rechte->isBerechtigt('mitarbeiter', 0, 'u'))
			{
				$mitarbeiter = new mitarbeiter($conn_fas);
				//Werte holen und zuweisen
				$mitarbeiter->new = ($_POST['new']=='true'?true:false);
				$mitarbeiter->person_id = $_POST['person_id'];
				$mitarbeiter->mitarbeiter_id = $_POST['mitarbeiter_id'];
				$mitarbeiter->anrede = $_POST['anrede'];
				$mitarbeiter->titelpre = $_POST['titelpre'];
				$mitarbeiter->titelpost = $_POST['titelpost'];
				$mitarbeiter->familienname = $_POST['nachname'];
				$mitarbeiter->vorname = $_POST['vorname'];
				$mitarbeiter->vornamen = $_POST['vornamen'];
				$mitarbeiter->uid = $_POST['uid'];
				$mitarbeiter->svnr = $_POST['svnr'];
				$mitarbeiter->ersatzkennzeichen = $_POST['ersatzkennzeichen'];
				$mitarbeiter->gebort = $_POST['geburtsort'];
				if($_POST['geburtsdatum']!='')
					$mitarbeiter->gebdat = convertdate($_POST['geburtsdatum']);
				else
					$mitarbeiter->gebdat = '';
				$mitarbeiter->bemerkung = $_POST['bemerkung'];
				$mitarbeiter->anzahlderkinder = $_POST['anzahlderkinder'];
				$mitarbeiter->geschlecht = $_POST['geschlecht'];
				$mitarbeiter->bismelden = ($_POST['bismelden']=='true'?true:false);
				$mitarbeiter->familienstand = $_POST['familienstand'];
				$mitarbeiter->staatsbuergerschaft = $_POST['staatsbuergerschaft'];
				$mitarbeiter->gebnation = $_POST['geburtsnation'];
				$mitarbeiter->persnr = $_POST['personal_nr'];
				$mitarbeiter->kurzbez = $_POST['kurzbezeichnung'];
				if($_POST['beginndatum']!='')
					$mitarbeiter->beginndatum = convertdate($_POST['beginndatum']);
				else
					$mitarbeiter->beginndatum = '';
				$mitarbeiter->stundensatz = $_POST['stundensatz'];
				$mitarbeiter->habilitation = ($_POST['habilitation']=='true'?true:false);
				$mitarbeiter->ausgeschieden = ($_POST['ausgeschieden']=='true'?true:false);
				if($_POST['beendigungsdatum']!='')
					$mitarbeiter->beendigungsdatum = convertdate($_POST['beendigungsdatum']);
				else
					$mitarbeiter->beendigungsdatum = '';
				$mitarbeiter->ausbildung = $_POST['ausbildung'];
				$mitarbeiter->aktstatus = $_POST['aktstatus'];
				$mitarbeiter->aktiv = $_POST['aktiv'];
				$mitarbeiter->updatevon = $benutzer->variable->fas_id;

				if($mitarbeiter->save()) //Datensatz speichern
				{
					$return = 'true';
					$errormsg = 'Datensatz erfolgreich gespeichert';
				}
				else
				{
					$return = 'false';
					$errormsg = $mitarbeiter->errormsg;
				}
			}
			else
			{
				$return = 'false';
				$errormsg = 'Sie haben keine Berechtigung zum Speichern';
			}
		}
		elseif(isset($_POST['type']) && $_POST['type']=='delmitarbeiter') //Person und Mitarbeiter loeschen
		{
			/**
			 * Beim loeschen wird eine variable Anzahl an IDs uebergeben die Anzahl wird
			 * in $_POST['anz'] gespeichert die einzelnen IDS heissen dann x1, x2, x3, ...
			 */
			if($rechte->isBerechtigt('admin', 0, 'd')
		    || $rechte->isBerechtigt('mitarbeiter', 0, 'd'))
			{
				$errormsg = '';
				$mitarbeiter = new mitarbeiter($conn_fas);
				$mitarbeiter->updatevon = $benutzer->variable->fas_id;
				for($i=0;$i<$_POST['anz'];$i++)
				{
					if(!$mitarbeiter->delete($_POST['x'.$i]))
					{
						$var = 'x'.$i;
						$errormsg .= "\n\rFehler beim loeschen des Datensatzes mit der ID ".$_POST[$var]." Meldung: ".$mitarbeiter->errormsg;
						$return = 'false';
					}
				}
				if($errormsg=='')
				{
					$return = 'true';
					$errormsg = 'Datensatz erfolgreich gespeichert';
				}
			}
			else
			{
				$return = 'false';
				$errormsg = 'Sie haben keine Berechtigung zum LÃ¶schen';
			}
		}
		elseif(isset($_POST['type']) && $_POST['type']=='newmitarbeiter') //Neuen,leeren Mitarbeiterdatensatz anlegen
		{
			if($rechte->isBerechtigt('admin', 0, 'i')
		    || $rechte->isBerechtigt('mitarbeiter', 0, 'i'))
			{
				$mitarbeiter = new mitarbeiter($conn_fas);
				$mitarbeiter->new=true;
				$mitarbeiter->aktstatus=100;
				$mitarbeiter->aktiv=true;
				$mitarbeiter->staatsbuergerschaft ='A';
				$mitarbeiter->gebnation = 'A';
				$mitarbeiter->bismelden = true;
				$mitarbeiter->ausbildung = 1;
				$mitarbeiter->svnr = '0000000000';
				$mitarbeiter->updatevon = $benutzer->variable->fas_id;
				$mitarbeiter->persnr = $mitarbeiter->getNextPersonalnr();

				if($mitarbeiter->save())
				{
					$return = 'true';
					$errormsg = $mitarbeiter->mitarbeiter_id;
					//Funktion anlegen falls noetig
					if(isset($_POST['art']) && $_POST['art']=='fix') //Fixangestellt - Echter Dienstvertrag
					{
						$fkt_obj = new funktion($conn_fas);
						$fkt_obj->new=true;
						$fkt_obj->mitarbeiter_id = $mitarbeiter->mitarbeiter_id;
						$fkt_obj->studiensemester_id = $_POST['studiensemester_id'];
						$fkt_obj->erhalter_id = 1;
						$fkt_obj->studiengang_id = null;
						$fkt_obj->fachbereich_id = null;
						$fkt_obj->name = null;
						$fkt_obj->funktion = null;
						$fkt_obj->beschart1 = 3;
						$fkt_obj->beschart2 = null;
						$fkt_obj->verwendung = null;
						$fkt_obj->hauptberuf = null;
						$fkt_obj->hauptberuflich = true;
						$fkt_obj->entwicklungsteam = false;
						$fkt_obj->besonderequalifikation = 0;
						$fkt_obj->ausmass = 0;
						$fkt_obj->updatevon = $benutzer->variable->fas_id;
						if($fkt_obj->save())
						{
							$return = 'true';
							$errormsg = $mitarbeiter->mitarbeiter_id;
						}
						else
						{
							$return = 'false';
							$errormsg = 'funktion konnte nicht angelegt werden:'.$fkt_obj->errormsg;
						}
					}
					elseif(isset($_POST['art']) && $_POST['art']=='frei') //Freier Mitarbeiter - Freier Dienstvertrag
					{
						$fkt_obj = new funktion($conn_fas);
						$fkt_obj->new=true;
						$fkt_obj->mitarbeiter_id = $mitarbeiter->mitarbeiter_id;
						$fkt_obj->studiensemester_id = $_POST['studiensemester_id'];
						$fkt_obj->erhalter_id = 1;
						$fkt_obj->studiengang_id = null;
						$fkt_obj->fachbereich_id = null;
						$fkt_obj->name = null;
						$fkt_obj->funktion = null;
						$fkt_obj->beschart1 = 4;
						$fkt_obj->beschart2 = null;
						$fkt_obj->verwendung = null;
						$fkt_obj->hauptberuf = null;
						$fkt_obj->hauptberuflich = true;
						$fkt_obj->entwicklungsteam = false;
						$fkt_obj->besonderequalifikation = 0;
						$fkt_obj->ausmass = 0;
						$fkt_obj->updatevon = $benutzer->variable->fas_id;
						if($fkt_obj->save())
						{
							$return = 'true';
							$errormsg = $mitarbeiter->mitarbeiter_id;
						}
						else
						{
							$return = 'false';
							$errormsg = 'funktion konnte nicht angelegt werden:'.$fkt_obj->errormsg;
						}
					}

				}
				else
				{
					$return = 'false';
					$errormsg = 'Datensatz konnte nicht angelegt werden: '.$mitarbeiter->errormsg;
				}
			}
			else
			{
				$return = 'false';
				$errormsg = 'Sie haben keine Berechtigung zum einfuegen';
			}
		}
		elseif(isset($_POST['type']) && $_POST['type']=='funktion') /***********FUNKTIONEN***********/
		{
			//Speichert eine Funktion
			if($rechte->isBerechtigt('admin', 0, 'i')
		    || $rechte->isBerechtigt('mitarbeiter', 0, 'i'))
			{
				//Parameter holen und zuweisen
				$funktion_obj = new funktion($conn_fas);
				$funktion_obj->new = ($_POST['new']=='true'?true:false);
				$funktion_obj->funktion_id = $_POST['funktion_id'];
				$funktion_obj->mitarbeiter_id = $_POST['mitarbeiter_id'];
				$funktion_obj->studiensemester_id = $_POST['studiensemester_id'];
				$funktion_obj->erhalter_id = $_POST['erhalter_id'];
				$funktion_obj->studiengang_id = $_POST['studiengang_id'];
				$funktion_obj->fachbereich_id = $_POST['fachbereich_id'];
				$funktion_obj->name = $_POST['name'];
				$funktion_obj->funktion = $_POST['funktion'];
				$funktion_obj->beschart1 = $_POST['beschart1'];
				$funktion_obj->beschart2 = $_POST['beschart2'];
				$funktion_obj->verwendung = $_POST['verwendung'];
				$funktion_obj->hauptberuf = $_POST['hauptberuf'];
				$funktion_obj->hauptberuflich = ($_POST['hauptberuflich']=='true'?true:false);
				$funktion_obj->entwicklungsteam = ($_POST['entwicklungsteam']=='true'?true:false);
				$funktion_obj->besonderequalifikation = $_POST['qualifikation'];
				$funktion_obj->ausmass = $_POST['ausmass'];
				$funktion_obj->updatevon = $benutzer->variable->fas_id;

				if($funktion_obj->save()) //Funktion Speichern
				{
					$return = 'true';
					$errormsg = $funktion_obj->status; // aktstatus der Person nach dem Speichern
				}
				else
				{
					$return = 'false';
					$errormsg = $funktion_obj->errormsg;
				}
			}
			else
			{
				$return = 'false';
				$errormsg = 'Sie haben keine Berechtigung zum einfuegen';
			}
		}
		elseif(isset($_POST['type']) && $_POST['type']=='delfunktion')
		{
			/**
			 * Beim loeschen wird eine variable Anzahl an IDs uebergeben die Anzahl wird
			 * in $_POST['anz'] gespeichert die einzelnen IDS heissen dann x1, x2, x3, ...
			 */
			if($rechte->isBerechtigt('admin', 0, 'd')
		    || $rechte->isBerechtigt('mitarbeiter', 0, 'd'))
			{
				$errormsg = '';
				$funktion_obj = new funktion($conn_fas);
				$funktion_obj->updatevon = $benutzer->variable->fas_id;
				for($i=0;$i<$_POST['anz'];$i++)
				{
					if(!$funktion_obj->delete($_POST['x'.$i]))
					{
						$var = 'x'.$i;
						$errormsg .= "\n\rFehler beim loeschen des Datensatzes mit der ID ".$_POST[$var]." Meldung: ".$funktion_obj->errormsg;
						$return = 'false';
					}
				}
				if($errormsg=='')
				{
					$return = 'true';
					$errormsg = $funktion_obj->status; //aktstatus der Person nach dem loeschen
				}
			}
			else
			{
				$return = 'false';
				$errormsg = 'Sie haben keine Berechtigung zum Löschen';
			}
		}
		elseif(isset($_POST['type']) && $_POST['type']=='adresse') /***********Adressen***********/
		{
			//Speichern eines Adressdatensatzes
			if($rechte->isBerechtigt('admin', 0, 'i')
		    || $rechte->isBerechtigt('mitarbeiter', 0, 'i'))
			{
				$adresse = new adresse($conn_fas);
				$adresse->adresse_id = $_POST['adress_id'];
				$adresse->person_id = $_POST['person_id'];
				$adresse->typ = $_POST['adresstyp'];
				$adresse->name = $_POST['name'];
				$adresse->nation = $_POST['nation'];
				$adresse->new = ($_POST['new']=='true'?true:false);
				$adresse->strasse = $_POST['strasse'];
				$adresse->plz = $_POST['plz'];
				$adresse->ort = $_POST['ort'];
				$adresse->gemeinde = $_POST['gemeinde'];
				$adresse->bismeldeadresse = ($_POST['bismeldeadresse']=='true'?true:false);
				$adresse->zustelladresse = ($_POST['zustelladresse']=='true'?true:false);
				$adresse->updatevon = $benutzer->variable->fas_id;

				if($adresse->save())
				{
					$return = 'true';
					$errormsg = 'Datensatz wurde erfolgreich gespeichert';
				}
				else
				{
					$return = 'false';
					$errormsg = $adresse->errormsg;
				}
			}
			else
			{
				$return = 'false';
				$errormsg = 'Sie haben keine Berechtigung zum einfuegen';
			}
		}
		elseif(isset($_POST['type']) && $_POST['type']=='deladresse') //Loescht Adressen
		{
			/**
			 * Beim loeschen wird eine variable Anzahl an IDs uebergeben die Anzahl wird
			 * in $_POST['anz'] gespeichert die einzelnen IDS heissen dann x1, x2, x3, ...
			 */
			if($rechte->isBerechtigt('admin', 0, 'd')
		    || $rechte->isBerechtigt('mitarbeiter', 0, 'd'))
			{
				$errormsg = '';
				$adresse = new adresse($conn_fas);
				$adresse->updatevon = $benutzer->variable->fas_id;
				for($i=0;$i<$_POST['anz'];$i++)
				{
					if(!$adresse->delete($_POST['x'.$i]))
					{
						$var = 'x'.$i;
						$errormsg .= "\n\rFehler beim loeschen des Datensatzes mit der ID ".$_POST[$var]." Meldung: ".$adresse->errormsg;
						$return = 'false';
					}
				}
				if($errormsg=='')
				{
					$return = 'true';
					$errormsg = 'Datensatz erfolgreich gespeichert';
				}
			}
			else
			{
				$return = 'false';
				$errormsg = 'Sie haben keine Berechtigung zum LÃ¶schen';
			}
		}
		elseif(isset($_POST['type']) && $_POST['type']=='email') /***********EMAIL***********/
		{
			//Speichert eine Email
			if($rechte->isBerechtigt('admin', 0, 'i')
		    || $rechte->isBerechtigt('mitarbeiter', 0, 'i'))
			{
				$email = new email($conn_fas);
				$email->email_id = $_POST['email_id'];
				$email->person_id = $_POST['person_id'];
				$email->name = $_POST['name'];
				$email->email = $_POST['email'];
				$email->typ = $_POST['typ'];
				$email->new = ($_POST['new']=='true'?true:false);
				$email->zustelladresse = ($_POST['zustelladresse']=='true'?true:false);
				$email->updatevon = $benutzer->variable->fas_id;

				if($email->save())
				{
					$return = 'true';
					$errormsg = 'Datensatz wurde erfolgreich gespeichert';
				}
				else
				{
					$return = 'false';
					$errormsg = $email->errormsg;
				}
			}
			else
			{
				$return = 'false';
				$errormsg = 'Sie haben keine Berechtigung zum einfuegen';
			}
		}
		elseif(isset($_POST['type']) && $_POST['type']=='delemail') //Loescht Emails
		{
			/**
			 * Beim loeschen wird eine variable Anzahl an IDs uebergeben die Anzahl wird
			 * in $_POST['anz'] gespeichert die einzelnen IDS heissen dann x1, x2, x3, ...
			 */
			if($rechte->isBerechtigt('admin', 0, 'd')
		    || $rechte->isBerechtigt('mitarbeiter', 0, 'd'))
			{
				$errormsg = '';
				$email = new email($conn_fas);
				$email->updatevon = $benutzer->variable->fas_id;
				for($i=0;$i<$_POST['anz'];$i++)
				{
					if(!$email->delete($_POST['x'.$i]))
					{
						$var = 'x'.$i;
						$errormsg .= "\n\rFehler beim loeschen des Datensatzes mit der ID ".$_POST[$var]." Meldung: ".$email->errormsg;
						$return = 'false';
					}
				}
				if($errormsg=='')
				{
					$return = 'true';
					$errormsg = 'Datensatz erfolgreich gespeichert';
				}
			}
			else
			{
				$return = 'false';
				$errormsg = 'Sie haben keine Berechtigung zum Loeschen';
			}
		}
		elseif(isset($_POST['type']) && $_POST['type']=='telefonnummer') /***********TELEFONNUMMER***********/
		{
			//Speichert eine Telefonnummer
			if($rechte->isBerechtigt('admin', 0, 'i')
		    || $rechte->isBerechtigt('mitarbeiter', 0, 'i'))
			{
				$telefon = new telefonnummer($conn_fas);
				$telefon->telefonnummer_id = $_POST['telefonnummer_id'];
				$telefon->person_id = $_POST['person_id'];
				$telefon->name = $_POST['name'];
				$telefon->nummer = $_POST['nummer'];
				$telefon->typ = $_POST['typ'];
				$telefon->new = ($_POST['new']=='true'?true:false);
				$telefon->updatevon = $benutzer->variable->fas_id;

				if($telefon->save())
				{
					$return = 'true';
					$errormsg = 'Datensatz wurde erfolgreich gespeichert';
				}
				else
				{
					$return = 'false';
					$errormsg = $telefon->errormsg;
				}
			}
			else
			{
				$return = 'false';
				$errormsg = 'Sie haben keine Berechtigung zum einfuegen';
			}
		}
		elseif(isset($_POST['type']) && $_POST['type']=='deltelefonnummer')
		{
			/**
			 * Beim loeschen wird eine variable Anzahl an IDs uebergeben die Anzahl wird
			 * in $_POST['anz'] gespeichert die einzelnen IDS heissen dann x1, x2, x3, ...
			 */
			if($rechte->isBerechtigt('admin', 0, 'd')
		    || $rechte->isBerechtigt('mitarbeiter', 0, 'd'))
			{
				$errormsg = '';
				$telefon = new telefonnummer($conn_fas);
				$telefon->updatevon = $benutzer->variable->fas_id;
				for($i=0;$i<$_POST['anz'];$i++)
				{
					if(!$telefon->delete($_POST['x'.$i]))
					{
						$var = 'x'.$i;
						$errormsg .= "\n\rFehler beim loeschen des Datensatzes mit der ID ".$_POST[$var]." Meldung: ".$telefon->errormsg;
						$return = 'false';
					}
				}
				if($errormsg=='')
				{
					$return = 'true';
					$errormsg = 'Datensatz erfolgreich gespeichert';
				}
			}
			else
			{
				$return = 'false';
				$errormsg = 'Sie haben keine Berechtigung zum LÃ¶schen';
			}
		}
		elseif(isset($_POST['type']) && $_POST['type']=='bankverbindung') /***********BANKVERBINDUNG***********/
		{
			//Speichert eine Bankverbindung
			if($rechte->isBerechtigt('admin', 0, 'i')
		    || $rechte->isBerechtigt('mitarbeiter', 0, 'i'))
			{
				$bankverbindung = new bankverbindung($conn_fas);
				$bankverbindung->bankverbindung_id = $_POST['bankverbindung_id'];
				$bankverbindung->person_id = $_POST['person_id'];
				$bankverbindung->name = $_POST['name'];
				$bankverbindung->anschrift = $_POST['anschrift'];
				$bankverbindung->blz = $_POST['blz'];
				$bankverbindung->bic = $_POST['bic'];
				$bankverbindung->kontonr = $_POST['kontonr'];
				$bankverbindung->iban = $_POST['iban'];
				$typ = ($_POST['verrechnungskonto']=='true'?10:0) + $_POST['typ'];
				$bankverbindung->typ = $typ;
				$bankverbindung->new = ($_POST['new']=='true'?true:false);
				$bankverbindung->updatevon = $benutzer->variable->fas_id;

				if($bankverbindung->save())
				{
					$return = 'true';
					$errormsg = 'Datensatz wurde erfolgreich gespeichert';
				}
				else
				{
					$return = 'false';
					$errormsg = $bankverbindung->errormsg;
				}
			}
			else
			{
				$return = 'false';
				$errormsg = 'Sie haben keine Berechtigung zum einfuegen';
			}
		}
		elseif(isset($_POST['type']) && $_POST['type']=='delbankverbindung')
		{
			/**
			 * Beim loeschen wird eine variable Anzahl an IDs uebergeben die Anzahl wird
			 * in $_POST['anz'] gespeichert die einzelnen IDS heissen dann x1, x2, x3, ...
			 */
			if($rechte->isBerechtigt('admin', 0, 'd')
		    || $rechte->isBerechtigt('mitarbeiter', 0, 'd'))
			{
				$errormsg = '';
				$bankverbindung = new bankverbindung($conn_fas);
				$bankverbindung->updatevon = $benutzer->variable->fas_id;
				for($i=0;$i<$_POST['anz'];$i++)
				{
					if(!$bankverbindung->delete($_POST['x'.$i]))
					{
						$var = 'x'.$i;
						$errormsg .= "\n\rFehler beim loeschen des Datensatzes mit der ID ".$_POST[$var]." Meldung: ".$bankverbindung->errormsg;
						$return = 'false';
					}
				}
				if($errormsg=='')
				{
					$return = 'true';
					$errormsg = 'Datensatz erfolgreich gespeichert';
				}
			}
			else
			{
				$return = 'false';
				$errormsg = 'Sie haben keine Berechtigung zum Löschen';
			}
		}
		elseif(isset($_POST['type']) && $_POST['type']=='lva_save') /*********** LEHRVERANSTALTUNGEN ***********/
		{
			/**
			 * Speichert eine Lehreinheit
			 */

			if(isset($_POST['studiengang_id']) && is_numeric($_POST['studiengang_id']))
			{
				$qry = "SELECT kennzahl FROM studiengang WHERE studiengang_pk=".$_POST['studiengang_id'];
				if($row=pg_fetch_object(pg_query($conn_fas,$qry)))
				{
					$studiengang = $row->kennzahl;
					//Ueberpruefen der Berechtigung fuer diesen Studiengang
					if($rechte->isBerechtigt('admin', $studiengang, 'u')
				    || $rechte->isBerechtigt('lva-verwaltung', $studiengang, 'u'))
					{
						$lva = new lehreinheit($conn_fas);
						//Werte zuweisen
						$lva->new=false;
						$lva->lehreinheit_id = (isset($_POST['lehreinheit_id'])?urldecode($_POST['lehreinheit_id']):'');
						$lva->studiengang_id = (isset($_POST['studiengang_id'])?urldecode($_POST['studiengang_id']):'');
						$lva->studiensemester_id = (isset($_POST['studiensemester_id'])?urldecode($_POST['studiensemester_id']):'');
						$lva->lehrveranstaltung_id = (isset($_POST['lehrveranstaltung_id'])?urldecode($_POST['lehrveranstaltung_id']):'');
						$lva->fachbereich_id = (isset($_POST['fachbereich_id'])?urldecode($_POST['fachbereich_id']):'');
						$lva->ausbildungssemester_id = (isset($_POST['ausbildungssemester_id'])?urldecode($_POST['ausbildungssemester_id']):'');
						$lva->lehreinheit_fk = (isset($_POST['lehreinheit_fk'])?urldecode($_POST['lehreinheit_fk']):'');
						$lva->lehrform_id = (isset($_POST['lehrform_id'])?urldecode($_POST['lehrform_id']):'');
						$lva->gruppe_id = (isset($_POST['gruppe_id'])?urldecode($_POST['gruppe_id']):'');
						$lva->nummer = (isset($_POST['nummer'])?urldecode($_POST['nummer']):'');
						$lva->bezeichnung = (isset($_POST['bezeichnung'])?urldecode($_POST['bezeichnung']):'');
						$lva->kurzbezeichnung = (isset($_POST['kurzbezeichnung'])?urldecode($_POST['kurzbezeichnung']):'');
						$lva->semesterwochenstunden = (isset($_POST['semesterwochenstunden'])?urldecode($_POST['semesterwochenstunden']):'');
						$lva->gesamtstunden = (isset($_POST['gesamtstunden'])?urldecode($_POST['gesamtstunden']):'');
						$lva->wochenrythmus = (isset($_POST['wochenrythmus'])?urldecode($_POST['wochenrythmus']):'');
						$lva->start_kw = (isset($_POST['kalenderwoche'])?urldecode($_POST['kalenderwoche']):'');
						$lva->stundenblockung = (isset($_POST['stundenblockung'])?urldecode($_POST['stundenblockung']):'');
						$lva->koordinator_id = (isset($_POST['koordinator_id'])?urldecode($_POST['koordinator_id']):'');
						$lva->plankostenprolektor = (isset($_POST['plankostenprolektor'])?urldecode($_POST['plankostenprolektor']):'');
						$lva->planfaktor = (isset($_POST['planfaktor'])?urldecode($_POST['planfaktor']):'');
						$lva->planlektoren = (isset($_POST['planlektoren'])?urldecode($_POST['planlektoren']):'');
						$lva->raumtyp_id = (isset($_POST['raumtyp_id'])?urldecode($_POST['raumtyp_id']):'');
						$lva->raumtypalternativ_id = (isset($_POST['raumtypalternativ_id'])?urldecode($_POST['raumtypalternativ_id']):'');
						$lva->bemerkungen = (isset($_POST['bemerkungen'])?urldecode($_POST['bemerkungen']):'');

						//Speichern
						if($lva->save())
						{
							$return = 'true';
							$errormsg = 'Datensatz erfolgreich gespeichert';
						}
						else
						{
							$return = 'false';
							$errormsg = $lva->errormsg;
						}
					}
					else
					{
						$return = 'false';
						$errormsg = 'Sie haben keine Berechtigung um diesen Datensatz zu ändern';
					}
				}
				else
				{
					$return = 'false';
					$errormsg = 'Studiengang konnte nicht ermittelt werden';
				}
			}
			else
			{
				$return = 'false';
				$errormsg = 'Fehlerhafte Parameteruebergabe';
			}
		}
		elseif(isset($_POST['type']) && $_POST['type']=='lva_delete')
		{
			/**
			 * Loescht eine Lehreinheit
			 */

			if(isset($_POST['lehreinheit_id']) && is_numeric($_POST['lehreinheit_id']))
			{
				$qry = "SELECT kennzahl FROM studiengang WHERE studiengang_pk = (SELECT studiengang_fk FROM lehreinheit WHERE lehreinheit_pk='".$_POST['lehreinheit_id']."')";
				if($row=pg_fetch_object(pg_query($conn_fas,$qry)))
				{
					$studiengang = $row->kennzahl;
					//Ueberpruefen der Berechtigung fuer diesen Studiengang
					if($rechte->isBerechtigt('admin', $studiengang, 'd')
				    || $rechte->isBerechtigt('lva-verwaltung', $studiengang, 'd'))
					{
						$lva = new lehreinheit($conn_fas);

						//Loeschen
						if($lva->delete($_POST['lehreinheit_id']))
						{
							$return = 'true';
							$errormsg = 'Datensatz erfolgreich gespeichert';
						}
						else
						{
							$return = 'false';
							$errormsg = $lva->errormsg;
						}
					}
					else
					{
						$return = 'false';
						$errormsg = 'Sie haben keine Berechtigung um diesen Datensatz zu loeschen';
					}
				}
				else
				{
					$return = 'false';
					$errormsg = 'Studiengang konnte nicht ermittelt werden';
				}
			}
			else
			{
				$return = 'false';
				$errormsg = 'Fehlerhafte Parameteruebergabe';
			}
		}
		elseif(isset($_POST['type']) && $_POST['type']=='lva_neu')
		{
			/**
			 * Loescht eine Lehreinheit
			 */

			if(isset($_POST['lehrveranstaltung_id']) && is_numeric($_POST['lehrveranstaltung_id']))
			{
				$qry = "SELECT kennzahl FROM studiengang WHERE studiengang_pk = (SELECT studiengang_fk FROM lehrveranstaltung WHERE lehrveranstaltung_pk='".$_POST['lehrveranstaltung_id']."')";
				if($row=pg_fetch_object(pg_query($conn_fas,$qry)))
				{
					$studiengang = $row->kennzahl;
					//Ueberpruefen der Berechtigung fuer diesen Studiengang
					if($rechte->isBerechtigt('admin', $studiengang, 'i')
				    || $rechte->isBerechtigt('lva-verwaltung', $studiengang, 'i'))
					{
						//LVA Laden
						$lva = new lehrveranstaltung($conn_fas);
						$lva->load($_POST['lehrveranstaltung_id']);

						//Daten Übernehmen
						$lehreinheit = new lehreinheit($conn_fas);
						$lehreinheit->new=true;
						$lehreinheit->lehrveranstaltung_id = $_POST['lehrveranstaltung_id'];
						$lehreinheit->studiengang_id = $lva->studiengang_id;
						$lehreinheit->fachbereich_id = $lva->fachbereich_id;
						$lehreinheit->ausbildungssemester_id = $lva->ausbildungssemester_id;
						$lehreinheit->kurzbezeichnung = $lva->kurzbezeichnung;
						$lehreinheit->bezeichnung = $lva->name;
						$lehreinheit->studiensemester_id = $lva->studiensemester_id;
						$lehreinheit->lehrform_id = 2;
						$lehreinheit->gesamtstunden = 0;
						$lehreinheit->faktor = 0;
						$lehreinheit->wochenrythmus = 1;
						$lehreinheit->start_kw  = 0;
						$lehreinheit->stundenblockung = 0;
						$lehreinheit->planlektoren = 1;

						$lehreinheit->updatevon = $benutzer->variable->fas_id;

						//Speichern
						if($lehreinheit->save())
						{
							$return = 'true';
							$errormsg = $lehreinheit->lehreinheit_id;
						}
						else
						{
							$return = 'false';
							$errormsg = $lehreinheit->errormsg;
						}
					}
					else
					{
						$return = 'false';
						$errormsg = 'Sie haben keine Berechtigung um diesen Datensatz zu loeschen';
					}
				}
				else
				{
					$return = 'false';
					$errormsg = 'Studiengang konnte nicht ermittelt werden';
				}
			}
			else
			{
				$return = 'false';
				$errormsg = 'Fehlerhafte Parameteruebergabe';
			}
		}
		elseif(isset($_POST['type']) && $_POST['type']=='lva_partizipierung')
		{
			/**
			 * Teilt eine Partizipierung zu
			 */

			if(isset($_POST['quell_lehreinheit_id']) && is_numeric($_POST['quell_lehreinheit_id'])
			&& isset($_POST['ziel_lehreinheit_id']) && is_numeric($_POST['ziel_lehreinheit_id']))
			{

				$qry = "SELECT kennzahl FROM studiengang WHERE studiengang_pk = (SELECT studiengang_fk FROM lehreinheit WHERE lehreinheit_pk='".$_POST['quell_lehreinheit_id']."')";
				if($row=pg_fetch_object(pg_query($conn_fas,$qry)))
				{
					$studiengang = $row->kennzahl;
					//Ueberpruefen der Berechtigung fuer diesen Studiengang
					if($rechte->isBerechtigt('admin', $studiengang, 'u')
				    || $rechte->isBerechtigt('lva-verwaltung', $studiengang, 'u'))
					{
						$lva = new lehreinheit($conn_fas);

						if($lva->setPartizipierung($_POST['quell_lehreinheit_id'], $_POST['ziel_lehreinheit_id']))
						{
							$return = 'true';
							$errormsg = 'Datensatz erfolgreich gespeichert';
						}
						else
						{
							$return = 'false';
							$errormsg = $lva->errormsg;
						}
					}
					else
					{
						$return = 'false';
						$errormsg = 'Sie haben keine Berechtigung und diese Aktion durchzufuehren';
					}
				}
				else
				{
					$return = 'false';
					$errormsg = 'Studiengang konnte nicht ermittelt werden';
				}
			}
			else
			{
				$return = 'false';
				$errormsg = 'Fehlerhafte Parameteruebergabe';
			}
		}
		elseif(isset($_POST['type']) && $_POST['type']=='lva_mitarbeiter_lehreinheit_zuteilung')
		{
			/**
			 * Speichert die Zuteilung eines Mitarbeiters zu einer Lehreinheit
			 */

			//Ermitteln des Studienganges zu dem diese Zuteilung gehoert
			if(isset($_POST['mitarbeiter_lehreinheit_id']) && is_numeric($_POST['mitarbeiter_lehreinheit_id']))
			{
				$qry = "SELECT kennzahl FROM studiengang WHERE studiengang_pk = (SELECT studiengang_fk FROM mitarbeiter_lehreinheit JOIN lehreinheit ON (mitarbeiter_lehreinheit.lehreinheit_fk=lehreinheit_pk) WHERE mitarbeiter_lehreinheit_pk='".$_POST['mitarbeiter_lehreinheit_id']."')";
				if($row=pg_fetch_object(pg_query($conn_fas,$qry)))
				{
					$studiengang = $row->kennzahl;
					//Ueberpruefen der Berechtigung fuer diesen Studiengang
					if($rechte->isBerechtigt('admin', $studiengang, 'u')
				    || $rechte->isBerechtigt('lva-verwaltung', $studiengang, 'u'))
					{
						$lva = new lehreinheit($conn_fas);
						//Werte zuweisen
						$lva->new=false;
						$lva->mitarbeiter_id = isset($_POST['mitarbeiter_id'])?$_POST['mitarbeiter_id']:'';
						$lva->faktor = isset($_POST['faktor'])?$_POST['faktor']:'';
						$lva->gesamtstunden_mitarbeiter = isset($_POST['gesamtstunden'])?$_POST['gesamtstunden']:'';
						$lva->kosten = isset($_POST['kosten'])?$_POST['kosten']:'';
						$lva->lehrfunktion_id = isset($_POST['lehrfunktion_id'])?$_POST['lehrfunktion_id']:'';
						$lva->lehreinheit_fk = isset($_POST['lehreinheit_id'])?$_POST['lehreinheit_id']:'';
						$lva->updatevon = $benutzer->variable->fas_id;
						$lva->mitarbeiter_lehreinheit_id = isset($_POST['mitarbeiter_lehreinheit_id'])?$_POST['mitarbeiter_lehreinheit_id']:'';

						//Speichern
						if($lva->save_zuteilung())
						{
							$return = 'true';
							$errormsg = 'Datensatz erfolgreich gespeichert';
						}
						else
						{
							$return = 'false';
							$errormsg = $lva->errormsg;
						}
					}
					else
					{
						$return = 'false';
						$errormsg = 'Sie haben keine Berechtigung um diesen Datensatz zu ändern';
					}
				}
				else
				{
					$return = 'false';
					$errormsg = 'Studiengang konnte nicht ermittelt werden'.$qry;
				}
			}
			else
			{
				$return = 'false';
				$errormsg = 'Fehlerhafte Parameteruebergabe';
			}
		}
		elseif(isset($_POST['type']) && $_POST['type']=='lva_mitarbeiter_lehreinheit_add')
		{
			/**
			 * Fuegt einen Dummy Lektor zu einer Lehreinheit hinzu
			 */

			//Ermitteln des Studienganges zu dem diese Zuteilung gehoert
			if(isset($_POST['lehreinheit_id']) && is_numeric($_POST['lehreinheit_id']))
			{
				$qry = "SELECT kennzahl FROM studiengang WHERE studiengang_pk = (SELECT studiengang_fk FROM lehreinheit WHERE lehreinheit_pk='".$_POST['lehreinheit_id']."')";
				if($row=pg_fetch_object(pg_query($conn_fas,$qry)))
				{
					$studiengang = $row->kennzahl;
					//Ueberpruefen der Berechtigung fuer diesen Studiengang
					if($rechte->isBerechtigt('admin', $studiengang, 'i')
				    || $rechte->isBerechtigt('lva-verwaltung', $studiengang, 'i'))
					{
						$lva = new lehreinheit($conn_fas);
						//Werte zuweisen
						$lva->new=true;
						$lva->mitarbeiter_id = 2701; //= Dr. Dieter Dummy
						$lva->faktor = 1;
						$lva->kosten = 0;
						$lva->gesamtstunden_mitarbeiter = 0;
						$lva->lehrfunktion_id = 2;
						$lva->lehreinheit_fk = isset($_POST['lehreinheit_id'])?$_POST['lehreinheit_id']:'';
						$lva->updatevon = $benutzer->variable->fas_id;

						//Speichern
						if($lva->save_zuteilung())
						{
							$return = 'true';
							$errormsg = $lva->mitarbeiter_lehreinheit_id;
						}
						else
						{
							$return = 'false';
							$errormsg = $lva->errormsg;
						}
					}
					else
					{
						$return = 'false';
						$errormsg = 'Sie haben keine Berechtigung um diesen Datensatz zu ändern';
					}
				}
				else
				{
					$return = 'false';
					$errormsg = 'Studiengang konnte nicht ermittelt werden';
				}
			}
			else
			{
				$return = 'false';
				$errormsg = 'Fehlerhafte Parameteruebergabe';
			}
		}
		elseif(isset($_POST['type']) && $_POST['type']=='lva_mitarbeiter_lehreinheit_del')
		{
			/**
			 * Loescht die zuteilung eines Lektors zu einer Lehrveranstaltung
			 */

			//Ermitteln des Studienganges zu dem diese Zuteilung gehoert
			if(isset($_POST['mitarbeiter_lehreinheit_id']) && is_numeric($_POST['mitarbeiter_lehreinheit_id']))
			{
				$qry = "SELECT kennzahl FROM studiengang WHERE studiengang_pk = (SELECT studiengang_fk FROM mitarbeiter_lehreinheit JOIN lehreinheit ON (mitarbeiter_lehreinheit.lehreinheit_fk=lehreinheit_pk) WHERE mitarbeiter_lehreinheit_pk='".$_POST['mitarbeiter_lehreinheit_id']."')";
				if($row=pg_fetch_object(pg_query($conn_fas,$qry)))
				{
					$studiengang = $row->kennzahl;
					//Ueberpruefen der Berechtigung fuer diesen Studiengang
					if($rechte->isBerechtigt('admin', $studiengang, 'u')
				    || $rechte->isBerechtigt('lva-verwaltung', $studiengang, 'u'))
					{
						$lva = new lehreinheit($conn_fas);

						//Loeschen des DS
						if($lva->delete_zuteilung($_POST['mitarbeiter_lehreinheit_id']))
						{
							$return = 'true';
							$errormsg = 'Datensatz erfolgreich gespeichert';
						}
						else
						{
							$return = 'false';
							$errormsg = $lva->errormsg;
						}
					}
					else
					{
						$return = 'false';
						$errormsg = 'Sie haben keine Berechtigung um diesen Datensatz zu ändern';
					}
				}
				else
				{
					$return = 'false';
					$errormsg = 'Studiengang konnte nicht ermittelt werden';
				}
			}
			else
			{
				$return = 'false';
				$errormsg = 'Fehlerhafte Parameteruebergabe';
			}
		}
		elseif(isset($_POST['type']) && $_POST['type']=='lva_mitarbeiter_lehreinheit_auswahladd')
		{
			/**
			 * Fuegt eine Funktion zu einem Mitarbeiter hinzu
			 */

			//Ermitteln des Studienganges zu dem diese Zuteilung gehoert
			if(isset($_POST['lehreinheit_id']) && is_numeric($_POST['lehreinheit_id'])
			&& isset($_POST['mitarbeiter_id']) && is_numeric($_POST['mitarbeiter_id']) )
			{
				$qry = "SELECT kennzahl, studiengang_pk, fachbereich_fk FROM studiengang JOIN lehreinheit ON (studiengang_fk=studiengang_pk) WHERE lehreinheit_pk = '".$_POST['lehreinheit_id']."'";
				if($row=pg_fetch_object(pg_query($conn_fas,$qry)))
				{
					$studiengang_kz = $row->kennzahl;
					$studiengang_id = $row->studiengang_pk;
					$fachbereich_id = $row->fachbereich_fk;
					$studiensemester_id = getStudiensemesterIdFromName($conn_fas, $benutzer->variable->semester_aktuell);
					//Ueberpruefen der Berechtigung fuer diesen Studiengang
					if($rechte->isBerechtigt('admin', $studiengang_kz, 'u')
				    || $rechte->isBerechtigt('lva-verwaltung', $studiengang_kz, 'u'))
					{
						$fkt = new funktion($conn_fas);
						//Nachschauen ob diese Funktion bereits existiert
						if($fkt->FunktionExists($_POST['mitarbeiter_id'], $studiengang_id, $fachbereich_id, $studiensemester_id, 1))
						{
							$return = 'false';
							$errormsg = 'Dieser Lektor befindet sich bereits in der Liste';
						}
						else
						{
							if($fkt->errormsg!='') //Falls ein Fehler aufgetreten ist
							{
								$return = 'false';
								$errormsg = $fkt->errormsg;
							}
							else
							{
								//Funktion anlegen
								$fkt->new = true;
								$fkt->mitarbeiter_id = $_POST['mitarbeiter_id'];
								$fkt->studiensemester_id = $studiensemester_id;
								$fkt->studiengang_id = $studiengang_id;
								$fkt->fachbereich_id = $fachbereich_id;
								$fkt->funktion = 1; //Lektor
								$fkt->erhalter_id = 1; //TW

								if($fkt->save())
								{
									$return = 'true';
									$errormsg = 'Datensatz erfolgreich angelegt';
								}
								else
								{
									$return = 'false';
									$errormsg = $fkt->errormsg;
								}

							}
						}
					}
					else
					{
						$return = 'false';
						$errormsg = 'Sie haben keine Berechtigung um diesen Datensatz zu ändern';
					}
				}
				else
				{
					$return = 'false';
					$errormsg = 'Studiengang konnte nicht ermittelt werden';
				}
			}
			else
			{
				$return = 'false';
				$errormsg = 'Fehlerhafte Parameteruebergabe';
			}
		}
		elseif(isset($_POST['type']) && $_POST['type']=='variablechange') /**********************SONSTIGES*****************/
		{
			/**
			 * Aendert die Variable Studiensemester
			 */
			if(isset($_POST['stsem']))
			{
				if($benutzer->setVariableStudiensemester($user,$_POST['stsem']))
				{
					$return = 'true';
					$errormsg = getStudiensemesterIdFromName($conn_fas, $_POST['stsem']);
				}
				else
				{
					$return = 'false';
					$errormsg = $benutzer->errormsg;
				}
			}
			else
			{
				$return = 'false';
				$errormsg = 'Falsche Paramenteruebergabe';
			}
		}
	}
?>
	<RDF:li>
    	<RDF:Description RDF:about="<?php echo $rdf_url.'/0' ?>" >
    		<DBDML:return><?php echo $return;  ?></DBDML:return>
        	<DBDML:errormsg><![CDATA[<?php echo $errormsg;  ?>]]></DBDML:errormsg>
        </RDF:Description>
	</RDF:li>
  </RDF:Seq>
</RDF:RDF>