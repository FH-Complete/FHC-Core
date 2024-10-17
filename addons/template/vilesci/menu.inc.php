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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 */
/**
 * Hier koennen neue Menuepunkte im Vilesci definiert werden
 */

// Hauptmenuepunkt hinzufuegen
$menu_addon = array
(
	'Template'=>array
	(
		'name'=>'Template', 'opener'=>'true', 'hide'=>'true', 'permissions'=>array('basis/addon'), 'image'=>'../../skin/images/vilesci_addons.png',
		'link'=>'left.php?categorie=Template', 'target'=>'nav',
		'TemplateEntry1'=>array('name'=>'Template Entry1', 'link'=>'../addons/template/vilesci/index.php', 'target'=>'main'),
		'TemplateEntry2'=>array('name'=>'Template Entry2', 'link'=>'../addons/template/vilesci/index.php', 'target'=>'main')
	)
);
$menu = array_merge($menu,$menu_addon);

// Submenuepunkt hinzufuegen unter dem Hauptmenue Punkt "Admin"
$menu_addon = array
(
	'TemplateSubmenu'=>array('name'=>'Template', 'link'=>'../addons/template/vilesci/index.php', 'target'=>'main','permissions'=>array('basis/addon')),

);
$menu['Admin'] = array_merge($menu['Admin'],$menu_addon);
?>
