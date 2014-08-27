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
 * Basisklasse fuer Menue Addons
 * 
 * Diese Klasse dient als Basisklasse fuer alle Menue Addons des CMS Systems
 */

require_once(dirname(__FILE__).'/../../include/basis_db.class.php');
require_once(dirname(__FILE__).'/../../include/functions.inc.php');

class menu_addon extends basis_db
{
	/**
	 * Konfigurationsarray fuer die Linkliste 
	 * $items[0] = array ('title'=>'title des links',
	 *                    'link'=>'url des links',
	 *                    'target'=>'target des links',
	 *                    'name'=>'Anzeigename des Links'); 
	 */
	protected $items=array();
	
	/**
	 * HTML Code fuer direkte Ausgabe
	 */
	protected $block;
	
	/**
	 * Wenn true, wird der HauptLink im Menue angezeigt, sonst nicht
	 */
	protected $link=true;
	
	/**
	 * Konfigurationsarray fuer den HauptLink
	 * array ('name'=>'name des links',
	 *        'link'=>'url des links',
	 *        'target'=>'target des links',
	 *        'content_id'=>'content_id des submenues das aufklappen soll'); 
	 */
	protected $linkitem = array();
		
	/**
	 * Konstruktor
	 */
	public function __construct()
	{
		global $includeparams;
		
		$content = $includeparams['content'];
		
		$this->linkitem = array('name'=>$content->titel,
						'link'=>'#open',
						'target'=>'_self',
						'content_id'=>$content->content_id);
	}

	/**
	 * Ausgabe der Daten
	 */
	public function output()
	{
		global $includeparams;
		$content = $includeparams['content'];
		
		if($this->link)
			($content->menu_open?(DrawLink($this->linkitem['link'],$this->linkitem['target'],$this->linkitem['name'],$this->linkitem['content_id'])):'');
			//Wenn Option "Menü öffen" angeklickt ist, erschein die Überschrift, sonst nicht -> Ist eine Pfusch-Lösung. Was kann man sonst machen damit Überschrift "Meine LV" nicht angezeigt wird?
		//echo '
			//<table class="tabcontent" id="Content'.$content->content_id.'" style="display: '.($content->menu_open?'visible':'none').'"><tr><td>';
		
		$this->outputBlock();
		$this->outputItems();
		
		//echo '</td></tr></table>';
	}
	
	/**
	 * Gibt alle Items als Linkliste aus
	 * 
	 */
	public function outputItems()
	{
		$user = get_uid();
		$is_lector=check_lektor($user);
		
		$sprache = getSprache(); 
		$p=new phrasen($sprache);
		
		if(count($this->items)>0)
		{
			$this->outputItems1($this->items);
			
		}
	}
	
	private function outputItems1($item, $child=false)
	{
		$menu=false;
		foreach($item as $row)
		{
			if(isset($row['childs']))
				$menu=true;
		}
		if($menu || $child)
			echo '<ul class="menu">';
		else
			echo '<ul>';
			
		foreach($item as $row)
		{
			if($menu || $child)
				echo '<li>';
			else
				echo '<li style="margin:0px">';
		
			if(isset($row['childs']))
				$class='item2';
			else
				$class='leaf';
			
			echo '<a class="'.$class.' " title="'.$row['title'].'" href="'.$row['link'].'" target="'.$row['target'].'">'.$row['name'].'</a>';
			
			if(isset($row['childs']))
			{
				$this->outputItems1($row['childs'],true);
			}
			echo '	</li>';
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