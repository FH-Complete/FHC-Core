<?php

if(file_exists("../../../vendor/tinymce/tinymce5/tinymce.min.js"))
{
	header('Content-Type: application/javascript');

	//echo file_get_contents("../../../vendor/tinymce/tinymce5/tinymce.min.js");

	//echo file_get_contents("../../../vendor/tinymce/tinymce5/tinymce.min.js");
	//echo "export default tinymce";

	//echo "export default " . json_encode(file_get_contents('../../../vendor/tinymce/tinymce5/tinymce.min.js')) . ";";

	echo "var tinymce = ";
	echo file_get_contents('../../../vendor/tinymce/tinymce5/tinymce.min.js') . ";";
	echo "export default tinymce";

}
else
{
	header('HTTP/1.0 404 Not Found');
}