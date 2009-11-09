<?php
	session_start();
	$uid=$REMOTE_USER;
	$uid='pam';
?>
<html>
<head>
<title>Passwort ?ndern </title>
<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
</head>
<body id="inhalt">
<?php
   if (!session_is_registered('count'))
   {
   	session_register('count');
	$count = 1;
   }
   else
   {
   	$count++;
	if ($count>3)
	{
		echo "<h4>Das Passwort Formular ist nach 3 Fehlern deaktiviert!</h4>";
		echo '<meta http-equiv="refresh" content="5; URL=https://cis.technikum-wien.at/profile/index.php">';
		exit;
	}
   }
?>
<font class="h1"><b>Änderung des FH Technikum Wien Passwortes</b></font>
<p>Sie können mit diesem Formular Ihr FH Technikum Wien Passwort ändern.<br>
Es wird sowohl Ihr Windows als auch Ihr Unix bzw. Mail Passwort geändert!</p>
<form method="POST" action="changepass.php">
  <table><tr><td>Username:</td>
    <td><INPUT type="text" name="username" readonly value="<?php echo $uid ?>"
    </td></tr>
    <tr><td>Altes Passwort:</td>
    <td><INPUT type="password" name="oldpass"></td></tr>
    <tr><td>Neues Passwort:</td>
    <td><INPUT type="password" name="newpass1"></td></tr>
    <tr>
      <td>
        Neues Passwort<br>wiederholen:
        </td>
    <td><INPUT type="password" name="newpass2"></td></tr>
</table>
<p>
	<input type="submit" value="Passwort ändern" name="Send">
	<input type="reset" value="Zurücksetzen" name="cancel">
</p>
</form>
</body>

</html>

