<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// broaden the allowed URI characters just for tests
$config['permitted_uri_chars'] = 'a-z A-Z 0-9~%.:_\-';
// ensure we read REQUEST_URI
$config['uri_protocol']       = 'REQUEST_URI';
