<?php
# $Id: personalize.php,v 1.6.2.2 2002/01/28 17:17:11 rp Exp $
$col1 = 70;
$col2 = 335;
$infile = "PDFlib-purchase-order.pdf";

$p = PDF_new();

PDF_open_file($p, "");

PDF_set_info($p, "Creator", "personalize.php");
PDF_set_info($p, "Author", "Thomas Merz");
PDF_set_info($p, "Title", "PDFlib personalization demo (php)");

$form = PDF_open_pdi($p, $infile, "", 0);
if (!$form) {
    die( "Couldn't open input file".$infile);
}

$page = PDF_open_pdi_page($p, $form, 1, "");
if (!$page) {
    die("Couldn't open page 1 in ".$infile);
}

$font = PDF_findfont($p, "Helvetica-Bold", "host", 0);

# get the dimensions of the imported form
$width = PDF_get_pdi_value($p, "width", $form, $page, 0);
$height = PDF_get_pdi_value($p, "height", $form, $page, 0);

PDF_begin_page($p, $width, $height);
PDF_place_pdi_page($p, $page, 0, 0, 1, 1);
PDF_close_pdi_page($p, $page);

PDF_setfont($p, $font, 18);
PDF_set_value($p, "leading", 24);
PDF_set_text_pos($p, $col1, 486);

PDF_show($p, "Doublecheck, Inc.");
PDF_continue_text($p, "Petra Porst");
PDF_continue_text($p, "500, Market St.");
PDF_continue_text($p, "94110 San Francisco, CA");
PDF_continue_text($p, "");
PDF_continue_text($p, "USA");
PDF_continue_text($p, "+1/950/123-4567");
PDF_continue_text($p, "+1/950/123-4568");
PDF_continue_text($p, "");
PDF_continue_text($p, "petra\@doublecheck.com");

$datestr = date("j M Y");
PDF_set_text_pos($p, $col2, 104);
PDF_continue_text($p, $datestr);

PDF_end_page($p);
PDF_close($p);
PDF_close_pdi($p, $form);

$buf = PDF_get_buffer($p);
$len = strlen($buf);

header("Content-type: application/pdf");
header("Content-Length: $len");
header("Content-Disposition: inline; filename=pdfclock_php.pdf");
print $buf;

PDF_delete($p);
?>
