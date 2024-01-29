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

// *********************************************
// * Datenbankschnittstelle fuer FAS und Tempus
// *********************************************

require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/log.class.php');
require_once('../include/benutzerfunktion.class.php');
require_once('../include/stundenplan.class.php');
require_once('../include/studiengang.class.php');
require_once('../include/reservierung.class.php');
require_once('../include/betriebsmittel.class.php');

$user = get_uid();

//error_reporting(0);

$return = false;
$errormsg = 'unknown';
$data = '';
$error = false;

//Berechtigungen laden
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
if(!$rechte->isBerechtigt('admin') && !$rechte->isBerechtigt('assistenz') && !$rechte->isBerechtigt('lv-plan'))
{
	$return = false;
	$errormsg = 'Keine Berechtigung';
	$data = '';
	$error = true;
}

if(!$error)
{
	if(isset($_POST['type']) && $_POST['type']=='undo')
	{
		//UNDO Befehl ausfuehren

		if (!isset($_POST['log_id']))
		{
			$return = false;
			$errormsg = 'Fehlerhafte Parameteruebergabe';
			$data = '';
			$error = true;
		}

		if(!$error)
		{
			$log = new log();

			if($log->undo($_POST['log_id']))
			{
				$return = true;
			}
			else
			{
				$return = false;
				$errormsg = 'Fehler bei UnDo:'.$log->errormsg;
			}
		}
	}
	elseif (isset($_POST['type']) && $_POST['type']=='addFunktionToMitarbeiter')
	{
		//Fuegt eine Lkt Funktion zu einem Studiengang/Mitarbeiter hinzu
		if(isset($_POST['uid']) && isset($_POST['studiengang_kz']))
		{
			if(!$rechte->isBerechtigt('admin', $_POST['studiengang_kz'],'suid') &&
			   !$rechte->isBerechtigt('assistenz', $_POST['studiengang_kz'],'suid'))
			{
				$return = false;
				$error = true;
				$errormsg = 'keine Berechtigung';
			}
			else
			{
				$stg = new studiengang($_POST['studiengang_kz']);
				$obj = new benutzerfunktion();
				$obj->uid = $_POST['uid'];
				$obj->oe_kurzbz = $stg->oe_kurzbz;
				$obj->funktion_kurzbz = 'lkt';
				$obj->updateamum = date('Y-m-d H:i:s');
				$obj->updatevon = $user;
				$obj->insertamum = date('Y-m-d H:i:s');
				$obj->insertvon = $user;
	
				if($obj->save(true))
				{
					$return = true;
				}
				else
				{
					$return = false;
					$errormsg = $obj->errormsg;
				}
			}
		}
		else
		{
			$return = false;
			$errormsg = 'Fehlerhafte Parameteruebergabe';
		}
	}
	elseif (isset($_POST['type']) && $_POST['type']=='delFunktionFromMitarbeiter')
	{
		//Loescht eine Lektorfunktion
		if(isset($_POST['uid']) && isset($_POST['studiengang_kz']))
		{
			
			if(!$rechte->isBerechtigt('admin', $_POST['studiengang_kz'],'suid') &&
			   !$rechte->isBerechtigt('assistenz', $_POST['studiengang_kz'],'suid'))
			{
				$return = false;
				$error = true;
				$errormsg = 'keine Berechtigung';
			}
			else
			{
				$obj = new benutzerfunktion();
				$stg_obj = new studiengang($_POST['studiengang_kz']);
				//Benutzerfunktion suchen
				if($obj->getBenutzerFunktion($_POST['uid'], 'lkt', $stg_obj->oe_kurzbz))
				{
					//Benutzerfunktion loeschen
					if($obj->delete($obj->benutzerfunktion_id))
					{
						$return = true;
					}
					else
					{
						$return = false;
						$errormsg = $obj->errormsg;
					}
				}
				else
				{
					$return = false;
					$errormsg = $obj->errormsg;
				}
			}
		}
		else
		{
			$return = false;
			$errormsg = 'Fehler bei Parameteruebergabe';
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='deletestundenplaneintrag')
	{
		if(!$rechte->isBerechtigt('lv-plan', null, 'suid') && !$rechte->isBerechtigt('admin', null, 'suid') )
		{
			$return = false;
			$error = true;
			$errormsg = 'keine Berechtigung';
		}
		else
		{
			//Loescht einen Eintrag aus der Stundenplantabelle
			loadVariables(get_uid());
			if(isset($_POST['stundenplan_id']) && is_numeric($_POST['stundenplan_id']))
			{
				$stundenplan = new stundenplan($db_stpl_table);
				if($stundenplan->delete($_POST['stundenplan_id']))
				{
					$return = true;
				}
				else 
				{
					$errormsg='Fehler beim Loeschen: '.$stundenplan->errormsg;
					$return = false;
					$data = '';
				}
			}
			else 
			{
				$return = false;
				$errormsg = 'ID ist ungueltig';
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='deletereservierung')
	{
		if(!$rechte->isBerechtigt('lv-plan', null, 'suid') && !$rechte->isBerechtigt('admin', null, 'suid') )
		{
			$return = false;
			$error = true;
			$errormsg = 'keine Berechtigung';
		}
		else
		{
			//Loescht eine Reservierung
			if(isset($_POST['reservierung_id']) && is_numeric($_POST['reservierung_id']))
			{
				$reservierung = new reservierung();
				if($reservierung->delete($_POST['reservierung_id']))
				{
					$return = true;
				}
				else 
				{
					$errormsg='Fehler beim Loeschen: '.$reservierung->errormsg;
					$return = false;
					$data = '';
				}
			}
			else 
			{
				$return = false;
				$errormsg = 'ID ist ungueltig';
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='savestundenplaneintrag')
	{
		if(!$rechte->isBerechtigt('lv-plan', null, 'suid') && !$rechte->isBerechtigt('admin', null, 'suid') )
		{
			$return = false;
			$error = true;
			$errormsg = 'keine Berechtigung';
		}
		else
		{
			loadVariables(get_uid());
			$stundenplan = new stundenplan($db_stpl_table);
			if($stundenplan->load($_POST['stundenplan_id']))
			{
				$stundenplan->unr = $_POST['unr'];
				$stundenplan->verband = $_POST['verband'];
				$stundenplan->gruppe = $_POST['gruppe'];
				$stundenplan->gruppe_kurzbz = $_POST['gruppe_kurzbz'];
				$stundenplan->ort_kurzbz = $_POST['ort_kurzbz'];
				$stundenplan->datum = $_POST['datum'];
				$stundenplan->stunde = $_POST['stunde'];
				$stundenplan->titel = htmlspecialchars_decode($_POST['titel']);
				$stundenplan->anmerkung = htmlspecialchars_decode($_POST['anmerkung']);
				$stundenplan->fix = ($_POST['fix']=='true'?true:false);
				$stundenplan->mitarbeiter_uid = $_POST['mitarbeiter_uid'];
				$stundenplan->updateamum = date('Y-m-d H:i:s');
				$stundenplan->updatevon = get_uid();
				$stundenplan->semester = $_POST['semester'];
				
				if($stundenplan->save(false))
				{
					$return = true;
				}
				else 
				{
					$return = false;
					$errormsg = 'Fehler beim Speichern der Daten:'.$stundenplan->errormsg;
				}
			}
			else 
			{
				$errormsg = 'Fehler beim Laden: '.$stundenplan->errormsg;
				$return = false;
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='deleteressource')
	{
		if(!$rechte->isBerechtigt('lehre/lvplan', null, 'suid'))
		{
			$return = false;
			$error = true;
			$errormsg = 'keine Berechtigung';
		}
		else
		{
			if(isset($_POST['stundenplan_betriebsmittel_id']) && is_array($_POST['stundenplan_betriebsmittel_id']))
			{
				$return = true;
				foreach($_POST['stundenplan_betriebsmittel_id'] as $stundenplan_betriebsmittel_id)
				{
					$betriebsmittel = new betriebsmittel();
					if(!$betriebsmittel->deleteStundenplanBetriebsmittel($stundenplan_betriebsmittel_id))
					{
						$errormsg='Fehler beim Loeschen: '.$betriebsmittel->errormsg;
						$return = false;
						$data = '';
					}
				}
			}
			else 
			{
				$return = false;
				$errormsg = 'ID ist ungueltig';
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='addressource')
	{
		if(!$rechte->isBerechtigt('lehre/lvplan', null, 'suid'))
		{
			$return = false;
			$error = true;
			$errormsg = 'keine Berechtigung';
		}
		else
		{
			$stunden = $_POST['stunden'];
			$stpl_id = $_POST['stpl_id'];
			$betriebsmittel_id = $_POST['betriebsmittel_id'];

			$db = new basis_db();

			if(isset($_POST['betriebsmittel_id']) && is_numeric($_POST['betriebsmittel_id']))
			{
				// Pro Stunde wird die Zuordnung nur auf eine der vorhandenen StundenplanIDs gehaengt
				foreach($stunden as $stunde)
				{
					$qry = "SELECT stundenplandev_id FROM lehre.tbl_stundenplandev WHERE stunde=".$db->db_add_param($stunde)." 
							AND stundenplandev_id in (".$db->db_implode4SQL($stpl_id).") ORDER BY stundenplandev_id LIMIT 1";

					if($result = $db->db_query($qry))
					{
						if($row = $db->db_fetch_object($result))
						{
							$id = $row->stundenplandev_id;

							$betriebsmittel = new betriebsmittel();
							$betriebsmittel->stundenplandev_id=$id;
							$betriebsmittel->betriebsmittel_id = $betriebsmittel_id;
							$betriebsmittel->insertvon = $user;
							$betriebsmittel->insertamum = date('Y-m-d H:i:s');
							$betriebsmittel->new=true;
							if($betriebsmittel->saveStundenplanBetriebsmittel())
							{
								$return = true;
							}
							else 
							{
								$errormsg='Fehler beim Speichern: '.$betriebsmittel->errormsg;
								$return = false;
								$data = '';
							}
						}
					}
				}
			}
			else 
			{
				$return = false;
				$errormsg = 'ID ist ungueltig';
			}
		}
	}
	elseif(isset($_POST['type']) && $_POST['type']=='saveressource')
	{
		if(!$rechte->isBerechtigt('lehre/lvplan', null, 'suid'))
		{
			$return = false;
			$error = true;
			$errormsg = 'keine Berechtigung';
		}
		else
		{
			if(isset($_POST['stundenplan_betriebsmittel_id']) && is_numeric($_POST['stundenplan_betriebsmittel_id']))
			{
				$stundenplan_betriebsmittel_id = $_POST['stundenplan_betriebsmittel_id'];

				
				$betriebsmittel = new betriebsmittel();

				if($betriebsmittel->loadBetriebsmittelStundenplan($stundenplan_betriebsmittel_id))
				{
					
					$betriebsmittel->anmerkung =$_POST['anmerkung'];

					if($betriebsmittel->saveStundenplanBetriebsmittel())
					{
						$return = true;
					}
					else 
					{
						$errormsg='Fehler beim Speichern: '.$betriebsmittel->errormsg;
						$return = false;
						$data = '';
					}
				}
			}
			else 
			{
				$return = false;
				$errormsg = 'ID ist ungueltig';
			}
		}
	}
	else
	{
		$return = false;
		$errormsg = 'Unkown type';
		$data = '';
	}
}

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
