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
$stgobj=new studiengang($conn);
$stgobj->load($stg);
//Logo
$pdf->Image("../../../skin/images/logo.jpg","430","55","120","35","jpg","");
//$pdf->Image("../../../skin/images/tw_logo_02.jpg","400","30","116","43","jpg","");

$lvobj = new lehrveranstaltung($conn, $lvid);

$pdf->SetFont('Arial','',16);
$pdf->MultiCell(0,20,'Anwesenheitsliste '.$lvobj->bezeichnung,0,'L',0);

$pdf->SetFont('Arial','',10);
$pdf->SetFillColor(190,190,190);
//Bei langen Namen muss der Gruppenname etwas weiter unten angezeigt werden da er
//sonst von der zweiten Zeile des Titels ueberschrieben wird.
if(strlen($lvobj->bezeichnung)>50)
$pdf->SetXY(30,75);
else
$pdf->SetXY(30,60);

//$stsem_obj = new studiensemester($conn);
//$stsem = $stsem_obj->getaktorNext();

$qry = "SELECT distinct on(kuerzel, semester, verband, gruppe, gruppe_kurzbz) UPPER(stg_typ::varchar(1) || stg_kurzbz) as kuerzel, semester, verband, gruppe, gruppe_kurzbz from campus.vw_lehreinheit WHERE lehrveranstaltung_id='".addslashes($lvid)."' AND studiensemester_kurzbz='".addslashes($stsem)."'";
if($lehreinheit_id!='')
	$qry.=" AND lehreinheit_id='".addslashes($lehreinheit_id)."'";
	
$gruppen='';
if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		if($gruppen!='')
			$gruppen.=', ';
		if($row->gruppe_kurzbz=='')
			$gruppen.=trim($row->kuerzel.'-'.$row->semester.$row->verband.$row->gruppe);
		else
			$gruppen.=$row->gruppe_kurzbz;
	}
}
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
		$pdf->MultiCell(130,$lineheight,'Hörer/Name',1,'L',0);
		$maxX +=130;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(65,$lineheight,'Kennzeichen',1,'C',0);
		$maxX +=65;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(65,$lineheight,'Gruppe',1,'C',0);
		$maxX +=65;
		//$pdf->SetXY($maxX,$maxY);
		//$pdf->MultiCell(80,$lineheight,'Foto',1,'C',0);

$qry = "SELECT 
			distinct vorname, nachname, matrikelnr, person_id,
			tbl_studentlehrverband.semester, tbl_studentlehrverband.verband, tbl_studentlehrverband.gruppe,
			(SELECT rolle_kurzbz FROM public.tbl_prestudentrolle WHERE prestudent_id=tbl_student.prestudent_id ORDER BY datum DESC, insertamum DESC, ext_id DESC LIMIT 1) as status 
		FROM 
			campus.vw_student_lehrveranstaltung JOIN public.tbl_benutzer USING(uid) 
			JOIN public.tbl_person USING(person_id) JOIN public.tbl_student ON(uid=student_uid) 
			LEFT JOIN public.tbl_studentlehrverband USING(student_uid)
		WHERE 
			lehrveranstaltung_id='".addslashes($lvid)."' AND 
			vw_student_lehrveranstaltung.studiensemester_kurzbz='".addslashes($stsem)."' AND
			tbl_studentlehrverband.studiensemester_kurzbz='".addslashes($stsem)."'";

if($lehreinheit_id!='')
	$qry.=" AND lehreinheit_id='".addslashes($lehreinheit_id)."'";
	
$qry.=' ORDER BY nachname, vorname';
$lineheight=80;
if($result = pg_query($conn, $qry))
{
	$i=0;
	while($elem = pg_fetch_object($result))
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
	
			$maxX=30;
			$pdf->SetXY($maxX,$maxY);
			$pdf->MultiCell(20,$lineheight,$i,1,'R',1);
			$maxX +=20;
			$pdf->SetXY($maxX,$maxY);
			$pdf->SetFont('Courier','B',10);
			$pdf->MultiCell(130,$lineheight,$elem->nachname,1,'L',1);
			$pdf->SetFont('Arial','',10);
			$pdf->SetXY($maxX+strlen($elem->nachname)*6+2,$maxY);
			if($elem->status=='Incoming')
				$inc=' (i)';
			else 
				$inc='';
			$pdf->MultiCell(130,$lineheight,$elem->vorname.$inc,0,'L',0);
			$maxX +=130;
			$pdf->SetXY($maxX,$maxY);
			$pdf->SetFont('Arial','',10);
			$pdf->MultiCell(65,$lineheight,trim($elem->matrikelnr),1,'C',1);
			$maxX +=65;
			$pdf->SetXY($maxX,$maxY);
			$pdf->MultiCell(65,$lineheight,$elem->semester.$elem->verband.$elem->gruppe,1,'C',1);
			$maxX +=65;
//			$pdf->SetXY($maxX,$maxY);
//			$pdf->MultiCell(80,$lineheight,'',0,'L',1);
			$pdf->Image(APP_ROOT."cis/public/bild.php?src=person&person_id=$elem->person_id",$maxX+1,$maxY+1,0,"78","jpg","");

		   $inhalt[]=array($i,$elem->nachname.' '.$elem->vorname,trim($elem->matrikelnr),$elem->semester.$elem->verband.$elem->gruppe,'');
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

//FHStg
$maxY=$pdf->GetY()+5;
$maxX=30;
$pdf->SetXY($maxX,$maxY);
$pdf->SetFont('Arial','B',8);
$pdf->MultiCell(520,$lineheight,'Fachhochschulstudiengang ('.strtoupper($stgobj->typ).') '.$stgobj->bezeichnung,0,'L',0);

//FHStg
$maxY=$pdf->GetY();
$maxX=30;
$pdf->SetXY($maxX,$maxY);
$pdf->SetFont('Arial','',8);
$pdf->MultiCell(520,$lineheight,'Fehlt ein Student länger als 2 Wochen, bitte um einen deutlichen Vermerk auf der Anwesenheitsliste. Die Anwesenheitsliste bitte am Ende des Monats im Sekretariat abgeben! Bitte achten Sie darauf, dass Sie nur VOLLSTÄNDIG AUSGEFÜLLTE LISTEN abgeben!',0,'L',0);


$pdf->Output('anwesenheitsliste.pdf','I');
?>