<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

// ONLY FOR DEBUGGING - If you are unsure, don't change it. If the message should be sent immediately. Default false
$config['send_immediately'] = false;

$config['msg_delivery'] = true; // Default true
$config['system_person_id'] = 1; // Dummy sender, used for sending messages from the system
$config['redirect_view_message_url'] = '/Redirect/redirectByToken/';
$config['message_html_view_url'] = '/ViewMessage/toHTML/';

// Change this to CIS Server (https://cis.example.com/index.ci.php) if you are sending Messages from Vilesci
$config['message_server'] = site_url();
$config['assistent_function'] = 'ass';

$config['message_redirect_url'] = array();
$config['message_redirect_url']['fallback'] = site_url('ViewMessage/writeReply');
// $config['message_redirect_url']['OE_ROOT'] = 'https://SERVER-NAME/addons/aufnahme/OE_ROOT/cis/index.php';
