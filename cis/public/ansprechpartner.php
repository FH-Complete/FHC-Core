<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../skin/cis.css" rel="stylesheet" type="text/css">
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

<table width="100%"  border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="10">&nbsp;</td>
    <td><table width="100%"  border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td class="ContentHeader"><font class="ContentHeader">&nbsp;Infrastruktur - Ansprechpartner</font></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
	  <tr>
	  	<td>
		  <table  border="0" cellspacing="0" cellpadding="0">
		  	<tr>
		  	  <td width="280" colspan="2" nowrap class="ContentHeader2">&nbsp;Leitung</td>
		  	  <td class="ContentHeader2" nowrap>&nbsp;</td>
		  	  <td nowrap>&nbsp;</td>
		  	  <td class="ContentHeader2" nowrap>Sprechzeiten</td>
		  	</tr>
		  	<tr>
		  	  <td colspan="2" nowrap>&nbsp;</td>
		  	  <td nowrap>&nbsp;</td>
		  	  <td nowrap>&nbsp;</td>
		  	  <td nowrap>&nbsp;</td>
		  	</tr>
		  	<tr>
		  	  <td width="260" nowrap>Mag. Dietmar Nestlang</td>
		  	  <td width="50" nowrap>215</td>
		  	  <td nowrap><?php echo ($visible?'<a class="Item" href="mailto:nestlang@technikum-wien.at">nestlang@technikum-wien.at</a>':'')?></td>
		  	  <td nowrap>&nbsp;</td>
		  	  <td nowrap>Termin nach Vereinbarung </td>
		  	</tr>
		  	<tr>
		  	  <td colspan="2" nowrap>&nbsp;</td>
		  	  <td nowrap>&nbsp;</td>
		  	  <td nowrap>&nbsp;</td>
		  	  <td nowrap>&nbsp;</td>
		  	</tr>
		  	<tr>
			  <td width="280" colspan="2" nowrap class="ContentHeader2">&nbsp;Service Desk</td>
			  <td class="ContentHeader2" nowrap><?php echo($visible?'<a class="Item" href="mailto:support@technikum-wien.at">support@technikum-wien.at</a>':'')?></td>
			  <td width="56" nowrap>&nbsp;</td>
			  <td class="ContentHeader2" nowrap>&nbsp;Sprechzeiten</td>
			</tr>
			<tr>
		  	  <td colspan="2" nowrap>&nbsp;</td>
		  	  <td nowrap>&nbsp;</td>
		  	  <td nowrap>&nbsp;</td>
		  	  <td nowrap>&nbsp;</td>
		  	</tr>
			<tr>
			  <td width="260" nowrap>Braunstorfer Michael (Leitung)</td>
			  <td width="50" nowrap>240</td>
			  <td nowrap><?php echo ($visible?'<a class="Item" href="mailto:michael.braunstorfer@technikum-wien.at">michael.braunstorfer@technikum-wien.at</a>':'')?></td>
			  <td width="56" nowrap>&nbsp;</td>
			  <td nowrap>Termin nach Vereinbarung</td>
			</tr>
			<tr>
			  <td width="260" nowrap>Ing. Esberger Franz </td>
			  <td width="50" nowrap>243</td>
			  <td nowrap><?php echo ($visible?'<a class="Item" href="mailto:franz.esberger@technikum-wien.at">franz.esberger@technikum-wien.at</a>':'')?></td>
			  <td width="56" nowrap>&nbsp;</td>
			  <td nowrap>Termin nach Vereinbarung</td>
			</tr>
			<tr>
			  <td width="260" nowrap>Vogt Eva</td>
			  <td width="50" nowrap>249</td>
			  <td nowrap><?php echo ($visible?'<a class="Item" href="mailto:eva.vogt@technikum-wien.at">eva.vogt@technikum-wien.at</a>':'')?></td>
			  <td width="56" nowrap>&nbsp;</td>
			  <td nowrap>Termin nach Vereinbarung</td>
			</tr>
			<tr>
			  <td width="260" nowrap>Elgner Richard</td>
			  <td width="50" nowrap>341</td>
			  <td nowrap><?php echo ($visible?'<a class="Item" href="mailto:relgner@technikum-wien.at">relgner@technikum-wien.at</a>':'')?></td>
			  <td width="56" nowrap>&nbsp;</td>
			  <td nowrap>Termin nach Vereinbarung</td>
			</tr>	
			<tr>
		  	  <td colspan="2" nowrap>&nbsp;</td>
		  	  <td nowrap>&nbsp;</td>
		  	  <td nowrap>&nbsp;</td>
		  	  <td nowrap>&nbsp;</td>
		  	</tr>
			<tr>
		      <td width="260" nowrap><strong>Lehre/Lektorensupport</strong></td>
			  <td width="50" nowrap>&nbsp;</td>
			  <td  nowrap>&nbsp;</td>
			  <td width="56" nowrap>&nbsp;</td>
			  <td nowrap>&nbsp;</td>
			</tr>
			<tr>
			  <td width="260" nowrap>Kata Papp </td>
			  <td width="50" nowrap>247</td>
			  <td  nowrap><?php echo ($visible?'<a class="Item" href="mailto:kata.papp@technikum-wien.at">kata.papp@technikum-wien.at</a>':'')?></td>
			  <td width="56" nowrap>&nbsp;</td>
			  <td nowrap>Termin nach Vereinbarung</td>
			</tr>
			<tr>
		  	  <td colspan="2" nowrap>&nbsp;</td>
		  	  <td nowrap>&nbsp;</td>
		  	  <td nowrap>&nbsp;</td>
		  	  <td nowrap>&nbsp;</td>
		  	</tr>
		  	<tr>
			  <td width="280" colspan="2" nowrap class="ContentHeader2">&nbsp;Haustechnik</td>
			  <td  class="ContentHeader2"  nowrap><?php echo ($visible?'<a class="Item" href="mailto:admin@technikum-wien.at">admin@technikum-wien.at</a>':'')?></td>
			  <td width="56" nowrap>&nbsp;</td>
			  <td class="ContentHeader2" nowrap>&nbsp;Sprechzeiten</td>
			</tr>
			<tr>
		  	  <td colspan="2" nowrap>&nbsp;</td>
		  	  <td nowrap>&nbsp;</td>
		  	  <td nowrap>&nbsp;</td>
		  	  <td nowrap>&nbsp;</td>
		  	</tr>
            <tr>
		      <td width="260" nowrap>Staubmann Robert (Leitung)</td>
			  <td width="50" nowrap>250</td>
			  <td  nowrap><?php echo ($visible?'<a class="Item" href="mailto:robert.staubmann@technikum-wien.at">robert.staubmann@technikum-wien.at</a>':'')?></td>
			  <td width="56" nowrap>&nbsp;</td>
			  <td nowrap>Termin nach Vereinbarung</td>
			</tr>
			<tr>
			  <td width="260" nowrap>Nagl Richard</td>
			  <td width="50" nowrap>252</td>
			  <td  nowrap><?php echo ($visible?'<a class="Item" href="mailto:richard.nagl@technikum-wien.at">richard.nagl@technikum-wien.at</a>':'')?></td>
			  <td width="56" nowrap>&nbsp;</td>
			  <td nowrap>Termin nach Vereinbarung</td>
			</tr>
			<tr>
		      <td width="260" nowrap>Harald Weigl </td>
		      <td width="50" nowrap>253</td>
			  <td  nowrap><?php echo ($visible?'<a class="Item" href="mailto:weigl@technikum-wien.at">weigl@technikum-wien.at</a>':'')?></td>
			  <td width="56" nowrap>&nbsp;</td>
			  <td nowrap>Termin nach Vereinbarung</td>
			</tr>
			<tr>
		  	  <td colspan="2" nowrap>&nbsp;</td>
		  	  <td nowrap>&nbsp;</td>
		  	  <td nowrap>&nbsp;</td>
		  	  <td nowrap>&nbsp;</td>
		  	</tr>
			<tr>
			  <td width="280" colspan="2" nowrap class="ContentHeader2">&nbsp;Systementwicklung</td>
			  <td  class="ContentHeader2" nowrap>&nbsp;</td>
			  <td width="56" nowrap>&nbsp;</td>
			  <td class="ContentHeader2" nowrap>&nbsp;Sprechzeiten</td>
			</tr>
			<tr>
		  	  <td colspan="2" nowrap>&nbsp;</td>
		  	  <td nowrap>&nbsp;</td>
		  	  <td nowrap>&nbsp;</td>
		  	  <td nowrap>&nbsp;</td>
		  	</tr>
	  		<tr>
			  <td width="260" nowrap>Dipl. Ing. (FH) Paminger Christian (Leitung)</td>
			  <td width="50" nowrap>245</td>
			  <td  nowrap><?php echo ($visible?'<a class="Item" href="mailto:christian.paminger@technikum-wien.at">christian.paminger@technikum-wien.at</a>':'')?></td>
			  <td width="56" nowrap>&nbsp;</td>
			  <td nowrap>Termin nach Vereinbarung</td>
			</tr>
			<tr>
			  <td width="260" nowrap>Dipl. Ing. (FH) Mag. Hangl Rudolf</td>
			  <td width="50" nowrap>248</td>
			  <td  nowrap><?php echo ($visible?'<a class="Item" href="mailto:rudolf.hangl@technikum-wien.at">rudolf.hangl@technikum-wien.at</a>':'')?></td>
			  <td width="56" nowrap>&nbsp;</td>
			  <td nowrap>Termin nach Vereinbarung</td>
			</tr>
			<tr>
			  <td width="260" nowrap>&Ouml;sterreicher Andreas</td>
			  <td width="50" nowrap>241</td>
			  <td  nowrap><?php echo ($visible?'<a class="Item" href="mailto:andreas.oesterreicher@technikum-wien.at">andreas.oesterreicher@technikum-wien.at</a>':'')?></td>
			  <td width="56" nowrap>&nbsp;</td>
			  <td nowrap>Termin nach Vereinbarung</td>
			</tr>
			<tr>
		  	  <td colspan="2" nowrap>&nbsp;</td>
		  	  <td nowrap>&nbsp;</td>
		  	  <td nowrap>&nbsp;</td>
		  	  <td nowrap>&nbsp;</td>
		  	</tr>
            <tr>
			    <td width="260" nowrap><strong> LV-Koordinationsstelle</strong></td>
			    <td width="50" nowrap>&nbsp;</td>
			  <td  nowrap><?php echo ($visible?'<a class="Item" href="mailto:lvplan@technikum-wien.at">lvplan@technikum-wien.at</a>':'')?></td>
			  <td width="56" nowrap>&nbsp;</td>
			  <td nowrap>&nbsp;</td>
			</tr>
			<tr>
			    <td width="260" nowrap>Ing. Dvorak Andreas</td>
			    <td width="50" nowrap>251</td>
			  <td  nowrap><?php echo ($visible?'<a class="Item" href="mailto:andreas.dvorak@technikum-wien.at">andreas.dvorak@technikum-wien.at</a>':'')?></td>
			  <td width="56" nowrap>&nbsp;</td>
			  <td nowrap>Termin nach Vereinbarung</td>
			</tr>
			<tr>
			  <td width="260" nowrap>Haas Bettina</td>
			  <td width="50" nowrap>257</td>
			  <td  nowrap><?php echo ($visible?'<a class="Item" href="mailto:bettina.haas@technikum-wien.at">bettina.haas@technikum-wien.at</a>':'')?></td>
			  <td nowrap>&nbsp;</td>
			  <td nowrap>Termin nach Vereinbarung</td>
			  </tr>
			<tr>
			    <td width="260" nowrap>Kindl Manfred </td>
			    <td width="50" nowrap>256</td>
			  <td  nowrap><?php echo ($visible?'<a class="Item" href="mailto:kindlm@technikum-wien.at">kindlm@technikum-wien.at</a>':'')?></td>
			  <td width="56" nowrap>&nbsp;</td>
			  <td nowrap>Termin nach Vereinbarung</td>
			</tr>
			<tr>
		  	  <td colspan="2" nowrap>&nbsp;</td>
		  	  <td nowrap>&nbsp;</td>
		  	  <td nowrap>&nbsp;</td>
		  	  <td nowrap>&nbsp;</td>
		  	</tr>
			<tr>
			  <td width="280" colspan="2" nowrap class="ContentHeader2">&nbsp;Systemadministration (Zentrale Services)</td>
			  <td  class="ContentHeader2" nowrap>&nbsp;</td>
			  <td width="56" nowrap>&nbsp;</td>
			  <td class="ContentHeader2" nowrap>&nbsp;Sprechzeiten</td>
			</tr>
			<tr>
		  	  <td colspan="2" nowrap>&nbsp;</td>
		  	  <td nowrap>&nbsp;</td>
		  	  <td nowrap>&nbsp;</td>
		  	  <td nowrap>&nbsp;</td>
		  	</tr>
  		           
          	<tr> 
              <td width="260" nowrap>Kopper Martin (Leitung)</td>
              <td width="50" nowrap>246</td>
              <td  nowrap><?php echo ($visible?'<a class="Item" href="mailto:martin.kopper@technikum-wien.at">martin.kopper@technikum-wien.at</a>':'')?></td>
              <td width="56" nowrap>&nbsp;</td>
              <td nowrap>Termin nach Vereinbarung</td>
          	</tr>
          	<tr> 
              <td width="260" nowrap>Dipl. Ing. Nimmervoll Alexander</td>
              <td width="50" nowrap>242</td>
              <td  nowrap><?php echo ($visible?'<a class="Item" href="mailto:alexander.nimmervoll@technikum-wien.at">alexander.nimmervoll@technikum-wien.at</a>':'')?></td>
              <td width="56" nowrap>&nbsp;</td>
              <td nowrap>Termin nach Vereinbarung</td>
          	</tr>
          	<tr> 
              <td width="260" nowrap>Esberger Franz Ferdinand</td>
              <td width="50" nowrap>346</td>
              <td  nowrap><?php echo ($visible?'<a class="Item" href="mailto:ffe@technikum-wien.at">ferdinand.esberger@technikum-wien.at</a>':'')?></td>
              <td width="56" nowrap>&nbsp;</td>
              <td nowrap>Termin nach Vereinbarung</td>
          	</tr>
            
		  </table>
		</td>
	  </tr>
    </table></td>
	<td width="30">&nbsp;</td>
  </tr>
</table>
</body>
</html>
