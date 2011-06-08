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
			<table class="tabcontent">
			<tr>
				<td valign="top">
	    			<h1>News</h1>
					<div id="news">
						<xsl:apply-templates select="content/news" />
					</div>
				</td>
				<xsl:if test="content/stg_extras" >
					<td></td>
					<td class="tdvertical" valign="top" width="20%">
						<xsl:apply-templates select="content/stg_extras" />
					</td>
				</xsl:if>
			</tr>
			</table>
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
	<xsl:template match="stg_extras">
			<h2><b>Studiengangsmanagement</b></h2>
			<font face='Arial, Helvetica, sans-serif' size='2'>
			<br />
			Studiengangsleitung:<br />
			<xsl:apply-templates select="stg_ltg" />
			<br />
			<xsl:if test="gf_ltg">
				geschäftsf. Leitung:<br />
				<xsl:apply-templates select="gf_ltg" />
				<br />
			</xsl:if>
			<xsl:if test="stv_ltg">
				Stellvertreter:<br />
				<xsl:apply-templates select="stv_ltg" />
				<br />
			</xsl:if>
			<xsl:if test="ass">
				Sekretariat:<br />
				<xsl:apply-templates select="ass" />
				<br />
			</xsl:if>
			<xsl:value-of select="zusatzinfo" disable-output-escaping="yes"/>
			<xsl:if test="stdv">
				<br />
				Studentenvertreter:<br />
				<xsl:apply-templates select="stdv" />
				<br />
			</xsl:if>
			</font>
	</xsl:template>

	<xsl:template match="stg_ltg">
			<b><xsl:value-of select="name" /></b><br />
			<xsl:variable name="mail" select="email"></xsl:variable>
			<a href="mailto:{mail}"><xsl:value-of select="email" /></a><br />
			Tel.:<xsl:value-of select="telefon" />
			<br />
	</xsl:template>
	<xsl:template match="gf_ltg">
			<b><xsl:value-of select="name" /></b><br />
			<xsl:variable name="mail" select="email"></xsl:variable>
			<a href="mailto:{mail}"><xsl:value-of select="email" /></a><br />
			Tel.:<xsl:value-of select="telefon" />
			<br />
	</xsl:template>
	<xsl:template match="stv_ltg">
			<b><xsl:value-of select="name" /></b><br />
			<xsl:variable name="mail" select="email"></xsl:variable>
			<a href="mailto:{mail}"><xsl:value-of select="email" /></a><br />
			Tel.:<xsl:value-of select="telefon" />
			<br />
	</xsl:template>
	<xsl:template match="ass">
			<b><xsl:value-of select="name" /></b><br />
			<xsl:variable name="mail" select="email"></xsl:variable>
			<a href="mailto:{mail}"><xsl:value-of select="email" /></a><br />
			Tel.:<xsl:value-of select="telefon" />
			<br />
	</xsl:template>
	<xsl:template match="stdv">
			<xsl:variable name="mail" select="email"></xsl:variable>
			<a href="mailto:{mail}"><xsl:value-of select="name" /></a><br />
	</xsl:template>
</xsl:stylesheet >
