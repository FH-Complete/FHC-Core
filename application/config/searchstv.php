<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');


$CI =& get_instance();


$config['student'] = $CI->config->item('student', 'search');

$config['prestudent'] = $CI->config->item('prestudent', 'search');
