<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

// Define configuration parameters
$config['email_number_to_sent'] = 1000; // Number of emails to sent each time sendAll is called
$config['email_number_per_time_range'] = 1; // Number of emails to sent before pause
$config['email_time_range'] = 1; // Length of the pause in seconds
$config['email_from_system'] = 'no-reply@technikum-wien.at';
$config['alias_from_system'] = 'No Reply';

// Smtp: if the CI email library has to connect to a smtp server
// Mail: if the system is setup to send emails with the standard php mail function
// Sendmail: if the system is setup to send email via Sendmail (or similar)
$config['protocol'] = ''; // mail, sendmail, or smtp

// If protocol is set to sendmail
$config['mailpath'] = ''; // SThe server path to Sendmail (or similar)

// If protocol is set to smtp
$config['smtp_host'] = 'localhost'; // SMTP Server Address
$config['smtp_port'] = 25;
$config['smtp_timeout'] = 5; // in seconds
$config['smtp_keepalive'] = false; // Enable persistent SMTP connections
$config['smtp_user'] = '';
$config['smtp_pass'] = '';
$config['wordwrap'] = true; // {unwrap}http://example.com/a_long_link_that_should_not_be_wrapped.html{/unwrap}
$config['wrapchars'] = 76; // Character count to wrap at.
$config['mailtype'] = 'html'; // html or text
$config['priority'] = 3; // Email Priority. 1 = highest. 5 = lowest. 3 = normal
$config['validate'] = false; // If true then the email address will be validated

// If enabled will be logged info about emails in Codeigniter error logs
$config['enable_debug'] = false;

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
