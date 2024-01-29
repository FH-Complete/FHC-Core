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
 * Authors:
 */

// Requirements here and there
require_once("../../config/vilesci.config.inc.php");
require_once("../../include/functions.inc.php");
require_once("../../include/benutzerberechtigung.class.php");
require_once("../../include/datum.class.php");

require_once("../../include/studiengang.class.php");
require_once("../../include/studiensemester.class.php");
require_once("../../include/studienjahr.class.php");
require_once("../../include/student.class.php");
require_once("../../include/konto.class.php");

// Get the uid of the logged user
$user = get_uid();
// Check rights
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
if (!$rechte->isBerechtigt("student/stammdaten", null, "suid"))
{
	die("Sie haben keine Berechtigung für diese Seite");
}

// Gets all studiensemester
$studiensemester = new studiensemester();
if ($studiensemester->getAll("desc") === false)
{
	die("Error: " . $studiensemester->errormsg);
}

// Gets current studiensemester
if (($currentStudiensemester = $studiensemester->getakt()) === false)
{
	die("Error: " . $studiensemester->errormsg);
}

// Gets all activ studiengang
$studiengang = new studiengang();
if ($studiengang->getAll("kurzbzlang", true) === false)
{
	die("Error: " . $studiengang->errormsg);
}

// Variables declaration
$logArray = array(); // Array for output messages
$errorOccurred = false; // Error flag
$dataPosted = false; // Post data flag
$postStudiensemester = null;
$postStudiengang = null;
$fileName = null;
$fileTmpName = null;
$fileMimeType = null;

// Constants
$L_CSV_N_COLS = 6; // Number of columns of the CSV file
$L_ERROR = "Error";
$L_WARNING = "Warning";
$L_INFO = "Info";
$L_LN_NOT_AVAILABLE = "N/A";

/**
 * Checks if the student is valid for that studiengang
 * and checks if the studiengang present in the file is
 * the same which was choose in the interface
 */
function lChkStudiengang($studiengang, $postStudiengang, $rowStudiengang, $studentStudiengang)
{
	$chkStudiengang = false;

	foreach($studiengang->result as $val)
	{
		if ($val->studiengang_kz == $postStudiengang && $val->kurzbzlang == $rowStudiengang)
		{
			$chkStudiengang = true;
			break;
		}
	}

	return $chkStudiengang && $studentStudiengang == $postStudiengang;
}

/**
 * Create an object of type konto and fill it with data
 */
function lCredit($student, $postStudiensemester, $rowAmount, $rowDate, $Studienjahr)
{
	$user = get_uid();
	// To format a date
	$datum = new datum();
	// To work on table tbl_konto
	$konto = new konto();
	// Copying data
	$konto->person_id = $student->person_id;
	$konto->studiengang_kz = $student->studiengang_kz;
	$konto->studiensemester_kurzbz = $postStudiensemester;
	$konto->betrag = $rowAmount;
	$konto->buchungstyp_kurzbz = "Leistungsstipendium";
	$konto->mahnspanne = 0;
	$konto->buchungsdatum = $datum->formatDatum($rowDate);
	$konto->buchungstext = "Leistungsstipendium für Studienjahr ".$Studienjahr;
	$konto->insertamum = date('Y-m-d H:i:s');
	$konto->insertvon = $user;

	return $konto;
}

/**
 * Set a negative amount for a konto
 */
function lDebit(&$konto)
{
	// Loading konto data by zahlungsreferenz
	$konto->loadFromZahlungsreferenz($konto->zahlungsreferenz);
	// Add reference to parent record
	$konto->buchungsnr_verweis = $konto->buchungsnr;
	// Change betrag sign
	$konto->betrag *= -1;
	// No zahlungsreferenz needed
	$konto->zahlungsreferenz = null;
}

/**
 * Add an entry in $logArray
 */
function lAddToLogArray($code, $lineNumber, $msg)
{
	global $logArray, $errorOccurred, $L_ERROR;

	if ($code == $L_ERROR)
	{
		$errorOccurred = true;
	}

	$log = new stdClass();
	$log->code = $code;
	$log->lineNumber = $lineNumber;
	$log->msg = $msg;

	array_push($logArray, $log);
}

function checkStipExists($uid, $stsem)
{
	$checkkonto = new konto();
	if ($checkkonto->checkLeistungsstipendium($uid, $stsem))
		return true;
	else
		return false;
}

// If data has been posted
if (isset($_POST["submit"]))
{
	$dataPosted = true;

	// If studiensemester and/or studiengang have not been posted
	if (!$errorOccurred && (empty($_POST["studiensemester"]) || !is_numeric($_POST["studiengang"])))
	{
		lAddToLogArray($L_ERROR, $L_LN_NOT_AVAILABLE, "No studiensemester or studiengang have been posted");
	}
	else // else save them
	{
		$postStudiensemester = $_POST["studiensemester"];
		$postStudiengang = $_POST["studiengang"];
	}

	// Checks if a file was uploaded
	if (!$errorOccurred && (!isset($_FILES) || !is_array($_FILES) || count($_FILES) == 0))
	{
		lAddToLogArray($L_ERROR, $L_LN_NOT_AVAILABLE, "No files have been uploaded");
	}

	// If the file is not present or it was not correctly uploaded
	if (!$errorOccurred && (!isset($_FILES["csvFile"]) || $_FILES["csvFile"]["error"] != 0))
	{
		lAddToLogArray($L_ERROR, $L_LN_NOT_AVAILABLE, "An error has occurred while uploading the CSV file");
	}
	else // else save file attributes
	{
		$fileName = $_FILES["csvFile"]["name"];
		$fileTmpName = $_FILES["csvFile"]["tmp_name"];
		$fileMimeType = mime_content_type($_FILES["csvFile"]["tmp_name"]);
	}

	// Checks the file mime type
	if (!$errorOccurred && ($fileMimeType != "text/plain"))
	{
		lAddToLogArray($L_ERROR, $L_LN_NOT_AVAILABLE, "The mime type of the uploaded file is not of the type text/plain");
	}

	// Opens the file in read mode
	if (!$errorOccurred && (($fileHandle = fopen($fileTmpName, "r")) === false))
	{
		lAddToLogArray($L_ERROR, $L_LN_NOT_AVAILABLE, "An error has occurred while opening the uploaded file on read mode");
	}
}
else // else no data has been posted
{
	$dataPosted = false;
}

// If everything is ok and data has been posted
if (!$errorOccurred && $dataPosted)
{
	$student = new student(); // Object that represents a student
	$fileRow = false; // Contains a single file row
	$lineNumber = 0; // lines number counter

	// Gets the previous studiensemester to the posted one
	if (($previousStudiensemester = $studiensemester->getPreviousFrom($postStudiensemester)) === false)
	{
		die("Error: " . $studiensemester->errormsg);
	}

	// Loops on file rows
	do
	{
		$lineNumber++;
		// Gets and parses a single row of the given file
		$fileRow = fgetcsv($fileHandle, 9999, ";", "\"");
		// If everything is ok
		if ($fileRow != null && $fileRow !== false)
		{
			// Checks if the row has the right amount of columns
			if (is_array($fileRow) && count($fileRow) == $L_CSV_N_COLS)
			{
				// Checks if character encoding is UTF-8
				if (mb_detect_encoding(implode(";", $fileRow), "UTF-8", true))
				{
					$rowName = $fileRow[0];
					$rowSurname = $fileRow[1];
					$rowCode = $fileRow[2]; // uid or matrikelnr
					$rowStudiengang = $fileRow[3];
					$rowAmount = $fileRow[4];
					$rowDate = $fileRow[5];

					// If this row is not the header
					if (strtolower($rowName) != "nachname")
					{
						// If $rowCode is a matrikelnr gets the uid
						if (($uid = $student->getUidFromMatrikelnummer($rowCode)) === false)
						{
							// Otherwise $rowCode is already a uid
							$uid = $rowCode;
							$student->errormsg = ""; // Clean errors messages
						}
						// Looking for a person by uid that is valid for the posted studiensemester
						// or for the previous one
						if ($student->load($uid, $postStudiensemester) === true
						 || $student->load($uid, $previousStudiensemester) === true)
						{
							// If the student is valid for that studiengang
							// and checks if the studiengang present in the file is
							// the same which was choose in the interface
							if (lChkStudiengang($studiengang, $postStudiengang, $rowStudiengang, $student->studiengang_kz) === true)
							{
								if (checkStipExists($uid, $postStudiensemester))
								{
									lAddToLogArray(
										$L_WARNING,
										$lineNumber,
										"This file row has been discarted because an entry exists in DB"
									);
								}
								else
								{
									$stsem = new studiensemester($postStudiensemester);
									$stjahr = new studienjahr();
									if (!$vorjahr = $stjahr->jump($stsem->studienjahr_kurzbz, -1))
										$vorjahr = 'Vorjahr';

									// Create an object of type konto and fill it with data
									$konto = lCredit($student, $postStudiensemester, $rowAmount, $rowDate, $vorjahr);
									// Inserting positive amount
									if ($konto->save(true) === true)
									{
										lDebit($konto); // Negative amount
										if ($konto->save(true) === true) // Inserting negative amount
										{
											lAddToLogArray(
												$L_INFO,
												$lineNumber,
												"Added!!!"
											);
										}
										else
										{
											lAddToLogArray(
												$L_WARNING,
												$lineNumber,
												"This file row has been discarted because an error has occurred while inserting in DB"
											);
										}
									}
									else
									{
										lAddToLogArray(
											$L_WARNING,
											$lineNumber,
											"This file row has been discarted because an error has occurred while inserting in DB"
										);
									}
								}

							}
							else
							{
								lAddToLogArray(
									$L_WARNING,
									$lineNumber,
									"This file row has been discarted because this person with this studiengang is not present in DB"
								);
							}
						}
						else
						{
							lAddToLogArray(
								$L_WARNING,
								$lineNumber,
								"This file row has been discarted because this person with this studiensemester is not present in DB"
							);
						}
					}
					else
					{
						lAddToLogArray($L_WARNING, $lineNumber, "This file row has been discarted because it is the header");
					}
				}
				else
				{
					lAddToLogArray($L_WARNING, $lineNumber, "This file row has been discarted because of invalid characters");
				}
			}
			else
			{
				lAddToLogArray(
					$L_WARNING,
					$lineNumber,
					"This file row has been discarted because it isn't well formatted and/or it hasn't " . $L_CSV_N_COLS . " columns"
				);
			}
		}
		else
		{
			// If it is not the end of the file, another error has occurred
			if (!feof($fileHandle))
			{
				lAddToLogArray($L_ERROR, $lineNumber, "An error has occurred while parsing this row, procedure terminated");
			}
		}
	}
	while($fileRow);

	// Close the file handler
	fclose($fileHandle);
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">
	</head>
	<body>

		<form name="saveLeistungsstipendium" method="post" enctype="multipart/form-data" action="">
			<table border=0>
				<tr><td valign="top">CMS-Format</td>
					<td></td>
					<td valign="top">
						Zeichensatz: UTF-8<br>
						Feldtrenner: ;<br>
						Texttrenner: "<br>
						Felder:<br>
						<pre>Nachname;Vorname;UID/PersKZ;Studiengang;Betrag;Überweisungsdatum
Dylan;Bob;1234567;MEE;750;03.10.2016</pre>
					</td>
				</tr>
				<tr>
					<th width="30%">
					<th width="3%">
					<th width="67%">
				</tr>
				<tr>
					<td>
						Studiensemester:
					</td>
					<td>&nbsp;</td>
					<td>
						<select name="studiensemester">
						<?php
							$selected = "";
							// Fills the select element with all the loaded studiensemester
							foreach($studiensemester->studiensemester as $val)
							{
								// If it is the current studiensemester then selects it
								if ($val->studiensemester_kurzbz == $currentStudiensemester)
								{
									$selected = "selected";
								}
								else
								{
									$selected = "";
								}

								echo sprintf("<option value=\"%s\" %s>%s</option>", $val->studiensemester_kurzbz, $selected, $val->studiensemester_kurzbz);
							}
						?>
						</select>
					</td>
				</tr>
				<tr>
					<td>
						Studiengang:
					</td>
					<td>&nbsp;</td>
					<td>
						<select name="studiengang">
						<?php
							// Fills the select element with all the loaded studiengang
							foreach($studiengang->result as $val)
							{
								echo sprintf("<option value=\"%s\">%s [%s%s] - %s</option>", $val->studiengang_kz, $val->kurzbzlang,$val->typ, $val->kurzbz, $val->bezeichnung);
							}
						?>
						</select>
					</td>
				</tr>
				<tr>
					<td>
						CSV file:
					</td>
					<td>&nbsp;</td>
					<td>
						<input type="file" name="csvFile" value="" />
					</td>
				</tr>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="3" align="center">
						<input type="submit" name="submit" value="Import CSV" />
					</td>
				</tr>
			</table>
		</form>

		<br/>
		<br/>

		<table border=0>
			<tr>
				<th width="25%" align="left">Status</th>
				<th width="25%" align="left">Record</th>
				<th width="50%" align="left">Message</th>
			</tr>
			<?php
				// Printing all the information
				$tableRow = "
					<tr style=\"color: %s;\">
						<td align=\"left\">
							%s
						</td>
						<td align=\"left\">
							%s
						</td>
						<td align=\"left\">
							%s
						</td>
					</tr>";

				foreach($logArray as $log)
				{
					$color = "green"; // great expectations
					if ($log->code == $L_ERROR)
					{
						$color = "red";
					}
					else if ($log->code == $L_WARNING)
					{
						$color = "orange";
					}

					echo sprintf($tableRow, $color, $log->code, $log->lineNumber, $log->msg);
				}
			?>
		</table>

	</body>
</html>
