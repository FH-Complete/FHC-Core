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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
/*
 * Created on 02.12.2004
 *
 */
// header für no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/xhtml+xml");
// xml
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
// DAO
require_once('../config/vilesci.config.inc.php');
require_once('../include/raumtyp.class.php');

// raumtypen holen
$raumtypDAO=new raumtyp();
$raumtypen=$raumtypDAO->getAll();

$rdf_url='http://www.technikum-wien.at/raumtyp';
?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:RAUMTYP="<?php echo $rdf_url; ?>/rdf#"
>

  <RDF:Seq about="<?php echo $rdf_url ?>/liste">

<?php

foreach ($raumtypDAO->result as $rt)
{
	?>
  <RDF:li>
	<RDF:Description  id="<?php echo $rt->raumtyp_kurzbz; ?>"  about="<?php echo $rdf_url.'/'.$rt->raumtyp_kurzbz; ?>" >
			<RAUMTYP:kurzbz><?php echo $rt->raumtyp_kurzbz  ?></RAUMTYP:kurzbz>
			<RAUMTYP:beschreibung><?php echo $rt->beschreibung  ?></RAUMTYP:beschreibung>
			<RAUMTYP:aktiv><?php echo ($rt->aktiv ? "true" : "false") ?></RAUMTYP:aktiv>
	</RDF:Description>
  </RDF:li>
	  <?php
}
?>
  </RDF:Seq>
</RDF:RDF>
