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
					<fo:external-graphic src="../skin/images/logo.jpg"  posx="140" posy="15" width="60mm" height="20mm" />
				</fo:block>
				
				<fo:block-container position="absolute" top="30mm" left="80mm">
					<fo:block text-align="left" font-family="sans-serif" font-size="12pt" font-weight="bold">
						Account Information
					</fo:block>
				</fo:block-container>
				
				<fo:block-container position="absolute" top="40mm" left="15mm">
					<fo:table table-layout="fixed" border-collapse="separate">
					    <fo:table-column column-width="30mm"/>
						<fo:table-column column-width="130mm"/>
						
						<fo:table-body>
				            <fo:table-row line-height="14pt">
								<fo:table-cell>
									<fo:block font-weight="bold" font-family="sans-serif" font-size="12pt" content-width="30mm" text-align="left">
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
					    <fo:table-column column-width="30mm"/>
						<fo:table-column column-width="50mm"/>
						<fo:table-column column-width="15mm"/>
						<fo:table-column column-width="30mm"/>

						<fo:table-body>
				            <fo:table-row line-height="14pt">
								<fo:table-cell>
									<fo:block font-weight="bold" font-family="sans-serif" font-size="12pt" content-width="30mm" text-align="left">
										Account:
									</fo:block>
								</fo:table-cell>
								<fo:table-cell>
									<fo:block font-family="sans-serif" font-weight="bold" font-size="12pt" content-width="50mm" text-align="left">
										<xsl:value-of select="account" />
									</fo:block>
								</fo:table-cell>
								<fo:table-cell>
									<fo:block font-weight="bold" font-family="sans-serif" font-size="12pt" content-width="15mm" text-align="left">
										Email:
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
					<fo:table table-layout="fixed" border-collapse="separate">
					    <fo:table-column column-width="30mm"/>
						<fo:table-column column-width="130mm"/>
						
						<fo:table-body>
				            <fo:table-row line-height="14pt">
								<fo:table-cell>
									<fo:block font-weight="bold" font-family="sans-serif" font-size="12pt" content-width="30mm" text-align="left">
										Passwort:
									</fo:block>
								</fo:table-cell>
								<fo:table-cell>
									<fo:block font-weight="bold" font-family="sans-serif" font-size="12pt" content-width="130mm" text-align="left">
										<xsl:value-of select="passwort" />
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
					<xsl:if test="bezeichnung">
						<fo:table table-layout="fixed" border-collapse="separate">
						    <fo:table-column column-width="30mm"/>
							<fo:table-column column-width="300mm"/>
							
							<fo:table-body>
					            <fo:table-row line-height="14pt">
									<fo:table-cell>
										<fo:block font-weight="bold" font-family="sans-serif" font-size="12pt" content-width="30" text-align="left">
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
				</fo:block-container>
				
				<fo:block-container position="absolute" top="80mm" left="80mm">
					<fo:block text-align="left" font-family="sans-serif" font-size="12pt" font-weight="bold">
						Account Mini FAQ
					</fo:block>
				</fo:block-container>
				
				<fo:block-container position="absolute" top="90mm" left="15mm">
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt" font-weight="bold">
						Wie melde ich mich am System an?
					</fo:block>
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt">
						Windows NT/XP: Melden sie sich an der Domäne TW an indem Sie die Tasten &lt;STRG&gt; + &lt;ALT&gt; + &lt;ENTF&gt; gleichzeitig drücken, danach Accountname und Passwort eingeben.
					</fo:block>
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt">
						Linux: Nach dem Systemstart im Boot Manager einfach Linux auswählen. Das System startet mit dem KDE Login Manager. Dort ist ebenfalls der Accountname und das Passwort einzugeben.\n
					</fo:block>
					
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt" font-weight="bold">
						Ändern des Passwortes
					</fo:block>
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt">
						Windows NT/XP: In den EDV-Räumen.\n
						Loggen Sie sich mit Ihrem Account unter Windows NT/XP in der TW Domäne ein, drücken Sie &lt;STRG&gt; + &lt;ALT&gt; + &lt;ENTF&gt; und wählen Sie den Punkt "Kennwort ändern".
					</fo:block>
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt">
						Linux: Passwort ändern auf Ihrem Fileserver <xsl:value-of select="fileserver" />.\n
						Loggen Sie sich mittels SSH (z.B. putty Programm) auf Ihrem Fileserver ein und geben Sie den Befehl "passwd" ein.\n
						Webmail: Auch im Webmailsystem auf https://webmail.technikum-wien.at können Sie ihr Passwort ändern.\n
						In allen Fällen wird neben dem Windows-Passwort auch das Unix-Passwort für die Fileserver bzw. den Mailserver mitgeändert.\n
					</fo:block>
					
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt" font-weight="bold">
						Wie und wo kann ich meine Daten ablegen?
					</fo:block>
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt">
						Nach einem erfolgreichen Login ist unter Windows NT/XP das Laufwerk M: mit Ihrem Userverzeichnis am 
						Server verbunden. Dort haben Sie die Möglichkeit Ihre Daten abzulegen. Achten Sie immer darauf, Ihr Quota
						(Speicherplatz den Sie zur Verfügung haben) nicht zu überschreiten! Einige Programme (wie etwa Netscape)
						funktionieren dann nicht mehr einwandfrei.\n
					</fo:block>
					
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt" font-weight="bold">
						Welche Möglichkeiten habe ich auf meine Daten zuzugreifen?
					</fo:block>
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt">
						Innerhalb des TW LANs wird Ihr Serververzeichnis immer mit dem Laufwerk M: verbunden. 
						Ausserhalb der FH können Sie per SSH bzw. WinSCP auf Ihre Daten auch von zu Hause aus zugreifen.\n
						Dazu müssen Sie sich mit dem Server <xsl:value-of select="fileserver" /> verbinden.\n
					</fo:block>
					
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt" font-weight="bold">
						Wie kann ich meine Mails von zu Hause aus abrufen?
					</fo:block>
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt">
						Verwenden Sie dazu den POP3 Dienst. In Ihrem Mail Client müssen Sie ihren Accountnamen und den POP3
						Server pop.technikum-wien.at einstellen. Als SMTP Server verwenden Sie den Ihres Providers. 
						Die FH selbst bietet keinen Wählleitungszugang!\n
						Eine weitere Möglichkeit, von überall Mails abzurufen, ist unser Webmail Service auf https://webmail.technikum-wien.at\n
					</fo:block>
					
					<xsl:if test="bezeichnung">
						<fo:block text-align="left" font-family="sans-serif" font-size="10pt" font-weight="bold">
							Warum werden meine Einstellungen am Windows NT/XP Desktop nicht gespeichert?
						</fo:block>
						<fo:block text-align="left" font-family="sans-serif" font-size="10pt">
							Alle Studenten teilen sich dasselbe Profil. Sie können daher keine Einstellungen sichern.\n
						</fo:block>
					</xsl:if>
					
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt" font-weight="bold">
						Wo erhalte ich weitere Informationen?
					</fo:block>
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt">
						Die primäre Anlaufstelle für Fragen rund um den Netzwerkbetrieb ist die Webseite\n
					</fo:block>
					<fo:block text-align="center" content-width='180mm' font-family="sans-serif" font-size="10pt">
						https://cis.technikum-wien.at\n
					</fo:block>
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt">
						Verwenden Sie die Informationen auf dieser Seite, um einen Überblick über die vorhandenen Möglichkeiten zu erhalten.
					</fo:block>
				</fo:block-container>
				
			</fo:flow>
		</fo:page-sequence>
	</xsl:template>
</xsl:stylesheet >