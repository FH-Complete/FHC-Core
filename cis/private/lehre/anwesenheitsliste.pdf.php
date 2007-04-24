<?php
   /**
    * anwesenheitsliste.pdf.php
    *
    * Erstellt eine Anwesenheitsliste im PDF-Format
    *
    * Aufruf:
    * anwesenheitsliste.pdf.php?stg=222&lfvt=1234 //alle Studenten vom Studiengang 222 Lehrfach 1234
    * anwesenheitsliste.pdf.php?stg=222&sem=1&lfvt=1234 //alle Studenten vom Studiengang 222 und Semester 1 Lehrfach 1234
    * anwesenheitsliste.pdf.php?stg=222&sem=1&verband=A&lfvt=1234 //alle Studenten vom Studiengang 222, Semester 1, Verband A Lehrfach 1234
    * anwesenheitsliste.pdf.php?stg=222&sem=1&verband=A&gruppe=1&lfvt=1234 //alle Studenten vom Studiengang 222, Semester 1, Verband A, Gruppe 1 Lehrfach 1234
    * anwesenheitsliste.pdf.php?stg=222&sem=1&einheit=DVT-1xyz1&lfvt=1234 //alle Studenten vom Studiengang 222, Semester 1,  Einheit DVT-1xyz1 Lehrfach 1234
    */

   setlocale(LC_ALL, "de");
   // Pfad zu fpdf
   define('FPDF_FONTPATH','../../../include/pdf/font/');
   // library einbinden
   require_once('../../../include/pdf/fpdf.php');
   
   require_once('../../config.inc.php');
   require_once('../../../include/person.class.php');
   require_once('../../../include/studiengang.class.php');
   require_once('../../../include/studiensemester.class.php');
   require_once('../../../include/lehrveranstaltung.class.php');
   require_once('../../../include/pdf.inc.php');
   error_reporting(E_ALL);
   ini_set('display_errors','1');
   
   if(!$conn=pg_pconnect(CONN_STRING))
      die('Fehler beim Herstellen der Datenbankverbindung');
   
   //Uebergabeparameter abpruefen
   if(isset($_GET['stg'])) //Studiengang
   {
   	  if(is_numeric($_GET['stg']))
      	$stg=$_GET['stg'];
      else
      	die('Fehler bei der Parameteruebergabe');
   }
   else 
   		$stg='';
   if(isset($_GET['sem'])) //Semester
   {
   	  if(is_numeric($_GET['sem']))
   	  	$sem=$_GET['sem'];
   	  else 
   	  	die('Fehler bei der Parameteruebergabe');
   }
   else 
   		$sem='';
   
   if(isset($_GET['verband'])) //Verband
      $verband=$_GET['verband'];
   else 
      $verband='';
   if(isset($_GET['gruppe'])) //Gruppe
      $gruppe=$_GET['gruppe'];
   else
	  $gruppe='';
   if(isset($_GET['gruppe_kurzbz'])) //Einheit
      $gruppe_kurzbz = $_GET['gruppe_kurzbz'];
   else 
      $gruppe_kurzbz='';
      
   if(isset($_GET['lvid']) && is_numeric($_GET['lvid']))
   		$lvid = $_GET['lvid'];
   	else 
   		die('Fehler bei der Parameteruebergabe');

/**
 * liefert den groesseren der beiden werte
 *
 */
function getmax($val1,$val2)
{
	return ($val1>$val2)?$val1:$val2;

}

//***************************************************************************************************************************************************

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
$pdf->Image("../../../skin/images/tw_logo_02.jpg","430","55","120","35","jpg","");
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

$stsem_obj = new studiensemester($conn);
$stsem = $stsem_obj->getaktorNext();

if($gruppe_kurzbz!='')
	$pdf->MultiCell(0,20,'Gruppe: '.$gruppe_kurzbz.' Studiensemester: '.$stsem);
else 
	$pdf->MultiCell(0,20,'Gruppe: '.strtoupper($stgobj->typ.$stgobj->kurzbz).' '.$sem.$verband.$gruppe.' Studiensemester: '.$stsem);
	
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
$pdf->MultiCell(280,$lineheight,'Anzahl der abgehaltenen Stunden',1,'L',0);
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
$pdf->MultiCell(520,$lineheight+2,'Lektoren',1,'L',1);

//Schleife aller lektoren

$qry = "SELECT distinct vorname, nachname FROM campus.vw_benutzer, lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter WHERE uid=mitarbeiter_uid AND tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND lehrveranstaltung_id='".addslashes($lvid)."' AND studiensemester_kurzbz='$stsem' ORDER BY nachname, vorname;";

if($result = pg_query($conn,$qry))
{
	while($row=pg_fetch_object($result))
	{
		$pdf->SetFont('Arial','',8);
		$maxY=$pdf->GetY();
		$maxX=30;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(280,$lineheight,"$row->vorname $row->nachname",1,'L',0);
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
$pdf->MultiCell(520,$lineheight+2,'Studenten',1,'L',1);

$maxY=$pdf->GetY();
$pdf->tablewidths = array(20,140,60,60,40,40,40,40,40,40); //Spaltenbreiten setzen
$pdf->SetLeftMargin(30);
$pdf->SetTopMargin(100); // Auch in der Funktion Header umstellen!!!

$aligns = array('R','L','C','C','L','L','L','L','L','L'); //Ausrichtung der Tabellen festlegen

$pdf->SetFont('Arial','',8);
$pdf->SetXY(30,$maxY);
$inhalt[]=array(' ','Hörer/Name','Kennzeichen','Gruppe','','','','','',''); //Spaltenueberschriften

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

//$stud = new student($conn);
//$result = $stud->getStudents($einheit,$gruppe,$verband,$sem,$stg);
$qry = 'SELECT distinct vorname, nachname, uid, matrikelnr, verband, gruppe, semester FROM ';
if($gruppe_kurzbz!='')
	$qry .= "campus.vw_student JOIN public.tbl_benutzergruppe USING(uid) WHERE studiensemester_kurzbz IS NULL AND gruppe_kurzbz='".addslashes($gruppe_kurzbz)."'";
else 
{
	$qry .= "campus.vw_student WHERE studiengang_kz='$stg' AND semester='$sem'";
	if($verband!='')
		$qry.=" AND verband='$verband'";
	if($gruppe!='')
		$qry.=" AND gruppe='$gruppe'";
}	
$qry.= " ORDER BY nachname, vorname";

if($result = pg_query($conn, $qry))
{
$i=0;
	while($elem = pg_fetch_object($result))
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

		$maxX=30;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(20,$lineheight,$i,1,'R',1);
		$maxX +=20;
		$pdf->SetXY($maxX,$maxY);
		$pdf->SetFont('Courier','B',8);
		$pdf->MultiCell(130,$lineheight,$elem->nachname,1,'L',1);
		$pdf->SetFont('Arial','',8);
		$pdf->SetXY($maxX+strlen($elem->nachname)*5+1,$maxY);
		$pdf->MultiCell(130,$lineheight,$elem->vorname,0,'L',0);
		$maxX +=130;
		$pdf->SetXY($maxX,$maxY);
		$pdf->SetFont('Arial','',8);
		$pdf->MultiCell(65,$lineheight,trim($elem->matrikelnr),1,'C',1);
		$maxX +=65;
		$pdf->SetXY($maxX,$maxY);
		$pdf->MultiCell(65,$lineheight,$elem->semester.$elem->verband.$elem->gruppe,1,'C',1);
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
	   $inhalt[]=array($i,$elem->nachname.' '.$elem->vorname,trim($elem->matrikelnr),$elem->semester.$elem->verband.$elem->gruppe,'','','','','','');
   }
}


/*







$stud = new student($conn);
$result = $stud->getStudents($einheit,$gruppe,$verband,$sem,$stg);

$i=0;

foreach($result as $elem)
{
   //Dummys filtern
   if(!preg_match('*dummy*',$elem->uid) && $elem->semester!=10)
   {
   	
   	
   	
   	
	   $i++;
	   $inhalt[]=array($i,$elem->nachname.' '.$elem->vornamen,trim($elem->matrikelnr),$elem->semester.$elem->verband.$elem->gruppe,'','','','','','');
   }
}
//Tabelle zeichnen
$h=$pdf->morepagestable($inhalt,10,$aligns);
*/




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
