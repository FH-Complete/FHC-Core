<?php
if (!defined('DB_NAME'))
	exit('No direct script access allowed');

// Add index to system.tbl_log
if (!$result = @$db->db_query("SELECT xslt_xhtml_c4 FROM campus.tbl_template LIMIT 1")) {
	$qry = "ALTER TABLE campus.tbl_template ADD COLUMN xslt_xhtml_c4 xml;";

	if (!$db->db_query($qry))
		echo '<strong>campus.tbl_template: ' . $db->db_last_error() . '</strong><br>';
	else
		echo '<br>campus.tbl_template: Spalte xslt_xhtml_c4 hinzugefuegt';

	$xml01 = <<<EOXML01
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html"/>
    <xsl:template match="content">
	<h1>
	    <xsl:value-of select="titel" />
	</h1>
	<xsl:value-of select="inhalt" disable-output-escaping="yes" />
    </xsl:template>
</xsl:stylesheet>
EOXML01;

	$xml02 = <<<EOXML02

<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html"/>
	<xsl:template match="content">
		<xsl:value-of select="inhalt" disable-output-escaping="yes" />
	</xsl:template>
</xsl:stylesheet>

EOXML02;

	$xml03 = <<<EOXML03

	<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html"/>
	<xsl:template match="content">
		<script type="text/javascript">
		window.location.href=''<xsl:value-of select="url" />'';
		</script>
		<div class="alert alert-primary" role="alert">Sie werden automatisch weitergeleitet. Sollte dies nicht der Fall sein, klicken sie bitte 
			<xsl:variable name="url" select="url"></xsl:variable>
			<a href="{url}">hier</a>
		</div>
	</xsl:template>
</xsl:stylesheet>

EOXML03;

	$xml04 = <<<EOXML04

<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html"/>
	<xsl:template match="content">
		<div class="alert alert-danger" role="alert">Diese Seite sollte nicht direkt aufgerufen werden!</div>
	</xsl:template>
</xsl:stylesheet>

EOXML04;

	$xml05 = <<<EOXML05

<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output encoding="UTF-8" omit-xml-declaration="yes" indent="yes"/>
	<xsl:output method="html"/>
	<xsl:template match="/">
		<div class="row">
			<xsl:choose>
				<xsl:when test="content/stg_extras">
					<div class="news-list col-9" aria-role="feed">
						<xsl:call-template name="content"/>
					</div>
					<aside class="col-3">
						<xsl:apply-templates select="content/stg_extras" />
					</aside>
				</xsl:when>
				<xsl:otherwise>
					<div class="news-list col" aria-role="feed">
						<xsl:call-template name="content"/>
					</div>
				</xsl:otherwise>
			</xsl:choose>
		</div>
	</xsl:template>
	<xsl:template name="content">
		<xsl:choose>
			<xsl:when test="content/news">
				<xsl:apply-templates select="content/news" />
			</xsl:when>
			<xsl:when test="content/newswrapper">
				<xsl:apply-templates select="content/newswrapper" />
			</xsl:when>
			<xsl:when test="newswrapper">
				<xsl:apply-templates select="newswrapper" />
			</xsl:when>
			<xsl:otherwise>
				<xsl:apply-templates select="news" />
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<xsl:template match="newswrapper">
		<xsl:apply-templates select="news">
			<xsl:with-param name="datum" select="datum" />
			<xsl:with-param name="datumdetail" select="datumdetail" />
			<xsl:with-param name="news_id" select="news_id" />
		</xsl:apply-templates>
	</xsl:template>
	<xsl:template match="news">
		<xsl:param name="datum" select="datum"/>
		<xsl:param name="datumdetail" select="datumdetail"/>
		<xsl:param name="news_id" select="news_id"/>
		<article class="news-list-item card mb-3">
			<header class="card-header d-flex justify-content-between align-items-center">
				<div class="row ">
                    <h2 class="h5 col-auto me-auto">
                        <xsl:value-of select="betreff"/>
                    </h2>
                    <div class="col-auto">	
						<address class="fw-bold small mb-0">
							<xsl:value-of select="verfasser"/>
						</address>
						<xsl:if test="\$datum">
							<time class="small text-end w-100" datetime="{\$datumdetail}">
								<xsl:value-of select="\$datum"/>
							</time>
						</xsl:if>
					</div>
				</div>
			</header>
			<xsl:if test="text">
				<xsl:if test="text != ''''">
					<div class="card-body">
						<div class="card-text">
							<xsl:value-of select="text" disable-output-escaping="yes" />
						</div>
					</div>
				</xsl:if>
			</xsl:if>
			<xsl:if test="\$news_id">
				<footer class="card-footer d-flex justify-content-end">
					<a class="btn btn-outline-primary btn-sm mx-1" href="#" data-href="newsverwaltung.php?news_id={\$news_id}" target="_blank" aria-label="Edit">
						<i class="fa fa-pencil" aria-hidden="true" title="Edit"></i>
					</a>
					<a class="btn btn-outline-primary btn-sm mx-1" href="#" data-confirm="Soll dieser Eintrag wirklich gelöscht werden?" data-href="newsverwaltung.php?news_id={\$news_id}&amp;action=delete" target="_blank" aria-label="Delete">
						<i class="fa fa-trash" aria-hidden="true" title="Delete"></i>
					</a>
				</footer>
			</xsl:if>
		</article>
	</xsl:template>
	<xsl:template match="stg_extras">
		<dl>
			<xsl:if test="stg_ltg">
				<dt>
					<xsl:value-of select="stg_ltg_name"/>
				</dt>
				<xsl:apply-templates select="stg_ltg" />
			</xsl:if>
			<xsl:if test="gf_ltg">
				<dt>
					<xsl:value-of select="gf_ltg_name"/>
				</dt>
				<xsl:apply-templates select="gf_ltg" />
			</xsl:if>
			<xsl:if test="stv_ltg">
				<dt>
					<xsl:value-of select="stv_ltg_name"/>
				</dt>
				<xsl:apply-templates select="stv_ltg"/>
			</xsl:if>
			<xsl:if test="ass">
				<dt>
					<xsl:value-of select="ass_name"/>
				</dt>
				<xsl:apply-templates select="ass"/>
			</xsl:if>
		</dl>
		<xsl:value-of select="zusatzinfo" disable-output-escaping="yes"/>
		<dl>
			<xsl:if test="hochschulvertr">
				<dt>
					<xsl:value-of select="hochschulvertr_name"/>
				</dt>
				<xsl:apply-templates select="hochschulvertr"/>
			</xsl:if>
			<xsl:if test="stdv">
				<dt>
					<xsl:value-of select="stdv_name"/>
				</dt>
				<xsl:apply-templates select="stdv"/>
			</xsl:if>
			<xsl:if test="jahrgangsvertr">
				<dt>
					<xsl:value-of select="jahrgangsvertr_name"/>
				</dt>
				<xsl:apply-templates select="jahrgangsvertr"/>
			</xsl:if>
		</dl>
		<xsl:apply-templates select="cis_ext_menu"/>
	</xsl:template>
	<xsl:template name="person_linked_name">
		<xsl:variable name="uid" select="uid"/>
		<a href="../cis/private/profile/index.php?uid={\$uid}">
			<xsl:value-of select="name"/>
		</a>
	</xsl:template>
	<xsl:template name="person">
		<xsl:param name="ass" select="ass"/>
		<xsl:variable name="phone" select="telefon"/>
		<xsl:variable name="mail" select="email"/>
		<dd>
			<address class="mb-0">
				<xsl:if test="\$ass">
					<xsl:if test="bezeichnung != ''Assistenz''">
						<h6>
							<xsl:value-of select="bezeichnung"/>
						</h6>
					</xsl:if>
				</xsl:if>
				<xsl:call-template name="person_linked_name"/>
				<br/>
				<i class="fa fa-phone" title="Telefon" aria-hidden="true"></i>
				<span class="sr-only">Telefon</span>: 
				<a href="tel:{translate(\$phone, translate(\$phone,''+1234567890'',''''),'''')}">
					<xsl:value-of select="telefon"/>
				</a>
				<br/>
				<i class="fa fa-home" title="Raum" aria-hidden="true"></i>
				<span class="sr-only">Raum</span>: 
				<xsl:value-of select="ort"/>
				<br/>
				<i class="fa fa-envelope" title="E-mail" aria-hidden="true"></i>
				<span class="sr-only">E-mail</span>: 
				<a href="mailto:{\$mail}">
					<xsl:value-of select="email"/>
				</a>
			</address>
		</dd>
	</xsl:template>
	<xsl:template name="person_short">
		<dd>
			<address class="mb-0">
				<xsl:call-template name="person_linked_name"/>
			</address>
		</dd>
	</xsl:template>
	<xsl:template match="stg_ltg">
		<xsl:call-template name="person"/>
	</xsl:template>
	<xsl:template match="gf_ltg">
		<xsl:call-template name="person"/>
	</xsl:template>
	<xsl:template match="stv_ltg">
		<xsl:call-template name="person"/>
	</xsl:template>
	<xsl:template match="ass">
		<xsl:call-template name="person">
			<xsl:with-param name="ass" select="1"/>
		</xsl:call-template>
	</xsl:template>
	<xsl:template match="hochschulvertr">
		<xsl:call-template name="person_short"/>
	</xsl:template>
	<xsl:template match="stdv">
		<xsl:call-template name="person_short"/>
	</xsl:template>
	<xsl:template match="jahrgangsvertr">
		<xsl:call-template name="person_short"/>
	</xsl:template>
	<xsl:template match="cis_ext_menu">
		<xsl:variable name="kurzbz" select="kurzbz"></xsl:variable>
		<xsl:variable name="stg_kz" select="stg_kz"></xsl:variable>
		<p>
			<a class="btn btn-primary" href="https://moodle.technikum-wien.at/course/view.php?idnumber=dl{\$stg_kz}" target="_blank">
				<xsl:value-of select="download_name"/>
			</a>
		</p>
	</xsl:template>
</xsl:stylesheet>

EOXML05;

	$qry = <<<EOSQL
	UPDATE campus.tbl_template SET xslt_xhtml_c4='{$xml01}' WHERE template_kurzbz='contentmittitel';
	UPDATE campus.tbl_template SET xslt_xhtml_c4='{$xml02}' WHERE template_kurzbz='contentohnetitel';
	UPDATE campus.tbl_template SET xslt_xhtml_c4='{$xml03}' WHERE template_kurzbz='redirect';
	UPDATE campus.tbl_template SET xslt_xhtml_c4='{$xml04}' WHERE template_kurzbz='include';
	UPDATE campus.tbl_template SET xslt_xhtml_c4='{$xml05}' WHERE template_kurzbz='news';
	UPDATE campus.tbl_template SET xslt_xhtml_c4=xslt_xhtml WHERE xslt_xhtml_c4 IS NULL;

EOSQL;


	if (!$db->db_query($qry))
		echo '<strong>campus.tbl_template: ' . $db->db_last_error() . '</strong><br>';
	else
		echo '<br>campus.tbl_template: Spalte xslt_xhtml_c4 Defaultwerte eingefügt';
}

$tabellen['campus.tbl_template'][] = 'xslt_xhtml_c4';
