<?php
require_once('xsdformprinter.php');

$xsd = file_get_contents('examples/shiporder1.xsd');

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>XSDFormPrinter</title>
<link rel="stylesheet" href="style.css" type="text/css">
<script type="text/javascript" src="../tiny_mce/tiny_mce.js"></script>
<script type="text/javascript" src="../fhcomplete/trunk/include/js/jquery.js"></script>
<script type="text/javascript" src="jquery.xmlDOM.js"></script>
<script type="text/javascript">

tinyMCE.init
(
	{
	mode : "textareas",
	theme : "simple"
	}
);
</script>
</head>
<body>
';
if(isset($_POST['XSDFormPrinter_XML']))
{
	echo nl2br(htmlentities($_POST['XSDFormPrinter_XML'])).'<hr>';
}
$xfp = new XSDFormPrinter\XSDFormPrinter();
$xfp->output($xsd);
?>
</body>
</html>
