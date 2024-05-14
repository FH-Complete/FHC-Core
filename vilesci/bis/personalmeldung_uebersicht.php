<?php
/* Copyright (C) 2017 fhcomplete.org
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
 * Authors: Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 */
require_once('../../config/vilesci.config.inc.php');

echo '<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">
		<title>Mitarbeitermeldung</title>
	</head>
<body>
<h2>Personalmeldung Übersicht</h2>
<ul>
	<li>
		<a href="personal_lektorenohnelehrauftrag.php">Lektoren ohne Lehraufträge</a><br>
		Deaktivieren von freien Lektoren die keinen Lehrauftrag mehr haben<br><br>
	</li>
	<li>
		<a href="personal_generateverwendung.php">Verwendungen generieren</a><br>
		Verwendungen aktualisieren für freie Lektoren die einen aktuellen Lehrauftrag
		haben, jedoch keine aktuelle Verwendung<br><br>
	</li>
	<li>
		<a href="checkverwendung.php">Plausibilitätsprüfungen Verwendungen</a><br>
		Diverse Prüfungen auf inkonsistente Daten<br><br>
	</li>
	<li>
		<a href="personalmeldung.php">Meldung generieren</a><br>
		Abschließende Plausibilitätsprüfungen durchführen und Meldung generieren<br><br>
	</li>
</ul>
';
