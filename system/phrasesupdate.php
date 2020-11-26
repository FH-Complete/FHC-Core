<?php
/* Copyright (C) 2013 FH Technikum-Wien
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
 * Authors: Cristina Hainberger	<hainberg@technikum-wien.at>
 *
 * Beschreibung:
 * The script checks phrases and phrase-texts for actuality in the database.
 * Missing attributes are inserted.
 */

//flag for at least one new phrase
$new = false;


$phrases = array(
	//*******************	CORE/global
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'alle',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'alle',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'all',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'zeilen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zeilen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'lines',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'titel',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Titel',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'title',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'uebersicht',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Übersicht',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'overview',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'details',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Details',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'details',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'waehlen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'wählen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'select',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'vollstaendig',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'vollständig',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'complete',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'unvollstaendig',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'unvollständig',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'incomplete',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'betreff',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Betreff',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'subject',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'sender',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Sender',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'sender',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'empfaenger',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Empfänger',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'receiver',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'gesendetAm',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'gesendet am',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'sent on',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'gelesenAm',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'gelesen am',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'read on',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'datum',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Datum',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'date',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'freigeben',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'freigeben',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'approve',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'letzterBearbeiter',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'letzter Bearbeiter',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'last change',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'letzteAktion',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'letzte Aktion',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'last action',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'gesperrtVon',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'gesperrt von',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'locked by',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'sperrdatum',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'sperrdatum',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'locking date',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'anzahl',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anzahl',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'amount',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'abgeschickt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'abgeschickt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'sent',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'inaktiv',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'inaktiv',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'inactive',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'aktiv',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'aktiv',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'active',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'gesendet',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'gesendet',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'sent',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'nichtGesendet',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'nicht gesendet',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'not sent',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'kontakt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kontakt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'contact',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'typ',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Typ',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'type',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'anmerkung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anmerkung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'note',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'name',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Name',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'name',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'stammdaten',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Stammdaten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'master data',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'uploaddatum',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Uploaddatum',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'upload date',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'letzterStatus',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'letzter Status',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'last status',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'nachrichten',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nachrichten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Messages',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'aktivitaeten',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Aktivitäten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'activities',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'notizen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Notizen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'notes',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'notiz',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Notiz',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'note',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'aktivitaet',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Aktivität',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'activity',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'hinzufuegen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'hinzufügen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'add',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'wirdBearbeitetVon',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'wird bearbeitet von',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'edited by',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'nichtAbgeschickt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'nicht abgeschickt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'not sent',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'anStudiengangFreigegeben',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'an Studiengang freigegeben',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'approved for the course',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'zumReihungstestFreigegeben',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'zum Reihungstest freigegeben',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'approved for placement test',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'nachricht',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nachricht',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Message',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'vorschau',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Vorschau',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'preview',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'vorlage',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Vorlage',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'template',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'bis',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'bis',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'until',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),

	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'mailAnXversandt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Mail an {email} versandt.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Mail was sent to {email}.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),

	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'beschreibung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Beschreibung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'description',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),

	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'nichtvorhanden',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'n.v.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'n/a',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),

	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'ohne',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'ohne',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'without',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),

	//*******************************		CORE/ui
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'speichern',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Speichern',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Save',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'abbrechen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Abbrechen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Break',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'loeschen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Löschen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Delete',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'entfernen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Entfernen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Remove',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'freigeben',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Freigeben',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Release',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'freigabeart',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Freigabeart',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Approval type',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'freigabeAnStudiengang',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Freigabe an Studiengang',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Approve for study program',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'freigabeZumReihungstest',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Freigabe zum Reihungstest',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Approve for placement test',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'nachrichtSenden',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nachricht senden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Send message',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'senden',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Senden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Send',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'anwenden',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anwenden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Apply',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'hinzufuegen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Hinzufügen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Add',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'absagen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Absagen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Cancel',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'bitteWaehlen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bitte wählen...',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Please select...',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'bitteEintragWaehlen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bitte Eintrag wählen...',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Please select entry...',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'keineEintraegeGefunden',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Keine Einträge gefunden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No entries found',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'felder',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Felder',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'fields',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'vorlageWaehlen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Vorlage wählen...',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Select template',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'fehlerBeimLesen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehler beim Lesen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error on Reading',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'fehlerBeimSpeichern',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehler beim Speichern',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error on Saving',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'gespeichert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Gespeichert',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Saved',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),


	//***************************	CORE/filter
	array(
		'app' => 'core',
		'category' => 'filter',
		'phrase' => 'filterEinstellungen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Filter Einstellungen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'filter settings',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),

	array(
		'app' => 'core',
		'category' => 'filter',
		'phrase' => 'filterHinzufuegen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Filter hinzufügen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'add filter',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),

	array(
		'app' => 'core',
		'category' => 'filter',
		'phrase' => 'feldHinzufuegen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Feld hinzufügen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'add field',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'filter',
		'phrase' => 'filterBeschreibung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Filter Beschreibung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'filter description',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),

	//****************************	 CORE/person
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'student',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Student',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'student',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
			'app' => 'core',
			'category' => 'person',
			'phrase' => 'vorname',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Vorname',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'first name',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'nachname',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nachname',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'last name',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'username',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Username',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'username',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'anrede',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anrede',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Address',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'uid',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'UID',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'UID',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'mann',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Mann',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Man',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'frau',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Frau',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Woman',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'staatsbuergerschaft',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Staatsbürgerschaft',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'citizenship',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'geburtsdatum',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Geburtsdatum',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'date of birth',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'svnr',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Sozialversicherungsnummer',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Social insurance number',
				'description' => 'social security number',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'ersatzkennzeichen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ersatzkennzeichen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Replacement bearing',
				'description' => 'Replacement Label',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'bpk',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'bPK',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'bPK',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'geschlecht',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Geschlecht',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'gender',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'geburtsnation',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Geburtsnation',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'country of birth',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'geburtsort',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Geburtsort',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'place of birth',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'email',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'eMail',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'email',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'telefon',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Telefon',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'phone',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'adresse',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Adresse',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'address',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'nation',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nation',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'nation',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'ort',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ort',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'place',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'postleitzahl',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Postleitzahl',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Post code',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'strasse',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Strasse',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Street',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'titelpre',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'TitelPre',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'TitlePre',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'titelpost',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'TitelPost',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'TitlePost',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),

	//****************	CORE/lehre
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'studiensemester',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Studiensemester',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'semester',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'ausbildungssemester',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ausbildungssemester',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Education semester',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'organisationsform',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Organisationsform',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'organisational form',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'organisationseinheit',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Organisationseinheit',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'organisation unit',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'gruppe',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Gruppe',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'group',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'studiengang',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Studiengang',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'degree-program',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'studienrichtung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Studienrichtung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'degree-program',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'pruefung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Prüfung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'examination',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'master',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Master',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Master',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'ects',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'ECTS',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'ECTS',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'sws',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'SWS',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'SP/W',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'pflichtfach',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Pflichtfach',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Mandatory',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'zeugnis',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zeugnis',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Transcript',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'notendurchschnitt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Notendurchschnitt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Grade average',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'gewichteternotendurchschnitt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'gewichteter Notendurchschnitt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'weighted grade point average',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'note',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Note',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Grade',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'lehrveranstaltung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Lehrveranstaltung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Course',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'lehreinheit',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'LV-Teil',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'teaching unit',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'kurzbz',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kurzbz',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'ShortDesc',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'semester',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Semester',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Semester',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'studienplan',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Studienplan',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'study plan',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'lektor',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Lektor',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'lector',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'nichtstudienplanrelevanteKurse',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nicht studienplanrelevante Lehrveranstaltung',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'info_notendurchschnitt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Notendurchschnitt über alle studienplanrelevanten Noten (inkl. negative)',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'info_notendurchschnitt_gewichtet',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Notendurchschnitt über alle studienplanrelevanten Noten (inkl. negative) gewichtet nach ECTS der LV.  = (Summe (Note der LV * ECTS der LV))/Gesamtsumme der ECTS',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'lehrform',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Lehrform',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Course Type',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	//**********************	INFOCENTER/infocenter
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'infocenter',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Infocenter',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Infocenter',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'dokumentenpruefung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Dokumentenprüfung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'document check',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'zgvPruefung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'ZGV Prüfung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'ZGV exam',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'zgvOrt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'ZGV Ort',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'ZGV place',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'zgvDatum',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'ZGV Datum',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'ZGV date',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'zgvNation',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'ZGV Nation',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'ZGV nation',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'bewerbung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bewerbung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'application',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'bewerber',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'BewerberIn',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'applicant',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'reifepruefung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Reifeprüfung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Graduate',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'reifepruefungszeugnis',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Reifeprüfungszeugnis',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Leaving certificate',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'bewerbungAbgeschickt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bewerbung abgeschickt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'application sent',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'ausstellungsnation',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ausstellungsnation',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'issuing country',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'formalGeprueft',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'formal geprüft',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'formally checked',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'nachzureichendeDokumente',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'nachzureichende Dokumente',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'documents to be hand in later',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'nachzureichenAm',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'nachzureichen am',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'to be delivered on',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'anmerkungenZurBewerbung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anmerkungen zur Bewerbung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Application Notes',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'zugangBewerbung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zugang Bewerbung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Access application',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'zgv',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'ZGV',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'ZGV',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'zugangsvoraussetzung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zugangsvoraussetzung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Access requirements',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'zugangsvoraussetzungen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zugangsvoraussetzungen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Entry requirements',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'keineZugangsvoraussetzungenTxt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Keine Zugangsvoraussetzungen für den Studiengang definiert',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No admission requirements defined for the course',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'letzteZgvUebernehmen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'letzte ZGV übernehmen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'last ZGV attended',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'absagegrund',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Absagegrund',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Reason for cancellation',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'absage',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Absage',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Cancellation',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'absageBestaetigen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Absage bestätigen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Confirm cancellation',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'absageBestaetigenTxt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bei Absage von InteressentInnen erhalten diese den Status "Abgewiesener" und deren ZGV-Daten können im Infocenter nicht mehr bearbeitet oder freigegeben werden. Alle nicht gespeicherten ZGV-Daten gehen verloren. Fortfahren?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'If interested parties are rejected, they receive the status "rejected" and their ZGV data can no longer be edited or released in the Info Center. All ZGV data that has not been saved will be lost. Continue?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'notizHinzufuegen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Notiz hinzufügen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Add note',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'tageKeineAktion',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Tage keine Aktion',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'days no action',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'anAusgewaehlte',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'an Ausgew&auml;hlte',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'to selected ones',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'interessentAbweisen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'InteressentIn abweisen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'reject applicant',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'interessentFreigeben',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'InteressentIn freigeben',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Approve applicant',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'interessentFreigebenTxt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bei Freigabe von InteressentInnen wird deren Interessentenstatus bestätigt und deren Zgvdaten können im Infocenter nicht mehr bearbeitet oder freigegeben werden.<br/> Alle nicht gespeicherten Zgvdaten gehen verloren.<br/> Fortfahren?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'If interested parties are released, their interested party status is confirmed and their Zgv data can no longer be edited or released in the Infocenter. <br/> All Zgv data not saved will be lost. <br/> Continue?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'freigabeBestaetigen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Freigabe bestätigen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Confirm approval',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'nachfrist',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nachfrist',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'extended deadline',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'bewerbungsfrist',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bewerbungsfrist',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'application deadline',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'notizAendern',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Notiz ändern',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Change note',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'parken',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'parken',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'park',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'ausparken',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'ausparken',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'unpark',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'geparkt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'geparkt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'parked',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'priorisierung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'prio',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'prio',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'parkdatum',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'parkdatum',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'parking date',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'rueckstelldatum',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'rückstelldatum',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'onHold date',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'bewerberParken',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'BewerberIn parken',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Park applicant',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'bewerberAusparken',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'BewerberIn ausparken',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Unpark applicant',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'nichtsZumAusparken',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nichts zum ausparken',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Nothing to park out',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'fehlerBeimAusparken',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehler beim Ausparken',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Parking error',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'fehlerBeimParken',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehler beim Parken',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Parking error',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'bewerberGeparktBis',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'BewerberIn geparkt bis',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Applicant parked until',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'bewerbungMussAbgeschickt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Die Bewerbung muss erst abgeschickt worden sein.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The application needs to be sent first.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'nurBachelorFreigeben',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nur Bachelorstudiengänge können freigegeben werden.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Only bachelor programmes can be approved.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'bewerberOnHold',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'BewerberIn zurückstellen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Put applicant on hold',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'bewerberOnHoldEntfernen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zurückstellung entfernen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Remove on hold state',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'bewerberOnHoldBis',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'BewerberIn zurückgestellt bis',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Applicant on hold until',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'nichtsZumEntfernen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nichts zum Entfernen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Nothing to remove',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'fehlerBeimEntfernen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehler beim Entfernen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error when removing',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'rueckstelldatumUeberschritten',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zurückstelldatum überschritten!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Exceeded date for on hold!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'parkenZurueckstellenInfo',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Geparkte und zurückgestellte BewerberInnen werden von der Bearbeitung temporär ausgenommen.
Geparkte BewerberInnen werden zum angegebenen Datum automatisch entparkt, während zurückgestellte BewerberInnen nur manuell durch Drücken des Buttons den Zurückgestellt-Status verlieren.
Bei einer Zurückstellung dient das Datum nur der Erinnerung.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Parked applicants and applicants on hold are temporarily excluded from the infocenter workflow.
Parked applicants are unparked automatically, whereas applicants on hold loose the status only when clicking the button manually.
When on hold, the date is only a reminder.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'rtPunkteEintragenInfo',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Es existierte bereits ein Bewerberstatus und eine Reihungstestteilnahme.
				Deshalb wurde bei der Freigabe der Bewerberstatus automatisch hinzugefügt und der Bewerber als Reihungstestabsolvent markiert.
				Die Reihungstestpunkte müssen aber noch manuell eingetragen werden!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'An applicant status and a placement test participation already existed for this person.
				Thus, the applicant status was added automatically and the applicant was marked as placement test participant.
				However, the placement test result is yet to be entered manually!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
    array(
        'app' => 'infocenter',
        'category' => 'infocenter',
        'phrase' => 'rtErgebnisExistiert',
        'insertvon' => 'system',
        'phrases' => array(
            array(
                'sprache' => 'German',
                'text' => 'Es existiert bereits ein RT-Ergebnis',
                'description' => '',
                'insertvon' => 'system'
            ),
            array(
                'sprache' => 'English',
                'text' => 'Placement test result already exists',
                'description' => '',
                'insertvon' => 'system'
            )
        )
    ),
	array(
		'app' => 'core',
		'category' => 'password',
		'phrase' => 'changeFor',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Passwort ändern für',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Changing password for',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'password',
		'phrase' => 'usage',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Das Passwort muss zumindest 8 Zeichen enthalten, davon mindestens 1 Großbuchstabe, 1 Kleinbuchstabe und eine Ziffer.<br />Das Passwort darf keine Leerzeichen und Umlaute enthalten.<br />Erlaubte Sonderzeichen sind: -$#[]{}!().,*:;_',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The password must contain at least 8 characters, of which 1 must be upper case, 1 lower case and 1 a numeral.<br><br>The password may not include spaces or umlauts.<br>The following special characters are allowed: -$#[]{}!().,*:;_',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'password',
		'phrase' => 'extraUsage',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Weitere Informationen zur Passwort Policy finden Sie unter <a href="../../../../cms/dms.php?id={PASSWORD_POLICY_DMS}">diesem Link</a>',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'More information about the Password Policy can be found at <a href="../../../../cms/dms.php?id={PASSWORD_POLICY_DMS}">this link</a>',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'password',
		'phrase' => 'password',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Passwort',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'password',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'password',
		'phrase' => 'old',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Altes Passwort',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Old password',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'password',
		'phrase' => 'new',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Neues Passwort',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'New password',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'password',
		'phrase' => 'newRepeat',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Wiederholung des neuen Passworts',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Repeat new password',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'password',
		'phrase' => 'change',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Passwort ändern',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Change password',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'password',
		'phrase' => 'pageTitle',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Passwort ändern',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Changing password',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'password',
		'phrase' => 'missingParameters',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bitte geben Sie das alte und neue Passwort ein',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Please enter the old and the new password',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'password',
		'phrase' => 'oldPasswordWrong',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Das alte Passwort ist nicht korrekt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The old password is incorrect',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'password',
		'phrase' => 'newNotSameRepeat',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Passwörter stimmen nicht überein',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Passwords do not match',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'password',
		'phrase' => 'length',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Das neue Passwort muss mindestens 8 Zeichen lang sein.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The new password must contain at least 8 characters.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'password',
		'phrase' => 'atLeastAUpperCase',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Das neue Passwort muss mindestens einen Grossbuchstaben enthalten.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The new password must contain at least 1 upper case character.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'password',
		'phrase' => 'atLeastANumber',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Es muss mindestens eine Ziffer vorhanden sein.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The new password must contain at least 1 numeral character.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'password',
		'phrase' => 'genericError',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Es ist ein Fehler aufgetreten. Passwortänderung fehlgeschlagen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'An Error occured. Password change failed.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'password',
		'phrase' => 'changed',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Passwort wurde erfolgreich geändert',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Password successfully changed',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'password',
		'phrase' => 'noBlanks',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Es darf kein Leerzeichen im Passwort vorkommen.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The password may not include spaces.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'password',
		'phrase' => 'noUmlauts',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Es dürfen keine Umlaute verwendet werden.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The password may not include umlauts.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'password',
		'phrase' => 'noSpecialCharacters',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bitte verwenden Sie nur erlaubte Sonderzeichen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Please use only permitted special characters.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'password',
		'phrase' => 'atLeastALowerCase',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Das neue Passwort muss mindestens einen Kleinbuchstaben enthalten.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The new password must contain at least 1 lower case character.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'password',
		'phrase' => 'newSameOld',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Das neue Passwort muss sich vom alten Passwort unterscheiden.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The new password must be different from the old password.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'password',
		'phrase' => 'wrongPassword',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Falsches Passwort',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Wrong password',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'password',
		'phrase' => 'passwordMissing',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Passwort fehlt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Password missing',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'status',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Status',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Status',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'lehrauftraegeBestellen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Lehrauftr&auml;ge bestellen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Order lectureships',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'lehrauftraegeErteilen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Lehraufträge erteilen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Approve lectureships',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'lehrauftraegeAnnehmen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Lehraufträge annehmen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Accept lectureships',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'stornierteLehrauftraege',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Stornierte Lehraufträge',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Cancelled lectureships',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'account',
		'phrase' => 'title',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Account Aktivierung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Account Activation',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'account',
		'phrase' => 'usage',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bitte wählen Sie ein Passwort für Ihren Account.<br>Das Passwort muss zumindest 8 Zeichen enthalten, davon mindestens 1 Großbuchstabe, 1 Kleinbuchstabe und eine Ziffer.<br>Das Passwort darf keine Leerzeichen und Umlaute enthalten.<br>Erlaubte Sonderzeichen sind: -$#[]{}!().,*:;_',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Please choose a password for your account<br>The password must contain at least 8 characters, of which 1 must be upper case, 1 lower case and 1 a numeral.<br>The password may not include spaces or umlauts.<br>The following special characters are allowed: -$#[]{}!().,*:;_',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'account',
		'phrase' => 'username',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Username',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Username',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'account',
		'phrase' => 'code',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Code',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Code',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'captcha',
		'phrase' => 'label',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Tippen Sie die angezeigten<br>Zeichen in das untere Feld.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Enter the characters in<br>the field below.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'captcha',
		'phrase' => 'reload',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ich kann das Bild nicht lesen - neu laden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Reload picture',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'account',
		'phrase' => 'activate',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Abschicken',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Activate',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'password',
		'phrase' => 'wrongCaptcha',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Captcha code falsch ',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Captcha code is wrong',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'account',
		'phrase' => 'missingParameters',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bitte geben Sie Benutzername, Code und Passwort ein',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Please enter username, code and password',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'account',
		'phrase' => 'wrongActivationCode',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Der angegebene Aktivierungscode ist falsch',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The provided activation code is wrong',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'received',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Empfangen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Received',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'reply',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Antworten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Reply',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'altRecipientNote',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '* Diese Nachricht wird an das Infocenter der FHTW zugestellt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '* This message will be delivered to the Infocenter of UAS Technikum Wien',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'refresh',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Aktualisierung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Refresh',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'from',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Von',
				'description' => 'Aktualisierung',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'From',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'newMessage',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Sie haben eine neue Nachricht erhalten',
				'description' => 'Aktualisierung',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'You received a new message',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'backToReadWriteMessage',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zurück zur Inbox/Outbox',
				'description' => 'Aktualisierung',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Back to Inbox/Outbox',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'dms',
		'phrase' => 'informationsblattExterneLehrende',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '<a href="../../../cms/dms.php?id={DMS_ID_INFOBLATT_EXTERNE_LEHRENDE}" target="_blank">Informationsblatt für externe Lehrende</a>',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '<a href="../../../cms/dms.php?id={DMS_ID_INFOBLATT_EXTERNE_LEHRENDE}" target="_blank">Information sheet for external lecturers</a>', // TODO: change to dms id as soon as english info sheet is available
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'hilfeZuDieserSeite',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Hilfe zu dieser Seite',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Guide to this site',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'anzeigen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anzeigen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Show',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'alleAnzeigen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Alle anzeigen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Show all',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'nurNeueAnzeigen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nur neue anzeigen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Show only new ones',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'nurGeaenderteAnzeigen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nur geänderte anzeigen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Show only changed ones',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'nurBestellteAnzeigen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nur bestellte anzeigen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Show only ordered ones',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'nurErteilteAnzeigen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nur erteilte anzeigen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Show only approved ones',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'nurAngenommeneAnzeigen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nur angenommene anzeigen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Show only accepted ones',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'nurStornierteAnzeigen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nur stornierte anzeigen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Show only cancelled ones',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'nurDummiesAnzeigen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nur Dummies anzeigen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Show only dummies',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'alleAuswaehlen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Alle ausw&auml;hlen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Select all',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'alleAbwaehlen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Alle abw&auml;hlen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Deselect all',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'ausgewaehlteZeilen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ausgew&auml;hlte Zeilen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Selected rows',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'lehrauftrag',
		'category' => 'ui',
		'phrase' => 'nurVerplanteOhneLektorAnzeigen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nur verplante ohne Lektor anzeigen (Dummies)',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Show only planned without lectors (dummies)',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'bestellt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'bestellt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'ordered',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'erteilt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'erteilt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'approved',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'angenommen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'angenommen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'accepted',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'storniert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'storniert',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'cancelled',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'bestelltVon',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'bestellt von ',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'ordered by ',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'erteiltVon',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'erteilt von ',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'approved by ',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'angenommenVon',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'angenommen von ',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'accepted by ',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'storniertVon',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'storniert von ',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'cancelled by ',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'storniertAm',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'storniert am ',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'cancelled on ',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'von',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'von',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'by',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'stunden',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Stunden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Hours',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'betrag',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Betrag',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'amount',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'vertrag',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Vertrag',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'contract',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'bezeichnung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bezeichnung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'title',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'kz',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'KZ',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'ID',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'projekt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Projekt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'project',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'projektarbeit',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Projektarbeit',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'project work',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'tabelleneinstellungen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Tabelleneinstellungen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Table settings',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'hilfe',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Hilfe',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Help',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'keineDatenVorhanden',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Keine Daten vorhanden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No data available',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'spaltenEinstellen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Spalten einstellen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Column settings',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'stunde',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Stunde',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Hour',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'minute',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Minute',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Minute',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'personalnummer',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Personalnummer',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'personnel number',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'stundensatz',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Stundensatz',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'hourly rate',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'am',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'am',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'on',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'lehrauftrag',
		'category' => 'ui',
		'phrase' => 'lehrauftragInBearbeitung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Lehrauftrag in Bearbeitung.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Teaching lectureship in progress.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'lehrauftrag',
		'category' => 'ui',
		'phrase' => 'wartetAufErteilung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Wartet auf Erteilung.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Waiting for approvement.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'lehrauftrag',
		'category' => 'ui',
		'phrase' => 'wartetAufErneuteErteilung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Wartet auf erneute Erteilung.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Waiting for re-approvement',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'lehrauftrag',
		'category' => 'ui',
		'phrase' => 'letzterStatusBestellt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Letzter Status: Bestellt. Wartet auf Erteilung.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Last status: Ordered. Waiting for approvement.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'lehrauftrag',
		'category' => 'ui',
		'phrase' => 'letzterStatusErteilt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Letzter Status: Erteilt. Wartet auf Annahme durch Lektor.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Last status: Approved. Waiting for the lector\'s acceptance.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'lehrauftrag',
		'category' => 'ui',
		'phrase' => 'letzterStatusAngenommen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Letzter Status: Angenommen. Vertrag wurde beidseitig abgeschlossen.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Last status: Accepted. Contract is mutually concluded.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'lehrauftrag',
		'category' => 'ui',
		'phrase' => 'vertragWurdeStorniert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Vertrag wurde storniert.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Contract was cancelled.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'stundenStundensatzGeaendert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Stunden / Stundensatz ge&auml;ndert.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Hours / Hourly rate changed',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'lehrauftrag',
		'category' => 'ui',
		'phrase' => 'neuerLehrauftragOhneLektorVerplant',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Neuer Lehrauftrag. Ohne Lektor verplant.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'New teaching lectureship. No lector assigned yet.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'lehrauftrag',
		'category' => 'ui',
		'phrase' => 'wartetAufBestellung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Wartet auf Bestellung. Danach Erteilen m&ouml;glich.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Waiting for order. Afterwards you can approve.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'lehrauftrag',
		'category' => 'ui',
		'phrase' => 'wartetAufErneuteBestellung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Wartet auf erneute Bestellung. Danach erneut Erteilen m&ouml;glich.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Waiting for re-order. Afterwards you can re-approve.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'lehrauftrag',
		'category' => 'ui',
		'phrase' => 'neuerLehrauftragWartetAufBestellung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Neuer Lehrauftrag. Wartet auf Bestellung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'New teaching lectureship. Waiting for order.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'lehrauftrag',
		'category' => 'ui',
		'phrase' => 'nachAenderungStundensatzStunden',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'NACH Änderung: Stundensatz: {0}  Stunden: {1}',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'AFTER change: Hourly rate: {0}  Hours: {1}',
				'description' => 'Hours',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'lehrauftrag',
		'category' => 'ui',
		'phrase' => 'vorAenderungStundensatzStunden',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'VOR Änderung: Stundensatz: {0}  Stunden: {1}',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'BEFORE change: Hourly rate: {0} Hours: {1}',
				'description' => 'Hours',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'lehrauftrag',
		'category' => 'ui',
		'phrase' => 'ungueltigeParameter',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ungültige Parameter',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Invalid parameters',
				'description' => 'Hours',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'table',
		'phrase' => 'spaltenEinAusblenden',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Spalten ein- und ausblenden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Show and hide columns',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'table',
		'phrase' => 'spaltenEinAusblendenMitKlickOeffnen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '
					Mit einem Klick auf <button><i class="fa fa-cog"></i></button> werden die Einstellungen ge&ouml;ffnet.
				',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Click on <button><i class = "fa fa-cog"></i></button> to open the settings',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'table',
		'phrase' => 'spaltenEinAusblendenAufEinstellungenKlicken',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Auf Spalteneinstellungen klicken',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Click on column settings',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'table',
		'phrase' => 'spaltenEinAusblendenMitKlickAktivieren',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '
					Durch (wiederholtes) Klicken auf ein Feld mit dem Spaltennamen wird die entsprechende Spalte in der
					Tabelle ein- bzw. ausgeblendet
				',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '
					By selecting / deselecting a column name, the corresponding column is shown / hidden in the table
				',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'table',
		'phrase' => 'spaltenEinAusblendenMitKlickSchliessen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Mit einem Klick auf <button><i class="fa fa-cog"></i></button>  werden die Einstellungen
					wieder geschlossen.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Click on <button><i class = "fa fa-cog"></i></button> to close the settings',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'table',
		'phrase' => 'spaltenbreiteVeraendern',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Spaltenbreite verändern',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Change column width',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'table',
		'phrase' => 'spaltenbreiteVeraendernText',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Um die Spaltenbreite zu ver&auml;ndern, fährt man im Spaltenkopf langsam  mit dem Mauszeiger auf den
					rechten Rand der entprechenden Spalte. <br>
					Sobald sich der Mauszeiger in einen Doppelpfeil verwandelt, wird die Maustaste geklickt und mit
					gedr&uuml;ckter Maustaste die Spalte nach rechts erweitert oder nach links verkleinert.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'To change the column width, slowly hover  with the mouse pointer on the right edge of the
					corresponding column header. <br>
					As soon as the mouse pointer changes into a double arrow, click the mouse button and keep it pressed
					while expanding the column width to the right or reducing it to the left.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'table',
		'phrase' => 'spaltenbreiteVeraendernInfotext',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Alle individuellen Tabelleneinstellungen werden in  Ihrem Browser Cache gespeichert. Wenn Sie Ihren
					Browser Cache l&ouml;schen, werden Ihre Einstellungen zurückgesetzt und  müssen gegebenenfalls neu
					eingestellt werden.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'All individual table settings are saved in your browser cache. If you clear your browser
					cache, your settings will be erased. You will then need to reset them again.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'table',
		'phrase' => 'zeilenAuswaehlen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zeilen auswählen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Select rows',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'table',
		'phrase' => 'zeilenAuswaehlenEinzeln',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Einzeln ausw&auml;hlen: <kbd>Strg</kbd> + Klick auf einzelne Zeile(n)',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Select individually: <kbd> Ctrl </kbd> + click on single line (s)',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'table',
		'phrase' => 'zeilenAuswaehlenBereich',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bereich ausw&auml;hlen: <kbd>Shift</kbd> + Klick auf Anfangs- und Endzeile',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Select a range: <kbd> Shift </kbd> + click on the start and end line',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'table',
		'phrase' => 'zeilenAuswaehlenAlle',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Alle ausw&auml;hlen: Button \'Alle ausw&auml;hlen\'',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Select all: Button \'Select all \' ',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'lehrauftragStandardBestellprozess',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Lehrauftrag Standard-Bestellprozess',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Standard Ordering Process for Teaching Lectureships',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'lehrauftragStandardBestellprozessBestellen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'BESTELLEN<br>(Studiengangsleitung)',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'ORDER<br>(Study course Director)',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'lehrauftragStandardBestellprozessErteilen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'ERTEILEN<br>(Department-/Kompetenzfeldleitung)',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'APPROVEMENT<br>(Department- / Competence field Manager)',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'lehrauftragStandardBestellprozessAnnehmen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'ANNEHMEN<br>(LektorIn)',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'ACCEPTANCE<br>(Lecturer)',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'lehrauftraegeBestellen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Lehrauftr&auml;ge bestellen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Order lectureships',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'lehrauftraegeBestellenText',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Sobald im FAS ein Lehrauftrag/eine Projektbetreuung angelegt wurde, k&ouml;nnen Sie diese
					hier bestellen.<br>Bestellte Lehrauftr&auml;ge sind zur Erteilung freigegeben.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'A lectureship is ready to be ordered as soon as it has been created in FAS.<br>
					Ordered lectureships are released for assignment.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'lehrauftraegeErteilen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Lehrauftr&auml;ge erteilen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Approve lectureships',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'lehrauftraegeErteilenText',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Sobald Lehrauftr&auml;ge bestellt wurden, k&ouml;nnen Sie diese hier erteilen.<br>
					Erteilte Lehrauftr&auml;ge k&ouml;nnen von den Lehrenden angenommen werden.<br>',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'A lectureship is ready to be approved as soon as it has been ordered.<br>
					Approved lectureships are released for acceptance.<br>',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'lehrauftraegeBestellenKlickStatusicon',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Klicken Sie unten auf das Status-Icon \'Nur neue anzeigen\', \'Nur ge&auml;nderte anzeigen\'
					oder \'Alle anzeigen\'',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Click on status-icon \'Show only new ones\', \'Show only changed ones\' or \'Show all\'',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'lehrauftraegeErteilenKlickStatusicon',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Klicken Sie unten auf das Status-Icon \'Nur bestellte anzeigen\' oder \'Alle anzeigen\'',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Click on status-icon \'Show only ordered ones\' or \'Show all\'',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'lehrauftraegeBestellenLehrauftraegeWaehlen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'W&auml;hlen Sie die zu bestellenden Lehrauftr&auml;ge selbst oder &uuml;ber den
					Button \'Alle ausw&auml;hlen\'.
				',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Select the lectureships you want to order individually or use the button \'Select all\'',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'lehrauftraegeErteilenLehrauftraegeWaehlen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'W&auml;hlen Sie die zu erteilenden Lehrauftr&auml;ge selbst oder &uuml;ber den
					Button \'Alle ausw&auml;hlen\'.
				',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Select the lectureships you want to aprove individually or use the button \'Select all\'',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'lehrauftraegeBestellenMitKlickBestellen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Klicken Sie auf Lehrauftrag bestellen.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Click on \'Order lectureships\'',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'lehrauftraegeErteilenMitKlickErteilen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Klicken Sie auf Lehrauftrag erteilen.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Click on \'Approve Lectureships\'',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'lehrauftraegeBestellenVertragWirdAngelegt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Für jeden bestellten Lehrauftrag legt das System einen Vertrag an.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The system creates a contract for each lectureship ordered.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'geaenderteLehrauftraege',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ge&auml;nderte Lehrauftr&auml;ge',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Changed lectureships',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'geaenderteLehrauftraegeText',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Im FAS k&ouml;nnen &Auml;nderungen an Stunden/Stundensatz eines Lehrauftrags
					durchgef&uuml;hrt werden, solange dieser nicht vom Lehrenden angenommen wurde.<br>
					Diese m&uuml;ssen dann erneut bestellt werden.<br><br>
					Sie k&ouml;nnen sich die vorgenommenen &Auml;nderungen anzeigen lassen, indem Sie mit der Maus &uuml;ber
					dem Status-Icon am Beginn der Zeile fahren.<br>',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'In FAS, working hours / hourly rates can be changed as long as they were not accepted by the
					teacher.<br>After each change, the lectureship needs to be re-ordered.<br><br>
					In case changes are made to lectureships, that have already been ordered or approved,
					you may want to have a deeper look into what have changed.<br>You can display that information by
					moving the mouse over the status icon at the beginning of the line.<br>',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'geaenderteLehrauftraegeTextBeiErteilung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Im FAS k&ouml;nnen &Auml;nderungen an Stunden/Stundensatz eines Lehrauftrags
					durchgef&uuml;hrt werden, solange dieser nicht vom Lehrenden angenommen wurde.<br>
					Diese m&uuml;ssen dann von der Studiengangsleitung erneut bestellt werden.<br><br>
					Waren diese Lehrauftr&auml;ge zuvor bereits erteilt, wird deren Status auf \'neu\' zur&uuml;ckgesetzt<br>.
				',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'In FAS, working hours / hourly rates can be changed as long as they were not accepted by the
					teacher.<br>After each change, the lectureship needs to be be re-ordered.<br><br>
					If the lectureship was already approved, the status will be reset to \'new\'<br>',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'lehrauftraegeNichtAuswaehlbar',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Warum kann ich manche Lehrauftr&auml;ge nicht ausw&auml;hlen?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Why can\'t I select some lectureships?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'lehrauftraegeNichtAuswaehlbarText',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nur Lehrauftr&auml;ge mit dem Status \'neu\' und \'ge&auml;ndert\' k&ouml;nnen bestellt werden.<br>
					Erteilte oder akzeptierte Lehrauftr&auml;ge werden nur zu Ihrer Information angezeigt und sind daher
					NICHT w&auml;hlbar.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Only lectureships with the status \'new\' and \'changed\' can be ordered. <br>
					Lectureships with the status \'approved\' or \'accepted\' are only shown for your information and
					are therefore NOT selectable.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'lehrauftraegeNichtAuswaehlbarTextBeiErteilung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nur Lehrauftr&auml;ge mit dem Status \'bestellt\' k&ouml;nnen erteilt werden.<br>
					Neue, angenommene und ge&auml;nderte Lehrauftr&auml;ge werden nur zu Ihrer Information
					angezeigt und sind daher NICHT w&auml;hlbar.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Only lectureships with the status \'ordered\' can be ordered. <br>
					Lectureships with the status \'new\', \'accepted\' or \'changed\' are only shown for your
					information and are therefore NOT selectable.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'lehrauftraegeNichtAuswaehlbarTextBeiAnnahme',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nur Lehrauftr&auml;ge mit dem Status \'erteilt\' k&ouml;nnen angenommen werden.<br>
					Bereits angenommene oder Lehrauftr&auml;ge in Bearbeitung werden nur zu Ihrer Information
					angezeigt und sind daher NICHT w&auml;hlbar.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Only approved teaching lectureships are selectable. (status MUST be approved).<br>
					Lectureships, that were already accepted  or that are in process are only shown for your
					information and are therefore NOT selectable.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'filterAlle',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '<b>Alle</b><br>Alle Lehrauftr&auml;ge mit jedem Status, auch ge&auml;nderte und Dummy-Auftr&auml;ge',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '<b>All</b><br> All teaching lectureships (any status)',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'filterAlleBeiAnnahme',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '<b>Alle</b><br>Alle Lehrauftr&auml;ge mit jedem Status',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '<b>All</b><br> All teaching lectureships (any status)',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'filterNeu',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '<b>Neu</b><br>Nur Lehrauftr&auml;ge, die im FAS &uuml;ber die Zuteilung eines Lehrenden zu einer
					Lehreinheit/einem Projekt angelegt und noch nicht bestellt worden sind',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '<b>New</b><br>Only lectureships, that had been created in FAS. They are not ordered yet',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'filterBestellt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '<b>Bestellt</b><br>Nur bestellte Lehrauftr&auml;ge (auch bestellte, die nachtr&auml;glich ge&auml;ndert wurden)',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '<b>Ordered</b><br>Only ordered lectureships. (Also ordered lectureships that have been changed)',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'filterErteilt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '<b>Erteilt</b><br>Nur erteilte Lehrauftr&auml;ge (auch erteilte, die nachtr&auml;glich ge&auml;ndert wurden)',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '<b>Approved</b><br>Only approved lectureships. (Also approved lectureships that have been changed)',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'filterErteiltBeiAnnahme',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '<b>Erteilt</b><br>Nur erteilte UND ge&auml;nderte Lehrauftr&auml;ge, die in Bearbeitung sind',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '<b>Approved</b><br>Only approved teaching lectureships and such which are in process',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'filterAngenommen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '<b>Angenommen</b><br>Nur angenommene Lehrauftr&auml;ge</td>',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '<b>Accepted</b><br> Only accepted lectureships',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'filterGeaendert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '<b>Ge&auml;ndert</b><br>Nur Lehrauftr&auml;ge, die ge&auml;ndert wurden, nachdem sie bereits
					bestellt oder erteilt worden sind',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '<b>Changed</b><br>Only lectureships, that have been changed.<br>(After they had already been
					ordered or approved)',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'filterDummies',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '<b>Dummies</b><br>Nur Lehrauftr&auml;ge, die mit einem Dummylektor angelegt sind',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '<b>Dummies</b><br>Only lectureships, that were assigend to a dummy lector',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'mehrHilfe',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Mehr Hilfe?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Need more Help?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'weitereInformationenUnter',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Weitere Informationen unter ',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'For further information please go to ',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'lehrauftraegeAnnehmen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Wie nehme ich Lehraufträge an?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'How do I accept lectureships?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'lehrauftraegeAnnehmenText',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Sobald Ihnen ein oder mehrere Lehrauftr&auml;ge erteilt wurden, können Sie diese annehmen.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'As soon as a lectureship has been approved (status = approved), you can accept it.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'lehrauftraegeAnnehmenKlickStatusicon',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Klicken Sie unten auf das Status-Icon \'Nur erteilte anzeigen\' oder \'Alle anzeigen\'',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Click on the status icon \'Show only approved\' or \'Show all\' below',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'lehrauftraegeAnnehmenLehrauftraegeWaehlen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Wählen Sie die Lehraufträge, die Sie annehmen möchten, selbst oder alle über den Button \'Alle auswählen\'.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Select the teaching assignments you would like to accept either by selecting them individually or by using the \'Select all\' button.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'lehrauftraegeAnnehmenMitKlickAnnehmen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Geben Sie Ihr CIS-Passwort ein und klicken auf Lehrauftrag annehmen.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Enter your CIS password and click on \'Accept lectureships\'.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'dokumentePDF',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Dokumente PDF',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Documents PDF',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'PDFLehrauftraegeFH',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'PDF Lehrauftr&auml;ge FH',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'PDF Lectureships UAS',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'PDFLehrauftraegeLehrgaenge',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'PDF Lehrauftr&auml;ge Lehrg&auml;nge',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'PDF Lectureships Acadamy Courses',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'einfuehrungstext',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Hier sehen Sie alle Abschlussprüfungen zu denen Sie als Vorsitz zugeteilt sind. Klicken Sie auf den entsprechenden Link um das Prüfungsprotokoll zu erstellen.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Here you can see all the Examination where you are assigned as a chair. Select the entry to create the protocol.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'pruefungBachelor',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'kommissionelle Bachelorprüfung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Bachelor Examination before a Committee',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'pruefungMaster',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'kommissionelle Masterprüfung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Master Examination before a Committee',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'arbeitBachelor',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bachelorarbeit',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Bachelor Paper',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'arbeitMaster',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Masterarbeit',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Master\'s Thesis',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'pruefungsprotokoll',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Prüfungsprotokoll',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Record of Examination',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'protokoll',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Protokoll',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Record of',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'abgehaltenAmBachelor',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'abgehalten am FH-Bachelorstudiengang',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'held in the UAS Bachelor\'s Degree Program',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'abgehaltenAmMaster',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'abgehalten am FH-Masterstudiengang',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'held in the UAS Master\'s Degree Program',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'studiengangskennzahl',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Studiengangskennzahl',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Classification Number',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'personenkennzeichen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Personenkennzeichen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Personal identity number',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'pruefungssenat',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Prüfungssenat',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Examining Committee',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'vorsitz',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Vorsitzende/r',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Chair',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'erstpruefer',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '1. Prüfer/in',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '1st Examiner',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'zweitpruefer',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '2. Prüfer/in',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '2nd Examiner',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'pruefungsdatum',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Prüfungsdatum',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Exam Date',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'pruefungsbeginn',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Prüfungsbeginn',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Time of Start',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'pruefungsende',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Prüfungsende',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Time of Finish',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'pruefungsantritt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Prüfungsantritt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Examination Attempt',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'einverstaendniserklaerungName',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Einverständniserklärung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Statement of agreement',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'einverstaendniserklaerungText',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Der/Die Studierende bestätigt, sich in guter körperlicher und geistiger Verfassung zu befinden,
				um die Prüfung durchzuführen und dass die technischen Voraussetzungen gegeben sind.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The student confirms to be in a physical and mental condition to take the exam and that the technical requirements are met.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'themaBeurteilung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Thema und Beurteilung der',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Topic and Assessment of',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'pruefungsgegenstand',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Prüfungsgegenstand',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Subject of the Examination',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'pruefungsnotizenBachelor',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Prüfungsteil/e in Englisch (Optional - entsprechend der Vorgabe des Studiengangs):
<< Nichtzutreffendes löschen >>
* Präsentation der Bachelorarbeit
* Prüfungsgespräch über die Bachelorarbeit

Fragen zur Eröffnung des Prüfungsgesprächs
<< Bitte ausfüllen >>

Gründe für negative Beurteilung ODER allfällige Anmerkungen bei positiver Beurteilung
<< Bitte ausfüllen >>

Allfällige besondere Vorkommnisse
<< Bitte ausfüllen >>',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Parts of the examination held in English (Optional - in line with the degree program\'s guidelines):
<< Delete as appropriate >>
* Presentation of the Bachelor Paper
* Examination interview on the Bachelor Paper

Question(s) to open the examination interview
<< Please fill out >>

Reasons for failing OR any possible explanatory notes on a passing grade
<< Please fill out >>

Any unusual occurrences
<< Please fill out >>',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'pruefungsnotizenMaster',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Prüfungsteil/e in Englisch (Optional - entsprechend der Vorgabe des Studiengangs):
<< Nichtzutreffendes löschen >>
* Präsentation der Masterarbeit
* Prüfungsgespräch über die Masterarbeit und Querverbindungen zu Fächern des Studienplans
* Prüfungsgespräch über sonstige studienplanrelevante Inhalte

Fragen zur Eröffnung des Prüfungsgesprächs
<< Bitte ausfüllen >>

Gründe für negative Beurteilung ODER allfällige Anmerkungen bei positiver Beurteilung
<< Bitte ausfüllen >>

Allfällige besondere Vorkommnisse
<< Bitte ausfüllen >>',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Parts of the examination held in English (Optional - in line with the degree program\'s guidelines):
<< Delete as appropriate >>
* Presentation of the Master\'s Thesis
* Examination interview on the Master\'s Thesis and its links to the subjects of the curriculum
* Examination interview on other subjects relevant to the curriculum

Question(s) to open the examination interview
<< Please fill out >>

Reasons for failing OR any possible explanatory notes on a passing grade
<< Please fill out >>

Any unusual occurrences
<< Please fill out >>',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'pruefungsgegenstandBachelor',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Prüfungsgespräch über die Bachelorarbeit',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Presentation and Examination interview on the Bachelor Paper and its links to subjects of the curriculum',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'pruefungsgegenstandMaster',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Prüfungsgespräch über die Masterarbeit und deren Querverbindungen zu Fächern des Studienplans sowie Prüfungsgespräch über das Stoffgebiet',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Examination interview on the Master’s Thesis and its links to subjects of the curriculum as well as examination interview on a curricular theme',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'beurteilungKriterienBachelor',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Beurteilung und Kriterien Bachelorprüfung<br /><br />
                                    <ul>
                                        <li>
                                            <i>Mit ausgezeichnetem Erfolg bestanden</i><br />
                                            Der oder die KandidatIn ist in der Lage, Wissen aus verschiedenen Lernbereichen fachlich korrekt in einem weit über das Wesentliche hinausgehenden Ausmaß souverän auf neue Situationen anzuwenden, und das noch dazu auf einem sehr hohen argumentativen Niveau.
                                        </li>
                                        <br />
                                        <li>
                                            <i>Mit gutem Erfolg bestanden</i><br />
                                            Der oder die KandidatIn ist in der Lage, Wissen aus verschiedenen Lernbereichen fachlich korrekt in einem über das Wesentliche hinausgehenden Ausmaß auf neue Situationen anzuwenden, und das noch dazu auf einem hohen argumentativen Niveau.
                                        </li>
                                        <br />
                                        <li>
                                            <i>Bestanden</i><br />
                                            Alle Lehrveranstaltungen (einschl. Bachelorarbeit) und Bachelorprüfung wurden positiv beurteilt.
                                        </li>
                                        <br />
                                        <li>
                                            <i>Nicht bestanden</i>
                                        </li>
                                    </ul>',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Criteria for the assessment of the Bachelor Examination<br /><br />
                                    <ul>
                                        <li>
                                            <i>Passed with distinction</i><br />
                                            The candidate is within the scope of the task able to apply knowledge from various learning areas to new situations in a technically correct manner, far beyond what is essential, and at a very high level of argument.
                                        </li>
                                        <br />
                                        <li>
                                            <i>Passed with merit</i><br />
                                            The candidate is within the scope of the task able to apply knowledge from various learning areas in a technically correct manner to an extent beyond what is essential to new situations, and at a high level of argument.
                                        </li>
                                        <br />
                                        <li>
                                            <i>Passed</i><br />
                                            All courses (including Bachelor thesis) and Bachelor examination were successfully completed.
                                        </li>
                                        <br />
                                        <li>
                                            <i>Failed</i>
                                        </li>
                                    </ul>',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'beurteilungKriterienMaster',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Beurteilung und Kriterien Masterprüfung<br /><br />
                                    <ul>
                                        <li>
                                            <i>Mit ausgezeichnetem Erfolg bestanden</i><br />
                                            <ul>
                                            	<li>Masterarbeit mit "Sehr gut" beurteilt</li>
                                            	<li>Der oder die KandidatIn ist in der Lage, Wissen aus verschiedenen Lernbereichen fachlich korrekt in einem weit über das Wesentliche hinausgehenden Ausmaß souverän auf neue Situationen anzuwenden, und das noch dazu auf einem sehr hohen argumentativen Niveau.</li>
                                            </ul>
                                        </li>
                                        <br />
                                        <li>
                                            <i>Mit gutem Erfolg bestanden</i><br />
                                            <ul>
                                            	<li>Masterarbeit mit "Sehr gut" oder mit "Gut" beurteilt</li>
												<li>Der oder die KandidatIn ist in der Lage, Wissen aus verschiedenen Lernbereichen fachlich korrekt in einem über das Wesentliche hinausgehenden Ausmaß auf neue Situationen anzuwenden, und das noch dazu auf einem hohen argumentativen Niveau.</li>
                                            </ul>
                                        </li>
                                        <br />
                                        <li>
                                            <i>Bestanden</i><br />
                                            Masterarbeit und Masterprüfung wurden positiv beurteilt.
                                        </li>
                                        <br />
                                        <li>
                                            <i>Nicht bestanden</i>
                                        </li>
                                    </ul>',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Criteria for the assessment of the Master Examination<br /><br />
                                    <ul>
                                        <li>
                                            <i>Passed with distinction</i><br />
                                            <ul>
												<li>Master thesis was graded "excellent"</li>
												<li>The candidate is within the scope of the task able to apply knowledge from various learning areas to new situations in a technically correct manner, far beyond what is essential, and at a very high level of argument.</li>
                                            </ul>
                                        </li>
                                        <br />
                                        <li>
                                            <i>Passed with merit</i><br />
                                            <ul>
												<li>Master thesis graded not worse than "good"</li>
												<li>The candidate is within the scope of the task able to apply knowledge from various learning areas in a technically correct manner to an extent beyond what is essential to new situations, and at a high level of argument.</li>
											</ul>
                                        </li>
                                        <br />
                                        <li>
                                            <i>Passed</i><br />
                                            Master thesis and Master examination were successfully completed.
                                        </li>
                                        <br />
                                        <li>
                                            <i>Failed</i>
                                        </li>
                                    </ul>',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'beurteilungBachelor',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Beurteilung Bachelorprüfung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Assessment of the Bachelor Examination',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'beurteilungMaster',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Beurteilung Masterprüfung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Assessment of the Master Examination',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'ueberpruefenFreigeben',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Speichern und Freigeben',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Save and Approve',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'freigegebenAm',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Freigegeben am',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Approved on',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'pruefungGespeichert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Prüfung erfolgreich gespeichert!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Examination successfully saved!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'pruefungSpeichernFehler',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehler beim Speichern der Prüfung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error when saving examination',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'abschlussbeurteilungLeer',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Abschlussbeurteilung darf nicht leer sein!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Assessment cannot be empty!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'beginnzeitLeer',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Beginnzeit darf nicht leer sein!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Start time cannot be empty!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'endezeitLeer',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Endzeit darf nicht leer sein!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'End time cannot be empty!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'beginnzeitFormatError',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Beginnzeit muss Format Stunden:Minuten haben!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Start time must have format Hours:Minutes!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'endezeitFormatError',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Endezeit muss Format Stunden:Minuten haben!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'End time must have format Hours:Minutes!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'endezeitBeforeError',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Endezeit darf nicht kleiner als Beginnzeit sein!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'End time cannot be before begin time!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'verfNotice',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '(Beurteilung kann nur nach Bestätigung der Einverständniserklärung erfolgen)',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '(Assessment can only be selected after confirming the statement of agreement)',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'alle',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Alle',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'All',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'heute',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Heute',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Today',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'letzteWoche',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Letzte Woche',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Last week',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'gestern',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Gestern',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Yesterday',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'meineFelder',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Meine Felder',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'My fields',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'zeitraum',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zeitraum',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Period',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	)
);


//*****		CHECK PHRASES & PHRASENTEXTE in German and English.
//*****		INSERT into phrase_tbl if new app + category + phrase found in phrasen-array.
//*****		INSERT into phrasentext_tbl if new text found in phrasen-phrases-array, conciders every language apart.

foreach ($phrases as $phrase)
{
	$qry = "SELECT phrase_id
			FROM system.tbl_phrase
			WHERE
				app=". $db->db_add_param($phrase['app']). " AND
				category=". $db->db_add_param($phrase['category']). " AND
				phrase=". $db->db_add_param($phrase['phrase']);

	//***	CHECK PHRASE
	if ($result = $db->db_query($qry))
	{
		$phrase_id = '';

		//phrase not existing -> insert phrase and get last inserted phrase_id
		if ($db->db_num_rows($result) === 0)
		{
			$qry_insert = "INSERT INTO system.tbl_phrase(
								app,
								phrase,
								insertamum,
								insertvon,
								category)
							VALUES(".
								$db->db_add_param($phrase['app']). ','.
								$db->db_add_param($phrase['phrase']). ','.
								' now(),'.
								$db->db_add_param($phrase['insertvon']). ','.
								$db->db_add_param($phrase['category']). ');';

			if ($db->db_query($qry_insert))
			{
				$new = true;

				$qry_lastId = "SELECT currval('system.tbl_phrase_phrase_id_seq') as id";
				if ($db->db_query($qry_lastId))
				{
					if ($obj = $db->db_fetch_object())
					{
						$phrase_id = $obj->id;
					}
				}
				echo 'Kategorie/Phrase: <b>'. $phrase['category']. '/'. $phrase['phrase']. ' hinzugefügt</b><br>';
			}
			else
				echo '<span class="error">Fehler: '. $phrase['category']. '/'.
				$phrase['phrase']. ' hinzufügen nicht möglich</span><br>';
		}
		//phrase existing -> get phrase_id
		else
		{
			if ($obj = $db->db_fetch_object($result))
			{
				$phrase_id = $obj->phrase_id;
			}
			echo 'Kategorie/Phrase: '. $phrase['category']. '/'. $phrase['phrase']. ' vorhanden.<br>';
		}


		//***	CHECK PHRASENTEXT
		//loop through languages
		foreach ($phrase['phrases'] as $phrase_phrases)
		{
			$language = $phrase_phrases['sprache'];

			//query phrasentext in certain language
			$qry_language =
				"SELECT *
				FROM system.tbl_phrasentext
				WHERE
					phrase_id=". $phrase_id. " AND
					sprache='". $language. "'";


			if ($result_language = $db->db_query($qry_language))
			{
				//if phrasentext not existing in certain language -> insert
				if ($db->db_num_rows($result_language) === 0 && !empty($phrase_phrases['text']))
				{
					$qry_insert = "INSERT INTO system.tbl_phrasentext(
										phrase_id,
										sprache,
										orgeinheit_kurzbz,
										orgform_kurzbz,
										text,
										description,
										insertamum,
										insertvon)
									VALUES(".
										$db->db_add_param($phrase_id, FHC_INTEGER). ','.
										$db->db_add_param($phrase_phrases['sprache']). ','.
										' NULL,'.
										' NULL,'.
										$db->db_add_param($phrase_phrases['text']). ','.
										$db->db_add_param($phrase_phrases['description']). ','.
										' now(),'.
										$db->db_add_param($phrase_phrases['insertvon']). ');';

					if ($db->db_query($qry_insert))
					{
						echo '-- Phrasentext '. strtoupper(substr($phrase_phrases['sprache'], 0, 3)). ': <b>'.
							$phrase_phrases['text']. ' hinzugefügt</b><br>';
					}
					else
					{
						echo '<span class="error">Fehler: Phrasentext '.
							strtoupper(substr($phrase_phrases['sprache'], 0, 3)). ': '. $phrase_phrases['text'].
							' hinzufügen nicht möglich</span><br>';
					}
				}
			}
		}
	}
}

if(!$new)
	echo '<b>Keine neuen Phrasen</b><br>';
