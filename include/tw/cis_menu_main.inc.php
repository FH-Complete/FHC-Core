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
          	<td class="tdwrap"><a href="public/news.php"  target="content" class="MenuItem" onClick="js_toggle_container('NEWS');"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;News</a></td>
        </tr>
		<tr>
          	<td class="tdwrap">
				<table class="tabcontent" id="NEWS" style="display: visible;">
				<tr>
		  			<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" target="_blank" href="http://www.technikum-wien.at/fh/aktuelles/news/"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Newsletter</a></td>
		  		</tr>
	  			<tr>
	          		<td class="tdwrap"></td>
					<td><a target="content" href="private/jahresplan/index.php" class="MenuItem" onClick="js_toggle_container('jahresplan');"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Eventkalender</a>
				  	<table class="tabcontent" id="jahresplan" style="display: none">
						<tr>
							<td class="tdwidth10" nowrap>&nbsp;</td>
							<td class="tdwrap"><a class="Item" href="/documents/fotos/" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Sponsionsfotos</a></td>
						</tr>
					</table>
					</td>
	  			</tr>	

		  		<tr>
		  			<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" target="_blank" href="http://alumni.technikum-wien.at/member_area/"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Jobb&ouml;rse</a></td>
		  		</tr>
				
		  		<tr>
	<!--		  		<td class="tdwrap"></td>
					<td><a href="private/info/oeh/index.php" target="content" class="Item" ><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;&Ouml;H-Wahl</a></td>
	-->
					<td class="tdwrap"></td>
					<td><a target="content" href="private/info/oeh/index.php" class="MenuItem" onClick="js_toggle_container('oeh');"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;ÖH</a>
				  	<table class="tabcontent" id="oeh" style="display: visible">
						<tr>
				  			<td class="tdwrap"></td>
							<td><a href="private/info/oeh/index2010.php" target="content" class="Item"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;&Ouml;H-Mandate Fr&uuml;hjahr 2010</a></td>
				  		</tr>
				  		<tr>
				  			<td class="tdwrap"></td>
							<td><a href="private/info/oeh/index2010herbst.php" target="content" class="Item"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;&Ouml;H-Herbstwahlen 2010</a></td>
				  		</tr>
				  	</table>
				 </tr>

				
	<!--    		<tr>
		  			<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" target="_blank" href="http://twist.technikum-wien.at/"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Studierendenvertretung</a></td>
		  		</tr>
	-->	
		  		<tr>
		  			<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" target="_blank" href="http://www.groll-gars.at/mensa.htm"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Mensa</a></td>
		  		</tr>

		  		
			  	</table>
			</td>
  		</tr>

		<!-- Hauptmenue Lehre -->
		<tr>
          	<td class="tdwrap"><a href="?Lehre" class="MenuItem" onClick="return(js_toggle_container('Lehre'));"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Lehre</a></td>
        </tr>
		<tr>
          	<td class="tdwrap">
		  		<table class="tabcontent" id="Lehre" style="display: visible;">
			  	<tr>
			  		<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="private/lehre/menu.php"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Lehrveranstaltungen</a></td>
			  	</tr>
	    	  	<tr>
			  		<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="private/freifaecher/menu.php"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Freif&auml;cher</a></td>
			  	</tr>
			  	<tr>
			  		<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="private/lvplan/index.php" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;LV-Plan</a></td>
				</tr>

			  	<tr>
			  		<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="http://valar3.technikum-wien.at:8080/dptmanager/" target="_blank"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Dynamic Power Trainer</a></td>
				</tr>

	    	  	<tr>
			  		<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="private/lehre/softgrid.php"  target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Software f&uuml;r Lehre</a></td>
			  	</tr>

				
				<tr>
					<td class="tdwidth10" nowrap>&nbsp;</td>
	          		<td class="tdwrap"><a class="Item" href="public/tw_international.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;FHTW International</a></td>
	  			</tr>
	  			<tr>
	  				<!--
					<td class="tdwidth10" nowrap>&nbsp;</td>
	          		<td class="tdwrap"><a class="Item" href="http://student.ephorus.de" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Plagiatspr&uuml;fung</a></td>
	          		-->
	          		<td class="tdwrap"></td>
					<td><a href="#" class="MenuItem" onClick="js_toggle_container('Plagiatspruefung');"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Plagiatspr&uuml;fung</a>
				  	<table class="tabcontent" id="Plagiatspruefung" style="display: none">
						<tr>
							<td class="tdwidth10" nowrap>&nbsp;</td>
							<td class="tdwrap"><a class="Item" href="public/plagiatspruefung_lektor.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;f&uuml;r LektorInnen</a></td>
						</tr>
						<tr>
							<td class="tdwidth10" nowrap>&nbsp;</td>
							<td class="tdwrap"><a class="Item" href="public/plagiatspruefung_student.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;f&uuml;r StudentInnen</a></td>
						</tr>
					</table>
					</td>
	  			</tr>
		  		<tr> 
					<td class="tdwrap"></td>
					<td><a href="#" class="MenuItem" onClick="js_toggle_container('Bibliothek');"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Bibliothek / Library</a>


				  		<table class="tabcontent" id="Bibliothek" style="display: none">
							<tr>
		  						<td class="tdwidth10" nowrap>&nbsp;</td>
							    <td class="tdwrap"><a href="public/bibliothek/Deutsch/bibliothek_allgemein.html" target="content" class="MenuItem" onClick="js_toggle_container('BIBLIOTHEK_DE');"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Deutsch</a>
								<table class="tabcontent" id="BIBLIOTHEK_DE" style="display: none">

										<tr>
											<td class="tdwidth10" nowrap>&nbsp;</td>
											<td class="tdwrap"><a class="Item" href="public/bibliothek/Deutsch/bibliothek_allgemein.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Allgemeines</a></td>
										</tr>
										<tr>
											<td class="tdwidth10" nowrap>&nbsp;</td>
											<td class="tdwrap"><a class="Item" href="public/bibliothek/Deutsch/bibliothek_onlinekatalog.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Online-Katalog / Recherche</a></td>
										</tr>
				
										<tr>
											<td class="tdwidth10" nowrap>&nbsp;</td>
											<td class="tdwrap"><a class="Item" href="public/bibliothek/Deutsch/bibliothek_ebooks.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;eBooks</a></td>
										</tr>
				
										<tr>
											<td class="tdwidth10" nowrap>&nbsp;</td>
											<td class="tdwrap"><a class="Item" href="public/bibliothek/Deutsch/bibliothek_e_journals.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;E-Journals</a></td>
										</tr>
				
										<tr>
											<td class="tdwidth10" nowrap>&nbsp;</td>
											<td class="tdwrap"><a class="Item" href="public/bibliothek/Deutsch/bibliothek_datenbanken.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Datenbanken</a></td>
										</tr>
				
				
										<tr>
											<td class="tdwidth10" nowrap>&nbsp;</td>
											<td class="tdwrap"><a class="Item" href="public/bibliothek/Deutsch/bibliothek_bestellung.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Bestellung von Medien</a></td>
										</tr>
										<tr>
											<td class="tdwidth10" nowrap>&nbsp;</td>
											<td class="tdwrap"><a class="Item" href="public/bibliothek/Deutsch/bibliothek_Publikationsdatenbank.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Publikationsdatenbank</a></td>
										</tr>
										<tr>
											<td class="tdwidth10" nowrap>&nbsp;</td>
											<td class="tdwrap"><a class="Item" href="public/bibliothek/Deutsch/bibliothek_aktuelles.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Aktuelles</a></td>
										</tr>
								</table>
								</td>
							</tr>

							<tr>
		  						<td class="tdwidth10" nowrap>&nbsp;</td>
							    <td class="tdwrap"><a href="public/bibliothek/Englisch/bibliothek_allgemein.html" target="content" class="MenuItem" onClick="js_toggle_container('BIBLIOTHEK_EN');"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;English</a>
								<table class="tabcontent" id="BIBLIOTHEK_EN" style="display: none">

										<tr>
											<td class="tdwidth10" nowrap>&nbsp;</td>
											<td class="tdwrap"><a class="Item" href="public/bibliothek/Englisch/bibliothek_allgemein.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;General information</a></td>
										</tr>
										<tr>
											<td class="tdwidth10" nowrap>&nbsp;</td>
											<td class="tdwrap"><a class="Item" href="public/bibliothek/Englisch/bibliothek_onlinekatalog.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Online Catalog / Research</a></td>
										</tr>
				
										<tr>
											<td class="tdwidth10" nowrap>&nbsp;</td>
											<td class="tdwrap"><a class="Item" href="public/bibliothek/Englisch/bibliothek_ebooks.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;eBooks</a></td>
										</tr>
				
										<tr>
											<td class="tdwidth10" nowrap>&nbsp;</td>
											<td class="tdwrap"><a class="Item" href="public/bibliothek/Englisch/bibliothek_e_journals.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;E-Journals</a></td>
										</tr>
				
										<tr>
											<td class="tdwidth10" nowrap>&nbsp;</td>
											<td class="tdwrap"><a class="Item" href="public/bibliothek/Englisch/bibliothek_datenbanken.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Databases</a></td>
										</tr>
				
				
										<tr>
											<td class="tdwidth10" nowrap>&nbsp;</td>
											<td class="tdwrap"><a class="Item" href="public/bibliothek/Englisch/bibliothek_bestellung.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Ordering</a></td>
										</tr>
										<tr>
											<td class="tdwidth10" nowrap>&nbsp;</td>
											<td class="tdwrap"><a class="Item" href="public/bibliothek/Englisch/bibliothek_Publikationsdatenbank.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Publication Database</a></td>
										</tr>
										<tr>
											<td class="tdwidth10" nowrap>&nbsp;</td>
											<td class="tdwrap"><a class="Item" href="public/bibliothek/Englisch/bibliothek_aktuelles.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;News</a></td>
										</tr>
								</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
			  		<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="private/info/gender/Gender_Toolkit.pdf" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Gender Toolkit</a></td>
			  	</tr>
				</table>
				&nbsp;
		  	</td>
  		</tr>

  		<!-- FuE -->
  		
		<tr>
          	<td class="tdwrap"><a href="http://www.technikum-wien.at/fh/forschung___entwicklung/" class="MenuItem" onClick="js_toggle_container('FuE');" target="_blank"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;F &amp; E</a></td>
        </tr>
		<tr>
          	<td class="tdwrap">
		  		<table class="tabcontent" id="FuE" style="display: none;">
			  	<tr>
			  		<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="http://www.technikum-wien.at/fh/forschung___entwicklung/forschungsaktivitaeten/" target="_blank"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;F&amp;E Projekte</a></td>
			  	</tr>
		  		</table>
		  	</td>
  		</tr>
		<!-- Weiterbildung -->
		<tr>
			<td class="tdwrap"><a href="private/info/weiterbildung/info.html" target="content" class="MenuItem" onClick="js_toggle_container('Weiterbildung');"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Weiterbildung</a></td>
		</tr>
		<tr>
			<td class="tdwrap">
		  	<table class="tabcontent" id="Weiterbildung" style="display: none">
				<tr>
					<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="private/info/weiterbildung/info.html" target="content" style="font-weight: bold;"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Aktuell</a></td>
				</tr>
			<!--<tr>
					<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="private/info/weiterbildung/aktuelles/aktuelles.html" target="content" style="font-weight: bold;"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Programm 2010/11</a></td>
				</tr> -->
				<tr>
					<td class="tdwidth10" nowrap>&nbsp;</td>
          			<td class="tdwrap"><a target="content" href="private/info/weiterbildung/archiv/archiv.html" class="MenuItem" onClick="js_toggle_container('WeiterbildungArchiv');" style="font-weight: normal;"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Archiv</a>

		  			<table class="tabcontent" id="WeiterbildungArchiv" style="display: none;">
		  				<tr>
						  	<td class="tdwidth10" nowrap>&nbsp;</td>
							<td class="tdwrap"><a class="Item" href="private/info/weiterbildung/archiv/archiv_10-11.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;2010/11</a></td>
						</tr>
		  				<tr>
						  	<td class="tdwidth10" nowrap>&nbsp;</td>
							<td class="tdwrap"><a class="Item" href="private/info/weiterbildung/archiv/archiv_09-10.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;2009/10</a></td>
						</tr>
		  				<tr>
						  	<td class="tdwidth10" nowrap>&nbsp;</td>
							<td class="tdwrap"><a class="Item" href="private/info/weiterbildung/archiv/archiv_08-09.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;2008/09</a></td>
						</tr>
		  				<tr>
						  	<td class="tdwidth10" nowrap>&nbsp;</td>
							<td class="tdwrap"><a class="Item" href="private/info/weiterbildung/archiv/archiv_07-08.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;2007/08</a></td>
						</tr>
						<tr>
						  	<td class="tdwidth10" nowrap>&nbsp;</td>
							<td class="tdwrap"><a class="Item" href="private/info/weiterbildung/archiv/archiv_06-07.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;2006/07</a></td>
						</tr>
						<tr>
						  	<td class="tdwidth10" nowrap>&nbsp;</td>
							<td class="tdwrap"><a class="Item" href="private/info/weiterbildung/archiv/archiv_05-06.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;2005/06</a></td>
						</tr>
						<tr>
						  	<td class="tdwidth10" nowrap>&nbsp;</td>
							<td class="tdwrap"><a class="Item" href="private/info/weiterbildung/archiv/archiv_04-05.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;2004/05</a></td>
						</tr>
						<tr>
						  	<td class="tdwidth10" nowrap>&nbsp;</td>
							<td class="tdwrap"><a class="Item" href="private/info/weiterbildung/archiv/archiv_03-04.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;2003/04</a></td>
						</tr>
					</table>
					</td>
				</tr>
				<tr>
					<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="private/info/weiterbildung/links.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Links</a></td>
				</tr>
				<tr>
					<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="http://www.lllacademy.at/" target="_blank"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;LLL-Academy</a></td>
				</tr>
				<tr>
					<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="http://www.technikum-wien.at/studium/lifelong_learning/cisco_academy/" target="_blank"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Cisco Academy</a></td>
				</tr>
			</table>
			</td>
  		</tr>
		<!-- Hauptmenue Kommunikation -->
		<tr>
          <td class="tdwrap"><a href="?Kommunikation" class="MenuItem" onClick="return(js_toggle_container('Kommunikation'));"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Kommunikation</a></td>
  		</tr>
		<tr>
        	<td class="tdwrap">
		  	<table class="tabcontent" id="Kommunikation" style="display: none">
			<tr>
				<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a class="Item" href="private/mailverteiler.php" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Mailverteiler</a></td>
			</tr>
	    	<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a class="Item" href="https://webmail.technikum-wien.at" target="_blank"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Webmail</a></td>
			</tr>
			<tr>
		        <td class="tdwidth10" nowrap>&nbsp;</td>
			  	<td class="tdwrap"><a class="Item" href="private/tools/psearch.php" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Personensuche</a></td>
			</tr>
			<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
			  	<td class="tdwrap"><a class="Item" href="private/info/telefonverzeichnis.php" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Telefonverzeichnis</a></td>
			</tr>
			<!--<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a class="Item" href="http://forum.technikum-wien.at/" target="_blank"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Forum</a></td>
			</tr>-->
			<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a class="Item" href="private/tools/feedback.php" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Feedback</a></td>
			</tr>
			<!--<tr>
				<td class="tdwidth10" nowrap>&nbsp;</td>-->
				
			    <!--<td class="tdwrap"><a href="private/info/unternehmenskommunikation.html" class="MenuItem" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Unternehmens-<br>&nbsp;&nbsp;&nbsp;kommunikation</a></td>-->
<!--
			    <td class="tdwrap"><a href="../documents/management/" class="MenuItem" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Unternehmens-<br>&nbsp;&nbsp;&nbsp;kommunikation</a></td>
-->
			<!--</tr>-->
			</table>
		  	</td>
  		</tr>

		<!-- Hauptmenue Infrastruktur -->
		<tr>
          	<td class="tdwrap"><a href="?Infrastruktur" class="MenuItem" onClick="return(js_toggle_container('Infrastruktur'));"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Infrastruktur</a></td>
  		</tr>
		<tr>
        	<td class="tdwrap">
		  	<table class="tabcontent" id="Infrastruktur" style="display: none">
		  	<tr>
				<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a class="Item" href="private/info/twbook/" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;twbook</a></td>
			</tr>
			<tr>
					<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="MenuItem" href="public/team_leitung.html" target="content" onClick="js_toggle_container('Team');"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Team</a>
					<table class="tabcontent" id="Team" style="display: none">
					<tr>
					 	<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="public/team_servicedesk.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Service Desk</a></td>
					</tr>
					<tr>
					 	<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="public/team_lvplanung.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;LV-Planung</a></td>
					</tr>
					<tr>
					 	<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="public/team_zentraleinkauf.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Zentraleinkauf</a></td>
					</tr>
					<tr>
					 	<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="public/team_haustechnik.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Haustechnik</a></td>
					</tr>
					<tr>
					 	<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="public/team_systementwicklung.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Systementwicklung</a></td>
					</tr>
					<tr>
					 	<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="public/team_serveradministration.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Serveradministration (Zentrale Services)</a></td>
					</tr>
					</table>
				</td>
			</tr>
			<tr>
		  		<td class="tdwidth10" nowrap>&nbsp;</td>
		  		<td class="tdwrap"><a class="Item" href="cisdocs/Dienstleistungskatalog.xls" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Dienstleistungskatalog</a></td>
			</tr>
	    	<tr>
				<td class="tdwidth10" nowrap>&nbsp;</td>
 				<td class="tdwrap"><a class="Item" href="https://bug.technikum-wien.at/" target="_blank"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Bug Tracking</a></td>
			</tr>
					<tr>
						<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="private/tools/notebook_registration.php" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Notebook-Registration</a></td>
					</tr>
					<tr>
						<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="private/tools/wlan_registration.php" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;WLAN-Zugang</a></td>
					</tr>
					<tr>
						<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="private/info/vpn/index.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;VPN-Zugang</a></td>
					</tr>
					<tr>
						<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="private/info/security.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Security</a></td>
					</tr>
					<tr>
						<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="private/info/zertifikat.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Zertifikat</a></td>
					</tr>
					<tr>
						<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="private/info/softgrid2.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;App-V&nbsp;(SoftGrid)</a></td>
					</tr>
				<tr>
					<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a href="?Medienaustattung" class="MenuItem" onClick="return(js_toggle_container('Medienaustattung'));"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Medienaustattung</a>
					<table class="tabcontent" id="Medienaustattung" style="display: none">
					<tr>
						<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="public/info/medien/medienausstattung_fixer_pc.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Fixer PC</a></td>
					</tr>
					<tr>
					 	<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="public/info/medien/medienausstattung_laptop.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Laptop</a></td>
					</tr>
					<tr>
					  	<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="public/info/medien/medienausstattung_visualizer.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Visualizer</a></td>
					</tr>
					<tr>
					  	<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="public/info/medien/medienausstattung_video.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Video</a></td>
					</tr>
					<tr>
					  	<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="public/info/medien/medienausstattung_dvd.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;DVD</a></td>
					</tr>
					<tr>
					  	<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="public/info/medien/medienausstattung_minidisc.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Minidisc</a></td>
					</tr>
					<tr>
					  	<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="public/info/medien/medienausstattung_cd.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;CD</a></td>
					</tr>
					<tr>
					  	<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="public/info/medien/medienausstattung_externes_video.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Externes Video</a></td>
					</tr>
					<tr>
					  	<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="public/info/medien/medienausstattung_mikrofon.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Mikrofon</a></td>
					</tr>
					<tr>
					  	<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="public/info/medien/medienausstattung_faq.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;FAQ</a></td>
					</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td class="tdwidth10" nowrap>&nbsp;</td>
			  	<td class="tdwrap"><a href="?Verwaltungstools" class="MenuItem" onClick="return(js_toggle_container('Verwaltungstools'));"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Verwaltungstools</a>
			   	<table class="tabcontent" id="Verwaltungstools" style="display: none">
			  	<tr>
			  		<td class="tdwidth10" nowrap>&nbsp;</td>
			  		<td class="tdwrap"><a class="Item" href="private/tools/newsverwaltung.php" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Newsverwaltung</a></td>
			  	</tr>
			  	<tr>
			  		<td class="tdwidth10" nowrap>&nbsp;</td>
			  		<td class="tdwrap"><a class="Item" href="https://vilesci.technikum-wien.at/" target="_blank"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;VileSci</a></td>
			  	</tr>
			  	<tr>
			  		<td class="tdwidth10" nowrap>&nbsp;</td>
			  		<td class="tdwrap"><a class="Item" href="https://wawi.technikum-wien.at/" target="_blank"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;WaWi</a></td>
			  	</tr>
			  	</table>
			  	</td>
  			</tr>
			
			<tr>
				<td class="tdwidth10" nowrap>&nbsp;</td>
			  	<td class="tdwrap"><a href="?AW_Verwaltungstools" class="MenuItem" onClick="return(js_toggle_container('AW_Verwaltungstools'));"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9" title="Regulations">&nbsp;Verordnungen</a>
			   	<table class="tabcontent" id="AW_Verwaltungstools" style="display: none">

					<tr>
						<td class="tdwidth10" nowrap>&nbsp;</td>
					  	<td class="tdwrap"><a href="?DE_Verwaltungstools" class="MenuItem" onClick="return(js_toggle_container('DE_Verwaltungstools'));"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Deutsch</a>
					   	<table class="tabcontent" id="DE_Verwaltungstools" style="display: none">
							
							<tr>
							  	<td class="tdwidth10" nowrap>&nbsp;</td>
								<td class="tdwrap"><a class="Item" href="public/info/verordnungen/Hausordnung.pdf" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Hausordnung</a></td>
							</tr>
							<tr>
							  	<td class="tdwidth10" nowrap>&nbsp;</td>
								<td class="tdwrap"><a class="Item" href="public/info/verordnungen/Brandschutzordnung.pdf" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Brandschutzordnung</a></td>
							</tr>
							<tr>
							  	<td class="tdwidth10" nowrap>&nbsp;</td>
								<td class="tdwrap"><a class="Item" href="public/info/verordnungen/Benutzungsordnung_Bibliothek.pdf" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Bibliotheksordnung</a></td>
							</tr>
							<tr>
							  	<td class="tdwidth10" nowrap>&nbsp;</td>
								<td class="tdwrap"><a class="Item" href="public/info/verordnungen/EDV Richtlinien.pdf" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;EDV Richtlinien</a></td>
							</tr>
							<tr>
							  	<td class="tdwidth10" nowrap>&nbsp;</td>
								<td class="tdwrap"><a class="Item" href="public/info/verordnungen/Datensicherung_Archivierung.pdf" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Datensicherung und Archivierung</a></td>
							</tr>
							
							<tr>
							    <td class="tdwidth10" nowrap>&nbsp;</td>
								<td class="tdwrap"><a href="?Laborordnung" class="MenuItem" onClick="return(js_toggle_container('Laborordnung'));"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Laborordnung</a>
								<table class="tabcontent" id="Laborordnung" style="display: none">
								  	<tr>
									  	<td class="tdwidth10" nowrap>&nbsp;</td>
										<td class="tdwrap"><a class="Item" href="public/info/verordnungen/Laborordnung Chemie.pdf" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Laborordnung Chemie</a></td>
									</tr>
									<tr>
										<td class="tdwidth10" nowrap>&nbsp;</td>
										<td class="tdwrap"><a class="Item" href="public/info/verordnungen/Laborordnung Roboterlabor.pdf" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Laborordnung Roboter</a></td>
									</tr>
								</table>
							</tr>
							
						</table>
						</td>
					</tr>							
							
					<tr>
						<td class="tdwidth10" nowrap>&nbsp;</td>
					  	<td class="tdwrap"><a href="?EN_Verwaltungstools" class="MenuItem" onClick="return(js_toggle_container('EN_Verwaltungstools'));"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Englisch</a>
					   	<table class="tabcontent" id="EN_Verwaltungstools" style="display: none">
					
							<tr>
							  	<td class="tdwidth10" nowrap>&nbsp;</td>
								<td class="tdwrap"><a class="Item" href="public/info/verordnungen/Hausordnung_E.pdf" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;General rules of conduct</a></td>
							</tr>
							<tr>
							  	<td class="tdwidth10" nowrap>&nbsp;</td>
								<td class="tdwrap"><a class="Item" href="public/info/verordnungen/Brandschutzordnung_E.pdf" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Fire Regulations</a></td>
							</tr>
							<tr>
							  	<td class="tdwidth10" nowrap>&nbsp;</td>
								<td class="tdwrap"><a class="Item" href="public/info/verordnungen/Library_regulations.pdf" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Library Regulations</a></td>
							</tr>
							<tr>
							  	<td class="tdwidth10" nowrap>&nbsp;</td>
								<td class="tdwrap"><a class="Item" href="public/info/verordnungen/EDV Richtlinien_E.pdf" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Guidelines for using EDP resources </a></td>
							</tr>
							<tr>
							  	<td class="tdwidth10" nowrap>&nbsp;</td>
								<td class="tdwrap"><a class="Item" href="public/info/verordnungen/Datensicherung_Archivierung_E.pdf" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Data back-up and archiving guidelines </a></td>
							</tr>
							
							<tr>
							    <td class="tdwidth10" nowrap>&nbsp;</td>
								<td class="tdwrap"><a href="?Laborordnung_E" class="MenuItem" onClick="return(js_toggle_container('Laborordnung_E'));"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Laboratory Regulations </a>
								<table class="tabcontent" id="Laborordnung_E" style="display: none">
								  	<tr>
									  	<td class="tdwidth10" nowrap>&nbsp;</td>
										<td class="tdwrap"><a class="Item" href="public/info/verordnungen/Laborordnung Chemie_E.pdf" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Extract from the Chemistry laboratory regulations </a></td>
									</tr>
									<tr>
										<td class="tdwidth10" nowrap>&nbsp;</td>
										<td class="tdwrap"><a class="Item" href="public/info/verordnungen/Laborordnung Roboterlabor_E.pdf" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Robot Laboratory </a></td>
									</tr>
								</table>
							</tr>
						</table>
						</td>
					</tr>
			
				</table>
				</td>
			</tr>

			
			
					
			<tr>
				<td class="tdwidth10" nowrap>&nbsp;</td>
		        <td class="tdwrap"><a href="?Location" class="MenuItem" onClick="return(js_toggle_container('Location'));"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Location</a></td>
			</tr>
			<tr>
				<td class="tdwidth10" nowrap>&nbsp;</td>
		        <td class="tdwrap">
				  	<table class="tabcontent" id="Location" style="display: none">
					<tr>
						<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="public/location.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Standort</a></td>
					</tr>
					<tr>
					  	<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="public/tw_building.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Geb&auml;udeplan</a></td>
					</tr>
					</table>
				</td>
  			</tr>
			</table>
		  	</td>
  		</tr>


  		<!--QM-->

  		<tr> 
			<td class="tdwrap"><a href="private/info/qm/info.html" target="content" class="MenuItem" onClick="js_toggle_container('QM');"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Qualit&auml;tsmanagement</a></td>
				<!--QM Language Deutsch -->				
				<tr>
        		<td class="tdwrap">
		  		<table class="tabcontent" id="QM" style="display: none">
						
					<tr>
					
  						<td class="tdwidth10" nowrap>&nbsp;</td>
					    <td class="tdwrap"><a href="private/info/qm/info.html" target="content" class="MenuItem" onClick="return(js_toggle_container('QM_DE'));"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Deutsch</a>
						<table class="tabcontent" id="QM_DE" style="display: none">

				  		<tr>
				  			<td class="tdwidth10" nowrap>&nbsp;</td>
							<td class="tdwrap"><a target="content" class="Item" href="private/info/qm_2010/Deutsch/1_Handbuch/QM-Handbuch_FHTW_2010.pdf" ><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;QM-Handbuch</a></td>
			  			</tr>

				  		<tr>
				  			<td class="tdwidth10" nowrap>&nbsp;</td>
							<td class="tdwrap"><a target="content" class="Item" href="private/info/qm/Qualitaetsmanagementhandbuch.php?lang="><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;CRM@FHTW</a></td>
			  			</tr>
						<tr>
				  			<td class="tdwidth10" nowrap>&nbsp;</td>
							<td class="tdwrap"><a class="Item" href="private/info/qm_2010/organigramm.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Organigramm</a></td>
			  			</tr>
						<tr>
			  				<td class="tdwidth10" nowrap>&nbsp;</td>
							<td class="tdwrap"><a class="Item" href="private/info/qm/prozesse/prozessmodell.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Prozesse</a></td>
				  		</tr>
						<tr>
				  			<td class="tdwidth10" nowrap>&nbsp;</td>
							<td class="tdwrap"><a class="Item" href="private/info/qm_2010/dokumente.php" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Dokumente</a></td>
			  			</tr>
				  		<tr>
				  			<td class="tdwidth10" nowrap>&nbsp;</td>
							<td class="tdwrap"><a target="_blank" class="Item" href="private/info/qm_2010/Deutsch/6_Ground_Rules/Ground_Rules_2010.pdf"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Ground Rules</a></td>
			  			</tr>
			  			<tr style="display:none;">
				  			<td class="tdwidth10" nowrap>&nbsp;</td>
							<td class="tdwrap"><a class="Item" href="private/info/qm/dokumente/beschluesse_regelungen.php?lang=" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Beschl&uuml;sse und Regelungen </a></td>
				  		</tr>
					</table>
				</td>
			</tr>
			<!--QM Language English -->				
			<tr>
  					<td class="tdwidth10" nowrap>&nbsp;</td>
				    <td class="tdwrap"><a href="private/info/qm/info.html" target="content"  class="MenuItem" onClick="return(js_toggle_container('QM_EN'));"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;English</a>
					<table class="tabcontent" id="QM_EN" style="display:none">
					
						<!-- EN QM Handbuch CRM@FHTW ist nun in Documents/Support Documents CRM@FHTW-->						
				  		<tr>
				  			<td class="tdwidth10" nowrap>&nbsp;</td>
							<td class="tdwrap"><a target="content" class="Item" href="private/info/qm_2010/English/1_QM-Handbook/QM_Handbook_UASTW_2010.pdf" ><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;QM-Handbook</a></td>
			  			</tr>
						<!--<tr>
				  			<td class="tdwidth10" nowrap>&nbsp;</td>
							<td class="tdwrap"><a target="content" class="Item" href="private/info/qm/Qualitaetsmanagementhandbuch.php?lang=en"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;CRM@FHTW</a></td>
			  			</tr>-->
						<tr>
				  			<td class="tdwidth10" nowrap>&nbsp;</td>
							<td class="tdwrap"><a class="Item" href="private/info/qm_2010/organigramm_en.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Organigram</a></td>
			  			</tr>
						<tr>
			  				<td class="tdwidth10" nowrap>&nbsp;</td>
							<td class="tdwrap"><a class="Item" href="private/info/qm/prozesse_en/prozessmodell.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Processes</a></td>
				  		</tr>
						<tr>
				  			<td class="tdwidth10" nowrap>&nbsp;</td>
							<td class="tdwrap"><a class="Item" href="private/info/qm_2010/dokumente.php?lang=en" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Documents</a></td>
			  			</tr>
				  		<tr>
				  			<td class="tdwidth10" nowrap>&nbsp;</td>
							<td class="tdwrap"><a target="_blank" class="Item" href="private/info/qm_2010/English/5_Ground Rules/Ground Rules_2010.pdf"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Ground Rules</a></td>
			  			</tr>
			  			<tr style="display:none;">
				  			<td class="tdwidth10" nowrap>&nbsp;</td>
							<td class="tdwrap"><a class="Item" href="private/info/qm/dokumente/beschluesse_regelungen.php?lang=en" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Resolutions and Regulations</a></td>
				  		</tr>
				</table>
				</td>
			</tr>
			</table>
		  	</td>
  		</tr>
		
		
		<!-- Hauptmenue -	Unternehmenskommunikation -->
		<tr>
          <td class="tdwrap"><a href="private/info/unternehmenskommunikation.html" class="MenuItem" target="content" onClick="js_toggle_container('unternehmenskommunikation');"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Unternehmenskommunikation</a></td>
  		</tr>
		<tr>
        	<td class="tdwrap">
		  	<table class="tabcontent" id="unternehmenskommunikation" style="display: none;">
		  	<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a class="Item" href="private/info/unternehmenskommunikation/logo.php" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Logo</a></td>
			</tr>
			<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a href="private/info/unternehmenskommunikation/CorporateWordingManual.php" class="Item" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Corporate Wording Manual</a></td>
			</tr>
			<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a href="private/info/unternehmenskommunikation/veranstaltungsleitfaden.php" class="Item" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Veranstaltungsleitfaden</a></td>
			</tr>
			</table>
		</td>
  		</tr>
		
		
		<!-- Hauptmenue Rektor -->
		 <!--
		<tr>
          <td class="tdwrap"><a href="public/rektorat.html" class="MenuItem" target="content" onClick="js_toggle_container('Infos');"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Rektorat</a></td>
  		</tr>
		<tr>
        	<td class="tdwrap">
		  	<table class="tabcontent" id="Infos" style="display: none;">
		  	<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a class="Item" href="public/rektortw.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Rektor FH Technikum Wien</a></td>
			</tr>
			<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a href="../documents/management/umgang_vielfalt" class="Item" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Umgang mit Vielfalt</a></td>
			</tr>

			</table>
		</td>-->
		
		<tr>
          <td class="tdwrap"><a href="public/rektorat.html" class="MenuItem" target="content" onClick="js_toggle_container('Infos');"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Rektorat</a></td>
  		</tr>
		<tr>
        	<td class="tdwrap">
		  	<table class="tabcontent" id="Infos" style="display: none;">
		  	<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a class="Item" href="http://www.technikum-wien.at/fh/leitbild/" target="_blank"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Leitbild der FHTW</a></td>
			</tr>
			<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a href="public/factsnfigures.html" class="Item" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Facts &amp; Figures</a></td>
			</tr>
			<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a href="cisdocs/Institutionelle_Evaluierung.PPT" class="Item" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Institutionelle Evaluierung</a></td>
			</tr>
			<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a href="public/frauenbeauftragte.html" class="Item" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Frauen an der FHTW</a></td>
			</tr>
			<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a href="public/hochschulprojekte.html" class="Item" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Hochschulprojekte</a></td>
			</tr>			
			<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a href="public/auszeichnungen.html" class="Item" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Auszeichnungen &amp; Preistr&auml;gerInnen</a></td>
			</tr>			
			<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a href="public/stipendien.html" class="Item" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Stipendien &amp; Awards</a></td>
			</tr>			
			</table>
		</td>
		</tr>
  		
  			<tr>
				<td class="tdwrap"><a href="public/kollegium.html" class="MenuItem" onClick="js_toggle_container('Kollegium');" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Fachhochschulkollegium</a></td>
			</tr>
			<tr>
				<td class="tdwrap">
		  		<table class="tabcontent" id="Kollegium" style="display: none;">
				<tr>
					<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="private/info/kollegiumswahl.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Kollegiumswahl 2008</a></td>
				</tr>
				<tr>
					<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="private/info/kollegiumswahl2010.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Kollegiumswahl 2010</a></td>
				</tr>
				</table>
				</td>
			</tr>
		<!-- Hauptmenue Studentenvertretung -->
	<!--	<tr>
          <td class="tdwrap"><a href="?Studentenvertretung" class="MenuItem" onClick="return(js_toggle_container('Studentenvertretung'));"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Studentenvertretung</a></td>
  		</tr>
		<tr>
        	<td class="tdwrap">
		  	<table class="tabcontent" id="Studentenvertretung" style="display: none;">
		  	<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a class="Item" href="public/rektortw.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;zur Homepage</a></td>
			</tr>
  			<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a href="public/kollegium.html" class="Item" onClick="js_toggle_container('Kollegium');" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Statuten</a></td>
			</tr>
			</table>
		</td>
  		</tr>-->
  		<!-- Hauptmenue Betriebsrat -->
 		<tr>
			<td class="tdwrap"><a href="?Betriebsrat" class="MenuItem" onClick="return(js_toggle_container('BTR'));"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Betriebsrat</a></td>
		</tr>
		<tr>
			<td class="tdwrap">
			<table class="tabcontent" id="BTR" style="display: none">
		  		<tr>
		  			<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="private/info/betriebsrat/news.php" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;News</a></td>
		  		</tr>
		  		<tr>
		  			<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="private/info/betriebsrat/info.php" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Info</a></td>
		  		</tr>
		  		<tr>
		  			<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="private/info/betriebsrat/dokumente.php" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Dokumente</a></td>
		  		</tr>
		  		<tr>
		  			<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="private/info/betriebsrat/betriebsratswahl.php" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Betriebsratswahl</a></td>
		  		</tr>
			  	</table>
			</td>
		</tr>
 		<!-- Hauptmenue FAQ -->
 		<tr>
			<td class="tdwrap"><a href="?FAQ" class="MenuItem" onClick="return(js_toggle_container('FAQ'));"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;FAQ</a></td>
		</tr>
		<tr>
			<td class="tdwrap">
				<table class="tabcontent" id="FAQ" style="display: none">
				<tr>
		  			<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="public/faq_systeminfo.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;allgem. Systeminfo</a></td>
		  		</tr>
		  		<tr>
		  			<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="public/faq_lan.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;LAN FAQ</a></td>
		  		</tr>
		  		<tr>
		  			<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="public/faq_druckinsel.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Druckinsel FAQ</a></td>
		  		</tr>
				<tr>
		  			<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="public/faq_bug.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Bug Tracking FAQ</a></td>
		  		</tr>
		  		<tr>
		  			<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="public/faq_upload.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Upload Lehre FAQ</a></td>
		  		</tr>
				<tr>
			  		<td class="tdwidth11" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="public/faq_telefonbeschreibung.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Telefon FAQ</a></td>
				</tr>				
		  		<tr>
			  		<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="public/info/mail.html" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Einrichten des Mails</a></td>
				</tr>
		  		<tr>
			  		<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="private/info/handbuecher/index.php" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Handb&uuml;cher</a></td>
				</tr>

		  		<tr>
			  		<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="public/archiv.php" target="content"><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Archiv-Links LV-Info</a></td>
				</tr>				 
				
			  	</table>
			</td>
		</tr>

		<!-- ************* Meine CIS ******************* -->
  		<tr>
			<td class="tdwrap"><a class="MenuItem" href="private/menu.php" ><img src="../skin/images/menu_item.gif" alt="menu item" width="7" height="9">&nbsp;Mein CIS</a></td>
		</tr>
	  </table>
	</td>
  </tr>
</table>