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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//PDF fuer die Anwesenheitsliste auf CIS

//PDF erzeugen
$pdf = new PDF('P','pt');
$pdf->Open();
$pdf->AddPage();
$pdf->AliasNbPages();

$pdf->SetFillColor(111,111,111);
$pdf->SetXY(30,40);
$stgobj=new studiengang();
$stgobj->load($stg);
//Logo
$pdf->Image("../../../skin/images/logo.jpg","418","45","","45","jpg","");
//$pdf->Image("../../../skin/images/tw_logo_02.jpg","400","30","116","43","jpg","");

$bezeichnung='';
if ($lvobj = new lehrveranstaltung($lvid))
	$bezeichnung=mb_convert_encoding($lvobj->bezeichnung,'ISO-8859-15','UTF-8');

$pdf->SetFont('Arial','',16);
$pdf->MultiCell(0,20,'Anwesenheitsliste '.$bezeichnung,0,'L',0);

$pdf->SetFont('Arial','',10);
$pdf->SetFillColor(190,190,190);
//Bei langen Namen muss der Gruppenname etwas weiter unten angezeigt werden da er
//sonst von der zweiten Zeile des Titels ueberschrieben wird.
if(strlen($bezeichnung)>50)
$pdf->SetXY(30,75);
else
$pdf->SetXY(30,60);

//$stsem_obj = new studiensemester($conn);
//$stsem = $stsem_obj->getaktorNext();

$qry = "SELECT distinct on(kuerzel, semester, verband, gruppe, gruppe_kurzbz) UPPER(stg_typ::varchar(1) || stg_kurzbz) as kuerzel, semester, verband, gruppe, gruppe_kurzbz from campus.vw_lehreinheit WHERE lehrveranstaltung_id='".addslashes($lvid)."' AND studiensemester_kurzbz='".addslashes($stsem)."'";
if($lehreinheit_id!='')
	$qry.=" AND lehreinheit_id='".addslashes($lehreinheit_id)."'";

$gruppen='';
if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		if($gruppen!='')
			$gruppen.=', ';
		if($row->gruppe_kurzbz=='')
			$gruppen.=trim($row->kuerzel.'-'.$row->semester.$row->verband.$row->gruppe);
		else
			$gruppen.=$row->gruppe_kurzbz;
	}
}
$gruppen=mb_convert_encoding($gruppen,'ISO-8859-15','UTF-8');

if(strlen($gruppen)>50)
	$linebreak="\n";
else
	$linebreak=' ';
$semester = new studiensemester($stsem);
$pdf->MultiCell(0,15,'Gruppe: '.$gruppen.$linebreak.'Studiensemester: '.(($semester->beschreibung != NULL) ? $semester->beschreibung : $stsem));

$maxY=$pdf->GetY();
$maxY=getmax($maxY,$pdf->GetY());


$pdf->SetFont('Arial','B',12);

$pdf->SetXY(30,90);
$pdf->MultiCell(0,20,'Monat _________');

//Anfang Tabelle
$pdf->SetFont('Arial','',8);
$lineheight=12;
//Datum
$maxY=$pdf->GetY()+10;
$maxX=30;
$pdf->SetXY($maxX,$maxY);
$pdf->MultiCell(280,$lineheight,'Datum',1,'L',0);
$maxX +=280;
$pdf->SetXY($maxX,$maxY);
$pdf->MultiCell(40,$lineheight,'',1,'L',0);
$maxX +=40;
$pdf->SetXY($maxX,$maxY);
$pdf->MultiCell(40,$lineheight,'',1,'L',0);
$maxX +=40;
$pdf->SetXY($maxX,$maxY);
$pdf->MultiCell(40,$lineheight,'',1,'L',0);
$maxX +=40;
$pdf->SetXY($maxX,$maxY);
$pdf->MultiCell(40,$lineheight,'',1,'L',0);
$maxX +=40;
$pdf->SetXY($maxX,$maxY);
$pdf->MultiCell(40,$lineheight,'',1,'L',0);
$maxX +=40;
$pdf->SetXY($maxX,$maxY);
$pdf->MultiCell(40,$lineheight,'',1,'L',0);

//Anzahl der abgehaltenen Stunden
$maxY=$pdf->GetY();
$maxX=30;
$pdf->SetXY($maxX,$maxY);
$pdf->MultiCell(280,$lineheight,'Anzahl der abgehaltenen Einheiten',1,'L',0);
$maxX +=280;
$pdf->SetXY($maxX,$maxY);
$pdf->MultiCell(40,$lineheight,'',1,'L',0);
$maxX +=40;
$pdf->SetXY($maxX,$maxY);
$pdf->MultiCell(40,$lineheight,'',1,'L',0);
$maxX +=40;
$pdf->SetXY($maxX,$maxY);
$pdf->MultiCell(40,$lineheight,'',1,'L',0);
$maxX +=40;
$pdf->SetXY($maxX,$maxY);
$pdf->MultiCell(40,$lineheight,'',1,'L',0);
$maxX +=40;
$pdf->SetXY($maxX,$maxY);
$pdf->MultiCell(40,$lineheight,'',1,'L',0);
$maxX +=40;
$pdf->SetXY($maxX,$maxY);
$pdf->MultiCell(40,$lineheight,'',1,'L',0);

//Lektoren
$pdf->SetFont('Arial','B',10);
$maxY=$pdf->GetY();
$maxX=30;
$pdf->SetXY($maxX,$maxY);
$pdf->MultiCell(520,$lineheight+2,'LektorInnen',1,'L',1);

//Schleife aller lektoren

$qry = "SELECT
			distinct vorname, nachname
		FROM campus.vw_benutzer, lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter
		WHERE
			uid=mitarbeiter_uid AND
			tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND
			lehrveranstaltung_id='".addslashes($lvid)."' AND
			studiensemester_kurzbz='$stsem' ";
		if($lehreinheit_id!='')
				$qry.=" AND tbl_lehreinheit.lehreinheit_id='".addslashes($lehreinheit_id)."'";
$qry.=" ORDER BY nachname, vorname;";
if($result = $db->db_query($qry))
{
	while($row=$db->db_fetch_object($result))
	{
		$pdf->SetFont('Arial','',8);
		$maxY=$pdf->GetY();
		$maxX=30;
		$pdf->SetXY($maxX,$maxY);

		$vorname=mb_convert_encoding(trim($row->vorname),'ISO-8859-15','UTF-8');
		$nachname=mb_convert_encoding(trim($row->nachname),'ISO-8859-15','UTF-8');
		$name="$vorname $nachname";

		$pdf->MultiCell(280,$lineheight,$name,1,'L',0);
		$maxX +=280;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(40,$lineheight,'',1,'L',0);
		$maxX +=40;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(40,$lineheight,'',1,'L',0);
		$maxX +=40;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(40,$lineheight,'',1,'L',0);
		$maxX +=40;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(40,$lineheight,'',1,'L',0);
		$maxX +=40;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(40,$lineheight,'',1,'L',0);
		$maxX +=40;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(40,$lineheight,'',1,'L',0);
	}
}
//Studenten
$pdf->SetFont('Arial','B',10);
$maxY=$pdf->GetY();
$maxX=30;
$pdf->SetXY($maxX,$maxY);
$pdf->MultiCell(520,$lineheight+2,'Studierende',1,'L',1);

$maxY=$pdf->GetY();
$pdf->tablewidths = array(20,140,60,60,40,40,40,40,40,40); //Spaltenbreiten setzen
$pdf->SetLeftMargin(30);
$pdf->SetTopMargin(100); // Auch in der Funktion Header umstellen!!!

$aligns = array('R','L','C','C','L','L','L','L','L','L'); //Ausrichtung der Tabellen festlegen

$pdf->SetFont('Arial','',8);
$pdf->SetXY(30,$maxY);
$inhalt[]=array(' ','HörerIn/Name','Kennzeichen','Gruppe','','','','','',''); //Spaltenueberschriften

//Studenten holen

$pdf->SetFont('Arial','',8);
		$maxY=$pdf->GetY();
		$maxX=30;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(20,$lineheight,'',1,'R',0);
		$maxX +=20;
		$pdf->SetXY($maxX,$maxY);
		$pdf->SetFont('Arial','B',8);
		$pdf->MultiCell(130,$lineheight,mb_convert_encoding('Hörer/Name','ISO-8859-15','UTF-8'),1,'L',0);
		$maxX +=130;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(65,$lineheight,'Kennzeichen',1,'C',0);
		$maxX +=65;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(65,$lineheight,'Gruppe',1,'C',0);
		$maxX +=65;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(40,$lineheight,'',1,'L',0);
		$maxX +=40;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(40,$lineheight,'',1,'L',0);
		$maxX +=40;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(40,$lineheight,'',1,'L',0);
		$maxX +=40;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(40,$lineheight,'',1,'L',0);
		$maxX +=40;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(40,$lineheight,'',1,'L',0);
		$maxX +=40;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(40,$lineheight,'',1,'L',0);
$stsem_obj = new studiensemester();
$stsem_obj->load($stsem);
$stsemdatumvon = $stsem_obj->start;
$stsemdatumbis = $stsem_obj->ende;

$qry = "SELECT
			distinct on(nachname, vorname, person_id) vorname, nachname, matrikelnr,
			tbl_studentlehrverband.semester, tbl_studentlehrverband.verband, tbl_studentlehrverband.gruppe,
			(SELECT status_kurzbz FROM public.tbl_prestudentstatus WHERE prestudent_id=tbl_student.prestudent_id ORDER BY datum DESC, insertamum DESC, ext_id DESC LIMIT 1) as status,
			tbl_bisio.bisio_id, tbl_bisio.von, tbl_bisio.bis,
			tbl_zeugnisnote.note
		FROM
			campus.vw_student_lehrveranstaltung
			JOIN public.tbl_benutzer USING(uid)
			JOIN public.tbl_person USING(person_id)
			JOIN public.tbl_prestudent ON(public.tbl_prestudent.person_id=public.tbl_person.person_id)
			LEFT JOIN public.tbl_studentlehrverband ON(public.tbl_prestudent.prestudent_id=public.tbl_studentlehrverband.prestudent_id AND campus.vw_student_lehrveranstaltung.studiensemester_kurzbz=public.tbl_studentlehrverband.studiensemester_kurzbz)
			LEFT JOIN lehre.tbl_zeugnisnote on(vw_student_lehrveranstaltung.lehrveranstaltung_id=tbl_zeugnisnote.lehrveranstaltung_id AND tbl_zeugnisnote.student_uid=tbl_student.student_uid AND tbl_zeugnisnote.studiensemester_kurzbz=tbl_studentlehrverband.studiensemester_kurzbz)
			LEFT JOIN bis.tbl_bisio ON(tbl_student.prestudent_id=tbl_bisio.prestudent_id)
		WHERE
			vw_student_lehrveranstaltung.lehrveranstaltung_id=".$db->db_add_param($lvid)." AND
			vw_student_lehrveranstaltung.studiensemester_kurzbz=".$db->db_add_param($stsem);

if($lehreinheit_id!='')
	$qry.=" AND vw_student_lehrveranstaltung.lehreinheit_id=".$db->db_add_param($lehreinheit_id);

$qry.=' ORDER BY nachname, vorname, person_id, tbl_bisio.bis DESC';
//echo $qry;
if($result = $db->db_query($qry))
{
	$i=0;
	while($elem = $db->db_fetch_object($result))
	{
		//Abbrecher und Unterbrecher nicht anzeigen
		if($elem->status!='Abbrecher' && $elem->status!='Unterbrecher')
		{
			$i++;
			if($i%2)
				$pdf->SetFillColor(190,190,190);
			else
				$pdf->SetFillColor(255,255,255);

			$pdf->SetFont('Arial','',8);
			$maxY=$pdf->GetY();
			if($maxY>770)
			{
				$pdf->AddPage();
				$maxY=$pdf->GetY();
			}

			$vorname=mb_convert_encoding(trim($elem->vorname),'ISO-8859-15','UTF-8');
			$nachname=mb_convert_encoding(trim($elem->nachname),'ISO-8859-15','UTF-8');
			$name="$vorname $nachname";
			$matrikelnr=trim(mb_convert_encoding($elem->matrikelnr,'ISO-8859-15','UTF-8'));
			$sem_verb_grup=trim(mb_convert_encoding($elem->semester.$elem->verband.$elem->gruppe,'ISO-8859-15','UTF-8'));




			$maxX=30;
			$pdf->SetXY($maxX,$maxY);
			$pdf->MultiCell(20,$lineheight,$i,1,'R',1);
			$maxX +=20;
			$pdf->SetXY($maxX,$maxY);
			$pdf->SetFont('Courier','B',8);
			$pdf->MultiCell(130,$lineheight,$nachname,1,'L',1);
			$pdf->SetFont('Arial','',8);
			$pdf->SetXY($maxX+strlen($nachname)*5+1,$maxY);
			if($elem->status=='Incoming') //Incoming
				$inc=' (i)';
			else
				$inc='';

			if($elem->bisio_id!='' && $elem->status!='Incoming' && ($elem->bis > $stsemdatumvon || $elem->bis=='') && $elem->von < $stsemdatumbis) //Outgoing
				$inc.=' (o)';

			if($elem->note==6) //angerechnet
				$inc.=' (ar)';


			$pdf->MultiCell(130,$lineheight,$vorname.$inc,0,'L',0);
			$maxX +=130;
			$pdf->SetXY($maxX,$maxY);
			$pdf->SetFont('Arial','',8);
			$pdf->MultiCell(65,$lineheight,$matrikelnr,1,'C',1);
			$maxX +=65;
			$pdf->SetXY($maxX,$maxY);
			$pdf->MultiCell(65,$lineheight,$sem_verb_grup,1,'C',1);
			$maxX +=65;
			$pdf->SetXY($maxX,$maxY);
			$pdf->MultiCell(40,$lineheight,'',1,'L',1);
			$maxX +=40;
			$pdf->SetXY($maxX,$maxY);
			$pdf->MultiCell(40,$lineheight,'',1,'L',1);
			$maxX +=40;
			$pdf->SetXY($maxX,$maxY);
			$pdf->MultiCell(40,$lineheight,'',1,'L',1);
			$maxX +=40;
			$pdf->SetXY($maxX,$maxY);
			$pdf->MultiCell(40,$lineheight,'',1,'L',1);
			$maxX +=40;
			$pdf->SetXY($maxX,$maxY);
			$pdf->MultiCell(40,$lineheight,'',1,'L',1);
			$maxX +=40;
			$pdf->SetXY($maxX,$maxY);
			$pdf->MultiCell(40,$lineheight,'',1,'L',1);
		    $inhalt[]=array($i,$nachname.' '.$vorname,$matrikelnr,$sem_verb_grup,'','','','','','');
		}
   }
}
//Fussnote
$maxY=$pdf->GetY()+5;
$maxX=30;
$pdf->SetXY($maxX,$maxY);
$pdf->SetFont('Arial','',8);
$pdf->MultiCell(520,$lineheight,'(i)  ... Incoming',0,'L',0);
$pdf->MultiCell(520,$lineheight,'(o)  ... Outgoing',0,'L',0);
$pdf->MultiCell(520,$lineheight,'(ar) ... angerechnet',0,'L',0);

//FHStg
$maxY=$pdf->GetY()+5;
$maxX=30;
$pdf->SetXY($maxX,$maxY);
$pdf->SetFont('Arial','B',8);


$bezeichnung=mb_convert_encoding('Fachhochschulstudiengang ('.strtoupper($stgobj->typ).') '.$stgobj->bezeichnung,'ISO-8859-15','UTF-8');
$pdf->MultiCell(520,$lineheight,$bezeichnung,0,'L',0);

//FHStg
$maxY=$pdf->GetY();
$maxX=30;
$pdf->SetXY($maxX,$maxY);
$pdf->SetFont('Arial','',8);
$pdf->MultiCell(520,$lineheight,'Fehlt ein/e Student/in länger als 2 Wochen, bitte um einen deutlichen Vermerk auf der Anwesenheitsliste. Die Anwesenheitsliste bitte am Ende des Monats im Sekretariat abgeben! Bitte achten Sie darauf, dass Sie nur VOLLST�NDIG AUSGEF�LLTE LISTEN abgeben!',0,'L',0);
$pdf->Output('anwesenheitsliste.pdf','I');
?>
