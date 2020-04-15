<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

// ONLY FOR DEBUGGING - If you are unsure, don't change it. If the message should be sent immediately. Default false
$config['send_immediately'] = false;

$config['msg_delivery'] = true; // Default true
$config['system_person_id'] = 1; // Dummy sender, used for sending messages from the system
$config['redirect_view_message_url'] = '/system/messages/ViewMessage/redirectByToken/';
$config['message_html_view_url'] = '/system/messages/ViewMessage/toHTML/';

// Change this to CIS Server (https://cis.example.com/index.ci.php) if you are sending Messages from Vilesci
$config['message_server'] = site_url();
// Organization unit function that are allowed to read messages for the organisation unit
$config['ou_receivers'] = array('ass');
// Organization units that will never receive notice emails
$config['ou_receivers_no_notice'] = array('infocenter');
// Organization units that will not send the notice email to the internal account, but to the private one
$config['ou_receivers_private'] = array('eac', 'ewu', 'scs');
//
$config['ou_function_whitelist'] = array('ass', 'Leitung', 'fachzuordnung', 'oezuordnung');

$config['message_redirect_url'] = array();
$config['message_redirect_url']['fallback'] = site_url('system/messages/ViewMessage/writeReply');
// $config['message_redirect_url']['OE_ROOT_1'] = 'https://<server name>/addons/aufnahme/OE_ROOT/cis/index.php';
// $config['message_redirect_url']['OE_ROOT_2'] = 'https://<server name>/<where ever you like to land to a message reply page>';
