<?php
# $Id: quickreference.php,v 1.6.2.3 2002/06/14 14:59:53 rp Exp $

$infile = "PDFlib-manual.pdf";
$maxrow = 2;
$maxcol = 2;
$pagecount = 4;
$width = 500.0;
$height = 770.0;
$startpage = 132;
$endpage = 135;

$p = PDF_new();

PDF_open_file($p, "");

PDF_set_info($p, "Creator", "quickreference.php");
PDF_set_info($p, "Author", "Thomas Merz");
PDF_set_info($p, "Title", "mini imposition demo (php)");

$manual = PDF_open_pdi($p, $infile, "", 0);
if (!$manual) {
    die("Couldn't open input file ".$infile);
}

$row = 0;
$col = 0;

for ($pageno = $startpage; $pageno <= $endpage; $pageno++) {
    if ($row == 0 && $col == 0) {
	PDF_begin_page($p, $width, $height);
	$font = PDF_findfont($p, "Helvetica-Bold", "host", 0);
	PDF_setfont($p, $font, 18);
	PDF_set_text_pos($p, 25, $height-24);
	PDF_show($p, "PDFlib 4.0 Quick Reference");
    }

    $page = PDF_open_pdi_page($p, $manual, $pageno, "");

    if (!$page) {
	die("Couldn't open page $pageno in $infile.\n");
    }

    PDF_place_pdi_page($p, $manual, $width/$maxcol*$col, $height - ($row + 1)
    		* $height/$maxrow, 1/$maxrow, 1/$maxrow);
    PDF_close_pdi_page($p, $page);

    $col++;
    if ($col == $maxcol) {
	$col = 0;
	$row++;
    }
    if ($row == $maxrow) {
	$row = 0;
	PDF_end_page($p);
    }
}

# finish the last partial page
if ($row != 0 || $col != 0) {
    PDF_end_page($p);
}

PDF_close($p);
PDF_close_pdi($p, $manual);

$buf = PDF_get_buffer($p);
$len = strlen($buf);

header("Content-type: application/pdf");
header("Content-Length: $len");
header("Content-Disposition: inline; filename=hello_php.pdf");
print $buf;

PDF_delete($p);
?>
