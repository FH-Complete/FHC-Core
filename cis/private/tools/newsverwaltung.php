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

// ---------------- CIS Include Dateien einbinden
	require_once('../../../config/cis.config.inc.php');

// ---------------- Diverse Funktionen und UID des Benutzers ermitteln
	require_once('../../../include/functions.inc.php');
	if (!$user=get_uid())
		die('Sie sind nicht angemeldet. Es wurde keine Benutzer UID gefunden !');

// ---------------- Classen Datenbankabfragen und Funktionen 
	include_once('../../../include/person.class.php');
	include_once('../../../include/benutzer.class.php');
	include_once('../../../include/benutzerberechtigung.class.php');
	// ---------------- News Classe und Allg.Funktionen	
	require_once('../../../include/news.class.php');
	

// ---------------- Originalaufruf festhalten mittels eines Schalters
	$newsSwitch=false;
	if (!isset($_REQUEST['studiengang_kz']) && !isset($_REQUEST['semester']) && !isset($_REQUEST['fachbereich_kurzbz']))
	{
		$newsSwitch='tools';
	}
	
	$rechte = new benutzerberechtigung();
 	$rechte->getBerechtigungen($user);

	if(check_lektor($user))
       	$is_lector=true;
	else
		$is_lector=false;
		
	if($rechte->isBerechtigt('admin') 
	|| $rechte->isBerechtigt('assistenz') 
	|| $rechte->isBerechtigt('lehre') 
	|| $rechte->isBerechtigt('news'))
		$berechtigt=true;
	else
		$berechtigt=false;
	if(!$berechtigt)
		die('Sie haben keine Berechtigung f&uuml;r diese Seite. !  <a href="javascript:history.back()">Zur&uuml;ck</a>');

	// Init	
	$error='';
	// Open der NEWs-Classe
	$news = new news();

#var_dump($news);
#	var_dump($_REQUEST);
	
	// Parameter einlesen		
	$news_id=trim((isset($_REQUEST['news_id']) ? $_REQUEST['news_id']:''));
	$btnSend=trim((isset($_REQUEST['btnSend']) ? $_REQUEST['btnSend']:''));
	$btnDel=trim((isset($_REQUEST['btnDel']) ? $_REQUEST['btnDel']:''));
	$btnRead=trim((isset($_REQUEST['btnRead']) ? $_REQUEST['btnRead']:''));
	
	// Verarbeiten der Daten
	if (!empty($btnSend))
	{
		if(isset($news_id) && $news_id != "")
			$news->new=false;
		else
			$news->new=true;			

		$news->news_id = $news_id;
		$news->betreff = trim((isset($_REQUEST['betreff']) ? $_REQUEST['betreff']:''));
		$news->verfasser =trim((isset($_REQUEST['verfasser']) ? $_REQUEST['verfasser']:''));
		$news->text = trim((isset($_REQUEST['text']) ? $_REQUEST['text']:''));
		$news->studiengang_kz=trim((isset($_REQUEST['studiengang_kz']) ? $_REQUEST['studiengang_kz']:0));
		$news->semester=(isset($_REQUEST['semester']) ? $_REQUEST['semester']:null);
		$news->fachbereich_kurzbz=(isset($_REQUEST['fachbereich_kurzbz']) ? $_REQUEST['fachbereich_kurzbz']:null);
		
		$chksenat=(isset($_REQUEST['chksenat']) ?true :false);
		if(isset($chksenat))
			$news->fachbereich_kurzbz = 'Senat';
		else
			$news->fachbereich_kurzbz = '';
			
		$news->datum = trim((isset($_REQUEST['datum']) ? $_REQUEST['datum']:date('d.m.Y')));
		$news->datum_bis = trim((isset($_REQUEST['datum_bis']) ? $_REQUEST['datum_bis']:null));
		$news->uid=$user;
		$news->updatevon=$user;
		$news->updateamum=date('Y-m-d H:i:s');	
		
#		var_dump($news);
		if($news->save())
		{
			if(isset($news_id) && $news_id != "")
				$error.=(!empty($error)?'<br>':'').'Die Nachricht wurde erfolgreich ge&auml;ndert!';		
			else
				$error.=(!empty($error)?'<br>':'').'Die Nachricht wurde erfolgreich eingetragen!';						
		}
		else
		{
			$error.=(!empty($error)?'<br>':'').$news->errormsg;
		}
				
	}

	// Verarbeiten der Daten
	if (!empty($btnDel))
	{
		if(isset($news_id) && $news_id != "")
		{
			if($news->delete($news_id))
			{
				writeCISlog('DELETE NEWS','');
				$error.=(!empty($error)?'<br>':'').'Die Nachricht wurde erfolgreich gel&ouml;scht!';						
				$news_id='';
			}
			else
				$error.=(!empty($error)?'<br>':'').'Fehler beim l&ouml;schen des Eintrages! '.$news->errormsg;
		}	
		
	}
	// Einlesen News
	if(isset($news_id) && $news_id != "")
	{
		if (!$news->load($news_id))
			$error.=(!empty($error)?'<br>':'').$news->errormsg;	
	}	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
<script language="JavaScript" type="text/javascript">



	function focusFirstElement()
	{
		if(document.NewsEntry.verfasser != null)
		{
			document.NewsEntry.verfasser.focus();
		}
	}

	function plausibElement()
	{
		document.getElementById('error').innerHTML='';
		if(document.NewsEntry.verfasser.value == '')
		{
			document.NewsEntry.verfasser.focus();
			document.getElementById('error').innerHTML='Eingabe Verfasser fehlt!';
			return false;
		}
		var checkDate=''
		if(document.NewsEntry.datum.value == '')
		{
			document.NewsEntry.datum.focus();
			document.getElementById('error').innerHTML='Eingabe Sichtbar ab fehlt!';
			return false;
		}
		
		if(document.NewsEntry.datum.value != '')
		{
			checkDate=checkdatum(document.NewsEntry.datum);
			if (checkDate)
			{
				document.NewsEntry.datum.focus();
				document.getElementById('error').innerHTML=checkDate;
				return false;		
			}
		}

		if (document.NewsEntry.datum_bis.value != '')
		{
			checkDate=checkdatum(document.NewsEntry.datum_bis);
			if (checkDate)
			{
				document.NewsEntry.datum_bis.focus();
				document.getElementById('error').innerHTML=checkDate;
				return false;		
			}	
		}

		
		if(document.NewsEntry.betreff.value == '')
		{
			document.NewsEntry.betreff.focus();
			document.getElementById('error').innerHTML='Eingabe Titel fehlt!';
			return false;
		}
		if(document.NewsEntry.text.value == '')
		{
			document.NewsEntry.text.focus();
			document.getElementById('error').innerHTML='Eingabe der Nachricht fehlt!';
			return false;
		}
		return true;
	}

	function checkdatum(datum)
	{
		var Datum=datum;
		if(Datum.value.length<10)
		{
			return 'Datum ' + Datum.value + ' ist ungültig. Bitte beachten Sie das führende nullen angegeben werden müssen (Beispiel: <?php echo date('d.m.Y');?>)';
		}
	      	var Tag, Monat,Jahr,Date; 
		Date=Datum.value;
	      	Tag=Date.substring(0,2); 
	      	Monat=Date.substring(3,5); 
	      	Jahr=Date.substring(6,10); 
	
	  	if (parseInt(Tag,10)<1 || parseInt(Tag,10)>31)
		{	
			return ' Tag '+ Tag + ' ist nicht richtig im Datum '+ Datum.value;
		}
	  	if (parseInt(Monat,10)<1 || parseInt(Monat,10)>12)
		{	
			return ' Monat '+ Monat + ' ist nicht richtig im Datum '+ Datum.value;
		}
	  	if (parseInt(Jahr,10)<2000 || parseInt(Jahr,10)>3000)
		{	
			return ' Jahr '+ Jahr + ' ist nicht richtig im Datum '+ Datum.value;
		}

  	return false;
	}		
	
	
	function deleteEntry(id)
	{
		if(confirm("Soll dieser Eintrag wirklich gelöscht werden?") == true)
		{
			document.location.href = '<?php echo $_SERVER['PHP_SELF'];?>?btnDel=y&news_id=' + id;
		}
	}

	function editEntry(id)
	{
		document.location.href = '<?php echo $_SERVER['PHP_SELF'];?>?btnRead=y&news_id=' + id;
	}
	
</script>
</head>

<body onLoad="focusFirstElement();">
<form onsubmit="if (!plausibElement()) return false;" name="NewsEntry" target="_self" action="<?php echo $_SERVER['PHP_SELF'];?>"  method="post" enctype="multipart/form-data">
<table class="tabcontent" id="inhalt">
  <tr>
	    <td class="tdwidth10">&nbsp;<a name="top" >&nbsp;</a></td>
	    <td>
		   <table class="tabcontent">

		      <tr><td class="ContentHeader"><font class="ContentHeader">&nbsp;Verwaltungstools - Newsverwaltung</font></td></tr>
		      <tr><td class="ContentHeader2">&nbsp;<?php echo (isset($news_id) && $news_id != ''?'Eintrag &auml;ndern':'Neuen Eintrag erstellen'); ?></td></tr>
			  <tr>
			    <td>
				  <table class="tabcontent">
				    <tr>
					  <td width="65">Verfasser:</td>
					  <td><input class="TextBox" style="color:black;background-color:#FFFCF2;border : 1px solid Black;" type="text" name="verfasser" size="30"<?php if(isset($news_id) && $news_id != "") echo ' value="'.$news->verfasser.'"'; ?>></td>
					  <td>Sichtbar ab:</td>
					  <td><input class="TextBox" style="color:black;background-color:#FFFCF2;border : 1px solid Black;" type="text" name="datum" size="10" value="<?php if(isset($news_id) && $news_id != "") echo date('d.m.Y',strtotime(strftime($news->datum))); else echo date('d.m.Y'); ?>"></td>
				    </tr>
					<tr>
					  <td>Titel:</td>
					  <td><input class="TextBox" style="color:black;background-color:#FFFCF2;border : 1px solid Black;" type="text" name="betreff" size="30"<?php if(isset($news_id) && $news_id != "") echo ' value="'.$news->betreff.'"'; ?>></td>
					  <td>Sichtbar bis (optional):</td>
					  <td><input type="text" class="TextBox" name="datum_bis" size="10" value="<?php if(isset($news_id) && $news_id != "" && $news->datum_bis!='') echo date('d.m.Y',strtotime(strftime($news->datum_bis))); else echo ''; ?>"></td>
					</tr>
					<tr>
					<td colspan="2">Bitte geben Sie hier Ihre Nachricht ein:</td>
					<?php
					  if($rechte->isBerechtigt('admin',0) || $rechte->isBerechtigt('assistenz',0))
					  {
					?>
						  <td>Senat:</td>
						  <td><input type="checkbox" name="chksenat" <?php if(isset($news_id) && $news_id!="" && $news->fachbereich_kurzbz=='Senat') echo ' checked'?>></td>
					<?php
					  }
					?>
				    </tr>
				</table>
				</td>
			  </tr>
			  <tr>
			  	<td><textarea class="TextBox" style="color:black;background-color:#FFFCF2;border : 1px solid Black;width: 99%; heigth: 166px;"   name="text" rows="10" cols="100" maxlength="1999"><?php if(isset($news_id) && $news_id != "") echo mb_eregi_replace("<br>", "\r\n", $news->text); ?></textarea></td>
			  </tr>
			  <tr>
			  	<td id="error" class="error">&nbsp;<?php echo $error; ?></td>
			  </tr>
			  <tr>
			  	<td nowrap>
					<table>
						<tr>
							<td>
						        <input type="submit" name="btnSend" value="Abschicken">&nbsp;
								<input type="reset" name="btnCancel" value="<?php echo (isset($news_id) && $news_id !=''?'Abbrechen':'Zur&uuml;cksetzen'); ?>" onClick="document.location.href='<?php echo $_SERVER['PHP_SELF']; ?>';">&nbsp;
						  		<input type="hidden" name="news_id" value="<?php echo $news_id;?>">	
							</td>
							<td style="color:black;background-color:#FFFCF2;border : 1px solid Black;">&nbsp;&nbsp;&nbsp;</td><td>Pflichtfelder</td>
							
						</tr>
					</table>
				</td>					
			  </tr>
		    </table>
		</td>
		<td class="tdwidth30">&nbsp;</td>
	  </tr>
	</table>
</form>
<?php

	// Einlesen News
	$all=true;

	$maxnews=(defined('MAXNEWS')?MAXNEWS:5);
	$maxalter=(defined('MAXNEWSALTER')?MAXNEWSALTER:30);		

	$maxalter=0;		
	
	$studiengang_kz=trim((isset($_REQUEST['studiengang_kz']) ? $_REQUEST['studiengang_kz']:0));
	$semester=trim((isset($_REQUEST['semester']) ? $_REQUEST['semester']:null));
	$fachbereich_kurzbz=trim((isset($_REQUEST['fachbereich_kurzbz']) ? $_REQUEST['fachbereich_kurzbz']:'*'));

#org news_entry.php	if (!$news->getnews(0,0,null, true, '*', 0))
	if (!$news->getnews($maxalter, $studiengang_kz, $semester, $all, $fachbereich_kurzbz, $maxnews))
		die($news->errormsg);	
		
	// Datenlesen OK - in Tabellenform anzeigen
	if(count($news->result)<1)
		exit('Zur Zeit gibt es keine aktuellen News!');
?>

<table class="tabcontent" id="inhalt">
  <tr>
    <td class="tdwidth10">&nbsp;</td>
    <td><table class="tabcontent">
        <tr>
	         <td>
		  	<table class="tabcontent">
			  <?php
				$i=0;
				foreach($news->result as $row)
				{
					$datum = date('d.m.Y',strtotime(strftime($row->datum)));

					echo '<tr>';

					$i++; // Zeilenwechsel - Counter
					if($i % 2 != 0)
						echo '<td class="MarkLine">';
					else
						echo '<td>';
					echo '  <table class="tabcontent">';
					echo '    <tr>';
					echo '      <td nowarp>';
					echo $datum.'&nbsp;'.$row->verfasser;
					echo '      </td>';
					echo '		<td align="right" nowrap>';
					echo '		  <a onClick="editEntry('.$row->news_id.');">Editieren</a>, <a onClick="deleteEntry('.$row->news_id.');">L&ouml;schen</a>, <a href="#top" >Top</a>';
					echo '		</td>';
					echo '    </tr>';
					echo '	  <tr>';
					echo '		<td>&nbsp;</td>';
					echo '	  </tr>';
					echo '  </table>';
					echo '  <strong>'.$row->betreff.'</strong><br>'.$row->text.'</td>';
					echo '</tr>';
					
					echo '<tr>';
					echo '  <td>&nbsp;</td>';
					echo '</tr>';
				}
			  ?>
			</table>
		  </td>
        </tr>
    </table></td>
    <td class="tdwidth30">&nbsp;</td>
  </tr>
</table>
<a href="#top" >&nbsp;Top</a>

</body>
</html>
