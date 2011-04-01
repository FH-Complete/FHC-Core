<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:fo="http://www.w3.org/1999/XSL/Format"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template match="content">
		<html>
		<head>
			<link rel="stylesheet" href="../skin/style.css.php" type="text/css"  />
		</head>
		<body>
	    <h1><xsl:value-of select="titel" /></h1>
		<xsl:value-of select="inhalt" disable-output-escaping="yes" />
		</body>
		</html>	
	</xsl:template>
</xsl:stylesheet >


