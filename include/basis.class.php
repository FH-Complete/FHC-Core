<?php
/* Copyright (C) 2010 Technikum-Wien
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
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 *
 */

/**
* Implementation super class
*/
class basis 
{
	/**
	* Error message
	* @var base_errors $msgs
	*/
	public $errormsg;
	
	/**
	* Constructor
	*
	* @access public
	*/
	public function __construct($db_system='pgsql')
	{
		//empty
	}

	public function getErrorMsg()
	{
		return $this->errormsg;
	}

	/**
	 * wenn $var '' ist wird NULL zurueckgegeben
	 * wenn $var !='' ist werden Datenbankkritische
	 * Zeichen mit Backslash versehen und das Ergbnis
	 * unter Hochkomma gesetzt.
	 * XXX: Es wird nicht NULL sondern 'null' zurückgegeben, und der funktionsnamen ist sehr irreführend wenn auch single-quotes dazu kommen. -mp
	 */
	protected function addslashes($var)
	{
		return ($var!=''?"'".addslashes($var)."'":'null');
	}
}
?>