<?php
 session_start();
     session_destroy();

     $hostname = $_SERVER['HTTP_HOST'];
     $path = dirname($_SERVER['PHP_SELF']);

     echo "Sie wurden erfolgreich ausgeloggt!!<br> Sie werden sofort weitergeleitet! ";

  // header('Location: http://'.$hostname.($path == '/' ? '' : $path).'/login.php');
?>

<html>
<head>
<title>logout</title>
</head>
<body>
<script type="text/javascript"></script>
 <script> function login() {
	document.location="login.php";

	}
	window.setTimeout("login()", 3000);
</script>
</body>
</html>
