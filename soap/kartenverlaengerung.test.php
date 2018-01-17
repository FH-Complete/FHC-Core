<?php

/* Copyright (C) 2012 FH Technikum-Wien
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
 * Authors: Karl Burkhart 	<burkhart@technikum-wien.at>
 */
require_once('../config/cis.config.inc.php');

if(isset($_REQUEST['sub_card']))
{
    $cardNumber = $_REQUEST['cardNumber']; 
    $client = new SoapClient(APP_ROOT."soap/kartenverlaengerung.wsdl.php?".microtime(true)); 
	
	try
	{      
        $response = $client->getNumber($cardNr = $cardNumber);
		
		var_dump($response);
	}
	catch(SoapFault $fault) 
	{
    	echo "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR;
	}
}

?>
<!DOCTYPE html>
<html>
    <head>
        <title>Webservice Kartenverlaengerung</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    </head>
    <body>
        <h1>Webservice Kartenverlängerung</h1>
        <form action ="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" name="card">
            <table>
                <tr>
                    <td>Kartennummer:</td>
                    <td><input type="text" name="cardNumber" size="35"></td>
                </tr>
                <tr>
                    <td><input type="submit" name="sub_card" value="Daten absenden"></td>
                </tr>
            </table>
        </form>
    </body>
</html>
