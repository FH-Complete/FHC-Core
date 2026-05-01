<?php


$raum_contentmittitel_xslt_xhtml=  <<<EOD
<xsl:stylesheet version="1.0" xmlns:fo="http://www.w3.org/1999/XSL/Format"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template match="content">
		<html>
		<head>
			<title><xsl:value-of select="titel" /></title>
			<link rel="stylesheet" href="../skin/style.css.php" type="text/css"  />
			<link rel="stylesheet" href="../skin/jquery.css" type="text/css" />
			<link rel="stylesheet" type="text/css" href="../skin/jquery-ui-1.9.2.custom.min.css" />
			<script type="text/javascript" src="../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
			<script type="text/javascript" src="../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
			<script type="text/javascript" src="../vendor/components/jqueryui/jquery-ui.min.js"></script>
			<script type="text/javascript" src="../include/js/jquery.ui.datepicker.translation.js"></script>
			<script type="text/javascript" src="../vendor/jquery/sizzle/sizzle.js"></script>
			<link rel="stylesheet" href="../skin/tablesort.css" type="text/css"/>
			<script type="text/javascript">

				$(document).ready(function()
				{
					$(".tablesorter").each(function()
					{
						var col=0;
						var sort=0;
						var no_sort=1;
						var classes = $(this).attr("class");
						var class_arr = classes.split(" ");
						var headersobj={};
		
						for(i in class_arr)
						{
							if(class_arr[i].indexOf("tablesorter_col_")!=-1)
							{
								col = class_arr[i].substr(16);
							}
						
							if(class_arr[i].indexOf("tablesorter_sort_")!=-1)
							{
								sort = class_arr[i].substr(17);
							}

							if(class_arr[i].indexOf("tablesorter_no_sort_")!=-1)
							{
								no_sort = class_arr[i].substr(20);
								headersobj[no_sort]={sorter:false};
							}
						}
		
						$(this).tablesorter(
						{
							sortList: [[col,sort]],
							widgets: ["zebra"],
							headers: headersobj
						});
					});
				});
			</script>
			
		</head>
		<body>
	    <h1><xsl:value-of select="titel" /></h1>
		<xsl:value-of select="inhalt" disable-output-escaping="yes" />
		</body>
		</html>
	</xsl:template>
</xsl:stylesheet>
EOD;

$raum_contentmittitel_xsd=<<<EOD
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
<xs:element name="content">
	<xs:element name="titel" type="xs:string"/>
	<xs:element name="inhalt" type="wysiwyg"/>
</xs:element>
</xs:schema>
EOD;

$raum_contentmittitel_xslt_xhtml_c4=   <<<EOD
<xsl:stylesheet version="1.0"
    xmlns:fo="http://www.w3.org/1999/XSL/Format"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:template match="content">
		<h1>
	    	<xsl:value-of select="titel" />
		</h1>
		<xsl:value-of select="inhalt" disable-output-escaping="yes" />
	</xsl:template>
</xsl:stylesheet>
EOD;

$raum_contentmittitel_insert_query=  <<<EOD
	INSERT INTO campus.tbl_template 
	(template_kurzbz, bezeichnung, xsd, xslt_xhtml, xslfo_pdf, xslt_xhtml_c4) 
	VALUES 
	('raum_contentmittitel','template for the raum view that uses the tabulator javascript instead of the jquery scripts and the table sorter ', '{$raum_contentmittitel_xsd}', '{$raum_contentmittitel_xslt_xhtml}' , NULL, '{$raum_contentmittitel_xslt_xhtml_c4}');
EOD;

$raum_content_update_query= <<<EOD
				UPDATE campus.tbl_content 
				SET template_kurzbz = 'raum_contentmittitel' 
				where content_id IN 
				(SELECT content_id 
				FROM public.tbl_ort 
				JOIN campus.tbl_content USING(content_id));
EOD;


if ($result = @$db->db_query("SELECT * FROM campus.tbl_template WHERE template_kurzbz='raum_contentmittitel'")) {
	
	// only inserting the new template if it doesn't exist already
    if ($db->db_num_rows($result) == 0) {
		
		// executing the insert statement for the new template
		if (!$db->db_query($raum_contentmittitel_insert_query)){
			echo '<strong>FAILED INSERTING NEW TEMPLATE RAUM_CONTENTMITTITEL : ' . $db->db_last_error() . '</strong><br>';
			
		}
		else {
			echo '<br>SUCCESSFULLY INSERTING NEW TEMPLATE RAUM_CONTENTMITTITEL ';
	
			// only update the rooms template if the insert of the room_contentmittitel template was successful
			// executing the update statement to update the template for all room content
			if (!$db->db_query($raum_content_update_query)){
				echo '<strong>FAILED TO UPDATE ROOMS WITH NEW TEMPLATE raum_contentmittitel : ' . $db->db_last_error() . '</strong><br>';
			}
			else{
				echo '<br>SUCCESSFULLY UPDATED ROOMS WITH NEW TEMPLATE raum_contentmittitel';
			}

		}
	
	}
}