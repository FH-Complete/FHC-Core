<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:fo="http://www.w3.org/1999/XSL/Format"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template match="content">
		<html>
		<head>
			<link rel="stylesheet" href="../skin/style.css.php" type="text/css"  />
			<link rel="stylesheet" href="../skin/jquery.css" type="text/css" />
			<script type="text/javascript" src="../include/js/jquery.js"></script>
			<link rel="stylesheet" href="../skin/tablesort.css" type="text/css" />
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
		<xsl:value-of select="inhalt" disable-output-escaping="yes" />
		</body>
		</html>	
	</xsl:template>
</xsl:stylesheet >


