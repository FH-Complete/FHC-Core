<?php
/* Copyright (C) 2006 Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>, 
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
/**
 * Hauptmenue fuer CIS
 */
?>
<table class="tabcontent">
<tr>
	<td width="159" valign="top" class="tdwrap">
		<table class="tabcontent">
		<tr>
			<td>&nbsp;</td>
		</tr>
		<!-- Hauptmenue News -->
		<tr>
          	<td class="tdwrap"><a href="public/news.php"  target="content" class="MenuItem" onClick="js_toggle_container('NEWS');"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;News</a></td>
        </tr>
		<tr>
          	<td class="tdwrap">
				<table class="tabcontent" id="NEWS" style="display: visible;">
				<tr>
		  			<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" target="_blank" href="http://www.technikum-wien.at/service/intern"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Newsletter</a></td>
		  		</tr>
<!--		  		<tr>
		  			<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" target="content" href="private/lehre/pinboard.php?fachbereich_kurzbz=Senat"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Senatsbeschl&uuml;sse</a></td>
		  		</tr>-->
		  		<tr>
		  			<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" target="_blank" href="http://www.technikum-wien.at/service/termine/"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Veranstaltungen</a></td>
		  		</tr>
		  		<tr>
		  			<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" target="_blank" href="http://alumni.technikum-wien.at/member_area/"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Jobb&ouml;rse</a></td>
		  		</tr>
			  	</table>
			</td>
  		</tr>

		<!-- Hauptmenue Lehre -->
		<tr>
          	<td class="tdwrap"><a href="?Lehre" class="MenuItem" onClick="return(js_toggle_container('Lehre'));"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Lehre</a></td>
        </tr>
		<tr>
          	<td class="tdwrap">
		  		<table class="tabcontent" id="Lehre" style="display: visible;">
			  	<tr>
			  		<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="private/lehre/menu.php"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Lehrveranstaltungen</a></td>
			  	</tr>
	    	  	<tr>
			  		<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="private/freifaecher/menu.php"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Freif&auml;cher</a></td>
			  	</tr>
			  	<tr>
			  		<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="private/lvplan/index.php" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;LV-Plan</a></td>
				</tr>
			  	<tr>
			  		<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="http://valar.technikum-wien.at" target="_blank"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Dynamic Power Trainer</a></td>
				</tr>

				<tr>
					<td class="tdwidth10" nowrap>&nbsp;</td>
	          		<td class="tdwrap"><a class="Item" href="public/tw_international.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;TW International</a></td>
	  			</tr>
	  			<tr>
					<td class="tdwidth10" nowrap>&nbsp;</td>
	          		<td class="tdwrap"><a class="Item" href="http://student.ephorus.de" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Plagiatspr&uuml;fung</a></td>
	  			</tr>

				<tr>
					<td class="tdwrap"></td>
					<td><a href="public/bibliothek_allgemein.html" target="content" class="MenuItem" onClick="js_toggle_container('Bibliothek');"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Bibliothek</a>
				  	<table class="tabcontent" id="Bibliothek" style="display: none">
						<tr>
							<td class="tdwidth10" nowrap>&nbsp;</td>
							<td class="tdwrap"><a class="Item" href="public/bibliothek_allgemein.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Allgemeines</a></td>
						</tr>
						<tr>
							<td class="tdwidth10" nowrap>&nbsp;</td>
							<td class="tdwrap"><a class="Item" href="public/bibliothek_onlinekatalog.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Online-Katalog / Recherche</a></td>
						</tr>
						<tr>
							<td class="tdwidth10" nowrap>&nbsp;</td>
							<td class="tdwrap"><a class="Item" href="public/bibliothek_elektronischeressourcen.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Elektronische Ressourcen</a></td>
						</tr>
						<tr>
							<td class="tdwidth10" nowrap>&nbsp;</td>
							<td class="tdwrap"><a class="Item" href="public/bibliothek_bestellung.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Bestellung von Medien</a></td>
						</tr>
						<tr>
							<td class="tdwidth10" nowrap>&nbsp;</td>
							<td class="tdwrap"><a class="Item" href="public/bibliothek_aktuelles.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Aktuelles</a></td>
						</tr>
					</table>
					</td>
				</tr>	
				</table>
				&nbsp;
		  	</td>
  		</tr>

  		<!-- FuE -->
  		
		<tr>
          	<td class="tdwrap"><a href="http://www.technikum-wien.at/insight/forschung_und_entwicklung/" class="MenuItem" onClick="js_toggle_container('FuE');" target="_blank"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;F &amp; E</a></td>
        </tr>
		<tr>
          	<td class="tdwrap">
		  		<table class="tabcontent" id="FuE" style="display: none;">
			  	<tr>
			  		<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="http://www.technikum-wien.at/insight/forschung_und_entwicklung/f_e_projekte/" target="_blank"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;F&amp;E Projekte</a></td>
			  	</tr>
		  		</table>
		  	</td>
  		</tr>
		<!-- Weiterbildung -->
		<tr>
			<td class="tdwrap"><a href="private/info/weiterbildung/info.html" target="content" class="MenuItem" onClick="js_toggle_container('Weiterbildung');"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Weiterbildung</a>
		  	<table class="tabcontent" id="Weiterbildung" style="display: none">
				<!--<tr>
					<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="campus/weiterbildung/info.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Info und Kontakt</a></td>
				</tr>-->
				<tr>
					<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="private/info/weiterbildung/info.html" target="content" style="font-weight: bold;"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Aktuell</a></td>
				</tr>
				<tr>
					<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="private/info/weiterbildung/aktuelles/aktuelles.html" target="content" style="font-weight: bold;"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Programm 2008/09</a></td>
				</tr>
				<tr>
					<td class="tdwidth10" nowrap>&nbsp;</td>
          			<td class="tdwrap"><a target="content" href="private/info/weiterbildung/archiv/archiv.html" class="MenuItem" onClick="js_toggle_container('WeiterbildungArchiv');" style="font-weight: normal;"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Archiv</a>

		  			<table class="tabcontent" id="WeiterbildungArchiv" style="display: none;">
		  				<tr>
						  	<td class="tdwidth10" nowrap>&nbsp;</td>
							<td class="tdwrap"><a class="Item" href="private/info/weiterbildung/archiv/archiv_07-08.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;2007/08</a></td>
						</tr>
						<tr>
						  	<td class="tdwidth10" nowrap>&nbsp;</td>
							<td class="tdwrap"><a class="Item" href="private/info/weiterbildung/archiv/archiv_06-07.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;2006/07</a></td>
						</tr>
						<tr>
						  	<td class="tdwidth10" nowrap>&nbsp;</td>
							<td class="tdwrap"><a class="Item" href="private/info/weiterbildung/archiv/archiv_05-06.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;2005/06</a></td>
						</tr>
						<tr>
						  	<td class="tdwidth10" nowrap>&nbsp;</td>
							<td class="tdwrap"><a class="Item" href="private/info/weiterbildung/archiv/archiv_04-05.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;2004/05</a></td>
						</tr>
						<tr>
						  	<td class="tdwidth10" nowrap>&nbsp;</td>
							<td class="tdwrap"><a class="Item" href="private/info/weiterbildung/archiv/archiv_03-04.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;2003/04</a></td>
						</tr>
					</table>
					</td>
				</tr>
				<tr>
					<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="private/info/weiterbildung/links.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Links</a></td>
				</tr>
				<tr>
					<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="http://www.lllacademy.at/" target="_blank"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;LLL-Academy</a></td>
				</tr>
			</table>
			</td>
  		</tr>
		<!-- Hauptmenue Kommunikation -->
		<tr>
          <td class="tdwrap"><a href="?Kommunikation" class="MenuItem" onClick="return(js_toggle_container('Kommunikation'));"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Kommunikation</a></td>
  		</tr>
		<tr>
        	<td class="tdwrap">
		  	<table class="tabcontent" id="Kommunikation" style="display: none">
			<tr>
				<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a class="Item" href="private/mailverteiler.php" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Mailverteiler</a></td>
			</tr>
	    	<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a class="Item" href="https://webmail.technikum-wien.at" target="_blank"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Webmail</a></td>
			</tr>
			<tr>
		        <td class="tdwidth10" nowrap>&nbsp;</td>
			  	<td class="tdwrap"><a class="Item" href="private/tools/psearch.php" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Personensuche</a></td>
			</tr>
			<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
			  	<td class="tdwrap"><a class="Item" href="private/info/telefonverzeichnis.php" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Telefonverzeichnis</a></td>
			</tr>
			<!--<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a class="Item" href="http://forum.technikum-wien.at/" target="_blank"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Forum</a></td>
			</tr>-->
			<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a class="Item" href="private/tools/feedback.php" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Feedback</a></td>
			</tr>
			<tr>
				<td class="tdwidth10" nowrap>&nbsp;</td>
			    <td class="tdwrap"><a href="../documents/management/" class="MenuItem" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Unternehmens-<br>&nbsp;&nbsp;&nbsp;kommunikation</a></td>
			</tr>
			</table>
		  	</td>
  		</tr>

		<!-- Hauptmenue Infrastruktur -->
		<tr>
          	<td class="tdwrap"><a href="?Infrastruktur" class="MenuItem" onClick="return(js_toggle_container('Infrastruktur'));"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Infrastruktur</a></td>
  		</tr>
		<tr>
        	<td class="tdwrap">
		  	<table class="tabcontent" id="Infrastruktur" style="display: none">
			<tr>
				<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a class="Item" href="public/ansprechpartner.php" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Ansprechpartner</a></td>
			</tr>
			<tr>
		  		<td class="tdwidth10" nowrap>&nbsp;</td>
		  		<td class="tdwrap"><a class="Item" href="cisdocs/Dienstleistungskatalog.xls" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Dienstleistungskatalog</a></td>
			</tr>
	    	<tr>
				<td class="tdwidth10" nowrap>&nbsp;</td>
				<!--<td class="tdwrap"><a class="Item" href="http://bug.technikum-wien.at/otrs/customer.pl" target="_blank"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Bug Tracking</a></td>-->
 				<td class="tdwrap"><a class="Item" href="https://bug.technikum-wien.at/" target="_blank"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Bug Tracking</a></td>
			</tr>
			<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a class="Item" href="private/tools/notebook_registration.php" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Notebook-Registration</a></td>
			</tr>
			<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a class="Item" href="private/tools/wlan_registration.php" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;WLAN-Registration</a></td>
			</tr>
			<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a class="Item" href="private/info/security.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Security</a></td>
			</tr>
			<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a class="Item" href="private/info/zertifikat.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Zertifikat</a></td>
			</tr>
			<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a class="Item" href="private/info/softgrid2.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;SoftGrid</a></td>
			</tr>
			<tr>
  					<td class="tdwidth10" nowrap>&nbsp;</td>
				    <td class="tdwrap"><a href="?Medienaustattung" class="MenuItem" onClick="return(js_toggle_container('Medienaustattung'));"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Medienaustattung</a>
					<table class="tabcontent" id="Medienaustattung" style="display: none">
					<tr>
						<td class="tdwidth10" nowrap>&nbsp;</td>
				    	<td class="tdwrap"><a class="Item" href="private/tools/inventar.php" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Inventar</a></td>
					</tr>
					<tr>
						<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="public/info/medien/medienausstattung_fixer_pc.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Fixer PC</a></td>
					</tr>
					<tr>
					 	<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="public/info/medien/medienausstattung_laptop.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Laptop</a></td>
					</tr>
					<tr>
					  	<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="public/info/medien/medienausstattung_visualizer.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Visualizer</a></td>
					</tr>
					<tr>
					  	<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="public/info/medien/medienausstattung_video.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Video</a></td>
					</tr>
					<tr>
					  	<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="public/info/medien/medienausstattung_dvd.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;DVD</a></td>
					</tr>
					<tr>
					  	<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="public/info/medien/medienausstattung_minidisc.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Minidisc</a></td>
					</tr>
					<tr>
					  	<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="public/info/medien/medienausstattung_cd.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;CD</a></td>
					</tr>
					<tr>
					  	<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="public/info/medien/medienausstattung_externes_video.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Externes Video</a></td>
					</tr>
					<tr>
					  	<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="public/info/medien/medienausstattung_mikrofon.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Mikrofon</a></td>
					</tr>
					<tr>
					  	<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="public/info/medien/medienausstattung_faq.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;FAQ</a></td>
					</tr>
					</table>
				</td>
			</tr>
			<!--<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a class="Item" href="campus/download_cisumfrage.php" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;CIS Umfrage</a></td>
			</tr>-->

			<tr>
				<td class="tdwidth10" nowrap>&nbsp;</td>
			  	<td class="tdwrap"><a href="?Verwaltungstools" class="MenuItem" onClick="return(js_toggle_container('Verwaltungstools'));"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Verwaltungstools</a>
			   	<table class="tabcontent" id="Verwaltungstools" style="display: none">
			  	<tr>
			  		<td class="tdwidth10" nowrap>&nbsp;</td>
			  		<td class="tdwrap"><a class="Item" href="private/tools/newsverwaltung.php" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Newsverwaltung</a></td>
			  	</tr>
			  	<tr>
			  		<td class="tdwidth10" nowrap>&nbsp;</td>
			  		<td class="tdwrap"><a class="Item" href="https://vilesci.technikum-wien.at/" target="_blank"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;VileSci</a></td>
			  	</tr>
			  	<tr>
			  		<td class="tdwidth10" nowrap>&nbsp;</td>
			  		<td class="tdwrap"><a class="Item" href="https://wawi.technikum-wien.at/" target="_blank"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;WaWi</a></td>
			  	</tr>
			  	</table>
			  	</td>
  			</tr>
  			<tr>
			    <td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a href="?Verordnungen" class="MenuItem" onClick="return(js_toggle_container('Verordnungen'));"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Verordnungen</a>
				  	<table class="tabcontent" id="Verordnungen" style="display: none">
					<tr>
					  	<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="public/info/verordnungen/hausordnung.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Hausordnung</a></td>
					</tr>
					<tr>
					  	<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="public/info/verordnungen/brandschutzordnung.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Brandschutzordnung</a></td>
					</tr>
					<tr>
					  	<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="public/info/verordnungen/benutzungsordnung_bibliothek.doc" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Bibliotheksordnung</a></td>
					</tr>
					<tr>
					  	<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="public/info/verordnungen/EDV_Richtlinien.doc" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;EDV Richtlinien</a></td>
					</tr>
					<tr>
					    <td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a href="?Verordnungen" class="MenuItem" onClick="return(js_toggle_container('Laborordnung'));"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Laborordnung</a>
						  	<table class="tabcontent" id="Laborordnung" style="display: none">
						  	<tr>
							  	<td class="tdwidth10" nowrap>&nbsp;</td>
								<td class="tdwrap"><a class="Item" href="public/info/verordnungen/laborordnung.doc" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Laborordnung Chemie</a></td>
							</tr>
							<tr>
								<td class="tdwidth10" nowrap>&nbsp;</td>
								<td class="tdwrap"><a class="Item" href="public/info/verordnungen/Roboter_Laborordnung.doc" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Laborordnung Roboter</a></td>
							</tr>
							</table>
					</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td class="tdwidth10" nowrap>&nbsp;</td>
		        <td class="tdwrap"><a href="?Location" class="MenuItem" onClick="return(js_toggle_container('Location'));"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Location</a></td>
			</tr>
			<tr>
				<td class="tdwidth10" nowrap>&nbsp;</td>
		        <td class="tdwrap">
				  	<table class="tabcontent" id="Location" style="display: none">
					<tr>
						<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="public/location.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Standort</a></td>
					</tr>
					<tr>
					  	<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="public/tw_building.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Geb&auml;udeplan</a></td>
					</tr>
					</table>
				</td>
  			</tr>
			</table>
		  	</td>
  		</tr>

  		<!--QM-->
  		<tr>

			<td class="tdwrap">
				<a href="private/info/qm/info.html" target="content" class="MenuItem" onClick="js_toggle_container('QM');"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Qualit&auml;tsmanagement</a>
				<table class="tabcontent" id="QM" style="display: none">
		  		<tr>
		  			<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="private/info/qm/Qualitaetsmanagementsystem.pdf" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;QM-Handbuch</a></td>
		  		</tr>
				<tr>
		  			<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="private/info/qm/organigramm.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Organigramm</a></td>
		  		</tr>
				<tr>
		  			<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="private/info/qm/prozesse/prozessmodell.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Prozesse</a></td>
		  		</tr>

				<tr>
		  			<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="private/info/qm/dokumente/dokumente.php" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Dokumente</a></td>
		  		</tr>
		  		<tr>
		  			<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="private/info/qm/vorlagen/Ground_Rules.pdf" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Ground Rules</a></td>
		  		</tr>
				</table>
			</td>
		</tr>

		<!-- Hauptmenue Rektor -->
		<tr>
          <td class="tdwrap"><a href="public/rektorat.html" class="MenuItem" target="content" onClick="js_toggle_container('Infos');"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Rektorat</a></td>
  		</tr>
		<tr>
        	<td class="tdwrap">
		  	<table class="tabcontent" id="Infos" style="display: none;">
		  	<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a class="Item" href="public/rektortw.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Rektor Technikum Wien</a></td>
			</tr>
  			<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a href="public/kollegium.html" class="MenuItem" onClick="js_toggle_container('Kollegium');" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Fachhochschulkollegium</a></td>
			</tr>
			<tr>
				<td class="tdwidth10" nowrap></td>
				<td class="tdwrap">
		  		<table class="tabcontent" id="Kollegium" style="display: none;">
				<tr>
					<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="public/kollegiumswahl.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Kollegiumswahl 2006/2007</a></td>
				</tr>
				</table>
				</td>
			</tr>
			<tr>
			    <td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a href="?Jahresplan" class="MenuItem" onClick="return(js_toggle_container('Jahresplan'));"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Jahresplan</a>
				  	<table class="tabcontent" id="Jahresplan" style="display: none">
					<tr>
					  	<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="private/info/jahresplan_WS2008.php" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;WS 2008</a></td>
					</tr>
					<tr>
					  	<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="private/info/jahresplan_SS2009.php" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;SS 2009</a></td>
					</tr>

					</table>
				</td>
			</tr>
<!--			<tr>
        		<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a class="Item" href="public/linkliste.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Links</a></td>
  			</tr>
-->
			<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a href="../documents/management/umgang_vielfalt" class="Item" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Umgang mit Vielfalt</a></td>
			</tr>

			</table>
		</td>
  		</tr>
		<!-- Hauptmenue Studentenvertretung -->
	<!--	<tr>
          <td class="tdwrap"><a href="?Studentenvertretung" class="MenuItem" onClick="return(js_toggle_container('Studentenvertretung'));"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Studentenvertretung</a></td>
  		</tr>
		<tr>
        	<td class="tdwrap">
		  	<table class="tabcontent" id="Studentenvertretung" style="display: none;">
		  	<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a class="Item" href="public/rektortw.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;zur Homepage</a></td>
			</tr>
  			<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a href="public/kollegium.html" class="Item" onClick="js_toggle_container('Kollegium');" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Statuten</a></td>
			</tr>
			</table>
		</td>-->
  		</tr>
  		<!-- Hauptmenue Betriebsrat -->
 		<tr>
			<td class="tdwrap">
				<a href="?Betriebsrat" class="MenuItem" onClick="return(js_toggle_container('BTR'));"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Betriebsrat</a>
				<table class="tabcontent" id="BTR" style="display: none">
		  		<tr>
		  			<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="private/info/betriebsrat/news.php" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;News</a></td>
		  		</tr>
		  		<tr>
		  			<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="private/info/betriebsrat/info.php" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Info</a></td>
		  		</tr>
		  		<tr>
		  			<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="private/info/betriebsrat/dokumente.php" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Dokumente</a></td>
		  		</tr>
			  	</table>
			</td>
		</tr>
 		<!-- Hauptmenue FAQ -->
 		<tr>
			<td class="tdwrap">
				<a href="?FAQ" class="MenuItem" onClick="return(js_toggle_container('FAQ'));"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;FAQ</a>
				<table class="tabcontent" id="FAQ" style="display: none">
				<tr>
		  			<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="public/faq_systeminfo.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;allgem. Systeminfo</a></td>
		  		</tr>
		  		<tr>
		  			<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="public/faq_lan.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;LAN FAQ</a></td>
		  		</tr>
		  		<tr>
		  			<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="public/faq_druckinsel.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Druckinsel FAQ</a></td>
		  		</tr>
				<tr>
		  			<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="public/faq_bug.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Bug Tracking FAQ</a></td>
		  		</tr>
		  		<tr>
		  			<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="public/faq_upload.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Upload Lehre FAQ</a></td>
		  		</tr>
		  		<tr>
			  		<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="public/info/mail.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Einrichten des Mails</a></td>
				</tr>
			  	</table>
			</td>
		</tr>

		<!-- ************* Meine CIS ******************* -->
  		<tr>
			<td class="tdwrap"><a class="MenuItem" href="private/menu.php" ><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Mein CIS</a></td>
		</tr>

<!--		<tr>
	       	<td class="tdwrap">
		  	<table class="tabcontent" id="MeineCIS" style="display: visible;">
		  	<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a class="Item" href="private/profile/index.php" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Profil</a></td>
			</tr>
		  	<tr>
				<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a class="Item" href="private/lvplan/stpl_week.php" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;LV-Plan</a></td>
			</tr>
-->
<!--			<tr>
				<td class="tdwidth10" nowrap>&nbsp;</td>
		        <td class="tdwrap"><a href="?Location" class="MenuItem" onClick="return(js_toggle_container('MeineLVs'));"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Location</a></td>
			</tr>
			<tr>
				<td class="tdwidth10" nowrap>&nbsp;</td>
		        <td class="tdwrap">
				  	<table class="tabcontent" id="MeineLVs" style="display: none">
					<tr>
						<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="public/location.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Standort</a></td>
					</tr>
					<tr>
					  	<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="public/tw_building.html" target="content"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Geb&auml;udeplan</a></td>
					</tr>
					</table>
				</td>
			</tr>-->
			</table>
			</td>
  		</tr>
	  </table>
	</td>

  </tr>
</table>