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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *          Gerald Raab <gerald.raab@technikum-wien.at>.
 */

	require_once('../../config.inc.php');
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/studiensemester.class.php');
	
	if(!$conn = pg_pconnect(CONN_STRING))
		die('Fehler beim Connecten');
	
	$uid=get_uid();
	$ansicht=false; //Wenn ein anderer User sich das Profil ansieht (Bei Personensuche)
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Profil</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
<script language="JavaScript" type="text/javascript">
function createStudienerfolg()
{
	var stsem = document.getElementById('stsem').value;
	var finanzamt = document.getElementById('finanzamt').checked;
	
	if(finanzamt)
		finanzamt = '&typ=finanzamt';
	else
		finanzamt = '';
	window.location.href= '../pdfExport.php?xml=studienerfolg.rdf.php&xsl=Studienerfolg&ss='+stsem+'&uid=<?php echo $uid;?>'+finanzamt;
}
</script>
</head>

<body>
<table class="tabcontent" id="inhalt">
  <tr>
    <td class="tdwidth10">&nbsp;</td>
    <td>
    <table class="tabcontent">
      <tr>
		<td class='ContentHeader'><font class='ContentHeader'>&nbsp;Studienerfolgsbest&auml;tigung</font></td>
		<!--<td align="right"><A href="../lvplan/help/index.html" class="hilfe" target="_blank">HELP&nbsp;</A></td>-->
	  </tr>
	</table>
	<br>
	Bitte wählen Sie das entsprechende Studiensemester aus.<br><br>
	<?php
		$qry = "SELECT distinct studiensemester_kurzbz FROM campus.vw_student JOIN public.tbl_prestudentstatus USING(prestudent_id) WHERE uid='$uid'";
		if($result = pg_query($conn, $qry))
		{
			echo 'Studiensemester: <SELECT id="stsem">';
			
			$stsem_obj = new studiensemester($conn);
			$stsem = $stsem_obj->getPrevious();
			
			while($row = pg_fetch_object($result))
			{
				if($stsem==$row->studiensemester_kurzbz)
					$selected = 'selected';
				else 
					$selected = '';
				
				echo '<OPTION value="'.$row->studiensemester_kurzbz.'" '.$selected.'>'.$row->studiensemester_kurzbz.'</OPTION>';
			}
			
			echo '</SELECT>';
			echo '<br><br><INPUT type="checkbox" id="finanzamt">zur Vorlage beim Wohnsitzfinanzamt<br>';
			echo '<br><br><INPUT type="button" value="Erstellen" onclick="createStudienerfolg()" />';
		}
		
	?>
</body>
</html>
