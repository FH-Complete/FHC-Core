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
										Benutzername:
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
						Wie aktiviere ich meinen Account?
					</fo:block>
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt">
						Zum Aktivieren Ihres Zugangs öffnen Sie mit Ihrem Web-Browser die Adresse https://cis.ktu-linz.ac.at/cis/public/accountactivation.php\n
						Tragen Sie in das Formular Ihren Benutzernamen und den Aktivierungscode ein und vergeben Sie ein Passwort für Ihren Account.\n\n
					</fo:block>
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt" font-weight="bold">
						Wie kann ich mein Kennwort ändern?
					</fo:block>
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt">
						Zum Ändern des Kennworts, öffnen Sie bitte die Seite: https://cis.ktu-linz.ac.at/cis/private/profile/change_password.php 
						Melden Sie sich bitte mit Ihren bestehenden Zugangsdaten (Benutzername und Passwort) an. Dies funktioniert nur, wenn Sie den Zugang bereits aktiviert haben.\n\n
					</fo:block>
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt" font-weight="bold">
						Wie kann ich meine Mails abrufen?
					</fo:block>
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt">
						Ihre Mails können Sie unter http://email.ktu-linz.ac.at abrufen. Melden Sie sich bitte auf dieser Seite mit E-Mailadresse und dem von Ihnen gewählten Passwort an.\n\n
					</fo:block>
					
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt" font-weight="bold">
						Wo erhalte ich weitere Informationen?
					</fo:block>
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt">
						Bei Fragen oder Problemen stehe ich Ihnen gerne unter sinn@ktu-linz.ac.at oder telefonisch unter +43 / (0)70 / 78 42 93 - 4135 zur Verfügung.\n\n
					</fo:block>
					
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt">
						Lukas Haselgrübler\n
						Studierendenverwaltung der KTU Linz
					</fo:block>
				</fo:block-container>
				
			</fo:flow>
		</fo:page-sequence>
	</xsl:template>
</xsl:stylesheet >
