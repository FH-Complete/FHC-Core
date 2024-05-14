<?php
header( 'Expires:  -1' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Pragma: no-cache' );
header('Content-Type: text/html;charset=UTF-8');

require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/studienordnung.class.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/studienplan.class.php');
require_once('../include/lvregel.class.php');

$uid = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);
if(!$rechte->isBerechtigt('lehre/studienordnung'))
	die('Sie haben keine Berechtigung für diese Seite');

$method = isset($_REQUEST['method'])?$_REQUEST['method']:'';

switch($method)
{
	case 'loadStudienordnungSTG':
		$studiengang_kz=$_REQUEST['studiengang_kz'];
		$studienordnung = new studienordnung();
		if($studienordnung->loadStudienordnungSTG($studiengang_kz))
		{
			$data['result']=$studienordnung->cleanResult();
			$data['error']='false';
			$data['errormsg']='';
		}
		else
		{
			$data['error']='true';
			$data['errormsg']=$studienordnung->errormsg;
		}
		break;
	case 'copyStudienordnung':
			$studienordnung_id=$_REQUEST['studienordnung_id'];

			$studienordnung = new studienordnung();
			if($studienordnung->loadStudienordnung($studienordnung_id))
			{
				// Studienordnung anlegen
				$studienordnung->new=true;
				$studienordnung->bezeichnung .= ' Kopie 1';
				$studienordnung->insertamum=date('Y-m-d H:i:s');
				$studienordnung->insertvon=$uid;

				if($studienordnung->save())
				{
					$studienordnung_id_neu = $studienordnung->studienordnung_id;

					// Studienplaene kopieren
					$studienplan = new studienplan();
					if($studienplan->loadStudienplanSTO($studienordnung_id))
					{
						foreach($studienplan->result as $studienplan_obj)
						{
							$stpllv_ID_Array=array();
							$lvregel_ID_Array=array();

							$studienplan_obj->studienordnung_id = $studienordnung_id_neu;
							$studienplan_obj->new=true;
							$studienplan_obj->insertamum=date('Y-m-d H:i:s');
							$studienplan_obj->insertvon=$uid;

							$studienplan_id_alt = $studienplan_obj->studienplan_id;

							if($studienplan_obj->save())
							{

								// Lehrveranstaltungszuordnungen kopieren
								$stpllv = new studienplan();
								$stpllv->loadStudienplanLV($studienplan_id_alt);
								foreach($stpllv->result as $stpllv_obj)
								{
									$stpllv_obj->new=true;
									$stpllv_obj->studienplan_id=$studienplan_obj->studienplan_id;
									$stpllv_obj->insertamum = date('Y-m-d H:i:s');
									$stpllv_obj->insertvon = $uid;

									$studienplan_lehrveranstaltung_id_alt = $stpllv_obj->studienplan_lehrveranstaltung_id;

									if($stpllv_obj->saveStudienplanLehrveranstaltung())
									{
										// Alte und neue ID Speichern damit danach die Parents gesetzt werden koennen
										$stpllv_ID_Array[$studienplan_lehrveranstaltung_id_alt]=$stpllv_obj->studienplan_lehrveranstaltung_id;
										
										// LVRegeln kopieren
										$lvregel = new lvregel();
										$lvregel->loadLVRegeln($studienplan_lehrveranstaltung_id_alt);
										foreach($lvregel->result as $regel_obj)
										{
											
											$regel_obj->new=false;
											$regel_obj->studienplan_lehrveranstaltung_id=$stpllv_obj->studienplan_lehrveranstaltung_id;
											$regel_obj->insertamum = date('Y-m-d H:i:s');
											$regel_obj->insertvon = $uid;
											
											$lvregel_id_alt = $regel_obj->lvregel_id;
											
											if($regel_obj->save())
											{
												// Alte und neue ID Speichern damit danach die Parents gesetzt werden koennen
												$lvregel_ID_Array[$lvregel_id_alt]=$regel_obj->lvregel_id;
											}
										}
									}
								}
							}
							
							// Damit die Parent Eintraege korrekt gesetzt werden koennen, muessen zuerst die uebergeordneten 
							// Eintraege in der Datenbank vorhanden sein. Deshalb werden zuerst alle Eintraege angelegt
							// und danach die Parent Keys korrekt gesetzt.

							// Alle neuen LVZuordnungen nochmals durchlaufen und die parents korrekt setzen
							foreach($stpllv_ID_Array as $studienplan_lehrveranstaltung_id)
							{
								$stpllv_obj = new studienplan();
								if($stpllv_obj->loadStudienplanLehrveranstaltung($studienplan_lehrveranstaltung_id))
								{
									if($stpllv_obj->studienplan_lehrveranstaltung_id_parent!='')
									{
										$stpllv_obj->studienplan_lehrveranstaltung_id_parent = $stpllv_ID_Array[$stpllv_obj->studienplan_lehrveranstaltung_id_parent];
										$stpllv_obj->saveStudienplanLehrveranstaltung();
									} 
								}
							}
							
							// Alle neuen LVRegeln nochmals durchlaufen und die parents korrekt setzen
							foreach($lvregel_ID_Array as $lvregel_id)
							{
								$lvregel_obj = new lvregel();
								if($lvregel_obj->load($lvregel_id))
								{
									if($lvregel_obj->lvregel_id_parent!='')
									{
										$lvregel_obj->lvregel_id_parent = $lvregel_ID_Array[$lvregel_obj->lvregel_id_parent];
										$lvregel_obj->save();
									}
								}
							}
						}
						// tbl_studienordnung_semester: wird nicht kopiert da es sonst dazu kommen kann, dass mehrere aktive Studienordnungen vorhanden sind
						$data['error']='false';
						$data['errormsg']='';
					}
					else
					{
						$data['error']='true';
						$data['errormsg']=$studienplan->errormsg;
					}
				}
				else
				{
					$data['error']='true';
					$data['errormsg']=$studienordnung->errormsg;
				}				
			}
			else
			{
				$data['error']='true';
				$data['errormsg']=$studienordnung->errormsg;
			}
			break;
	default:
		break;
}

echo json_encode($data);

?>
