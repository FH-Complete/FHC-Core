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

require_once('../../../config/vilesci.config.inc.php');
require_once('../../../include/mail.class.php');
?>

<HTML>
<HEAD>
	<TITLE>VileSci-ServerTests</TITLE>
	<META http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<LINK rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</HEAD>

<?php
$msg='';
// Test-Email versenden
if(isset($_POST['Abschicken']))
{
    $mail = new mail($_POST['sendto'], $_POST['sendfrom'], 'Test', 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Link vollständig darzustellen.');
	$mail->setHTMLContent('Dies ist ein Test'); 
	if(!$mail->send())
		$msg.= '<span class="error">Fehler beim Senden des Mails</span><br />';
	else
		$msg.= ' Mail verschickt an '.$_POST['sendto'];
    
}
?>



<BODY class="background_main">
	<H2>VileSci ServerTests</H2>
	<UL>
		<LI><A href="php-info.php" class="linkblue">PHP-Info</A></LI>
		<LI><A href="pdftest/test.php" class="linkblue">PDF-Test</A></LI>
		<LI>
			<FORM name="mail" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
				Mail Verschicken an:
				<INPUT type="text" name="sendto" value="pam@technikum-wien.at"><br>
                Von:
                <INPUT type="text" name="sendfrom" value=""> <br>
				<INPUT type="submit" name="Abschicken" value="Go">
			</FORM>
            <BR>
            <BR>
            <?php echo $msg; ?>
		</LI>
	</UL>
</BODY>
</HTML>

