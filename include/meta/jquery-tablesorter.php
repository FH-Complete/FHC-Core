<?php
/* Copyright (C) 2015 Technikum-Wien
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
 * Authors: Andreas Moik <moik@technikum-wien.at>
 */
//require_once(dirname(__FILE__).'/config/vilesci.config.inc.php'); Muss vor dieser Datei eingebunden werden!

$dr = DOC_ROOT;
$dr = str_replace($_SERVER["DOCUMENT_ROOT"], "", $dr);


//Originaldateien des Herstellers
echo '<link rel="stylesheet" type="text/css" href="'.$dr.'vendor/FHC-vendor/jquery-tablesorter/css/theme.default.css">';

echo '<script src="'.$dr.'vendor/FHC-vendor/jquery-tablesorter/js/jquery.tablesorter.js"></script>';


//Anpassungen
echo '<link rel="stylesheet" type="text/css" href="'.$dr.'include/vendor_custom/jquery-tablesorter/tablesort.css">';

?>
