<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:fo="http://www.w3.org/1999/XSL/Format"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template match="/">
		<html>
		<head>
			<link rel="stylesheet" href="../skin/style.css.php" type="text/css"  />
			<title>News</title>
		</head>
		<body>
	    <h1>News</h1>
		<div id="news">
			<xsl:apply-templates match="news" />
		</div>
		</body>
		</html>	
	</xsl:template>
	<xsl:template match="news">
			
		<div class="news">
			<div class="titel">
				<table width="100%">
					<tr>
						<td width="60%" align="left"><xsl:value-of select="betreff"/></td>
						<td width="40%" align="right"><xsl:value-of select="verfasser"/> <span style="font-weight: normal"> ( <xsl:value-of select="datum"/> )</span></td>
					</tr>
				</table>
			</div>
			<div class="text">
				<xsl:value-of select="text" disable-output-escaping="yes" />
			</div>
		</div>
		<br />
	</xsl:template>
</xsl:stylesheet >


