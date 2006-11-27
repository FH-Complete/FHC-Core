<?php
/**
 * erstellt ein RDF File mit den Emails
 * Created on 23.3.2006
 * Aufruf: email.rdf.php?pers_id=xyz
 */
// header für no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/vnd.mozilla.xul+xml");
// xml
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
// DAO
include('../../vilesci/config.inc.php');
include_once('../../include/fas/email.class.php');

// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING_FAS))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$rdf_url='http://www.technikum-wien.at/email';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:EMAIL="<?php echo $rdf_url; ?>/rdf#"
>


  <RDF:Seq RDF:about="<?php echo $rdf_url ?>/alle">
<?php
//Parameter holen
if(isset($_GET['pers_id']))
{
	$pers_id = $_GET['pers_id'];

	// emails holen
	$emailDAO=new email($conn);
	$emailDAO->load_pers($pers_id);

			
	foreach ($emailDAO->result as $email)
	{
		?>
		  <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/'.$email->email_id; ?>" >
	        	<EMAIL:email_id><?php echo $email->email_id;  ?></EMAIL:email_id>
	        	<EMAIL:person_id><?php echo $email->person_id;  ?></EMAIL:person_id>
	    		<EMAIL:email><![CDATA[<?php echo $email->email;  ?>]]></EMAIL:email>
	    		<EMAIL:name><?php echo $email->name;  ?></EMAIL:name>
	    		<EMAIL:typ><?php echo $email->typ;  ?></EMAIL:typ>
	    		<EMAIL:zustelladresse><?php echo ($email->zustelladresse?'Ja':'Nein');  ?></EMAIL:zustelladresse>
	      	</RDF:Description>
	      </RDF:li>
	<?php
	
	}
}
elseif(isset($_GET['email_id']))
{
	$email_id = $_GET['email_id'];
	$emailDAO=new email($conn);
	$emailDAO->load($email_id);
?>
		  <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/'.$emailDAO->email_id; ?>" >
	        	<EMAIL:email_id><?php echo $emailDAO->email_id;  ?></EMAIL:email_id>
	        	<EMAIL:person_id><?php echo $emailDAO->person_id;  ?></EMAIL:person_id>
	    		<EMAIL:email><![CDATA[<?php echo $emailDAO->email;  ?>]]></EMAIL:email>
	    		<EMAIL:name><?php echo $emailDAO->name;  ?></EMAIL:name>
	    		<EMAIL:typ><?php echo $emailDAO->typ;  ?></EMAIL:typ>
	    		<EMAIL:zustelladresse><?php echo ($emailDAO->zustelladresse?'Ja':'Nein');  ?></EMAIL:zustelladresse>
	      	</RDF:Description>
	      </RDF:li>
	<?php
}
?>

  </RDF:Seq>


</RDF:RDF>