<?php
/* Copyright (C) 2008 Technikum-Wien
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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
session_start();

require_once('../../config/cis.config.inc.php');
require_once('../../include/wochenplan.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/betriebsmittel.class.php');
require_once('../../include/betriebsmittelperson.class.php');
require_once('../../include/betriebsmitteltyp.class.php');
require_once('../../include/mail.class.php');
require_once('../../include/news.class.php');
require_once('../../include/content.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/konto.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/authentication.class.php');
require_once('../../include/addon.class.php');
require_once('../../include/'.EXT_FKT_PATH.'/serviceterminal.inc.php');

	if (!$db = new basis_db())
		$db=false;

//	Initialisieren des Fehlertextes
	$error='';
	$news='';
// ------------------------------------------------------------------------------------------
//	Konstante
// ------------------------------------------------------------------------------------------
	// Session Bereich
	if (!defined('constSESSIONNAME')) define('constSESSIONNAME',"infoterminal" );
	// Datum - Format
	if (!defined('constHeaderDatumZeit')) define('constHeaderDatumZeit','%A, %d %B %G  %R' );
	if (!defined('constRaumDatumZeit')) define('constRaumDatumZeit','%a, %d.%m.%Y' );
	if (!defined('constHeaderStundenplan')) define('constHeaderStundenplan','KW %W,  %B %G' );
	if (!defined('constHeaderStundenplanTag')) define('constHeaderStundenplanTag','%A<br>%d.%m.%y' );
	if (!defined('constAktuelleZeitHHMi')) define('constAktuelleZeitHHMi', date("Hi", time()));
	if (!defined('constAktuelleZeitHH')) define('constAktuelleZeitHH', date("H", time()));

// ------------------------------------------------------------------------------------------
//	Request Parameter
// ------------------------------------------------------------------------------------------
  	$timer=trim((isset($_REQUEST['timer']) ? $_REQUEST['timer']:0));
	if(!isset($ServiceTerminalDefaultRaumtyp))
		$ServiceTerminalDefaultRaumtyp='HS';

	// Raumtyp
  	$raumtyp_kurzbz=trim((isset($_REQUEST['raumtyp_kurzbz']) ? $_REQUEST['raumtyp_kurzbz']:$ServiceTerminalDefaultRaumtyp));
	// Saal - Raum
  	$ort_kurzbz=trim((isset($_REQUEST['ort_kurzbz']) ? $_REQUEST['ort_kurzbz']:''));
	// Work
  	$work=trim((isset($_REQUEST['work']) ? $_REQUEST['work']:'raumanzeigen'));
	// User
  	$key_input=trim((isset($_REQUEST['key_input']) ? $_REQUEST['key_input']:''));
  	$uid=trim((isset($_REQUEST['uid']) ? $_REQUEST['uid']:''));
  	$pwd=trim((isset($_REQUEST['pwd']) ? $_REQUEST['pwd']:''));
  	$debug=trim((isset($_REQUEST['debug']) ? $_REQUEST['debug']:''));
  	$sdtools=trim((isset($_REQUEST['sdtools']) ? $_REQUEST['sdtools']:false));
  	$standort_id = (isset($_COOKIE['standort_id']) ? $_COOKIE['standort_id']:'');
	if ($sdtools)
		$work='login';


// ------------------------------------------------------------------------------------------
//	Verarbeiten wenn Kennzeichen work = login oder logoff
// ------------------------------------------------------------------------------------------
	if (mb_strtolower($work)=='logoff')
	{
		if (isset($_SESSION[constSESSIONNAME]))
			unset($_SESSION[constSESSIONNAME]);
		$uid='';
		$work='raumanzeigen';
	  	$raumtyp_kurzbz=$ServiceTerminalDefaultRaumtyp;
	}


	// Es gibt eine Serverauth., aber es erfolgte noch kein Login fuer Persoenliche Daten - Login erzwingen
	if (isset($_SERVER['PHP_AUTH_USER'])  && !empty($_SERVER['PHP_AUTH_USER']) && (!isset($_SESSION[constSESSIONNAME]["uid"]) || empty($_SESSION[constSESSIONNAME]["uid"])) )
	{
		$work="login";
	  	$uid=trim((isset($_SERVER['PHP_AUTH_USER'])?$_SERVER['PHP_AUTH_USER']:''));
  		$pwd=trim((isset($_SERVER['PHP_AUTH_PW'])?$_SERVER['PHP_AUTH_PW']:''));
	}

	// Login Prozedure wenn Anmeldung ueber einen Schluessel erfolgte
	// - Lesen der Betriebsmittel um Anwender zu ermitteln ( es wird hier kein Passwort benoetigt / LDAP )
	$cardlogin=false;
	$cardnumber = "";
	if ($db && !empty($key_input)) // Login
	{
	    // Pruefen ob es sich um eine HEX Eingabe handelt
	    $betriebsmittel = new betriebsmittel();
	    //$key_input = $betriebsmittel->transform_kartennummer($key_input);

	    // führende nullen entfernen
	    $key_input = preg_replace("/^0*/", "", $key_input);
	    $uidStudent = getUidFromCardNumber($key_input);
	    if($uidStudent != false)
	    {
		$uid = $uidStudent;
		$work = "login";
		$cardlogin = true;
	    }
	    else
	    {
		$addon_externeAusweise = false;
		$addon = new addon();
		$addon->loadAddons();
		foreach($addon->result as $ad)
		{
		    if($ad->kurzbz == "externeAusweise")
		    {
			$addon_externeAusweise = true;
		    }
		}

		if($addon_externeAusweise)
		{
		    require_once (dirname(__FILE__).'/../../addons/externeAusweise/include/idCard.class.php');
		    $idCard = new idCard();
		    if($idCard->loadByCardnumber($key_input))
		    {
			$uid = "";
			$cardnumber = $idCard->cardnumber;
			$work = "verlaengerung";
			$cardlogin = true;
			$_SESSION[constSESSIONNAME]["uid"]=$cardnumber;
		    }
		}
	    }
	}

	if (mb_strtolower($work)=='login')
	{
		if (isset($_SESSION[constSESSIONNAME]))
			unset($_SESSION[constSESSIONNAME]);
		if (!empty($uid)) // Login
		{
			$ldapstatus='';
			if ($cardlogin || !$ldapstatus=ldap_uid_check($uid,$pwd) )
			{
				// Lesen der Userdaten
				if ($user_array=uid_read_mitarbeiter_oder_student($db,$uid))
				{
					// Personendaten lesen wenn Mitarbeiter oder Student gefunden wurde
					$_SESSION[constSESSIONNAME]["uid"]=$uid;
					$_SESSION[constSESSIONNAME]["pwd"]=$pwd;
					$_SESSION[constSESSIONNAME]["dat"]=$user_array;
				}
			}

			// Wenn kein ldapstatus geliefert wurde ist alles OK, sonst ist im ldapstatus die Fehlermeldung
			$error.=$ldapstatus;
			// Login erfolgreich - Eigenenstundenplan anzeigen
			if (isset($_SESSION[constSESSIONNAME]["uid"])  && !empty($_SESSION[constSESSIONNAME]["uid"]) )
				$work='stundenplan';
		}
	}
// ------------------------------------------------------------------------------------------
//	Lesen Newstickerzeilen
// ------------------------------------------------------------------------------------------
	$studiengang_kz="0";
	$semester="";
	if(isset($_SESSION[constSESSIONNAME]["dat"]) && isset($_SESSION[constSESSIONNAME]["dat"]->studiengang_kz) )
	{
		$studiengang_kz=trim($_SESSION[constSESSIONNAME]["dat"]->studiengang_kz);
		$semester=trim($_SESSION[constSESSIONNAME]["dat"]->semester);
	}
	$fachbereich_kurzbz="";
 	if (strtolower($work)!=strtolower("meinedaten") || !isset($_SESSION[constSESSIONNAME]))
		$news=read_create_html_news($db,$fachbereich_kurzbz,$studiengang_kz,$semester);

// ------------------------------------------------------------------------------------------
//	Linkes Auswahlmenue fuer Raumtypen
// ------------------------------------------------------------------------------------------
	if(isset($ServiceTerminalRaumtypen) && !is_null($ServiceTerminalRaumtypen))
		$row_ort = $ServiceTerminalRaumtypen;
	else
	{
		$row_ort=array(
				array("type"=>"EDV","beschreibung"=>"&nbsp;Freie&nbsp;<br>&nbsp;PC R&auml;ume&nbsp;","img"=>""),
				array("type"=>"HS","beschreibung"=>"&nbsp;Freie&nbsp;<br>&nbsp;H&ouml;rs&auml;le&nbsp;","img"=>""),
				array("type"=>"SEM","beschreibung"=>"&nbsp;Freie&nbsp;<br>&nbsp;Seminarr&auml;ume&nbsp;","img"=>""),
				array("type"=>"Lab","beschreibung"=>"&nbsp;Freie&nbsp;<br>&nbsp;Laborr&auml;ume&nbsp;","img"=>""),
				);
	}

$refreshtime = ($sdtools?99999:(isset($_SESSION[constSESSIONNAME]["uid"]) && !empty($_SESSION[constSESSIONNAME]["uid"])?10:(date('H')>22 || date('H')<5?12000:900)));
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Info-Terminal</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="pragma" content="no-cache">
<meta http-equiv="expires" content="-1">
<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
<link rel="stylesheet" href="infoterm.css" type="text/css">
	<script language="JavaScript1.2" type="text/javascript">
	<!--
		var keyfeld;
		var warten;
		var PrintWin;
		var BitteWartenID='bitteWarten';
		var input_focus;

        var verlaengerungsautomat = false;

        function isVerlaengerungsautomat()
        {
            $.ajax({url:"<?php echo mb_str_replace('https://','http://',APP_ROOT); ?>isProxyActive",
            cache: false,
            async: true,
            timeout: 35000,
            error: proxyError,
            dataType:"text",
            data: { },
            success: proxySuccess
            });
        }

        function proxyError(data)
        {
        }

        function proxySuccess(data)
        {
            if(data=='TRUE')
                verlaengerungsautomat = true;

            if(verlaengerungsautomat == true)
            {
                <?php if (!isset($_SESSION[constSESSIONNAME]["uid"])  && empty($_SESSION[constSESSIONNAME]["uid"]))
                    echo 'getRFID(); ';?>


                document.getElementById('btn_ejectcard').style.display = 'block';
				if(document.getElementById('btn_verlaengerung'))
                	document.getElementById('btn_verlaengerung').style.display = 'block';
            }

        }

        function checkVerlaengerung()
        {
            if(verlaengerungsautomat == true)
                alert('Verlängerungsautomat');
            else
                alert('Infoterminal');
        }

        function getRFID()
        {
            $.ajax({url:"<?php echo mb_str_replace('https://','http://',APP_ROOT); ?>getUID",
            cache: false,
            async:true,
            timeout:35000,
            error: showRFIDError,
            dataType: "text",
            data: { },
            success: fillRFID
            });
        }

        function showRFIDError(data)
        {
            // wenn fehler aufgetreten ist, nochmal aufrufen
            getRFID();
        }

        function fillRFID(data)
        {
            if(data != '' && data != 'TIMEOUT')
            {
                // wenn nummer und kein timeout zurückgekommen ist dann weiterleiten
                document.location.href='<?php echo $_SERVER['PHP_SELF'];?>?key_input='+data+'&work=login&timer=<?PHP echo time().'&amp;standort_id='.$standort_id; ?>';
            }

            if(data =='TIMEOUT')
            {
                getRFID();
            }
        }

        function printCard()
        {
			// Automatischen Seiten Refresh deaktivieren
        	window.clearTimeout(logouttimeout);

        	$('#divDruckStatus').html('Karte wird gedruckt - Bitte warten <img src="../../skin/images/spinner.gif">');
        	$('#divKarteDrucken').hide();

			// Karte Drucken
            $.ajax({url:"<?php echo mb_str_replace('https://','http://',APP_ROOT); ?>printCard",
            cache:false,
            async:true,
            timeout:30000,
            error: cardError,
            dataType: "text",
            success: cardSuccess
            });
        }

        function cardError(data)
        {
			alert("Print Error")
        }

        function cardSuccess(data)
        {
        	$('#divDruckStatus').html('Bitte entnehmen Sie ihre Karte');

        	logouttimeout = window.setTimeout('logout()', 2000);

            // nach print noch einmal aus der seriellen verbindung lesen

            $.ajax({url:"<?php echo mb_str_replace('https://','http://',APP_ROOT); ?>getUID",
            cache: false,
            async:true,
            timeout:35000,
            dataType: "text",
            data: { }
            });
        }

        function ejectCard()
        {
			$('#btn_ejectcard').html('Ejecting Card..');
            $.ajax({url:"<?php echo mb_str_replace('https://','http://',APP_ROOT); ?>ejectCard",
            cache:false,
            async:true,
            timeout:4000,
            error: ejectError,
            dataType:"text",
            success: ejectSuccess
            });
        }

        function ejectError(data)
        {
            $('#btn_ejectcard').html('Eject Failed<br>Restarting Service');
			window.location.href='index.php?work=Logoff&amp;raumtyp_kurzbz=&amp;ort_kurzbz=&amp;timer=<?PHP echo time().'&standort_id='.$standort_id; ?>';
        }

        function ejectSuccess(data)
        {
            // logout
            window.location.href='index.php?work=Logoff&amp;raumtyp_kurzbz=&amp;ort_kurzbz=&amp;timer=<?PHP echo time().'&standort_id='.$standort_id; ?>';
        }

		function logout()
		{
            if(verlaengerungsautomat == true)
            {
                document.getElementById('btn_ejectcard').disabled=true;

                ejectCard();

            }
            else
            {
                window.location.href='index.php?work=Logoff&amp;raumtyp_kurzbz=&amp;ort_kurzbz=&amp;timer=<?PHP echo time().'&standort_id='.$standort_id; ?>';
            }
		}

		function LogoutTimer()
		{
			logouttimeout = window.setTimeout('logout()', <?php echo $refreshtime*1000;?>);
		}

		function updateSiteRefresh()
		{
			window.clearTimeout(logouttimeout);
			LogoutTimer();
		}
		<?php if (isset($_SESSION[constSESSIONNAME]["uid"])  && !empty($_SESSION[constSESSIONNAME]["uid"]) )
		{ ?>
			input_focus=window.setInterval('input_focus_key()',8000);
			function input_focus_key() {
				if (document.getElementById('key_input')) {
				 	document.getElementById('key_input').focus();
			}
		}
		<?php } ?>

		function check_key(param_key_input_feld) {
			keyfeld=param_key_input_feld;
			if (keyfeld && keyfeld.value.length>0) {
				// Bitte Wartentext
				document.getElementById(BitteWartenID).className='einblenden';
				// 10 Stellige Schluessel-Keys muessen auch berucksichtigt werden
				if (keyfeld.value.length>7) {
					warten=window.setInterval('call_key()',1000);
				}
				if (keyfeld.value.length>11) {
					call_key();
				}
			}
		}

		function call_key() {
			// sollte noch das Timerobjekt vorhanden sein muss es entfernt werden
				if (input_focus) {
					window.clearInterval(input_focus);
					input_focus=false;
				}

				if (warten) {
					window.clearInterval(warten);
					warten=false;
				}

				if (PrintWin) {
					PrintWin.close();
					PrintWin=false;
				}

				if (keyfeld && keyfeld.value.length>0) {
					keyfeld.className='ausblenden';
					keyfeld.disabled=true ;

					var tmpWert=keyfeld.value;

					tmpWert=tmpWert.substring(0,12 );
					document.location.href='<?php echo $_SERVER['PHP_SELF'];?>?key_input='+encodeURIComponent(tmpWert)+'&work=login&timer=<?PHP echo time().'&amp;standort_id='.$standort_id; ?>';
					}
		}

		function show_layer(x)
		{
 		if (document.getElementById && document.getElementById(x))
		{
			document.getElementById(x).style.visibility = 'visible';
			document.getElementById(x).style.display = 'inline';
		} else if (document.all && document.all[x]) {
		   	document.all[x].visibility = 'visible';
			document.all[x].style.display='inline';
	      	} else if (document.layers && document.layers[x]) {
	           	 document.layers[x].visibility = 'show';
			 document.layers[x].style.display='inline';
	          }
	}

	function hide_layer(x)
	{
		if (document.getElementById && document.getElementById(x))
		{
		   	document.getElementById(x).style.visibility = 'hidden';
			document.getElementById(x).style.display = 'none';
       	} else if (document.all && document.all[x]) {
			document.all[x].visibility = 'hidden';
			document.all[x].style.display='none';
       	} else if (document.layers && document.layers[x]) {
	           	 document.layers[x].visibility = 'hide';
			 document.layers[x].style.display='none';
	          }
	}

	var aktivTimeout;
	function close_news()
	{
		window.clearTimeout(aktivTimeout);
		hide_layer('news');
		document.getElementById('news').innerHTML='';
		if (document.getElementById('key_input')) {
		 	document.getElementById('key_input').focus();
		}
	}

	//-->
</script>

</head>
<body onload="if (document.getElementById('key_input')) { document.getElementById('key_input').focus();} LogoutTimer(); isVerlaengerungsautomat(); ">

<?php
	ob_flush();
	flush();


    echo '
    <div id="news" style="display:none; width:90%;	border: 2px solid Black;padding: 7px 7px 7px 7px;background-color: #FFFFFF;z-index:100;position:absolute;top: 15px;left:20px;empty-cells: hide;"></div>
<table  style="z-index:1" class="content" border="0" cellspacing="0" cellpadding="0">
  <tr>
	<!-- Start Linkes Menue -->
  	<td valign="top">
		<table class="ort_liste" cellpadding="1" cellspacing="1">
    <tr><td style="padding-bottom: 10px;" align="center" valign="middle">';

    echo '<a href="'.htmlspecialchars($_SERVER['PHP_SELF']).'?standort_id='.$standort_id.'">
    			<img alt="Logo" src="../../skin/styles/'.DEFAULT_STYLE.'/logo.png" border="0" style="max-width: 170px; max-height: 150px">
    	  </a></td></tr>';
	if(isset($_SESSION[constSESSIONNAME]["uid"])  && !empty($_SESSION[constSESSIONNAME]["uid"]) && !empty($_SESSION[constSESSIONNAME]["pwd"]))
	{
		//Angemeldeter User -  Stundenplan der Woche
		echo '
		  <tr class="cursor_hand">
	  		<td>
				<a href="'.htmlspecialchars($_SERVER['PHP_SELF']).'?work=stundenplan&amp;standort_id='.$standort_id.'">
						<span class="blau_mitteText">
							Mein<br>LV-Plan
						</span>
				</a>
	  		</td>
 		</tr>';
	}
	else
	{
		echo '<tr class="keyinput"><td class="keyinput"><span id="key_input_feld"><input id="key_input" name="key_inputs" onkeydown="check_key(this);" onkeypress="check_key(this);" maxlength="12"></span></td></tr>';
	}

	// Tabelle der Raumtypen
	echo html_output_liste_raumtypen($row_ort);
	if(isset($_SESSION[constSESSIONNAME]["uid"])  && !empty($_SESSION[constSESSIONNAME]["uid"]) && empty($cardnumber))
	{
		 //Angemeldeter User -  Stundenplan der Woche
		 echo '
		  <tr class="cursor_hand">
	  		<td>
				<a href="'.htmlspecialchars($_SERVER['PHP_SELF']).'?work=meinedaten&amp;standort_id='.$standort_id.'">
						<span class="blau_mitteText">
							Pers&ouml;nliche<br>Daten
						</span>
				</a>
	  		</td>
 		</tr>
        <tr class="cursor_hand" id="btn_verlaengerung" style="display:none; ">
	  		<td>
				<a href="'.htmlspecialchars($_SERVER['PHP_SELF']).'?work=verlaengerung&amp;standort_id='.$standort_id.'">
						<span class="blau_mitteText">
							Studierendenausweis <br> verl&auml;ngern
						</span>
				</a>
	  		</td>
 		</tr>';
	}
	else
	{
		// Lageplan
	    if(defined('CIS_INFOSCREEN_LAGEPLAN_ANZEIGEN') && CIS_INFOSCREEN_LAGEPLAN_ANZEIGEN)
	    {
		echo '
		<tr class="cursor_hand">
	  		<td>
				<a href="'.htmlspecialchars($_SERVER['PHP_SELF']).'?work=lageplan&amp;standort_id='.$standort_id.'">
					<span class="blau_mitteText">
						Lageplan<br>&nbsp;
					</span>
				</a>
	  		</td>
 		</tr>';
	    }
	}

	echo '<tr><td>&nbsp;</td></tr>';
	// Login

	// Wenn keine Server Userauth. vorhanden ist
	if (!isset($_SERVER['PHP_AUTH_USER']) || (isset($_SERVER['PHP_AUTH_USER']) && empty($_SERVER['PHP_AUTH_USER'])) )
	{
		echo '
		  <tr class="cursor_hand">
		  		<td>
					<a href="'.htmlspecialchars($_SERVER['PHP_SELF']).'?work='.(isset($_SESSION[constSESSIONNAME]["uid"]) && !empty($_SESSION[constSESSIONNAME]["uid"])?'logoff':'login').'&amp;raumtyp_kurzbz='.$raumtyp_kurzbz.'&amp;ort_kurzbz='.$ort_kurzbz.'&amp;standort_id='.$standort_id.'">
						<span class="blau_mitteText">';
		if(isset($_SESSION[constSESSIONNAME]["uid"]) && !empty($_SESSION[constSESSIONNAME]["uid"]))
			echo '<img alt="Logout" height="35" src="system-users_out.png" border="0">&nbsp;Logoff';
		else
			echo '<img alt="Login" height="35" src="system-users_in.png" border="0">&nbsp;Login';

		echo '
						</span>
					</a>
		  		</td>
			</tr>
		  <tr class="cursor_hand">
	  		<td>
				<a href="#" onclick="ejectCard();return false;" id="btn_ejectcard" style="display:none">
						<span class="blau_mitteText">
							Eject<br>Card
						</span>
				</a>
	  		</td>
 		</tr>';
	}

	echo '
		<tr><td height="100%">&nbsp;</td></tr>
  		</table>
  	</td>';

	// Ende Linkes Menue
 	echo '<td width="100%" valign="top" id="content">';

	if (!$db || mb_strtolower($work)==mb_strtolower('lageplan'))
	{
		echo '<h1>Lageplan '.CAMPUS_NAME.'</h1>';
		$pfad_standort_lageplan = '../../skin/styles/'.DEFAULT_STYLE.'/lageplan_'.$standort_id.'.jpg';
		// Wenn fuer den ausgewaehlten Standort ein eigener Lageplan verfuegbar ist, wird dieser angezeigt
		// ansonsten wird der normale Lageplan angezeigt.
		if($standort_id!='' && file_exists($pfad_standort_lageplan))
			echo '<img height="400" src="'.$pfad_standort_lageplan.'" border="0" >';
		else
			echo '<img height="400" src="../../skin/styles/'.DEFAULT_STYLE.'/lageplan.jpg" border="0" >';
	}
	else if (mb_strtolower($work)==mb_strtolower("login"))
	{
		echo '<h1>Login '.CAMPUS_NAME.'</h1>';
		include_once('keyboard.php');
	}
	else if (strtolower($work)==strtolower("meinedaten") && isset($_SESSION[constSESSIONNAME]))
	{
		echo meine_uid_informationen($db,$_SESSION[constSESSIONNAME]["uid"],$_SESSION[constSESSIONNAME]["dat"]);
	}
    else if (strtolower($work)==strtolower("verlaengerung") && isset($_SESSION[constSESSIONNAME]))
	{
		karten_verlaengerung($_SESSION[constSESSIONNAME]["uid"],$cardnumber);
	}
	else if (mb_strtolower($work)==mb_strtolower("stundenplan") && isset($_SESSION[constSESSIONNAME]["uid"])  && !empty($_SESSION[constSESSIONNAME]["uid"]) )
	{
		echo alle_uid_stundenplan_informationen($db,$_SESSION[constSESSIONNAME]["uid"],$_SESSION[constSESSIONNAME]["dat"]);
	}
	else
	{
		echo alle_raum_informationen($db,$raumtyp_kurzbz,$ort_kurzbz, $standort_id);
	}

	// Zusatzinformation wie Error,News und Warten
	echo '<span class="error_zeile">'.(isset($error)?$error:'').'&nbsp;</span>
	  <span id="bitteWarten" class="ausblenden"><span class="error_text"><br>Bitte warten</span></span>
	  <br><span class="news_zeile">'.(isset($news)?$news:'').'&nbsp;</span>
	</td>
  </tr>
</table>
</body>
</html>';

/*
*
* @meine_uid_informationen Termine zur Auswahl Raumtype
*
* @param $db Aktuelle Datenbankverbindung
* @param $uid Userkurzzeichen
* @param $user_array Anwenderinformatinen in Tabellenform
*
* @return HTML Tablle des Anwenderkalenders
*
*/
function meine_uid_informationen($db,$uid,$user="")
{
	global $standort_id;
	$html_user_daten='';
	$html_user_daten_detail='';
	// Lesen der Gesamtinformation zu einer Person (ALle UIDs holen)
	$user_array=array();
	if ($db)
		$user_array=personen_id_read_mitarbeiter_oder_student($db,$user->person_id);

	if (isset($user_array) && is_array($user_array) && count($user_array)>1)
	{
			$html_user_daten.='<table class="persoenlichedaten">';
				$html_user_daten.='<tr>';

				reset($user_array);
				for ($i=0;$i<count($user_array);$i++)
				{
					$user_array[$i]->uid=trim($user_array[$i]->uid);

					$html_user_daten.='<td>';
					if ($user_array[$i]->aktiv =='t' || ($user_array[$i]->aktiv !='f' && $user_array[$i]->aktiv))
					{
						$html_user_daten.='<a href="'.$_SERVER['PHP_SELF'].'?sdtools=1&amp;login=1&amp;uid='.urlencode($user_array[$i]->uid).'&amp;standort_id='.$standort_id.'">';
						$html_user_daten.='
								<span class="gruen_mitteText">&nbsp;'.($user_array[$i]->uid==$uid?'<b>':''). trim($user_array[$i]->uid).($user_array[$i]->uid==$uid?'</b>':'').'&nbsp;</span>';
						$html_user_daten.='</a>';
					}
					else
					{
						$html_user_daten.='
								<span class="rot_mitteText">&nbsp;'.trim($user_array[$i]->uid).'&nbsp;</span>';
					}
					$html_user_daten.='</td>';
				}
				$html_user_daten.='</tr>';
			$html_user_daten.='</table>';

			$html_user_daten_detail.='<hr>';
			reset($user_array);
			for ($i=0;$i<count($user_array);$i++)
				$html_user_daten_detail.=($i>0?'<hr>':'').meine_uid_informationen_detail($db,$user_array[$i]->uid,$i);
	}
	else
	{
		$html_user_daten_detail.=meine_uid_informationen_detail($db,$uid,0);
	}
	$html_user_daten.=$html_user_daten_detail;

	$html_user_daten.='<hr>';

	return $html_user_daten;
}
#-------------------------------------------------------------------------------------------
/*
* Zeigt die Oberfläche zur Kartenverlängerung an
* @param $uid Userkurzzeichen
*/
function karten_verlaengerung($uid, $cardnumber=NULL)
{
    if(is_null($cardnumber))
    {
	$studienbeitrag = false;
	// Mitarbeiter brauchen die Karte nicht verlängern

	$cardPerson = new benutzer();
	if(!$cardPerson->load($uid))
	{
	    die('Konnte User nicht laden');
	}

	$html_user_daten='';
	$html_user_daten.='<h1>Verl&auml;ngerung Studienausweis</h1>';
	$html_user_daten.='<table>
		    <tr>
			<td valign="top">
			    <table>
				<tr>
				    <td><b><font size="+2">'.($cardPerson->titelpre?$cardPerson->titelpre.' ':'').$cardPerson->vorname.' '.$cardPerson->nachname.' '.($cardPerson->titelpost?$cardPerson->titelpost:'').'</font></b>&nbsp;</td>
				</tr>
				<tr>
				    <td></td>
				</tr>
			    </table>
			&nbsp;</td>
			<td valign="top">
			    <table>
				<tr><td>&nbsp;</td></tr>
			    </table>
			&nbsp;</td>
		    </tr>
	</table>';

	echo $html_user_daten;
    }
    // User zur Karte konnte nicht geladen werden

	$data = ServiceTerminalCheckVerlaengerung($uid, $cardnumber);

	if($data[0]===true)
    {
        echo $data[1];
        echo '<br>Um Karte zu verlängern dr&uuml;cken Sie bitte folgenden Button:';

        echo'   <table>
                    <tr class="cursor_hand" id="btn_drucken">
                        <td>
                        	<div id="divKarteDrucken">
                            <a onclick="printCard();">
                                    <span class="blau_mitteText">
                                    <br>
                                        Karte drucken<br>
                                    <br>
                                    </span>
                            </a>
                            </div>

                        </td>
                    </tr>
                </table>
       			 <div id="divDruckStatus" style="font-size:large; font-weight:bold; color:red;">
                 </div>';
    }
    else
        echo $data[1].'<br><br>';

}

#-------------------------------------------------------------------------------------------
/*
*
* @meine_uid_informationen_detail Detailanzeige Userprofil
*
* @param $db Aktuelle Datenbankverbindung
* @param $uid Userkurzzeichen
* @param $user_array Anwenderinformatinen in Tabellenform
*
* @return HTML Tablle des Anwenderkalenders
*
*/
function meine_uid_informationen_detail($db,$uid,$count=0)
{
	$html_user_daten='';

	$stg = '';
	$stg_obj = new studiengang();
	$stg_obj->getAll('typ, kurzbz', false);
	$stg_arr = array();
	foreach ($stg_obj->result as $row)
		$stg_arr[$row->studiengang_kz]=$row->kurzbzlang;
	if(!($erg=$db->db_query("SELECT * FROM campus.vw_benutzer WHERE uid=".$db->db_add_param($uid, FHC_STRING))))
		die($db->db_last_error());
	$num_rows=$db->db_num_rows($erg);
	if ($num_rows==1)
	{
		$person_id=$db->db_result($erg,0,"person_id");

		$anrede=$db->db_result($erg,0,"anrede");
		$vorname=$db->db_result($erg,0,"vorname");
		$vornamen=$db->db_result($erg,0,"vornamen");
		$nachname=$db->db_result($erg,0,"nachname");
		$gebdatum=$db->db_result($erg,0,"gebdatum");
		$gebort=$db->db_result($erg,0,"gebort");

		$aktiv=$db->db_result($erg,0,"aktiv");

		$svnr=$db->db_result($erg,0,"svnr");

		$titelpre=$db->db_result($erg,0,"titelpre");
		$titelpost=$db->db_result($erg,0,"titelpost");
		$email=$db->db_result($erg,0,"uid").'@'.DOMAIN;
		$email_alias=$db->db_result($erg,0,"alias");
		if ($email_alias)
			$email_alias=$email_alias.'@'.DOMAIN;

		$hp=$db->db_result($erg,0,"homepage");
		$aktiv=$db->db_result($erg,0,"aktiv");
		$foto=$db->db_result($erg,0,"foto");
	}

	if(!($erg_stud=$db->db_query("SELECT studiengang_kz, semester, verband, gruppe, matrikelnr, typ::varchar(1) || kurzbz AS stgkz, tbl_studiengang.bezeichnung AS stgbz FROM public.tbl_student JOIN public.tbl_studiengang USING(studiengang_kz) WHERE student_uid=".$db->db_add_param($uid, FHC_STRING))))
		die($db->db_last_error());
	$stud_num_rows=$db->db_num_rows($erg_stud);
	if ($stud_num_rows==1)
	{
		$stg=$db->db_result($erg_stud,0,"studiengang_kz");
		$stgbez=$db->db_result($erg_stud,0,"stgbz");
		$stgkz=$db->db_result($erg_stud,0,"stgkz");
		$semester=$db->db_result($erg_stud,0,"semester");
		$verband=$db->db_result($erg_stud,0,"verband");
		$gruppe=$db->db_result($erg_stud,0,"gruppe");
		$matrikelnr=$db->db_result($erg_stud,0,"matrikelnr");
	}

	$ort='';
	$kurzbz='';
	$tel='';
	$vorwahl='';
	if(!($erg_lekt=$db->db_query("SELECT * FROM public.tbl_mitarbeiter WHERE mitarbeiter_uid=".$db->db_add_param($uid, FHC_STRING))))
		die($db->db_last_error());
	$lekt_num_rows=$db->db_num_rows($erg_lekt);
	if ($lekt_num_rows==1)
	{
		$row=$db->db_fetch_object($erg_lekt,0);
		$kurzbz=$row->kurzbz;
		$tel=$row->telefonklappe;
		$ort=$row->ort_kurzbz;
		$vorwahl = '';
		if($tel != "")
		{
			$vorwahl = '+43 1 333 40 77-';
			if($row->standort_id!='')
			{
				$qry = "SELECT kontakt FROM public.tbl_kontakt WHERE standort_id=".$db->db_add_param($row->standort_id,FHC_INTEGER)." AND kontakttyp = 'telefon'";
				if($result_tel = $db->db_query($qry))
				if($row_tel = $db->db_fetch_object($result_tel))
					$vorwahl = $row_tel->kontakt;
			}
		}
	}

	// Mail-Groups
    if(isset($semester))
        $semester_qry = " and semester =".$db->db_add_param($semester, FHC_STRING);
    else
        $semester_qry = '';

	if(!($erg_mg=$db->db_query("SELECT gruppe_kurzbz, beschreibung FROM campus.vw_persongruppe WHERE mailgrp and uid=".$db->db_add_param($uid, FHC_STRING)." ".$semester_qry." ORDER BY gruppe_kurzbz")))
		die($db->db_last_error());
	$nr_mg=$db->db_num_rows($erg_mg);

	if ($count==0)
	{
		$html_user_daten.='<h1>Pers&ouml;nliche Daten</h1>';
		$html_user_daten.='<table>
					<tr>
						<td valign="top">
							<table>
				      			<tr>
									<td><h2>'.$anrede.' '.($titelpre?$titelpre.' ':'').$vorname.' '.$nachname.'</h2>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
					        		<td>&nbsp;</td>
								</tr>
							</table>
						</td>
					</tr>
		</table>';
	}

	if ($count==0)
		$html_user_daten.='<hr>';

// 	HTML Header mit den Benutzerdaten
	$html_user_daten.='<table width="100%">';
	$html_user_daten.='<tr><td colspan="4" style="background-color: #E9ECEE;">Informationen zu BenutzerIn <b>'.$uid.'</b></td></tr>';

		if ($aktiv=='f' || !$aktiv)
		{
			$html_user_daten.='<tr>';
				$html_user_daten.='<td colspan="2" style="color:red;"><b>Account nicht mehr AKTIV !</b></td>';
			$html_user_daten.='</tr>';
		}
		else
		{
			$html_user_daten.='<tr>';
			$html_user_daten.='<td colspan="2">Aktiv</td>';
			$html_user_daten.='</tr>';
		}
		$html_user_daten.='<tr><td valign="top" colspan="2" width="50%"><table width="100%">';
		$html_user_daten.='<tr><td style="background-color: #E9ECEE;" align="center" colspan="2" ><b><font size="+1">Email</font></b></td></tr>';
		$html_user_daten.='<tr><td ><b>Intern</b></td><td >'.$email.'</td></tr>';
		$html_user_daten.='<tr><td ><b>Alias</b></td><td >'.$email_alias.'</td></tr>';

		$html_user_daten.='</table>';
		$html_user_daten.='&nbsp;</td></tr></table>';
		return $html_user_daten;

}

#-------------------------------------------------------------------------------------------
/*
*
* @alle_uid_stundenplan_informationen Termine zur Auswahl Raumtype
*
* @param $db Aktuelle Datenbankverbindung
* @param $uid Userkurzzeichen
* @param $user_array Anwenderinformatinen in Tabellenform
*
* @return HTML Tablle des Anwenderkalenders
*
*/
function alle_uid_stundenplan_informationen($db,$uid,$user_array="")
{
	$html_liste_raum='';
	if ($db && (empty($user_array) || (!is_array($user_array) && !is_object($user_array))) )
	{
		$user_array=uid_read_mitarbeiter_oder_student($db,$uid);
	}
	if (empty($user_array) || (!is_array($user_array) && !is_object($user_array)) )
	{
		return $html_liste_raum;
	}

// ------------------------------------------------------------------------------------------
//	Alle Termine zum User lesen
// ------------------------------------------------------------------------------------------
	// Authentifizierung
	if (check_student($uid))
		$type='student';
	elseif (check_lektor($uid))
		$type='lektor';
	else
	{
		//die("Cannot set usertype!");
		//GastAccountHack
		$type='student';
	}

	// Stundenplan erstellen
	$stdplan=new wochenplan($type);
	// Benutzergruppe
	$stdplan->user=$type;
	// aktueller Benutzer
	$stdplan->user_uid=$uid;
	// Zusaetzliche Daten laden

	if (isset($user_array->studiengang_kz))
	{
	// Student
		if (! $stdplan->load_data($type,$uid,NULL,trim($user_array->studiengang_kz),trim($user_array->semester),trim($user_array->verband),trim($user_array->gruppe)) )
		{
			die($stdplan->errormsg);
		}
	}
	else
	{
	// Mitarbeiter
		if (! $stdplan->load_data($type,$uid) )
		{
			die($stdplan->errormsg);
		}
	}

	$datum=time();
	// Stundenplan einer Woche laden
	if (! $stdplan->load_week($datum))
	{
		die($stdplan->errormsg);
	}
	$ersterTagMonat=date('m', $stdplan->datum);
	$ersterTag=date('d', $stdplan->datum);
	$year=date('Y', $stdplan->datum);
	$weekday=date('w');
// ------------------------------------------------------------------------------------------
//	Stunden lesen
// ------------------------------------------------------------------------------------------
	$row_stunde=array();
	$qry="SELECT stunde, beginn, ende FROM lehre.tbl_stunde ORDER BY stunde";
	if(!$result=$db->db_query($qry))
			die('Probleme beim lesen der Stundentabelle '.$db->db_last_error());
	$num_rows_stunde=$db->db_num_rows();
	while($row_stunden = $db->db_fetch_object())
	{
		$row_stunden->time_beginn=mktime(mb_substr($row_stunden->beginn, 0,2),mb_substr($row_stunden->beginn, 3,2));
		$row_stunden->time_ende=mktime(mb_substr($row_stunden->ende, 0,2),mb_substr($row_stunden->ende, 3,2));
		$row_stunden->beginn_show=mb_substr($row_stunden->beginn, 0,5);
		$row_stunden->ende_show=mb_substr($row_stunden->ende, 0,5);
		$row_stunde[]=$row_stunden;
	}

// ------------------------------------------------------------------------------------------
//	Tabelle alle Termine zum User anzeigen
// ------------------------------------------------------------------------------------------
	$html_liste_raum.='<a name="top"><h1>Pers&ouml;nlicher LV-Plan von '.(isset($user_array->name)?$user_array->name:$uid).' ('.$uid.') '. strftime(constHeaderStundenplan,mktime(0, 0, 0, $ersterTagMonat,$ersterTag, $year)).' </h1>';
	$html_liste_raum.='<table cellspacing="1" cellpadding="1" class="stundenplan"></a>';

	// Datum-Header
	$html_liste_raum.='<tr>';
	$html_liste_raum.='<th><a href="#bottom"><img src="go-bottom.png" border="0">&nbsp;</a>Zeit</th>';
	$lektor_max=0;
	// Datumszeile
	for ($ii=0;$ii<TAGE_PRO_WOCHE;$ii++)
	{
		$aktiverWochentag=date('w', mktime(0, 0, 0, $ersterTagMonat, $ersterTag + $ii, $year));
		$aktiverTag=strftime(constHeaderStundenplanTag,mktime(0, 0, 0, $ersterTagMonat,$ersterTag+ $ii, $year));
		$html_liste_raum.='<th>'.$aktiverTag.'</th>';
	}
	$html_liste_raum.='</tr>';
// ------------------------------------------------------------------------------------------
//	Stundenplanaufbau
// ------------------------------------------------------------------------------------------
	reset($row_stunde);
	for ($i=0;$i<count($row_stunde);$i++)
	{
	// Zeile je Stundeneinteilung
		$html_liste_raum.='<tr>';
		// Stunden Linker Rand - Erste Spalte
		if ($row_stunde[$i]->time_beginn<=time() && $row_stunde[$i]->time_ende>=time())
			$html_liste_raum.='<td class="stundenplan_stunden_detail" style="color:black;letter-spacing : 2px;background-color:#E9ECEE;border : 1px solid Black;">'.$row_stunde[$i]->beginn_show.'-'.$row_stunde[$i]->ende_show.'</td>';
		else
			$html_liste_raum.='<td class="stundenplan_stunden_detail">'.$row_stunde[$i]->beginn_show.'-'.$row_stunde[$i]->ende_show.'</td>';
		// ------------------------------------------------------------------------------------------------------------
		// Pausenzeiten werden zur naechsten Std. gerechnet als Aktuellezeit
		// dh. Letztes Ende ist gleich Start aktueller Datensatz
		if ($i && $row_stunde[$i - 1]->time_ende && $row_stunde[$i]->time_beginn!=$row_stunde[$i - 1]->time_ende)
		{
			 $row_stunde[$i]->time_beginn=$row_stunde[$i - 1]->time_ende;
		}

		// Je Tage die Stunden ausgeben
		for ($ii=0;$ii<TAGE_PRO_WOCHE;$ii++)
		{

			$aktiverWochentag=date('w', mktime(0, 0, 0, $ersterTagMonat, $ersterTag + $ii, $year));
			$aktiverTag=strftime(constHeaderStundenplanTag,mktime(0, 0, 0, $ersterTagMonat,$ersterTag+ $ii, $year));
			$aktiverDatumseintrag=date('Ymd',mktime(0, 0, 0, $ersterTagMonat,$ersterTag+ $ii, $year));
			$aktiverTag=strftime(constHeaderStundenplanTag,mktime(0, 0, 0, $ersterTagMonat,$ersterTag+ $ii, $year));
			$aktiverWochentag=date('w', mktime(0, 0, 0, $ersterTagMonat, $ersterTag + $ii, $year));

			$zeit_aktuell=false;
			if ($weekday==$aktiverWochentag && $row_stunde[$i]->time_beginn<=time() && $row_stunde[$i]->time_ende>=time())
			{
				$zeit_aktuell=true;
			}

			if ($zeit_aktuell)
			{
				$html_liste_raum.='<td class="stundenplan_detail_kpl_aktuell"><table width="100%" cellspacing="1" cellpadding="0" border="0">';
			}
			else
			{
				$html_liste_raum.='<td class="stundenplan_detail_kpl_normal"><table width="100%" cellspacing="1" cellpadding="0" border="0">';
			}


			$gef_raum_einteilung_check=false;
			$TagInd=$ii + 1;
			$StdInd=$i + 1;

			$lektor='';
			$lektor_anz=0;

			if (isset($stdplan->std_plan[$TagInd]) && isset($stdplan->std_plan[$TagInd][$StdInd]) && isset($stdplan->std_plan[$TagInd][$StdInd][0]->lehrfach))
			{
				foreach ($stdplan->std_plan[$TagInd][$StdInd] as $lehrstunde)
				{
					//if (!isset($lehrstunde->reservierung) || ($lehrstunde->reservierung && $type!='lektor') )
					//	continue;

					if (!$gef_raum_einteilung_check)
						$gef_raum_einteilung_check=$lehrstunde;
					$lektor.=(isset($lehrstunde->lektor) && !empty($lehrstunde->lektor)?trim($lehrstunde->lektor).'<br>':'tw-0');
					$lektor_anz++;
					if ($lektor_max<$lektor_anz)
						$lektor_max=$lektor_anz;
				}
			}
			if ($gef_raum_einteilung_check)
			{
				if ($gef_raum_einteilung_check->reservierung)
					$lehrstunde=trim($gef_raum_einteilung_check->titel).'<br>';
				else
					$lehrstunde=trim($gef_raum_einteilung_check->lehrfach).'-'.trim($gef_raum_einteilung_check->lehrform).'<br>';

				$ort=(isset($gef_raum_einteilung_check->ort) && !empty($gef_raum_einteilung_check->ort)?trim($gef_raum_einteilung_check->ort).'<br>':'');
				$farbe=(isset($gef_raum_einteilung_check->farbe) && !empty($gef_raum_einteilung_check->farbe)?$gef_raum_einteilung_check->farbe:'');
 				$html_liste_raum.='<tr><td '.($zeit_aktuell?' class="stundenplan_detail_aktuell" ':' class="stundenplan_detail_normal" ') .' '.(!empty($farbe)? ' style="background-color:#'.$farbe.';" ':'').'>';
#					$html_liste_raum.=$TagInd.'**'.$StdInd .'<br>'.$lehrstunde.$lektor.$ort;
					$html_liste_raum.=$lehrstunde.$lektor.($lektor_anz>1?'':'{***}')."<b>$ort</b>";
				$html_liste_raum.='</td></tr>';
			}
			else
			{
				$html_liste_raum.='<tr><td '.($zeit_aktuell?' class="stundenplan_detail_aktuell" ':' class="stundenplan_detail_normal" ') .' >&nbsp;</td></tr>';
			}
			$html_liste_raum.='</table></td>';
		}
		$html_liste_raum.='</tr>';
	}
	$html_liste_raum=($lektor_max>1?str_replace('{***}','<br>',$html_liste_raum):str_replace('{***}','',$html_liste_raum));
	$html_liste_raum.='<tr><th><a href="#top"><img src="go-top.png" border="0">&nbsp;</a>Top<a name="bottom">&nbsp;</a></th><th colspan="7">&nbsp;</th></tr>';
	$html_liste_raum.='</table>';
	return $html_liste_raum;
}
#-------------------------------------------------------------------------------------------
/*
*
* @alle_raum_informationen Rauminformation zur Auswahl Raumtype
*
* @param $db Aktuelle Datenbankverbindung
* @param $raumtyp_kurzbz Raumtyp
* @param $ort_kurzbz Detailanzeige Stundenplan eines Raums
*
* @return HTML Tablle der Raumtypen
*
*/
function alle_raum_informationen($db,$raumtyp_kurzbz,$ort_kurzbz, $standort_id)
{
	// HTML Init - Raumliste - Tabelle
	$html_liste_raum='';
	// Header - Raumliste - Tabelle
	$html_liste_raum.='<h1>'. strftime(constHeaderDatumZeit,time()).'</h1>';
	// DB Verbindung pruefen, Plausib - ohne Verbindung Header anzeigen
	if (!$db)
		return $html_liste_raum;

// ------------------------------------------------------------------------------------------
//	Alle Raum Typen zur Selektion
// ------------------------------------------------------------------------------------------
	$row_raum=array();
	$row_raum_aktiv=array();
	$row_raum_alle=array();

	$qry="";
	$qry.=" SELECT DISTINCT ";
	$qry.=" tbl_ortraumtyp.ort_kurzbz ";
	$qry.=" ,tbl_ort.bezeichnung ,tbl_ort.aktiv ";
	$qry.=" , (SELECT 'gesperrt'::text FROM public.tbl_ortraumtyp WHERE raumtyp_kurzbz='Gesperrt' AND ort_kurzbz=tbl_ort.ort_kurzbz) as gesperrt";
	$qry.=" FROM tbl_raumtyp , tbl_ortraumtyp , tbl_ort ";
	$qry.=" WHERE tbl_ortraumtyp.raumtyp_kurzbz=tbl_raumtyp.raumtyp_kurzbz ";
	$qry.=" AND tbl_ort.ort_kurzbz=tbl_ortraumtyp.ort_kurzbz ";
	$qry.=" AND tbl_ort.aktiv ";
	$qry.=" AND lower(tbl_raumtyp.raumtyp_kurzbz) like lower('%".$db->db_escape(trim($raumtyp_kurzbz))."%') ";
	if($standort_id!='')
		$qry.=" AND (tbl_ort.standort_id=".$db->db_add_param($standort_id, FHC_INTEGER)." OR tbl_ort.standort_id is null)";
	$qry.=" order by tbl_ortraumtyp.ort_kurzbz ";
	$qry.=" ; ";

	if(!$result=$db->db_query($qry))
			die('Probleme beim lesen der Raumtyptabelle ');
	$num_rows_stunde=$db->db_num_rows($result);

	if($num_rows_stunde==0)
		return "Derzeit sind hier keine Eintraege vorhanden";

	while($tmp_row_raum = $db->db_fetch_object($result))
	{
		// Wenn noch kein Raum gewaehlt wurde den ersten als Default nehmen
		if (!trim($ort_kurzbz))
			$ort_kurzbz=$tmp_row_raum->ort_kurzbz;

		// Aktiven Raum Anzeigen
		if (trim($ort_kurzbz)==trim($tmp_row_raum->ort_kurzbz))
			$row_raum_aktiv=$tmp_row_raum;

		$row_raum_alle[]=$tmp_row_raum->ort_kurzbz;
		$row_raum[]=$tmp_row_raum;
	}

	if (count($row_raum_aktiv)<1)
		$row_raum_aktiv=$row_raum[0];

	// --------------------------------------------------------------
	// Raumreservierungen fuer Aktive Raumauswahl
	// --------------------------------------------------------------

	// Die aktive Stunde ermitteln - zum lesen welcher Raum jetzt besetzt ist - aktive Lehreinheit
	$row_stunde=array();

	$qry="";
	$qry.="SELECT stunde, beginn, ende ";
	$qry.=" FROM lehre.tbl_stunde ";
	$qry.=" WHERE ".$db->db_add_param(constAktuelleZeitHHMi,FHC_STRING)." between to_char(tbl_stunde.beginn, 'HH24MI') and  to_char(tbl_stunde.ende, 'HH24MI') ";
	$qry.=" ORDER BY stunde LIMIT 1 ; ";

	if(!$result=$db->db_query($qry))
			die('Probleme beim lesen der Raumtyptabelle '.$db->db_last_error());

	// In einer Pause wird kein Datensatz gefunden, den letzten holen
	if (!$num_rows_stunde=$db->db_num_rows($result))
	{
		$qry="";
		$qry.="SELECT stunde, beginn, ende ";
		$qry.=" FROM lehre.tbl_stunde ";
		$qry.=" WHERE ".$db->db_add_param(constAktuelleZeitHH,FHC_STRING)." between to_char(tbl_stunde.beginn, 'HH24') and  to_char(tbl_stunde.ende, 'HH24') ";
		$qry.=" ORDER BY stunde LIMIT 1; ";
		if(!$result=$db->db_query($qry))
			die('Probleme beim lesen der Raumtyptabelle '.$db->db_last_error());
	}

	while($tmp_row_stunde = $db->db_fetch_object($result))
		$row_stunde[]=$tmp_row_stunde;

	// Plausib Stunde
	if(!isset($row_stunde[0]))
		$row_stunde[0]=new stdClass();

	$row_stunde[0]->stunde=(isset($row_stunde[0]) && isset($row_stunde[0]->stunde)?$row_stunde[0]->stunde:0);

	$html_liste_raum.='<table class="raum_auswahlliste">';
	$html_liste_raum.='<tr>';
	reset($row_raum);
	for ($i=0;$i<count($row_raum);$i++)
	{

		// Default
		$farbe="orange";

		$ort_kurzbz=$row_raum[$i]->ort_kurzbz;
		$datum=date("Y-m-d", mktime(0,0,0,date("m"),date("d"),date("y")));
		$stunde_von=$row_stunde[0]->stunde;
		$stunde_bis=$row_stunde[0]->stunde;
		if ($info=stundenplan_raum($db,$ort_kurzbz,$datum,$stunde_von,$stunde_bis))
		{
			$farbe="rot";
		}

		$ort_kurzbz=$row_raum[$i]->ort_kurzbz;
		$datum=date("Y-m-d", mktime(0,0,0,date("m"),date("d"),date("y")));
		$stunde_von=$row_stunde[0]->stunde;
		$stunde_bis=$row_stunde[0]->stunde + 1;
		if (!$info=stundenplan_raum($db,$ort_kurzbz,$datum,$stunde_von,$stunde_bis))
		{
			$farbe="gruen";
		}

		if ($row_raum[$i]->gesperrt=="gesperrt")
			$farbe="rot";

		// Nach 4 Raumanzeigen eine Neuezeile erzeugen
		$html_liste_raum.=($i==0 || $i%4?"":"</tr><tr>");
		$html_liste_raum.='<td>';
			$html_liste_raum.='<a href="'.$_SERVER['PHP_SELF'].'?raumtyp_kurzbz='.$raumtyp_kurzbz.'&amp;ort_kurzbz='.$ort_kurzbz.'&amp;standort_id='.$standort_id.'">';
			$html_liste_raum.='<span class="'.$farbe.'_mitteText">';
			$html_liste_raum.=trim($ort_kurzbz);
			$html_liste_raum.='
					</span>';
		$html_liste_raum.='</a>';

		$html_liste_raum.='</td>';
	}
	$html_liste_raum.='</tr>';
	$html_liste_raum.='</table>';

	// Legende
	$html_liste_raum.='<br>';
	$html_liste_raum.='<table width="100%" cellpadding="0" cellspacing="0">';
	$html_liste_raum.='<tr>';

		$html_liste_raum.='<td><table><tr><td><span class="gruen_mitteText">&nbsp;&nbsp;&nbsp;</span></td><td>Mindestens n&auml;chsten 2 Einheiten frei</td></tr></table></td>';
		$html_liste_raum.='<td><table><tr><td><span class="orange_mitteText">&nbsp;&nbsp;&nbsp;</span></td><td>Derzeit frei</td></tr></table></td>';
		$html_liste_raum.='<td><table><tr><td><span class="rot_mitteText">&nbsp;&nbsp;&nbsp;</span></td><td>Raum derzeit besetzt / gesperrt</td></tr></table></td>';
	$html_liste_raum.='</tr>';
	$html_liste_raum.='</table>';
	$html_liste_raum.='<hr>';

	// Aktiver Raum Haederinformation
	$html_liste_raum.='<h1>'.$row_raum_aktiv->ort_kurzbz.'&nbsp;&nbsp;-&nbsp;&nbsp;'.strftime(constRaumDatumZeit,time()).'&nbsp;&nbsp;<span style="font-size:small;">'.$row_raum_aktiv->bezeichnung.'</span>'.'</h1>';

	$ort_kurzbz=$row_raum_aktiv->ort_kurzbz;
	$datum=date("Ymd", mktime(0,0,0,date("m"),date("d"),date("y")));

	$stunde_von=0;
	$stunde_bis=99;
	if (!$row_raum_plan=stundenplan_raum($db,$ort_kurzbz,$datum,$stunde_von,$stunde_bis))
	{
		$row_raum_plan=array();
		$html_liste_raum."<br> keine Reservierungen ".$datum;
	}

	$html_liste_raum.='<table class="raum_liste" cellpadding="1" cellspacing="1">';
	$html_liste_raum.='<tr>';

	// Stundentabelle lesen
	$row_stunde=array();
	$qry="SELECT stunde, beginn, ende FROM lehre.tbl_stunde ORDER BY stunde";
	if(!$result=$db->db_query($qry))
			die('Probleme beim lesen der Stundentabelle '.$db->db_last_error());

	$lastEnde=0;
	$num_rows_stunde=$db->db_num_rows();
	while($row = $db->db_fetch_object())
	{
		$row->time_beginn=mktime(mb_substr($row->beginn, 0,2),mb_substr($row->beginn, 3,2));
		$row->time_ende=mktime(mb_substr($row->ende, 0,2),mb_substr($row->ende, 3,2));

		$row->beginn_kurz=mb_substr($row->beginn, 0,5);
		$row->ende_kurz=mb_substr($row->ende, 0,5);

		$row->beginn_show=substr($row->beginn, 0,5);
		$row->ende_show=substr($row->ende, 0,5);

		// Pausenzeiten werden zur naechsten Std. gerechnet als Aktuellezeit
		// dh. Letztes Ende ist gleich Start aktueller Datensatz
		if ($lastEnde && $row->time_beginn!=$lastEnde)
		{
			 $row->time_beginn=$lastEnde;
		}

		// Aktuelle Stunde kennzeichnen
		$row->aktuell=false;
		if ($row->time_beginn<=time() && $row->time_ende>=time())
			$row->aktuell=true;
		else
			$row->aktuell=false;
		$row_stunde[]=$row;
	}

	// zur Stundentabelle die Rauminformationen lesen
	for ($i=0;$i<count($row_stunde);$i++)
	{
		$row=$row_stunde[$i];

		$tageshelfte=$num_rows_stunde/2;
		$html_liste_raum.=($i==0 || $i%$tageshelfte?"":"</tr><tr>");

		$html_liste_raum.='<td valign="top"><table class="raum_liste_detail" cellpadding="0" cellspacing="0">';
		if ($row->aktuell)
		{
			$html_liste_raum.='<tr><th style="color:black;letter-spacing : 2px;background-color:#E9ECEE; border : 1px solid Black;">'.trim($row->beginn_kurz)."<br>".trim($row->ende_kurz).'</th></tr>';
		}
		else
		{
			$html_liste_raum.='<tr><th >'.trim($row->beginn_kurz)."<br>".trim($row->ende_kurz).'</th></tr>';
        }

		reset($row_raum_plan);
		$gef_raum_einteilung=array();
		for ($ii=0;$ii<count($row_raum_plan);$ii++)
		{
			if ($row->stunde!=$row_raum_plan[$ii]->stunde)
			{
				continue;
			}
			$gef_raum_einteilung=$row_raum_plan[$ii];
			// Stundenplan Detail lesen
			if (isset($gef_raum_einteilung->stundenplan_id) && !empty($gef_raum_einteilung->stundenplan_id))
			{
				$gef_raum_einteilung->infotext='Fehler lesen Stundenplan '.$gef_raum_einteilung->stundenplan_id;
				// Details wurden bereits gelesen
				if (isset($gef_stundenplan_detail) && isset($gef_stundenplan_detail->stundenplan_id) && $gef_stundenplan_detail->stundenplan_id==$gef_raum_einteilung->stundenplan_id)
				{
					$gef_raum_einteilung->infotext=$gef_stundenplan_detail->lehrfach.'-'.$gef_stundenplan_detail->lehrform.'<br>'.$gef_stundenplan_detail->lektor.'<br>'.mb_strtoupper(trim($gef_stundenplan_detail->stg_typ).trim($gef_stundenplan_detail->stg_kurzbz)).'-'.$gef_stundenplan_detail->semester.$gef_stundenplan_detail->verband;
				}
				// Detail lesen
				elseif ($gef_stundenplan_detail=stundenplan_detail($db,$gef_raum_einteilung->stundenplan_id))
				{
					if (isset($gef_stundenplan_detail->lehrfach))
						$gef_stundenplan_detail->lehrfach=trim(str_replace(array('<br>','<br>',"\n\r","\n"),'',$gef_stundenplan_detail->lehrfach));
					if (isset($gef_stundenplan_detail->lehrform))
						$gef_stundenplan_detail->lehrform=trim(str_replace(array('<br>','<br>',"\n\r","\n"),'',$gef_stundenplan_detail->lehrform));
					if (isset($gef_stundenplan_detail->lektor))
						$gef_stundenplan_detail->lektor=trim(str_replace(array('<br>','<br>',"\n\r","\n"),'',$gef_stundenplan_detail->lektor));
					if (isset($gef_stundenplan_detail->stg_kurzbzlang))
						$gef_stundenplan_detail->stg_kurzbzlang=trim(str_replace(array('<br>','<br>',"\n\r","\n"),'',$gef_stundenplan_detail->stg_kurzbzlang));

					$img_sticky='';
					$gef_stundenplan_detail->titel=trim($gef_stundenplan_detail->titel);
					if(!empty($gef_stundenplan_detail->titel) )
						$img_sticky=' <img src="../../skin/images/sticky.png" tooltip="'.$gef_stundenplan_detail->titel.'"/>';

					$gef_raum_einteilung->infotext=$gef_stundenplan_detail->lehrfach.'-'.$gef_stundenplan_detail->lehrform.$img_sticky.'<br>'.$gef_stundenplan_detail->lektor.'<br>'.$gef_stundenplan_detail->stg_kurzbzlang.'-'.$gef_stundenplan_detail->semester.$gef_stundenplan_detail->verband;
				}

				if (isset($gef_stundenplan_detail->farbe) && !empty($gef_stundenplan_detail->farbe) )
				{
					$gef_raum_einteilung->farbe=$gef_stundenplan_detail->farbe;
				}
			}
			// Reservierung Detail
			if (isset($gef_raum_einteilung->reservierung_id) && !empty($gef_raum_einteilung->reservierung_id))
			{
				$gef_raum_einteilung->infotext='Fehler lesen Reservierung '.$gef_raum_einteilung->reservierung_id;
				// Details wurden bereits gelesen
				if (isset($gef_stundenplan_detail) && isset($gef_stundenplan_detail->reservierung_id) && $gef_stundenplan_detail->reservierung_id==$gef_raum_einteilung->reservierung_id)
				{
					$gef_raum_einteilung->infotext=(!empty($gef_stundenplan_detail->titel)?$gef_stundenplan_detail->titel.'<br>':'').(!empty($gef_stundenplan_detail->uid)?$gef_stundenplan_detail->uid.'<br>':'').$gef_stundenplan_detail->beschreibung;
				}
				// Detail lesen
				elseif ($gef_stundenplan_detail=reservierung_detail($db,$gef_raum_einteilung->reservierung_id))
				{
					if (isset($gef_stundenplan_detail->titel))
						$gef_stundenplan_detail->titel=trim(str_replace(array('<br>','<br>',"\n\r","\n"),'',$gef_stundenplan_detail->titel));
					if (isset($gef_stundenplan_detail->beschreibung))
						$gef_stundenplan_detail->beschreibung=trim(str_replace(array('<br>','<br>',"\n\r","\n"),'',$gef_stundenplan_detail->beschreibung));
					if (isset($gef_stundenplan_detail->uid))
						$gef_stundenplan_detail->uid=trim(str_replace(array('<br>','<br>',"\n\r","\n"),'',$gef_stundenplan_detail->uid));
					$gef_raum_einteilung->infotext=(!empty($gef_stundenplan_detail->titel)?$gef_stundenplan_detail->titel.'<br>':'').'<br>'.(!empty($gef_stundenplan_detail->uid)?$gef_stundenplan_detail->uid.'<br>':'').$gef_stundenplan_detail->beschreibung;
				}
				if (isset($gef_stundenplan_detail->farbe) && !empty($gef_stundenplan_detail->farbe) )
				{
					$gef_raum_einteilung->farbe=$gef_stundenplan_detail->farbe;
				}
			}
		}
		$html_liste_raum.='<tr><td '.($row->aktuell?' class="raum_liste_detail_stundenplan_aktuell" ':' class="raum_liste_detail_stundenplan_normal"  ') .' '. (isset($gef_raum_einteilung->farbe)?' style="background-color:#'.$gef_raum_einteilung->farbe.'" ':'').'>'.(isset($gef_raum_einteilung->infotext) && $gef_raum_einteilung->infotext? $gef_raum_einteilung->infotext :'&nbsp;<br><br>').'&nbsp;</td></tr>';
		$html_liste_raum.='</table>';
	}
	$html_liste_raum.='</tr>';
	$html_liste_raum.='</table>';
	$html_liste_raum.='<div align="right"><table><tr><td style="color:black;background-color:#E9ECEE;border : 1px solid Black;">&nbsp;&nbsp;&nbsp;</td><td>Aktuelle Einheit</td></tr></table></div>';
	return $html_liste_raum;
}

#-------------------------------------------------------------------------------------------
/*
*
* @alle_rauminformationen Rauminformation zur Auswahl Raumtype
*
* @param $db Aktuelle Datenbankverbindung
* @param $ort_kurzbz Detailanzeige Stundenplan eines Raums Optional
* @param $datum Datum der Raumres. in Form von JJJJMMTT  Optional
* @param $row_stunde_von Stundenplan ab  Optional
* @param $row_stunde_bis Stundenplan ab Optonal

* @param $uid UserUid Optional
* @param $kalenderwoche Kalenderwoche Optional
* @param $studiengang_kz Studienkennzeichen Optional
* @param $semester Semester Optional
* @param $verband="" Verbandskennzeichen Optional
* @param $gruppe Verband-Gruppe Optional

*
* @return array Tablle der Rauminformation
*
*/
function stundenplan_raum($db,$ort_kurzbz="",$datum="",$stunde_von,$stunde_bis=0,$uid="",$kalenderwoche="",$studiengang_kz="",$semester="",$verband="",$gruppe="")
{
	// Plausib
	if (!$db)
		return array();

	if (empty($stunde_bis))
		$stunde_bis=$stunde_von;

	//--- Raumbelegung jetzt
	$qry="";
	$qry.=' SELECT studiengang_kz,0 as "stundenplan_id",tbl_reservierung.reservierung_id,tbl_reservierung.ort_kurzbz,tbl_reservierung.titel,tbl_reservierung.semester,tbl_reservierung.studiengang_kz,tbl_reservierung.verband, tbl_reservierung.gruppe  , to_char(tbl_reservierung.datum, \'YYYYMMDD\') as "datum_jjjjmmtt", to_char(tbl_reservierung.datum, \'IW\') as "datum_woche" , tbl_stunde.beginn, tbl_stunde.ende , to_char(tbl_stunde.beginn, \'HH24:MI\') as "beginn_anzeige" , to_char(tbl_stunde.ende, \'HH24:MI\') as "ende_anzeige" , EXTRACT(EPOCH FROM tbl_reservierung.datum) as "datum_timestamp" ,tbl_stunde.stunde ';
	$qry.=' FROM campus.tbl_reservierung , lehre.tbl_stunde ';
	$qry.=" WHERE tbl_stunde.stunde=tbl_reservierung.stunde  ";
	$qry.=" and tbl_reservierung.stunde between ". $db->db_add_param(trim($stunde_von), FHC_STRING) ." and ". $db->db_add_param(trim($stunde_bis), FHC_STRING) ;

	$datum_obj = new datum();
	if (!empty($datum))
	{
		$qry.=" and  tbl_reservierung.datum =".$db->db_add_param(trim($datum), FHC_STRING);
	}
	if (!empty($kalenderwoche))
	{
		$qry.=" and  to_char(tbl_reservierung.datum, 'IW') =".$db->db_add_param(trim($kalenderwoche), FHC_STRING);
	}
	if (!empty($ort_kurzbz))
	{
		$qry.=" and  ort_kurzbz=".$db->db_add_param(trim($ort_kurzbz), FHC_STRING);
	}
	if (!empty($uid) || $uid=='0')
	{
		$qry.=" and uid=".$db->db_add_param(trim($uid), FHC_STRING);
	}
	if (!empty($studiengang_kz) || $studiengang_kz=='0')
	{
		$qry.=" and studiengang_kz=".$db->db_add_param($studiengang_kz, FHC_STRING);
	}
	if (!empty($semester) || $semester=='0')
	{
		$qry.=" and semester=".$db->db_add_param($semester, FHC_STRING);
	}
	if (!empty($verband) || $verband=='0')
	{
		$qry.=" and verband=".$db->db_add_param(trim($verband), FHC_STRING);
	}
	if (!empty($gruppe) || $gruppe=='0')
	{
		$qry.=" and gruppe=".$db->db_add_param($gruppe, FHC_STRING);
	}

	$qry.=" UNION ";
	$qry.=' SELECT studiengang_kz,tbl_stundenplan.stundenplan_id,0 as "reservierung_id", tbl_stundenplan.ort_kurzbz,tbl_stundenplan.titel,tbl_stundenplan.semester,tbl_stundenplan.studiengang_kz,tbl_stundenplan.verband ,tbl_stundenplan.gruppe  , to_char(tbl_stundenplan.datum, \'YYYYMMDD\') as "datum_jjjjmmtt", to_char(tbl_stundenplan.datum, \'IW\') as "datum_woche" , tbl_stunde.beginn, tbl_stunde.ende , to_char(tbl_stunde.beginn, \'HH24:MI\') as "beginn_anzeige" , to_char(tbl_stunde.ende, \'HH24:MI\') as "ende_anzeige" , EXTRACT(EPOCH FROM tbl_stundenplan.datum) as "datum_timestamp"  ,tbl_stunde.stunde  ';
	$qry.=' FROM lehre.tbl_stundenplan , lehre.tbl_stunde  ';
	$qry.=" WHERE tbl_stunde.stunde=tbl_stundenplan.stunde ";
	$qry.=" and tbl_stundenplan.stunde between ".$db->db_add_param(trim($stunde_von), FHC_STRING)." and ".$db->db_add_param(trim($stunde_bis), FHC_STRING);

	if (!empty($datum))
	{
		$qry.=" and  tbl_stundenplan.datum =".$db->db_add_param(trim($datum), FHC_STRING);
	}
	if (!empty($kalenderwoche))
	{
		$qry.=" and  to_char(tbl_stundenplan.datum, 'IW') =".$db->db_add_param(trim($kalenderwoche), FHC_STRING);
	}
	if (!empty($ort_kurzbz))
	{
		$qry.=" and  ort_kurzbz =E".$db->db_add_param(trim($ort_kurzbz), FHC_STRING);
	}
	if (!empty($uid) || $uid=='0')
	{
		$qry.=" and mitarbeiter_uid=".$db->db_add_param(trim($uid), FHC_STRING);
	}
	if (!empty($studiengang_kz) || $studiengang_kz=='0')
	{
		$qry.=" and studiengang_kz=".$db->db_add_param($studiengang_kz, FHC_STRING);
	}
	if (!empty($semester) || $semester=='0')
	{
		$qry.=" and semester=".$db->db_add_param($semester, FHC_STRING);
	}
	if (!empty($verband) || $verband=='0')
	{
		$qry.=" and verband=E".$db->db_add_param(trim($verband), FHC_STRING);
	}
	if (!empty($gruppe) || $gruppe=='0')
	{
		$qry.=" and gruppe=".$db->db_add_param($gruppe, FHC_STRING);
	}
	$qry.=" ; ";

	$row_raum_belegt=array();

	if(!$result=$db->db_query($qry))
		die('Probleme beim lesen der Stundenplan '.$db->db_last_error());

	if (!$num_rows_stunde=$db->db_num_rows($result))
		return $row_raum_belegt;

	while($row = $db->db_fetch_object($result))
	{
		$row_raum_belegt[]=$row;
	}
	return $row_raum_belegt;
}

#-------------------------------------------------------------------------------------------
/*
*
* @stundenplan_detail Stundenplan mit Lehrveranstaltungsinformationen
*
* @param $db Aktuelle Datenbankverbindung
* @param $stundenplan_id StundenplanID
*
* @return array Tablle des Stundenplan im Detail
*
*/
function stundenplan_detail($db,$stundenplan_id)
{
	$row_stundenplan_detail=false;
	if (!$db || empty($stundenplan_id))
		return $row_stundenplan_detail;
	//--- Raumbelegung jetzt
	$qry=' SELECT * FROM campus.vw_stundenplan ';
	$qry.=" WHERE vw_stundenplan.stundenplan_id=".$db->db_add_param($stundenplan_id, FHC_INTEGER);
	$qry.=" ORDER BY datum,stunde  ";
	if(!$result=$db->db_query($qry))
		die('Probleme beim lesen der Stundenplan '.$db->db_last_error());
	if (!$num_rows_stunde=$db->db_num_rows())
		return $row_stundenplan_detail;
	while($row = $db->db_fetch_object())
		$row_stundenplan_detail=$row;
	return $row_stundenplan_detail;
}
#-------------------------------------------------------------------------------------------
/*
*
* @reservierung_detail Stundenplan mit Reservierungsinformationen
*
* @param $db Aktuelle Datenbankverbindung
* @param $reservierung_id ReservierungID
*
* @return array Tablle des Reservierung im Detail
*
*/
function reservierung_detail($db,$reservierung_id)
{
	$row_reservierung_detail=false;
	if (!$db || empty($reservierung_id))
		return $row_reservierung_detail;
	//--- Reservierung jetzt
	$qry=' SELECT * FROM campus.vw_reservierung ';
	$qry.=' WHERE vw_reservierung.reservierung_id='.$db->db_add_param($reservierung_id, FHC_INTEGER);
	if(!$result=$db->db_query($qry))
		die('Probleme beim lesen der Stundenplan '.$db->db_last_error());
	if (!$num_rows_stunde=$db->db_num_rows($result))
		return $row_reservierung_detail;
	while($row = $db->db_fetch_object($result))
		$row_reservierung_detail=$row;
	return $row_reservierung_detail;
}
#-------------------------------------------------------------------------------------------
/*
*
* @html_output_liste_raumtypen Tabellenliste der Raumtypen
*
* @param $array Raumtyp,Beschreibung
*
* @return HTML Tablle der Raumtypen
*
*/
function html_output_liste_raumtypen($row_ort)
{
	global $standort_id;
	$html_liste_orte='';
	if (!is_array($row_ort) || count($row_ort)<1)
		return $html_liste_orte;

	for ($i=0;$i<count($row_ort);$i++)
	{
		$html_liste_orte.='<tr>';
			$html_liste_orte.='<td>';
			$html_liste_orte.='<a href="'.$_SERVER['PHP_SELF'].'?raumtyp_kurzbz='.trim($row_ort[$i]["type"]).'&amp;standort_id='.$standort_id.'">';
			$html_liste_orte.='<span class="blau_mitteText">';
			$html_liste_orte.=trim($row_ort[$i]["beschreibung"]);
			$html_liste_orte.='</span>';
		$html_liste_orte.='</a>';
		$html_liste_orte.='</td>';
		$html_liste_orte.='</tr>';
	}
	return $html_liste_orte;
}
#-------------------------------------------------------------------------------------------
/*
*
* @alle_uid_stundenplan_informationen Termine zur Auswahl Raumtype
*
* @param $db Aktuelle Datenbankverbindung
* @param $uid Userkurzzeichen
* @param $pwd Password
*
* @return true wenn Fehler oder false wenn LDAP Inormationen zum User gefunden wurde
*
*/
function ldap_uid_check($uid,$pwd="")
{
	if($pwd=='')
		return "Es wurde kein Passwort eingetragen";

	// eventuelle Daten vom Vorgaenger loeschen - sicherstellen das Initial ist
	if (isset($_SESSION[constSESSIONNAME]))
		unset($_SESSION[constSESSIONNAME]);

	// Check User vorhanden ist ( Password wenn Online eingabe ), ansonst zurueck
	if (empty($uid))
		return "Benutzername fehlt!";

	$auth = new authentication();
	if($auth->checkpassword($uid, $pwd))
		return false;
	else
		return "Login fehlgeschlagen ".$auth->errormsg;
}
#-------------------------------------------------------------------------------------------
#-------------------------------------------------------------------------------------------
/*
*
* @uid_read_mitarbeiter_oder_student Daten zum Mitarbeiter oder Studenten
*
* @param $db Aktuelle Datenbankverbindung
* @param $uid Userkurzzeichen
*
* @return Array der User Inormationen wenn User gefunden wurde ansonst false
*
*/
function uid_read_mitarbeiter_oder_student($db,$uid)
{
	$rows=array();
	// Plausib
	if (!$db)
		return $rows;

	// Pruefen ob Mitarbeiter
	$qry="SELECT uid,person_id,anrede,titelpre,vorname,vornamen,nachname,aktiv FROM campus.vw_mitarbeiter where uid=".$db->db_add_param(trim($uid), FHC_STRING)." LIMIT 1 ; ";
	if(!$results=$db->db_query($qry))
		die('Probleme beim lesen der Mitarbeiter '.$db->db_last_error());

	if ($num_rows_stunde=$db->db_num_rows($results))
	{
		while($rows = $db->db_fetch_object($results))
		{
			$rows->name='';
			$rows->name.=(isset($rows->anrede)?trim($rows->anrede).' ':'');
			$rows->name.=(isset($rows->titelpre)?trim($rows->titelpre).' ':'');
			$rows->name.=(isset($rows->vorname)?trim($rows->vorname).' ':'');
			$rows->name.=(isset($rows->vornamen)?trim($rows->vornamen).' ':'');
			$rows->name.=(isset($rows->nachname)?trim($rows->nachname).' ':'');
			return $rows;
		}
	}

	// Wenn kein Mitarbeiter pruefen ob Student
	$qry="SELECT  uid,person_id,anrede,titelpre,vorname,vornamen,nachname,aktiv  FROM campus.vw_student where uid=".$db->db_add_param(trim($uid), FHC_STRING)." LIMIT 1 ; ";
	if(!$result=$db->db_query($qry))
		die('Probleme beim Lesen der Studierenden ');
	if ($num_rows_stunde=$db->db_num_rows($result))
	{
		while($rows = $db->db_fetch_object($result))
		{
			$rows->name='';
			$rows->name.=(isset($rows->anrede)?trim($rows->anrede).' ':'');
			$rows->name.=(isset($rows->titelpre)?trim($rows->titelpre).' ':'');
			$rows->name.=(isset($rows->vorname)?trim($rows->vorname).' ':'');
			$rows->name.=(isset($rows->vornamen)?trim($rows->vornamen).' ':'');
			$rows->name.=(isset($rows->nachname)?trim($rows->nachname).' ':'');
			return $rows;
		}
	}
	// Daten gefunden wurden ist nicht mehr der Initialwert False als Returnparameter vorhanden
	return $rows;
}
#-------------------------------------------------------------------------------------------
/*
*
* @personen_id_read_mitarbeiter_oder_student Daten zum Mitarbeiter oder Studenten
*
* @param $db Aktuelle Datenbankverbindung
* @param $person_id Userkurzzeichen
*
* @return Array der User Inormationen wenn User gefunden wurde ansonst false
*
*/
function personen_id_read_mitarbeiter_oder_student($db,$person_id)
{
	$row=array();
	// Plausib
	if (!$db)
		return $row;
	// Pruefen ob Mitarbeiter
	$qry='';
	$qry.=' SELECT uid,person_id,anrede,titelpre,vorname,vornamen,nachname,aktiv FROM campus.vw_mitarbeiter where person_id='.$db->db_add_param(trim($person_id), FHC_INTEGER);
	$qry.=' UNION ';
	// Pruefen ob Student
	$qry.='SELECT  uid,person_id,anrede,titelpre,vorname,vornamen,nachname,aktiv FROM campus.vw_student where person_id='.$db->db_add_param(trim($person_id), FHC_INTEGER);
	$qry.=' LIMIT 20  ';
	if(!$result=$db->db_query($qry))
		die('Probleme beim lesen der MitarbeiterInnen/Studierenden '.$db->db_last_error());
	if (!$num_rows_stunde=$db->db_num_rows($result))
		return $row;
	while($rows = $db->db_fetch_object($result))
	{
			$rows->name='';
			$rows->name.=(isset($rows->anrede)?trim($rows->anrede).' ':'');
			$rows->name.=(isset($rows->titelpre)?trim($rows->titelpre).' ':'');
			$rows->name.=(isset($rows->vorname)?trim($rows->vorname).' ':'');
			$rows->name.=(isset($rows->vornamen)?trim($rows->vornamen).' ':'');
			$rows->name.=(isset($rows->nachname)?trim($rows->nachname).' ':'');
			$row[]=$rows;
	}
	// Daten gefunden wurden ist nicht mehr der Initialwert False als Returnparameter vorhanden
	return $row;
}

#-------------------------------------------------------------------------------------------
/*
*
* @read_create_html_news lesen der CIS - News zum anzeigen als HTML Tabelle
*
* @param $db Aktuelle Datenbankverbindung
* @param $fachbereich_kurzbz Fachbereichskennzeichen
* @param $studiengang_kz Studiengan Kennzeichen
* @param $semester Semester
*
* @return HTML Tabelle mit Newszeilen
*
*/
function read_create_html_news($db,$fachbereich_kurzbz,$studiengang_kz,$semester)
{
	if(defined('CIS_INFOSCREEN_NEWS_ANZEIGEN') && CIS_INFOSCREEN_NEWS_ANZEIGEN==false)
		return '';

	// ------------------------------------------------------------------------------------------
	//	Lesen Newstickerzeilen
	// ------------------------------------------------------------------------------------------
	//	Initialisieren der Newstickerzeilen
	$news='';

	$news_obj = new news();
	$news_obj->getnews(MAXNEWSALTER, $studiengang_kz, $semester, false, null, MAXNEWS);

	// Newsliste erzeugen
	$news='<table class="news" border="0" cellpadding="0" cellspacing="0">';
	$i=0;

	foreach($news_obj->result as $row)
	{
		if($row->content_id!='')
		{
			$lang=DEFAULT_LANGUAGE;
			$content = new content();
			$content->getContent($row->content_id, $lang, null, null, false);

			$xml_inhalt = new DOMDocument();
			if($content->content!='')
			{
				$xml_inhalt->loadXML($content->content);
			}

			if($xml_inhalt->getElementsByTagName('verfasser')->item(0))
				$verfasser = $xml_inhalt->getElementsByTagName('verfasser')->item(0)->nodeValue;
			if($xml_inhalt->getElementsByTagName('betreff')->item(0))
				$betreff = $xml_inhalt->getElementsByTagName('betreff')->item(0)->nodeValue;
			if($xml_inhalt->getElementsByTagName('text')->item(0))
				$text = $xml_inhalt->getElementsByTagName('text')->item(0)->nodeValue;

			$i++; // wird zum Zeilenfarben - CSS umschalten benoetigt
			$text=mb_ereg_replace("href","hrefs",trim($text));
			$text=mb_ereg_replace(array("\r\n", "\n", "\r","<br>")," ",$text);
			//DMS Pfad korrigieren
			$text=mb_ereg_replace("dms.php","../../cms/dms.php",$text);

			$news.='<tr valign="top" onclick="updateSiteRefresh();aktivTimeout=setTimeout(\'close_news();\',10000);document.getElementById(\'news\').innerHTML=document.getElementById(\'news_'.$row->news_id.'_anzeige\').innerHTML;show_layer(\'news\')" '.($i%2? ' class="news_row_0" ':' class="news_row_1" ').'><td width="2%"><img src="feed.png" border="0" /></td>';

			$news.='<td width="89%" >'. (stristr($text,'</table>')?$text:(mb_strlen($text)>90?mb_substr(trim('<b>'.$betreff.'</b><br>'.$text),0,90).'<span style="font-size:7px;">...</span>' :trim($text))).'</td>

				<td width="9%">
							<span class="blau_mitteText" style="font-size:small;">
								Detail
							</span>
				</td>
				<td class="ausblenden">
					<div id="news_'.$row->news_id.'_anzeige"><h1>'.trim($betreff).'</h1>'.trim($text).'
						<hr><br><br><div onclick="close_news();">
							<span class="blau_mitteText">
								schliessen
							</span>
						</div>
						<p>&nbsp;</p>
					</div>
				</td></tr><tr '.($i%2? ' class="news_row_0" ':' class="news_row_1" ').'><td '.($i%2? ' class="news_row_0" ':' class="news_row_1" ').' colspan="3">&nbsp;</td></tr>';
		}
	}
	$news.='</table>';
	$news=mb_ereg_replace('href=','',$news);
	return $news;
}
?>
