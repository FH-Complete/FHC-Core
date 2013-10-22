<?php
header( 'Expires:  -1' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Pragma: no-cache' );
header('Content-Type: text/html;charset=UTF-8');

require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/studienplan.class.php');
require_once('../include/benutzerberechtigung.class.php');

$uid = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);
if(!$rechte->isBerechtigt('lehre/studienordnung'))
	die('Sie haben keine Berechtigung für diese Seite');

$method = isset($_REQUEST['method'])?$_REQUEST['method']:'';

switch($method)
{
	case 'loadStudienplanSTO':
		$studienordnung_id=$_REQUEST['studienordnung_id'];
		$studienplan = new studienplan();
		if($studienplan->loadStudienplanSTO($studienordnung_id))
		{
			$data['result']=$studienplan->cleanResult();
			$data['error']='false';
			$data['errormsg']='';
		}
		else
		{
			$data['error']='true';
			$data['errormsg']=$studienplan->errormsg;
		}
		break;
	default:
		break;
}

echo json_encode($data);

?>
