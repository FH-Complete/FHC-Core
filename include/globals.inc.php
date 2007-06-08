<?php
	// Auth: Benutzer des Webportals
	//define ('USER_UID','strtolower(trim($_SERVER['REMOTE_USER']))');
	// fuer Testzwecke
	define ('USER_UID','pam');
	
	// Hintergrundfarben fuer Tabellen beim Zeitwunsch
	global $cfgStdBgcolor;
	$cfgStdBgcolor=array();
	$cfgStdBgcolor[0]="#FF0000";
	$cfgStdBgcolor[1]="#D44128";
	$cfgStdBgcolor[2]="#CA8780";
	$cfgStdBgcolor[3]="#C0C0C0";
	$cfgStdBgcolor[4]="#A2C294";
	$cfgStdBgcolor[5]="#4EA83C";
	$cfgStdBgcolor[6]="#006000";

	// Wochentage auf Deutsch (Zeitwunsch)
	global $tagbez;
	$tagbez=array();
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
