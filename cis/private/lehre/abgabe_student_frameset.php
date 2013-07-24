<?php
/* Copyright (C) 2010 FH Technikum Wien
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
 *          Karl Burkhart <karl.burkhart@technikum-wien.at>.
 */
if(isset($_GET['uid']))
	$uid = $_GET['uid'];
else
	$uid = '';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Frameset//EN">
<html>

<head>
	<title>Bachelor-/Diplomarbeitsabgabe - Student</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>

<frameset rows="200,*" frameborder="1" border="1" framespacing="1">
<?php
	echo '<frame src="abgabe_student.php'.($uid!=''?'?uid='.$uid:'').'" id="uebersicht" name="uebersicht"/>';
	echo '<frame src="abgabe_student_details.php'.($uid!=''?'?uid='.$uid:'').'" id="as_detail" name="as_detail"/>';
?>
	<noframes>
		<body bgcolor="#FFFFFF">
			This application works only with a frames-enabled browser.<br />
			<a href="main.php">Use without frames</a>
		</body>
	</noframes>
</frameset>

</html>