<?php
/**
 * This Script is for Loading the Codeigniter Context in Non-Codeigniter Scripts
 * Usage:
 * $ci = require_once(ci_loader.php');
 * $ci->load->library('xxx');
 */
ob_start();
require_once('index.ci.php');
ob_get_clean();
return $CI;
