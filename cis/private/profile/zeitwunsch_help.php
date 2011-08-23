<?php
/* Copyright (C) 2006 Technikum-Wien
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
/**
 * Uebersicht der Zeitsperren der Mitarbeiter
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/globals.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/person.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/mitarbeiter.class.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/zeitsperre.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/fachbereich.class.php');
require_once('../../../include/organisationseinheit.class.php');
?>

<html>
<head>
<title>Profil</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
</head>
<body id="inhalt">
<H3>Erkl&auml;rung:</H3>
<P>Bitte kontrollieren/&auml;ndern Sie Ihre Zeitw&uuml;nsche und klicken Sie anschlie&szlig;end
  auf &quot;Speichern&quot;!<BR><BR>
</P>
<TABLE align="center" name="Zeitwerte">
  <TR>
    <TH><B>Wert</B></TH>
    <TH>
      <DIV align="center"><B>Bedeutung</B></DIV>
    </TH>
  </TR>
  <TR>
    <TD>
      <DIV align="right">2</DIV>
    </TD>
    <TD>Hier m&ouml;chte ich unterrichten</TD>
  </TR>
  <TR>
    <TD>
      <DIV align="right">1</DIV>
    </TD>
    <TD>Hier kann ich unterrichten</TD>
  </TR>
  <!--<TR>
    <TD>
      <DIV align="right">0</DIV>
    </TD>
    <TD>keine Bedeutung</TD>
  </TR>-->
  <TR>
    <TD>
      <DIV align="right">-1</DIV>
    </TD>
    <TD>Hier nur in extremen Notf&auml;llen</TD>
  </TR>
  <TR>
    <TD>
      <DIV align="right">-2</DIV>
    </TD>
    <TD>Hier auf gar keinen Fall !!!</TD>
  </TR>
</TABLE>
<P>&nbsp;</P>
<H3>Folgende Punkte sind zu beachten:</H3>
<OL>
  <LI> Verwenden Sie den Wert -2 nur, wenn Sie zu dieser Stunde wirklich nicht
    k&ouml;nnen, um eine bessere Optimierung zu erm&ouml;glichen.</LI>
  <LI>Es sollten f&uuml;r jede Stunde die tats&auml;chlich unterrichtet wird,
    mindestens das 3-fache an positiven Zeitw&uuml;nschen angegeben werden.<BR>
    Beispiel: Sie unterrichten 4 Stunden/Woche, dann sollten Sie mindestens
    12 Stunden im Raster mit positiven Werten ausf&uuml;llen.</LI>
</OL>
<P>Bei Problemen wenden Sie sich bitte an die <A class="Item" href="mailto:lvplan@technikum-wien.at">LV-Koordinationsstelle</A>.</P>
</body>
</html>