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
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Logout</title>
	<script type="text/javascript">
		function login() 
		{
			document.location="index.php";
		}
	</script>
</head>
<body>
<?php
	session_start();
	session_destroy();
	
	if(isset($_SERVER['REMOTE_USER']))
	{
		echo 'ACHTUNG! Sie sind per BASIC-Authentifizierung angemeldet. Ein Logout ist hierbei nicht möglich. Um sich korrekt auszuloggen schliessen Sie bitte ALLE Browserfenster.';
	}
	else
	{
		echo '
		<center>
		<strong>
		<br />
		<br />
		Sie wurden erfolgreich ausgeloggt!!<br /> Sie werden sofort weitergeleitet!<br />
		</strong>
		<script type="text/javascript">
		window.setTimeout("login()", 2500);
		</script>
		';
	}
?>
<br />
Sollten Sie nicht weitergeleitet werden klicken Sie bitte <a href="index.php">hier</a>
</center>
</body>
</html>
