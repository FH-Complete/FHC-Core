<?php


$new_xslt_xhtml_version2=  <<<END
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

            <link href="https://cdnjs.cloudflare.com/ajax/libs/tabulator/5.0.7/css/tabulator.min.css" rel="stylesheet" />
            <script type="text/javascript" src="https://c3p0.ma0594.technikum-wien.at/fh-core/vendor/olifolkerd/tabulator5/dist/js/tabulator.min.js?2019102903"></script>
			<link rel="stylesheet" href="../skin/tablesort.css" type="text/css"/>
			<script type="text/javascript">

			$(document).ready(function()
			{
                let tables = document.getElementsByClassName("tablesorter");
				for(table of tables){
					new Tabulator(table, {
						layout:"fitDataFill",
						autoResize:true,
						resizableRows:true,
						columnDefaults:{
							formatter:"html",
							resizable:true,
						}
					})
				}
            });

			</script>
		</head>
		<body>
	    <h1><xsl:value-of select="titel" /></h1>
		<xsl:value-of select="inhalt" disable-output-escaping="yes" />
		</body>
		</html>
	</xsl:template>
</xsl:stylesheet >
END;



$new_xslt_xhtml=  <<<END
<xsl:stylesheet version="1.0" xmlns:fo="http://www.w3.org/1999/XSL/Format"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template match="content">
		<html>
		<head>
			<title><xsl:value-of select="titel" /></title>
			<link rel="stylesheet" href="../skin/style.css.php" type="text/css"  />
			<link rel="stylesheet" href="../skin/jquery.css" type="text/css" />
			<link rel="stylesheet" type="text/css" href="../skin/jquery-ui-1.9.2.custom.min.css" />

            <link href="https://cdnjs.cloudflare.com/ajax/libs/tabulator/5.0.7/css/tabulator.min.css" rel="stylesheet" />
            <script type="text/javascript" src="https://c3p0.ma0594.technikum-wien.at/fh-core/vendor/olifolkerd/tabulator5/dist/js/tabulator.min.js?2019102903"></script>


			<link rel="stylesheet" href="../skin/tablesort.css" type="text/css"/>
			<script type="text/javascript">

            document.addEventListener("DOMContentLoaded", function() 
			{
				let tables = document.getElementsByClassName("tablesorter");
				for(table of tables){
					new Tabulator(table, {
						layout:"fitDataFill",
						autoResize:true,
						resizableRows:true,
						columnDefaults:{
							formatter:"html",
							resizable:true,
						}
					})
				}
			});

			</script>
		</head>
		<body>
	    <h1><xsl:value-of select="titel" /></h1>
		<xsl:value-of select="inhalt" disable-output-escaping="yes" />
		</body>
		</html>
	</xsl:template>
</xsl:stylesheet >
END;
        $qry = "UPDATE campus.tbl_template SET xslt_xhtml='".$new_xslt_xhtml."' WHERE template_kurzbz='contentmittitel';";

        if (!$db->db_query($qry))
            echo '<strong>UPDATE OF TEMPLATE CONTENTMITTITEL FAILED : ' . $db->db_last_error() . '</strong><br>';
        else
            echo '<br>UPDATE OF TEMPLATE CONTENTMITTITEL WAS SUCCESSFUL';


$raum_content_update_query=  <<<END
UPDATE "campus"."tbl_content" 
	SET template_kurzbz = 'raum_contentmittitel' 
	where content_id IN 
	(SELECT content_id 
	FROM "public"."tbl_ort" 
	JOIN campus.tbl_content USING(content_id));
END;


if (!$db->db_query($raum_content_update_query))
            echo '<strong>FAILED TO UPDATE ALL TEMPLATES FOR THE ROOMS : ' . $db->db_last_error() . '</strong><br>';
        else
            echo '<br>SUCCESSFUL UPDATE ALL TEMPLATES FOR THE ROOMS TO raum_contentmittitel';




