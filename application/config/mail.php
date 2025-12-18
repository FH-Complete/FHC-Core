<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

// Define configuration parameters
$config['email_number_to_sent'] = 1000; // Number of emails to sent each time sendAll is called
$config['email_number_per_time_range'] = 882; // Number of emails to sent before pause
$config['email_time_range'] = 1; // Length of the pause in seconds
$config['email_from_system'] = 'no-reply@technikum-wien.at';
$config['alias_from_system'] = 'No Reply';

// If protocol is set to smtp
$config['smtp_host'] = 'localhost'; // SMTP Server Address
$config['smtp_port'] = 25;
$config['smtp_timeout'] = 1; // in seconds
$config['smtp_keepalive'] = false; // Enable persistent SMTP connections
$config['smtp_auth'] = false;
$config['smtp_user'] = '';
$config['smtp_pass'] = '';
$config['smtp_encryption'] = ''; // '', 'tls' or 'ssl'
$config['wordwrap'] = 76;
$config['is_html'] = true; // html or text
$config['priority'] = 3; // 1 = High, 3 = Normal, 5 = low. When null, the header is not set at all.

// If enabled will be logged info about emails
// 0: Disable debugging (you can also leave this out completely, 0 is the default).
// 1: Output messages sent by the client.
// 2: as 1, plus responses received from the server (this is the most useful setting).
// 3: as 2, plus more information about the initial connection - this level can help diagnose STARTTLS failures.
// 4: as 3, plus even lower-level information, very verbose, don't use for debugging SMTP, only low-level problems.
$config['enable_debug'] = 0;

// default sender
$config['sancho_mail_default_sender'] = defined('SANCHO_MAIL_DEFAULT_SENDER') ? SANCHO_MAIL_DEFAULT_SENDER : '';

// If to use images for custom mails
$config['sancho_mail_use_images'] = defined('SANCHO_MAIL_USE_IMAGES') ? SANCHO_MAIL_USE_IMAGES : false;

// image path for sancho mail, relativ to document root
$config['sancho_mail_img_path'] = defined('SANCHO_MAIL_IMG_PATH') ? SANCHO_MAIL_IMG_PATH : '';

// header image for custom mails
$config['sancho_mail_header_img'] = defined('SANCHO_MAIL_HEADER_IMG') ? SANCHO_MAIL_HEADER_IMG : '';

// footer image for custom mails
$config['sancho_mail_footer_img'] = defined('SANCHO_MAIL_FOOTER_IMG') ? SANCHO_MAIL_FOOTER_IMG : '';

