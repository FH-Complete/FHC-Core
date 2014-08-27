<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" xmlns:fo="http://www.w3.org/1999/XSL/Format"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output method="xml" version="1.0" indent="yes" />
	
	<xsl:template match="accountinfoblaetter">
		<fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<fo:layout-master-set>
				<fo:simple-page-master page-height="297mm" page-width="210mm" margin="5mm 25mm 5mm 25mm" master-name="PageMaster">
					<fo:region-body margin="20mm 0mm 20mm 0mm"/>
				</fo:simple-page-master>
			</fo:layout-master-set>
			<xsl:apply-templates select="infoblatt"/>
		</fo:root>
	</xsl:template>
	
	<xsl:template match="infoblatt">
		<fo:page-sequence master-reference="PageMaster">
			<fo:flow  ><!--flow-name="xsl-region-body"-->
				<!-- Logo -->
				<fo:block>
					<!-- <fo:external-graphic src="../skin/images/logo.jpg"  posx="140" posy="15" width="60mm" height="20mm" /> -->
					<fo:external-graphic  posx="167" posy="15" height="20mm" >
							 <xsl:attribute name="src">
							  	../skin/images/logo.jpg
							 </xsl:attribute>
						</fo:external-graphic>
				</fo:block>
				
				<fo:block-container position="absolute" top="30mm" left="80mm">
					<fo:block text-align="left" font-family="sans-serif" font-size="12pt" font-weight="bold">
						Account Information
					</fo:block>
				</fo:block-container>
				
				<fo:block-container position="absolute" top="40mm" left="15mm">
					<fo:table table-layout="fixed" border-collapse="separate">
					    <fo:table-column column-width="40mm"/>
						<fo:table-column column-width="130mm"/>
						
						<fo:table-body>
				            <fo:table-row line-height="14pt">
								<fo:table-cell>
									<fo:block font-weight="bold" font-family="sans-serif" font-size="12pt" content-width="40mm" text-align="left">
										Name:
									</fo:block>
								</fo:table-cell>
								<fo:table-cell>
									<fo:block font-weight="bold" font-family="sans-serif" font-size="12pt" content-width="130mm" text-align="left">
										<xsl:value-of select="name" />
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
					<fo:table table-layout="fixed" border-collapse="separate">
					    <fo:table-column column-width="40mm"/>
						<fo:table-column column-width="50mm"/>
						<fo:table-column column-width="15mm"/>
						<fo:table-column column-width="30mm"/>

						<fo:table-body>
				            <fo:table-row line-height="14pt">
								<fo:table-cell>
									<fo:block font-weight="bold" font-family="sans-serif" font-size="12pt" content-width="40mm" text-align="left">
										Username:
									</fo:block>
								</fo:table-cell>
								<fo:table-cell>
									<fo:block font-family="sans-serif" font-weight="bold" font-size="12pt" content-width="50mm" text-align="left">
										<xsl:value-of select="account" />
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
					<fo:table table-layout="fixed" border-collapse="separate">
					    <fo:table-column column-width="40mm"/>
						<fo:table-column column-width="130mm"/>
						
						<fo:table-body>
				            <fo:table-row line-height="14pt">
								<fo:table-cell>
									<fo:block font-weight="bold" font-family="sans-serif" font-size="12pt" content-width="40mm" text-align="left">
										Aktivierungscode:
									</fo:block>
								</fo:table-cell>
								<fo:table-cell>
									<fo:block font-weight="bold" font-family="sans-serif" font-size="12pt" content-width="130mm" text-align="left">
										<xsl:choose>
											<xsl:when test="aktivierungscode=''">Account wurde bereits aktiviert
											</xsl:when>
											<xsl:otherwise>
											<xsl:value-of select="aktivierungscode" />
											</xsl:otherwise>
										</xsl:choose>										
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
					<xsl:if test="bezeichnung">
						<fo:table table-layout="fixed" border-collapse="separate">
						    <fo:table-column column-width="40mm"/>
							<fo:table-column column-width="300mm"/>
							
							<fo:table-body>
					            <fo:table-row line-height="14pt">
									<fo:table-cell>
										<fo:block font-weight="bold" font-family="sans-serif" font-size="12pt" content-width="40" text-align="left">
											Studiengang:
										</fo:block>
									</fo:table-cell>
									<fo:table-cell>
										<fo:block font-weight="bold" font-family="sans-serif" font-size="12pt" content-width="300mm" text-align="left">
											<xsl:value-of select="bezeichnung" />
										</fo:block>
									</fo:table-cell>
								</fo:table-row>
							</fo:table-body>
						</fo:table>
					</xsl:if>
						<fo:table table-layout="fixed" border-collapse="separate">
						    <fo:table-column column-width="40mm"/>
							<fo:table-column column-width="300mm"/>
							<fo:table-body>
					            <fo:table-row line-height="14pt">

								<fo:table-cell>
									<fo:block font-weight="bold" font-family="sans-serif" font-size="12pt" content-width="15mm" text-align="left">
										E-Mail:
									</fo:block>
								</fo:table-cell>
								<fo:table-cell>
									<fo:block font-weight="bold" font-family="sans-serif" font-size="12pt" content-width="30mm" text-align="left">
										<xsl:value-of select="email" />
									</fo:block>
								</fo:table-cell>
								</fo:table-row>
							</fo:table-body>
						</fo:table>
				</fo:block-container>
				
				<fo:block-container position="absolute" top="80mm" left="80mm">
					<fo:block text-align="left" font-family="sans-serif" font-size="12pt" font-weight="bold">
						Account Mini FAQ
					</fo:block>
				</fo:block-container>
				
				<fo:block-container position="absolute" top="90mm" left="15mm">
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt" font-weight="bold">
						Wie aktiviere ich meinen Acccount?
					</fo:block>
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt">
						Öffnen Sie mit ihrem Web-Browser die Adresse https://cis.ktu-linz.ac.at/cis/public/accountactivation.php\n
						Tragen Sie in das Formular Ihren Usernamen und Aktivierungscode ein und vergeben Sie ein Passwort für Ihren Account.\n
					</fo:block>
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt" font-weight="bold">
						Ändern des Passwortes
					</fo:block>
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt">
						Windows XP/7: In den EDV-Räumen.\n
						Loggen Sie sich mit Ihrem Account unter Windows XP/7 ein, drücken Sie &lt;STRG&gt; + &lt;ALT&gt; + &lt;ENTF&gt; und wählen Sie den Punkt "Kennwort ändern".\n
					</fo:block>
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt" font-weight="bold">
						Wie kann ich meine Mails von zu Hause aus abrufen?
					</fo:block>
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt">
						Eine Anleitung zur Einrichtung Ihres Mail-Clients finden Sie auf http://www.ktu-linz.ac.at unter dem Punkt FAQ.\n
					</fo:block>
					
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt" font-weight="bold">
						Wo erhalte ich weitere Informationen?
					</fo:block>
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt">
						Die primäre Anlaufstelle für Fragen rund um den Netzwerkbetrieb ist die Webseite\n
					</fo:block>
					<fo:block text-align="center" content-width='180mm' font-family="sans-serif" font-size="10pt">
						http://www.ktu-linz.ac.at\n
					</fo:block>
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt">
						Verwenden Sie die Informationen auf dieser Seite, um einen Überblick über die vorhandenen Möglichkeiten zu erhalten.
					</fo:block>
				</fo:block-container>
				
				<!-- Englische Version -->
				<fo:block font-size="16pt" 
					font-family="sans-serif" 
					space-after.optimum="15pt"
					text-align="center"
                    >
                    <!--break-before="page" -->
			  </fo:block>
			  <fo:block-container position="absolute" top="180mm" left="80mm">
					<fo:block text-align="left" font-family="sans-serif" font-size="12pt" font-weight="bold">
						Account Mini FAQ
					</fo:block>
				</fo:block-container>
				<fo:block-container position="absolute" top="190mm" left="15mm">
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt" font-weight="bold">
						Account activation:
					</fo:block>
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt">
						Open your web browser and go to https://cis.ktu-linz.ac.at/cis/public/accountactivation.php\n
						Enter your user name and activation key. Enter a new password for your account.\n
					</fo:block>
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt" font-weight="bold">
						Password Change:
					</fo:block>
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt">
						In the computer labs of KTU Linz\n
						In Windows XP and Windows 7, hold down &lt;ctrl&gt; + &lt;alt&gt; + &lt;delete&gt; simultaneously. Select "Kennwort ändern" (Change Password). Then change your password.\n
					</fo:block>
										
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt" font-weight="bold">
						Looking for further information?
					</fo:block>
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt">
						For questions concerning the KTU Linz network services, see\n
					</fo:block>
					<fo:block text-align="center" content-width='180mm' font-family="sans-serif" font-size="10pt">
						http://www.ktu-linz.ac.at\n
					</fo:block>
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt">
						These pages will give you a detailed overview of all services available
					</fo:block>
				</fo:block-container>
				
			</fo:flow>
		</fo:page-sequence>
	</xsl:template>
</xsl:stylesheet >
