<?php
/* Copyright (C) 2008 Technikum-Wien
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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
 
	require_once('../../../config/cis.config.inc.php');
  require_once('../../../include/basis_db.class.php');
  if (!$db = new basis_db())
      die('Fehler beim Oeffnen der Datenbankverbindung');
  
	//include('../../../include/globals.inc.php');
	include_once('../../../include/functions.inc.php');

	if (!$uid=get_uid())
		die('Sie sind nicht angemeldet. Es wurde keine Benutzer UID gefunden ! <a href="javascript:history.back()">Zur&uuml;ck</a>');

	$sql_query="SET search_path TO campus; SELECT titelpre, titelpost, uid, nachname, vorname FROM vw_benutzer WHERE uid LIKE '$uid'";
	//echo $sql_query;
	$result=$db->db_query($sql_query);

	if($db->db_num_rows($result)==0)
	{
		//GastAccount
		$titelpre='';
		$titelpost='';
		$uid='';
		$nachname='';
		$vornamen='';
		//echo "User not found!";
	}
	else
	{
		$titelpre=$db->db_result($result,0,'"titelpre"');
		$titelpost=$db->db_result($result,0,'"titelpost"');
		$uid=$db->db_result($result,0,'"uid"');
		$nachname=$db->db_result($result,0,'"nachname"');
		$vornamen=$db->db_result($result,0,'"vorname"');
	}
	$sql_query="SELECT studiengang_kz, kurzbz, kurzbzlang, bezeichnung, typ FROM public.tbl_studiengang WHERE aktiv ORDER BY typ, kurzbz";
	$result_stg=$db->db_query($sql_query);
	if(!$result_stg)
		die ("Studiengang not found!");
	$num_rows_stg=$db->db_num_rows($result_stg);
	$sql_query="SELECT ort_kurzbz FROM public.tbl_ort WHERE aktiv AND lehre ORDER BY ort_kurzbz";
	$result_ort=$db->db_query($sql_query);
	if(!$result_ort)
	  	die("ort not found!");
	$num_rows_ort=$db->db_num_rows($result_ort);
	$sql_query="SELECT uid, kurzbz FROM vw_mitarbeiter ORDER BY kurzbz";
	$result_lektor=$db->db_query($sql_query);
	if(!$result_lektor)
		die("lektor not found!");
	$num_rows_lektor=$db->db_num_rows($result_lektor);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Lehrveranstaltungsplan</title>
<script language="JavaScript">
<!--
function MM_jumpMenu(targ,selObj,restore){ //v3.0
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}
//-->
</script>
<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
</head>

<BODY id="inhalt">
<font size="2">
	<table class="tabcontent">
		<tr>
			<td class="ContentHeader"><font class="ContentHeader">&nbsp;Lehrveranstaltungsplan</font></td>
			<td align="right" class="ContentHeader"><A href="help/index.html" class="hilfe" target="_blank"><font class="ContentHeader">HELP&nbsp;</font></A></td>
		</tr>
	</table>
	<!--<DIV align="right">Version: <?php echo VERSION; ?></DIV>-->
	Username:
	<?php
		if (isset($uid))
			echo $uid;
		else
			echo 'nicht vorhanden! Bitte wenden Sie sich an den <A href="mailto:'.MAIL_ADMIN.'">Admin</A>!';
	?><BR>
  	<DIV align="left">
  		<a class="Item" href="stpl_week.php?pers_uid=<?php echo $uid; ?>"><?php echo $titelpre.' '.$vornamen." ".$nachname.' '.$titelpost;?></a>
		&nbsp; -> Ihr pers&ouml;nlicher Lehrveranstaltungsplan<BR>
		<a class="Item" href="../profile/index.php">PROFIL</a>
		&nbsp; -> Hier k&ouml;nnen Sie Ihre Stammdaten kontrollieren.<BR>
	</DIV>
	<BR>
	<FORM name="Auswahl" action="stpl_week.php">
		<table class="tabcontent">
		<tr>
			<td width="50%" class="ContentHeader2">
				&nbsp;Saalplan
			</td>
			<td width="50%" class="ContentHeader2">
				&nbsp;Lektorenplan
			</td>
		</tr>
		<tr>
			<td>
			<BR>
			Saal:
			<select name="select" onChange="MM_jumpMenu('self',this,0)">
        		<option value="stpl_wekk.php" selected>... ??? ...</option>
        	  	<?php
				for ($i=0;$i<$num_rows_ort;$i++)
				{
					$row=$db->db_fetch_object ($result_ort, $i);
					echo "<option value=\"stpl_week.php?type=ort&ort_kurzbz=$row->ort_kurzbz\">$row->ort_kurzbz</option>";
				}
				?>
			</select>
			(Saalreservierung)<BR><BR>
			<A class="Item" href="stpl_reserve_list.php">Reservierungsliste</A> (Reservierungen l&ouml;schen)<BR>
			<A class="Item" href="raumsuche.php">Raumsuche</A><BR>
			</td>

			<td valign="top">
			<br>
			Lektor:
	  		<select name="lektor" onChange="MM_jumpMenu('self',this,0)">
			    	<option value="stpl_week.php" selected>... ??? ...</option>
			    	<?php
				for ($i=0;$i<$num_rows_lektor;$i++)
				{
					$row=$db->db_fetch_object ($result_lektor, $i);
					echo "<option value=\"stpl_week.php?type=lektor&pers_uid=$row->uid\">$row->kurzbz</option>";
				}
				?>
			</select>
			</td>
		</tr>
		</table>
		<br><br>
		<table class="tabcontent"><tr><td class="ContentHeader2">&nbsp;Lehr-Verband</td></tr></table>
		<table width="40%" border="0" cellpadding="0" cellspacing="3">
		<tr nowrap>
		<td width="20%" valign="middle">
			Studiengang<BR>
			<select name="stg_kz" >
				<?php
				$num_rows=$db->db_num_rows($result_stg);
				for ($i=0;$i<$num_rows;$i++)
				{
					$row=$db->db_fetch_object ($result_stg, $i);
					echo '<option value="'.$row->studiengang_kz.'">'.strtoupper($row->typ.$row->kurzbz)." ( $row->kurzbzlang - $row->bezeichnung )</option>";
				}
				?>
			</select>
		</td>
		<td valign="middle">
			Sem<BR>
			<select name="sem">
			<option value="1">1</option>
			<option value="2">2</option>
			<option value="3">3</option>
			<option value="4">4</option>
			<option value="5">5</option>
			<option value="6">6</option>
			<option value="7">7</option>
			<option value="8">8</option>
			</select>
		</td>
		<td valign="middle">
			Ver<BR>
			<select name="ver" >
			<option value="0" selected>*</option>
			<option value="A">A</option>
			<option value="B">B</option>
			<option value="C">C</option>
			<option value="D">D</option>
			<option value="F">F</option>
			<option value="V">V</option>
			</select>
		</td>
		<td valign="middle" >
			Grp<BR>
			<select name="grp">
			<option value="0" selected>*</option>
			<option value="1">1</option>
			<option value="2">2</option>
			<option value="3">3</option>
			<option value="3">4</option>
			</select>
		</td>
		<TD valign="bottom">
			<INPUT type="hidden" name="type" value="verband">
			<INPUT type="submit" name="Abschicken" value="Go">
		</TD>
		</tr>
		</table>
	</form>
	<a class="Item" href="verband_uebersicht.php">Lehrverb&auml;nde</a> -> &Uuml;bersicht der Lehrverb&auml;nde<BR>
<BR><BR><HR>
<P>Fehler und Feedback bitte an <A class="Item" href="mailto:<?php echo MAIL_LVPLAN?>">LV-Koordinationsstelle</A>.</P>
<!--
<P class=little>
    Erstellt am 24.8.2001 von <A href="mailto:pam@technikum-wien.at">Christian Paminger</A>.<BR>
    Letzte &Auml;nderung am 11.1.2005 von <A href="mailto:pam@technikum-wien.at">Christian Paminger</A>.
</P>
-->
</font>
</body>
</html>
