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

	// Wochentage auf Deutsch (Zeitwunsch)
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

	// Studiengaenge die keine Alias Email Adressen erhalten
	$noalias=array();
	$noalias[0]='330';
	$noalias[1]='331';
	$noalias[2]='204';
	
	
?>
