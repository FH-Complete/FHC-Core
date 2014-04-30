<!DOCTYPE html>
<?php
/*
 * Copyright 2014 fhcomplete.org
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 * 
 *
 * Authors: Stefan Puraner	<puraner@technikum-wien.at>
 */

require_once('../../../../config/cis.config.inc.php');
require_once('../../../../include/functions.inc.php');
require_once('../../../../include/benutzerberechtigung.class.php');
require_once('../../../../include/pruefungCis.class.php');
require_once('../../../../include/studiensemester.class.php');

$uid = get_uid();
$db = new basis_db();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

$studiensemester = new studiensemester();
$pruefung = new pruefungCis();
$pruefung->getPruefungByMitarbeiter($uid, $studiensemester->getakt());
if(empty($pruefung->result) && !$rechte->isBerechtigt('lehre/pruefungsanmeldungAdmin'))
    die('Sie haben keine Berechtigung für diese Seite');

?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Prüfungsanmeldung Verwaltung</title>
        <script src="../../../../include/js/datecheck.js"></script>
        <script src="../../../../include/js/jquery1.9.min.js"></script>
	<script src="../../../../include/js/jquery.tablesorter.min.js"></script>
        <script src="./pruefung.js"></script>
        <link rel="stylesheet" href="../../../../skin/jquery-ui-1.9.2.custom.min.css">
        <link rel="stylesheet" href="../../../../skin/fhcomplete.css">
        <link rel="stylesheet" href="../../../../skin/style.css.php">
        <link rel="stylesheet" href="../../../../skin/tablesort.css">
	<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
	<style type="text/css">
	    body {
		padding: 10px 0 0 10px;
	    }
	    
	    #stgWrapper {
		position: absolute;
		height: 80%;
		width: 450px;
		padding: 1.8em 1.5em 1.8em 1em;
		border-radius: 25px;
		/*border: 1px solid black;*/
		box-shadow: 0em 0em 2em 0.5em #888888 inset;
	    }
	    
	    #studiengaenge {
		/*border: 1px solid black;*/
		width: 94%;
		position: relative;
		float: left;
		padding: 0 1em 0em 1em;
		height: 100%;
		overflow: auto;
		overflow-x: hidden;
	    }
	    
	    
	    #prfWrapper {
		position: absolute;
		height: 80%;
		width: 200px;
		left: 510px;
		padding: 1.8em 1.5em 1.8em 1em;
		border-radius: 25px;
		/*border: 1px solid black;*/
		box-shadow: 0em 0em 2em 0.5em #888888 inset;
	    }
	    
	    #pruefungen {
		/*border: 1px solid black;*/
		width: 94%;
		position: relative;
		float: left;
		padding: 0 1em 0em 1em;
		height: 100%;
		overflow: auto;
		overflow-x: hidden;
	    }
	    
	    #anmWrapper {
		position: absolute;
		/*top: 45px;*/
		left: 760px;
		width: 400px;
		height: 80%;
		padding: 1.8em 1.5em 1.8em 1em;
		border-radius: 25px;
		/*border: 1px solid black;*/
		box-shadow: 0em 0em 2em 0.5em #888888 inset;
	    }
	    
	    #anmeldungen {
		height: 100%;
		overflow: auto;
		overflow-x: hidden;
	    }
	    
	    #anmeldungen > div, h2 {
		padding: 0.5em;
	    }
	    
	    #message {
		position: fixed;
		bottom: 0px;
		width: 100%;
		height: 2em;
		font-size: 1.5em;
		font-weight: bold;
	    }
	    
	    #sortable { 
		list-style-type: none; 
		margin: 0; 
		padding: 0; 
		width: 100%;
	    }
	    #sortable li { 
		margin: 0 3px 3px 3px; 
		padding: 0.2em 0.4em 0.4em; 
		padding-left: 1.5em; 
		font-size: 1.4em; 
		height: 18px;
		list-style-image: none;
		display: block;
	    }
	    #sortable li span {
		/*position: absolute;*/ 
		margin-left: -1.3em; 
		float:left;
	    }
	    
	    .resultOK {
		color: green;
	    }
	    
	    .resultNotOK {
		color: red;
	    }
	    
	    #sortable li a {
		float: left;
	    }
	    
	    #sortable li div {
		float: right;
		margin-left: 5px;
		font-size: 0.8em;
	    }
	    
	    .anmerkungInfo {
		text-align: right;
		width: 10%;
	    }
	    
	    #progressbar {
		position: fixed;
		width: 300px;
		top: 30%;
		left: 50%;
		margin-left: -150px;
		z-index: 100;
		background: '#9CFF29';
	    }
	    .modalOverlay {
		position: fixed;
		width: 100%;
		height: 100%;
		top: 0px;
		left: 0px;
		background-color: rgba(0,0,0,0.3); /* black semi-transparent */
	    }
	    
	    .studiengang {
		font-size: 1em;
		font-weight: bold;
	    }
	</style>
    </head>
    <body>
	<script>
	    $(document).ready(function(){
		loadStudiengaenge();
	    });
	</script>
	<h1>Anmeldungen Verwalten</h1>
	<div id='stgWrapper'>
	    <div id='studiengaenge'>
		<h2>Studiengänge</h2>
		<ul id='stgListe'>
		    
		</ul>
	    </div>
	</div>
	<div id='prfWrapper'>
	    <div id='pruefungen'>
		<h2>Prüfungen</h2>
		<ul id="pruefungenListe">

		</ul>
	    </div>
	</div>
	<div id='anmWrapper'>
	    <div id="anmeldungen">
		<h2>Anmeldungen</h2>
		<div id="anmeldeDaten">

		</div>
		<div id="reihungSpeichernButton">

		</div>
		<div id="kommentar">
		    
		</div>
		<div id="kommentarSpeichernButton">
		    
		</div>
	    </div>
	</div>
	
	<div id="message"></div>
	<div id="progressbar"></div>
    </body>
</html>
