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
<div id="GlobalMenu">
	[ <a class='Item' href="index.html" target="_top"><?php echo $p->t('profil/home');?></a>
	| <a class='Item' href="menu.php?content_id=173" target="menu"><?php echo $p->t('profil/meinCis');?></a>
	| <a class='Item' href="menu.php?content_id=166" target="menu"><?php echo $p->t('lvaliste/lehrveranstaltungen');?></a>
	<?php
	if(CHOOSE_LAYOUT)
		echo '| <a class="Item" href="../layouts.php" target="content">Layouts</a>';
	?>
	| <a class='Item' href="../cms/dms.php?id=<?php echo $p->t('dms_link/cisHandbuch');?>" target="_blank"><?php echo $p->t('global/handbuch');?></a>
	]
</div>