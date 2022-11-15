<?php
/* Copyright (C) 2015 fhcomplete.org
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
 * Authors: Manfred Kindl <manfred.kindl@technikum-wien.at>
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/lehrveranstaltung.class.php');
require_once('../../../include/lehreinheitgruppe.class.php');
require_once('../../../include/lehreinheit.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/lehreinheitmitarbeiter.class.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/erhalter.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/lehrelisthelper.class.php');
require_once('../../../include/covid/covidhelper.class.php');

$debug = false;

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$user=get_uid();

$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($user);

if(isset($_GET['lvid']) && is_numeric($_GET['lvid']))
	$lvid = $_GET['lvid'];
else
	die('Eine gueltige LvID muss uebergeben werden');

$lv = new lehrveranstaltung();
$lv->load($lvid);

if(isset($_GET['stsem']))
	$studiensemester = $_GET['stsem'];
else
	die('Eine Studiensemester muss uebergeben werden');

if(	!$berechtigung->isBerechtigt('admin')
	&& !$berechtigung->isBerechtigt('assistenz')
	&& !$berechtigung->isBerechtigt('lehre', $lv->oe_kurzbz, 's')
	&& !check_lektor_lehrveranstaltung($user,$lvid,$studiensemester))
	die('Sie muessen LektorIn der LV sein oder das Recht "ADMIN", "ASSISTENZ" oder "LEHRE" haben, um diese Seite aufrufen zu koennen');

isset($_GET['stg_kz']) ? $studiengang = $_GET['stg_kz'] : $studiengang = NULL;
isset($_GET['lehreinheit_id']) ? $lehreinheit = $_GET['lehreinheit_id'] : $lehreinheit = NULL;

$stg = new studiengang();
$stg->load($lv->studiengang_kz);

$lehrelisthelper = new LehreListHelper($db, $studiensemester, $lvid, $lv, $stg, $lehreinheit);
$arr_lehrende = $lehrelisthelper->getArr_Lehrende();
$data = $lehrelisthelper->getData();
$studentuids = $lehrelisthelper->getStudentUids();

$covidhelper = new CovidHelper();
$covidhelper->fetchCovidStatus($studentuids);
$covidstatus = $covidhelper->getCovidStatus();

$now = new DateTime('now', new DateTimeZone('Europe/Vienna'));
header('Content-Type: text/html; charset=utf-8');
?>
<!<!doctype html>
<html>
<head>
	<title>FHC - Nachweisliste</title>
	<link rel="stylesheet" type="text/css" href="../../../vendor/twbs/bootstrap3/dist/css/bootstrap.min.css">
	<link href="../../../vendor/fortawesome/font-awesome4/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
</head>
<body>

<div class="container-fluid">
	<div class="row">
		<div class="col-lg-6">
			
			<h1>Nachweisliste "<?php echo $data['bezeichnung']; ?>"</h1>
			<ul>
				<li><strong>Gruppen</strong>: <?php echo $data['gruppen']; ?></li>
				<li><strong>Studiensemester</strong>: <?php echo $data['studiensemester']; ?></li>
				<li><strong>Lehrende</strong>: <?php echo $lehrelisthelper->getLehrende_String(); ?></li>
				<li><strong>generiert</strong>: <?php echo $now->format('d.m.Y H:i'); ?></li>
				<li><strong>Anzahl der Studierenden</strong>: <?php echo $data['anzahl_studierende']; ?></li>
			</ul>
			
			<table class="table table-striped table-hover table-condensed">
				<thead>
					<tr>
						<th>lfd.Nr.</th>
						<th>Name</th>
						<th>Kennzeichen</th>
						<th>Gruppe</th>
						<th>Nachweis</th>
					</tr>
				</thead>
				<tbody>
<?php
$len = strlen($data['anzahl_studierende']);
$lfdnr = 1;
foreach ($data as $value) 
{
	if( !(is_array($value) && isset($value['student'])) ) 
	{
		continue;
	}
	$tmpstudent =& $value['student']; 
?>
					<tr class="<?php echo $covidhelper->getBootstrapClass($tmpstudent['uid'])?>" title="<?php echo $covidhelper->getTitle($tmpstudent['uid'])?>">
						<td><?php echo sprintf('%0' . $len . 'd', $lfdnr); ?></td>
						<td><?php echo $tmpstudent['nachname'] . ' ' . $tmpstudent['vorname'] . ' ' . $tmpstudent['zusatz']; ?></td>
						<td><?php echo $tmpstudent['personenkennzeichen']; ?></td>
						<td><?php echo $tmpstudent['semester'] . $tmpstudent['verband'] . $tmpstudent['gruppe']; ?></td>
						<td><?php echo $covidhelper->getIconHtml($tmpstudent['uid']); ?></td>
					</tr>
<?php
	$lfdnr++;
}
?>
				</tbody>
			</table>
	
		</div>
	</div>

<?php
if( $debug ) 
{
?>
	<div class="row">
		<div class="col-lg-6">
			
			<div class="debug">
				<pre>
<?php
print_r($data);
print_r($covidstatus);
?>
				</pre>
			</div>
			
		</div>
	</div>
<?php
}
?>

</div>

</body>
</html>
