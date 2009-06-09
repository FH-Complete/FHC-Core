<?php
function mail_id_generator()
{
	mt_srand((double)microtime()*1000000);

	/* Laenge des Passwortes dem Zufall ueberlassen */
	$length = 6; //mt_rand(6, 6);
	$fix_similar = '';
	$valid_charset = "";

    /* Stelle ein Charset zusammen */
    if (!$valid_charset)
    {
    	// deactivated, regarding an case sensitive issue
	    //$valid_charset .= 'ABCDEFGHIJKLMNOPQRSTUVXYZ';
    	$valid_charset .= 'abcdefghijklmnopqrstuvxyz';
 	   	$valid_charset .= '0123456789';
    	//$valid_charset .= '!@_-';
    }

    $charset_length = mb_strlen($valid_charset,'UTF-8');

    if ($charset_length == 0) return false;

    /* Initialisieren - Auswahl von chars bis definierte Anzahl erreicht */
    $mail_id = "";
    while(strlen($mail_id) < $length)
    {
    	/* Waehle einen zufaelligen char aus */
	    $char = $valid_charset[mt_rand(0, ($charset_length-1))];

	    /* Abgleich von gleich aussehenden chars */
	    if (($fix_similar && !strpos('O01lI5S', $char)) || !$fix_similar) $mail_id .= $char;
	}
    return $mail_id;
}

require_once('../config.inc.php');
require_once('../../include/functions.inc.php');
/* ffe, 20051020 to stop bored people
if(!$conn=pg_pconnect(CONN_STRING))
   die("Fehler beim Herstellen der DB Verbindung");

if(!check_lektor($_SERVER["REMOTE_USER"],$conn))
{
	ob_clean();
	exit();
}
*/
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../skin/style.css.php" rel="stylesheet" type="text/css">
</head>
<title>&Ouml;ffnen eines Mailverteilers</title>
<body id="inhalt">
<?php
 if(isset($_REQUEST['token']) && isset($_REQUEST['grp']))
 {
 ?>
 	<table class="tabcontent">
 		<tr>
 	        	<td class="ContentHeader"><font class="ContentHeader">Mailverteiler</font></td>
 	        	<td class="ContentHeader"><font class="ContentHeader">Status</font></td>
 	      	</tr>
<?php
		/* Generate an random String  */
		$mail_id=mail_id_generator();

		/* call the shellpart at polyxena */
		$command = "ssh polyxena sudo /root/bin/mlistin.sh " . $_REQUEST['grp'] . " " . $mail_id . " 2>&1";
		exec($command);

		/* ffe, 20051020 - do a little logging */
		$message= date("F j G:i:s") . " mailgroup: [" . $_REQUEST['grp'] . "] (using " . $mail_id . ") requested by [" . $_SERVER['PHP_AUTH_USER'] . "]\n";

		$filet = fopen(LOG_PATH.'.htmlistopen.log', "a");
	   	fwrite($filet, $message, mb_strlen($message,'UTF-8'));
	    fclose($filet);

		// for the users
		echo "
		<tr>
			<td><a href='mailto:".$_REQUEST['grp'].$mail_id."@technikum-wien.at'>".$_REQUEST['desc']."</a></td>
			<td>Ge&ouml;ffnet (Code: ".$mail_id.")</td>
		</tr>
<table>

		<p>Um ein Mail an den Verteiler zu senden klicken Sie bitte auf den obigen Link. Ihr Mailprogramm &ouml;ffnet automatisch eine Vorlage f&uuml;r ein neues Mail, welche bereits die korrekte Adresse enth&auml;lt.

		<p>Das Senden ist f&uuml;r den Zeitraum von <b>2 Stunden</b> bzw. f&uuml;r die <b>einmalige</b> Benutzung unter der Adresse <a href='mailto:".$_REQUEST['grp'].$mail_id."@technikum-wien.at'>".$_REQUEST['grp'].$mail_id."@technikum-wien.at</a> m&ouml;glich.

		";
 }
 else
 {
 	if($_REQUEST['grp']=="")
 	{
 		exit();
 	}
 	else
 	{
 		//Fixed for https
 		//echo"Bitte best&auml;tigen Sie das &Ouml;ffnen des Verteilers ".$_REQUEST['grp'].": <a href=\"http://".$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']."?grp=".$_REQUEST['grp']."&desc=".$_REQUEST['desc']."&token=1\">Best&auml;tige</a>";
 		echo"Bitte best&auml;tigen Sie das &Ouml;ffnen des Verteilers ".$_REQUEST['grp'].": <a href=\"".$_SERVER['SCRIPT_NAME']."?grp=".$_REQUEST['grp']."&desc=".$_REQUEST['desc']."&token=1\">Best&auml;tige</a>";
	}
 }
 	?>

</body></html>
