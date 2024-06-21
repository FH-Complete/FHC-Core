<?php
$path = "../../../vendor/vuejs/vuedatepicker_js/vue-datepicker.iife.js";

if(file_exists($path))
{
	header('Content-Type: application/javascript');
	echo file_get_contents($path);
	echo "export default VueDatePicker";
}
else
{
	header('HTTP/1.0 404 Not Found');
}
