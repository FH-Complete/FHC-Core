<xsl:stylesheet version="1.0" xmlns:fo="http://www.w3.org/1999/XSL/Format"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template match="/">
		<html>
		<head>
			<link rel="stylesheet" href="../skin/infoscreen.css" type="text/css"  />
			<title>News</title>
		</head>
		<body>
			<table class="tabcontent">
			<tr>
				<td valign="top">
					<xsl:choose>
						<xsl:when test="content/news_titel">
							<h1>News  <xsl:value-of select="content/news_titel" /></h1>
						</xsl:when>
						<xsl:otherwise>
							<h1>News  <xsl:value-of select="content/studiengang_bezeichnung" /></h1>
						</xsl:otherwise>
					</xsl:choose>
	    			
					<div id="news">
						<xsl:choose>
							<xsl:when test="content/news">
								<xsl:apply-templates select="content/news" />
							</xsl:when>
							<xsl:otherwise>
								<xsl:apply-templates select="news" />
							</xsl:otherwise>
						</xsl:choose>
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
						<td width="40%" align="right">
							<xsl:value-of select="verfasser"/> 
							<span style="font-weight: normal"> ( <xsl:value-of select="datum"/> )</span>
							<xsl:if test="news_id">
								<xsl:variable name="news_id" select="news_id"></xsl:variable>
								<xsl:text> </xsl:text><a href="newsverwaltung.php?news_id={news_id}" target="content">edit</a>
								<xsl:text> </xsl:text><a href="newsverwaltung.php?news_id={news_id}&amp;action=delete" target="content" onclick="return confdel();">delete</a>
								<script type="text/javascript">
								function confdel()
								{
									return confirm('Soll dieser Eintrag wirklich gelöscht werden?');
								} 
								</script>
							</xsl:if>
						</td>
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
			<h2><b><xsl:value-of select="stg_header" /></b></h2>
			<font face='Arial, Helvetica, sans-serif' size='2'>
			<br />
			<xsl:value-of select="stg_ltg_name" /><br />
			<xsl:apply-templates select="stg_ltg" />
			<br />
			<xsl:if test="gf_ltg">
				<xsl:value-of select="gf_ltg_name" /><br />
				<xsl:apply-templates select="gf_ltg" />
				<br />
			</xsl:if>
			<xsl:if test="stv_ltg">
				<xsl:value-of select="stv_ltg_name" /><br />
				<xsl:apply-templates select="stv_ltg" />
				<br />
			</xsl:if>
			<xsl:if test="ass">
				<xsl:value-of select="ass_name" /><br />
				<xsl:apply-templates select="ass" />
				<br />
			</xsl:if>
			<xsl:value-of select="zusatzinfo" disable-output-escaping="yes"/>
			<xsl:if test="stdv">
				<br />
				<xsl:value-of select="stdv_name" /><br />
				<xsl:apply-templates select="stdv" />
				<br />
			</xsl:if>
			<xsl:apply-templates select="cis_ext_menu" />
			</font>
	</xsl:template>

	<xsl:template match="stg_ltg">
			<b><xsl:value-of select="name" /></b><br />
			<xsl:variable name="mail" select="email"></xsl:variable>
			<a href="mailto:{$mail}"><xsl:value-of select="email" /></a><br />
			Tel.:<xsl:value-of select="telefon" />
			<br />
	</xsl:template>
	<xsl:template match="gf_ltg">
			<b><xsl:value-of select="name" /></b><br />
			<xsl:variable name="mail" select="email"></xsl:variable>
			<a href="mailto:{$mail}"><xsl:value-of select="email" /></a><br />
			Tel.:<xsl:value-of select="telefon" />
			<br />
	</xsl:template>
	<xsl:template match="stv_ltg">
			<b><xsl:value-of select="name" /></b><br />
			<xsl:variable name="mail" select="email"></xsl:variable>
			<a href="mailto:{$mail}"><xsl:value-of select="email" /></a><br />
			Tel.:<xsl:value-of select="telefon" />
			<br />
	</xsl:template>
	<xsl:template match="ass">
			<b><xsl:value-of select="name" /></b><br />
			<xsl:variable name="mail" select="email"></xsl:variable>
			<a href="mailto:{$mail}"><xsl:value-of select="email" /></a><br />
			Tel.:<xsl:value-of select="telefon" />
			<br />
	</xsl:template>
	<xsl:template match="stdv">
			<xsl:variable name="mail" select="email"></xsl:variable>
			<a href="mailto:{$mail}"><xsl:value-of select="name" /></a><br />
	</xsl:template>
	<xsl:template match="cis_ext_menu">
			<xsl:variable name="kurzbz" select="kurzbz"></xsl:variable>
			<xsl:variable name="kurzbzlang" select="kurzbzlang"></xsl:variable>
			<img src="../skin/images/seperator.gif" /><xsl:text> </xsl:text><a href="../documents/{kurzbz}/lehrziele/" class="Item" target="_blank"><xsl:value-of select="lehrziele_name" /></a><br />
			<img src="../skin/images/seperator.gif" /><xsl:text> </xsl:text><a href="../documents/{kurzbz}/download/" class="Item" target="_blank"><xsl:value-of select="download_name" /></a><br />
			<img src="../skin/images/seperator.gif" /><xsl:text> </xsl:text><a href="news://news.technikum-wien.at/{kurzbzlang}" class="Item" target="_blank"><xsl:value-of select="newsgroup_name" /></a><br />
	</xsl:template>
</xsl:stylesheet >

