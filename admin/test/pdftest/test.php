<?php
# $Id: hello.php,v 1.3.2.2 2002/01/22 19:48:48 tm Exp $

$p = PDF_new();
PDF_open_file($p, "");

PDF_set_info($p, "Creator", "PDF-LIB");
PDF_set_info($p, "Author", "Christian Paminger");
PDF_set_info($p, "Title", "TEST (PHP)");

PDF_begin_page($p, 595, 842);

# Change "host" encoding to "winansi" or whatever you need!
$font = PDF_findfont($p, "Courier", "host", 0);
PDF_setfont($p, $font, 10.0);

PDF_set_text_pos($p, 50, 700);
PDF_show($p, "Kalender");
PDF_continue_text($p, "(says PHP)");

PDF_end_page($p);
PDF_close($p);

$buf = PDF_get_buffer($p);
$len = strlen($buf);

header("Content-type: application/pdf");
header("Content-Length: $len");
header("Content-Disposition: inline; filename=hello.pdf");
print $buf;

PDF_delete($p);
?>
