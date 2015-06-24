<?php
function ean13($datatoencode)
{
	$datatoprint = "";
	$datatoencode = trim($datatoencode);

	// Check to make sure data is numeric and remove dashes, etc.
	$onlycorrectdata = "";
	$stringlength = mb_strlen($datatoencode);

	for($i=0;$i<=$stringlength;$i++)
	{
		// Add all numbers to $onlycorrectdata string
		if(is_numeric(mb_substr($datatoencode, $i, 1)) )
			$onlycorrectdata = $onlycorrectdata . mb_substr($datatoencode, $i, 1);
	}

	// Remove check digits if they added one
	if(mb_strlen($onlycorrectdata) == 13) 
		$onlycorrectdata = mb_substr($onlycorrectdata, 0, 12);
	if(mb_strlen($onlycorrectdata) == 15)
		$onlycorrectdata = mb_substr($onlycorrectdata, 0, 12) . mb_substr($onlycorrectdata, 13, 2);
	if(mb_strlen($onlycorrectdata) == 18) 
		$onlycorrectdata = mb_substr($onlycorrectdata, 0, 12) . mb_substr($onlycorrectdata, 13, 5);

	$EAN2AddOn = "";
	$EAN5AddOn = "";
	$EANAddOnToPrint = "";
	if(mb_strlen($onlycorrectdata) == 17)
		$EAN5AddOn = mb_substr($onlycorrectdata, 12, 5);
	if(mb_strlen($onlycorrectdata) == 14)
		$EAN2AddOn = mb_substr($onlycorrectdata, 12, 2);
	//split 12 digit number from add-on
	$datatoencode = mb_substr($onlycorrectdata, 0, 12);

	//Calculate Check Digit
	$Factor = 3;
	$weightedTotal = "0";
	for($i=mb_strlen($datatoencode)-1;$i>=0;$i--)
	{
		//Get the value of each number starting at the end
		$CurrentChar = mb_substr($datatoencode, $i, 1);
		//multiply by the weighting factor which is 3,1,3,1...
		//and add the sum together
		$weightedTotal = $weightedTotal + $CurrentChar * $Factor;
		//change factor for next calculation
		$Factor = 4 - $Factor;
	}
	//Find the CheckDigit by finding the number + weightedTotal that = a multiple of 10
	//divide by 10, get the remainder and subtract from 10
	$i = ($weightedTotal % 10);
	if($i <> 0)
		$CheckDigit = (10 - $i);
	else
		$CheckDigit = 0;

	//Now we must encode the leading digit into the left half of the EAN-13 symbol
	//by using variable parity between character sets A and B
	$LeadingDigit = mb_substr($datatoencode, 0, 1);
	switch($LeadingDigit)
	{
		case 0:
		    $Encoding = "AAAAAACCCCCC";
			break;
		case 1:
		    $Encoding = "AABABBCCCCCC";
			break;
		case 2:
		    $Encoding = "AABBABCCCCCC";
			break;
		case 3:
		    $Encoding = "AABBBACCCCCC";
			break;
		case 4:
		    $Encoding = "ABAABBCCCCCC";
			break;
		case 5:
		    $Encoding = "ABBAABCCCCCC";
			break;
		case 6:
		    $Encoding = "ABBBAACCCCCC";
			break;
		case 7:
		    $Encoding = "ABABABCCCCCC";
			break;
		case 8:
		    $Encoding = "ABABBACCCCCC";
			break;
		case 9:
		    $Encoding = "ABBABACCCCCC";
			break;
	}
	//add the check digit to the end of the barcode . remove the leading digit
	$datatoencode = mb_substr($datatoencode, 1, 12) . $CheckDigit;
	//Now that we have the total number including the check digit, determine character to print
	//for proper barcoding:
	$datalen = mb_strlen($datatoencode);

	for($i=0; $i<=$datalen;$i++)
	{
		//Get the ASCII value of each number excluding the first number because
		//it is encoded with variable parity
		$CurrentChar = ord(mb_substr($datatoencode, $i, 1));
		$CurrentEncoding = mb_substr($Encoding, $i, 1);
		//Print different barcodes according to the location of the CurrentChar and CurrentEncoding
		switch($CurrentEncoding)
		{
		  case "A":
		    $datatoprint = $datatoprint . Chr($CurrentChar);
			break;
		  case "B":
		    $datatoprint = $datatoprint . Chr($CurrentChar + 17);
			break;
		  case "C":
		    $datatoprint = $datatoprint . Chr($CurrentChar + 27);
			break;
		}

		//add in the 1st character along with guard patterns
		switch($i)
		{
		  case 0:
		    //For the LeadingDigit print the human readable character,
		    //the normal guard pattern and then the rest of the barcode
		    if($LeadingDigit > 4)
				$datatoprint = Chr(ord($LeadingDigit) + 64) . "(" . $datatoprint;
		    if($LeadingDigit < 5)
				$datatoprint = Chr(ord($LeadingDigit) + 37) . "(" . $datatoprint;
			break;
		  case 5:
		    //Print the center guard pattern after the 6th character
		    $datatoprint = $datatoprint . "*";
			break;
		  case 11:
		    //For the last character (12) print the the normal guard pattern
		    //after the barcode
		     $datatoprint = $datatoprint . "(";
		}
	}
	 
	//Process 5 digit add on if it exists
	if(mb_strlen($EAN5AddOn) == 5)
	{

		$EANAddOnToPrint = "";
		//Get check digit for add on
		$Factor = 3;
		$weightedTotal = "0";
		for($i=mb_strlen($EAN5AddOn);$i>=0;$i--)
		{
		    //Get the value of each number starting at the end
		    $CurrentChar = mb_substr($EAN5AddOn, $i, 1);
		    //multiply by the weighting factor which is 3,9,3,9.
		    //and add the sum together
		    if($Factor == "3") 
				$weightedTotal = $weightedTotal + $CurrentChar * 3;
		    if($Factor = "1")
				$weightedTotal = $weightedTotal + $CurrentChar * 9;
		    //change factor for next calculation
		    $Factor = 4 - $Factor;
		}
		//Find the CheckDigit by extracting the right-most number from weightedTotal
		$CheckDigit = mb_substr($weightedTotal, -1);
		//Now we must encode the add-on CheckDigit into the number sets
		//by using variable parity between character sets A and B
		switch($CheckDigit)
		{
		    case 0:
		        $Encoding = "BBAAA";
				break;
		    case 1:
		        $Encoding = "BABAA";
				break;
		    case 2:
		        $Encoding = "BAABA";
				break;
		    case 3:
		        $Encoding = "BAAAB";
				break;
		    case 4:
		        $Encoding = "ABBAA";
				break;
		    case 5:
		        $Encoding = "AABBA";
				break;
		    case 6:
		        $Encoding = "AAABB";
				break;
		    case 7:
		       $Encoding = "ABABA";
				break;
		    case 8:
		        $Encoding = "ABAAB";
				break;
		    case 9:
		        $Encoding = "AABAB";
				break;
		}
	   
		//Now that we have the total number including the check digit, determine character to print
		//for proper barcoding:
	   for($i = 0;$i<=mb_strlen($EAN5AddOn);$i++)
		{
		    //Get the value of each number
		    //it is encoded with variable parity
		   $CurrentChar = mb_substr($EAN5AddOn, $i, 1);
		    $CurrentEncoding = mb_substr($Encoding, $i, 1);
		    //Print different barcodes according to the location of the CurrentChar and CurrentEncoding
		    switch($CurrentEncoding)
			{
		      case "A":
		        if($CurrentChar== "0") $EANAddOnToPrint = $EANAddOnToPrint . Chr(34);
		        if($currentchar== "1") $EANAddOnToPrint = $EANAddOnToPrint . Chr(35);
		        if($currentchar== "2") $EANAddOnToPrint = $EANAddOnToPrint . Chr(36);
		        if($currentchar== "3") $EANAddOnToPrint = $EANAddOnToPrint . Chr(37);
		        if($currentchar== "4") $EANAddOnToPrint = $EANAddOnToPrint . Chr(38);
		        if($currentchar== "5") $EANAddOnToPrint = $EANAddOnToPrint . Chr(44);
		        if($currentchar== "6") $EANAddOnToPrint = $EANAddOnToPrint . Chr(46);
		        if($currentchar== "7") $EANAddOnToPrint = $EANAddOnToPrint . Chr(47);
		        if($currentchar== "8") $EANAddOnToPrint = $EANAddOnToPrint . Chr(58);
		        if($currentchar== "9") $EANAddOnToPrint = $EANAddOnToPrint . Chr(59);
				break;
		      case "B":
		        if($currentchar== "0") $EANAddOnToPrint = $EANAddOnToPrint . Chr(122);
		        if($currentchar== "1") $EANAddOnToPrint = $EANAddOnToPrint . Chr(61);
		        if($currentchar== "2") $EANAddOnToPrint = $EANAddOnToPrint . Chr(63);
		        if($currentchar== "3") $EANAddOnToPrint = $EANAddOnToPrint . Chr(64);
		        if($currentchar== "4") $EANAddOnToPrint = $EANAddOnToPrint . Chr(91);
		        if($currentchar== "5") $EANAddOnToPrint = $EANAddOnToPrint . Chr(92);
		        if($currentchar== "6") $EANAddOnToPrint = $EANAddOnToPrint . Chr(93);
		        if($currentchar== "7") $EANAddOnToPrint = $EANAddOnToPrint . Chr(95);
		        if($currentchar== "8") $EANAddOnToPrint = $EANAddOnToPrint . Chr(123);
		        if($currentchar== "9") $EANAddOnToPrint = $EANAddOnToPrint . Chr(125);
				break;
		    }
		    //add in the space and add-on guard pattern
		    switch($i)
			{
		      case 0:
		        $EANAddOnToPrint = Chr(32) . Chr(43) . $EANAddOnToPrint . Chr(33);
				break;
		      //Now print add-on delineators between each add-on character
		      case 1:
		        $EANAddOnToPrint = $EANAddOnToPrint . Chr(33);
				break;
		      case 2:
		        $EANAddOnToPrint = $EANAddOnToPrint . Chr(33);
				break;
		      case 3:
		        $EANAddOnToPrint = $EANAddOnToPrint . Chr(33);
				break;
		      case 4:
		        $EANAddOnToPrint = $EANAddOnToPrint;
				break;
		    }
		}
	}
	 
	//Process 2 digit add on if it exists
	if(mb_strlen($EAN2AddOn) == 2)
	{
		$EANAddOnToPrint = "";
		//Get encoding for add on
		for($i=0;$i<=99;$i=$i+4)
		{
		    if($EAN2AddOn ==$i) $Encoding = "AA";
		    if($EAN2AddOn ==$i + 1) $Encoding = "AB";
		    if($EAN2AddOn ==$i + 2) $Encoding = "BA";
		    if($EAN2AddOn ==$i + 3) $Encoding = "BB";
		}
		//Now that we have the total number including the encoding
		//determine what to print
		for($i = 1; $i<mb_strlen($EAN2AddOn);$i++)
		{
		    //Get the value of each number
		    //it is encoded with variable parity
		    $currentchar = mb_substr($EAN2AddOn, $i, 1);
		    $CurrentEncoding = mb_substr($Encoding, $i, 1);
		    //Print different barcodes according to the location of the $currentchar and CurrentEncoding
		    switch($CurrentEncoding)
			{
		      case "A":
			    if( $currentchar == "0") $EANAddOnToPrint = $EANAddOnToPrint . Chr(34);
		        if( $currentchar == "1") $EANAddOnToPrint = $EANAddOnToPrint . Chr(35);
		        if( $currentchar == "2") $EANAddOnToPrint = $EANAddOnToPrint . Chr(36);
		        if( $currentchar == "3") $EANAddOnToPrint = $EANAddOnToPrint . Chr(37);
		        if( $currentchar == "4") $EANAddOnToPrint = $EANAddOnToPrint . Chr(38);
		        if( $currentchar == "5") $EANAddOnToPrint = $EANAddOnToPrint . Chr(44);
		        if( $currentchar == "6") $EANAddOnToPrint = $EANAddOnToPrint . Chr(46);
		        if( $currentchar == "7") $EANAddOnToPrint = $EANAddOnToPrint . Chr(47);
		        if( $currentchar == "8") $EANAddOnToPrint = $EANAddOnToPrint . Chr(58);
		        if( $currentchar == "9") $EANAddOnToPrint = $EANAddOnToPrint . Chr(59);
				break;
		      case "B":
		        if( $currentchar == "0") $EANAddOnToPrint = $EANAddOnToPrint . Chr(122);
		        if( $currentchar == "1") $EANAddOnToPrint = $EANAddOnToPrint . Chr(61);
		        if( $currentchar == "2") $EANAddOnToPrint = $EANAddOnToPrint . Chr(63);
		        if( $currentchar == "3") $EANAddOnToPrint = $EANAddOnToPrint . Chr(64);
		        if( $currentchar == "4") $EANAddOnToPrint = $EANAddOnToPrint . Chr(91);
		        if( $currentchar == "5") $EANAddOnToPrint = $EANAddOnToPrint . Chr(92);
		        if( $currentchar == "6") $EANAddOnToPrint = $EANAddOnToPrint . Chr(93);
		        if( $currentchar == "7") $EANAddOnToPrint = $EANAddOnToPrint . Chr(95);
		        if( $currentchar == "8") $EANAddOnToPrint = $EANAddOnToPrint . Chr(123);
		        if( $currentchar == "9") $EANAddOnToPrint = $EANAddOnToPrint . Chr(125);
				break;
		    }
		    //add in the space . add-on guard pattern
		    switch($i)
			{
		      case 0:
		        $EANAddOnToPrint = Chr(32) . Chr(43) . $EANAddOnToPrint . Chr(33);
				break;
		      //Now print add-on delineators between each add-on character
		      case 1:
		        $EANAddOnToPrint = $EANAddOnToPrint;
				break;
		    }
		}
	}
	 
	//Get Printable String
	$Printable_string = $datatoprint . $EANAddOnToPrint . " ";
	 
	//Return PrintableString
	return $Printable_string; 
}
