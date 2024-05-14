<?xml version="1.0" encoding="UTF-8"?>
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
</xsl:stylesheet >
