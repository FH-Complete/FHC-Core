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
 * Authors: Nikolaus Krondraf <nikolaus.krondraf@technikum-wien.at>
 */

require_once('../../config/vilesci.config.inc.php');
require_once('../../include/lehrveranstaltung.class.php');

isset($_GET['stg_kz']) ? $stg_kz = $_GET['stg_kz'] : $stg_kz = NULL;
isset($_GET['sem']) ? $sem = $_GET['sem'] : $sem = NULL;

if(is_null($sem) || is_null($stg_kz))
    die("Studiengangskennzahl und Semester müssen übergeben werden");

$lva = new lehrveranstaltung;
$lva->load_lva($stg_kz, $sem, null, true, true, "bezeichnung");

if(is_array($lva->lehrveranstaltungen))
{
    $result = array();
    
    foreach($lva->lehrveranstaltungen as $value)
    {
        $result[$value->lehrveranstaltung_id] = $value->bezeichnung . " (" . $value->lehrform_kurzbz . ")";
    }
    
    echo json_encode($result);
}
else
{
    echo "Daten konnten nicht geladen werden";
}