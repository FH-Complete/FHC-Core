<?php
	include('../config.inc.php');
	include('../../include/functions.inc.php');

?>

<html>
<head>
<title>Lektor Edit</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../include/styles.css" type="text/css">
</head>

<body class="background_main">
<h4>Lektor Neu</h4>
<form name="lektor_new" action="lektor_new_save.php">
  <table border="<?php echo $cfgBorder;?>" bgcolor="<?php echo $cfgThBgcolor; ?>">
<tr>
      <td>UID*</td>
      <td><input type="text" name="uid"></td></tr>
<tr><td>Titel</td><td><input type="text" name="titel"></td></tr>
<tr><td>Vornamen</td><td><input type="text" name="vornamen"></td></tr>
<tr><td>Nachname</td><td><input type="text" name="nachname"></td></tr>
<tr><td>Geburtsdatum*</td><td><input type="text" name="gebdatum"></td></tr>
<tr><td>gebort</td><td><input type="text" name="gebort"></td></tr>
<tr><td>eMail Technikum</td><td><input type="text" name="emailtw"></td></tr>
<tr><td>eMail Forward</td><td><input type="text" name="emailforw"></td></tr>
<tr><td>eMail Alias</td><td><input type="text" name="emailalias"></td></tr>
<tr><td>Kurzbezeichnung</td><td><input type="text" name="kurzbz"></td></tr>
<tr><td>Telefon Technikum</td><td><input type="text" name="studiengang_id"></td></tr>
<tr><td>Fix Angestellt</td><td><SELECT name="fixangestellt">
          <OPTION value="t">Ja</OPTION>
          <OPTION value="f" selected>Nein</OPTION>
        </SELECT></td></tr>
</table>

  <input type="submit" name="Save" value="Speichern">
  <input type="hidden" name="id" value="<?php echo $id; ?>">
</form>
</body>
</html>