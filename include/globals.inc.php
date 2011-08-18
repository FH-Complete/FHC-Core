<?php

	// Auth: Benutzer des Webportals
	define ('USER_UID','strtolower(trim($_SERVER["REMOTE_USER"]))');
	// fuer Testzwecke
	//define ('USER_UID','pam');

	// Hintergrundfarben fuer Tabellen beim Zeitwunsch
	global $cfgStdBgcolor;
	$cfgStdBgcolor=array();
	$cfgStdBgcolor[0]="#CC0000";
	$cfgStdBgcolor[1]="#FF2200";
	$cfgStdBgcolor[2]="#FF9922";
	$cfgStdBgcolor[3]="#FFFF55";
	$cfgStdBgcolor[4]="#C0ECC3";
	$cfgStdBgcolor[5]="#48FA66";
	$cfgStdBgcolor[6]="#CCFFCC";

	// Mehrsprachige Wochentage
	global $tagbez;
	$tagbez=array();
	$tagbez[1][1]="Montag";
	$tagbez[1][2]="Dienstag";
	$tagbez[1][3]="Mittwoch";
	$tagbez[1][4]="Donnerstag";
	$tagbez[1][5]="Freitag";
	$tagbez[1][6]="Samstag";
	$tagbez[1][7]="Sonntag";
	$tagbez[2][1]="Monday";
	$tagbez[2][2]="Tuesday";
	$tagbez[2][3]="Wednesday";
	$tagbez[2][4]="Thursday";
	$tagbez[2][5]="Friday";
	$tagbez[2][6]="Saturday";
	$tagbez[2][7]="Sunday";
	
	// Mehrsprache Monatstage 
	global $monatsname;
	$monatsname = array();
	$monatsname[1][0]="Januar";
	$monatsname[1][1]="Februar";
	$monatsname[1][2]="März";
	$monatsname[1][3]="April";
	$monatsname[1][4]="Mai";
	$monatsname[1][5]="Juni";
	$monatsname[1][6]="Juli";
	$monatsname[1][7]="August";
	$monatsname[1][8]="September";
	$monatsname[1][9]="Oktober";
	$monatsname[1][10]="November";
	$monatsname[1][11]="Dezember";
	$monatsname[2][0]="January";
	$monatsname[2][1]="February";
	$monatsname[2][2]="March";
	$monatsname[2][3]="April";
	$monatsname[2][4]="May";
	$monatsname[2][5]="June";
	$monatsname[2][6]="July";
	$monatsname[2][7]="August";
	$monatsname[2][8]="September";
	$monatsname[2][9]="October";
	$monatsname[2][10]="November";
	$monatsname[2][11]="December";
	//$monatsname = array("Januar", "Februar", "M&auml;rz", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember");

	// Studiengaenge die keine Alias Email Adressen erhalten
	$noalias=array();
	$noalias[0]='330';
	$noalias[1]='331';
	$noalias[2]='204';
	
	
?>
