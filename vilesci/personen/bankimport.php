<?php
/* Copyright (C) 2025 Technikum-Wien
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
require_once("../../config/vilesci.config.inc.php");
require_once("../../include/functions.inc.php");
require_once("../../include/benutzerberechtigung.class.php");
require_once("../../include/datum.class.php");
require_once("../../include/studiengang.class.php");
require_once("../../include/studiensemester.class.php");
require_once("../../include/studienjahr.class.php");
require_once("../../include/student.class.php");
require_once("../../include/konto.class.php");
require_once("../../include/bankverbindung.class.php");

// Get the uid of the logged user
$user = get_uid();
// Check rights
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
if (!$rechte->isBerechtigt("admin", null, "suid"))
{
	die("Sie haben keine Berechtigung für diese Seite");
}

// Variables declaration
$logArray = array(); // Array for output messages
$errorOccurred = false; // Error flag
$dataPosted = false; // Post data flag
$fileName = null;
$fileTmpName = null;
$fileMimeType = null;

// Constants
$L_CSV_N_COLS = 4; // Number of columns of the CSV file
$L_ERROR = "Error";
$L_WARNING = "Warning";
$L_INFO = "Info";
$L_LN_NOT_AVAILABLE = "N/A";

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



// If data has been posted
if (isset($_POST["submit"]))
{
	$dataPosted = true;

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
	$fileRow = false; // Contains a single file row
	$lineNumber = 0; // lines number counter

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
					$rowSurname = $fileRow[0];
					$rowName = $fileRow[1];
					$rowPersonID = $fileRow[2];
					$rowIBAN = $fileRow[3];

					// If this row is not the header
					if (strtolower($rowSurname) != "nachname")
					{
						// Bankverbindung hinterlegen
						$bank = new bankverbindung();
						$found = false;
						if($bank->load_pers($rowPersonID))
						{
							foreach($bank->result as $row_bank)
							{
								if(str_replace(' ', '', $row_bank->iban) == str_replace(' ', '', $rowIBAN))
								{
									lAddToLogArray(
										$L_WARNING,
										$lineNumber,
										"Bank IBAN already found for PersonID ".$rowPersonID
									);
									$found = true;

									// Update Datum aktualisieren damit Update in Fremdsystem getriggert wird
									$row_bank->new=false;
									$row_bank->updateamum = date('Y-m-d H:i:s');
									$row_bank->updatevon = 'Bankimport';
									if($row_bank->save())
									{
										lAddToLogArray(
											$L_INFO,
											$lineNumber,
											"Bank Date Update for PersonID ".$rowPersonID
										);
									}
									else
									{
										lAddToLogArray(
											$L_WARNING,
											$lineNumber,
											"Bank Date Update Failed for PersonID ".$rowPersonID
										);
									}

									break;
								}
							}
						}

						if(!$found)
						{
							$bank = new bankverbindung();
							$bank->new = true;
							$bank->iban = $rowIBAN;
							$bank->person_id = $rowPersonID;
							//$bank->bic = $rowBIC;
							//$bank->name = $rowBank;
							$bank->typ = 'p';
							$bank->verrechnung = true;
							$bank->insertamum = date('Y-m-d H:i:s');
							$bank->insertvon = 'Bankimport';
							$bank->updateamum = date('Y-m-d H:i:s');
							$bank->updatevon = 'Bankimport';
							if($bank->save())
							{
								lAddToLogArray(
									$L_INFO,
									$lineNumber,
									"Bankdaten hinzugefügt"
								);
							}
							else
							{
								lAddToLogArray(
									$L_WARNING,
									$lineNumber,
									"Failed to Add Bankdata".$bank->errormsg
								);
							}
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
<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">
	</head>
	<body>
		<h1>Bank Data Import</h1>
		Diese Seite dient dazu Bankdaten für Studierende per CSV Import ins System zu laden.<br /> <br />
		<form name="saveBank" method="post" enctype="multipart/form-data" action="">
			<table border=0>
				<tr><td valign="top">CMS-Format</td>
					<td></td>
					<td valign="top">
						Zeichensatz: UTF-8<br>
						Feldtrenner: ;<br>
						Texttrenner: "<br>
						Felder:<br>
						<pre>Nachname;Vorname;PersonID;IBAN</pre>
					</td>
				</tr>
				<tr>
					<th width="30%">
					<th width="3%">
					<th width="67%">
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
