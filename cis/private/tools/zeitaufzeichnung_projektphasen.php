<?php

require_once('../../../config/cis.config.inc.php');
/*require_once('../../../include/functions.inc.php');*/
require_once('../../../include/basis_db.class.php');
require_once('../../../include/projektphase.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');
//$uid=get_uid();
if(isset($_GET['projekt_kurzbz'])) // TODO maybe check that phasen only shown if projekt is projekt of logged in user
{
	$projekt_kurzbz = $_GET['projekt_kurzbz'];
	$projektphase = new projektphase();

	if($projektphase->getProjektphasen($projekt_kurzbz))
	{
		$result_obj = array();
		foreach($projektphase->result as $row)
		{
			$item['projektphase_id']=$row->projektphase_id;
			$item['bezeichnung']=$row->bezeichnung;
			$result_obj[]=$item;
		}
		echo json_encode($result_obj);
	}
	exit;
}
