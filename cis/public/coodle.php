<?php

/* Copyright (C) 2012 FH Technikum-Wien
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
 * Authors: Karl Burkhart 	<burkhart@technikum-wien.at>,
 * 			Andreas Österreicher <oesi@technikum-wien.at>
 */
require_once('../../config/cis.config.inc.php');
require_once('../../include/coodle.class.php');

$coodleId = (isset($_GET['coodle_id']) ? $_GET['coodle_id'] : '');
$accessKey = (isset($_GET['zugangscode']) ? $_GET['zugangscode'] : '');

$coodle = new coodle();
if (!$coodle->load($coodleId))
	die($coodle->errormsg);

$coodle->getRessourceFromUser($coodleId, '', $accessKey);
$redirectUrl = $coodle->uid ? APP_ROOT . 'cis.php/Cis/Coodle?id=' . $coodleId : APP_ROOT . 'cis.php/Cis/CoodleExternal/' . $coodleId . '/' . $accessKey;

header('Location: ' . $redirectUrl);
exit;
?>
