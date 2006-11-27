<?php
include('../vilesci/config.inc.php');
?>

// SemesterPlan aktualisieren
function onSTPLSemesterRefresh()
{
	// Welche Ansicht ist aktiv?
	var semesterplan=document.getElementById('tabpanels-left');
	var panelIndex=semesterplan.getAttribute("selectedIndex");
	if (panelIndex==0)
		onVerbandSelect();
	else if (panelIndex==1)
		onOrtSelect();
	else if (panelIndex==2)
		onLektorSelect();
}

// SemesterPlan drucken
function onSTPLSemesterPrint()
{
	// Wie ist gerade die Source vom iFrame
	var iframeTimeTableSemester=document.getElementById('iframeTimeTableSemester');
	var iframeTimeTableSemesterSource=iframeTimeTableSemester.getAttribute("src");
	var src=iframeTimeTableSemesterSource.replace("content/timetable-week.xul.php","stdplan/stpl_kalender.php");
	var newWindow=window.open(src, "subWindowTimeTableSemester","");
}
