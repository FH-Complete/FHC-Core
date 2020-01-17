<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

// ONLY FOR DEBUGGING - If you are unsure, don't change it. If the message should be sent immediately. Default false
$config['send_immediately'] = false;

$config['msg_delivery'] = true; // Default true
$config['system_person_id'] = 1; // Dummy sender, used for sending messages from the system
$config['redirect_view_message_url'] = '/system/messages/Redirect/redirectByToken/';
$config['message_html_view_url'] = '/system/messages/ViewMessage/toHTML/';

// Change this to CIS Server (https://cis.example.com/index.ci.php) if you are sending Messages from Vilesci
$config['message_server'] = site_url();
$config['ou_receivers'] = array('ass');

$config['message_redirect_url'] = array();
$config['message_redirect_url']['fallback'] = site_url('system/messagesViewMessage/writeReply');
// $config['message_redirect_url']['OE_ROOT_1'] = 'https://<server name>/addons/aufnahme/OE_ROOT/cis/index.php';
// $config['message_redirect_url']['OE_ROOT_2'] = 'https://<server name>/<where ever you like to land to a message reply page>';
