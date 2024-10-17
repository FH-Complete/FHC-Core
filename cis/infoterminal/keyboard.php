
<style type="text/css">
<!--
div.weiter {
  width:80%;
}

table.keyboardInputLayout {
  white-space:nowrap;
  border-collapse:separate;
  border-spacing:0px;
  background-color:#dddddd;
  width:90%;
  border:0px;
}

table.keyboardInputLayout td {
  color:#000000;
  margin:0px;
  line-height:1;  
  text-align:center;
  padding:5px 5px 5px 5px;  
  font-size: large;
  width:10%;
  height:10%;  
}

table.keyboardInput {
}

table.keyboardInput td {
  padding:17px 15px 17px 15px;
  border-top:2px solid #eeeeee;
  border-right:2px solid #6e6e6e;
  border-bottom:2px solid #6e6e6e;
  border-left:2px solid #eeeeee;  
}

td.keyboardFunkTasteOFF {
  background-color:#D0D0D0;
}
td.keyboardFunkTasteON {
  background-color:#90EE90;
}

.anzeigen {display:inline;}
.verstecken {display:none;}


	/* Knopf Blau */
	span.blau_mitteText{text-align:center; font-size: x-large; color: #000; background-color: #A5AFB6; width:97%; display:block; padding: 3px;}}


-->
</style>

<script language="JavaScript1.2" type="text/javascript">
<!--


	var inputFeld=false;

	var inputUID='uid';
	var inputPWD='pwd';
	
	var shiftTaste=false;
	var capsTaste=false;
	var altGR=false;

	function setAltGrFeld() {

		shiftTaste=false;
		shiftTaste1.className='keyboardFunkTasteOFF';
		shiftTaste2.className='keyboardFunkTasteOFF';

		capsTaste=false;
		capsTaste1.className='keyboardFunkTasteOFF';
		capsTaste2.className='keyboardFunkTasteOFF';

		if (altGR) {
			altGR=false;
			setShift();
		} else {
			altGR=true;
			setAltGr();			
		}
		
	}	
	
	function setCapsFeld() {
		if (capsTaste) {
			unsetShift();
			capsTaste=false;
			document.getElementById('capsTaste1').className='keyboardFunkTasteOFF';
			document.getElementById('capsTaste2').className='keyboardFunkTasteON';
		} else {
			setShift();
			capsTaste=true;
			document.getElementById('capsTaste1').className='keyboardFunkTasteOFF';
			document.getElementById('capsTaste2').className='keyboardFunkTasteON';
		}	
		shiftTaste=false;
		document.getElementById('shiftTaste1').className='keyboardFunkTasteOFF';
		document.getElementById('shiftTaste2').className='keyboardFunkTasteOFF';
	}	

	function setShiftTaste(Feld) {
		if (!capsTaste) {
			document.getElementById('capsTaste1').className='keyboardFunkTasteOFF';
			document.getElementById('capsTaste2').className='keyboardFunkTasteOFF';
			if (shiftTaste) {
				document.getElementById('shiftTaste1').className='keyboardFunkTasteOFF';
				document.getElementById('shiftTaste2').className='keyboardFunkTasteON';
				unsetShift();
				shiftTaste=false;
			} else {
				shiftTaste=Feld;
				document.getElementById('shiftTaste1').className='keyboardFunkTasteOFF';
				document.getElementById('shiftTaste2').className='keyboardFunkTasteON';
				setShift();
			}	
		}
	}	

	
	function setInputFeld(Wert) {
		if (!inputFeld) {
			setTabFeld(); 
		}

		if (inputFeld) {
			inputFeld.value=inputFeld.value+Wert;
		}	
		if (shiftTaste) {
			setShiftTaste()			
		}
	}
	
	function setEmptyFeld() {
		if (inputFeld) {
			inputFeld.value='';
		}	
		if (shiftTaste) {
			setShiftTaste()			
		}		
	}

	function setTabFeld() {
		if (inputFeld && inputFeld.name==inputUID) {
			document.getElementById(inputPWD).focus();			
		} else {
			document.getElementById(inputUID).focus();
		}
	}
	
	function setEnterFeld() {
		document.getElementById('tastatur').submit();
	}
	function setBkspFeld() {
		if (inputFeld && inputFeld.value.length >0) {
			var tmpWert=inputFeld.value;
			inputFeld.value=tmpWert.substring(0,inputFeld.value.length - 1 );
		}
		if (shiftTaste) {
			setShiftTaste()			
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

	function unsetShift() {
		hide_layer('Row1AltGr');
		hide_layer('Row1Shift');
		hide_layer('Row2AltGr');
		hide_layer('Row2Shift');
		hide_layer('Row3Shift');
		hide_layer('Row3AltGr');
		hide_layer('Row4Shift');
		hide_layer('Row4AltGr');
		
		
		show_layer('Row1');
		show_layer('Row2');
		show_layer('Row3');
		show_layer('Row4');
	}	

	function setShift() {
		hide_layer('Row1');
		hide_layer('Row1AltGr');
		hide_layer('Row2');
		hide_layer('Row2AltGr');
		hide_layer('Row3');
		hide_layer('Row3AltGr');
		hide_layer('Row4');
		hide_layer('Row4AltGr');

		show_layer('Row1Shift');
		show_layer('Row2Shift');
		show_layer('Row3Shift');
		show_layer('Row4Shift');

	}	

	function setAltGr() {
		hide_layer('Row1');
		hide_layer('Row1Shift');
		hide_layer('Row2');
		hide_layer('Row2Shift');
		hide_layer('Row3');
		hide_layer('Row3Shift');
		hide_layer('Row4');
		hide_layer('Row4Shift');

		show_layer('Row1AltGr');
		show_layer('Row2AltGr');
		show_layer('Row3AltGr');
		show_layer('Row4AltGr');
	}	

//-->
</script>	

<form accept-charset="UTF-8" id="tastatur" target="_self" action="<?php echo $_SERVER['PHP_SELF'];?>"  method="post" enctype="multipart/form-data"  >

<table border="0" cellpadding="1" cellspacing="1">
	<tr>
		<th>&nbsp;Benutzername&nbsp;</th>
		<th>&nbsp;Passwort&nbsp;</th>
	</tr>
	<tr>
		<td>&nbsp;<input onfocus="inputFeld=this;" type="text" value="" id="uid" name="uid" >&nbsp;</td>
		<td>&nbsp;<input onfocus="inputFeld=this;" type="Password" value="" id="pwd" name="pwd">&nbsp;</td>
		<td  class="verstecken">
			<input class="verstecken" type="text" value="Login" name="work">
			<input class="verstecken" type="text" value="<?php echo trim((isset($_REQUEST['raumtyp_kurzbz']) ? $_REQUEST['raumtyp_kurzbz']:'EDV')); ?>" name="raumtyp_kurzbz">
			<input class="verstecken" type="text" value="<?php echo trim((isset($_REQUEST['ort_kurzbz']) ? $_REQUEST['ort_kurzbz']:'')); ?>" name="ort_kurzbz">
			<input class="verstecken" type="text" value="<?php echo $standort_id ?>" name="standort_id">
		</td>
	</tr>	
</table>




</form>

<table  class="keyboardInputLayout" onmouseover="if (!inputFeld) {setTabFeld(); }" >

	<!-- Num Leiste -->
	<tr>
		<td id="Row1" class="anzeigen">
			<table class="keyboardInput">
				<tr>
					<td onclick="setInputFeld('^');">^</td>
					<td onclick="setInputFeld('1');">1</td>
					<td onclick="setInputFeld('2');">2</td>
					<td onclick="setInputFeld('3');">3</td>
					<td onclick="setInputFeld('4');">4</td>
					<td onclick="setInputFeld('5');">5</td>
					<td onclick="setInputFeld('6');">6</td>
					<td onclick="setInputFeld('7');">7</td>
					<td onclick="setInputFeld('8');">8</td>
					<td onclick="setInputFeld('9');">9</td>
					<td onclick="setInputFeld('0');">0</td>
					<td onclick="setInputFeld('&szlig;');">&szlig;</td>
					<td onclick="setInputFeld('&acute;');">&acute;</td>
					<td onclick="setBkspFeld();" class="keyboardFunkTasteOFF" id="iBksp1">Bksp</td>
				</tr>
			</table>
		</td>
	</tr>
	
	<tr>
		<td id="Row1Shift" class="verstecken">
			<table class="keyboardInput">
				<tr>
					<td onclick="setInputFeld('&deg;');">&deg;</td>
					<td onclick="setInputFeld('!');">!</td>
					<td onclick="setInputFeld('&quot;');">&quot;</td>
					<td onclick="setInputFeld('&sect;');">&sect;</td>
					<td onclick="setInputFeld('$');">$</td>
					<td onclick="setInputFeld('%');">%</td>
					<td onclick="setInputFeld('&amp;');">&amp;</td>
					<td onclick="setInputFeld('/');">/</td>
					<td onclick="setInputFeld('(');">(</td>
					<td onclick="setInputFeld(')');">)</td>
					<td onclick="setInputFeld('=');">=</td>
					<td onclick="setInputFeld('?');">?</td>
					<td onclick="setInputFeld('&acute;');">&acute;</td>
					<td onclick="setBkspFeld();" class="keyboardFunkTasteOFF" id="iBksp2">Bksp</td>
				</tr>
			</table>
		</td>
	</tr>

	<tr>
		<td id="Row1AltGr" class="verstecken">
			<table class="keyboardInput">
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td onclick="setInputFeld('&sup2;');">&sup2;</td>
					<td onclick="setInputFeld('&sup3;');">&sup3;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td onclick="setInputFeld('{');">{</td>
					<td onclick="setInputFeld('[');">[</td>
					<td onclick="setInputFeld(']');">]</td>
					<td onclick="setInputFeld('}');">}</td>
					<td onclick="setInputFeld('\\');">\</td>
					<td>&nbsp;</td>
					<td onclick="setBkspFeld();" class="keyboardFunkTasteOFF" id="iBksp3">Bksp</td>
				</tr>
			</table>
		</td>
	</tr>
	

	<tr>
		<td id="Row2" class="anzeigen">
			<table class="keyboardInput">
				<tr>
					<td onclick="setTabFeld();" class="keyboardFunkTasteOFF" id="iTab1">Tab</td>
					<td onclick="setInputFeld('q');">q</td>
					<td onclick="setInputFeld('w');">w</td>
					<td onclick="setInputFeld('e');">e</td>
					<td onclick="setInputFeld('r');">r</td>
					<td onclick="setInputFeld('t');">t</td>
					<td onclick="setInputFeld('z');">z</td>
					<td onclick="setInputFeld('u');">u</td>
					<td onclick="setInputFeld('i');">i</td>
					<td onclick="setInputFeld('o');">o</td>
					<td onclick="setInputFeld('p');">p</td>
					<td onclick="setInputFeld('&uuml;');">&uuml;</td>
					<td onclick="setInputFeld('+');">+</td>
					<td onclick="setEmptyFeld();" class="keyboardFunkTasteOFF">Clear</td>

				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td id="Row2Shift" class="verstecken">
			<table class="keyboardInput">
				<tr>
					<td onclick="setTabFeld();" class="keyboardFunkTasteOFF" id="iTab2">Tab</td>
					<td onclick="setInputFeld('Q');">Q</td>
					<td onclick="setInputFeld('W');">W</td>
					<td onclick="setInputFeld('E');">E</td>
					<td onclick="setInputFeld('R');">R</td>
					<td onclick="setInputFeld('T');">T</td>
					<td onclick="setInputFeld('Z');">Z</td>
					<td onclick="setInputFeld('U');">U</td>
					<td onclick="setInputFeld('I');">I</td>
					<td onclick="setInputFeld('O');">O</td>
					<td onclick="setInputFeld('P');">P</td>
					<td onclick="setInputFeld('&Uuml;');">&Uuml;</td>
					<td onclick="setInputFeld('*');">*</td>
					<td onclick="setEmptyFeld();" class="keyboardFunkTasteOFF">Clear</td>
				</tr>
			</table>
		</td>
	</tr>

	<tr>
		<td id="Row2AltGr" class="verstecken">
			<table class="keyboardInput">
				<tr>
					<td onclick="setTabFeld();" class="keyboardFunkTasteOFF" id="iTab3">Tab</td>
					<td onclick="setInputFeld('@');">@</td>
					<td>&nbsp;</td>
					<td onclick="setInputFeld('&euro;');">&euro;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td onclick="setInputFeld('~');">~</td>
					<td onclick="setEmptyFeld();" class="keyboardFunkTasteOFF">Clear</td>
				</tr>
			</table>
		</td>
	</tr>

	<tr>
		<td id="Row3" class="anzeigen">
			<table class="keyboardInput">
				<tr>
					<td onclick="setCapsFeld();" class="keyboardFunkTasteOFF" id="capsTaste1">Caps</td>
					<td onclick="setInputFeld('a');">a</td>
					<td onclick="setInputFeld('s');">s</td>
					<td onclick="setInputFeld('d');">d</td>
					<td onclick="setInputFeld('f');">f</td>
					<td onclick="setInputFeld('g');">g</td>
					<td onclick="setInputFeld('h');">h</td>
					<td onclick="setInputFeld('j');">j</td>
					<td onclick="setInputFeld('k');">k</td>
					<td onclick="setInputFeld('l');">l</td>
					<td onclick="setInputFeld('&ouml;');">&ouml;</td>
					<td onclick="setInputFeld('&auml;');">&auml;</td>
					<td onclick="setInputFeld('#');">#</td>
					<td onclick="setEnterFeld();" class="keyboardFunkTasteOFF" id="iEnter1">Enter</td>
				</tr>
			</table>
		</td>
	</tr>

	<tr>
		<td id="Row3Shift" class="verstecken">
			<table class="keyboardInput">
				<tr>
					<td onclick="setCapsFeld();" class="keyboardFunkTasteON" id="capsTaste2">Caps</td>
					<td onclick="setInputFeld('A');">A</td>
					<td onclick="setInputFeld('S');">S</td>
					<td onclick="setInputFeld('D');">D</td>
					<td onclick="setInputFeld('F');">F</td>
					<td onclick="setInputFeld('G');">G</td>
					<td onclick="setInputFeld('H');">H</td>
					<td onclick="setInputFeld('J');">J</td>
					<td onclick="setInputFeld('K');">K</td>
					<td onclick="setInputFeld('L');">L</td>
					<td onclick="setInputFeld('&Ouml;');">&Ouml;</td>
					<td onclick="setInputFeld('&Auml;');">&Auml;</td>
					<td onclick="setInputFeld('&rsquo;');">&rsquo;</td>
					<td onclick="setEnterFeld();" class="keyboardFunkTasteOFF" id="iEnter2">Enter</td>
				</tr>
			</table>
		</td>
	</tr>

	<tr>
		<td id="Row3AltGr" class="verstecken">
			<table class="keyboardInput">
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td onclick="setEnterFeld();" class="keyboardFunkTasteOFF" id="iEnter3">Enter</td>
				</tr>
			</table>
		</td>
	</tr>


	<tr>
		<td id="Row4" class="anzeigen">
			<table class="keyboardInput">
				<tr>
					<td  onclick="if (shiftTaste) {setShiftTaste();} else {setShiftTaste(this);}" class="keyboardFunkTasteOFF" id="shiftTaste1">Shift</td>
					<td onclick="setInputFeld('<');"><</td>
					<td onclick="setInputFeld('y');">y</td>
					<td onclick="setInputFeld('x');">x</td>
					<td onclick="setInputFeld('c');">c</td>
					<td onclick="setInputFeld('v');">v</td>
					<td onclick="setInputFeld('b');">b</td>
					<td onclick="setInputFeld('n');">n</td>
					<td onclick="setInputFeld('m');">m</td>
					<td onclick="setInputFeld(',');">,</td>
					<td onclick="setInputFeld('.');">.</td>
					<td onclick="setInputFeld('-');">-</td>
					<td onclick="setAltGrFeld();" class="keyboardFunkTasteOFF" id="shiftAltGr1">AltGr</td>
				</tr>
			</table>
		</td>
	</tr>
	
	<tr>
		<td id="Row4Shift" class="verstecken">
			<table class="keyboardInput">
				<tr>
					<td  onclick="if (shiftTaste) {setShiftTaste();} else {setShiftTaste(this);}" class="keyboardFunkTasteON" id="shiftTaste2">Shift</td>
					<td onclick="setInputFeld('>');">></td>
					<td onclick="setInputFeld('Y');">Y</td>
					<td onclick="setInputFeld('X');">X</td>
					<td onclick="setInputFeld('C');">C</td>
					<td onclick="setInputFeld('V');">V</td>
					<td onclick="setInputFeld('B');">B</td>
					<td onclick="setInputFeld('N');">N</td>
					<td onclick="setInputFeld('M');">M</td>
					<td onclick="setInputFeld(';');">;</td>
					<td onclick="setInputFeld(':');">:</td>
					<td onclick="setInputFeld('-');">-</td>
					<td onclick="setAltGrFeld();" class="keyboardFunkTasteOFF" id="shiftAltGr2">AltGr</td>
				</tr>
			</table>
		</td>
	</tr>



	<tr>
		<td id="Row4AltGr" class="verstecken">
			<table class="keyboardInput">
				<tr>
					<td onclick="setInputFeld('|');">|</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td onclick="setInputFeld('&micro;');">&micro;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td onclick="setAltGrFeld();" class="keyboardFunkTasteON" id="shiftAltGr3">AltGr</td>
				</tr>
			</table>
		</td>
	</tr>
	
	<tr>
		<td>
			<table class="keyboardInput" style="width:100%;text-align:center;">
				<tr>
					<td style="width:100%;text-align:center;" onclick="setInputFeld(' ');">Leer</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<!-- Weiter Knopf -->
<div class="weiter" align="right" style="padding-top: 5px;" onclick="setEnterFeld();">
	<div style="text-align:right;border:0;height:10%;width:20%;">		
				<span class="blau_mitteText">
					&nbsp;weiter&nbsp;
				</span>							
	</div>
</div>

