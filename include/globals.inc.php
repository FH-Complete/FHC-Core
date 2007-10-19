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
	$cfgStdBgcolor[4]="#88FF88";
	$cfgStdBgcolor[5]="#AAFFAA";
	$cfgStdBgcolor[6]="#CCFFCC";

	// Wochentage auf Deutsch (Zeitwunsch)
	global $tagbez;
	$tagbez=array();
	$tagbez[0]="Sonntag";
	$tagbez[1]="Montag";
	$tagbez[2]="Dienstag";
	$tagbez[3]="Mittwoch";
	$tagbez[4]="Donnerstag";
	$tagbez[5]="Freitag";
	$tagbez[6]="Samstag";

	// Studiengaenge die keine Alias Email Adressen erhalten
	$noalias=array();
	$noalias[0]='330';
	$noalias[1]='331';
	$noalias[2]='204';
	
	
?>
