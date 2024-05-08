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
require_once('../../../include/functions.inc.php');
require_once('../../../include/phrasen.class.php');

$sprache = getSprache(); 
$p= new phrasen($sprache);

?>

<html>
<head>
<title><?php echo $p->t('zeitwunsch/profil');?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
</head>
<body id="inhalt">
<H3><?php echo $p->t('zeitwunsch/erklaerung');?>:</H3>
<P><?php echo $p->t('zeitwunsch/kontrollierenSieIhreZeitwuensche');?>!<BR><BR>
</P>
<TABLE align="center" name="Zeitwerte">
  <TR>
    <TH><B><?php echo $p->t('zeitwunsch/wert');?></B></TH>
    <TH>
      <DIV align="center"><B><?php echo $p->t('zeitwunsch/bedeutung');?></B></DIV>
    </TH>
  </TR>
  <TR>
    <TD>
      <DIV align="right">2</DIV>
    </TD>
    <TD><?php echo $p->t('zeitwunsch/hierMoechteIchUnterrichten');?>!</TD>
  </TR>
  <TR>
    <TD>
      <DIV align="right">1</DIV>
    </TD>
    <TD><?php echo $p->t('zeitwunsch/hierKannIchUnterrichten');?>!</TD>
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
    <TD><?php echo $p->t('zeitwunsch/nurInNotfaellen');?>!</TD>
  </TR>
  <TR>
    <TD>
      <DIV align="right">-2</DIV>
    </TD>
    <TD><?php echo $p->t('zeitwunsch/hierAufGarKeinenFall');?>!</TD>
  </TR>
</TABLE>
<P>&nbsp;</P>
<H3><?php echo $p->t('zeitwunsch/folgendePunkteSindZuBeachten');?>:</H3>
<OL>
  <LI> <?php echo $p->t('zeitwunsch/verwendenSieDenWertNur');?>.</LI>
  <LI> <?php echo $p->t('zeitwunsch/esSolltenFuerJedeStunde');?>.</LI>
</OL>
<P><?php echo $p->t('zeitwunsch/beiProblemenWendenSieSichAn');?> <A class="Item" href="mailto:lvplan@technikum-wien.at"><?php echo $p->t('lvplan/lvKoordinationsstelle');?></A>.</P>
</body>
</html>