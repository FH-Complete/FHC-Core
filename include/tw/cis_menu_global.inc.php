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
 * Menue Oben Rechts fuer CIS Seite
 */

require_once ('../include/phrasen.class.php');
require_once ('../include/functions.inc.php');

$sprache = getSprache(); 
$p = new phrasen($sprache); 
?>

	<td style="border-right-width:1px; border-right-style:solid; border-color:#626B71;padding-right:3px;padding-left:3px;"><div id="GlobalMenu" style="display: inline; text-transform:uppercase; font-weight: bold"><a class='Item' href="index.html" target="_top"><?php echo $p->t('profil/home');?></a></div></td>
	<!--<td style="border-right-width:1px; border-right-style:solid; border-color:#626B71;padding-right:3px;"><div id="GlobalMenu" style="display: inline; text-transform:uppercase; font-weight: bold"><a class='Item' href="menu.php?content_id=173" target="menu"><?php echo $p->t('profil/meinCis');?></a></div></td>-->
	<!--<td style="border-right-width:1px; border-right-style:solid; border-color:#626B71;padding-right:3px;padding-left:3px;"><div id="GlobalMenu" style="display: inline; text-transform:uppercase; font-weight: bold"><a class='Item' href="menu.php?content_id=166" target="menu"><?php echo $p->t('lvaliste/lehrveranstaltungen');?></a></div></td>-->
	<!--<td style="border-right-width:1px; border-right-style:solid; border-color:#626B71;padding-right:3px;padding-left:3px;"><div id="GlobalMenu" style="display: inline; text-transform:uppercase; font-weight: bold"><a class='Item' href="../cis/private/lvplan/stpl_week.php" target="blank">Mein LV-Plan</a></div></td>-->
	<td style="border-right-width:1px; border-right-style:solid; border-color:#626B71;padding-right:3px;padding-left:3px;"><div id="GlobalMenu" style="display: inline; text-transform:uppercase; font-weight: bold"><a class='Item' href="http://fhcomplete.technikum-wien.at/dokuwiki/doku.php" target="blank">WIKI</a></div></td>
	<?php
	if(CHOOSE_LAYOUT)
		echo '<td style="border-right-width:1px; border-right-style:solid; border-color:#626B71;padding-right:5px;padding-left:5px;"><div id="GlobalMenu" style="display: inline; text-transform:uppercase; font-weight: bold"><a class="Item" href="../layouts.php" target="content">Layouts</a></div></td>';
	?>
	<td style="padding-left:3px;"><div id="GlobalMenu" style="display: inline; text-transform:uppercase; font-weight: bold"><a class='Item' href="../cms/dms.php?id=<?php echo $p->t('dms_link/cisHandbuch');?>" target="_blank"><?php echo $p->t('global/handbuch');?></a></div></td>
	&nbsp;&nbsp;&nbsp;&nbsp;
