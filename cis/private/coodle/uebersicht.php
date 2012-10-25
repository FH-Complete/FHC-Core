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
 * Authors: Karl Burkhart 	<burkhart@technikum-wien.at>
 */

require_once('../../../config/cis.config.inc.php');
require_once('../../../include/coodle.class.php');
require_once('../../../include/functions.inc.php'); 
require_once('../../../include/phrasen.class.php');
require_once('../../../include/datum.class.php'); 

$lang = getSprache(); 

$p = new phrasen($lang); 

$uid = get_uid(); 
$message = '';

echo '
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
   <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <link rel="stylesheet"  href="../../../skin/fhcomplete.css" type="text/css">
        <link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">'; ?>

        <link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
        <link href="../../../skin/tablesort.css" rel="stylesheet" type="text/css">
        <link href="../../../skin/jquery.css" rel="stylesheet"  type="text/css"/>
        <script src="../../../include/js/tablesort/table.js" type="text/javascript"></script>
        <script src="../../../include/js/jquery.js" type="text/javascript"></script> 
        <script type="text/javascript">
        $(document).ready(function() 
        {
            $("#myTableFiles").tablesorter(
            {
                sortList: [[0,0]],
                widgets: ["zebra"]
            }); 
          
        }); 
       
       
        function conf_user(ersteller_uid)
        {
            var uid = ersteller_uid; 
            
            if(uid == '<?php echo $uid; ?>')
            {
                return true; 
            }
            else
                {
                    alert("<?php echo $p->t('global/keineBerechtigung'); ?>");
                    return false; 
                }
        }
        </script>
<?php

echo'   <title>'.$p->t('coodle/uebersicht').'</title>
    </head>
    <body>';

$method = isset($_GET['method'])?$_GET['method']:'';

// coodle umfrage löschen
if($method=='delete')
{
    $coodle= new coodle(); 
    $coodle_id = isset($_GET['coodle_id'])?$_GET['coodle_id']:'';
    
    if($coodle->load($coodle_id))
	{
        // löschen nur von eigenen Umfragen möglich
		if($coodle->ersteller_uid!=$uid)
			$message = '<span class="error">'.$p->t('global/keineBerechtigung').'</span>';
        else
        {
            if($coodle->delete($coodle_id))
                $message ='<span class="ok">Erfolgreich storniert!</span>';
            else
                $message ='<span class="error">'.$p->t('coodle/umfrageKonnteNichtGeloeschtWerden').'</span>';
        }
	}
    else
        $message = '<span class ="error">'.$p->t('coodle/umfrageNichtGeladen').'</span>';
}

echo'<h1>'.$p->t('coodle/uebersicht').'</h1>
    <br>
    <div style="display:block; text-align:left; float:left;"><a href="stammdaten.php" target="_blank">'.$p->t('coodle/neueUmfrage').'</a></div><br>
    <div style="display:block; text-align:right; margin-right:16px; ">'.$message.'</div>
    <table id="myTableFiles" class="tablesorter">
    <thead>
        <tr>
            <th width="5%">'.$p->t('coodle/coodleId').'</th>
            <th width="20%">'.$p->t('coodle/titel').'</th>
            <th width="40%">'.$p->t('coodle/beschreibung').'</th>
            <th>'.$p->t('coodle/letzterStatus').'</th>
            <th>'.$p->t('coodle/ersteller').'</th>
            <th>Endedatum</th>
            <th>'.$p->t('coodle/aktion').'</th>
        </tr>
    </thead>';

$datum = new datum(); 
$coodle = new coodle(); 
$coodle->getCoodleFromUser($uid);
foreach($coodle->result as $c)
{
    echo '<tr>
            <td>'.$coodle->convert_html_chars($c->coodle_id).'</td>
            <td>'.$coodle->convert_html_chars($c->titel).'</td>
            <td>'.(substr($c->beschreibung,0,40)).'</td>
            <td>'.$coodle->convert_html_chars($c->coodle_status_kurzbz).'</td>
            <td>'.$coodle->convert_html_chars($c->ersteller_uid).'</td>
            <td>'.$coodle->convert_html_chars($datum->formatDatum($c->endedatum, 'd.m.Y')).'</td>
            <td>
                <a href="stammdaten.php?coodle_id='.$c->coodle_id.'" target="_blank" onclick="return conf_user(\''.$c->ersteller_uid.'\');">&nbsp;<img src="../../../skin/images/edit.png" title="Umfrage bearbeiten"></a> 
                <a href="'.$_SERVER['PHP_SELF'].'?method=delete&coodle_id='.$c->coodle_id.'" onclick="return conf_user(\''.$c->ersteller_uid.'\');">&nbsp;<img src="../../../skin/images/delete_x.png" title="Umfrage löschen"></a>
                <a href="../../public/coodle.php?coodle_id='.$c->coodle_id.'"> &nbsp; <img src="../../../skin/images/date_go.png" title="zur Umfrage"></a>
            </td>
        </tr>';
}

?>
