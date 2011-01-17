<?php
/* Copyright (C) 2010 Technikum-Wien
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
 *          Karl Burkhart <burkhart@technikum-wien.at
 */
/**
 * Enthaelt das Array fuer die Menuepunkt der WaWi-Seite
 */
$menu=array
(
	'Administration'=> 		array
	(
		'name'=>'Administration', 'opener'=>'true', 'hide'=>'false', 'permissions'=>array('wawi/kostenstelle','wawi/konto','wawi/budget'), 

		'Konto'=>array
		(
			'name'=>'Konto', 'permissions'=>array('wawi/konto'),'link'=>'kontouebersicht.php', 'target'=>'content',
			'KontoNeu'=>array('name'=>'Neu', 'link'=>'kontouebersicht.php?method=update', 'target'=>'content'),
			'KontoZusammenlegen'=>array('name'=>'Zusammenlegen', 'link'=>'kontouebersicht.php?method=merge', 'target'=>'content'),
		),
		'Kostenstelle'=>array
		(
			'name'=>'Kostenstelle', 'permissions'=>array('wawi/kostenstelle'),'link'=>'kostenstellenuebersicht.php', 'target'=>'content',
			'KostenstelleNeu'=>array('name'=>'Neu', 'link'=>'kostenstellenuebersicht.php?method=update', 'target'=>'content'),
			'KostenstelleZusammenlegen'=>array('name'=>'Zusammenlegen', 'link'=>'kostenstellenuebersicht.php?method=merge', 'target'=>'content'),
			'Budgeteingabe'=>array('name'=>'Budgeteingabe', 'permissions'=>array('wawi/budget'), 'link'=>'budget.php', 'target'=>'content'),
		),
	),
	'Benutzerbereich'=> 	array
	(
		'name'=>'Benutzerbereich', 'opener'=>'true', 'hide'=>'false','permissions'=>array('wawi/bestellung','wawi/rechnung'),
		'Bestellung'=>array
		(
			'name'=>'Bestellung', 'permissions'=>array('wawi/bestellung'),'link'=>'bestellung.php?method=suche', 'target'=>'content',
			'BestellungNeu'=>array('name'=>'Neu', 'link'=>'bestellung.php?method=new', 'target'=>'content'),
		),
		'Rechnung'=>array
		(
			'name'=>'Rechnung', 'permissions'=>array('wawi/rechnung'),'link'=>'rechnung.php?method=suche', 'target'=>'content',
			'RechnungNeu'=>array('name'=>'Neu', 'link'=>'rechnung.php?method=new', 'target'=>'content'),
		),
		'Personensuche'=>array
		(
			'name'=>'Personensuche', 'link'=>'https://cis.technikum-wien.at/cis/private/tools/psearch.php', 'target'=>'content',
		),
		'Firma'=>array
		(
			'name'=>'Firma', 'link'=>'firma.php', 'target'=>'content','permissions'=>array('wawi/firma'),
			'FirmaNeu'=>array('name'=>'Neu', 'link'=>'firma.php?method=new', 'target'=>'content'),
		)
	),
	'Berichte'=> 	array
	(
		'name'=>'Berichte', 'opener'=>'true', 'hide'=>'false','permissions'=>array('wawi/bestellung','wawi/rechnung'),
		'Tags'=>array
		(
			'name'=>'Tags', 'permissions'=>array('wawi/bestellung'),'link'=>'auswertungen/wawi_tags.php', 'target'=>'content',
		)
	)
	
);
?>