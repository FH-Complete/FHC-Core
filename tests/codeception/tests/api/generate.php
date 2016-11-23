<?php
	
	/**
	 * Recursively finds all files in the given folder
	 */
	function lstFiles($dir, $lst = null)
	{
		$retLst = array();
		
		if ($lst == null)
			$lst = scandir($dir);
		
		$lst = array_diff($lst, array('..', '.'));
		
		foreach ($lst as $el)
		{
			if (is_dir($dir.'/'.$el))
			{
				$retLst = array_merge($retLst, lstFiles($dir.'/'.$el, scandir($dir.'/'.$el)));
			}
			else
			{
				array_push($retLst, $dir.'/'.$el);
			}
		}
		
		return $retLst;
	}
	
	// Get a list of every file present in the given folder
	$lstFiles = lstFiles('../../../../application/controllers/api/v1');
	
	// Automatically detects the line ending character
	ini_set('auto_detect_line_endings', true);
	// Gets the template of the header of the test file
	if (($fileTplHead = file_get_contents('./template_head.tpl')) === false)
		die('Problems loading template_head.tpl');
	// Gets the template of the body of the test file
	if (($fileTplCall = file_get_contents('./template_call.tpl')) === false)
		die('Problems loading template_call.tpl');
	
	// Loops the list of files
	foreach($lstFiles as $file)
	{
		// Open the file for reading
		if (($fileHandle = fopen($file, 'r')) !== false)
		{
			$name = ''; // Name of the test
			$functions = array(); // List of functions
			$functionsCounter = -1; // Functions counter
			
			// Reads from the file line by line
			while (($line = fgets($fileHandle, 4096)) !== false)
			{
				// Drops the spaces at the beginning and at the and of the line
				$line = trim($line);
				
				// If it is the line that declare the class
				if (strpos($line, 'class') !== false)
				{
					$name = explode(' ', $line)[1];
				}
				
				// If it is a line that declare a function
				if (strpos($line, 'public function get') !== false)
				{
					$functionsCounter++;
					$functions[$functionsCounter] = array();
					$functions[$functionsCounter]['name'] = trim(preg_replace('/^get/i', ' ', explode(' ', $line)[2]));
					$functions[$functionsCounter]['name'] = trim(str_replace('()', ' ', $functions[$functionsCounter]['name']));
					$functions[$functionsCounter]['parameters'] = array();
				}
				
				// If it is a line that get a parameter
				if (strpos($line, 'this->get') !== false)
				{
					$parameters = explode('\'', $line);
					if (count($parameters) >= 2)
					{
						$functions[$functionsCounter]['parameters'][] = $parameters[1];
					}
					else
					{
						$parameters = explode('"', $line);
						if (count($parameters) >= 2)
						{
							$functions[$functionsCounter]['parameters'][] = $parameters[1];
						}
					}
				}
			}
			
			fclose($fileHandle); // Closing the file pointer is always a good thing
			
			// Gets the path of the api
			$apiPath = trim(str_replace('../../../../application/controllers/api/', ' ', $file));
			$apiPath = substr($apiPath, 0, strrpos($apiPath, '/') + 1);
			// Prefix of the test file name given by the parent folder
			$namePrefix = trim(str_replace('v1/', ' ', $apiPath));
			$namePrefix = ucfirst(trim(str_replace('/', ' ', $namePrefix)));
			
			// If if is not a fake
			if (trim($name) != '')
			{
				// Where to create the test files
				$testDir = './v1/';
				// If the test file is not already present
				if (!file_exists($testDir.$namePrefix.$name.'Cept.php'))
				{
					// Create and open the test file for writing
					if (($fileTestHandle = fopen($testDir.$namePrefix.$name.'Cept.php', 'w')) !== false)
					{
						// Lst of function to place in the header
						$strLstFunctions = '';
						for($i = 0; $i < count($functions); $i++)
						{
							$function = $functions[$i];
							if ($i == 0)
							{
								$strLstFunctions .= $apiPath.$name.'/'.': ';
							}
							
							$strLstFunctions .= $function['name'];
							
							if ($i < count($functions) - 1)
							{
								$strLstFunctions .= ' ';
							}
						}
						
						// Create the test file header using the template
						$strToWrite = str_replace('_CALL_', $strLstFunctions, $fileTplHead);
						// Writes the header into the test file
						if (fwrite($fileTestHandle, $strToWrite."\n") === false)
						{
							echo 'Error!!!';
						}
						
						// For every function create a call
						foreach($functions as $function)
						{
							// Gets a list of parameters
							$strLstParameters = '';
							for($i = 0; $i < count($function['parameters']); $i++)
							{
								$parameter = $function['parameters'][$i];
								$strLstParameters .= '"'.$parameter.'" => "1"';
								if ($i < count($function['parameters']) - 1)
								{
									$strLstParameters .= ", ";
								}
							}
							// Create the call using the template
							$strToWrite = str_replace('_CALL_', $apiPath.$name.'/'.$function['name'], $fileTplCall);
							$strToWrite = str_replace('_PARAMETERS_', $strLstParameters, $strToWrite);
							// Write it into the test file
							if (fwrite($fileTestHandle, $strToWrite."\n") === false)
							{
								echo 'Error!!!';
							}
						}
						
						fclose($fileTestHandle); // As usual
					}
					else
					{
						echo "Error opening file: ".$testDir.$name.'Cept.php'."\n";
					}
				}
				else
				{
					echo $testDir.$name."Cept.php is already present\n";
				}
			}
		}
	}
	
?>