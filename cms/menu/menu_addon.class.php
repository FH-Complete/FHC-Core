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

require_once(dirname(__FILE__).'/../../include/basis_db.class.php');

class menu_addon extends basis_db
{
	protected $items=array(); //title, link, target, name
	protected $block;
	
	/**
	 * Konstruktor
	 */
	public function __construct()
	{
	}

	/**
	 * Gibt alle Items als Linkliste aus
	 * 
	 */
	public function outputItems()
	{
		echo '<ul style="margin-top: 0px; margin-bottom: 0px;">';
		foreach($this->items as $row)
		{
			echo '<li>
				<a class="Item2" title="'.$row['title'].'" href="'.$row['link'].'" target="'.$row['target'].'">'.$row['name'].'</a>
				</li>';
		}
		echo '</ul>';
	}
	
	/**
	 * Gibt einen CodeBlock aus
	 */
	public function outputBlock()
	{
		echo $this->block;
	}
}
?>