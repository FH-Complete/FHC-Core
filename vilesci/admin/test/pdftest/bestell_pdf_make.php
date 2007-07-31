<html>
<head>
<title>Bestellschein</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php
	$fp = fopen("bestell.pdf", "w");
	$pdf = pdf_open($fp);
	pdf_set_info_author($pdf, "Dodik Thomas");
	PDF_set_info_title($pdf, "Bestellung");
	PDF_set_info_author($pdf, "Thomas Dodik");
	pdf_set_info_creator($pdf, "PHP");
	pdf_set_info_subject($pdf, "Subject");
	PDF_begin_page($pdf, 595, 842);
	PDF_add_outline($pdf, "Page 1");
	pdf_set_font($pdf, "Times-Roman", 30, 4);
	pdf_set_text_rendering($pdf, 1);
	PDF_show_xy($pdf, "Times-Roman", 50, 750);
	pdf_moveto($pdf, 50, 740);
	pdf_lineto($pdf, 330,740);
	pdf_stroke($pdf);
	PDF_end_page($pdf);
	PDF_close($pdf);
	fclose($fp);
?>
<A HREF=../bestell_pdf_get.php>finished</A> 
</body>

</html>