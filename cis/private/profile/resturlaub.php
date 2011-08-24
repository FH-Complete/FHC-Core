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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
// **
// * @brief Uebersicht der Resturlaubstage

require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/resturlaub.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/phrasen.class.php');

$sprache = getSprache();
$p = new phrasen($sprache);

$uid = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if (!$rechte->isBerechtigt('admin',0) && !$rechte->isBerechtigt('mitarbeiter'))
	die($p->t('global/keineBerechtigungFuerDieseSeite'));

$db = new basis_db();

$jahr=date('Y');
if (date('m')>8)
{
	$datum_beginn_iso=$jahr.'-09-01';
	$datum_beginn='1.Sept.'.$jahr;
	$datum_ende_iso=($jahr+1).'-08-31';
	$datum_ende='31.Aug.'.($jahr+1);
	$geschaeftsjahr=$jahr.'/'.($jahr+1);
}
else
{
	$datum_beginn_iso=($jahr-1).'-09-01';
	$datum_beginn='1.Sept.'.($jahr-1);
	$datum_ende_iso=$jahr.'-08-31';
	$datum_ende='31.Aug.'.$jahr;
	$geschaeftsjahr=($jahr-1).'/'.$jahr;
}

echo '
<html>
<head>
	<title>'.$p->t('zeitsperre/resturlaubstage').'</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
</head>

<body>
	<h1>Resturlaubstage</H1>

	<TABLE >
    <TR class="liste">
    	<TH>'.$p->t('global/nachname').'</TH>
    	<TH>'.$p->t('global/vorname').'</TH>
    	<TH>'.$p->t('zeitsperre/resturlaubstagePerDatum',array($datum_beginn)).'</TH>
    	<TH>'.$p->t('zeitsperre/aktuellerStand').'</TH>
    	<TH>'.$p->t('zeitsperre/resturlaubstagePerDatum',array($datum_ende)).'</TH>
	</TR>
	';
	
$obj=new resturlaub();
$obj->getResturlaubFixangestellte();
$i=0;

foreach ($obj->result as $row)
{
	echo '<TR class="liste'.($i%2).'">';
	echo "<TD>$row->nachname</TD><TD>$row->vorname $row->vornamen</TD>";
	echo "<TD>$row->resturlaubstage</TD>";

	//Urlaub berechnen (date_part('month', vondatum)>9 AND date_part('year', vondatum)='".(date('Y')-1)."') OR (date_part('month', vondatum)<9 AND date_part('year', vondatum)='".date('Y')."')
	$qry = "SELECT 
			(SELECT sum(bisdatum-vondatum+1) as anzahltage FROM campus.tbl_zeitsperre
			 WHERE zeitsperretyp_kurzbz='Urlaub' AND mitarbeiter_uid='".addslashes($row->mitarbeiter_uid)."' AND
			 (
				vondatum>='".addslashes($datum_beginn_iso)."' AND bisdatum<='".addslashes($datum_ende_iso)."'
			 )) as anzahltage,
			 (SELECT sum(bisdatum-vondatum+1) as anzahltage FROM campus.tbl_zeitsperre
			 WHERE zeitsperretyp_kurzbz='Urlaub' AND mitarbeiter_uid='".addslashes($row->mitarbeiter_uid)."' AND
			 (
				vondatum>='".addslashes($datum_beginn_iso)."' AND bisdatum<=now()
			 )) as anzahltageaktuell
			 ";
	$tttt="\n";
	if($result_summe = $db->db_query($qry))
	{
		if($row_summe = $db->db_fetch_object($result_summe))
		{
			$gebuchterurlaub = $row_summe->anzahltage;
			$gebuchterurlaubaktuell = $row_summe->anzahltageaktuell;
		}
	}
	if($gebuchterurlaub=='')
		$gebuchterurlaub=0;
	if($gebuchterurlaubaktuell=='')
		$gebuchterurlaubaktuell=0;
		
	echo '<td>'.($row->urlaubstageprojahr+$row->resturlaubstage-$gebuchterurlaubaktuell).'</td>';
	echo '<td>'.($row->urlaubstageprojahr+$row->resturlaubstage-$gebuchterurlaub).'</td>';
	echo '</TR>';
	$i++;
}
	
	echo '
	</table>
	</body>
</html>';
?>
