<?php

if(file_exists("../../../vendor/vuepic/vue-datepicker-js/vue-datepicker.iife.js"))
{
	header('Content-Type: application/javascript');
	echo file_get_contents("../../../vendor/vuepic/vue-datepicker-js/vue-datepicker.iife.js");
	echo "export default VueDatePicker";
}
else
{
	header('HTTP/1.0 404 Not Found');
}
