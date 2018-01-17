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
require_once('../../../include/phrasen.class.php');
require_once('../../../include/person.class.php'); 

if(isset($_GET['lang']))
    setSprache($_GET['lang']);

$sprache = getSprache(); 
$p=new phrasen($sprache); 

if (isset($_POST['userid'])) 
{
    $login = trim($_REQUEST['userid']); 
    $person = new person(); 

    session_start();

    $person_id=$person->checkZugangscode($login); 

    //Zugangscode wird  überprüft
    if($person_id != false)
    {
        $_SESSION['prestudent/user'] = $login;
        $_SESSION['prestudent/person_id'] = $person_id; 

        header('Location: prestudent.php');
        exit;
    }
    else
    {
        $errormsg= $p->t('incoming/ungueltigerbenutzer');
    }
}

?>
<!DOCTYPE html>
<html>
    <head>
        <title>Prestudenten-Tool</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta name="robots" content="noindex">
        <link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
        <link href="../../../include/js/tablesort/table.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <table width="100%" border="0">
            <tr>
                <td align="left"></td>
                <td align ="right"><?php 		
                    echo $p->t("global/sprache")." ";
                    echo '<a href="'.$_SERVER['PHP_SELF'].'?lang=English">'.$p->t("global/englisch").'</a> | 
                    <a href="'.$_SERVER['PHP_SELF'].'?lang=German">'.$p->t("global/deutsch").'</a><br>';?>
                </td>
            </tr>
        </table>
        <form action ="index.php" method="POST">
            <table border ="0" width ="100%" height="40%">
                <tr height="50%">
                    <td align ="center" valign="center"><h3><?php echo $p->t('incoming/wilkommenAnFh'); ?>. <br> <?php echo $p->t('incoming/bitteCodeEingeben'); ?>: </h3><span style="font-size:1.2em"></span></td>
                </tr>
                <tr >
                    <td align="center" valign="bottom"> <img src="../../../skin/images/tw_logo_02.jpg"></td>
                </tr>
            </table>
            <table border ="0" width ="100%">
                <tr>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                        <td align="center"><input type="text" size="30" value="<?php echo $p->t('incoming/zugangscode') ?>" name ="userid" onfocus="this.value='';"></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td align="center"><input type="submit" value="Login" name="submit"></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td align="center"><div class="error"><?php if(isset($errormsg))
                        echo $errormsg; ?></div>
                </tr>
            </table>
        </form>
    </body>
</html>