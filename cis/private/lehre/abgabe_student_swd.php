<?php
/* Copyright (C) 2008 Technikum-Wien
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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
?>
<html>
<head>
<title>Abgabesystem_Studentensicht</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../../../skin/vilesci.css" type="text/css">
<link rel="stylesheet" href="../../../include/js/tablesort/table.css" type="text/css">
<script src="../../../include/js/tablesort/table.js" type="text/javascript"></script>
<script language="JavaScript">
</script>
</head>
<body class="background_main">
<FORM NAME=weiter id=weiter METHOD=POST ACTION="http://www.bsz-bw.de/cgi-bin/oswd-suche.pl">
<input type="hidden" name="ruecksprung" value="http://dav.technikum-wien.at/ruhan/portal/trunk/cis/private/lehre/abgabe_student_swd.php">
</form>
<?php
if(isset($_POST['subject_swd']))
{
	$subject_swd=$_POST['subject_swd'];
	echo "<script>document.getElementById(\"swd\").value='$subject_swd';</script>";
	echo "<script>
		if(opener.document.getElementById('kontrollschlagwoerter').value=='')
		{
			opener.document.getElementById('kontrollschlagwoerter').value='$subject_swd';window.close();
		}
		else
		{
			opener.document.getElementById('kontrollschlagwoerter').value=opener.document.getElementById('kontrollschlagwoerter').value+', $subject_swd';window.close();
		}
		</script>";
	
}
else 
{
	echo "<script>document.getElementById('weiter').submit();</script>";
}
?>
</body>
</html>