<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../skin/style.css.php" rel="stylesheet" type="text/css">
</head>

<body>
<?php
if(isset($_SERVER["REMOTE_USER"]))
{
	$visible=true;
}
else
{
	$visible=false;
}
?>

<table id="inhalt" class="tabcontent">
  <tr>
    <td class="tdwidth10">&nbsp;</td>
    <td><table class="tabcontent">
      <tr>
        <td class="ContentHeader"><font class="ContentHeader">&nbsp;Infrastruktur - Ansprechpartner</font></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
	  <tr>
	  	<td>
		  <table  class="tabcontent2" cellspacing="0" >
		  	<tr>
		  	  <td width="280" colspan="2" nowrap class="ContentHeader2">&nbsp;Leitung</td>
		  	  <td class="ContentHeader2" nowrap>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	  <td class="ContentHeader2" nowrap>&nbsp;Sprechzeiten</td>
		  	</tr>
		  	<tr>
		  	  <td colspan="2" class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	</tr>
		  	<tr>
		  	  <td width="260" class='tdwrap'>Mag. Nestlang Dietmar</td>
		  	  <td width="50" class='tdwrap'>215</td>
		  	  <td class='tdwrap'><?php echo ($visible?'<a class="Item" href="mailto:nestlang@technikum-wien.at">nestlang@technikum-wien.at</a>':'')?></td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>Termin nach Vereinbarung </td>
		  	</tr>
		  	<tr>
		  	  <td colspan="2" class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	</tr>
		  	<tr>
			  <td width="280" colspan="2" nowrap class="ContentHeader2">&nbsp;Service Desk</td>
			  <td class="ContentHeader2" nowrap><?php echo($visible?'<a class="Item" href="mailto:support@technikum-wien.at">support@technikum-wien.at</a>':'')?></td>
			  <td width="56" class='tdwrap'>&nbsp;</td>
			  <td class="ContentHeader2" nowrap>&nbsp;Sprechzeiten</td>
			</tr>
			<tr>
		  	  <td colspan="2" class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	</tr>
		  	<tr>
		  	  <td width="260" class='tdwrap'>Mag. Raab Gerald (Leitung)</td>
		  	  <td width="50" class='tdwrap'>342</td>
		  	  <td class='tdwrap'><?php echo ($visible?'<a class="Item" href="mailto:nestlang@technikum-wien.at">nestlang@technikum-wien.at</a>':'')?></td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>Termin nach Vereinbarung </td>
		  	</tr>
		  	<tr>
		  	  <td colspan="2" class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	</tr>
		  	<tr>
		      <td width="260" class='tdwrap'><strong>Helpdesk</strong></td>
			  <td width="50" class='tdwrap'>&nbsp;</td>
			  <td  class='tdwrap'>&nbsp;</td>
			  <td width="56" class='tdwrap'>&nbsp;</td>
			  <td class='tdwrap'>&nbsp;</td>
			</tr>
			<tr>
			  <td width="260" class='tdwrap'>Vogt Eva (karenziert)</td>
			  <td width="50" class='tdwrap'>249</td>
			  <td class='tdwrap'><?php echo ($visible?'<a class="Item" href="mailto:eva.vogt@technikum-wien.at">eva.vogt@technikum-wien.at</a>':'')?></td>
			  <td width="56" class='tdwrap'>&nbsp;</td>
			  <td class='tdwrap'>Termin nach Vereinbarung</td>
			</tr>
			<tr>
			  <td width="260" class='tdwrap'>Elgner Richard, BSc</td>
			  <td width="50" class='tdwrap'>341</td>
			  <td class='tdwrap'><?php echo ($visible?'<a class="Item" href="mailto:relgner@technikum-wien.at">relgner@technikum-wien.at</a>':'')?></td>
			  <td width="56" class='tdwrap'>&nbsp;</td>
			  <td class='tdwrap'>Termin nach Vereinbarung</td>
			</tr>
			<tr>
			  <td width="260" class='tdwrap'>Ing. Lechner Martin, BSc</td>
			  <td width="50" class='tdwrap'>240</td>
			  <td class='tdwrap'><?php echo ($visible?'<a class="Item" href="mailto:lechner@technikum-wien.at">lechner@technikum-wien.at</a>':'')?></td>
			  <td width="56" class='tdwrap'>&nbsp;</td>
			  <td class='tdwrap'>Termin nach Vereinbarung</td>
			</tr>
			<!--<tr>
			  <td width="260" class='tdwrap'>Braunstorfer Michael</td>
			  <td width="50" class='tdwrap'>240</td>
			  <td class='tdwrap'><?php echo ($visible?'<a class="Item" href="mailto:michael.braunstorfer@technikum-wien.at">michael.braunstorfer@technikum-wien.at</a>':'')?></td>
			  <td width="56" class='tdwrap'>&nbsp;</td>
			  <td class='tdwrap'>Termin nach Vereinbarung</td>
			</tr>-->
			<tr>
		  	  <td colspan="2" class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	</tr>
			<tr>
		      <td width="260" class='tdwrap'><strong>Lehre/Lektorensupport</strong></td>
			  <td width="50" class='tdwrap'>&nbsp;</td>
			  <td  class='tdwrap'>&nbsp;</td>
			  <td width="56" class='tdwrap'>&nbsp;</td>
			  <td class='tdwrap'>&nbsp;</td>
			</tr>
			<tr>
			  <td width="260" class='tdwrap'>DI Papp Kata</td>
			  <td width="50" class='tdwrap'>247</td>
			  <td  class='tdwrap'><?php echo ($visible?'<a class="Item" href="mailto:kata.papp@technikum-wien.at">kata.papp@technikum-wien.at</a>':'')?></td>
			  <td width="56" class='tdwrap'>&nbsp;</td>
			  <td class='tdwrap'>Termin nach Vereinbarung</td>
			</tr>
			<tr>
		  	  <td colspan="2" class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	</tr>
		  	<tr>
		      <td width="260" class='tdwrap'><strong>e-learning Kompetenzcenter</strong></td>
			  <td width="50" class='tdwrap'>&nbsp;</td>
			  <td  class='tdwrap'>&nbsp;</td>
			  <td width="56" class='tdwrap'>&nbsp;</td>
			  <td class='tdwrap'>&nbsp;</td>
			</tr>
			<tr>
			  <td width="260" class='tdwrap'>Krösl Katharina</td>
			  <td width="50" class='tdwrap'>249</td>
			  <td  class='tdwrap'><?php echo ($visible?'<a class="Item" href="mailto:katharina.kroesl@technikum-wien.at">kata.papp@technikum-wien.at</a>':'')?></td>
			  <td width="56" class='tdwrap'>&nbsp;</td>
			  <td class='tdwrap'>Termin nach Vereinbarung</td>
			</tr>
			<tr>
		  	  <td colspan="2" class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	</tr>
			<tr>
		      	  <td width="260" class='tdwrap'><strong>Softgrid-Virtualisierung</strong></td>
			  <td width="50" class='tdwrap'>&nbsp;</td>
			  <td  class='tdwrap'>&nbsp;</td>
			  <td width="56" class='tdwrap'>&nbsp;</td>
			  <td class='tdwrap'>&nbsp;</td>
			</tr>
			<tr>
			  <td width="260" class='tdwrap'>Ing. Esberger Franz</td>
			  <td width="50" class='tdwrap'>243</td>
			  <td class='tdwrap'><?php echo ($visible?'<a class="Item" href="mailto:franz.esberger@technikum-wien.at">franz.esberger@technikum-wien.at</a>':'')?></td>
			  <td width="56" class='tdwrap'>&nbsp;</td>
			  <td class='tdwrap'>Termin nach Vereinbarung</td>
			</tr>
			<tr>
		  	  <td colspan="2" class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	</tr>
		  	<tr>
			  <td width="280" colspan="2" nowrap class="ContentHeader2">&nbsp;Haustechnik</td>
			  <td  class="ContentHeader2"  nowrap><?php echo ($visible?'<a class="Item" href="mailto:admin@technikum-wien.at">admin@technikum-wien.at</a>':'')?></td>
			  <td width="56" class='tdwrap'>&nbsp;</td>
			  <td class="ContentHeader2" nowrap>&nbsp;Sprechzeiten</td>
			</tr>
			<tr>
		  	  <td colspan="2" class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	</tr>
            <tr>
		      <td width="260" class='tdwrap'>Staubmann Robert (Leitung)</td>
			  <td width="50" class='tdwrap'>250</td>
			  <td  class='tdwrap'><?php echo ($visible?'<a class="Item" href="mailto:robert.staubmann@technikum-wien.at">robert.staubmann@technikum-wien.at</a>':'')?></td>
			  <td width="56" class='tdwrap'>&nbsp;</td>
			  <td class='tdwrap'>Termin nach Vereinbarung</td>
			</tr>
			<tr>
			  <td width="260" class='tdwrap'>Nagl Richard</td>
			  <td width="50" class='tdwrap'>252</td>
			  <td  class='tdwrap'><?php echo ($visible?'<a class="Item" href="mailto:richard.nagl@technikum-wien.at">richard.nagl@technikum-wien.at</a>':'')?></td>
			  <td width="56" class='tdwrap'>&nbsp;</td>
			  <td class='tdwrap'>Termin nach Vereinbarung</td>
			</tr>
			<tr>
		      <td width="260" class='tdwrap'>Weigl Harald</td>
		      <td width="50" class='tdwrap'>253</td>
			  <td  class='tdwrap'><?php echo ($visible?'<a class="Item" href="mailto:weigl@technikum-wien.at">weigl@technikum-wien.at</a>':'')?></td>
			  <td width="56" class='tdwrap'>&nbsp;</td>
			  <td class='tdwrap'>Termin nach Vereinbarung</td>
			</tr>
			<tr>
		  	  <td colspan="2" class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	</tr>
		  	<tr>
		      <td width="260" class='tdwrap'><strong>Empfang</strong></td>
			  <td width="50" class='tdwrap'>&nbsp;</td>
			  <td  class='tdwrap'>&nbsp;</td>
			  <td width="56" class='tdwrap'>&nbsp;</td>
			  <td class='tdwrap'>&nbsp;</td>
			</tr>
			<tr>
		  	  <td width="260" class='tdwrap'>Schantl Ingrid</td>
		  	  <td width="50" class='tdwrap'>100</td>
		  	  <td class='tdwrap'><?php echo ($visible?'<a class="Item" href="mailto:schantl@technikum-wien.at">schantl@technikum-wien.at</a>':'')?></td>
		  	  <td width="56" class='tdwrap'>&nbsp;</td>
			  <td class='tdwrap'>Termin nach Vereinbarung</td>
		  	</tr>
		  	<tr>
		  	  <td width="260" class='tdwrap'>Reicher Eunike</td>
		  	  <td width="50" class='tdwrap'>100</td>
		  	  <td class='tdwrap'><?php echo ($visible?'<a class="Item" href="mailto:reicher@technikum-wien.at">reicher@technikum-wien.at</a>':'')?></td>
		  	  <td width="56" class='tdwrap'>&nbsp;</td>
			  <td class='tdwrap'>Termin nach Vereinbarung</td>
		  	</tr>
		  	<tr>
		  	  <td colspan="2" class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	</tr>
			<tr>
			  <td width="280" colspan="2" nowrap class="ContentHeader2">&nbsp;Systementwicklung</td>
			  <td  class="ContentHeader2" nowrap>&nbsp;</td>
			  <td width="56" class='tdwrap'>&nbsp;</td>
			  <td class="ContentHeader2" nowrap>&nbsp;Sprechzeiten</td>
			</tr>
			<tr>
		  	  <td colspan="2" class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	</tr>
	  		<tr>
			  <td width="260" class='tdwrap'>Dipl. Ing. (FH) Paminger Christian (Leitung)</td>
			  <td width="50" class='tdwrap'>245</td>
			  <td  class='tdwrap'><?php echo ($visible?'<a class="Item" href="mailto:christian.paminger@technikum-wien.at">christian.paminger@technikum-wien.at</a>':'')?></td>
			  <td width="56" class='tdwrap'>&nbsp;</td>
			  <td class='tdwrap'>Termin nach Vereinbarung</td>
			</tr>
			<tr>
			  <td width="260" class='tdwrap'>Dipl. Ing. (FH) Mag. Hangl Rudolf</td>
			  <td width="50" class='tdwrap'>248</td>
			  <td  class='tdwrap'><?php echo ($visible?'<a class="Item" href="mailto:rudolf.hangl@technikum-wien.at">rudolf.hangl@technikum-wien.at</a>':'')?></td>
			  <td width="56" class='tdwrap'>&nbsp;</td>
			  <td class='tdwrap'>Termin nach Vereinbarung</td>
			</tr>
			<tr>
			  <td width="260" class='tdwrap'>&Ouml;sterreicher Andreas</td>
			  <td width="50" class='tdwrap'>241</td>
			  <td  class='tdwrap'><?php echo ($visible?'<a class="Item" href="mailto:andreas.oesterreicher@technikum-wien.at">andreas.oesterreicher@technikum-wien.at</a>':'')?></td>
			  <td width="56" class='tdwrap'>&nbsp;</td>
			  <td class='tdwrap'>Termin nach Vereinbarung</td>
			</tr>
			<tr>
			  <td width="260" class='tdwrap'>Simane-Sequens Gerald</td>
			  <td width="50" class='tdwrap'>258</td>
			  <td  class='tdwrap'></td>
			  <td width="56" class='tdwrap'>&nbsp;</td>
			  <td class='tdwrap'>Termin nach Vereinbarung</td>
			</tr>
			<tr>
		  	  <td colspan="2" class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	</tr>
            <tr>
			    <td width="260" class='tdwrap'><strong> LV-Koordinationsstelle</strong></td>
			    <td width="50" class='tdwrap'>&nbsp;</td>
			  <td  class='tdwrap'><?php echo ($visible?'<a class="Item" href="mailto:lvplan@technikum-wien.at">lvplan@technikum-wien.at</a>':'')?></td>
			  <td width="56" class='tdwrap'>&nbsp;</td>
			  <td class='tdwrap'>&nbsp;</td>
			</tr>
			<tr>
			    <td width="260" class='tdwrap'>Ing. Dvorak Andreas</td>
			    <td width="50" class='tdwrap'>251</td>
			  <td  class='tdwrap'><?php echo ($visible?'<a class="Item" href="mailto:andreas.dvorak@technikum-wien.at">andreas.dvorak@technikum-wien.at</a>':'')?></td>
			  <td width="56" class='tdwrap'>&nbsp;</td>
			  <td class='tdwrap'>Termin nach Vereinbarung</td>
			</tr>
			<tr>
			  <td width="260" class='tdwrap'>Haas Bettina</td>
			  <td width="50" class='tdwrap'>257</td>
			  <td  class='tdwrap'><?php echo ($visible?'<a class="Item" href="mailto:bettina.haas@technikum-wien.at">bettina.haas@technikum-wien.at</a>':'')?></td>
			  <td class='tdwrap'>&nbsp;</td>
			  <td class='tdwrap'>Termin nach Vereinbarung</td>
			  </tr>
			<tr>
			    <td width="260" class='tdwrap'>Kindl Manfred </td>
			    <td width="50" class='tdwrap'>256</td>
			  <td  class='tdwrap'><?php echo ($visible?'<a class="Item" href="mailto:kindlm@technikum-wien.at">kindlm@technikum-wien.at</a>':'')?></td>
			  <td width="56" class='tdwrap'>&nbsp;</td>
			  <td class='tdwrap'>Termin nach Vereinbarung</td>
			</tr>
			<tr>
		  	  <td colspan="2" class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	</tr>
			<tr>
			  <td width="280" colspan="2" nowrap class="ContentHeader2">&nbsp;Systemadministration (Zentrale Services)</td>
			  <td class="ContentHeader2" nowrap>&nbsp;</td>
			  <td width="56" class='tdwrap'>&nbsp;</td>
			  <td class="ContentHeader2" nowrap>&nbsp;Sprechzeiten</td>
			</tr>
			<tr>
		  	  <td colspan="2" class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	</tr>

          	<tr>
              <td width="260" class='tdwrap'>Kopper Martin (Leitung)</td>
              <td width="50" class='tdwrap'>246</td>
              <td class='tdwrap'><?php echo ($visible?'<a class="Item" href="mailto:martin.kopper@technikum-wien.at">martin.kopper@technikum-wien.at</a>':'')?></td>
              <td width="56" class='tdwrap'>&nbsp;</td>
              <td class='tdwrap'>Termin nach Vereinbarung</td>
          	</tr>
          	<tr>
              <td width="260" class='tdwrap'>Dipl. Ing. Nimmervoll Alexander</td>
              <td width="50" class='tdwrap'>242</td>
              <td class='tdwrap'><?php echo ($visible?'<a class="Item" href="mailto:alexander.nimmervoll@technikum-wien.at">alexander.nimmervoll@technikum-wien.at</a>':'')?></td>
              <td width="56" class='tdwrap'>&nbsp;</td>
              <td class='tdwrap'>Termin nach Vereinbarung</td>
          	</tr>
          				
			<tr>
		  	  <td colspan="2" class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	</tr>
			<tr>
			  <td width="280" colspan="2" nowrap class="ContentHeader2">&nbsp;Einkauf</td>
			  <td class="ContentHeader2" nowrap>&nbsp;</td>
			  <td width="56" class='tdwrap'>&nbsp;</td>
			  <td class="ContentHeader2" nowrap>&nbsp;Sprechzeiten</td>
			</tr>
			<tr>
		  	  <td colspan="2" class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	  <td class='tdwrap'>&nbsp;</td>
		  	</tr>
			<tr> 
              <td width="260" class='tdwrap'>Isailovic Julia</td>
              <td width="50" class='tdwrap'>224</td>
              <td class='tdwrap'><?php echo ($visible?'<a class="Item" href="mailto:isailovic@technikum-wien.at">isailovic@technikum-wien.at</a>':'')?></td>
              <td width="56" class='tdwrap'>&nbsp;</td>
              <td class='tdwrap'>Termin nach Vereinbarung</td>
          	</tr>

		  </table>
		</td>
	  </tr>
    </table></td>
	<td class="tdwidth30">&nbsp;</td>
  </tr>
</table>
</body>
</html>
