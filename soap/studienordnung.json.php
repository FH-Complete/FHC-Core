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
	case 'saveSemesterZuordnung':
		$studienordnung_id=$_REQUEST['studienordnung_id'];
		$studiensemester_kurzbz=$_REQUEST['studiensemester_kurzbz'];
		$ausbildungssemester=$_REQUEST['ausbildungssemester'];

		$studienordnung = new studienordnung();
		$studienordnung->loadStudienordnung($studienordnung_id);
		if($result = $studienordnung->saveSemesterZuordnung($studienordnung_id, $studiensemester_kurzbz, $ausbildungssemester))
		{
			$data['result']=$result;
			$data['error']='false';
			$data['errormsg']='';
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
