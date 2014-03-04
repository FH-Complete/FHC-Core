<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:fo="http://www.w3.org/1999/XSL/Format"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template match="content">
		<html>
		<head>
		<script type="text/javascript">
			window.location.href='<xsl:value-of select="url" />';
		</script>
		</head>
		<body>
		Sie werden automatisch weitergeleitet.
		Sollte dies nicht der Fall sein, klicken sie bitte 
		<xsl:variable name="url" select="url"></xsl:variable>
		<a href="{url}" target="_blank">hier</a><br/><br/>
		You will be redirected automatically. If not, please click  
		<a href="{url}" target="_blank">here</a>
		</body>
		</html>	
	</xsl:template>
</xsl:stylesheet >


