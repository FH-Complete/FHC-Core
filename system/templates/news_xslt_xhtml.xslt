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
			<table class="cmstable" cellspacing="0" cellpadding="0">
			<tr>
				<td class="cmscontent" rowspan="2" valign="top">
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
						<xsl:apply-templates select="content/stg_extras/cis_ext_menu" />
				</xsl:if>
			</tr>
			<tr>
				<xsl:if test="content/stg_extras" >
						<xsl:apply-templates select="content/stg_extras" />
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
		<xsl:if test="stg_ltg!='' or ass!='' or stdv!='' or zusatzinfo!=''">
			<td class="teambox" style="width: 20%;">
			<font face='Arial, Helvetica, sans-serif' size='2'>
			<xsl:if test="stg_ltg">			
				<h2><xsl:value-of select="stg_ltg_name" /></h2>
				<xsl:apply-templates select="stg_ltg" />
			</xsl:if>		
			<xsl:if test="gf_ltg">
				<h2><xsl:value-of select="gf_ltg_name" /></h2>
				<xsl:apply-templates select="gf_ltg" />
				
			</xsl:if>
			<xsl:if test="stv_ltg">
				<h2><xsl:value-of select="stv_ltg_name" /></h2>
				<xsl:apply-templates select="stv_ltg" />
				
			</xsl:if>
			<xsl:if test="ass">
				<h2><xsl:value-of select="ass_name" /></h2>
				<xsl:apply-templates select="ass" />
				
			</xsl:if>
			<xsl:value-of select="zusatzinfo" disable-output-escaping="yes"/>
			<xsl:if test="hochschulvertr">
				
				<h2><xsl:value-of select="hochschulvertr_name" /></h2>
				<p><xsl:apply-templates select="hochschulvertr" /></p>
				
			</xsl:if>
			<xsl:if test="stdv">
				
				<h2><xsl:value-of select="stdv_name" /></h2>
				<p><xsl:apply-templates select="stdv" /></p>
				
			</xsl:if>
			<xsl:if test="jahrgangsvertr">
				
				<h2><xsl:value-of select="jahrgangsvertr_name" /></h2>
				<p><xsl:apply-templates select="jahrgangsvertr" /></p>
				
			</xsl:if>
			</font>
			</td>
		</xsl:if>
	</xsl:template>

	<xsl:template match="stg_ltg">
			<xsl:variable name="uid" select="uid"></xsl:variable>
			<p><a href="../cis/private/profile/index.php?uid={$uid}"><xsl:value-of select="name" /></a><br />			
			T: <xsl:value-of select="telefon" /><br />
			R: <xsl:value-of select="ort" /><br />
			E: <xsl:variable name="mail" select="email"></xsl:variable>
			<a href="mailto:{$mail}"><xsl:value-of select="email" /></a></p>
	</xsl:template>
	<xsl:template match="gf_ltg">
			<xsl:variable name="uid" select="uid"></xsl:variable>
			<p><a href="../cis/private/profile/index.php?uid={$uid}"><xsl:value-of select="name" /></a><br />			
			T: <xsl:value-of select="telefon" /><br />
			R: <xsl:value-of select="ort" /><br />
			E: <xsl:variable name="mail" select="email"></xsl:variable>
			<a href="mailto:{$mail}"><xsl:value-of select="email" /></a></p>
	</xsl:template>
	<xsl:template match="stv_ltg">
			<xsl:variable name="uid" select="uid"></xsl:variable>
			<p><a href="../cis/private/profile/index.php?uid={$uid}"><xsl:value-of select="name" /></a><br />			
			T: <xsl:value-of select="telefon" /><br />
			R: <xsl:value-of select="ort" /><br />
			E: <xsl:variable name="mail" select="email"></xsl:variable>
			<a href="mailto:{$mail}"><xsl:value-of select="email" /></a></p>
	</xsl:template>
	<xsl:template match="ass">
			<xsl:variable name="uid" select="uid"></xsl:variable>
			<p>
			<xsl:if test="bezeichnung != 'Assistenz'" >
				<b><xsl:value-of select="bezeichnung" /></b><br />
			</xsl:if>
			<a href="../cis/private/profile/index.php?uid={$uid}"><xsl:value-of select="name" /></a><br />			
			T: <xsl:value-of select="telefon" /><br />
			R: <xsl:value-of select="ort" /><br />
			E: <xsl:variable name="mail" select="email"></xsl:variable>
			<a href="mailto:{$mail}"><xsl:value-of select="email" /></a></p>
	</xsl:template>
	<xsl:template match="hochschulvertr">
			<xsl:variable name="uid" select="uid"></xsl:variable>
			<a href="../cis/private/profile/index.php?uid={$uid}"><xsl:value-of select="name" /></a><br />
	</xsl:template>
	<xsl:template match="stdv">
			<xsl:variable name="uid" select="uid"></xsl:variable>
			<a href="../cis/private/profile/index.php?uid={$uid}"><xsl:value-of select="name" /></a><br />
	</xsl:template>
	<xsl:template match="jahrgangsvertr">
			<xsl:variable name="uid" select="uid"></xsl:variable>
			<a href="../cis/private/profile/index.php?uid={$uid}"><xsl:value-of select="name" /></a><br />
	</xsl:template>
	<xsl:template match="cis_ext_menu">
			<xsl:variable name="kurzbz" select="kurzbz"></xsl:variable>
			<xsl:variable name="stg_kz" select="stg_kz"></xsl:variable>
			<td class="menubox">
			<p><xsl:text> </xsl:text><a href="https://moodle.technikum-wien.at/course/view.php?idnumber=dl{$stg_kz}" class="Item" target="_blank"><xsl:value-of select="download_name" /></a></p>
			</td>
	</xsl:template>
</xsl:stylesheet >
