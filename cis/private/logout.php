<?php
require_once('../../config/cis.config.inc.php');

if (isset($_COOKIE['fhclogout']) && ($_COOKIE['fhclogout'] === 'fhclogout'))
{
	setcookie('fhclogout', '', -1, '/');
	http_response_code(401);
	header('WWW-Authenticate: Basic realm="' . AUTH_NAME . '"');
	?>
	<!doctype html>
	<html>
		<head>
			<title>FH-Complete logout Basic Auth</title>
				<meta http-equiv="refresh" content="2; url=<?php echo APP_ROOT . 'cis/'; ?>"/>
			</head>
		<body>
			<script>
				function logout()
				{
					console.log('FH-Complete logout Basic Auth');
					window.location.href = '<?php echo APP_ROOT . 'cis/'; ?>';
				}

				logout();
			</script>
		</body>
	</html>
	<?php
}
else
{
	http_response_code(303);
	header('Location:' . APP_ROOT . 'cis/');
}