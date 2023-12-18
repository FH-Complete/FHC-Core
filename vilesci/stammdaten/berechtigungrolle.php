<?php
/* Copyright (C) 2009 Technikum-Wien
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
 * Authors: Christian Paminger 		< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 			< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/berechtigung.class.php');
require_once('../../include/benutzerberechtigung.class.php');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('basis/berechtigung'))
	die('Sie habe keine Rechte um diese Seite anzuzeigen');

$rolle_kurzbz = filter_input(INPUT_GET, 'rolle_kurzbz');
$delete = filter_input(INPUT_GET, 'delete', FILTER_VALIDATE_BOOLEAN);
$copy = filter_input(INPUT_POST, 'copy');
$vergleich = filter_input(INPUT_GET, 'vergleich');
?>
<html>
	<head>
		<title>Rollen Uebersicht</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
		<link href="../../skin/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css">
		<?php
		include('../../include/meta/jquery.php');
		include('../../include/meta/jquery-tablesorter.php');
		?>
		<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
		<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
		<script language="Javascript">
			$(document).ready(function()
			{
				$("#t1").tablesorter(
				{
					sortList: [[0,0]],
					widgets: ["zebra", "filter", "stickyHeaders"],
					headers: {3:{filter:false, sorter:false}},
					widgetOptions : {filter_saveFilters : true}
				});

				$("#t2").tablesorter(
				{
					sortList: [[0,0]],
					widgets: ["zebra", "filter", "stickyHeaders"],
					headers: {2:{filter:false, sorter:false}},
					widgetOptions : {filter_saveFilters : true}
				});
				$("#t3").tablesorter(
				{
					sortList: [],
					widgets: ["zebra"]
				});
				$("#t4").tablesorter(
				{
					sortList: [],
					widgets: ["zebra"]
				});
				$('.resetsaved').click(function()
				{
					$(".tablesorter").trigger("filterReset");
					window.location("<?php echo $_SERVER['PHP_SELF'] ?>");
					return false;
				});
				$("textarea").keyup(function()
				{
					$(this).siblings("span").text((256 - $(this).val().length));
				});

				// Breite des Autocompletes korrigieren um das Springen zu verhindern
				$.extend($.ui.autocomplete.prototype.options, {
					open: function(event, ui) {
						$(this).autocomplete("widget").css({
							"width": ($(".ui-menu-item").width()+ 20 + "px"),
							"padding-left": "5px"
						});
					}
				});

				$(".berechtigung_autocomplete").autocomplete({
					source: "benutzerberechtigung_autocomplete.php?autocomplete=berechtigung",
					minLength:2,
					response: function(event, ui)
					{
						//Value und Label fuer die Anzeige setzen
						for(i in ui.content)
						{
							ui.content[i].value=ui.content[i].berechtigung_kurzbz;
							ui.content[i].label=ui.content[i].berechtigung_kurzbz+" - "+ui.content[i].beschreibung;
						}
					},
					select: function(event, ui)
					{
						//Ausgewaehlte Ressource zuweisen und Textfeld wieder leeren
						$(this).val(ui.item.berechtigung_kurzbz);
					}
				});

				$(".copyButton").click(function(event)
				{
					event.preventDefault();
					$(this).siblings().show();
					$(this).hide();
				});
			});

			function validateNewData()
			{
				if($('#berechtigung_neu_autocomplete').val() == '')
				{
					alert('Berechtigung darf nicht leer sein')
					return false;
				}
				else if ($('#art_neu').val() == '')
				{
					alert('Art darf nicht leer sein')
					return false;
				}
				else if ($('#art_neu').val() != '')
				{
					var eingabe, c, erlaubt = 'suid', laenge;
					eingabe = $('#art_neu').val();
					eingabe = eingabe.toLowerCase();
					laenge = eingabe.length;
					for (c = 0; c < laenge; c++)
					{
						d = eingabe.charAt(c);
						if (erlaubt.indexOf(d) == -1)
						{
							alert ('Erlaubte Werte für Art sind s,u,i,d');
							return false;
						}
					}
				}
				else
					return true;
			}
		</script>
	</head>

	<body class="background_main">


	<?php
	if(isset($rolle_kurzbz))
	{
		echo '<h2>Berechtigungen der Rolle "'.$rolle_kurzbz.'"</h2>';
		$berechtigung_kurzbz = filter_input(INPUT_GET, 'berechtigung_kurzbz');
		$art = filter_input(INPUT_GET, 'art');
		$save = filter_input(INPUT_GET, 'save');
		$anmerkung = filter_input(INPUT_GET, 'anmerkung');

		if(isset($save))
		{
			if($rolle_kurzbz && $berechtigung_kurzbz && $art)
			{
				$berechtigung = new berechtigung();
				$berechtigung->rolle_kurzbz = $rolle_kurzbz;
				$berechtigung->berechtigung_kurzbz = $berechtigung_kurzbz;
				$berechtigung->art = $art;
				$berechtigung->anmerkung = $anmerkung;
				$berechtigung->insertamum = date('Y-m-d H:i:s');
				$berechtigung->insertvon = $user;

				if($berechtigung->saveRolleBerechtigung()): ?>
					<b>Zuteilung gespeichert</b>
				<?php
					$berechtigung_kurzbz = '';
					$art = 'suid';
					$anmerkung = '';
				else: ?>
					<b>Fehler beim Speichern der Zuteilung: <?php echo $berechtigung->errormsg ?>
				<?php endif;
			}
		}

		if(isset($delete))
		{
			$berechtigung = new berechtigung();
			if(!$berechtigung->deleteRolleBerechtigung($rolle_kurzbz, $berechtigung_kurzbz)): ?>
				<b>Fehler beim Löschen: </b><?php echo $berechtigung->errormsg ?>
			<?php else: ?>
				<b>Berechtigung <?php echo $berechtigung_kurzbz.' mit '.$art ?> gelöscht!</b>
			<?php endif;
		} ?>

		<a href="<?php echo basename(__FILE__) ?>">
			Zurück zur Rollen Übersicht
		</a>
		<br><br>
		<?php
		$berechtigung = new berechtigung();
		$berechtigung->getBerechtigungen();
		?>
		<form action="<?php echo basename(__FILE__) ?>" method="GET">
			<div style="vertical-align: top">
			<input type="text" 
					value="<?php echo $berechtigung_kurzbz ?>"
					placeholder="Berechtigung" 
					id="berechtigung_neu_autocomplete" 
					class="berechtigung_autocomplete" 
					name="berechtigung_kurzbz" 
					style="width: 300px">
			<input type="hidden" name="rolle_kurzbz" value="<?php echo $rolle_kurzbz ?>">
			<input type="text" 
					id="art_neu" 
					value="<?php echo ($art != '' ? $art : 'suid') ?>"
					size="4" 
					name="art">
			<textarea type="text"
					placeholder="Anmerkung"
					id="anmerkung_neu"
					rows="2"
					cols="50"
					size="200"
					maxlength="256"
					name="anmerkung"
					style="vertical-align: top; font-family: inherit; font-size: small;"><?php echo $anmerkung ?></textarea>
			<input type="submit" name="save" value="Hinzufügen" onclick="return validateNewData()">
			</div>
		</form>
		<button type="button" class="resetsaved" title="Reset Filter">Reset Filter</button>
		<table id="t1" class="tablesorter">
			<thead>
				<tr>
					<th>Kurzbz</th>
					<th>Art</th>
					<th>Beschreibung</th>
					<th>Anmerkung</th>
					<th colspan="2"></th>
				</tr>
			</thead>
			<tbody>
		<?php
		$berechtigungen = new berechtigung();
		$berechtigungen->getRolleBerechtigung($rolle_kurzbz);

		foreach($berechtigungen->result as $rolle): ?>
				<tr>
					<td><?php echo $rolle->berechtigung_kurzbz ?></td>
					<td><?php echo $rolle->art ?></td>
					<td><?php echo $rolle->beschreibung ?></td>
					<td><?php echo $rolle->anmerkung ?></td>
					<td>
						<a href="<?php echo basename(__FILE__) ?>?rolle_kurzbz=<?php echo $rolle->rolle_kurzbz ?>&berechtigung_kurzbz=<?php echo $rolle->berechtigung_kurzbz ?>&art=<?php echo $rolle->art ?>&anmerkung=<?php echo $rolle->anmerkung ?>">
							Bearbeiten
						</a>
					</td>
					<td>
						<a href="<?php echo basename(__FILE__) ?>?delete=1&rolle_kurzbz=<?php echo $rolle->rolle_kurzbz ?>&berechtigung_kurzbz=<?php echo $rolle->berechtigung_kurzbz ?>&art=<?php echo $rolle->art ?>&anmerkung=<?php echo $rolle->anmerkung ?>">
							Recht entfernen
						</a>
					</td>
				</tr>
		<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}
	elseif(isset($vergleich))
	{
		echo '<h2>Rollen vergleichen</h2>';
		$rolle1 = filter_input(INPUT_GET, 'rolle_kurzbz1');
		$rolle2 = filter_input(INPUT_GET, 'rolle_kurzbz2');
		 ?>

		<a href="<?php echo basename(__FILE__) ?>">
			Zurück zur Rollen Übersicht
		</a>
		<br><br>

		<?php
		$rollen1Arr = array();
		$rollen2Arr = array();
		$rollenGesamt = array();
		$rollen = new berechtigung();
		$rollen->getRolleBerechtigung($rolle1);
		foreach ($rollen->result AS $recht)
		{
			$rollen1Arr[$recht->berechtigung_kurzbz] = $recht->art;
		}
		$rollen = new berechtigung();
		$rollen->getRolleBerechtigung($rolle2);
		foreach ($rollen->result AS $recht)
		{
			$rollen2Arr[$recht->berechtigung_kurzbz] = $recht->art;
		}
		$rollenGesamt = array_merge($rollen1Arr,$rollen2Arr);
		ksort($rollenGesamt);

		echo '	<form action="'.basename(__FILE__).'?vergleich=vergleich" method="GET" style="width: 60%">
					<div style="width: 100%">
						<div style="width: 50%; float: left;">
							Rolle 1:
							<select id="rolle_kurzbz" name="rolle_kurzbz1">
								<option value="">Bitte auswählen</option>';
							$rollen = new berechtigung();
							$rollen->getRollen('rolle_kurzbz');
							foreach($rollen->result as $rolle)
							{
								if ($rolle1 == $rolle->rolle_kurzbz)
									$selected = 'selected="selected"';
								else
									$selected = '';

								echo '<option value="'.$rolle->rolle_kurzbz.'"  title="'.$rolle->beschreibung.'" '.$selected.'>'.$rolle->rolle_kurzbz.'</option>';
							}
							echo '</select>';
							if (isset($rolle1))
							{
								echo '	<table id="t3" class="tablesorter">
											<thead>
											<tr>
												<th>Kurzbz</th>
												<th>Art</th>
											</tr>
											</thead>
											<tbody>';

											foreach ($rollenGesamt AS $recht => $art)
											{
												if (array_key_exists($recht, $rollen1Arr))
												{
													if ($art != $rollen1Arr[$recht])
													{
														echo '	<tr>
																	<td style="border: 1px solid transparent">'.$recht.'</td>
																	<td style="border: 1px solid black">'.$rollen1Arr[$recht].'</td>
																</tr>';
													}
													else
													{
														echo '	<tr>
																	<td style="border: 1px solid transparent">'.$recht.'</td>
																	<td style="border: 1px solid transparent">'.$art.'</td>
																</tr>';
													}

												}
												else
												{
													echo '	<tr>
																<td style="border: 1px solid black; border-right: 0">&nbsp;</td>
																<td style="border: 1px solid black; border-left: 0">&nbsp;</td>
															</tr>';
												}
											}
											echo '
											</tbody>
										</table>';
							}
						echo '
						</div>
						<div style="width: 50%; float: left;">
							Rolle 2:
							<select id="rolle_kurzbz" name="rolle_kurzbz2">
								<option value="">Bitte auswählen</option>';
							$rollen = new berechtigung();
							$rollen->getRollen('rolle_kurzbz');
							foreach($rollen->result as $rolle)
							{
								if ($rolle2 == $rolle->rolle_kurzbz)
									$selected = 'selected="selected"';
								else
									$selected = '';

								echo '<option value="'.$rolle->rolle_kurzbz.'"  title="'.$rolle->beschreibung.'" '.$selected.'>'.$rolle->rolle_kurzbz.'</option>';
							}
							echo '</select>';
							echo '<input style="margin-left: 20px" type="submit" name="vergleich" value="Vergleichen">';
							if (isset($rolle2))
							{
								echo '	<table id="t4" class="tablesorter">
											<thead>
											<tr>
												<th>Kurzbz</th>
												<th>Art</th>
											</tr>
											</thead>
											<tbody>';
								foreach ($rollenGesamt AS $recht => $art)
								{
									if (array_key_exists($recht, $rollen2Arr))
									{
										if ($art != $rollen2Arr[$recht])
										{
											echo '	<tr>
														<td style="border: 1px solid transparent">'.$recht.'</td>
														<td style="border: 1px solid black">'.$rollen2Arr[$recht].'</td>
													</tr>';
										}
										else
										{
											echo '	<tr>
														<td style="border: 1px solid transparent">'.$recht.'</td>
														<td style="border: 1px solid transparent">'.$art.'</td>
													</tr>';
										}
									}
									else
									{
										echo '	<tr>
													<td style="border: 1px solid black; border-right: 0">&nbsp;</td>
													<td style="border: 1px solid black; border-left: 0">&nbsp;</td>
												</tr>';
									}
								}
											echo '
											</tbody>
										</table>';
							}
						echo '
						</div>						
					</div>
				</form>';
	}
	else
	{
		echo '<h2>Rollen Übersicht</h2>';
		$save = filter_input(INPUT_POST, 'save');
		$edit = filter_input(INPUT_POST, 'edit');

		if(isset($save))
		{
			$kurzbz = filter_input(INPUT_POST, 'kurzbz');
			$beschreibung = filter_input(INPUT_POST, 'beschreibung');

			if(isset($kurzbz) && isset($beschreibung))
			{
				$berechtigung = new berechtigung();
				$berechtigung->rolle_kurzbz = $kurzbz;
				$berechtigung->beschreibung = $beschreibung;
				$berechtigung->new = true;

				if($berechtigung->saveRolle())
				{
					echo 'Daten wurden gespeichert';
				}
				else
				{
					echo 'Fehler beim Speichern:'.$berechtigung->errormsg;
				}
			}
			else
			{
				echo 'Zum Speichern der Daten muss die kurzbz und die Beschreibung angegeben werden';
			}
		}

		$kurzbz = filter_input(INPUT_GET, 'kurzbz');

		if(isset($delete) && isset($kurzbz))
		{
			$berechtigung = new berechtigung();
			if($berechtigung->deleteRolle($kurzbz))
				echo 'Die Rolle '.$kurzbz.' wurde erfolgreich gelöscht';
			else
				echo 'Fehler beim Löschen:'.$berechtigung->errormsg;
		}

		if(isset($copy))
		{
			$kurzbz = filter_input(INPUT_POST, 'kurzbz');
			$copyName = filter_input(INPUT_POST, 'copy_name');
			$beschreibung = filter_input(INPUT_POST, 'beschreibung');

			if(isset($kurzbz))
			{
				$berechtigung = new berechtigung();
				$berechtigung->rolle_kurzbz = $copyName;
				$berechtigung->beschreibung = $beschreibung;
				$berechtigung->new = true;

				if($berechtigung->saveRolle())
				{
					$rollenrechte = new berechtigung();
					$rollenrechte->getRolleBerechtigung($kurzbz);
					foreach($rollenrechte->result as $rollenrecht)
					{
						$newRolleRecht = new berechtigung();
						$newRolleRecht->rolle_kurzbz = $copyName;
						$newRolleRecht->berechtigung_kurzbz = $rollenrecht->berechtigung_kurzbz;
						$newRolleRecht->art = $rollenrecht->art;
						$newRolleRecht->anmerkung = $rollenrecht->anmerkung;
						$newRolleRecht->insertamum = date('Y-m-d H:i:s');
						$newRolleRecht->insertvon = $user;
						if(!$newRolleRecht->saveRolleBerechtigung())
						{
							echo 'Fehler beim Speichern des Rechts '.$rollenrecht->berechtigung_kurzbz.' zur Rolle '.$rollenrecht->rolle_kurzbz;
							break;
						}
					}
					echo 'Rolle erfolgreich kopiert';
				}
				else
				{
					echo 'Fehler beim kopieren der Rolle '.$kurzbz.':'.$berechtigung->errormsg;
				}
			}
			else
			{
				echo 'Zum Speichern der Daten muss die kurzbz und die Beschreibung angegeben werden';
			}
		}

		if(isset($edit))
		{
			$beschreibung = filter_input(INPUT_POST, 'beschreibung');

			$berechtigung = new berechtigung();
			$berechtigung->rolle_kurzbz = $kurzbz;
			$berechtigung->beschreibung = $beschreibung;
			$berechtigung->saveRolle(false);
		}

		//Tabelle mit Rollen anzeigen
		$berechtigung = new berechtigung();
		$berechtigung->getRollen(); ?>

		<p style="text-align: right">
			<a href="<?php echo basename(__FILE__) ?>?vergleich=vergleich">
				Rollen vergleichen
			</a>
		</p>
		<button type="button" class="resetsaved" title="Reset Filter">Reset Filter</button>
		<table id="t2" class="tablesorter">
			<thead>
				<tr>
					<th>Kurzbz</th>
					<th>Beschreibung</th>
					<th colspan="3">Aktion</th>
				</tr>
			</thead>
			<tbody>

			<?php
			$edit = filter_input(INPUT_GET, 'edit');
			$kurzbz = filter_input(INPUT_GET, 'kurzbz');
			foreach($berechtigung->result as $rolle):
				if($edit && $rolle->rolle_kurzbz == $kurzbz)
				{
					$rolle_edit = $rolle;
				}
				?>
				<tr>
					<td>
						<a href="<?php echo basename(__FILE__) ?>?kurzbz=<?php echo $rolle->rolle_kurzbz ?>&edit=1">
							<?php echo $rolle->rolle_kurzbz ?>
						</a>
					</td>
					<td><?php echo $rolle->beschreibung ?></td>
					<td>
						<a href="<?php echo basename(__FILE__) ?>?rolle_kurzbz=<?php echo $rolle->rolle_kurzbz ?>">
							Berechtigungen zuordnen
						</a>
					</td>
					<td>
						<form method="POST" style="display: none">
							<input type="text" placeholder="Name der Kopie" maxlength="32" size="35" name="copy_name" value=""/>
							<input type="hidden" name="kurzbz" value="<?php echo $rolle->rolle_kurzbz ?>"/>
							<input type="hidden" name="beschreibung" value="<?php echo $rolle->beschreibung ?>"/>
							<input type="submit" name="copy" value="Jetzt kopieren" />
						</form>
						<a class="copyButton" href="">
							Rolle kopieren
						</a>
					</td>
					<td>
						<a href="<?php echo basename(__FILE__) ?>?kurzbz=<?php echo $rolle->rolle_kurzbz ?>&delete=1" onclick="return confirm('Achtung! Das Löschen einer Rolle löscht auch alle Zuordnungen dieser Rolle zu BenutzerInnen.\n\nWollen Sie die Rolle <?php echo $rolle->rolle_kurzbz ?> wirklich löschen?');">
							Rolle löschen
						</a>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<br><div style="vertical-align: top">
			<?php
			if($edit):
				?>
				<form method="POST">
					Kurzbz: <input type="text" maxlength="32" size="35" name="kurzbz" value="<?php echo $rolle_edit->rolle_kurzbz ?>" disabled />
					Beschreibung: <textarea style="vertical-align: top; font-family: inherit; font-size: small;" cols="50" rows="3" type="text" maxlength="256" size="200" name="beschreibung" value="" /><?php echo $rolle_edit->beschreibung ?></textarea>
					<span style="color: grey; display: inline-block; width: 30px;"><?php echo (256 - strlen($rolle_edit->beschreibung)) ?></span>
					&nbsp;<input type="submit" name="edit" value="Speichern" />
				</form>
		<a href="<?php echo basename(__FILE__) ?>">Neue Rolle anlegen</a>
			<?php else: ?>
				<form method="POST">
					Kurzbz: <input type="text" maxlength="32" size="35" name="kurzbz" value="" />
					Beschreibung: <textarea style="vertical-align: top; font-family: inherit; font-size: small;" cols="50" rows="3" type="text" maxlength="256" size="200" name="beschreibung" value="" /></textarea>
					<span style="color: grey; display: inline-block; width: 30px;" id="countdown_textarea_new">256</span>
					&nbsp;<input type="submit" name="save" value="Anlegen" />
				</form>
			<?php endif; ?>
	<?php } ?>
			</div>
	</body>
</html>
