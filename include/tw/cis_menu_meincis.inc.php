<?php
	$stsemobj = new studiensemester($db_conn);
	//$stsem = $stsemobj->getAktorNext();
	$stsem = $stsemobj->getNearest();
?>
<table class="tabcontent">
<tr>
	<td width="159" class='tdvertical' nowrap>
		<table class="tabcontent">
		<tr>
          <td nowrap><a class="HyperItem" href="../index.html" target="_top">&lt;&lt; HOME</a></td>
  		</tr>
  		<tr>
			<td>&nbsp;</td>
		</tr>
		<!-- ************* Meine CIS ******************* -->
  		<tr>
			<td nowrap><a class="MenuItem" href="?MeineCIS" onClick="return(js_toggle_container('MeineCIS'));" target="content"><img src="../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Mein CIS</a></td>
		</tr>

		<tr>
	       	<td nowrap>
		  	<table class="tabcontent" id="MeineCIS" style="display: visible;">
		  	<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class='tdwrap' ><a class="Item" href="profile/index.php" target="content" <?php echo (!$aktiv?'style="color: red;"':''); ?>><img src="../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Profil</a></td>
			</tr>
			<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class='tdwrap'><a class="Item" href="https://webmail.technikum-wien.at" target="_blank"><img src="../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Webmail</a></td>
			</tr>
			<?php
			if ($is_student)
			{
					echo '<tr>
				  	<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="profile/dokumente.php" target="content"><img src="../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Dokumente</a></td>
				</tr>';
			}

			echo '
		  	<tr>
				<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a class="Item" href="lvplan/stpl_week.php" target="content"><img src="../../skin/images/menu_item.gif" width="7" height="9">&nbsp;LV-Plan</a></td>
			</tr>';

			//Projekt-Zeitaufzeichnung
			$qry = "SELECT count(*) as anzahl FROM fue.tbl_projektbenutzer WHERE uid='$user'";

			if($result = pg_query($db_conn, $qry))
			{
				if($row = pg_fetch_object($result))
				{
					if($row->anzahl>0)
					{
						echo '
					  	<tr>
							<td class="tdwidth10" nowrap>&nbsp;</td>
							<td class="tdwrap"><a class="Item" href="tools/zeitaufzeichnung.php" target="content"><img src="../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Zeitaufzeichnung</a></td>
						</tr>';
					}
				}
			}

			if ($is_student)
			{
				echo '<tr>
					  	<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="lehre/notenliste.php" target="content"><img src="../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Leistungsbeurteilung</a></td>
					  </tr>';
				echo '<tr>
					  	<td class="tdwidth10" nowrap>&nbsp;</td>
						<td class="tdwrap"><a class="Item" href="profile/zahlungen.php" target="content"><img src="../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Zahlungen</a></td>
					  </tr>';
				echo '	<tr>
					<td class="tdwidth10" nowrap>&nbsp;</td>
				    <td class="tdwrap">
				    	<a href="?Location" class="MenuItem" onClick="return(js_toggle_container(\'MeineLVs\'));">
				    		<img src="../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Meine LV
				    	</a>
				    </td>
				</tr>
				<tr>
          				<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap">
		  			<table class="tabcontent" id="MeineLVs" style="display: visible;">
					<tr>
					  	<td class="tdwrap">
							<ul style="margin-top: 0px; margin-bottom: 0px;">';

							$qry = "SELECT distinct lehrveranstaltung_id, bezeichnung, studiengang_kz, semester, lehre, lehreverzeichnis from campus.vw_student_lehrveranstaltung WHERE uid='$user' AND studiensemester_kurzbz='$stsem' AND lehre=true AND lehreverzeichnis<>'' ORDER BY studiengang_kz, semester, bezeichnung";

							if($result = pg_query($db_conn,$qry))
							{
								while($row = pg_fetch_object($result))
								{
									if($row->studiengang_kz==0 && $row->semester==0)
										echo '<li><a class="Item2" title="'.$row->bezeichnung.'" href="freifaecher/lesson.php?lvid='.$row->lehrveranstaltung_id.'" target="content">FF '.CutString($row->bezeichnung,$cutlength).'</a></li>';
									else
										echo '<li><a class="Item2" title="'.$row->bezeichnung.'" href="lehre/lesson.php?lvid='.$row->lehrveranstaltung_id.'" target="content">'.$stg[$row->studiengang_kz].$row->semester.' '.CutString($row->bezeichnung,$cutlength).'</a></li>';
								}
							}
							else
								echo "Fehler beim Auslesen der LV";
				echo '
							</ul>
						</td>
					</tr>
					</table>
		  			</td>
				</tr>';
				//Projektarbeitsabgabe
				echo '
				<tr>
					<td class="tdwidth10" nowrap>&nbsp;</td>
				    <td class="tdwrap">
				    	<a href="lehre/abgabe_student_frameset.html" class="Item" target="content">
				    		<img src="../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Bachelor- und Diplomarbeitsabgabe
				    	</a>
				    </td>
				</tr>';
			}

			//Eigene LVs des eingeloggten Lektors anzeigen
			if($is_lector)
			{
				?>
				<tr>
					<td class="tdwidth10" nowrap>&nbsp;</td>
				    <td class='tdwrap'>
				    	<a href="profile/zeitwunsch.php?uid=<?php echo $user; ?>" class="Item" target="content">
				    		<img src="../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Zeitw&uuml;nsche
				    	</a>
				    </td>
				</tr>
				<tr>
					<td class="tdwidth10" nowrap>&nbsp;</td>
				    <td class='tdwrap'>
				    	<a href="profile/urlaubstool.php" class="Item" target="content">
				    		<img src="../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Urlaubstool
				    	</a>
				    </td>
				</tr>
				<tr>
					<td class="tdwidth10" nowrap>&nbsp;</td>
				    <td class='tdwrap'>
				    	<a href="profile/zeitsperre_resturlaub.php" class="Item" target="content">
				    		<img src="../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Zeitsperre
				    	</a>
				    </td>
				</tr>
				<tr>
					<td class="tdwidth10" nowrap>&nbsp;</td>
				    <td class='tdwrap'>
				    	<a href="profile/lva_liste.php?uid=<?php echo $user; ?>" class="Item" target="content">
				    		<img src="../../skin/images/menu_item.gif" width="7" height="9">&nbsp;LV-&Uuml;bersicht
				    	</a>
				    </td>
				</tr>
				<tr>
					<td class="tdwidth10" nowrap>&nbsp;</td>
				    <td class='tdwrap'>
				    	<a href="?Location" class="MenuItem" onClick="return(js_toggle_container('MeineLVs'));">
				    		<img src="../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Meine LV
				    	</a>
				    </td>
				</tr>
				<tr>
          			<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class='tdwrap'>
		  			<table class="tabcontent" id="MeineLVs" style="display: visible;">
					<tr>
					  	<td class='tdwrap'>
							<ul style="margin-top: 0px; margin-bottom: 0px;">
							<?php
							$qry = "SELECT distinct tbl_lehrveranstaltung.bezeichnung,typ, tbl_studiengang.kurzbz, tbl_lehrveranstaltung.studiengang_kz, semester, lehreverzeichnis, tbl_lehrveranstaltung.lehrveranstaltung_id, tbl_lehrveranstaltung.orgform_kurzbz
									FROM 
										lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, 
										lehre.tbl_lehreinheitmitarbeiter, public.tbl_studiengang
								    WHERE 
								    	tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
									    tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND tbl_lehrveranstaltung.studiengang_kz=tbl_studiengang.studiengang_kz AND
									    mitarbeiter_uid='$user' AND tbl_lehreinheit.studiensemester_kurzbz='$stsem' AND
									    tbl_lehrveranstaltung.aktiv AND tbl_lehrveranstaltung.lehre ORDER BY typ, tbl_studiengang.kurzbz, semester, bezeichnung";

							if($result = pg_query($db_conn,$qry))
							{
								while($row = pg_fetch_object($result))
								{
									if($row->studiengang_kz==0 && $row->semester==0)
										echo '<li><a class="Item2" title="'.$row->bezeichnung.'" href="freifaecher/lesson.php?lvid='.$row->lehrveranstaltung_id.'" target="content">FF '.CutString($row->bezeichnung,$cutlength).'</a></li>';
									else
										echo '<li><a class="Item2" title="'.$row->bezeichnung.'" href="lehre/lesson.php?lvid='.$row->lehrveranstaltung_id.'" target="content">'.$stg[$row->studiengang_kz].$row->semester.' '.$row->orgform_kurzbz.' '.CutString($row->bezeichnung, $cutlength).'</a></li>';
								}
							}
							else
								echo "Fehler beim Auslesen des Lehrfaches";
							?>
							</ul>
						</td>
					</tr>
					</table>
		  			</td>
				</tr>
				<!--Projektarbeitsabgabe-->
				<tr>
					<td class="tdwidth10" nowrap>&nbsp;</td>
				    <td class='tdwrap'>
				    	<a href="lehre/abgabe_lektor_frameset.html" class="Item" target="content">
				    		<img src="../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Bachelor- und Diplomarbeitsabgabe
				    	</a>
				    </td>
				</tr>
			<?php
			}
			
			if ($rechte->isFix())
			{
				?>
				<tr>
					<td class="tdwidth10" nowrap>&nbsp;</td>
				    <td class='tdwrap'>
				    	<a href="profile/zeitsperre_days.php?days=12" target="content" class="MenuItem" onClick="js_toggle_container('Zeitsperren');">
				    		<img src="../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Zeitsperren
				    	</a>
				    </td>
				</tr>
				<tr>
          					<td class="tdwidth10" nowrap>&nbsp;</td>
					<td nowrap>
		  			<table class="tabcontent" id="Zeitsperren" style="display: none;">
					<tr>
					  	<td class='tdwrap'>
							<ul style="margin-top: 0px; margin-bottom: 0px;">
							<?php
							if ($rechte->isBerechtigt('admin',0) || $rechte->isBerechtigt('mitarbeiter'))
								echo '<li><a class="Item2" href="profile/resturlaub.php" target="content">Resturlaub</a></li>';
							if ($rechte->isBerechtigt('admin',0) || $rechte->isBerechtigt('mitarbeiter') || $fkt->checkFunktion('stglstv')|| $fkt->checkFunktion('stgl') || $fkt->checkFunktion('ass'))
							{
								echo '<li><a class="Item2" href="profile/zeitsperre.php?fix=true" target="content">Fix-Angestellte</a></li>';
								echo '<li><a class="Item2" href="profile/zeitsperre.php?fix=true&lektor=true" target="content">Fixe Lektoren</a></li>';
								echo '<li><a class="Item2" href="profile/zeitsperre.php?institut=" target="content">Institut</a></li>';
							}
							$stge=$rechte->getStgKz('admin');
							foreach($stg_obj->result as $row)
								if (in_array($row->studiengang_kz,$stge))
									echo '<li><a class="Item2" href="profile/zeitsperre.php?funktion=lkt&stg_kz='.$row->studiengang_kz.'" target="content">Lektoren '.$row->kurzbzlang.'</a></li>';
							?>
							</ul>
						</td>
					</tr>
					</table>
		  			</td>
				</tr>

			<?php
				//URLAUBE
				//Untergebene holen
				$qry = "SELECT * FROM public.tbl_benutzerfunktion WHERE (funktion_kurzbz='fbl' OR funktion_kurzbz='stgl') AND uid='".addslashes($user)."'";

				if($result = pg_query($db_conn, $qry))
				{
					$institut='';
					$stge='';
					while($row = pg_fetch_object($result))
					{
						if($row->funktion_kurzbz=='fbl')
						{
							if($institut!='')
								$institut.=',';

							$institut.="'".addslashes($row->fachbereich_kurzbz)."'";
						}
						elseif($row->funktion_kurzbz=='stgl')
						{
							if($stge!='')
								$stge.=',';
							$stge.="'".$row->studiengang_kz."'";
						}

					}
				}

				$qry = "SELECT distinct uid FROM public.tbl_benutzerfunktion WHERE funktion_kurzbz='oezuordnung' AND (false ";

				if($institut!='')
					$qry.=" OR fachbereich_kurzbz in($institut)";
				if($stge!='')
					$qry.=" OR studiengang_kz in($stge)";

				$qry.=")";

				$untergebene='';
				if($result = pg_query($db_conn, $qry))
				{


					while($row = pg_fetch_object($result))
					{
						if($untergebene!='')
							$untergebene.=',';
						$untergebene.="'".addslashes($row->uid)."'";
					}
				}

				if($untergebene!='')
				{
					$qry = "SELECT * FROM public.tbl_person JOIN public.tbl_benutzer USING(person_id) WHERE uid in($untergebene) ORDER BY nachname, vorname";

					if($result = pg_query($db_conn, $qry))
					{
						echo '
						<tr>
							<td class="tdwidth10" nowrap>&nbsp;</td>
						    <td class="tdwrap">
						    	<a href="profile/urlaubsfreigabe.php" target="content" class="MenuItem" onClick="js_toggle_container(\'urlaub\');">
						    		<img src="../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Urlaube
						    	</a>
						    </td>
						</tr>
						<tr>
		          			<td class="tdwidth10" nowrap>&nbsp;</td>
							<td nowrap>
				  			<table class="tabcontent" id="urlaub" style="display: none;">
							<tr>
							  	<td class="tdwrap">
									<ul style="margin-top: 0px; margin-bottom: 0px;">';
						echo '<li><a class="Item2" href="profile/urlaubsfreigabe.php" target="content">Alle</a></li>';

						while($row = pg_fetch_object($result))
						{
								echo '<li><a class="Item2" href="profile/urlaubsfreigabe.php?uid='.$row->uid.'" target="content">'."$row->nachname $row->vorname $row->titelpre $row->titelpost".'</a></li>';
						}
						echo '</ul>
								</td>
							</tr>
							</table>
				  			</td>
						</tr>';
					}

				}
			}
			?>
			</table>
			</td>
  		</tr>
	  </table>
	</td>

  </tr>
</table>