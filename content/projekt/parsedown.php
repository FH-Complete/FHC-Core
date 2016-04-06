<?php
/* Copyright (C) 2015 fhcomplete.org
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
 * Authors: Christian Paminger <christian@paminger.at>
 */
 //echo 'start';
 require_once('../../config/vilesci.config.inc.php');
 require_once('../../include/Parser/Parsedown.php');
 require_once('../../include/projekttask.class.php');
 require_once('../../include/projektphase.class.php');
 
 $Parsedown = new Parsedown();
 $task=new projekttask();
 $phase=new projektphase();
 
 
if(isset($_GET['projekttask_id']))
{
	$task->load($_GET['projekttask_id']);
	echo $Parsedown->text($task->beschreibung); 
}
elseif (isset($_GET['projektphase_id']))
{
	$phase->load($_GET['projektphase_id']);
	echo $Parsedown->text($phase->beschreibung); 
}
else
{
	die('"projekttask_id nor projektphase_id" is set!');
}
