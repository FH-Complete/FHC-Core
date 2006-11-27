<?php
# $id$

$RADIUS = 200.0;
$MARGIN = 20.0;

$p = PDF_new();

PDF_open_file($p, "");

PDF_set_info($p, "Creator", "pdfclock.php");
PDF_set_info($p, "Author", "Rainer Schaaf");
PDF_set_info($p, "Title", "PDF clock (PHP)");

PDF_begin_page($p, 2 * ($RADIUS + $MARGIN), 2 * ($RADIUS + $MARGIN));

PDF_translate($p, $RADIUS + $MARGIN, $RADIUS + $MARGIN);
PDF_setcolor($p, "both", "rgb", 0.0, 0.0, 1.0, 0.0);
PDF_save($p);

# minute strokes 
PDF_setlinewidth($p, 2.0);
for ($alpha = 0; $alpha < 360; $alpha += 6)
{
    PDF_rotate($p, 6.0);
    PDF_moveto($p, $RADIUS, 0.0);
    PDF_lineto($p, $RADIUS-$MARGIN/3, 0.0);
    PDF_stroke($p);
}

PDF_restore($p);
PDF_save($p);

# 5 minute strokes
PDF_setlinewidth($p, 3.0);
for ($alpha = 0; $alpha < 360; $alpha += 30)
{
    PDF_rotate($p, 30.0);
    PDF_moveto($p, $RADIUS, 0.0);
    PDF_lineto($p, $RADIUS-$MARGIN, 0.0);
    PDF_stroke($p);
}

$ltime = getdate();

# draw hour hand 
PDF_save($p);
PDF_rotate($p, -(($ltime['minutes']/60.0)+$ltime['hours']-3.0)*30.0);
PDF_moveto($p, -$RADIUS/10, -$RADIUS/20);
PDF_lineto($p, $RADIUS/2, 0.0);
PDF_lineto($p, -$RADIUS/10, $RADIUS/20);
PDF_closepath($p);
PDF_fill($p);
PDF_restore($p);

# draw minute hand
PDF_save($p);
PDF_rotate($p, -(($ltime['seconds']/60.0)+$ltime['minutes']-15.0)*6.0);
PDF_moveto($p, -$RADIUS/10, -$RADIUS/20);
PDF_lineto($p, $RADIUS * 0.8, 0.0);
PDF_lineto($p, -$RADIUS/10, $RADIUS/20);
PDF_closepath($p);
PDF_fill($p);
PDF_restore($p);

# draw second hand
PDF_setcolor($p, "both", "rgb", 1.0, 0.0, 0.0, 0.0);
PDF_setlinewidth($p, 2);
PDF_save($p);
PDF_rotate($p, -(($ltime['seconds'] - 15.0) * 6.0));
PDF_moveto($p, -$RADIUS/5, 0.0);
PDF_lineto($p, $RADIUS, 0.0);
PDF_stroke($p);
PDF_restore($p);

# draw little circle at center
PDF_circle($p, 0, 0, $RADIUS/30);
PDF_fill($p);

PDF_restore($p);
PDF_end_page($p);

PDF_close($p);

$buf = PDF_get_buffer($p);
$len = strlen($buf);

header("Content-type: application/pdf");
header("Content-Length: $len");
header("Content-Disposition: inline; filename=pdfclock.pdf");
print $buf;

PDF_delete($p);
?>
