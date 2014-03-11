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
$pdf->Image("../../../skin/images/logo.jpg","430","45","","45","jpg","");
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

$pdf->MultiCell(0,20,'Gruppe: '.$gruppen);
$pdf->MultiCell(0,20,'Studiensemester: '.$stsem);
	
$maxY=$pdf->GetY();
$maxY=getmax($maxY,$pdf->GetY());


$pdf->SetFont('Arial','B',12);

//Studenten
$pdf->SetFont('Arial','B',10);
$maxY=$pdf->GetY();
$maxX=30;
$pdf->SetXY($maxX,$maxY);

$maxY=$pdf->GetY();
$pdf->tablewidths = array(20,140,60,60,60); //Spaltenbreiten setzen
$pdf->SetLeftMargin(30);
$pdf->SetTopMargin(100); // Auch in der Funktion Header umstellen!!!

$aligns = array('R','L','C','C','C'); //Ausrichtung der Tabellen festlegen
$maxY+=10;
$pdf->SetFont('Arial','',8);
$pdf->SetXY(30,$maxY);
$lineheight=10;

//Studenten holen
$pdf->SetFont('Arial','',8);
		$maxY=$pdf->GetY();
		$maxX=30;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(20,$lineheight,'',1,'R',0);
		$maxX +=20;
		$pdf->SetXY($maxX,$maxY);
		$pdf->SetFont('Arial','B',8);
		$pdf->MultiCell(130,$lineheight,'HörerIn/Name',1,'L',0);
		$maxX +=130;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(65,$lineheight,'Kennzeichen',1,'C',0);
		$maxX +=65;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(65,$lineheight,'Gruppe',1,'C',0);
		$maxX +=65;
		//$pdf->SetXY($maxX,$maxY);
		//$pdf->MultiCell(80,$lineheight,'Foto',1,'C',0);
$stsem_obj = new studiensemester();
$stsem_obj->load($stsem);
$stsemdatumvon = $stsem_obj->start;
$stsemdatumbis = $stsem_obj->ende;
$qry = "SELECT 
			distinct on(nachname, vorname, person_id) vorname, nachname, matrikelnr, person_id, foto_sperre,
			tbl_studentlehrverband.semester, tbl_studentlehrverband.verband, tbl_studentlehrverband.gruppe,
			(SELECT status_kurzbz FROM public.tbl_prestudentstatus WHERE prestudent_id=tbl_student.prestudent_id ORDER BY datum DESC, insertamum DESC, ext_id DESC LIMIT 1) as status,
			tbl_bisio.bisio_id, tbl_bisio.bis, tbl_bisio.von,
			tbl_zeugnisnote.note 
		FROM 
			campus.vw_student_lehrveranstaltung JOIN public.tbl_benutzer USING(uid) 
			JOIN public.tbl_person USING(person_id) JOIN public.tbl_student ON(uid=student_uid) 
			LEFT JOIN public.tbl_studentlehrverband USING(student_uid,studiensemester_kurzbz)
			LEFT JOIN lehre.tbl_zeugnisnote on(vw_student_lehrveranstaltung.lehrveranstaltung_id=tbl_zeugnisnote.lehrveranstaltung_id AND tbl_zeugnisnote.student_uid=tbl_student.student_uid AND tbl_zeugnisnote.studiensemester_kurzbz=tbl_studentlehrverband.studiensemester_kurzbz)
			LEFT JOIN bis.tbl_bisio ON(uid=tbl_bisio.student_uid)
		WHERE 
			vw_student_lehrveranstaltung.lehrveranstaltung_id='".addslashes($lvid)."' AND 
			vw_student_lehrveranstaltung.studiensemester_kurzbz='".addslashes($stsem)."'";

if($lehreinheit_id!='')
	$qry.=" AND vw_student_lehrveranstaltung.lehreinheit_id='".addslashes($lehreinheit_id)."'";
$qry.=' ORDER BY nachname, vorname, person_id, tbl_bisio.bis DESC';

$lineheight=80;
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
				
			$pdf->SetFont('Arial','',10);
			$maxY=$pdf->GetY();
			if($maxY>660)
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
			$pdf->SetFont('Courier','B',10);
			$pdf->MultiCell(130,$lineheight,$nachname,1,'L',1);
			$pdf->SetFont('Arial','',10);
			$pdf->SetXY($maxX+strlen($nachname)*6+2,$maxY);
			if($elem->status=='Incoming')
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
			$pdf->SetFont('Arial','',10);
			$pdf->MultiCell(65,$lineheight,trim($matrikelnr),1,'C',1);
			$maxX +=65;
			$pdf->SetXY($maxX,$maxY);
			$pdf->MultiCell(65,$lineheight,$sem_verb_grup,1,'C',1);
			$maxX +=65;
//			$pdf->SetXY($maxX,$maxY);
//			$pdf->MultiCell(80,$lineheight,'',0,'L',1);
			if($elem->foto_sperre=='f')
				$pdf->Image(APP_ROOT."cis/public/bild.php?src=person&person_id=".$elem->person_id,$maxX+1,$maxY+1,0,"78","jpg","");
		   	$inhalt[]=array($i,$name,$matrikelnr,$sem_verb_grup,'');
		}
   }
}

$lineheight=10;
//Fussnote
$maxY=$pdf->GetY()+5;
$maxX=30;
$pdf->SetXY($maxX,$maxY);
$pdf->SetFont('Arial','',8);
$pdf->MultiCell(520,$lineheight,'(i) ... Incoming',0,'L',0);
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
/*
$maxY=$pdf->GetY();
$maxX=30;
$pdf->SetXY($maxX,$maxY);
$pdf->SetFont('Arial','',8);
$pdf->MultiCell(520,$lineheight,'Fehlt ein Student l�nger als 2 Wochen, bitte um einen deutlichen Vermerk auf der Anwesenheitsliste. Die Anwesenheitsliste bitte am Ende des Monats im Sekretariat abgeben! Bitte achten Sie darauf, dass Sie nur VOLLST�NDIG AUSGEF�LLTE LISTEN abgeben!',0,'L',0);
*/

$pdf->Output('anwesenheitsliste.pdf','I');
?>