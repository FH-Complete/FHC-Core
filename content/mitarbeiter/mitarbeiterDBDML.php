<?php
/* Copyright (C) 2006 Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
/**
 * Script zur Veränderung von Mitarbeiterdaten in der Datenbank
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../config/global.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/log.class.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/mitarbeiter.class.php');
require_once('../../include/bisverwendung.class.php');
require_once('../../include/bisfunktion.class.php');
require_once('../../include/entwicklungsteam.class.php');
require_once('../../include/buchung.class.php');
require_once('../../include/pruefung.class.php');
require_once('../../include/projektbetreuer.class.php');
require_once('../../include/vertrag.class.php');
require_once('../../include/lehreinheitmitarbeiter.class.php');
require_once('../../include/wawi_konto.class.php');
require_once('../../include/addon.class.php');

$user = get_uid();

$return = false;
$errormsg = 'unknown';
$data = '';
$error = false;

//Berechtigungen laden
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
if(!$rechte->isBerechtigt('admin', null, 'suid') && !$rechte->isBerechtigt('mitarbeiter', null, 'suid'))
{
	$return = false;
	$errormsg = 'Sie haben keine Berechtigung zum Speichern';
	$data = '';
	$error = true;
}

if(!$error)
{
	//in der Variable type wird die auszufuehrende Aktion mituebergeben
	if(isset($_POST['type']) && $_POST['type']=='mitarbeitersave')
	{
		//Speichert die Mitarbeiterdaten
		$mitarbeiter = new mitarbeiter();

		if($mitarbeiter->load($_POST['uid']))
		{
			//Werte zuweisen
			$mitarbeiter->anrede = $_POST['anrede'];
			$mitarbeiter->titelpre = $_POST['titelpre'];
			$mitarbeiter->titelpost = $_POST['titelpost'];
			$mitarbeiter->vorname = $_POST['vorname'];
			$mitarbeiter->wahlname = $_POST['wahlname'];
			$mitarbeiter->vornamen = $_POST['vornamen'];
			$mitarbeiter->nachname = $_POST['nachname'];
			$mitarbeiter->gebdatum = $_POST['geburtsdatum'];
			$mitarbeiter->gebort = $_POST['geburtsort'];
			$mitarbeiter->gebzeit = $_POST['geburtszeit'];
			$mitarbeiter->anmerkungen = $_POST['anmerkungen'];
			$mitarbeiter->homepage = $_POST['homepage'];
			$mitarbeiter->svnr = $_POST['svnr'];
			$mitarbeiter->ersatzkennzeichen = $_POST['ersatzkennzeichen'];
			$mitarbeiter->familienstand = $_POST['familienstand'];
			$mitarbeiter->geschlecht = $_POST['geschlecht'];
			$aktiv_alt = $mitarbeiter->bnaktiv;
			$mitarbeiter->bnaktiv = ($_POST['aktiv']=='true'?true:false);
			$mitarbeiter->anzahlkinder = $_POST['anzahlderkinder'];
			$mitarbeiter->staatsbuergerschaft = $_POST['staatsbuergerschaft'];
			$mitarbeiter->geburtsnation = $_POST['geburtsnation'];
			$mitarbeiter->sprache = $_POST['sprache'];
			$mitarbeiter->kurzbz = $_POST['kurzbezeichnung'];
			$mitarbeiter->stundensatz = $_POST['stundensatz'];
			$mitarbeiter->telefonklappe = $_POST['telefonklappe'];
			$mitarbeiter->lektor = ($_POST['lektor']=='true'?true:false);
			$mitarbeiter->fixangestellt = ($_POST['fixangestellt']=='true'?true:false);
			$mitarbeiter->bismelden = ($_POST['bismelden']=='true'?true:false);
			$mitarbeiter->ausbildungcode = $_POST['ausbildung'];
			$mitarbeiter->anmerkung = $_POST['anmerkung'];
			$mitarbeiter->ort_kurzbz = $_POST['ort_kurzbz'];
			$mitarbeiter->standort_id = $_POST['standort_id'];
			$mitarbeiter->alias = $_POST['alias'];
			$mitarbeiter->updateamum = date('Y-m-d H:i:s');
			$mitarbeiter->updatevon = $user;
			$mitarbeiter->kleriker = ($_POST['kleriker'] == 'true'?true:false);
			if($rechte->isBerechtigt('mitarbeiter/personalnummer'))
			{
				$mitarbeiter->personalnummer = $_POST['personalnummer'];
			}
			if($mitarbeiter->save())
			{
				$return = true;
			}
			else
			{
				$errormsg = $mitarbeiter->errormsg;
				$return = false;
			}
		}
		else
		{
			$errormsg = $mitarbeiter->errormsg;
			$return = false;
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='verwendungsave')
	{
		//Speichert die BISVerwendung
		$verwendung = new bisverwendung();

		if($_POST['neu']!='true')
		{
			if(!$verwendung->load($_POST['bisverwendung_id']))
			{
				$error = true;
				$return = false;
				$errormsg = $verwendung->errormsg;
			}
			$verwendung->new = false;
		}
		else
		{
			$verwendung->new = true;
			$verwendung->insertamum = date('Y-m-d H:i:s');
			$verwendung->insertvon = $user;
		}

		if(!$error)
		{
			$verwendung->ba1code = $_POST['ba1code'];
			$verwendung->ba2code = $_POST['ba2code'];
			$verwendung->beschausmasscode = $_POST['beschausmasscode'];
			$verwendung->verwendung_code = $_POST['verwendung_code'];
			$verwendung->mitarbeiter_uid = $_POST['mitarbeiter_uid'];
			$verwendung->hauptberufcode = $_POST['hauptberufcode'];
			if($_POST['hauptberuflich']=='true')
				$verwendung->hauptberuflich = true;
			elseif($_POST['hauptberuflich']=='false')
				$verwendung->hauptberuflich = false;
			else
				$verwendung->hauptberuflich = '';
			$verwendung->habilitation = ($_POST['habilitation']=='true'?true:false);
			$verwendung->beginn = $_POST['beginn'];
			$verwendung->ende = $_POST['ende'];
			$verwendung->vertragsstunden = str_replace(',','.',$_POST['vertragsstunden']);
			$verwendung->updateamum = date('Y-m-d H:i:s');
			$verwendung->updatevon = $user;
			$verwendung->dv_art = $_POST['dv_art'];
			$verwendung->inkludierte_lehre = $_POST['inkludierte_lehre'];
			if($_POST['zeitaufzeichnungspflichtig']=='true')
				$verwendung->zeitaufzeichnungspflichtig = true;
			elseif($_POST['zeitaufzeichnungspflichtig']=='false')
				$verwendung->zeitaufzeichnungspflichtig = false;
			else
				$verwendung->azgrelevant = '';
			if($_POST['azgrelevant']=='true')
				$verwendung->azgrelevant = true;
			elseif($_POST['azgrelevant']=='false')
				$verwendung->azgrelevant = false;
			else
				$verwendung->azgrelevant = '';

			if($_POST['homeoffice']=='true')
				$verwendung->homeoffice = true;
			elseif($_POST['homeoffice']=='false')
				$verwendung->homeoffice = false;
			else
				$verwendung->homeoffice = '';

			if($verwendung->save())
			{
				$return = true;
				$data = $verwendung->bisverwendung_id;
			}
			else
			{
				$errormsg = $verwendung->errormsg;
				$return = false;
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='verwendungdelete')
	{
		//Loescht die BISVerwendung
		$verwendung = new bisverwendung();
		if($verwendung->delete($_POST['bisverwendung_id']))
		{
			$return = true;
		}
		else
		{
			$return = false;
			$errormsg = $verwendung->errormsg;
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='funktionsave')
	{
		//Speichert die BISFunktion
		$funktion = new bisfunktion();

		if($_POST['neu']!='true')
		{
			if(!$funktion->load($_POST['bisverwendung_id'],$_POST['studiengang_kz_old']))
			{
				$error = true;
				$return = false;
				$errormsg = $funktion->errormsg;
			}
			$funktion->new = false;
		}
		else
		{
			$funktion->new = true;
			$funktion->insertamum = date('Y-m-d H:i:s');
			$funktion->insertvon = $user;
		}

		if(!$error)
		{
			$funktion->bisverwendung_id = $_POST['bisverwendung_id'];
			$funktion->studiengang_kz = $_POST['studiengang_kz'];
			$funktion->studiengang_kz_old = $_POST['studiengang_kz_old'];
			$funktion->sws = str_replace(',','.',$_POST['sws']);
			$funktion->updateamum = date('Y-m-d H:i:s');
			$funktion->updatevon = $user;

			if($funktion->save())
			{
				$return = true;
			}
			else
			{
				$errormsg = $funktion->errormsg;
				$return = false;
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='funktiondelete')
	{
		//Loescht die BISVerwendung
		$funktion = new bisfunktion();
		if($funktion->delete($_POST['bisverwendung_id'],$_POST['studiengang_kz']))
		{
			$return = true;
		}
		else
		{
			$return = false;
			$errormsg = $funktion->errormsg;
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='entwicklungsteamsave')
	{
		//Speichert den Entwicklungsteameintrag
		$entwt = new entwicklungsteam();

		if($_POST['neu']!='true')
		{
			if(!$entwt->load($_POST['mitarbeiter_uid'],$_POST['studiengang_kz_old']))
			{
				$error = true;
				$return = false;
				$errormsg = $entwt->errormsg;
			}
			$entwt->new = false;
		}
		else
		{

			if($entwt->exists($_POST['mitarbeiter_uid'],$_POST['studiengang_kz']))
			{
				$error = true;
				$errormsg = 'Es existiert bereits ein Eintrag fuer diesen Studiengang';
				$return = false;
			}
			$entwt->new = true;
			$entwt->insertamum = date('Y-m-d H:i:s');
			$entwt->insertvon = $user;
		}

		if(!$error)
		{
			$entwt->mitarbeiter_uid = $_POST['mitarbeiter_uid'];
			$entwt->studiengang_kz = $_POST['studiengang_kz'];
			$entwt->studiengang_kz_old = $_POST['studiengang_kz_old'];
			$entwt->besqualcode = $_POST['besqualcode'];
			$entwt->beginn = $_POST['beginn'];
			$entwt->ende = $_POST['ende'];
			$entwt->updateamum = date('Y-m-d H:i:s');
			$entwt->updatevon = $user;

			if($entwt->save())
			{
				$return = true;
			}
			else
			{
				$errormsg = $entwt->errormsg;
				$return = false;
			}
		}

	}
	elseif(isset($_POST['type']) && $_POST['type']=='entwicklungsteamdelete')
	{
		//Loescht einen Entwicklungsteameintrag
		$entwt = new entwicklungsteam();
		if($entwt->delete($_POST['mitarbeiter_uid'],$_POST['studiengang_kz']))
		{
			$return = true;
		}
		else
		{
			$return = false;
			$errormsg = $entwt->errormsg;
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='buchungsave')
	{
		if(!$rechte->isBerechtigt('buchung/mitarbeiter',null,'sui'))
		{
			$return = false;
			$errormsg = 'Sie haben keine Berechtigung für diesen Vorgang';
		}
		else
		{
			//Speichert die Buchungen eines Mitarbeiters
			$buchung = new buchung();

			$buchung->buchung_id = $_POST['buchung_id'];
			$buchung->buchungstyp_kurzbz = $_POST['buchungstyp_kurzbz'];
			$buchung->konto_id = $_POST['konto_id'];
			$buchung->kostenstelle_id = $_POST['kostenstelle_id'];
			$buchung->betrag = str_replace(',','.',$_POST['betrag']);
			$buchung->buchungstext = $_POST['buchungstext'];
			$buchung->buchungsdatum = $_POST['buchungsdatum'];
			if($buchung->buchung_id=='')
			{
				$buchung->new=true;
				$buchung->insertamum = date('Y-m-d H:i:s');
				$buchung->insertvon = $user;
			}
			else
			{
				$buchung->new=false;
				$buchung->updateamum = date('Y-m-d H:i:s');
				$buchung->updatevon = $user;
			}

			if($buchung->save())
			{
				$return = true;
			}
			else
			{
				$errormsg = $buchung->errormsg;
				$return = false;
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='buchungdelete')
	{
		if(!$rechte->isBerechtigt('buchung/mitarbeiter',null,'suid'))
		{
			$return = false;
			$errormsg = 'Sie haben keine Berechtigung für diesen Vorgang';
		}
		else
		{
			//Loescht eine Buchung
			$buchung = new buchung();
			if($buchung->delete($_POST['buchung_id']))
			{
				$return = true;
			}
			else
			{
				$return = false;
				$errormsg = $buchung->errormsg;
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='vertraggenerate')
	{
		if(!$rechte->isBerechtigt('vertrag/mitarbeiter',null,'suid'))
		{
			$return = false;
			$errormsg = 'Sie haben keine Berechtigung für diesen Vorgang';
		}
		else
		{
			$errormsg='';

			if(isset($_POST['person_id']))
				$person_id = $_POST['person_id'];
			elseif(isset($_POST['mitarbeiter_uid']))
			{
				$ma = new mitarbeiter();
				$ma->load($_POST['mitarbeiter_uid']);
				$person_id=$ma->person_id;
			}
			else
			{
				$return = false;
				$errormsg = 'Falsche Parameter';
			}

			$person = new person();
			$person->load($person_id);

			$vertrag = new vertrag();
			$neu = false;
			if(isset($_POST['vertrag_id']) && $_POST['vertrag_id']!='')
			{
				// Bearbeiten eines Vertrags
				$vertrag_id=$_POST['vertrag_id'];

				if($vertrag->load($vertrag_id))
				{
					$vertrag->updatevon = $user;
					$vertrag->updateamum = date('Y-m-d H:i:s');
				}
				else
				{
					$errormsg.=$vertrag->errormsg;
				}
			}
			else
			{
				// Neuen Vertrag erstellen
				$vertrag->person_id = $person_id;
				$vertrag->inservon = $user;
				$vertrag->insertamum = date('Y-m-d H:i:s');
				$neu = true;
			}

			$vertrag->vertragstyp_kurzbz=$_POST['vertragstyp_kurzbz'];
			$vertrag->betrag=str_replace(',','.',$_POST['betrag']);
			$vertrag->bezeichnung = $_POST['bezeichnung'];
			$vertrag->anmerkung = $_POST['anmerkung'];
			$vertrag->vertragsdatum = $_POST['vertragsdatum'];
			if(isset($_POST['lehrveranstaltung_id']))
				$vertrag->lehrveranstaltung_id = $_POST['lehrveranstaltung_id'];

			if($errormsg=='')
			{
				if($vertrag->save())
				{
					$vertrag_id = $vertrag->vertrag_id;

					// Vertragselemente zuordnen
					foreach($_POST as $key=>$value)
					{
						if(strstr($key, 'type_'))
						{
							$index = mb_substr($key,5);

							$type = $_POST['type_'.$index];
							$projektarbeit_id = $_POST['projektarbeit_id_'.$index];
							$betreuerart_kurzbz = $_POST['betreuerart_kurzbz_'.$index];
							$pruefung_id = $_POST['pruefung_id_'.$index];
							$lehreinheit_id = $_POST['lehreinheit_id_'.$index];
							$mitarbeiter_uid = $_POST['mitarbeiter_uid_'.$index];
							$stsem = $_POST['stsem_'.$index];
							switch($type)
							{
								case 'Lehrauftrag':
									$lehreinheitmitarbeiter = new lehreinheitmitarbeiter();
									if($lehreinheitmitarbeiter->load($lehreinheit_id, $mitarbeiter_uid))
									{
										$lehreinheitmitarbeiter->vertrag_id=$vertrag_id;
										if(!$lehreinheitmitarbeiter->save())
											$errormsg.=$lehreinheitmitarbeiter->errormsg;
									}
									else
										$errormsg.=$lehreinheitmitarbeiter->errormsg;

									break;
								case 'Pruefung':
									$pruefung = new pruefung();
									if($pruefung->load($pruefung_id))
									{
										$pruefung->vertrag_id=$vertrag_id;
										if(!$pruefung->save())
											$errormsg.=$pruefung->errormsg;
									}
									else
										$errormsg.=$pruefung->errormsg;
									break;
								case 'Betreuung':
									$projektbetreuer = new projektbetreuer();
									if($projektbetreuer->load($person_id, $projektarbeit_id, $betreuerart_kurzbz))
									{
										$projektbetreuer->vertrag_id=$vertrag_id;
										if(!$projektbetreuer->save())
											$errormsg.=$projektbetreuer->errormsg;
									}
									else
										$errormsg.=$projektbetreuer->errormsg;
									break;
								default:
									$errormsg.='Unknown type '.$type;
									break;
							}
						}
					}

					if($errormsg=='' && $neu)
					{
						// Neu Status setzen
						$vertrag = new vertrag();

						$vertrag->vertrag_id = $vertrag_id;
						$vertrag->vertragsstatus_kurzbz = 'neu';
						$vertrag->datum = date('Y-m-d H:i:s');
						$vertrag->uid = $user;

						if(!$vertrag->saveVertragsstatus(true))
							$errormsg.=$vertrag->errormsg;
                        else if($_POST['vertragstyp_kurzbz'] == 'Pruefungshonorar')
                        {
                            // Retour Status setzen
                            $vertrag->vertragsstatus_kurzbz = 'retour';
                            $vertrag->datum = date('Y-m-d H:i:s');

                            if(!$vertrag->saveVertragsstatus(true))
                                $errormsg.=$vertrag->errormsg;
                        }
					}

					if($errormsg=='')
						$return=true;
					else
						$return=false;

				}
				else
				{
					$return = false;
					$errormsg = $vertrag->errormsg;
				}
			}
			else
			{
				$return = false;
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='vertragsstatusadd')
	{
		if(!$rechte->isBerechtigt('vertrag/mitarbeiter',null,'suid'))
		{
			$return = false;
			$errormsg = 'Sie haben keine Berechtigung für diesen Vorgang';
		}
		else
		{
			$vertrag_id = $_POST['vertrag_id'];
			$status = $_POST['status'];

			$vertrag = new vertrag();

			$vertrag->vertrag_id = $vertrag_id;
			$vertrag->vertragsstatus_kurzbz = $status;
			$vertrag->datum = date('Y-m-d H:i:s');
			$vertrag->uid = $user;
			$vertrag->insertvon = $user;

			if($vertrag->saveVertragsstatus(true))
			{
				$return=true;
			}
			else
			{
				$return = false;
				$errormsg = $vertrag->errormsg;
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='vertragsstatusupdate')
	{
		if(!$rechte->isBerechtigt('vertrag/mitarbeiter',null,'suid'))
		{
			$return = false;
			$errormsg = 'Sie haben keine Berechtigung für diesen Vorgang';
		}
		else
		{
			$vertrag_id = filter_input(INPUT_POST, "vertrag_id");
			$status = filter_input(INPUT_POST, "status");
			$datum = filter_input(INPUT_POST, "datum");
			$time = date('H:i');
			$time = explode(":",$time);
			$datum = explode("-", $datum);
			$datum = date('Y-m-d H:i:s', mktime($time[0],$time[1],0,$datum[1],$datum[2],$datum[0]));

			$vertrag = new vertrag($vertrag_id);
			$vertrag->getStatus($vertrag_id,$status);

			$vertrag->datum = $datum;
			$vertrag->updatevon = $user;
			$vertrag->updateamum = date('Y-m-d H:i:s');

			if($vertrag->saveVertragsstatus(false))
			{
				$return=true;
			}
			else
			{
				$return = false;
				$errormsg = $vertrag->errormsg;
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='vertragsstatusdelete')
	{
		if(!$rechte->isBerechtigt('vertrag/mitarbeiter',null,'suid'))
		{
			$return = false;
			$errormsg = 'Sie haben keine Berechtigung für diesen Vorgang';
		}
		else
		{
			$vertrag_id = filter_input(INPUT_POST, "vertrag_id");
			$status = filter_input(INPUT_POST, "status");

			$vertrag = new vertrag();

			if($vertrag->deleteVertragsstatus($vertrag_id, $status))
			{
				$return=true;
			}
			else
			{
				$return = false;
				$errormsg = 'Failed'.$vertrag->errormsg;
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='vertragdelete')
	{
		if(!$rechte->isBerechtigt('vertrag/mitarbeiter',null,'suid'))
		{
			$return = false;
			$errormsg = 'Sie haben keine Berechtigung für diesen Vorgang';
		}
		else
		{

			$vertrag_id = filter_input(INPUT_POST, "vertrag_id");
			$vertrag = new vertrag();

			// Wenn das Abrechnungsaddon geladen ist dann pruefen ob dieser Vertrag bereits abgerechnet wurde
			$addons = new addon();
			if(in_array('abrechnung',$addons->aktive_addons))
			{
				require_once('../../addons/abrechnung/include/abrechnung.class.php');
				$abrechnung = new abrechnung();
				if($abrechnung->isTeilabgerechnet($vertrag_id))
				{
					$return =false;
					$error=true;
					$errormsg='Vertrag kann nicht gelöscht werden da er bereits abgerechnet wurde.';
				}
			}

			if(!$error)
			{
				if($vertrag->delete($vertrag_id))
				{
					$return=true;
				}
				else
				{
					$return = false;
					$errormsg = 'Failed'.$vertrag->errormsg;
				}
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='vertragsdetaildelete')
	{
		if(!$rechte->isBerechtigt('vertrag/mitarbeiter',null,'suid'))
		{
			$return = false;
			$errormsg = 'Sie haben keine Berechtigung für diesen Vorgang';
		}
		else
		{
			$errormsg='';

			$vertragstype = $_POST['vertragstype'];
			$projektarbeit_id = $_POST['projektarbeit_id'];
			$betreuerart_kurzbz = $_POST['betreuerart_kurzbz'];
			$pruefung_id = $_POST['pruefung_id'];
			$lehreinheit_id = $_POST['lehreinheit_id'];
			$mitarbeiter_uid = $_POST['mitarbeiter_uid'];
			$stsem = $_POST['stsem'];
			$vertrag_id = $_POST['vertrag_id'];
			$betrag = $_POST['betrag'];

			switch($vertragstype)
			{
				case 'Lehrauftrag':
					$lehreinheitmitarbeiter = new lehreinheitmitarbeiter();
					if($lehreinheitmitarbeiter->load($lehreinheit_id, $mitarbeiter_uid))
					{
						$lehreinheitmitarbeiter->vertrag_id='';
						if(!$lehreinheitmitarbeiter->save())
							$errormsg.=$lehreinheitmitarbeiter->errormsg;
					}
					else
						$errormsg.=$lehreinheitmitarbeiter->errormsg;

					break;
				case 'Pruefung':
					$pruefung = new pruefung();
					if($pruefung->load($pruefung_id))
					{
						$pruefung->vertrag_id='';
						if(!$pruefung->save())
							$errormsg.=$pruefung->errormsg;
					}
					else
						$errormsg.=$pruefung->errormsg;
					break;
				case 'Betreuung':
					$projektbetreuer = new projektbetreuer();
					if($projektbetreuer->load($person_id, $projektarbeit_id, $betreuerart_kurzbz))
					{
						$projektbetreuer->vertrag_id='';
						if(!$projektbetreuer->save())
							$errormsg.=$projektbetreuer->errormsg;
					}
					else
						$errormsg.=$projektbetreuer->errormsg;
					break;
				default:
					$errormsg.='Unknown type '.$vertragstype;
					break;
			}
			if($errormsg=='')
			{
				$vertrag = new vertrag();
				if($vertrag->load($vertrag_id))
				{
					$vertrag->betrag = $vertrag->betrag-$betrag;
					if($vertrag->save(false))
					{
						$return =true;
					}
					else
					{
						$errormsg.=$vertrag->errormsg;
						$return =false;
					}
				}
			}
			else
				$return = false;
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='kontosave')
	{
		// Legt ein neues Konto für den Mitarbeiter an
		$konto = new wawi_konto;
		$konto->new = true;
		$konto->aktiv = true;
		$konto->insertamum = date('Y-m-d H:i:s');
		$konto->insertvon = $user;
		$konto->beschreibung['German'] = $_POST['beschreibung'];
		$konto->kurzbz = $_POST['kurzbz'];
		$konto->person_id = isset($_POST['person_id']) ? $_POST['person_id'] : null;

		if (!$konto->save())
		{
			$error = true;
			$return = false;
			$errormsg = $konto->errormsg;
		}
		else
		{
			$error = false;
			$return = true;
			$errormsg = "";
		}
	}
	else
	{
		$return = false;
		$errormsg = 'Unkown type';
		$data = '';
	}
}

//RDF mit den Returnwerden ausgeben
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:NC="http://home.netscape.com/NC-rdf#"
	xmlns:DBDML="http://www.technikum-wien.at/dbdml/rdf#"
>
  <RDF:Seq RDF:about="http://www.technikum-wien.at/dbdml/msg">
	<RDF:li>
    	<RDF:Description RDF:about="http://www.technikum-wien.at/dbdml/0" >
    		<DBDML:return>'.($return?'true':'false').'</DBDML:return>
        	<DBDML:errormsg><![CDATA['.$errormsg.']]></DBDML:errormsg>
        	<DBDML:data><![CDATA['.$data.']]></DBDML:data>
        </RDF:Description>
	</RDF:li>
  </RDF:Seq>
</RDF:RDF>
';
?>
