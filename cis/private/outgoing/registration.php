<?php
/* Copyright (C) 2012 Technikum-Wien
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
 * Authors: Karl Burkhart <burkhart@technikum-wien.at>
 * 
 */

require_once('../../../config/cis.config.inc.php');
require_once('../../../include/preoutgoing.class.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/phrasen.class.php');

$uid = get_uid(); 

$sprache = getSprache(); 
$p=new phrasen($sprache); 

$outgoing = new preoutgoing(); 
if($outgoing->loadUid($uid))
    header("Location: outgoing.php?ansicht=auswahl"); 


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
		<title><?php echo $p->t('incoming/outgoingRegistration'); ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
	</head>
	<body>
        <h1><?php echo $p->t('incoming/outgoingRegistration'); ?></h1>
        <br>
        <div id="test" style="margin-left:50px; margin-right:50px; font-size:16px;"><?php echo $p->t('incoming/willkommenBeiOutgoingAnmeldung');?></div>
        <table width="100%">
            <tr>
                <td align="center"> <form action ="outgoing.php?method=new&ansicht=auswahl" method="POST">
                    <input type="submit" value="<?php echo $p->t('incoming/zurAnmeldung');?>"/>
                     </form>
                </td>
            </tr>
        </table>
        
    </body>
</html>

