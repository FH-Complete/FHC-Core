<?php
/* Copyright (C) 2011 FH Technikum-Wien
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
 * Authors: Andreas Oesterreicher 	<andreas.oesterreicher@technikum-wien.at>
 */
/**
 * Dies ist eine Vorlage fuer die Verwendung von Menue Addons
 */
require_once(dirname(__FILE__).'/menu_addon.class.php');

class menu_addon_test extends menu_addon
{
	public function __construct()
	{
		parent::__construct();
		
		//Link ausgeben oder nicht
		$this->link=true;
		
		//Liste mit Links
		$this->items[]=array('title'=>'Testlink 1',
							 'target'=>'content',
							 'link'=>'lesson.php',
							 'name'=>'tl1');
		$this->items[]=array('title'=>'Testlink 2',
							 'target'=>'content',
							 'link'=>'lesson.php',
							 'name'=>'tl2');
		$this->items[]=array('title'=>'Testlink 3',
							 'target'=>'content',
							 'link'=>'lesson.php',
							 'name'=>'tl3');
		
		// Eigener Codeblock
		$this->block='
			<form method="POST">
				<select name="stg_kz">
					<option value="1">1</option>
					<option value="2">2</option>
					<option value="3">3</option>
				</select>
				<input type="submit" value="ok">
			</form>
			';
		if(isset($_POST['stg_kz']))
			$this->block.='KZ:'.$this->convert_html_chars($_POST['stg_kz']);
			
		
		$this->output();
	}	
}

new menu_addon_test();
?>
