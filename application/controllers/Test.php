<?php
 
if ( !defined("PHPUNIT_TEST") ) {
    show_404();
}
 
class Test extends CI_Controller
{
    public function index()
    {
        // Yep... This is all we need.
        ini_set('error_reporting', E_ALL); // or error_reporting(E_ALL);
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
    }
}
