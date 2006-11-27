<?php
# $id$

$p = PDF_new();
PDF_open_file($p, "");

PDF_set_info($p, "Creator", "image.php");
PDF_set_info($p, "Author", "Rainer Schaaf");
PDF_set_info($p, "Title", "image sample (PHP)");

$imagefile = "nesrin.jpg";

$image = PDF_open_image_file($p, "jpeg", $imagefile, "", 0);
if (!$image) {
    die("Couldn't open image ".$imagefile);
}

# See the PDFlib manual for more advanced image size calculations
$width = PDF_get_value($p, "imagewidth", $image);
$height = PDF_get_value($p, "imageheight", $image);

# We generate a page with the image's dimensions
PDF_begin_page($p, $width, $height);
PDF_place_image($p, $image, 0, 0, 1);
PDF_close_image($p, $image);
PDF_end_page($p);

PDF_close($p);

$buf = PDF_get_buffer($p);
$len = strlen($buf);

header("Content-type: application/pdf");
header("Content-Length: $len");
header("Content-Disposition: inline; filename=image.pdf");
print $buf;

PDF_delete($p);
?>
