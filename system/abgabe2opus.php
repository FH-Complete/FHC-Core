<?php
/* Copyright (C) 2009 Technikum-Wien
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
 * Authors: Christian Paminger 		<christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher 	<andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl 			<rudolf.hangl@technikum-wien.at>,
 *          Gerald Simane-Sequens 	<gerald.simane-sequens@technikum-wien.at>
 *
 *******************************************************************************************************
 *				abgabe2opus.php
 * 		abgabe2opus kopiert neue Abgaben ins opus
 *******************************************************************************************************/

require_once('../config/cis.config.inc.php');
require_once('../include/datum.class.php');
require_once('../include/mail.class.php');
require_once("../opus/lib/stringValidation.php");
require_once('../opus/lib/opus.class.php');

	//$db_obj = new basis_db();
		
	// zugriff auf mysql-datenbank
	if (!$conn_ext=mysql_pconnect (OPUS_SERVER, OPUS_USER, OPUS_PASSWD))
		die('Fehler beim Verbindungsaufbau!');
	mysql_select_db(OPUS_DB, $conn_ext);
	
	//zugriff auf pg-datenbank
	$conn_str='host='.DB_HOST.' port='.DB_PORT.' dbname='.DB_NAME.' user='.DB_USER.' password='.DB_PASSWORD;
	//Connection Herstellen
	if(!$db_conn = pg_connect($conn_str))
		die('Fehler beim Oeffnen der Datenbankverbindung');

mysql_set_charset('utf8',$conn_ext);
/*
$qry = "SET CLIENT_ENCODING TO 'WIN1252';";
			
if(!pg_query($db_conn,$qry))
{
	die('Encoding konnte nicht gesetzt werden');
}
*/

$datum_obj = new datum();
//$jahr='';
//$source_opus=''; 
$fehler='';
$fehler1='';
$error=false;
$begutachter1='';
$begutachter2='';
$verfasser='';
$abgabedatum='';
$datum='';
$institut='';
$typ='';
$bereich=1;
$stg='';
$row_opus=0;
$opus_url=OPUS_PATH_PAA;			
$url_paa=PAABGABE_PATH;
$kopiert='';
$ii=0;

function indexdatei($source_opus, $fd)
{
	$la="de";
	$search_creator='';
	$startfile='';
	$dir_array=array();
	$mod_gpg='';
	$publisher_faculty='';
	$advisor='';
	$date_accepted='';
	//require_once'../opus/lib/opus.class.php';
	$opus = new OPUS('../opus/lib/opus.conf');
	$php = $opus->value("php");
	$db = $opus->value("db");
	$opustable = $opus->value("opus_table");
	$url = $opus->value("url");
	$mod_checksum = $opus->value("mod_checksum");
	$projekt = $opus->value("projekt");
	$url_anzeigen = $opus->value("url_anzeigen");
	$urn_anzeigen = $opus->value("urn_anzeigen");
	$lic_active = $opus->value("license_active");
	$doku_pfad = $opus->value("doku_pfad");
	$statistik = $opus->value("statistik");
	$awstats_url = $opus->value("awstats_url");
	$awstats_config = $opus->value("awstats_config");
	$empfehlen = $opus->value("empfehlen");
	$mehrsprachig = $opus->value("mehrsprachig");
	$google_scholar = $opus->value("google_scholar");
	$url_google_scholar = $opus->value("url_google_scholar");
	# BD, 16.9.05: steht nun in frontdoor.conf
	# $titel = $opus->value("titel_frontdoor");
	# $ueberschrift = $opus->value("ueberschrift_frontdoor");
	// Anfang Collections
	$coll_anzeigen = $opus->value("coll_anzeigen");
	// Ende Collections
	$opustable_autor = $opustable . "_autor";
	$opustable_inst = $opustable . "_inst";
	$opustable_diss = $opustable . "_diss";
	$opustable_subject_type = $opustable . "_subject_type";
	$opustable_sr = $opustable . "_schriftenreihe";
	// Anfang Collections
	$opustable_coll = $opustable . "_coll";
	// Ende Collections
	// Social-Bookmarking-Schnittstellen
	$connotea_export = $opus->value("connotea_export");
	$delicious_export = $opus->value("delicious_export");
	$social_bookmarking = 0;
	if ($connotea_export == 1 || $delicious_export == 1) 
	{
	    $social_bookmarking = 1;
	}
	/*if ($_REQUEST["show_connotea"]) 
	{
	    $_SESSION["show_connotea"] = true;
	}*/
	// Ende Social-Bookmarking-Schnittstellen
	//require_once("../opus/lib/stringValidation.php");
	//$source_opus = $_REQUEST['source_opus'];
	if (!_is_valid($source_opus, 1, 10, "[0-9]+")) 
	{
	    die("Fehler in Parameter source_opus");
	}
	# A.Maile 10.2.05: Sprache einlesen aus opus.conf, falls nicht gesetzt
	if (!$la) 
	{
	    $la = $opus->value("la");
	}
	# O. Marahrens: Automatisch alle Texte aus der Textdatei holen
	$texte = new OPUS("../opus/texte/$la/frontdoor.conf");
	foreach($texte->getValues() as $k => $v) {
	    $$k = $v;
	}

	$res = $opus->query("SELECT * FROM $opustable WHERE source_opus = $source_opus ");
	$num_res = $opus->num_rows($res);
	if ($num_res > 0) 
	{
	    $mrow = $opus->fetch_row($res);
	    $title_orig = $mrow[0];
	    $title = htmlspecialchars($mrow[0]);
	    $creator_corporate = $mrow[1];
	    $subject_swd = htmlspecialchars($mrow[2]);
	    $description = $mrow[3];
	    # Abstract fuer die Metadaten html-codieren
	    $description_meta = htmlspecialchars($mrow[3]);
	    $publisher_university = $mrow[4];
	    $contributors_name = $mrow[5];
	    $contributors_corporate = $mrow[6];
	    $date_year = $mrow[7];
	    $date_creation = $mrow[8];
	    $date_modified_old = $mrow[9];
	    $type = $mrow[10];
	    $source_opus = $mrow[11];
	    $source_title = htmlspecialchars($mrow[12]);
	    $source_swb = $mrow[13];
	    $language = $mrow[14];
	    $verification = $mrow[15];
	    $subject_uncontrolled_german = htmlspecialchars($mrow[16]);
	    $subject_uncontrolled_english = htmlspecialchars($mrow[17]);
	    $title_en = $mrow[18];
	    $description2 = $mrow[19];
	    # Abstract fuer die Metadaten html-codieren
	    $description2_meta = htmlspecialchars($mrow[19]);
	    $subject_type = $mrow[20];
	    $date_valid = $mrow[21];
	    $description_lang = $mrow[22];
	    $description2_lang = $mrow[23];
	    $sachgruppe_ddc = $mrow[24];
	    $urn = $mrow[25];
	    $bereich_id = $mrow[26];
	    if ($lic_active >= 2) 
	    {
	        $lic = $mrow[27];
	    }
	    $isbn = $mrow[28];
	    $bem_extern = $mrow[30];
	    //********************************************************************************************************************************
	    //Änderung TW
	    $issn = $mrow[32];
	    $ac_nr = $mrow[33];
	    $studiengang = $mrow[34];
	    $seitenanzahl = $mrow[35];
	    $datum = $mrow[36];
	    $gutachter1 = $mrow[37];
	    $gutachter2 = $mrow[38];
	    $studiensemester = $mrow[39];
	    //********************************************************************************************************************************
	    $opus->free_result($res);
	    /***** Schriftenreihe Start *****/
	    $sr_id = "";
	    $sr_band = "";
	    $res = $opus->query("SELECT * FROM $opustable_sr WHERE source_opus = $source_opus ");
	    $num_res = $opus->num_rows($res);
	    if ($num_res > 0) 
	    {
	        $mrow = $opus->fetch_row($res);
	        $sr_id = $mrow[1];
	        $sr_band = $mrow[2];
	        $res = $opus->query("select name from schriftenreihen where sr_id = '$sr_id' ");
	        $mrow = $opus->fetch_row($res);
	        $sr_name = $mrow[0];
	        $opus->free_result($res);
	    }
	    /***** Schriftenreihe Stop *****/
	    /* Bei Dissertation (type 8) und Habilitation (type 24) zusaetzlich */
	    /* Tag der muendlichen Pruefung (bzw. des Kollquiums) und Hauptberichter anzeigen */
	    if ($type == "8" || $type == "24") 
	    {
	        $res = $opus->query("SELECT date_accepted, advisor, publisher_faculty, title_de FROM $opustable_diss WHERE source_opus = $source_opus");
	        $anz = $opus->num_rows($res);
	        if ($anz > 0) 
	        {
	            $mrow = $opus->fetch_row($res);
	            $date_accepted = $mrow[0];
	            $advisor = htmlspecialchars($mrow[1]);
	            $faculty_nr = $mrow[2];
	            $title_de = $mrow[3];
	        }
	        $opus->free_result($res);
	        $res = $opus->query("SELECT fakultaet from faculty_$la where nr = '$faculty_nr'");
	        $mrow = $opus->fetch_row($res);
	        $publisher_faculty = $mrow[0];
	        $opus->free_result($res);
	    }
	    $jahr = date("Y", $date_creation);
	    $res = $opus->query("SELECT name FROM institute_$la i, $opustable_inst oi WHERE i.nr=oi.inst_nr and oi.source_opus = '$source_opus'");
	    $instnum = $opus->num_rows($res);
	    $i = 0;
	    while ($i < $instnum) 
	    {
	        $i++;
	        $mrow = $opus->fetch_row($res);
	        $inst[$i] = $mrow[0];
	    }
	    $opus->free_result($res);
	    // Anfang Collections
	    if ($coll_anzeigen == "true") 
	    {
	        $res = $opus->query("SELECT c.coll_id FROM collections as c, $opustable_coll as oc WHERE c.coll_id = oc.coll_id and oc.source_opus = '$source_opus'");
	        $collnum = $opus->num_rows($res);
	        $i = 0;
	        while ($i < $collnum) 
	        {
	            $i++;
	            $mrow = $opus->fetch_row($res);
	            $coll[$i] = $mrow[0];
	        }
	        $opus->free_result($res);
	    }
	    // Ende Collections
	    $res = $opus->query("SELECT sprache FROM language_$la WHERE code='$language'");
	    $mrow = $opus->fetch_row($res);
	    $sprache = ucfirst($mrow[0]);
	    $opus->free_result($res);
	    $res = $opus->query("SELECT sprache FROM language_$la WHERE code='$description_lang'");
	    $mrow = $opus->fetch_row($res);
	    $sprache_description = ucfirst($mrow[0]);
	    $opus->free_result($res);
	    if ($description2_lang != "") 
	    {
	        $res = $opus->query("SELECT sprache FROM language_$la WHERE code='$description2_lang'");
	        $mrow = $opus->fetch_row($res);
	        $sprache_description2 = ucfirst($mrow[0]);
	        $opus->free_result($res);
	    }
	    $res = $opus->query("SELECT dokumentart FROM resource_type_$la WHERE typeid='$type'");
	    $mrow = $opus->fetch_row($res);
	    $dokumentart = $mrow[0];
	    $opus->free_result($res);
	    # Start Lizenzvertrag
	    # Link auf Lizenz ausgeben, falls Lizenz-Modul aktiv und Feld lic belegt.
	    # Falls Lizenz-Modul aktiv, aber Feld lic nicht belegt (alte OPUS-Bestaende)
	    # dann Link auf urheberrecht.php ausgeben.
	    if ($lic_active >= 2) 
	    {
	        $res = $opus->query("SELECT longname, link, logo, desc_text, desc_html FROM license_$la WHERE shortname='$lic'");
	        $licnum = $opus->num_rows($res);
	        if ($licnum == 1) 
	        {
	            $mrow = $opus->fetch_row($res);
	            $licname = $mrow[0];
	            $liclink = $mrow[1];
	            $liclogo = $mrow[2];
	            $licdesc = $mrow[3];
	            $licdesc_html = $mrow[4];
	            $liclink_head = "<a href=\"$liclink?la=$la\" target=\"_blank\">$lizenz</a><p />";
	        } 
	        else 
	        {
	            $liclink_head = "<a href=\"" . $doku_pfad . "/urheberrecht.php?la=$la\">$text1</a><p />";
	        }
	        $opus->free_result($res);
	    } 
	    else 
	    {
	        $liclink_head = "<a href=\"" . $doku_pfad . "/urheberrecht.php?la=$la\">$text1</a><p />";
	    }
	    # Ende Lizenzvertrag
	    # Zugriff auf Volltexte weltweit/campusweit/weitere Bereiche
	    # Falls in Altbestaenden (< Opus 3.0) kein Bereich angegeben ist,
	    # wird bereich_id auf 1 gesetzt = freier Zugriff auf die Dokumente,
	    # sonst kommt eine Fehlermeldung der Datenbank
	    if ($bereich_id == "" || $bereich_id == 0) 
	    {
	        $bereich_id = 1;
	    }
	    $res = $opus->query("SELECT bereich, volltext_pfad, volltext_url FROM bereich_$la WHERE bereich_id = $bereich_id");
	    $num = $opus->num_rows($res);
	    if ($num > 0) 
	    {
	        $mrow = $opus->fetch_row($res);
	        $bereich = $mrow[0];
	        $volltext_pfad = $mrow[1];
	        $volltext_url = $mrow[2];
	        $opus->free_result($res);
	    }
	    # Ende Zugriff auf Volltexte weltweit/campusweit/weitere Bereiche
	    $autor = $opus->query("SELECT creator_name, reihenfolge FROM $opustable_autor WHERE source_opus = $source_opus order by reihenfolge");
	    $anzahl_creator_name = $opus->num_rows($autor);
	    $i = 0;
	    while ($i < $anzahl_creator_name) 
	    {
	        $mrow = $opus->fetch_row($autor);
	        $creator_name = $mrow[0];
	        $creator_name = htmlspecialchars($creator_name);
	        $search_creator.= " ; " . $creator_name;
	        $i++;
	    }
	    $search_creator = substr($search_creator, 3);
	    # Titel und Autoren des Dokuments sollen in <title> erscheinen
	    $titel = "$projekt - $title - $search_creator";
	    # Annette Maile, 18.3.05 Design aus lib/design.php einlesen
	    //require ("../../lib/design.$php");
	    //$design = new design;
	    //$design->head_titel($titel);
	    # Ausgabe einiger DC-Metadaten
	    fwrite($fd,"<META NAME=\"DC.Title\" CONTENT=\"$title\">\n");
	    fwrite($fd,"<META NAME=\"title\" CONTENT=\"$title\">\n");
	    if ($anzahl_creator_name > 0) 
	    {
	        $opus->data_seek($autor, 0);
	        $i = 0;
	        while ($i < $anzahl_creator_name) 
	        {
	            $mrow = $opus->fetch_row($autor);
	            $creator_name = $mrow[0];
	            $creator_name = htmlspecialchars($creator_name);
	            fwrite($fd,"<META NAME=\"DC.Creator\" CONTENT=\"$creator_name\">\n");
	            $search_creator.= " ; " . $creator_name;
	            $i++;
	        }
	    }
	    if ($search_creator != "") 
	    {
	        fwrite($fd,"<META NAME=\"author\" CONTENT=\"$search_creator\">\n");
	    }
	    $subject = $subject_swd;
	    if ($subject_uncontrolled_german != "") 
	    {
	        $subject.= " , $subject_uncontrolled_german";
	    }
	    if ($subject_uncontrolled_english != "") 
	    {
	        $subject.= " , $subject_uncontrolled_english";
	    }
	    fwrite($fd,"<META NAME=\"DC.Subject\" CONTENT=\"$subject\">\n");
	    fwrite($fd,"<META NAME=\"keywords\" CONTENT=\"$subject\">\n");
	    fwrite($fd,"<META NAME=\"DC.Identifier\" CONTENT=\"$volltext_url/$jahr/$source_opus/\">\n");
	    //*******************************************************************************************************************************************
		//Änderung TW
		if ($urn && $urn_anzeigen) 
	    {
		    if ($urn != "") 
		    {
		        fwrite($fd,"<META NAME=\"DC.Identifier\" CONTENT=\"$urn\">\n");
		    }
		}
	    //*******************************************************************************************************************************************
	    fwrite($fd,"<META NAME=\"DC.Description\" CONTENT=\"$description_meta \n$description2_meta\">\n");
	    fwrite($fd,"<META NAME=\"description\" CONTENT=\"$description_meta \n$description2_meta\">\n");
	    # Ende der DC-Metadaten
	    //$design->head_ueberschrift($ueberschrift, $la);
	    # Button fuer Anzeige des Skripts in weiteren Sprachen),
	    # falls mehrere Sprachen angeboten werden.
	    if ($mehrsprachig == 1) 
	    {
	        $design->andere_sprache($la);
	    }
	    # Start Ausgabe der Frontdoor
	    fwrite($fd,"<P><FONT class=\"frontdoor\">$liclink_head \n");
	    # fwrite($fd,"Bitte beziehen Sie sich beim Zitieren dieses Dokumentes immer auf folgende<BR>\n");
	    # fwrite($fd,"$dokumentart zugaenglich unter:");
	    fwrite($fd,"$dokumentart $t_dokumentart_2<BR>\n");
	    # Wenn Urn vorhanden und urn_anzeigen auf 1 gesetzt ist, wird die Urn angezeigt.
	    # Wenn keine Urn vorhanden ist und url_anzeigen auf 2 gesetzt ist, dann Url anzeigen.
	    # Wenn url_anzeigen auf 1 gesetzt ist, dann immer Url anzeigen.
	    if ($urn && $urn_anzeigen) 
	    {
	        fwrite($fd,"URN: <a href=\"http://nbn-resolving.de/$urn\"><B>$urn</B></a><BR>\n");
	    } 
	    else 
	    {
	        if ($url_anzeigen == 2) 
	        {
	        	if (file_exists("$volltext_pfad/$jahr/$source_opus") == 1) 
	        	{
	            	fwrite($fd,"URL: <a href=\"$volltext_url/$jahr/$source_opus/\"><B>$volltext_url/$jahr/$source_opus/</B></a><BR>\n");
	        	}
	        }
	    }
	    if ($url_anzeigen == 1) 
	    {
	    	if (file_exists("$volltext_pfad/$jahr/$source_opus") == 1) 
	        {
	        	fwrite($fd,"URL: <a href=\"$volltext_url/$jahr/$source_opus/\"><B>$volltext_url/$jahr/$source_opus/</B></a><BR>\n");
	        }
	    }
	    fwrite($fd,"</FONT><HR>");
	    fwrite($fd,"<P>\n");
	    fwrite($fd,"<B>$t_titel1:</B> $title\n");
	    if ($title_en != "") 
	    {
	        fwrite($fd,"<P><B>$t_titel2:</B> $title_en\n");
	    }
	    /*if ($title_de != "") 
	    {
	        fwrite($fd,"<P><B>$title_de</B>\n");
	    }*/
	    fwrite($fd,"<P> \n");
	    include ("../opus/lib/font.html");
	    $link = "$url/ergebnis.$php?suchart=teil&Lines_Displayed=10&sort=o.date_year+DESC%2C+o.title&suchfeld1=freitext&suchwert1=&opt1=AND&opt2=AND&suchfeld3=date_year&suchwert3=&startindex=0&page=0&dir=2&suche=&suchfeld2=oa.person&suchwert2=";
	    #$res = $opus->query("SELECT creator_name, reihenfolge from opus_autor where source_opus = $source_opus order by reihenfolge ");
	    if ($anzahl_creator_name > 0) 
	    {
	        $opus->data_seek($autor, 0);
	        $mrow = $opus->fetch_row($autor);
	        $creator_name = $mrow[0];
		# A. Maile, 6.8.2007: Ersten Autor speichern f&uuml;r Google Scholar
		$first_creator = $creator_name;
	        $person = htmlspecialchars(rawurlencode($creator_name));
	        fwrite($fd,"<B>Autor(in): <A HREF=\"$link$person\">$creator_name</A></B>");
	        $pod_creator_names = $person;
	        if ($anzahl_creator_name > 1) 
	        {
	            $i = 1;
	            while ($i < $anzahl_creator_name) 
	            {
	                $i++;
	                $mrow = $opus->fetch_row($autor);
	                $creator_name = $mrow[0];
	                $person = htmlspecialchars(rawurlencode($creator_name));
	                fwrite($fd," ; \n<B>Autor(in): <A HREF=\"$link$person\">$creator_name</A></B>");
	                $pod_creator_names.= " ; " . $person;
	            }
	        }
	    }
	    $opus->free_result($autor);
	    fwrite($fd,"<P><FONT class=\"fr_font_klein\">");
	    if ($contributors_name != "") 
	    {
	        # fwrite($fd,"<BR>Weitere Beteiligte (Hrsg. etc.): $contributors_name \n");
	        fwrite($fd,"$t_weitere_beteiligte $contributors_name <BR> \n");
	    }
	    if ($creator_corporate != "") 
	    {
	        fwrite($fd,"$creator_corporate <BR> \n");
	    }
	    fwrite($fd,"</B> \n");
	    fwrite($fd,"<P>\n");
	    fwrite($fd,"<TABLE> \n");
	    if ($source_title != "") 
	    {
	        fwrite($fd,"<TR> \n<TD class=\"frontdoor\" valign=\"top\">");
	        fwrite($fd,"<B>$t_quelle</B></TD> \n");
	        fwrite($fd,"<TD></TD><TD class=\"frontdoor\" valign=\"bottom\">");
	        fwrite($fd,"<B>($date_year)</B> <I>$source_title</I> </TD>\n");
	        fwrite($fd,"</TR> \n");
	    }
	    /* Es werden die Formate, die in der Tabelle Format im Feld extension stehen */
	    /* in der Frontdoor angezeigt. Es wird geschaut, ob das jeweilige Directory  */
	    /* exisitiert und die darin enthaltenen Dateien in einer Tabelle ausgegeben  */
	    $format = $opus->query("select extension from format where name <> 'Originalformat' ");
	    $num_format = $opus->num_rows($format);
	    $f = 0;
	    while ($f < $num_format) 
	    {
	        $f++;
	        $mrow = $opus->fetch_row($format);
	        $ext = $mrow[0];
	        if (file_exists("$volltext_pfad/$jahr/$source_opus/$ext") == 1) 
	        {
	            /* Falls Startfile .*.anzeigen vorhanden, dann nur dieses anzeigen */
	            /* hauptsaechlich bei html */
	            $handle = opendir("$volltext_pfad/$jahr/$source_opus/$ext");
	            while ($file = readdir($handle)) 
	            {
	                if (preg_match("/\.anzeigen/", $file)) 
	                {
	                    $startfile = $file;
	                }
	            }
	            closedir($handle);
	            if ($startfile != "") 
	            {
	                /* fuehrenden Punkt und .anzeigen entfernen = Startfile des html-Dokuments */
	                $startfile = str_replace(".anzeigen", "", $startfile);
	                $startfile = substr($startfile, 1);
	                fwrite($fd,"<TR>\n<TD class=\"frontdoor\" valign=\"top\">");
	                fwrite($fd,"<B>$ext-$t_format:</B> \n</TD>\n");
	                fwrite($fd,"\n<TD></TD><TD><TABLE BORDER=0>\n<TR>\n");
	                fwrite($fd,"<TD class=\"frontdoor\" valign=\"bottom\">");
	                fwrite($fd,"<A HREF=\"$volltext_url/$jahr/$source_opus/$ext/$startfile\">Dokument1.$ext </A>\n</TD>\n");
	                fwrite($fd,"\n</TR>\n</TABLE>\n");
	                fwrite($fd,"</TD></TR>\n");
	            } 
	            else 
	            {
	                $i = 1;
	                $j = 1;
	                /* Verzeichnis in Array einlesen und sortieren */
	                $handle = opendir("$volltext_pfad/$jahr/$source_opus/$ext");
	                while ($file = readdir($handle)) 
	                {
	                    if ($file != "." && $file != "..") 
	                    {
	                        /* Dateien, die mit .bem_ beginnen sind Bemerkungen zu den Dateien, */
	                        /* daher werden nur Dateien, die nicht mit .bem_ anfangen in der    */
	                        /* Frontdoor aufgelistet. Gepackte Dateien werden separat aufgelistet                                    	    */
	                        if (!preg_match("/^\.bem_/", $file) && !preg_match("/\.zip/i", $file) && !preg_match("/\.gz/i", $file)) {
	                            $dir_array[count($dir_array) ] = $file;
	                        }
	                    }
	                }
	                closedir($handle);
	                $num = count($dir_array);
	                if ($num > 0) 
	                {
	                    sort($dir_array);
	                    fwrite($fd,"<TR>\n<TD class=\"frontdoor\" valign=\"top\">");
	                    fwrite($fd,"<B>$ext-$t_format:</B> \n</TD>\n");
	                    fwrite($fd,"\n<TD></TD><TD class=\"frontdoor\"><TABLE BORDER=0>\n<TR>\n");
	                    $k = 0;
	                    while ($k < $num) 
	                    {
	                        $filename = array_shift($dir_array);
	                        $bem_file = "";
	                        if (file_exists("$volltext_pfad/$jahr/$source_opus/$ext/.bem_$filename")) 
	                        {
	                            $fd = fopen("$volltext_pfad/$jahr/$source_opus/$ext/.bem_$filename", "r");
	                            $bem_file = fread($fd, filesize("$volltext_pfad/$jahr/$source_opus/$ext/.bem_$filename"));
	                            fclose($fd);
	                        }
	                        $file = "$volltext_pfad/$jahr/$source_opus/$ext/$filename";
	                        $array = explode(".", $filename);
	                        $last = count($array) -1;
	                        $ext2 = strtolower($array[$last]);
	                        $size = stat($file);
	                        $filesize = $size[7];
	                        $filesize = $filesize/1024;
	                        if ($filesize < 1) 
	                        {
	                            $filesize = number_format($filesize, 1, ",", ".");
	                        } 
	                        else 
	                        {
	                            $filesize = number_format($filesize, 0, ",", ".");
	                        }
	                        $format2 = $opus->query("select extension from format where extension like '$ext2%' ");
	                        $num_format2 = $opus->num_rows($format2);
	                        if ($num_format2 > 0) 
	                        {
	                            if ($i > 3) 
	                            {
	                                fwrite($fd,"\n</TR>\n<TR>\n");
	                                $i = 1;
	                            }
	                            fwrite($fd,"<TD class=\"frontdoor\" valign=\"top\">");
	                            fwrite($fd,"<A HREF=\"$volltext_url/$jahr/$source_opus/$ext/$filename\" target=\"new\">Dokument $j.$ext2 ($filesize KB) </A> ");
	                            if ($bem_file != "") 
	                            {
	                                fwrite($fd,"($bem_file)");
	                            }
	                            fwrite($fd,"\n</TD>\n");
	                            $i++;
	                            $j++;
	                        }
	                        $k++;
	                    }
	                    fwrite($fd,"\n</TR>\n</TABLE>\n");
	                    fwrite($fd,"</TD></TR>\n");
	                }
	            }
	            /* gezipptes File mit Endung zip bzw. gz einlesen */
	            $g = 0;
	            while ($g < 2) 
	            {
	                $g++;
	                if ($g == 1) 
	                {
	                    $z = "zip";
	                }
	                if ($g == 2) 
	                {
	                    $z = "gz";
	                }
	                $handle = opendir("$volltext_pfad/$jahr/$source_opus/$ext");
	                while ($file = readdir($handle)) 
	                {
	                    if (preg_match("/\.$z/i", $file)) 
	                    {
	                        if (!preg_match("/^\.bem_/", $file)) 
	                        {
	                            $line = "$volltext_pfad/$jahr/$source_opus/$ext/$file";
	                            $size = stat($line);
	                            $filesize = $size[7];
	                            $filesize = $filesize/1024;
	                            if ($filesize < 1) 
	                            {
	                                $filesize = number_format($filesize, 1);;
	                            } 
	                            else 
	                            {
	                                $filesize = number_format($filesize);
	                            }
	                            $bem_file = "";
	                            if (file_exists("$volltext_pfad/$jahr/$source_opus/$ext/.bem_$file")) 
	                            {
	                                $fd = fopen("$volltext_pfad/$jahr/$source_opus/$ext/.bem_$file", "r");
	                                $bem_file = fread($fd, filesize("$volltext_pfad/$jahr/$source_opus/$ext/.bem_$file"));
	                                fclose($fd);
	                            }
	                            fwrite($fd,"<TR>\n<TD class=\"frontdoor\">");
	                            fwrite($fd,"<B>$ext gepackt:</B> \n</TD>\n");
	                            fwrite($fd,"\n<TD></TD><TD><TABLE BORDER=0>\n<TR>\n");
	                            fwrite($fd,"<TD class=\"frontdoor\">");
	                            fwrite($fd,"<A HREF=\"$volltext_url/$jahr/$source_opus/$ext/$file\">Dokument1.$z ($filesize KB) </A> ");
	                            if ($bem_file != "") 
	                            {
	                                fwrite($fd,"($bem_file)");
	                            }
	                            fwrite($fd,"\n</TD>\n");
	                            fwrite($fd,"\n</TR>\n</TABLE>\n");
	                            fwrite($fd,"</TD></TR>\n");
	                        }
	                    }
	                }
	                closedir($handle);
	            }
	        }
	    }
	    $opus->free_result($format);
	    fwrite($fd,"</table>");
	    fwrite($fd,"<BR> \n");
	
	    # Checksummen-Ueberpruefung
	    if ($mod_gpg == 1 || $mod_checksum == 1) 
	    {
	        fwrite($fd,"<img src= \"$url/Icons/blind.gif\" border=\"0\" width=\"2\" height=\"1\"><A HREF=\"$url/dok_unversehrtheit.php?la=$la&source_opus=$source_opus\" title=\"$unv\"><img src=\"$url/Icons/unversehrt.jpg\" border=\"0\"></a> \n");
		}
	
	    # A. Maile 11.10.05: Link zur Dokumentempfehlung
	    if ($empfehlen == 1) 
	    {
	        fwrite($fd,"<img src= \"$url/Icons/blind.gif\" border=\"0\" width=\"2\" height=\"1\"><A HREF=\"$url/mailform.php?la=$la&bereich_id=$bereich_id&jahr=$jahr&source_opus=$source_opus\" title=\"$t_empfehlen\"><img src=\"$url/Icons/hand.jpg\" border=\"0\"></a> \n");
	    }
	
	    # A. Maile 11.10.05: Link zur Statistik-Anzeige
	    if ($statistik == 1) 
	    {
	        fwrite($fd,"<img src= \"$url/Icons/blind.gif\" border=\"0\" width=\"2\" height=\"1\"><a href=\"$url/statistik.php?source_opus=$source_opus&title=".htmlspecialchars(rawurlencode($title))."&la=$la\" TARGET=_blank title=\"$t_statistik\"><img src=\"$url/Icons/statistik.jpg\" border=\"0\"></A>  \n");
	        # Fuer Statistik mit awstats folgende Zeile auskommentieren.
	        #fwrite($fd,"<img src= \"$url/Icons/blind.gif\" border=\"0\" width=\"2\" height=\"1\"><A HREF=\"$awstats_url?urlfilter=/$source_opus/&urlfilterex=&output=urldetail&config=$awstats_config&lang=de \" TARGET=_blank title=\"$t_statistik\"><img src=\"$url/Icons/statistik.jpg\" border=\"0\"></A> $t_statistik \n");
	    }
	
	    # O.Marahrens 30.03.07: Social-Bookmarking-Dienste
	    if ($social_bookmarking == 1) 
	    {
	        # O.Marahrens 02.02.07: Link zum Connotea-Bookmark
	        if ($connotea_export == 1) 
	        {
	            fwrite($fd,"<img src= \"$url/Icons/blind.gif\" border=\"0\" width=\"2\" height=\"1\"><!--<a href=\"" . $_SERVER["REQUEST_URI"] . "&show_connotea=1#connotea_interface\" title=\"$t_connotea_bookmark\">--><a href=\"$url/connotea.php?source_opus=$source_opus\" title=\"$t_connotea_bookmark\" onclick=\"window.open('$url/connotea.php?source_opus=$source_opus','connotea','toolbar=no,width=700,height=400'); return false;\"><img src=\"$url/Icons/connotea_icon.jpg\" border=\"0\" alt=\"$t_connotea_bookmark\" /></a> \n");
	        }
	        # U.Herb: del.icio.us-Bookmarking
	        if ($delicious_export == 1) 
	        {
	            fwrite($fd,"<img src= \"$url/Icons/blind.gif\" border=\"0\" width=\"2\" height=\"1\"><A HREF=\"http://del.icio.us/post\" title=\"$t_delicious_bookmark\"
	            onclick=\"window.open('http://del.icio.us/post?v=4&noui&jump=close&url='+encodeURIComponent(location.href)+
	            '&title='+encodeURIComponent(document.title),
	            'delicious','toolbar=no,width=700,height=400'); return false;\" alt=\"$t_delicious_bookmark\"><img src=\"$url/Icons/delicious.jpg\" border=\"0\" alt=\"$t_delicious_bookmark\" /></A> \n");
	        }
	    }
	    # A. Maile 6.8.2007: Link auf Google Scholar mit Suche nach exaktem Titel und Autor
	    if ($google_scholar == 1) 
	    {
	        fwrite($fd,"<img src= \"$url/Icons/blind.gif\" border=\"0\" width=\"2\" height=\"1\"><a href=\"$url_google_scholar%22" . rawurlencode(utf8_encode($title_orig)) . "%22&as_sauthors=%22" . rawurlencode(utf8_encode($first_creator)) . "%22\" target=\"new\" title=\"$t_google_scholar\"><img src=\"$url/Icons/google_scholar.jpg\" border=\"0\"></a> \n");
	    }
	
	    fwrite($fd,"<img src= \"$url/Icons/blind.gif\" border=\"0\" width=\"2\" height=\"1\">" .
	         "<a href=\"$url/biblio.php?source_opus=$source_opus&export=" .
	         "bibtex\" title=\"$t_bibtex\"><img src=\"$url/Icons/bibtex.jpg\" border=\"0\"></a> \n" .
	         "<img src= \"$url/Icons/blind.gif\" border=\"0\" width=\"2\" height=\"1\">" .
	    	 "<a href=\"$url/biblio.php?source_opus=$source_opus&export=ris\" title=\"$t_ris\">" .
	         "<img src=\"$url/Icons/ris.jpg\" border=\"0\"></a> \n");
	
	    fwrite($fd,"<hr> \n");
	    fwrite($fd,"<table>");
	    
	    if ($lic_active >= 1) 
	    {
	        $pod_active = $opus->value("pod_active");
	        if ($pod_active > 0 && $bereich_id == 1) { // check, ob lic pod erlaubt und Volltext frei zugaenglich
	            $res = $opus->query("SELECT pod_allowed FROM license_$la WHERE shortname='$lic'");
	            $podnum = $opus->num_rows($res);
	            if ($podnum == 1) 
	            {
	                $mrow = $opus->fetch_row($res);
	                if ($mrow[0] == 1) 
	                {
	                    fwrite($fd,"<TR> \n<TD class=\"frontdoor\">");
	                    fwrite($fd,"<B>$pod_linkname_1</B> </TD> \n");
	                    fwrite($fd,"<TD></TD><TD class=\"frontdoor\">\n<table border=\"0\"><tr><td class=\"frontdoor\">\n");
	                    $pod_info = $opus->value("pod_info");
	                    $tmp_url = "$volltext_url/$jahr/$source_opus/";
	                    $podlink = $pod_info . "?urn=" . urlencode($urn) . "&docurl=" . urlencode($tmp_url) . "&lic=" . $lic . "&aut=" . $pod_creator_names . "&tit=" . htmlspecialchars(rawurlencode($title)) . "&bereich_id=" . $bereich_id . "&la=" . $la;
	                    $pod_uselogos = $opus->value("pod_uselogos");
	                    $pod_partner_logo = $opus->value("pod_partner_logo");
	                    if (($pod_uselogos) AND (strlen($pod_partner_logo) > 0)) 
	                    {
	                        $podlogo_width = $opus->value("pod_logo_width");
	                        $podlogo_height = $opus->value("pod_logo_height");
	                        $podlogo_wh = "";
	                        if ($podlogo_width) 
	                        {
	                            $podlogo_wh.= " width=\"" . $podlogo_width . "\"";
	                        }
	                        if ($podlogo_height) 
	                        {
	                            $podlogo_wh.= " height=\"" . $podlogo_height . "\"";
	                        }
	                        fwrite($fd,"<a href=\"$podlink\" target=\"_blank\">\n");
	                        fwrite($fd,"<img src=\"" . $pod_partner_logo . "\" alt=\"POD-Logo\" border=\"0\"" . $podlogo_wh . ">");
	                        fwrite($fd,"</a>&nbsp;\n");
	                    }
	                    fwrite($fd,"<a href=\"$podlink\" target=\"_blank\">");
	                    fwrite($fd,"$pod_linkname_2 </a> \n</td>\n</tr>\n</table>\n</TD>\n");
	                    fwrite($fd,"</TR> \n");
	                }
	            }
	            $opus->free_result($res);
	        }
	    }
	
	    # Zugriff auf Volltexte weltweit/campusweit/weitere Bereiche
	    # Wenn Zugriffsbeschraenkung besteht ($bereich_id > 1)
	    # dann diese anzeigen.
	    if ($bereich_id > 1) 
	    {
	        fwrite($fd,"<TR> \n<TD class=\"frontdoor\" valign=\"top\">");
	        # fwrite($fd,"<B>Zugriffsbeschr&auml;nkung:</B></TD> \n");
	        fwrite($fd,"<B>$text3:</B></TD> \n");
	        fwrite($fd,"<TD></TD><TD class=\"frontdoor\" valign=\"bottom\">");
	        fwrite($fd,"$bereich </TD>\n");
	        fwrite($fd,"</TR> \n");
	    }
	    # Ende Zugriff auf Volltexte weltweit/campusweit/weitere Bereiche
	    if ($subject_swd != "") 
	    {
	        fwrite($fd,"<TR> \n<TD class=\"frontdoor\" valign=\"top\" width=\"130\">");
	        fwrite($fd,"<B>$schlagwoerter_swd</B></TD> \n");
	        fwrite($fd,"<TD></TD><TD class=\"frontdoor\" valign=\"bottom\">");
	        fwrite($fd,"$subject_swd </TD>\n");
	        fwrite($fd,"</TR> \n");
	    }
	    if ($subject_uncontrolled_german != "") 
	    {
	        fwrite($fd,"<TR>\n<TD class=\"frontdoor\" valign=\"top\">");
	        fwrite($fd,"<B>$schlagwoerter_frei</B></TD> \n");
	        fwrite($fd,"<TD></TD><TD class=\"frontdoor\" valign=\"bottom\">");
	        fwrite($fd,"$subject_uncontrolled_german </TD>\n");
	        fwrite($fd,"</TR> \n");
	    }
	    if ($subject_uncontrolled_english != "") 
	    {
	        fwrite($fd,"<TR> \n<TD class=\"frontdoor\" valign=\"top\">");
	        fwrite($fd,"<B>$schlagwoerter_engl</B></TD> \n");
	        fwrite($fd,"<TD></TD><TD class=\"frontdoor\" valign=\"bottom\">");
	        fwrite($fd,"$subject_uncontrolled_english </TD>\n");
	        fwrite($fd,"</TR> \n");
	    }
	    if ($subject_type != "") 
	    {
	        $opustable_subject_type = $opustable . "_" . $subject_type;
	        $res = $opus->query("SELECT class from $opustable_subject_type where source_opus = $source_opus");
	        $num = $opus->num_rows($res);
	        if ($num > 0) 
	        {
	            $mrow = $opus->fetch_row($res);
	            $class = $mrow[0];
	            $i = 1;
	            while ($i < $num) 
	            {
	                $i++;
	                $mrow = $opus->fetch_row($res);
	                $class = "$class , $mrow[0]";
	            }
	            $opus->free_result($res);
	            $res = $opus->query("SELECT name from klassifikation_$la where table_name = '$subject_type' ");
	            $mrow = $opus->fetch_row($res);
	            $class_name = $mrow[0];
	            $opus->free_result($res);
	            fwrite($fd,"<TR> \n<TD class=\"frontdoor\" valign=\"top\">");
	            fwrite($fd,"<B>$class_name:</B></TD> \n");
	            fwrite($fd,"<TD></TD><TD class=\"frontdoor\" valign=\"bottom\">");
	            fwrite($fd,"$class </TD>\n");
	            fwrite($fd,"</TR> \n");
	        }
	    }
	    //Anfang Collections
	    if ($coll_anzeigen == "true") 
	    {
	        $i = 0;
	        while ($i < $collnum) 
	        {
	            $i++;
	            // Wir finden den coll_name der Collection heraus
	            $res_coll = $opus->query("SELECT coll_name FROM collections WHERE coll_id = $coll[$i];");
	            $mrow_coll = $opus->fetch_row($res_coll);
	            $coll_name = $mrow_coll[0];
	            // Wir finden den Namen und die ganze Hierarchie nach oben heraus
	            $query_eltern = "SELECT a.coll_name, (a.rgt - a.lft ) AS height
								FROM collections AS a, collections AS b
								WHERE b.lft BETWEEN a.lft AND a.rgt
								AND b.coll_id = '$coll[$i]'
								ORDER BY height DESC;";
	            $res_eltern = $opus->query($query_eltern);
	            $num_eltern = $opus->num_rows($res_eltern);
	            fwrite($fd,"<tr> \n<td class=\"frontdoor\" valign=\"top\">");
	            if ($collnum > 1) 
	            {
	                fwrite($fd,"<b>$t_collection $i:</b></td> \n");
	            } 
	            else 
	            {
	                fwrite($fd,"<b>$t_collection:</b></td>\n");
	            }
	            fwrite($fd,"<td></td><td class=\"frontdoor\" valign=\"bottom\">");
	            fwrite($fd,"<a href=\"$url/abfrage_collections.php?coll_id=$coll[$i]&la=$la\" target=\"new\">");
	            $m = 0;
	            while ($m < $num_eltern) 
	            {
	                $mrow_eltern = $opus->fetch_row($res_eltern);
	                $coll_name_eltern = $mrow_eltern[0];
	                if ($coll_name_eltern != $coll_name) 
	                {
	                    fwrite($fd,"$coll_name_eltern / ");
	                } 
	                else 
	                {
	                    fwrite($fd,"$coll_name_eltern");
	                }
	                $m++;
	            }
	            fwrite($fd,"</a></td> \n");
	            fwrite($fd,"</tr> \n");
	        }
	    }
	    // Ende Collections
	    $i = 0;
	    while ($i < $instnum) 
	    {
	        $i++;
	        fwrite($fd,"<TR> \n<TD class=\"frontdoor\" valign=\"top\">");
	        if ($instnum > 1) 
	        {
	            fwrite($fd,"<B>$t_institut $i:</B></TD> \n");
	        } 
	        else 
	        {
	            fwrite($fd,"<B>$t_institut:</B></TD> \n");
	        }
	        fwrite($fd,"<TD></TD><TD class=\"frontdoor\" valign=\"bottom\">");
	        fwrite($fd,"$inst[$i]</TD>\n");
	        fwrite($fd,"</TR> \n");
	    }
	    if ($publisher_faculty != "") 
	    {
	        fwrite($fd,"<TR> \n<TD class=\"frontdoor\" valign=\"top\">");
	        fwrite($fd,"<B>$t_fakultaet</B></TD> \n");
	        fwrite($fd,"<TD></TD><TD class=\"frontdoor\" valign=\"bottom\">");
	        fwrite($fd,"$publisher_faculty </TD>\n");
	        fwrite($fd,"</TR> \n");
	    }
	//***************************************************************************************************************************************
	//Änderung TW
	//Studiengang, Seitenanzahl, Datum, AC-Nummer und Begutachter eingef&uuml;gt
	    if ($studiengang != "" && $studiengang>0) 
	    {
	        fwrite($fd,"<TR> \n<TD class=\"frontdoor\" valign=\"top\">");
	        $res = $opus->query("SELECT * FROM studiengang WHERE stg_nr ='$studiengang'");
	        $rrr = 'name_'.$la;
	        $mrow = $opus->fetch_object($res);
	        $name = $mrow->$rrr;
	        $typ = $mrow->typ;
	        fwrite($fd,"<B>$t_studiengang</B></TD> \n");
	        fwrite($fd,"<TD></TD><TD class=\"frontdoor\" valign=\"bottom\">");
	        fwrite($fd,"$studiengang, $typ $name </TD>\n");
	        fwrite($fd,"</TR> \n");
	    }
	    if ($ac_nr != "") 
	    {
	        fwrite($fd,"<TR> \n<TD class=\"frontdoor\" valign=\"top\">");
	        fwrite($fd,"<B>$t_acnr</B></TD> \n");
	        fwrite($fd,"<TD></TD><TD class=\"frontdoor\" valign=\"bottom\">");
	        fwrite($fd,"$ac_nr </TD>\n");
	        fwrite($fd,"</TR> \n");
	    }
	    if ($seitenanzahl != "") 
	    {
	        fwrite($fd,"<TR> \n<TD class=\"frontdoor\" valign=\"top\">");
	        fwrite($fd,"<B>$t_sanz</B></TD> \n");
	        fwrite($fd,"<TD></TD><TD class=\"frontdoor\" valign=\"bottom\">");
	        fwrite($fd,"$seitenanzahl </TD>\n");
	        fwrite($fd,"</TR> \n");
	    }
	    if ($datum != "" && $datum!=NULL) 
	    {
	        fwrite($fd,"<TR> \n<TD class=\"frontdoor\" valign=\"top\">");
	        fwrite($fd,"<B>$t_datum</B></TD> \n");
	        fwrite($fd,"<TD></TD><TD class=\"frontdoor\" valign=\"bottom\">");
	        $datum_obj = new datum();
	        $datum=$datum_obj->formatDatum($datum, 'd.m.Y');
	        fwrite($fd,$datum."</TD>\n");
	        fwrite($fd,"</TR> \n");
	    }
	    if ($gutachter1 != "") 
	    {
	        fwrite($fd,"<TR> \n<TD class=\"frontdoor\" valign=\"top\">");
	        fwrite($fd,"<B>$t_gutachter1</B></TD> \n");
	        fwrite($fd,"<TD></TD><TD class=\"frontdoor\" valign=\"bottom\">");
	        fwrite($fd,"$gutachter1 </TD>\n");
	        fwrite($fd,"</TR> \n");
	    }    
	    if ($gutachter2 != "") 
	    {
	        fwrite($fd,"<TR> \n<TD class=\"frontdoor\" valign=\"top\">");
	        fwrite($fd,"<B>$t_gutachter2</B></TD> \n");
	        fwrite($fd,"<TD></TD><TD class=\"frontdoor\" valign=\"bottom\">");
	        fwrite($fd,"$gutachter2 </TD>\n");
	        fwrite($fd,"</TR> \n");
	    }
	    if ($studiensemester != 0 && $studiensemester != '') 
	    {
	        fwrite($fd,"<TR> \n<TD class=\"frontdoor\" valign=\"top\">");
	        fwrite($fd,"<B>$t_studiensemester</B></TD> \n");
	        fwrite($fd,"<TD></TD><TD class=\"frontdoor\" valign=\"bottom\">");
	        fwrite($fd,"$studiensemester</TD>\n");
	        fwrite($fd,"</TR> \n");
	    }
	//****************************************************************************************************************************************
	    if ($sachgruppe_ddc != "" && $sachgruppe_ddc != "no") 
	    {
	        $res = $opus->query("SELECT sachgruppe FROM sachgruppe_ddc_$la where nr = '$sachgruppe_ddc'");
	        $num = $opus->num_rows($res);
	        if ($num > 0) 
	        {
	            $mrow = $opus->fetch_row($res);
	            $sachgruppe_ddc = $mrow[0];
	        }
	        $opus->free_result($res);
	        fwrite($fd,"<TR> \n<TD class=\"frontdoor\" valign=\"top\">");
	        fwrite($fd,"<B>$ddc_sachgruppe</B></TD> \n");
	        fwrite($fd,"<TD></TD><TD class=\"frontdoor\" valign=\"bottom\">");
	        fwrite($fd,"$sachgruppe_ddc</TD>\n");
	        fwrite($fd,"</TR> \n");
	    }
	    if ($contributors_corporate != "") 
	    {
	        fwrite($fd,"<TR> \n<TD class=\"frontdoor\" valign=\"top\">");
	        fwrite($fd,"<B>$sonstige_institution</B></TD> \n");
	        fwrite($fd,"<TD></TD><TD class=\"frontdoor\" valign=\"bottom\">");
	        fwrite($fd,"$contributors_corporate </TD>\n");
	        fwrite($fd,"</TR> \n");
	    }
	    
	    fwrite($fd,"<TR> \n<TD class=\"frontdoor\" valign=\"top\">");
	    fwrite($fd,"<B>$t_dokumentart</B></TD> \n");
	    fwrite($fd,"<TD></TD><TD class=\"frontdoor\" valign=\"bottom\">");
	    fwrite($fd,"$dokumentart </TD>\n");
	    fwrite($fd,"</TR> \n");
	    /***** Schriftenreihe Start *****/
	    if ($sr_id != "" && $sr_band != "") 
	    {
	        fwrite($fd,"<TR> \n<TD class=\"frontdoor\" valign=\"top\">");
	        fwrite($fd,"<B>$t_schriftenreihe</B></TD> \n");
	        fwrite($fd,"<TD></TD><TD class=\"frontdoor\" valign=\"bottom\">");
	        fwrite($fd,"<a href=\"$url/schriftenreihen_ebene2.php?sr_id=$sr_id&la=$la\" target=\"new\">$sr_name</a> </TD>\n");
	        fwrite($fd,"</TR> \n");
	        fwrite($fd,"<TR> \n<TD class=\"frontdoor\" valign=\"top\">");
	        fwrite($fd,"<B>$t_bandnr</B></TD> \n");
	        fwrite($fd,"<TD></TD><TD class=\"frontdoor\" valign=\"bottom\">");
	        fwrite($fd,"$sr_band </TD>\n");
	        fwrite($fd,"</TR> \n");
	    }
	    /***** Schriftenreihe Stop *****/
	    if ($advisor != "") 
	    {
	        fwrite($fd,"<TR> \n<TD class=\"frontdoor\" valign=\"top\">");
	        fwrite($fd,"<B>$t_hauptberichter</B></TD> \n");
	        fwrite($fd,"<TD></TD><TD class=\"frontdoor\" valign=\"bottom\">");
	        fwrite($fd,"$advisor </TD>\n");
	        fwrite($fd,"</TR> \n");
	    }
	    if ($isbn != "") 
	    {
	        fwrite($fd,"<TR> \n<TD class=\"frontdoor\" valign=\"top\">");
	        fwrite($fd,"<B>$t_isbn</B></TD> \n");
	        fwrite($fd,"<TD></TD><TD class=\"frontdoor\" valign=\"bottom\">");
	        fwrite($fd,"$isbn </TD>\n");
	        fwrite($fd,"</TR> \n");
	    }
	    if ($issn != "") 
	    {
	        fwrite($fd,"<TR> \n<TD class=\"frontdoor\" valign=\"top\">");
	        fwrite($fd,"<B>$t_issn</B></TD> \n");
	        fwrite($fd,"<TD></TD><TD class=\"frontdoor\" valign=\"bottom\">");
	        fwrite($fd,"$issn </TD>\n");
	        fwrite($fd,"</TR> \n");
	    }
	    fwrite($fd,"<TR> \n<TD class=\"frontdoor\" valign=\"top\">");
	    fwrite($fd,"<B>$t_sprache</B></TD> \n");
	    fwrite($fd,"<TD></TD><TD class=\"frontdoor\" valign=\"bottom\">");
	    fwrite($fd,"$sprache </TD>\n");
	    fwrite($fd,"</TR> \n");
	    if ($date_accepted != "") 
	    {
	        fwrite($fd,"<TR> \n<TD class=\"frontdoor\" valign=\"top\">");
	        fwrite($fd,"<B>$t_pruefung_muendlich</B></TD> \n");
	        fwrite($fd,"<TD></TD><TD class=\"frontdoor\" valign=\"bottom\">");
	        $date_accepted_format = strftime("%d.%m.%Y", $date_accepted);
	        fwrite($fd,"$date_accepted_format </TD>\n");
	        fwrite($fd,"</TR> \n");
	    }
	    fwrite($fd,"<TR> \n<TD class=\"frontdoor\" valign=\"top\">");
	    fwrite($fd,"<B>$t_erstellungsjahr</B></TD> \n");
	    fwrite($fd,"<TD></TD><TD class=\"frontdoor\" valign=\"bottom\">");
	    fwrite($fd,"$date_year </TD>\n");
	    fwrite($fd,"</TR> \n");
	    fwrite($fd,"<TR> \n<TD class=\"frontdoor\" valign=\"top\">");
	    fwrite($fd,"<B>$t_publikationsdatum</B></TD> \n");
	    fwrite($fd,"<TD></TD><TD class=\"frontdoor\" valign=\"bottom\">");
	    $date_creation_format = strftime("%d.%m.%Y", $date_creation);
	    fwrite($fd,"$date_creation_format </TD>\n");
	    fwrite($fd,"</TR> \n");
	    if ($date_valid != 0) 
	    {
	        fwrite($fd,"<TR> \n<TD class=\"frontdoor\" valign=\"top\">");
	        #fwrite($fd,"<B>G&uuml;ltig bis:</B></TD> \n");
	        fwrite($fd,"<B>$text6</B></TD> \n");
	        fwrite($fd,"<TD></TD><TD class=\"frontdoor\" valign=\"bottom\">");
	        $date_valid_format = strftime("%d.%m.%Y", $date_valid);
	        fwrite($fd,"$date_valid_format </TD>\n");
	        fwrite($fd,"</TR> \n");
	    }
	    if ($bem_extern != "") 
	    {
	        fwrite($fd,"<TR> \n<TD class=\"frontdoor\" valign=\"top\">");
	        fwrite($fd,"<B>$t_bemerkung</B></TD> \n");
	        fwrite($fd,"<TD></TD><TD class=\"frontdoor\" valign=\"bottom\">");
	        fwrite($fd,"$bem_extern </TD>\n");
	        fwrite($fd,"</TR> \n");
	    }
	    fwrite($fd,"<TR> \n<TD class=\"frontdoor\" valign=\"top\">");
	    fwrite($fd,"<B>$text4 $sprache_description:</B></TD> \n");
	    fwrite($fd,"<TD></TD><TD class=\"frontdoor\" valign=\"bottom\">");
	    fwrite($fd,nl2br($description));
	    fwrite($fd,"</TD>\n");
	    fwrite($fd,"</TR> \n");
	    if ($description2 != "") 
	    {
	        fwrite($fd,"<TR> \n<TD class=\"frontdoor\" valign=\"top\">");
	        fwrite($fd,"<B>$text4 $sprache_description2:</B></TD> \n");
	        fwrite($fd,"<TD></TD><TD class=\"frontdoor\" valign=\"bottom\">");
	        fwrite($fd,nl2br($description2));
	        fwrite($fd,"</TD>\n");
	        fwrite($fd,"</TR> \n");
	    }
	    # Start Lizenzvertrag
	    if ($lic_active >= 2) 
	    {
	        if ($licname != "") 
	        {
	            fwrite($fd,"<TR> \n<TD class=\"frontdoor\" valign=\"top\">");
	            fwrite($fd,"<B>$lizenz:</B></TD> \n");
	            fwrite($fd,"<TD></TD>\n<TD class=\"frontdoor\" valign=\"bottom\">");
	            if (strlen(trim($licdesc_html)) > 0) 
	            {
	                fwrite($fd,"\n" . $licdesc_html . "\n");
	            }
	            $lic_uselogos = $opus->value("license_uselogos");
	            if (($lic_uselogos) AND (strlen($liclogo) > 0)) 
	            {
	                $liclogo_width = $opus->value("license_logo_width");
	                $liclogo_height = $opus->value("license_logo_height");
	                $liclogo_wh = "";
	                if ($liclogo_width) 
	                {
	                    $liclogo_wh.= " width=\"" . $liclogo_width . "\"";
	                }
	                if ($liclogo_height) 
	                {
	                    $liclogo_wh.= " height=\"" . $liclogo_height . "\"";
	                }
	                fwrite($fd,"<a href=\"$liclink?la=$la\" target=\"_blank\">\n");
	                fwrite($fd,"<img src=\"" . $liclogo . "\" alt=\"Lizenz-Logo\" border=\"0\"" . $liclogo_wh . ">");
	                fwrite($fd,"</a>&nbsp;\n");
	            }
	            fwrite($fd,"<a href=\"$liclink?la=$la\" target=\"_blank\">\n");
	            fwrite($fd,"$licname</a> \n</td>\n");
	            fwrite($fd,"</tr> \n");
	        }
	    }
	    fwrite($fd,"</TABLE> \n");    
	} 
	else 
	{
	    #fwrite($fd,"IDN $source_opus nicht vorhanden.");
	    fwrite($fd,"$text7 $source_opus $text8 ");
	}
	//$opus->close($sock);
}


//****************************************************************************************************
//Einlesen Projektarbeiten (nur Diplomarbeiten)
//Bedingungen:
//Entweder DA oder LV benotet
//Abgabedatum nicht länger als 6 Monate zurück
//Freigegeben oder Endedatum der Sperre vorbei
//****************************************************************************************************
$qry="SELECT *, tbl_lehreinheit.studiensemester_kurzbz, tbl_projektarbeit.student_uid as stud_uid, tbl_fachbereich.bezeichnung as fb_bez, 
	tbl_lehrveranstaltung.studiengang_kz as stg_kz, tbl_projektarbeit.note as note1, tbl_zeugnisnote.note as note2  
	FROM lehre.tbl_projektarbeit 
	JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) 
	JOIN lehre.tbl_lehrveranstaltung ON(tbl_lehreinheit.lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id) 
	JOIN lehre.tbl_lehrveranstaltung as lehrfach ON(tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id) 
	JOIN public.tbl_fachbereich ON(lehrfach.oe_kurzbz=tbl_fachbereich.oe_kurzbz) 
	LEFT JOIN lehre.tbl_zeugnisnote ON(tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_zeugnisnote.lehrveranstaltung_id AND tbl_zeugnisnote.studiensemester_kurzbz=tbl_lehreinheit.studiensemester_kurzbz AND tbl_projektarbeit.student_uid=tbl_zeugnisnote.student_uid)
	WHERE ((tbl_projektarbeit.note>0 AND tbl_projektarbeit.note<5) OR (tbl_zeugnisnote.note>0 AND tbl_zeugnisnote.note<5)) AND projekttyp_kurzbz='Diplom'
	AND to_char(tbl_projektarbeit.abgabedatum,'YYYYMMDD')>'".date('Ymd',mktime(0, 0, 0, date('m')-6, date('d'), date('Y')))."' 
	AND (tbl_projektarbeit.freigegeben OR (to_char(tbl_projektarbeit.gesperrtbis,'YYYYMMDD')<'".date('Ymd',mktime(0, 0, 0, date('m'), date('d'), date('Y')))."'))";

//echo $qry."<br>";

if($erg=pg_query($db_conn,$qry))
{
	while($row=pg_fetch_object($erg))
	{
		if(($row->note1<0 OR $row->note1>4) && ($row->note2<0 OR $row->note2>4))
		{
			continue;
		}
		$opus_url=OPUS_PATH_PAA;			
		$url_paa=PAABGABE_PATH;
		$row->sprache=mb_strtolower(mb_substr($row->sprache,0,3));
		//echo "--->".$row->projektarbeit_id.", ".$row->projekttyp_kurzbz.", ".$row->stud_uid.", ".$row->abgabedatum."<br>";
		//****************************************************************************************************
		//weitere benötigte Daten
		//****************************************************************************************************
		//verfasser
		$verfasser="";
		$qry_std="SELECT * FROM public.tbl_benutzer 
			JOIN public.tbl_person on(tbl_person.person_id=tbl_benutzer.person_id) 
			WHERE uid='".$row->stud_uid."';";
		//echo $qry_std."<br>";
		if($result_std=pg_query($db_conn,$qry_std))
		{
			if(pg_num_rows($result_std)>0)
			{
				while($row_std=pg_fetch_object($result_std))
				{
					if(trim($verfasser)=='')
					{
						$verfasser=trim($row_std->nachname.", ".$row_std->vorname);
					}
					else 
					{
						$verfasser.=" , ".trim($row_std->nachname.", ".$row_std->vorname);
					}
				}
			}
			else 
			{
				$fehler.="\nKein Verfasser zugeordnet!";
				$error=true;
			}
		}
		else 
		{
			$mail = new mail(MAIL_ADMIN, 'vilesci@technikum-wien.at', 'abgabe2opus', 'Quelldatenbanken konnten nicht ge&ouml;ffnet werden (sel benutzer)!'."\n".$qry_std);
			$mail->send();
			die($qry_std);
		}
		//begutachter
		$begutachter1="";
		$qry_bet="SELECT * FROM lehre.tbl_projektbetreuer 
			JOIN public.tbl_person on(lehre.tbl_projektbetreuer.person_id=public.tbl_person.person_id) 
			WHERE projektarbeit_id='".$row->projektarbeit_id."'  
			AND (betreuerart_kurzbz='Betreuer' OR betreuerart_kurzbz='Begutachter' OR betreuerart_kurzbz='Erstbegutachter' OR betreuerart_kurzbz='Erstbetreuer');";
		//echo $qry_bet."<br>";
		if($result_bet=pg_query($db_conn,$qry_bet))
		{
			if(pg_num_rows($result_bet)>0)
			{
				while($row_bet=pg_fetch_object($result_bet))
				{
					if(trim($begutachter1)=='')
					{
						$begutachter1=trim($row_bet->nachname.", ".$row_bet->vorname);
					}
					else 
					{
						$begutachter1.=" , ".trim($row_bet->nachname.", ".$row_bet->vorname);
					}
				}
			}
			else 
			{
				$fehler.="\nKein Begutachter zugeordnet!";
				$error=true;
			}
		}
		else 
		{
			$mail = new mail(MAIL_ADMIN, 'vilesci@technikum-wien.at', 'abgabe2opus', 'Quelldatenbanken konnten nicht ge&ouml;ffnet werden!'."\n".$qry_bet);
			$mail->send($qry_bet);
			die();
		}
		if($row->projekttyp_kurzbz!='Bachelor')
		{
			$begutachter2="";
			$qry_bet="SELECT * FROM lehre.tbl_projektbetreuer 
				JOIN public.tbl_person on(lehre.tbl_projektbetreuer.person_id=public.tbl_person.person_id) 
				WHERE projektarbeit_id='".$row->projektarbeit_id."'  
				AND (betreuerart_kurzbz='Zweitbetreuer' OR betreuerart_kurzbz='Zweitbegutachter');";
			//echo $qry_bet."<br>";
			if($result_bet=pg_query($db_conn,$qry_bet))
			{
				if(pg_num_rows($result_bet)>0)
				{
					while($row_bet=pg_fetch_object($result_bet))
					{
						if(trim($begutachter2)=='')
						{
							$begutachter2=trim($row_bet->nachname.", ".$row_bet->vorname);
						}
						else 
						{
							$begutachter2.=" , ".trim($row_bet->nachname.", ".$row_bet->vorname);
						}
					}
				}
				else 
				{
					//$fehler.="\nKein Zweitbegutachter zugeordnet!";
					//$error=true;
					$begutachter2 = $begutachter1;
				}
			}
			else 
			{
				$mail = new mail(MAIL_ADMIN, 'vilesci@technikum-wien.at', 'abgabe2opus', 'Quelldatenbanken konnten nicht ge&ouml;ffnet werden!'."\n".$qry_bet);
				$mail->send();
				die($qry_bet);
			}
		}
		//Institute
		if($row->fb_bez==NULL || trim($row->fb_bez)=='')
		{
			$fehler.="\nInstitut nicht gefunden!";
			$error=true;	
		}
		else 
		{
			$qry_inst="SELECT * FROM institute_de WHERE trim(name)='".trim($row->fb_bez)."';";
			if($result_inst = mysql_query($qry_inst,$conn_ext))
			{
				if(mysql_num_rows($result_inst)>0)
				{
					while($row_inst=mysql_fetch_object($result_inst))
					{
						$institut=$row_inst->nr;
					}
				}
				else 
				{
					$fehler.="\nInstitutsname nicht gefunden!";
					$error=true;	
				}
			}
		}
		//echo $qry_inst."<br>";
		if($row->kontrollschlagwoerter==NULL || $row->kontrollschlagwoerter=='' || $row->abstract==NULL || $row->abstract=='' || $row->abstract_en==NULL || $row->abstract_en=='' )
		{			
			$fehler.=$row->stud_uid.": Projektarbeit (".$row->projekttyp_kurzbz.") ".$row->projektarbeit_id.$fehler;
			if($row->kontrollschlagwoerter==NULL || $row->kontrollschlagwoerter=='')
			{
				$fehler.="\nKontrollierte Schlagw&ouml;rter nicht eingegeben!";
				$error=true;
			}
			if($row->abstract==NULL || $row->abstract=='')
			{
				$fehler.="\nAbstract nicht eingegeben!";
				$error=true;
			}
			if($row->abstract_en==NULL || $row->abstract_en=='')
			{
				$fehler.="\nEnglischer Abstract nicht eingegeben!";
				$error=true;
			}
			if($row->seitenanzahl==NULL || $row->seitenanzahl=='')
			{
				$fehler.="\nSeitenanzahl nicht eingegeben!";
				$error=true;
			}
			if($row->stg_kz==NULL || $row->stg_kz=='' || $row->stg_kz==0)
			{
				$fehler.="\nStudiengang nicht gefunden!";
				$error=true;
			}
			if($row->studiensemester_kurzbz==NULL || $row->studiensemester_kurzbz=='')
			{
				$fehler.="\nStudiensemester nicht gefunden!";
				$error=true;
			}
		}
		
		if(!$error)
		{
			//*******************************************************************************************
			//Einf&uuml;gen in OPUS
			//*******************************************************************************************
					
			//	Originaltitel der Arbeit				title
			//	Titel der Arbeit in Englisch			title_en
			//	1. Verfasser(innen)name 				(opus_autor) source_opus, creator_name, 1
			//	Universität								publisher_university = FHTW
			//	Typ der Arbeit							type (Nummer)								7=Diplomarbeit, 25=Bachelorarbeit
			//	Institut								(opus_inst) source_opus, inst_nr			
			//	Studiengang								stg_nr
			//	Datumsfeld								datum
			//	1. Gutachter							begutachter1
			//	2. Gutachter							begutachter2
			//	Kontrollierte Schlagwörter (Deutsch)	subject_swd
			//	Schlagwörter dt							subject_uncontrolled_german
			//	Schlagwörter en							subject_uncontrolled_english
			//	Abstract								description
			//	Abstract en								description2
			//	Abstract Sprache 1						sprache
			//	Abstract Sprache 2						description2_lang = eng
			//	Sachgrupppe								sachgruppe_ddc = 000						000=Allgemeines, Wissenschaft
			//	Jahr									date_year
			//	Seitenanzahl							seitenanzahl
			//	Studiensemester							studiensemester_kurzbz
			//	Projektabeit ID							projektarbeit_id
			//	Sprache									sprache			
			//	Zugriffsbeschränkung					bereich_id									1=uneingeschränkt, 2=innerh. Campus
			
			if($row->projekttyp_kurzbz=='Diplom')
				$typ=7;
			if($row->projekttyp_kurzbz=='Bachelor')
				$typ=25;
			$stg=($row->stg_kz<1000?'0'.$row->stg_kz:$row->stg_kz);
			$qry_src="Select max(source_opus) as source from opus
						UNION
						SELECT id as source from seq_temp
						ORDER BY source DESC LIMIT 1";
			if($result_src = mysql_query($qry_src,$conn_ext))
			{
				while($row_src=mysql_fetch_object($result_src))
				{
					$row_opus=$row_src->source+1;
				}
			}
			$qry_chk="SELECT projektarbeit_id FROM opus WHERE projektarbeit_id=".$row->projektarbeit_id;
			if($result_chk=mysql_query($qry_chk))
			{
				if(mysql_num_rows($result_chk)>0)
				{
					//Datensatz bereits eingetragen
					echo "Bereits vorhanden: ".$row->projektarbeit_id."<br>";
				}
				else 
				{
					$qry_ins="INSERT INTO opus 
						(source_opus, title, title_en, publisher_university, type, stg_nr, datum, begutachter1, begutachter2, subject_swd, 
						subject_uncontrolled_german, subject_uncontrolled_english, description, description2, description_lang, description2_lang, 
						sachgruppe_ddc, date_year, seitenanzahl, studiensemester_kurzbz, projektarbeit_id, language, bereich_id, date_creation) values 
						('".$row_opus."', '".addslashes($row->titel)."', '".addslashes($row->titel_english)."', 'FHTW', '".$typ."', '".$stg."', '".$row->abgabedatum."', '"
						.addslashes($begutachter1)."', '".addslashes($begutachter2)."', '".addslashes($row->kontrollschlagwoerter)."', '".addslashes($row->schlagwoerter)
						."', '".addslashes($row->schlagwoerter_en)."', '".addslashes($row->abstract)."', '".addslashes($row->abstract_en)."', '".$row->sprache
						."', 'eng', '000', '".$datum_obj->formatDatum($row->abgabedatum,'Y')."', '".$row->seitenanzahl."', '".$row->studiensemester_kurzbz."', '"
						.$row->projektarbeit_id."', '".$row->sprache."', '".$bereich."', UNIX_TIMESTAMP())";
					$qry_cre="INSERT INTO opus_autor (source_opus, creator_name, reihenfolge) VALUES ('".$row_opus."', '".$verfasser."', '1')";
					$qry_inst="INSERT INTO opus_inst (source_opus, inst_nr) VALUES ('".$row_opus."', '".$institut."')";
					$qry_seq="UPDATE seq_temp SET id=".$row_opus;					
					
					$qry="START TRANSACTION";
		
					//echo $qry."<br>".$qry_ins."<br>".$qry_cre."<br>".$qry_inst;
					if(!$result=mysql_query($qry))
					{
						$fehler1.="\n\nTransaktion nicht begonnen! \n".mysql_errno($conn_ext) . ": " . mysql_error($conn_ext);
					}
					else 
					{
						if(!$result=mysql_query($qry_ins))
						{
							$fehler1.="\n\nTransaktion abgebrochen! \n".mysql_errno($conn_ext) . ": " . mysql_error($conn_ext);
							mysql_query('ROLLBACK',$conn_ext);
						}
						else 
						{
							if(!$result=mysql_query($qry_cre))
							{
								$fehler1.="\n\nTransaktion abgebrochen!! \n".mysql_errno($conn_ext) . ": " . mysql_error($conn_ext);
								if(!$result=mysql_query('ROLLBACK',$conn_ext))
								{
									$fehler1.="\n\nRollback nicht durchgef&uuml;hrt. \n".mysql_errno($conn_ext) . ": " . mysql_error($conn_ext);
								}
							}
							else 
							{
								if(!$result=mysql_query($qry_inst))
								{
									echo nl2br("\n\nTransaktion abgebrochen!!! \n".mysql_errno($conn_ext) . ": " . mysql_error($conn_ext));
									mysql_query('ROLLBACK',$conn_ext);
								}
								else 
								{
									if(!$result=mysql_query($qry_seq))
									{
										//Sequenz schreiben
										echo nl2br("\n\nTransaktion abgebrochen!!! \n".mysql_errno($conn_ext) . ": " . mysql_error($conn_ext));
										mysql_query('ROLLBACK',$conn_ext);
									}
									else 
									{
										//arbeit freigegeben?
										if($row->freigegeben)
										{
											//Kopieren der Abgabedatei
											$qry_file="SELECT * FROM campus.tbl_paabgabe WHERE projektarbeit_id='".$row->projektarbeit_id."' and paabgabetyp_kurzbz='end' AND abgabedatum is not null ORDER BY abgabedatum desc LIMIT 1";
											if($result_file=pg_query($db_conn,$qry_file))
											{
												if($row_file=pg_fetch_object($result_file))
												{
													if(!is_dir($opus_url.date('Y')))
													{
														mkdir($opus_url.date('Y'), 0775);
													}
													if(!is_dir($opus_url.date('Y')."/".$row_opus))
													{
														mkdir($opus_url.date('Y')."/".$row_opus, 0775);
													}
													$opus_url=$opus_url.date('Y')."/".$row_opus;
													if(!is_dir($opus_url."/pdf/"))
													{
														mkdir($opus_url."/pdf/", 0775);
													}
													//echo "\nQuelle: ".$url_paa.$row_file->paabgabe_id.'_'.$row->stud_uid.'.pdf'." -> ".$opus_url."".$row_file->paabgabe_id.'_'.$row->stud_uid.'.pdf';
													copy($url_paa.$row_file->paabgabe_id.'_'.$row->stud_uid.'.pdf',$opus_url."/pdf/".$row_file->paabgabe_id.'_'.$row->stud_uid.'.pdf');
													//&uuml;berpr&uuml;fen, ob Datei wirklich kopiert wurde
													if(is_file($opus_url."/pdf/".$row_file->paabgabe_id.'_'.$row->stud_uid.'.pdf'))
													{
														//COMMIT durchf&uuml;hren
														if(!$result=mysql_query('COMMIT',$conn_ext))
														{
															mysql_query('ROLLBACK',$conn_ext);
															$fehler1.="\nCommit nicht ausgef&um;hrt! \n".$row_opus."/".$verfasser."\n".mysql_errno($conn_ext) . ": " . mysql_error($conn_ext);
														}
														else 
														{
															if (file_exists($opus_url)) 
															{
													            $fd = fopen($opus_url."/index.html", 'w');
													            if ($fd == 0) 
													            {
													                $fehler1.="\nFehler beim Oeffnen des Index-Files \n\n";
													                exit;
													            } 
													            else 
													            {
													            	indexdatei($row_opus, $fd);
													                fclose($fd);
													                $kopiert.="OPUS-Nr. $row_opus, von $verfasser, ProjektarbeitID $row->projektarbeit_id\n";
													                #print ("Indexdatei zu Dokument $source_opus wurde in die Datei <a href=\"$volltext_url/$jahr/$source_opus/index.html\">index.html</a> geschrieben.<P> \n");
													            }
													        } 
													        else 
													        {
													            $fehler1.="\n".$opus_url."/pdf/ nicht vorhanden.\n \n";
													        }
														}
													}
													else 
													{
														mysql_query('ROLLBACK',$conn_ext);
														$fehler1.="\nDatei wurde nicht kopiert! \nZielpfad:".$opus_url."/pdf/".$row_file->paabgabe_id.'_'.$row->stud_uid.".pdf \nSource: ".$url_paa.$row_file->paabgabe_id.'_'.$row->stud_uid.'.pdf'."\n";
													}
												}
												else 
												{
													mysql_query('ROLLBACK',$conn_ext);
													$fehler1.="\nAbgabe konnte nicht geladen werden! \n".$row_opus."/".$verfasser."\n".$db->db_last_error();
												}
											} 
											else 
											{
												mysql_query('ROLLBACK',$conn_ext);
												$fehler1.="\nEintragung der Abgabe nicht gefunden! \n".$row_opus."/".$verfasser."/".$qry_file."\n".$db->db_last_error();
											}	
										}
										else 
										{
											//COMMIT durchf&uuml;hren
											if(!$result=mysql_query('COMMIT',$conn_ext))
											{
												mysql_query('ROLLBACK',$conn_ext);
												$fehler1.="\nCommit wurde nicht ausgef&um;hrt! \n".$row_opus."/".$verfasser."\n".mysql_errno($conn_ext) . ": " . mysql_error($conn_ext);
											}
										}
									}
								}
							}
						}
					}
				}
			}
			else 
			{
				$fehler1.="\n&Uuml;berpr&uuml;fung, ob bereits vorhanden, konnte nicht durchgef&uuml;hrt werden! \n".mysql_errno($conn_ext)."\n".$qry_chk."\n";
			}
			if($fehler1!='')
			{
				$fehler.="-->".$fehler1;
				$fehler.="\n-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------";
				$fehler.="\nBegutachter1: ".$begutachter1."\nBegutachter2: ".$begutachter2."\nTitel: ".$row->titel."\nTitel en: ".$row->titel_english."\n";
				$fehler.="Verfasser: ".$verfasser."\nInstitut: ".$institut."\nStudiengang: ".($row->stg_kz<1000?'0'.$row->stg_kz:$row->stg_kz)."\nDatum: ".$datum_obj->formatDatum($row->abgabedatum,'d.m.Y')."\n";
				$fehler.="Kontr. Schlagw&ouml;rter: ".$row->kontrollschlagwoerter."\nSchlagw&ouml;rter dt: ".$row->schlagwoerter."\nSchlagw&ouml;rter en: ".$row->schlagwoerter_en."\n";
				$fehler.="Abstract: ".$row->abstract."\nAbstract_en: ".$row->abstract_en."\nSeitenanzahl: ".$row->seitenanzahl."\nStudiensemester: ".$row->studiensemester_kurzbz."\n";
				$fehler.="Projektarbeit ID: ".$row->projektarbeit_id."\nTyp der Arbeit: ".$row->projekttyp_kurzbz."\n";
				$fehler1='';
			}
		}
		else 
		{
			$fehler.="\n-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------";
			$fehler.="\nBegutachter1: ".$begutachter1."\nBegutachter2: ".$begutachter2."\nTitel: ".$row->titel."\nTitel en: ".$row->titel_english."\n";
			$fehler.="Verfasser: ".$verfasser."\nInstitut: ".$institut."\nStudiengang: ".($row->stg_kz<1000?'0'.$row->stg_kz:$row->stg_kz)."\nDatum: ".$datum_obj->formatDatum($row->abgabedatum,'d.m.Y')."\n";
			$fehler.="Kontr. Schlagw&ouml;rter: ".$row->kontrollschlagwoerter."\nSchlagw&ouml;rter dt: ".$row->schlagwoerter."\nSchlagw&ouml;rter en: ".$row->schlagwoerter_en."\n";
			$fehler.="Abstract: ".$row->abstract."\nAbstract_en: ".$row->abstract_en."\nSeitenanzahl: ".$row->seitenanzahl."\nStudiensemester: ".$row->studiensemester_kurzbz."\n";
			$fehler.="Projektarbeit ID: ".$row->projektarbeit_id."\nTyp der Arbeit: ".$row->projekttyp_kurzbz."\n";
			$error=false;			
		}
	}
	if($fehler!='')
	{
		$mail = new mail(MAIL_ADMIN, 'vilesci@technikum-wien.at', 'abgabe2opus', "Aufgetretene Fehler: \n".$fehler);
		$mail->send();
		$fehler='';
	}
}
else 
{
	$mail = new mail(MAIL_ADMIN, 'vilesci@technikum-wien.at', 'abgabe2opus', 'Quelldatenbank konnte nicht ge&ouml;ffnet werden!'."\n".$qry);
	$mail->send();
	die($qry);
}
if ($kopiert!='' && $kopiert!=NULL)
{
	$mail = new mail(MAIL_ADMIN, 'vilesci@technikum-wien.at', 'abgabe2opus', "Übertragene Projektarbeiten:\n".$kopiert);
	$mail->send();
}

?>
