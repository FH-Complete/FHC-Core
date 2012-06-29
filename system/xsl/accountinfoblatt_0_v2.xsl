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
						Windows XP/7: Melden sie sich an der Domäne TW an indem Sie die Tasten &lt;STRG&gt; + &lt;ALT&gt; + &lt;ENTF&gt; gleichzeitig drücken, danach Accountname und Passwort eingeben.
					</fo:block>
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt">
						Linux: Nach dem Systemstart im Boot Manager einfach Linux auswählen. Das System startet mit dem KDE Login Manager. Dort ist ebenfalls der Accountname und das Passwort einzugeben.\n
					</fo:block>
					
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt" font-weight="bold">
						Ändern des Passwortes
					</fo:block>
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt">
						Windows XP/7: In den EDV-Räumen.\n
						Loggen Sie sich mit Ihrem Account unter Windows XP/7 in der TW Domäne ein, drücken Sie &lt;STRG&gt; + &lt;ALT&gt; + &lt;ENTF&gt; und wählen Sie den Punkt "Kennwort ändern".
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
						Nach einem erfolgreichen Login ist unter Windows XP/7 das Laufwerk M: mit Ihrem Userverzeichnis am 
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
						Eine Anleitung zur Einrichtung Ihres Mail-Clients finden Sie auf https://cis.technikum-wien.at unter dem Punkt FAQ.\n
						Eine weitere Möglichkeit, von überall Mails abzurufen, ist unser Webmail Service auf https://webmail.technikum-wien.at\n
					</fo:block>
					
					<xsl:if test="bezeichnung">
						<fo:block text-align="left" font-family="sans-serif" font-size="10pt" font-weight="bold">
							Warum werden meine Einstellungen am Windows XP/7 Desktop nicht gespeichert?
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
				
				<!-- Englische Version -->
				<fo:block font-size="16pt" 
					font-family="sans-serif" 
					space-after.optimum="15pt"
					text-align="center"
					break-before="page">
			  </fo:block>
			  <fo:block-container position="absolute" top="80mm" left="80mm">
					<fo:block text-align="left" font-family="sans-serif" font-size="12pt" font-weight="bold">
						Account Mini FAQ
					</fo:block>
				</fo:block-container>
				<fo:block-container position="absolute" top="90mm" left="15mm">
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt" font-weight="bold">
						System Log-in:
					</fo:block>
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt">
						Windows XP/7: Hold down &lt;ctrl&gt; + &lt;alt&gt; + &lt;delete&gt; simultaneously to log in to the domain of the University of Applied Sciences Technikum Wien. Enter your user name (Account) and password in the log-in window.
					</fo:block>
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt">
						Linux: Start your system and choose Linux in the Boot Manager. The system will then launch KDE Log-in Manager. Enter your user name (see Account) and password in the log-in window.\n
					</fo:block>
					
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt" font-weight="bold">
						Password Change:
					</fo:block>
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt">
						There are two ways to change your password:\n
						In the computer labs of UAS Technikum Wien:\n
						In Windows XP and Windows 7, hold down &lt;ctrl&gt; + &lt;alt&gt; + &lt;delete&gt; simultaneously to log in to the Technikum Wien domain. Select "Kennwort ändern" (Change Password). Then change your password.
					</fo:block>
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt">
						In Linux, use an SSH client such as Putty to log in to your file server <xsl:value-of select="fileserver" /> and enter "passwd". Then change your password.\n
						Webmail: Open your web browser and go to https://webmail.technikum-wien.at. Enter your user name (Account) and password, then skip maintenance and select "My Account &gt; Password" from the pane on the left. Then change your password.\n
					</fo:block>
					
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt" font-weight="bold">
						Disk space for your files:
					</fo:block>
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt">
						In Windows XP and Windows 7, log in to the UAS Technikum Wien domain, and you will be automatically connected to volume M:, which contains your personal disk space.\n
						In Linux, use an SSH client such as Putty to log in to your file server <xsl:value-of select="fileserver" />.
					</fo:block>
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt" font-weight="bold">
						Make sure not to exceed your quota, as some software will stop functioning in that case.\n
					</fo:block>
					
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt" font-weight="bold">
						Access to your files:
					</fo:block>
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt">
						From within UAS Technikum Wien, you can always access volume M: in Windows XP and Windows 7. From outside, use an SSH or SCP client to connect to <xsl:value-of select="fileserver" />.\n
					</fo:block>
					
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt" font-weight="bold">
						Setting up your UAS Technikum Wien mail account:
					</fo:block>
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt">
						Set up a POP3 account in your mail program of choice. Enter your user name (see Account) and password,
						pop.technikum-wien.at as your incoming mail server and smtp.technikum-wien.at as your outgoing mail server.\n
						Alternatively, you can read mail in your web browser at https://webmail.technikum-wien.at.\n
					</fo:block>
										
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt" font-weight="bold">
						Looking for further information?
					</fo:block>
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt">
						For questions concerning the UAS Technikum Wien network services, see\n
					</fo:block>
					<fo:block text-align="center" content-width='180mm' font-family="sans-serif" font-size="10pt">
						https://cis.technikum-wien.at\n
					</fo:block>
					<fo:block text-align="left" font-family="sans-serif" font-size="10pt">
						These pages will give you a detailed overview of all services available
					</fo:block>
				</fo:block-container>
				
			</fo:flow>
		</fo:page-sequence>
	</xsl:template>
</xsl:stylesheet >