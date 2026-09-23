<?php

require_once('../config/vilesci.config.inc.php');
require_once('../include/lehrveranstaltung_faktor.class.php');
require_once('../include/lehrveranstaltung.class.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/studiensemester.class.php');

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('basis/person', null, 'suid'))
{
	exit('Sie haben keine Berechtigung für die Seite');
}

$method = isset($_REQUEST['method']) ? $_REQUEST['method']: '' ;
$lv_faktor = new lehrveranstaltung_faktor();

switch($method)
{
	case 'addFaktor':
		$faktor = isset($_REQUEST['faktor']) ? $_REQUEST['faktor']: '' ;
		if ($faktor !== '')
		{
			if (!isRightType($faktor['lv_id']))
			{
				echo json_encode([
					'status' => 'error',
					'message' => 'Nur LVs und Templates möglich'
				]);
				break;
			}
			if (vonHigherThanBis($faktor['von'], $faktor['bis']))
			{
				echo json_encode([
					'status' => 'error',
					'message' => 'Von nach Bis'
				]);
				break;
			}
			if (exists($faktor['lv_id'], $faktor['von'], $faktor['bis'], null, $faktor['lehrform_kurzbz']))
			{
				echo json_encode([
					'status' => 'error',
					'message' => 'Für den Zeitraum bereits vorhanden'
				]);
				break;
			}

			$newFaktor = new lehrveranstaltung_faktor();
			$newFaktor->lehrveranstaltung_id = $faktor['lv_id'];
			$newFaktor->faktor = $faktor['faktor'];
			$newFaktor->studiensemester_kurzbz_von = $faktor['von'];
			$newFaktor->studiensemester_kurzbz_bis = $faktor['bis'];
			$newFaktor->lehrform_kurzbz = $faktor['lehrform_kurzbz'];
			$newFaktor->insertvon = get_uid();
			$result = $newFaktor->save(true);

			echo json_encode($result);
		}
		break;
	case 'updateFaktor':

		$faktor = isset($_REQUEST['faktor']) ? $_REQUEST['faktor']: '' ;

		if ($faktor !== '')
		{
			if (vonHigherThanBis($faktor['von'], $faktor['bis']))
			{
				echo json_encode([
					'status' => 'error',
					'message' => 'Von nach Bis'
				]);
				break;
			}
			if (exists($faktor['lv_id'], $faktor['von'], $faktor['bis'], $faktor['id'], $faktor['lehrform_kurzbz']))
			{
				echo json_encode([
					'status' => 'error',
					'message' => 'Für den Zeitraum bereits vorhanden'
				]);
				break;
			}

			$updateFaktor = new lehrveranstaltung_faktor();
			$updateFaktor->lehrveranstaltung_faktor_id = $faktor['id'];
			$updateFaktor->faktor = $faktor['faktor'];
			$updateFaktor->studiensemester_kurzbz_von = $faktor['von'];
			$updateFaktor->studiensemester_kurzbz_bis = $faktor['bis'];
			$updateFaktor->lehrform_kurzbz = $faktor['lehrform_kurzbz'];
			$updateFaktor->updatevon = get_uid();
			$updateFaktor->updateamum = date('Y-m-d H:i:s');
			$result = $updateFaktor->save();
			echo json_encode($result);
		}
		break;
	case 'deleteFaktor':
		$faktor = isset($_REQUEST['faktor']) ? $_REQUEST['faktor']: '' ;
		if ($faktor !== '')
		{
			$result = $lv_faktor->deleteFaktor($faktor['id']);
			echo json_encode($result);
		}
 		break;
	default:
		break;
}

function isRightType($lv_id)
{
	$lv = new lehrveranstaltung($lv_id);
	if (in_array($lv->lehrtyp_kurzbz, array('lv', 'tpl')))
		return true;

	return false;
}

function exists($lv_id, $von, $bis, $id = null, $lehrform_kurzbz = null)
{
	$lv_faktor = new lehrveranstaltung_faktor();

	$lehrform_kurzbz = $lehrform_kurzbz === ' - ' ? null : $lehrform_kurzbz;
	return $lv_faktor->checkIfExists($lv_id, $von, $bis, $id, $lehrform_kurzbz);
}

function vonHigherThanBis($von, $bis)
{
	$vonStsem = new studiensemester($von);
	$bisStsem = new studiensemester($bis);

	if (is_null($bis) || $bis === "")
		return false;
	if ($vonStsem->start > $bisStsem->start)
		return true;
	else
		return false;

}

?>
