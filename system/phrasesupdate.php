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
		'phrase' => 'bearbeitungGesperrt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bearbeitung gesperrt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Locked for editing',
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
		'phrase' => 'text',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Text',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'text',
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
		'phrase' => 'anzahlNichtGesendet',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anzahl (nicht gesendet)',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'amount (not sent)',
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
		'phrase' => 'notizDerSTGL',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Notiz der STGL',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Note of the study course director',
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
		'phrase' => 'bewerberVorhanden',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'BewerberIn möglicherweise vorhanden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Applicant maybe available',
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

	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'insertvon',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Erstellt von',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Inserted by',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'insertamum',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Erstellt am',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Inserted on',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'updatevon',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Geändert von',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Updated by',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'updateamum',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Geändert am',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Updated on',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
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
		'category' => 'global',
		'phrase' => 'create',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anlegen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Create',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
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
		'category' => 'global',
		'phrase' => 'actions',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Aktionen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Actions',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'unknown_error',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ein unbekannter Fehler ist aufgetreten: {error}',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'An unknown error occurred: {error}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),

	//*******************************		CORE/ui
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'loading',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Lädt...',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Loading...',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'toggle_nav',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Navigation ein-/ausblenden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Toggle navigation',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
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
				'text' => 'Cancel',
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
    array(
        'app' => 'core',
        'category' => 'ui',
        'phrase' => 'geloescht',
        'insertvon' => 'system',
        'phrases' => array(
            array(
                'sprache' => 'German',
                'text' => 'Gel&ouml;scht',
                'description' => '',
                'insertvon' => 'system'
            ),
            array(
                'sprache' => 'English',
                'text' => 'Deleted',
                'description' => '',
                'insertvon' => 'system'
            )
        )
    ),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'error_fieldWriteAccess',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Sie haben keine Schreibrechte für dieses Feld',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'You do not have writing rights for this field',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'error_fieldMustBeArray',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Das {field} Feld muss ein Array sein',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The {field} field must be an array',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'filterdelete',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Filter löschen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Clear filter',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
	'app' => 'core',
	'category' => 'anrechnung',
	'phrase' => 'benotungDerLV',
	'insertvon' => 'system',
	'phrases' => array(
		array(
			'sprache' => 'German',
			'text' => '	Lehrveranstaltung bereits benotet',
			'description' => '',
			'insertvon' => 'system'
		),
		array(
			'sprache' => 'English',
			'text' => 'Course already graded',
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
		'phrase' => 'filterApply',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Filtern',
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
		'phrase' => 'person_id',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Person ID',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Person ID',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
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
				'text' => 'Salutation',
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
		'phrase' => 'adressen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Adressen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'addresses',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'bankverbindungen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bankverbindungen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'bank details',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'rechnungsadresse',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Rechnungsadresse',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'billing address',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'heimatadresse',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Heimatadresse',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'home address',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'co_name',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'abweichender Empfänger',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'c/o',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'gemeinde',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Gemeinde',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'municipality',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'gemeinde_waehlen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bitte gültige Gemeinde wählen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Please select a valid municipality',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'plz_waehlen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bitte gültige PLZ wählen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Please select a valid zip code',
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
		'phrase' => 'plz',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'PLZ',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'zip',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'zustellung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zustellung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'delivery',
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
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'matrikelnummer',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Matrikelnummer',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Matriculation number',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'error_uidNotInPerson',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Die angegebene UID passt nicht zu der angegebenen Person',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Given UID is not assigned to the given Person',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'unruly',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Unruly',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Unruly',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'error_noBenutzer',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kein Benutzer gefunden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No User found',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),

	//****************	CORE/lehre
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'lvOptions',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Lehrveranstaltungsoptionen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Course options',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'noGrades',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Unbewertet',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Not Graded',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
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
		'phrase' => 'lehrveranstaltung_id',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Lehrveranstaltung ID',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Course ID',
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
				'text' => 'LektorIn',
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
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'studiengangskennzahlLehre',
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
				'text' => 'Study program number',
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
		'phrase' => 'zgvRueckfragen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'ZGV Prüfung beantragen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'apply for a ZGV examination',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'zgvErfuellt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'ZGV erfüllt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'ZGV fulfilled',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'zgvNichtErfuellt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'ZGV nicht erfüllt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'ZGV unfulfilled',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'zgvErfuelltPruefung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'ZGV mit Prüfungen erfüllt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'ZGV fulfilled with exam',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'zgvInPruefung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'ZGV noch in Prüfung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'ZGV still in review',
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
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'dokumentWirdNachgereicht',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Dokument wird nachgereicht',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Document will be submitted later',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'datumUngueltig',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Das Datumsformat ist ungültig oder liegt außerhalb des gültigen Bereichs. Bitte geben Sie ein gültiges Datum im Format tt.mm.jjjj ein.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Date is invalid or out of range. Please enter a valid date in the format dd.mm.yyyy.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'dokUngueltig',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bei dem Dokument ist keine Nachreichung möglich.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Date is invalid or out of range. Please enter a valid date in the format dd.mm.yyyy.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'nachreichDatumNichtVergangenheit',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Das Datum der Nachreichung darf nicht in der Vergangenheit liegen.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The date of submission may not be in the past.',
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
		'app' => 'infocenter',
		'category' => 'infocenter',
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
		'phrase' => 'rueckstellgrund',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Rückstellgrund',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'onHold reason',
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
		'phrase' => 'nurBachelorMasterFreigeben',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nur Bachelorstudiengänge/Masterstudiengänge können freigegeben werden.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Only bachelor/master programmes can be approved.',
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
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'kaution',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kaution',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Deposit',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'rechnungsnummer',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Rechnungsnummer',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Invoice Number',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'date',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Rechnungsnummer',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Invoice Number',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'faelligam',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fällig am',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Due on',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'gesamtbetrag',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Gesamtbetrag',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Total amount',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'rechnungsempfaenger',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Rechnungsempfänger',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Invoice recipient',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'rechnung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Rechnungsempfänger',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Invoice',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'studiensemester',
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
		'app' => 'infocenter',
		'category' => 'infocenter',
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
				'text' => 'Title',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
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
				'text' => 'Date',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'zahlungsbestaetigung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zahlungsbestätigung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Payment confirmation',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'bewerbung',
		'phrase' => 'erklaerungInvoices',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ablauf und Zahlungsbedingungen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Procedure and terms of payment',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'rechnungserklaerung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Wir möchten Sie darauf aufmerksam machen, dass bei der Überweisung *immer* die Rechnungsnummer als Zahlungsreferenz anzuführen ist.
						Andernfalls erfolgt keine automatische Zahlungszuordnung und es kann zu einer Verzögerung der Darstellung des aktuellen Zahlungsstatus
						der Rechnung im Campus Informations-System kommen.
						<br/>
						<br/>
						Im Falle dass der Betrag an ein falsches Konto überwiesen wurde, bitten wir Sie höflichst sich an Ihre Bank zu wenden.
						<br/>
						<br/>
						Jede Rechnung gilt als "Bezahlt", wenn der Gesamtbetrag vollständig auf unser Konto eingelangt ist.
						<br/>
						<br/>',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'We would like to draw your attention to the fact that the invoice number must *always* be quoted as the payment reference when making a bank transfer.
							Otherwise, no automatic payment allocation will take place and there may be a delay in displaying the current payment status of the invoice in Campus Informations-System.
							<br />
							<br />
							In the event that the amount has been transferred to an incorrect account, we kindly ask you to contact your bank.
							<br />
							<br />
							Each invoice is considered "paid" when the total amount has been credited to our account in full.
							<br />
							<br />',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'kontoinfotitle',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kontoinformationen der FHTW',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'FHTW account information',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'kontoinfobody',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Sämtliche Zahlungen sind an die nachstehende Kontonummer zu leisten und die Rechnungsnummer muss als Zahlungsreferenz eingegeben werden.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'All payments must be made to the following account number and the invoice number must be entered as the payment reference.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'kontoinfoausland',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Auslandsüberweisungen:
											<br/>
											Bei Auslandsüberweisungen sind die Spesenkosten von den
											<br/>
											Zahlenden zusätzlich zu den Rechnungsbeträgen zu zahlen.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Foreign bank transfers:
											<br />
											In the case of foreign bank transfers, the charges are to be paid by
											<br />
											the payer in addition to the invoice amounts.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'rechnungtitle',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Rechnungen & Zahlungsbestätigungen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Invoices & payment confirmations',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),

	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'faq0frage',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Warum ist die Einzahlung trotz Einzahlung noch offen?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Why is the deposit still outstanding despite payment?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'faq0antwort',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Der häufigste Grund für diesen Fall ist, dass bei der Überweisung nicht die Rechnungsnummer als Zahlungsreferenz eingegeben wird.
Wir bitten Sie höflichst in diesem Fall eine Mail an <a href="mailto:billing@technikum-wien.at">billing@technikum-wien.at</a> mit Zahlungsbestätigung zu senden.
Die Transaktion und die Bearbeitung der Zahlung, kann bis zu sechs Werktage dauern.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The most common reason for this is that the invoice number is not entered as the payment reference in the bank transfer.
In this case, we kindly ask you to send an e-mail to <a href="mailto:billing@technikum-wien.at">billing@technikum-wien.at</a> with a payment confirmation.
The transaction and processing of the payment can take up to six working days.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'faq1frage',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ich habe keine Rechnung erhalten, was tun?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'I have not received an invoice, what should I do?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'faq1antwort',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'In diesem Fall ist der Spam-Ordner zu kontrollieren. Falls die Rechnung nicht übermittelt wurde ersuchen wir um Information an <a href="mailto:billing@technikum-wien.at">billing@technikum-wien.at</a>.
Die Rechnung wird Ihnen erneut zugesendet. <u><strong>Erst nach Erhalt der Rechnung ist der Betrag zu überweisen</strong></u>',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'In this case, please check your spam folder. If the invoice has not been sent, please inform us at <a href="mailto:billing@technikum-wien.at">billing@technikum-wien.at</a>.
The invoice will be sent to you again. <u><strong>The amount is only to be transferred after receipt of the invoice!</strong></u>',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'faq2frage',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Refundierung des Studienbeitrags',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Refund of the tuition fee',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'faq2antwort',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Der Studienbeitrag wird nicht rückerstattet, wenn…
-Anfänger*innen, die ihren Studienplatz nach Semesterbeginn (1. September / 16. Februar) nicht in Anspruch nehmen
-Studierende, die ihr Studium nach Semesterbeginn (1. September / 16. Februar) abbrechen.

-Unterbrechung vor dem 15.10. bzw. 15.3.: Studienbeitrag wird rückerstattet
-Unterbrechung nach dem 15.10. bzw. 15.3.: Studienbeitrag wird nicht rückerstattet
-in den Folgesemestern der Unterbrechung sind keine Studienbeiträge zu zahlen; der ÖHBeitrag ist jedoch in jedem Semester der Unterbrechung zu zahlen',
			'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The tuition fee will not be refunded if...
-Freshmen who do not take up their study place after the start of the semester (September 1 / February 16)
-Students who discontinue their studies after the start of the semester (September 1 / February 16).

-Interruption before 15.10. or 15.3.: tuition fees will be refunded
-Interruption after 15.10. or 15.3.: Tuition fee will not be refunded
-No tuition fees are payable in the semesters following the interruption; however, the ÖH fee must be paid in each semester of the interruption',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'faq3frage',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Sie sind vom Studienbeitrag befreit und haben eine Rechnung für den Studienbeitrag bekommen?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'You are exempt from paying tuition fees and have received an invoice for tuition fees?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'faq3antwort',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Treten Sie bitte in Kontakt mit Ihrer Studiengangsassistenz. Die offene Rechnung wird storniert.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Please contact your degree program assistant. The outstanding invoice will be canceled.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'faq4frage',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Mir ist ein Fehler bei der Überweisung unterlaufen, was tun?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'I made a mistake with the transfer, what should I do?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'faq4antwort',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bitte den Fehler an <a href="mailto:billing@technikum-wien.at">billing@technikum-wien.at</a> melden.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Please report the error to <a href="mailto:billing@technikum-wien.at">billing@technikum-wien.at</a>.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'faq5frage',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Eine Rechnung wurde zwei Mal überwiesen, was tun?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'An invoice has been transferred twice, what should I do?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'faq5antwort',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Falls eine Rechnung doppelt überwiesen wurde, bitten wir Sie dies an <a href="mailto:billing@technikum-wien.at">billing@technikum-wien.at</a> zu melden. Wir werden Ihnen eine Zahlung refundieren.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'If an invoice has been transferred twice, please report this to <a href="mailto:billing@technikum-wien.at">billing@technikum-wien.at</a>. We will refund you one payment.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'faq6frage',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Es stehen mehrere Positionen auf der Rechnung – soll für jede Position eine Überweisung durchgeführt werden?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'There are several items on the invoice - should a transfer be made for each item?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'faq6antwort',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nein, es ist immer der auf der Rechnung ausgewiesene Gesamtbetrag zu überweisen.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No, the total amount shown on the invoice must always be transferred.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'faq7frage',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Wann kann der Betrag überwiesen werden?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'When can the amount be transferred?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'faq7antwort',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Wir möchten Sie darauf hinweisen, dass Überweisungen erst bei Erhalt der Rechnung durchzuführen sind. Bitte um Angabe der Rechnungsnummer als Zahlungsreferenz.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'We would like to point out that bank transfers should only be made on receipt of the invoice. Please state the invoice number as the payment reference.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'zahlungsempfaenger',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fachhochschule Technikum Wien',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'University of Applied Sciences Technikum Wien',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'unrulyPersonFound',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Person mit Markierung „unruly“ gefunden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Person flagged as "unruly" detected',
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
		'phrase' => 'neuUndGeaenderteAnzeigen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Neue und geänderte anzeigen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Show new and changed',
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
		'phrase' => 'error_invalid_date',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Das Datumsformat ist ungültig oder liegt außerhalb des gültigen Bereichs.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The date format is invalid or out of range.',
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
		'phrase' => 'all_semester',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Alle Semester',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'All semester',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'current_semester',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Aktuelles Semester',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Current semester',
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
		'phrase' => 'zukuenftige',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zukünftige',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'upcoming',
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
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'und',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'und',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'and',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),

	//*******************	Projektarbeitsbeurteilung - CORE
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'sehrGut',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Sehr Gut',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Excellent',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'gut',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Gut',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Good',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'befriedigend',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Befriedigend',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Satisfactory',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'genuegend',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Genügend',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Sufficient',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'nichtGenuegend',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nicht genügend',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Insufficient',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'notenschluessel',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Notenschlüssel',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'criteria',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'speichernAbsenden',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Speichern und Absenden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Save and send',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'fehlt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'fehlt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'missing',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'ungueltig',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'ungültig',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'invalid',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	//*******************	Projektarbeitsbeurteilung - specific
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
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
				'text' => 'Study Program',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
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
				'text' => 'Organizational structure ',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
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
				'text' => 'Bachelor\'s Paper',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'beurteilung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Beurteilung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Assessment of',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'projektarbeitsbeurteilung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Projektarbeitsbeurteilung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Projekt Work Assessment',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'erstBegutachter',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Erst-Begutachter*in',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'First Assessor',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'titelDerArbeit',
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
				'text' => 'Title of ',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'plagiatscheckBeschreibung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Der Plagiatscheck wurde durchgeführt und bestätigt, dass der zentrale Inhalt der Arbeit im erforderlichen Ausmaß eigenständig verfasst wurde (vgl. Satzungsteil Studienrechtliche Bestimmungen / Prüfungsordnung, § 20 Abs. 2 und 3).',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The plagiarism check has been carried out and confirms that the central content of the paper has been written independently to the required extent (cf. part of the Statutes on Studies Act Provisions / Examination Regulations, § 20 Para. 2 and 3).',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'plagiatscheckBeschreibungMaster',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Der Plagiatscheck wurde durchgeführt und bestätigt, dass der zentrale Inhalt der Arbeit im erforderlichen Ausmaß eigenständig verfasst wurde (vgl. Satzungsteil Studienrechtliche Bestimmungen / Prüfungsordnung, § 20 Abs. 2 und 3).',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The plagiarism check has been carried out and confirms that the central content of the thesis has been written independently to the required extent (cf. part of the Statutes on Studies Act Provisions / Examination Regulations, § 20 Para. 2 and 3).',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'kriterien',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kriterien',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Criteria',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'punkte',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Punkte',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Points',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'thema',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Thema',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Subject',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'themaText',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Das Thema wurde in eine im Rahmen einer Bachelorarbeit bearbeitbare Form übergeführt (Entwicklung sinnvoller Forschungsfragen bzw. Aufgabenstellungen, etc.).',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The subject was handled in a suitable manner for a bachelor\'s paper (well-structured, meaningful research questions or tasks, etc.)',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'themaTextMaster',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Das Thema wurde in eine im Rahmen einer Masterarbeit bearbeitbare Form übergeführt (Entwicklung sinnvoller Forschungsfragen bzw. Aufgabenstellungen, etc.).',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The subject was handled in a suitable manner for a Master\'s thesis (well-structured, meaningful research questions or tasks, etc.)',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'loesungsansatz',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Lösungsansatz',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Solution Approach',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'loesungsansatzText',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Der Lösungsansatz ist für das Thema geeignet und entspricht dem Stand der Technik.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The approach to the solution is suitable for the topic and corresponds to the state of the art.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'methode',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Methode',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Methods',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'methodeText',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Das Vorgehen ist in Bezug auf die fachspezifische Ausrichtung der Arbeit angemessen, anhand der Fachliteratur begründet und korrekt umgesetzt.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The procedure is appropriate in relation to the subject-specific orientation of the paper, justified on the basis of the specialist literature and correctly implemented.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'methodeTextMaster',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Das Vorgehen ist in Bezug auf die fachspezifische Ausrichtung der Arbeit angemessen, anhand der Fachliteratur begründet und korrekt umgesetzt.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The procedure is appropriate in relation to the subject-specific orientation of the thesis, justified on the basis of the specialist literature and correctly implemented.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'ereignisseDiskussion',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ergebnisse und Diskussion',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Results & Discussion of the conclusion',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'ereignisseDiskussionText',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Die Ergebnisse werden im Lichte der Fragestellung interpretiert und kritisch diskutiert im Hinblick auf ihren Mehrwert für Forschung und/oder Berufspraxis. Visualisierungen unterstützen die Argumentation.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The results are interpreted in the light of the research question and critically discussed with regard to their added value for research and/or professional practice. Visualisations support the argumentation.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'eigenstaendigkeit',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Eigenständigkeit',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Independence',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'eigenstaendigkeitText',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Die Arbeit wurde in selbständiger Arbeitsweise (z.B. eigenständige Lösung der Fragestellungen und aufgetretener Probleme) verfasst.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The paper was written in an independent way (e.g. independent solutions to the questions and problems that occurred)',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'eigenstaendigkeitTextMaster',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Die Arbeit wurde in selbständiger Arbeitsweise (z.B. eigenständige Lösung der Fragestellungen und aufgetretener Probleme) verfasst.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The thesis was written in an independent way (e.g. independent solutions to the questions and problems that occurred)',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'struktur',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Struktur',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Structure',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'strukturText',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Die Arbeit ist schlüssig strukturiert.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The paper is structured coherently.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'strukturTextMaster',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Die Arbeit ist schlüssig strukturiert.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The thesis is structured coherently.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'stil',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Stil',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Style',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'stilText',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Rechtschreibung und Grammatik sind korrekt. Die Verwendung von Fachsprache ist angemessen. Die Anforderungen an gendergerechte Sprache sind nach den geltenden Richtlinien umgesetzt.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Spelling and grammar are correct. The use of technical language is appropriate. The requirements for gender-appropriate language are implemented according to the applicable guidelines.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'form',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Form',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Form',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'formText',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Die Anforderungen an Gliederung, Verzeichnisse, Textsatz und Grafiken bzw. Tabellen sind nach den geltenden Richtlinien umgesetzt.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The requirements for structure, lists, typesetting and graphics or tables are implemented in accordance with the applicable guidelines.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'literatur',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Literatur',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Sources',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'literaturText',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Quellen und Literatur sind für die wissenschaftliche Auseinandersetzung mit dem Thema der Arbeit relevant, geben den aktuellen Stand der Forschung wieder und decken das Thema ab.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Sources and literature are relevant for the scientific discussion of the topic of the paper, reflect the current state of the art and cover the topic.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'literaturTextMaster',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Quellen und Literatur sind für die wissenschaftliche Auseinandersetzung mit dem Thema der Arbeit relevant, geben den aktuellen Stand der Forschung wieder und decken das Thema ab.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Sources and literature are relevant for the scientific discussion of the topic of the thesis, reflect the current state of the art and cover the topic.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'zitierregeln',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zitierregeln',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Citation Rules',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'zitierregelnText',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Die Zitierregeln werden korrekt angewendet und durchgehend umgesetzt.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The citation rules are correctly applied and implemented throughout.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'gesamtpunkte',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Gesamtpunkte',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'total points',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'gutachtenZweitBegutachtung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Das Gutachten des/der Zweit-Begutachter*in liegt vor und ist in die Beurteilung eingeflossen.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The second assessor’s assessment has been submitted and is part of the final grade.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'bitteBeurteilen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bitte beurteilen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Please assess',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'begruendungText',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Begründung (verpflichtend nur für die Note "Nicht Genügend")',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Reason (only required for the grade "insufficient")',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'unzureichendErfuellt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'unzureichend erfüllt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'inadequately met',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'genuegendErfuellt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'genügend erfüllt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'adequately met',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'gutErfuellt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'gut erfüllt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'well fulfilled',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'sehrGutErfuellt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'sehr gut erfüllt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'very well fulfilled',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'notenschluesselHinweis',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Liegt die Punkteanzahl bei den Kriterien "1 - 5" oder "6 - 10"  in Summe unter 50%, ist die {0} insgesamt als negativ zu beurteilen.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'If the number of points for the criteria "1 - 5" or "6 - 10" is below 50% in total, the {0} is to be assessed as negative overall.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'notenschluesselHinweisNullPunkteEinKriterium',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Falls ein Kriterium mit 0 Punkten bewertet wird, ist die {0} insgesamt als negativ zu beurteilen.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'If a criterion is assessed with 0 points, the {0} is to be assessed as negative overall.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'zweitBegutachter',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zweit-Begutachter*in',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Second Assessor',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'begutachter',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Begutachter*in',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Assessor',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'kurzeSchriftlicheBeurteilung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kurze schriftliche Beurteilung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Short written assessment',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'fragestellungRelevant',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ist die Fragestellung relevant und aktuell?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Is the question relevant and topical?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'inhaltMethode',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Inhalt und Methode',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Content and Methods',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'aufgabenstellungNachvollziehbar',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ist die Aufgabenstellung nachvollziehbar und gut argumentiert dargestellt?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Is the task presented comprehensibly and is it well argued?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'methodischeVorgangsweiseAngemessen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ist die methodische Vorgangsweise angemessen und korrekt angewendet?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Has the methodological approach been applied appropriately and correctly?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'mehrwertBerufspraxis',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Liefert das Ergebnis einen Mehrwert für die Berufspraxis?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Does the result provide added value for professional practice?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'eigenstaendigkeitErgebnis',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Eigenständigkeit beim Erreichen des Ergebnisses',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Independence in achieving the result',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'arbeitEigenstaendigVerfasst',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ist die Arbeit eigenständig verfasst worden?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Was the thesis written independently?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'arbeitGutStrukturiert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ist die Arbeit schlüssig strukturiert?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Is the thesis structured coherently?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'gliederungInhaltlichVerstaendlich',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ist die Gliederung inhaltlich verständlich und in Bezug auf das Thema schlüssig aufgebaut ("roter Faden")?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Is the structure understandable in terms of content and is it coherent in relation to the topic ("red thread")?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'nameStudierende',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Name des*der Studierenden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Name of student',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'beurteiltVon',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Beurteilt von',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Assessed by',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
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
				'text' => 'Student number',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'beurteilungGespeichertGesendet',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Beurteilung gespeichert und gesendet',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Assessment saved and sent',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'beurteilungGespeichert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Beurteilung gespeichert',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Assessment saved',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'beurteilungFehler',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehler beim Speichern der Beurteilung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error when saving assessment',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'ungueltigerToken',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ungültiger Token',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Invalid Token',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'zurProjektarbeitsUebersicht',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zur Projektarbeitsübersicht (CIS login)',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Projekt work overview (CIS login)',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'zurZweitbegutachterBewertung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zur Bewertung des/der Zweitbegutachters*in',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'To assessment of second assessor',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'zweitbegutachterFehltWarnung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Die Beurteilung des/der Zweitbegutachters*in liegt noch nicht vor.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The assessment of the second assessor is not available yet.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'projektarbeitsbeurteilungUebersicht',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Projektarbeitsbeurteilungsübersicht',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Projekt Work Assessment Overview',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'abgabedatum',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Abgabe - Datum',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Upload - Date',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'freischaltung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Freischaltung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Activation',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'resendToken',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Token an Zweit-Begutachter*in senden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Send token to Second Assessor',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'freischalten',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Freischalten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Unlock',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'kommissionellePruefungHinweis',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Dies ist eine im Rahmen einer kommissionellen Wiederholungsprüfung vorgelegte Bachelorarbeit. Die Beurteilung erfolgt erst im Anschluss an eine Abstimmung der Mitglieder des Prüfungssenats.',
				'description' => '',
			),
			array(
				'sprache' => 'English',
				'text' => 'This is a Bachelor\'s thesis submitted within the frame of a committee re-sit examination. The assessment only takes place after a vote of the members of the examination commission.',
				'description' => '',
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'kommissionMailSenden',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Infomail an Mitglieder des Prüfungssenats senden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Send infomail to members of the examination committee',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'kommissionMailGesendet',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Mail an Mitglieder des Prüfungssenats gesendet',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Sent mail to members of the examination committee',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'kommissionMailFehler',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehler beim Senden der Mail an Prüfungssenat',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error when sending mail to commission members',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'zweitbetreuerBewertungFehlt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Absenden erst nach Abschluss der Bewertung durch Zweitbegutachter*in möglich',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Sending only possible after completion of assessment by Second Assessor',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'nichtErfuellt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'nicht erfüllt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'not fulfilled',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'mindestanforderungErfuellt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Mindestanforderung erfüllt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'minimum requirement fulfilled',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'inWeitenTeilenErfuellt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'in weiten Teilen erfüllt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'fulfilled for the most part',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'vollstaendigErfuellt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'vollständig erfüllt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'fully fulfilled',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'kommissionsmitglieder',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Mitglieder Prüfungssenat',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Members of the examination commission',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'plagiatscheckNichtGesetzt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Plagiatscheck auffällig, negative Beurteilung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Plagiarism check not passed, negative assessment',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'titelBearbeiten',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Titel bearbeiten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Edit title',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'titelGespeichert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Titel gespeichert',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Title saved',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'titelSpeichernFehler',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehler beim Speichern des Titels',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error when saving title',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'plagiatscheckHinweisNegativeBeurteilung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '(Bei Plagiat wird die Arbeit negativ bewertet.)',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '(Plagiarism leads to a negative grade.)',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'plagiatscheck',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Plagiatscheck',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Plagiarism check',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'betreuernote',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Betreuernote',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Assessor grade',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'senatsvorsitz',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Vorsitz Prüfungssenat',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Assessor',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'parbeitDownload',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Download Projektarbeit',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Download thesis',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'betreuerart',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Betreuerart',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Assessor type',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'nebenBegutachter',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nebenbegutachter*in',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'secondary assessor',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'notenschluesselHinweisGewichtung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Die Punkteanzahl der Kriterien "1 - 5" wird mit 70%; die Punkteanzahl der Kriterien "6 - 10" mit 30% gewichtet.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The number of points for criteria "1 - 5" is weighted with 70%; the number of points for criteria "6 - 10" is weighted with 30%.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'gewichtet',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'gewichtete',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'weightened',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'sprache',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Sprache',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'language',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'spracheAendernFehler',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehler beim Ändern der Sprache',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error when changing language',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'anerkennungNachgewiesenerKenntnisse',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anerkennung nachgewiesener Kenntnisse',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Recognition of Prior Knowledge',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'anrechnungBeantragen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anrechnung beantragen',
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
		'category' => 'anrechnung',
		'phrase' => 'anrechnung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anrechnung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Exemption',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'antragStellen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Antrag stellen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Apply for Exemption',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'antragsdaten',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Antragsdaten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Application data',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'nachweisdokumente',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nachweisdokumente',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Verification Documents',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
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
		'category' => 'ui',
		'phrase' => 'hochladen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Hochladen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Upload',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'antragStellenText',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ich beantrage die Feststellung der Gleichwertigkeit aufgrund',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'I apply for equivalence to be established on the basis of',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'antragStellenWegenZeugnis',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'eines Zeugnisses (vgl. § 4 Abs. 8 Satzung „Studienrechtliche Bestimmungen / Prüfungsordnung)',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'a certificate (see § 4 para. 8, Statute on Studies Act Provisions / Examination Regulations of the UASTW)',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
    array(
        'app' => 'core',
        'category' => 'anrechnung',
        'phrase' => 'antragStellenWegenHochschulzeugnis',
        'insertvon' => 'system',
        'phrases' => array(
            array(
                'sprache' => 'German',
                'text' => 'eines Hochschulzeugnisses (vgl. § 4 Abs. 8 Satzung „Studienrechtliche Bestimmungen / Prüfungsordnung)',
                'description' => '',
                'insertvon' => 'system'
            ),
            array(
                'sprache' => 'English',
                'text' => 'a university certificate (see § 4 para. 8, Statute on Studies Act Provisions / Examination Regulations of the UASTW)',
                'description' => '',
                'insertvon' => 'system'
            )
        )
    ),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'antragStellenWegenPraxis',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'der nachgewiesenen beruflichen Praxis (vgl. § 4 Abs. 9 Satzung „Studienrechtliche Bestimmungen / Prüfungsordnung)',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'professional practice (see § 4 para. 9, Statute on Studies Act Provisions / Examination Regulations of the UASTW)',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
    array(
        'app' => 'core',
        'category' => 'anrechnung',
        'phrase' => 'bisherAngerechneteEcts',
        'insertvon' => 'system',
        'phrases' => array(
            array(
                'sprache' => 'German',
                'text' => 'Bisher angerechnete ECTS',
                'description' => '',
                'insertvon' => 'system'
            ),
            array(
                'sprache' => 'English',
                'text' => 'All previous recognized ECTS',
                'description' => '',
                'insertvon' => 'system'
            )
        )
    ),
    array(
        'app' => 'core',
        'category' => 'anrechnung',
        'phrase' => 'anrechnungEctsTooltipText',
        'insertvon' => 'system',
        'phrases' => array(
            array(
                'sprache' => 'German',
                'text' => 'Anzeige der Summe der bisher angerechneten ECTS. Für Quereinsteiger in ein höheres Semester werden die ECTS der angerechneten Semester berücksichtigt.<br><br>Seit Oktober 2021 gelten Höchstgrenzen für Anrechnungen:<br>max. 60 ECTS für schulische Zeugnisse (anrechenbar sind nur BHS- und AHS-Zeugnisse!)<br>max. 60 ECTS für berufliche Qualifikationen<br>max. 90 ECTS INSGESAMT für schulische und berufliche Qualifikationen',
                'description' => '',
                'insertvon' => 'system'
            ),
            array(
                'sprache' => 'English',
                'text' => 'Sum of previous recognized ECTS. Lateral Entries are considered with ECTS of the recognized semester.<br><br>Maximum ECTS limits are applied since Octobre 2021:<br><br>max. 60 ECTS school qualification (BHS and AHS only)<br>max. 60 ECTS professional qualification<br>max. 90 ECTS OVERALL for school and professional qualification',
                'description' => '',
                'insertvon' => 'system'
            )
        )
    ),
    array(
        'app' => 'core',
        'category' => 'anrechnung',
        'phrase' => 'anrechnungEctsTooltipTextBeiUeberschreitung',
        'insertvon' => 'system',
        'phrases' => array(
            array(
                'sprache' => 'German',
                'text' => 'Seit Oktober 2021 gelten Höchstgrenzen für Anrechnungen:<br>max. 60 ECTS für schulische Zeugnisse (anrechenbar sind nur BHS- und AHS-Zeugnisse!)<br>max. 60 ECTS für berufliche Qualifikationen<br>max. 90 ECTS INSGESAMT für schulische Zeugnisse und berufliche Qualifikationen',
                'description' => '',
                'insertvon' => 'system'
            ),
            array(
                'sprache' => 'English',
                'text' => 'Maximum ECTS limits are applied since Octobre 2021:<br>max. 60 ECTS school qualification (BHS and AHS only)<br>max. 60 ECTS professional qualification<br>max. 90 ECTS OVERALL for school and professional qualification',
                'description' => '',
                'insertvon' => 'system'
            )
        )
    ),
    array(
        'app' => 'core',
        'category' => 'anrechnung',
        'phrase' => 'anrechnungEctsTextBeiUeberschreitung',
        'insertvon' => 'system',
        'phrases' => array(
            array(
                'sprache' => 'German',
                'text' => 'Die Höchstgrenze für Anrechnungen gem. § 12 Abs. 3 Fachhochschulgesetz wird überschritten.<span class="fw-bold">Bisherige ECTS + ECTS dieser LV: Total: {0} [ Schulisch: {1}  | Beruflich: {2}  ]</span>',
                'description' => '',
                'insertvon' => 'system'
            ),
            array(
                'sprache' => 'English',
                'text' => '<br>Exceedance of maximum limit for exemption (see § 12 para. 3, Regulations of the UASTW).<br><b>Former ECTS + ECTS of this course: Total: {0} [ School  qualification: {1} | Professional qualification: {2} ]</b> ',
                'description' => '',
                'insertvon' => 'system'
            )
        )
    ),
    array(
        'app' => 'core',
        'category' => 'anrechnung',
        'phrase' => 'textUebernehmenOderEigenenBegruendungstext',
        'insertvon' => 'system',
        'phrases' => array(
            array(
                'sprache' => 'German',
                'text' => 'Begründungstext aus Liste übernehmen oder eigene Begründung angeben',
                'description' => '',
                'insertvon' => 'system'
            ),
            array(
                'sprache' => 'English',
                'text' => 'Copy reason from list above or write your own reason',
                'description' => '',
                'insertvon' => 'system'
            )
        )
    ),
    array(
        'app' => 'core',
        'category' => 'anrechnung',
        'phrase' => 'begruendungEcts',
        'insertvon' => 'system',
        'phrases' => array(
            array(
                'sprache' => 'German',
                'text' => 'Erläutern Sie die Gleichwertigkeit der ECTS',
                'description' => '',
                'insertvon' => 'system'
            ),
            array(
                'sprache' => 'English',
                'text' => 'Explain ECTS equivalencies',
                'description' => '',
                'insertvon' => 'system'
            )
        )
    ),
    array(
        'app' => 'core',
        'category' => 'anrechnung',
        'phrase' => 'begruendungLvinhalt',
        'insertvon' => 'system',
        'phrases' => array(
            array(
                'sprache' => 'German',
                'text' => 'Erläutern Sie die Gleichwertigkeit der Lehrveranstaltungsinhalte',
                'description' => '',
                'insertvon' => 'system'
            ),
            array(
                'sprache' => 'English',
                'text' => 'Explain the equivalence of course content',
                'description' => '',
                'insertvon' => 'system'
            )
        )
    ),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'anrechnungBegruendungEctsTooltipText',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Hinsichtlich des Umfangs der LV, die Sie anrechnen lassen wollen: Bitte erläutern Sie, warum Ihr Zeugnis bzw. Ihre berufliche Praxis mit dem Umfang der LV gleichwertig ist.<br><br>Referenzbeispiele für die ECTS-Berechnung finden Sie rechts in der Infobox.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Regarding the scope of the course you want to have credited: Please explain why your certificate or your professional practice is equivalent to the scope of the course.<br><br>Reference examples for the ECTS calculation can be found in the info box on the right.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'anrechnungBegruendungLvinhaltTooltipText',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Hinsichtlich der Lernergebnisse der LV (vgl. CIS), die Sie anrechnen lassen wollen: Bitte erläutern Sie, warum die von Ihnen erworbenen Kompetenzen mit diesen Lernergebnissen gleichwertig sind.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'With regard to the learning outcomes of the course (cf. CIS) for which you want to receive credit: Please explain why the competences you have acquired are equivalent to these learning outcomes.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'requestAnrechnungInfoEctsBerechnungTitle',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Referenzbeispiele ECTS-Berechnung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Reference examples of ECTS calculation',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'requestAnrechnungInfoEctsBerechnungBody',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "<b>1 ECTS an der FH Technikum Wien entspricht einem Arbeitsaufwand von 25 Stunden.</b>
					<br><br><u>Schulisches Zeugnis:</u>
					<br>Bitte die Unterrichtsstunden nachvollziehbar in ECTS umrechnen (ein Schuljahr besteht aus ca. 40 Wochen; d.h., eine Unterrichtsstunde pro Woche sind insgesamt ca. 40 Stunden Jahresaufwand.)
					<br><br><u>Hochschulzeugnis:</u>
					<br>Bitte die ECTS angeben.
					<br><br><u>Berufliche Praxis:</u>
					<br>Bitte die Dauer der einschlägigen beruflichen Praxis nachvollziehbar in ECTS umrechnen (1,5 ECTS sind ungefähr eine Arbeitswoche im Umfang von 37,5 Stunden).",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "<br>1 ECTS at the FH Technikum Wien corresponds to a workload of 25 hours.</b>
					<br><br><u>School certificate:</u>
					<br>Please convert the teaching hours into ECTS in a comprehensible way (a school year consists of approx. 40 weeks; i.e. one teaching hour per week is a total of approx. 40 hours of annual work).
					<br><br><u>University certificate:</u>
					<br>Please indicate the ECTS.
					<br><br><u>Professional practice:</u>
					<br>Please convert the duration of the relevant professional practice into ECTS in a comprehensible way (1.5 ECTS are approximately one working week of 37.5 hours).",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'begruendungEctsLabel',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Begründung Gleichwertigkeit ECTS',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Reason Equivalency of ECTS',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'begruendungLvinhaltLabel',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Begründung Gleichwertigkeit LV-Inhalt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Reason Equivalency of Course content',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'weitereInformationen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Weitere Informationen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Further information',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'antrag',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Antrag',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Application',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'anrechnungIst',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Antrag ist',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Application is',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'deadlineUeberschritten',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Außerhalb der Einreichfrist',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Deadline is exceeded',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'antragdatum',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Antragsdatum',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Application date',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'anrechnungenGenehmigen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anrechnungen genehmigen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Approve Applications for Recognition of Prior Knowledge',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'anrechnungGenehmigen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anrechnung genehmigen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Approve application for Recognition of Prior Knowledge',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'empfehlungAnfordern',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Empfehlung anfordern',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Request recommendation',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'genehmigen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Genehmigen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Approve',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'ablehnen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ablehnen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Reject',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'empfehlung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Empfehlung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Recommendation',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'begruendung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Begr&uuml;ndung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Reason',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'studentIn',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'StudentIn',
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
		'category' => 'ui',
		'phrase' => 'nurEmpfohleneAnzeigen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nur empfohlene anzeigen (die noch genehmigt/abgelehnt werden müssen)',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Show only recommended ones (that need to be approved/rejected)',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'nurNichtEmpfohleneAnzeigen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nur nicht empfohlene anzeigen (die noch genehmigt/abgelehnt werden müssen)',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Show only not recommended ones (that need to be approved/rejected)',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'nurGenehmigteAnzeigen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nur genehmigte anzeigen',
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
		'phrase' => 'nurAbgelehnteAnzeigen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nur abgelehnte anzeigen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Show only rejected ones',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'nurFehlendeEmpfehlungenAnzeigen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nur jene anzeigen, wo eine Empfehlung noch fehlt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Show only those ones that need your recommendation',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'ja',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'ja',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'yes',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'nein',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'nein',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'no',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'anrechnungenPruefen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anrechnungen pr&uuml;fen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Review Applications for Recognition of Prior Knowledge',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'empfehlen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Empfehlen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Recommend',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'nichtEmpfehlen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nicht empfehlen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Do not recommend',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'nichtSelektierbarAufgrundVon',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nicht selektierbar aufgrund von: ',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Not selectable because of: ',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'confirmTextAntragHatBereitsEmpfehlung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Mindestens 1 Antrag enthält bereits eine Empfehlung.\nWollen Sie wirklich für Ihre Auswahl eine Empfehlung anfordern und bereits vorhandene Empfehlungen dabei zurücksetzen?",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "At least one application was already recommended.\nDo you really want to request for recommendation for your selection?",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'herkunftDerKenntnisse',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Herkunft der Kenntnisse',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Origin of previous knowledge',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'herkunft',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Herkunft',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Origins',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'detailsicht',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Detailsicht',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Details',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'lektorInnen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'LektorInnen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Lectors',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'empfehlungPositivSubquestion',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Bitte best&auml;tigen Sie: <b>Anrechnung wird empfohlen, weil die Kenntnisse inhaltlich und umfangm&auml;ßig gleichwertig sind.</b>",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Please confirm: <b>Recognition and exemption is recommended for this application based on the documents submitted.</b>",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'empfehlungPositivConfirmed',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Anrechnung wird empfohlen, weil die Kenntnisse inhaltlich und umfangmäßig gleichwertig sind.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Recognition and exemption is recommended based on the documents submitted.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'empfehlungNegativPruefungNichtMoeglich',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anrechnung wird nicht empfohlen, weil die Prüfung der Gleichwertigkeit aus formalen Gründen (z.B. mangelhafte Unterlagen) nicht möglich war.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Equivalence can not be determined because the enclosures contain insufficient information as regards the teaching content.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'empfehlungNegativKenntnisseNichtGleichwertig',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anrechnung wird nicht empfohlen, weil die Kenntnisse inhaltlich und umfangmäßig nicht gleichwertig sind.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Equivalence can not be determined because of the insufficient learning objectives and the length of the course',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
    array(
        'app' => 'core',
        'category' => 'anrechnung',
        'phrase' => 'empfehlungNegativKenntnisseNichtGleichwertigWeil',
        'insertvon' => 'system',
        'phrases' => array(
            array(
                'sprache' => 'German',
                'text' => 'Die anzurechnenden Kenntnisse sind umfangmäßig und/oder inhaltlich nicht gleichwertig, weil<span id="helpTxtBegruendungErgaenzen">...[Erläuterung: Bitte Begründung ergänzen.]</span>',
                'description' => '',
                'insertvon' => 'system'
            ),
            array(
                'sprache' => 'English',
                'text' => 'The equivalence in terms of learning objectives and the length of the course can not be determined because of<span id="helpTxtBegruendungErgaenzen">...[Explanation: Please add reason.]</span>',
                'description' => '',
                'insertvon' => 'system'
            )
        )
    ),
    array(
        'app' => 'core',
        'category' => 'anrechnung',
        'phrase' => 'empfehlungNegativKenntnisseNichtGleichwertigWeilHinweis',
        'insertvon' => 'system',
        'phrases' => array(
            array(
                'sprache' => 'German',
                'text' => 'Die anzurechnenden Kenntnisse sind umfangmäßig und/oder inhaltlich nicht gleichwertig, weil... <span class="text-danger"><b>Bei einer Ablehnung ist eine individuelle Begründung erforderlich. Dies kann nur über die Detailseite erfolgen.</b></span>',
                'description' => '',
                'insertvon' => 'system'
            ),
            array(
                'sprache' => 'English',
                'text' => 'The equivalence in terms of learning objectives and the length of the course can not be determined because of... <span class="text-danger"><b>If the application is rejected, an individual reason is required. This can only be done from the detail page.</b></span>',
                'description' => '',
                'insertvon' => 'system'
            )
        )
    ),
    array(
        'app' => 'core',
        'category' => 'anrechnung',
        'phrase' => 'genehmigungNegativKenntnisseNichtGleichwertigWeil',
        'insertvon' => 'system',
        'phrases' => array(
            array(
                'sprache' => 'German',
                'text' => 'Die anzurechnenden Kenntnisse sind umfangmäßig und/oder inhaltlich nicht gleichwertig, weil<span id="helpTxtBegruendungErgaenzen">...[Erläuterung: Bitte ergänzen oder Empfehlungstext des Lektors übernehmen und ggf. redigieren.]</span>',
                'description' => '',
                'insertvon' => 'system'
            ),
            array(
                'sprache' => 'English',
                'text' => 'The equivalence in terms of learning objectives and the length of the course can not be determined because of<span id="helpTxtBegruendungErgaenzen">...[Explanation: Please complete or adopt the text of the lectors recommendation and edit it if necessary]</span>',
                'description' => '',
                'insertvon' => 'system'
            )
        )
    ),
    array(
        'app' => 'core',
        'category' => 'anrechnung',
        'phrase' => 'genehmigungNegativKenntnisseNichtGleichwertigWeilHinweis',
        'insertvon' => 'system',
        'phrases' => array(
            array(
                'sprache' => 'German',
                'text' => 'Die anzurechnenden Kenntnisse sind umfangmäßig und/oder inhaltlich nicht gleichwertig, weil... <span class="text-danger"><b>Bei einer Ablehnung ist eine individuelle Begründung erforderlich. Dies kann nur über die Detailseite erfolgen.</b></span>',
                'description' => '',
                'insertvon' => 'system'
            ),
            array(
                'sprache' => 'English',
                'text' => 'The equivalence in terms of learning objectives and the length of the course can not be determined because of... <span class="text-danger"><b>If the application is rejected, an individual reason is required. This can only be done from the detail page.</b></span>',
                'description' => '',
                'insertvon' => 'system'
            )
        )
    ),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'empfehlungdatum',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Empfehlungsdatum',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Recommendation date',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'textUebernehmen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Text &uuml;bernehmen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Use this text',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'bitteBegruendungAngeben',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bitte geben Sie eine Begründung für die Ablehnung an und best&auml;tigen danach.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Please give a reason why you do not recommend to approve this applications and confirm.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
    array(
        'app' => 'core',
        'category' => 'ui',
        'phrase' => 'bitteBegruendungVervollstaendigen',
        'insertvon' => 'system',
        'phrases' => array(
            array(
                'sprache' => 'German',
                'text' => 'Bitte vervollständigen Sie die Begründung.',
                'description' => '',
                'insertvon' => 'system'
            ),
            array(
                'sprache' => 'English',
                'text' => 'Please complete the reason.',
                'description' => '',
                'insertvon' => 'system'
            )
        )
    ),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'moeglicheBegruendungen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Mögliche Begründungen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Possible reasons',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'andereBegruendung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Andere Begründung. Bitte im Notizfeld angeben.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Other reasons. Please state the reasons in the field for comments.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'begruendungWirdFuerAlleUebernommen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Die Begründung wird für alle gewählten Anträge übernommen.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'This reason will be used for all of the selected applications.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'empfehlungNegativConfirmed',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => ' Anrechnung wird nicht empfohlen.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Recognition and exemption is not recommended based on the documents submitted.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'empfehlungVon',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => ' Empfehlung von',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Recommended by',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'empfehlungAm',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => ' Empfehlung am',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Recommended on',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'empfehlungenPositiv',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Bitte best&auml;tigen Sie: <b>Alle ausgew&auml;hlten Anrechnungen werden empfohlen, weil die Kenntnisse inhaltlich und umfangm&auml;ßig gleichwertig sind.</b>",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Please confirm: <b>Recognition and exemption is recommended for these applications based on the documents submitted.</b>",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'empfehlungenNegativ',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anrechnungen werden nicht empfohlen.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Recognitions and exemptions are not recommended.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'nochKeineEmpfehlung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Es wurde keine Empfehlung abgegeben.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No request for recommendation.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'genehmigungNegativ',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Antrag wird nicht genehmigt.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Recognition and exemption is rejected based on the documents submitted.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'genehmigungNegativQuestion',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Antrag nicht genehmigen?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Do you want to reject recognition and exemption for this application?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'genehmigungenNegativ',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Antr&auml;ge werden nicht genehmigt.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Recognition and exemption are rejected for these applications based on the documents submitted.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'genehmigungenNegativQuestion',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anrechnungen nicht genehmigen?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Do you want to reject recognition and exemption for these applications?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'genehmigungNegativPruefungNichtMoeglich',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anrechnung wird nicht genehmigt, weil die Prüfung der Gleichwertigkeit aus formalen Gründen (z.B. mangelhafte Unterlagen) nicht möglich war.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Recognition and exemption is rejected because the enclosures contain insufficient information as regards the teaching content.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'genehmigungNegativKenntnisseNichtGleichwertig',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anrechnung wird nicht genehmigt, weil die Kenntnisse inhaltlich und umfangmäßig nicht gleichwertig sind.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Recognition and exemption is rejected because of the insufficient learning objectives and the length of the course.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
    array(
        'app' => 'core',
        'category' => 'anrechnung',
        'phrase' => 'genehmigungNegativEctsHoechstgrenzeUeberschritten',
        'insertvon' => 'system',
        'phrases' => array(
            array(
                'sprache' => 'German',
                'text' => 'Anrechnung wird nicht genehmigt aufgrund einer Überschreitung der Höchstgrenzen für Anrechnungen gem. § 12 Abs. 3 Fachhochschulgesetz.',
                'description' => '',
                'insertvon' => 'system'
            ),
            array(
                'sprache' => 'English',
                'text' => 'Recognition and exemption is rejected because of exceedance of maximum limit for exemption (see § 12 para. 3, Regulations of the UASTW).',
                'description' => '',
                'insertvon' => 'system'
            )
        )
    ),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'genehmigungenPositiv',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Bitte best&auml;tigen Sie: <b>Alle ausgew&auml;hlten Anrechnungen werden genehmigt, weil die Kenntnisse inhaltlich und umfangm&auml;ßig gleichwertig sind.</b>",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Please confirm: <b>Recognition and exemption is approved for these applications based on the documents submitted.</b>",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'genehmigungenPositivQuestion',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anrechnungen genehmigen?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Do you want to approve recognition and exemption for these applications?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'genehmigungPositiv',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anrechnung wird genehmigt, weil die Kenntnisse inhaltlich und umfangmäßig gleichwertig sind.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Recognition and exemption is approved based on the documents submitted.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'genehmigungPositivQuestion',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Antrag genehmigen?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Do you want to approve recognition and exemption for this application?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'genehmigungPositivSubquestion',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Bitte best&auml;tigen Sie: <b>Anrechnung wird genehmigt, weil die Kenntnisse inhaltlich und umfangm&auml;ßig gleichwertig sind.</b>",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Please confirm: <b>Recognition and exemption is approved for this application based on the documents submitted.</b>",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'keineEmpfehlungAngefordert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Es wurde noch keine Empfehlung angefordert.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No recommendation has yet been requested.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'empfehlungAngefordertNochKeineEmpfehlung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Empfehlung wurde angefordert am ',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Recommendation was requested on ',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'genehmigung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Genehmigung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Approvement',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'abgeschlossenVon',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Abgeschlossen von',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Closed by',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'abschlussdatum',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Abschlussdatum',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Closing date',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'nochKeineGenehmigung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Der Antrag auf Anerkennung der nachgewiesenen Kenntnisse erfordert Ihre Genehmigung / Ablehnung.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The application is waiting for your approvement.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
    array(
        'app' => 'core',
        'category' => 'anrechnung',
        'phrase' => 'anrechnungenVerwalten',
        'insertvon' => 'system',
        'phrases' => array(
            array(
                'sprache' => 'German',
                'text' => 'Anrechnungen verwalten',
                'description' => '',
                'insertvon' => 'system'
            ),
            array(
                'sprache' => 'English',
                'text' => 'Administration of applications.',
                'description' => '',
                'insertvon' => 'system'
            )
        )
    ),
    array(
        'app' => 'core',
        'category' => 'anrechnung',
        'phrase' => 'anrechnungszeitraumFestlegen',
        'insertvon' => 'system',
        'phrases' => array(
            array(
                'sprache' => 'German',
                'text' => 'Anrechnungszeitraum festlegen',
                'description' => '',
                'insertvon' => 'system'
            ),
            array(
                'sprache' => 'English',
                'text' => 'Set appplication period',
                'description' => '',
                'insertvon' => 'system'
            )
        )
    ),
    array(
        'app' => 'core',
        'category' => 'anrechnung',
        'phrase' => 'anrechnungszeitraumHinzufuegen',
        'insertvon' => 'system',
        'phrases' => array(
            array(
                'sprache' => 'German',
                'text' => 'Anrechnungszeitraum hinzufügen',
                'description' => '',
                'insertvon' => 'system'
            ),
            array(
                'sprache' => 'English',
                'text' => 'Add appplication period',
                'description' => '',
                'insertvon' => 'system'
            )
        )
    ),
    array(
        'app' => 'core',
        'category' => 'anrechnung',
        'phrase' => 'anrechnungszeitraumSpeichern',
        'insertvon' => 'system',
        'phrases' => array(
            array(
                'sprache' => 'German',
                'text' => 'Anrechnungszeitraum speichern',
                'description' => '',
                'insertvon' => 'system'
            ),
            array(
                'sprache' => 'English',
                'text' => 'Save application period',
                'description' => '',
                'insertvon' => 'system'
            )
        )
    ),
    array(
        'app' => 'core',
        'category' => 'anrechnung',
        'phrase' => 'anrechnungszeitraumStart',
        'insertvon' => 'system',
        'phrases' => array(
            array(
                'sprache' => 'German',
                'text' => 'Anrechnungszeitraum Start',
                'description' => '',
                'insertvon' => 'system'
            ),
            array(
                'sprache' => 'English',
                'text' => 'Startdate application',
                'description' => '',
                'insertvon' => 'system'
            )
        )
    ),
    array(
        'app' => 'core',
        'category' => 'anrechnung',
        'phrase' => 'anrechnungszeitraumEnde',
        'insertvon' => 'system',
        'phrases' => array(
            array(
                'sprache' => 'German',
                'text' => 'Anrechnungszeitraum Ende',
                'description' => '',
                'insertvon' => 'system'
            ),
            array(
                'sprache' => 'English',
                'text' => 'Enddate application',
                'description' => '',
                'insertvon' => 'system'
            )
        )
    ),
    array(
        'app' => 'core',
        'category' => 'ui',
        'phrase' => 'errorStartdatumNichtInStudiensemester',
        'insertvon' => 'system',
        'phrases' => array(
            array(
                'sprache' => 'German',
                'text' => 'Das Startdatum liegt außerhalb des gewählten Studiensemesters.',
                'description' => '',
                'insertvon' => 'system'
            ),
            array(
                'sprache' => 'English',
                'text' => 'The startdate is not within the selected study semester.',
                'description' => '',
                'insertvon' => 'system'
            )
        )
    ),
    array(
        'app' => 'core',
        'category' => 'ui',
        'phrase' => 'errorEndedatumNichtInStudiensemester',
        'insertvon' => 'system',
        'phrases' => array(
            array(
                'sprache' => 'German',
                'text' => 'Das Endedatum liegt außerhalb des gewählten Studiensemesters.',
                'description' => '',
                'insertvon' => 'system'
            ),
            array(
                'sprache' => 'English',
                'text' => 'The enddate is not within the selected study semester.',
                'description' => '',
                'insertvon' => 'system'
            )
        )
    ),
    array(
        'app' => 'core',
        'category' => 'ui',
        'phrase' => 'errorStartdatumNachEndedatum',
        'insertvon' => 'system',
        'phrases' => array(
            array(
                'sprache' => 'German',
                'text' => 'Das Startdatum muss VOR dem Endedatum liegen.',
                'description' => '',
                'insertvon' => 'system'
            ),
            array(
                'sprache' => 'English',
                'text' => 'The startdate must be BEFORE the enddate.',
                'description' => '',
                'insertvon' => 'system'
            )
        )
    ),
    array(
        'app' => 'core',
        'category' => 'ui',
        'phrase' => 'frageSicherLoeschen',
        'insertvon' => 'system',
        'phrases' => array(
            array(
                'sprache' => 'German',
                'text' => "Sicher löschen?",
                'description' => '',
                'insertvon' => 'system'
            ),
            array(
                'sprache' => 'English',
                'text' => "Definitely delete?",
                'description' => '',
                'insertvon' => 'system'
            )
        )
    ),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'uploadTooltipText',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '<p>Max. Uploadvolumen: 2MB<br>Max. Anzahl Dokumente: 1<br>Tipp: Um mehrere Einzelseiten zu einer Datei zusammenfügen zu können, empfehlen wir Ihnen kostenlose Programme wie bspw. PDF Merge.</p>',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Max. Uploadvolume: 2MB<br>Max. document amount: 1<br>Hint: To combine more than one document we recommend using free pdf merging software like e.g. PDF Merge.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'anrechnungInfoTooltipText',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Wichtig: Bitte die Fristen, Voraussetzungen und Formvorgaben rechts in den Infoboxen beachten.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Important: Please pay attention to the information about deadlines and conditions provided in the right infobox.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'bestaetigen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bestätigen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Confirm',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'empfehlungPositivQuestion',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anrechnung empfehlen?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Do you want to recommend recognition and exemption for this application?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'empfehlungenPositivQuestion',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anrechnungen empfehlen?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Do you want to recommend recognition and exemption for these applications?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'empfehlungNegativQuestion',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anrechnung nicht empfehlen?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Don\'t you want to recommend recognition and exemption for this application?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'empfehlungenNegativQuestion',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anrechnungen nicht empfehlen?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Don\'t you want to recommend recognition and exemption for these applications??',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
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
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'antragWurdeAngelegt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Antrag wurde angelegt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Application was created',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'anrechnungGrundTooltipText',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "<h5><u>Beantragung aufgrund eines Zeugnisses</u></h5>
                Bitte laden Sie das Zeugnis und weitere Nachweis-Dokumente (z.B. Syllabus, Lehrpläne,
                Modulbeschreibung…) hoch.
                <br><br>Die folgenden Informationen müssen enthalten sein: Name der das Zeugnis ausstellenden Institution;
                Beschreibung der Lehrinhalte und / oder Lernergebnisse;
                Zeitlicher Umfang der Lehrveranstaltung (z. B. SWS, ECTS, Unterrichtsstunden…)
                <br><br>
                <h5><u>Beantragung aufgrund nachgewiesener beruflicher Praxis</u></h5>
                Soll die Anrechnung auf der Grundlage der beruflichen Praxis erfolgen, laden Sie bitte eine detaillierte
                Tätigkeitsbeschreibung hoch. Dies kann durch betriebliche Ausbildungsnachweise und / oder Nachweise von
                einschlägigen beruflichen Tätigkeiten mit Zeitangaben (z. B. durch ein qualifiziertes Arbeitszeugnis
                oder durch Bestätigungen des Arbeitgebers) erfolgen.
                <br><br>Falls Sie für den Nachweis der Gleichwertigkeit in Bundesgesetzblättern veröffentlichte Lehrpläne (vgl. HTL, HAK…) verwenden, laden Sie bitte nur die für die Anrechnung relevanten Teile hoch oder markieren Sie diese entsprechend.
                <br><br>Falls diese Informationen nicht enthalten sind, können wir den Antrag nicht prüfen und er wird abgelehnt.
                ",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "<h5><u>Application for recognition based on a certificate</u></h5>
               	Please upload the certificate and other supporting documents (e.g. syllabus, curricula, module description ...).
                <br><br>The following information must be included:
                    name of the institution issuing the certificate;
                    description of the teaching content and / or learning outcomes;
                    duration of the course (e.g. ECTS, contact hours per week, total number of hours taught...)
                <br><br>
                <h5><u>Application for recognition based on professional practice</u></h5>
                If the exemption is to be based on professional practice, please upload a detailed job description. This can be done through proof of company training and / or proof of relevant occupational activities with time information (e.g. through a qualified job reference or through confirmation from the employer).
                <br><br>If you use curricula published in federal law gazettes (cf. HTL, HAK ...) to prove equivalence, please upload only the parts relevant for recognition or mark them accordingly.
                <br><br>If this information is not included, we will not be able to check the application and it will be rejected.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'anrechnungGrundAllgemeinTooltipText',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Falls Sie für den Nachweis der Gleichwertigkeit in Bundesgesetzblättern veröffentlichte Lehrpläne (vgl. HTL, HAK…) verwenden, laden Sie bitte nur die für die Anrechnung relevanten Teile hoch oder markieren Sie diese entsprechend.
                <br><br><u>Falls diese Informationen nicht enthalten sind, können wir den Antrag nicht prüfen und er wird abgelehnt.</u>",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "If you use curricula published in federal law gazettes (cf. HTL, HAK ...) to prove equivalence, please upload only the parts relevant for recognition or mark them accordingly.
                <br><br><u>If this information is not included, we will not be able to check the application and it will be rejected.</u>",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'anrechnungGrundZeugnisTooltipText',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "<h5><u>Beantragung aufgrund eines Zeugnisses</u></h5>
                Bitte laden Sie das Zeugnis und weitere Nachweis-Dokumente (z.B. Syllabus, Lehrpläne,
                Modulbeschreibung…) hoch.
                <br><br>Die folgenden Informationen müssen enthalten sein: Name der das Zeugnis ausstellenden Institution;
                Beschreibung der Lehrinhalte und / oder Lernergebnisse;
                Zeitlicher Umfang der Lehrveranstaltung (z. B. SWS, ECTS, Unterrichtsstunden…)",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "<h5><u>Application for recognition based on a certificate</u></h5>
               	Please upload the certificate and other supporting documents (e.g. syllabus, curricula, module description ...).
                <br><br>The following information must be included:
                    name of the institution issuing the certificate;
                    description of the teaching content and / or learning outcomes;
                    duration of the course (e.g. ECTS, contact hours per week, total number of hours taught...)
   ",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'anrechnungGrundBerufTooltipText',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "<h5><u>Beantragung aufgrund beruflicher Praxis</u></h5>
                    Bitte erstellen Sie eine detaillierte Tätigkeitsbeschreibung. Dafür steht im CIS ein Formular zur Verfügung, das in ein PDF-Dokument umzuwandeln und gemeinsam mit dem Lebenslauf hochzuladen ist.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "<h5><u>Application for recognition based on professional practice</u></h5>
                    Please supply a detailed job description. Therefore a formular is provided in CIS, that should be converted as pdf file and supplied together with your CV.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'requestAnrechnungInfoFristenTitle',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Beantragung: Fristen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Deadlines',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'requestAnrechnungInfoFristenBody',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Bitte laden Sie den Antrag in deutscher oder englischer Sprache für das
                <ul>
                    <li><u>Wintersemester spätestens bis 22. September</u></li>
                    <li><u>Sommersemester spätestens bis 22. Februar</u> hoch.</li>
                </ul>
                <br>Die Entscheidung über den Antrag erfolgt in der Regel innerhalb von zwei Wochen ab dem 22. September
                bzw. 22. Februar.
                <br><br>Für jede Lehrveranstaltung ist ein gesonderter Antrag beizubringen.</li>",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Please upload the application in German or English
                <ul>
                    <li><u>by September 22nd for the winter semester</u></li>
                    <li><u>by February 22nd for the summer semester</u> at the latest.</li>
                </ul>
                <br>The decision on the application is usually made within two weeks from September 22 (winter semester) or February 22 (summer semester).
                <br><br>A separate application must be submitted for each course.</li>",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'requestAnrechnungInfoNachweisdokumenteTitle',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nachweisdokumente: Voraussetzung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Prerequisites for Verification Documents',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'requestAnrechnungInfoNachweisdokumenteBody',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Bitte laden Sie mehrere Nachweis-Dokumente zusammengefasst in einem PDF-Dokument hoch.
					<br><br>Falls für den Nachweis der Gleichwertigkeit in Bundesgesetzblättern veröffentlichte Lehrpläne (vgl. HTL, HAK…) verwendet werden,
					sind die für die Anrechnung relevanten Teile entsprechend zu <b>markieren</b>.
					<br><br><span class=\"text-danger\">Falls das nicht gemacht wird, wird der Antrag aus formalen Gründen <b>abgelehnt</b>.</span>",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Please combine and upload more than one verification document in one PDF document.
				<br><br>If you use curricula published in federal law gazettes (cf. HTL, HAK ...) to prove equivalence, please <b>mark</b> the parts relevant for recognition accordingly.
				<br><br><span class=\"text-danger\">If this information is not included, the application must be <b>rejected</b>.</span>",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'requestAnrechnungInfoHerkunftKenntnisseTitle',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Herkunft der Kenntnisse: Angaben',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Prerequisites for Origin of previous Knowledge',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'requestAnrechnungInfoHerkunftKenntnisseBody',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "<h5><u>Bei Anrechnungen von schulischen bzw. hochschulischen Zeugnissen</u></h5>
                Bitte geben Sie an, wo Sie die Kenntnisse erworben haben: (Hoch-)Schultyp, Standort, Fachrichtung.
                Beispiel Schule: HTL Mödling, Fahrzeugtechnik; Beispiel Hochschule: TU Wien, Bachelor
                Wirtschaftsinformatik
                <br>
                <h5><u>Bei Anrechnungen von beruflicher Praxis</u></h5>
                Bitte erstellen Sie eine detaillierte Tätigkeitsbeschreibung. Dafür steht im CIS ein Formular zur Verfügung, das in ein PDF-Dokument umzuwandeln und gemeinsam mit dem Lebenslauf hochzuladen ist.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "<h5><u>If school or university certificates are to be recognized</u></h5>
                Please indicate where you acquired the knowledge: type of (university) school, location, subject area. Example school: HTL Mödling, vehicle technology; Example university: Vienna University of Technology, Bachelor of Business Informatics
                <br>
                <h5><u>If professional practice is to be recognized</u></h5>
                Please supply a detailed job description. Therefore a formular is provided on CIS, that should be converted as pdf file and supplied together with the CV.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'reviewAnrechnungInfoFristenTitle',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Beantragung: Fristen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Deadlines',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'reviewAnrechnungInfoFristenBody',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Die Entscheidung über den Antrag durch die Studiengangsleitung sollte
                <ul>
                    <li><u>innerhalb von zwei Wochen ab dem 22. September (Wintersemester)</u></li>
                    <li><u>innerhalb von zwei Wochen ab dem 22. Februar (Sommersemester)</u> erfolgen.</li>
                </ul>",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "The decision on the application is usually made by the program director
                <ul>
                    <li><u>within two weeks from September 22 (winter semester)</u></li>
                    <li><u>within two weeks from February 22 (summer semester)</u>.</li>
                </ul>",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'reviewAnrechnungInfoAntragVoraussetungenTitle',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Antrag: Voraussetzungen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Prerequisites for Application',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'reviewAnrechnungInfoAntragVoraussetungenBody',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Eine Anerkennung setzt voraus, dass die erworbenen Kenntnisse mit dem Inhalt und Umfang der Lehrveranstaltung gleichwertig sind.
                <br><br>Positiv absolvierte Prüfungen von allgemein- und berufsbildenden höheren Schulen sind anzurechnen, sofern sie hinsichtlich Inhalt und Umfang mit der zu erlassenden Lehrveranstaltung gleichwertig sind (vgl. Satzungsteil Studienrechtliche Bestimmungen / Prüfungsordnung, § 4 Abs. 8).
                <br><br>
                <u>Umfangmäßige Gleichwertigkeit Schule - Hochschule:</u>
                <br>1 ECTS an der FH Technikum Wien entspricht einem Arbeitsaufwand von 25 Stunden, ein Schulhalbjahr besteht aus ca. 20 Wochen.
                <br>Das heißt eine Unterrichtsstunde pro Woche sind insgesamt ca. 20 Stunden.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "A prerequisite for recognition is that the knowledge acquired is equivalent to the content and scope of the course.
				<br><br>Successfully completed examinations from general and vocational secondary schools are to be recognized as long as they are equivalent to the course to be exempted with regard to content and scope (cf. Statute on Studies Act Provisions / Examination Regulations, § 4 Para. 8).
				<br><br>
				<u>Equivalence school - university in terms of scope:</u>
                <br>1 ECTS at the UAS Technikum Wien corresponds to a workload of 25 hours, a school semester consists of approx. 20 weeks.
                <br>i.e. one teaching hour per week is a total of approx. 20 hours.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'reviewAnrechnungInfoNachweisdokumenteTitle',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nachweisdokumente: Voraussetzung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Prerequisites for Verification Documents',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'reviewAnrechnungInfoNachweisdokumenteBody',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "<h5><u>Beantragung aufgrund eines Zeugnisses</u></h5>
         		Falls für den Nachweis der Gleichwertigkeit in Bundesgesetzblättern veröffentlichte Lehrpläne (vgl. HTL, HAK…) verwendet werden, sind entweder nur die für die Anrechnung relevanten Teile hochzuladen oder entsprechend zu markieren.
                <br><br>Die folgenden Informationen müssen enthalten sein:
                <ol>
                    <li>Name der das Zeugnis ausstellenden Institution</li>
                    <li>Beschreibung der Lehrinhalte und / oder Lernergebnisse</li>
                    <li>Zeitlicher Umfang der Lehrveranstaltung (z. B. SWS, ECTS, Unterrichtsstunden…)</li>
                </ol>
                <br>
                <h5><u>Beantragung aufgrund beruflicher Praxis</u></h5>
                Es wird eine detaillierte Tätigkeitsbeschreibung benötigt. Dafür steht im CIS ein Formular zur Verfügung, das in ein PDF-Dokument umzuwandeln und gemeinsam mit dem Lebenslauf hochzuladen ist.
                <br><br><span class=\"text-danger\">Falls diese Informationen nicht enthalten sind, kann der Antrag nicht geprüft werden und er wird abgelehnt.</span>",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '<h5><u>Application for recognition based on a certificate</u></h5>
                To prove equivalence of curricula published in federal law gazettes (cf. HTL, HAK ...), only the parts relevant for recognition should be uploaded or marked accordingly.
                <br><br>The following information must be included:
                <ol>
                    <li>name of the institution issuing the certificate</li>
                    <li>description of the teaching content and / or learning outcomes</li>
                    <li>duration of the course (e.g. ECTS, contact hours per week, total number of hours taught...)</li>
                </ol>
                <br>
                <h5><u>Application for recognition based on professional practice</u></h5>
                If the exemption is to be based on professional practice, an upload of a detailed job description is required. Therefore a formular is provided in CIS, that should be converted as pdf file and supplied together with the CV.
                <br><br><span class="text-danger">If this information is not included, the application can not be checked adequately and it might need to be rejected.</span>',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'reviewAnrechnungInfoHerkunftKenntnisseTitle',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Herkunft der Kenntnisse: Angaben',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Prerequisites for Origin of previous Knowledge',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'reviewAnrechnungInfoHerkunftKenntnisseBody',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "<h5><u>Bei Anrechnungen von schulischen bzw. hochschulischen Zeugnissen</u></h5>
 					Angabe, wo die Kenntnisse erworben worden sind: (Hoch-)Schultyp, Standort, Fachrichtung. Beispiel Schule: HTL Mödling, Fahrzeugtechnik; Beispiel Hochschule: TU Wien, Bachelor
                Wirtschaftsinformatik
                <br>
                <h5><u>Bei Anrechnungen von beruflicher Praxis</u></h5>
                Angabe von Unternehmen, Position und Funktion sowie Dauer der Beschäftigung.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "<h5><u>If school or university certificates are to be recognized</u></h5>
                Indication where the knowledge has been acquired: type of (university) school, location, subject area. Example school: HTL Mödling, vehicle technology; Example university: Vienna University of Technology, Bachelor of Business Informatics
                <br>
                <h5><u>If professional practice is to be recognized</u></h5>
                Specification of company, position and function as well as length of employment.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'systemfehler',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Systemfehler<br>Bitte kontaktieren Sie den Systemadministrator.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "System Error<br>Please contact the system administrator.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'bitteMindEinenAntragWaehlen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Bitte w&auml;hlen Sie zumindest einen Antrag aus.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Please select at least one application.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'bitteBegruendungAngeben',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Bitte geben Sie eine Begr&uml;ndung an.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Please provide a reason.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'anrechnungenWurdenEmpfohlen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => " Anrechnungsantr&auml;ge wurden empfohlen.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => " Applications have been recommended.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'anrechnungenWurdenNichtEmpfohlen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => " Anrechnungsantr&auml;ge wurden nicht empfohlen.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => " Applications have not been recommended.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'anrechnungenWurdenGenehmigt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => " Anrechnungsantr&auml;ge wurden genehmigt.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => " Applications have been approved.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'anrechnungenWurdenAbgelehnt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => " Anrechnungsantr&auml;ge wurden abgelehnt.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => " Applications have been rejected.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'empfehlungWurdeAngefordert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => " Empfehlung wurde angefordert.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => " Recommendation has been requested.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'empfehlungWurdeAngefordertAusnahmeWoKeineLektoren',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Empfehlungsanfragen: {0}<br>Abgeschickt: {1}<br>Nicht abgeschickt: {2}<br>Grund: Keine Lektoren zu LV zugeteilt.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Requests for recommendation: {0}<br>Sent: {1}<br>Not sent: {2}<br>Reason: No lectors assigned to the course yet.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'alleInBearbeitungSTGL',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Alle anzeigen, die durch die Studiengangsleitung bearbeitet werden.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Show all that are processed by the study course director.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'alleInBearbeitungLektor',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Alle anzeigen, die auf Empfehlung von LektorIn warten.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Show all that are waiting for recommendation.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'zuruecknehmen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Zur&uuml;cknehmen",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Withdraw",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'genehmigungAblehnungWirklichZuruecknehmen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Ihre Genehmigung / Ablehnung wirklich zurücknehmen?",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Do you really want to withdraw your approval / rejection?",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'erfolgreichZurueckgenommen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Erfolgreich zurückgenommen.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Successfully withdrawn.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'empfehlungsanforderungWirklichZuruecknehmen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Empfehlungsanforderung wirklich zurücknehmen?",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Do you really want to withdraw your request for recommendation?",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'antragNurImAktSS',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Der Antrag kann nur für das aktuelle Semester gestellt werden",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "You only can apply for the actual study semester",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'antragNichtFuerVerganganeSS',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Der Antrag kann nicht für vergangene Semester gestellt werden",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "You can not apply for the past study semester",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'neu',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Neu",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "New",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'inBearbeitung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "in Bearbeitung",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "in process",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
    array(
        'app' => 'core',
        'category' => 'ui',
        'phrase' => 'bearbeitetVon',
        'insertvon' => 'system',
        'phrases' => array(
            array(
                'sprache' => 'German',
                'text' => "bearbeitet von",
                'description' => '',
                'insertvon' => 'system'
            ),
            array(
                'sprache' => 'English',
                'text' => "edited by",
                'description' => '',
                'insertvon' => 'system'
            )
        )
    ),
    array(
        'app' => 'core',
        'category' => 'ui',
        'phrase' => 'bearbeitetAm',
        'insertvon' => 'system',
        'phrases' => array(
            array(
                'sprache' => 'German',
                'text' => "bearbeitet am",
                'description' => '',
                'insertvon' => 'system'
            ),
            array(
                'sprache' => 'English',
                'text' => "edited on",
                'description' => '',
                'insertvon' => 'system'
            )
        )
    ),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'antragWurdeGestellt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Antrag wurde gestellt.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Application was submitted successfully.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'antragBereitsGestellt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Der Antrag wurde bereits gestellt.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Application has already been submitted.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'genehmigungNegativEmpfehlungstextUebernehmen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Empfehlungstext des Lektors als Begründung übernehmen und ggf. redigieren.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Copy the lectors recommendation text as reason for the rejection and edit if necessary",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'errorFelderFehlen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Daten fehlen.<br>Bitte f&uuml;llen Sie alle Formularfelder aus",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Missing data.<br>Please fill in all form fields",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'felderFehlen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Bitte füllen Sie alle Formularfelder aus",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Please fill in all form fields",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'errorUploadFehlt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Dokument fehlt.<br>Bitte laden Sie noch die entsprechenden Dokumente hoch.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Missing document.<br>Please upload the required documents.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
    array(
        'app' => 'core',
        'category' => 'ui',
        'phrase' => 'errorDokumentZuGross',
        'insertvon' => 'system',
        'phrases' => array(
            array(
                'sprache' => 'German',
                'text' => "Dokument zu groß",
                'description' => '',
                'insertvon' => 'system'
            ),
            array(
                'sprache' => 'English',
                'text' => "Document maximum size exceeded",
                'description' => '',
                'insertvon' => 'system'
            )
        )
    ),
    array(
        'app' => 'core',
        'category' => 'ui',
        'phrase' => 'errorUploadFehltOderZuGross',
        'insertvon' => 'system',
        'phrases' => array(
            array(
                'sprache' => 'German',
                'text' => "Dokument fehlt oder zu groß",
                'description' => '',
                'insertvon' => 'system'
            ),
            array(
                'sprache' => 'English',
                'text' => "Document missing or maximum size exceeded",
                'description' => '',
                'insertvon' => 'system'
            )
        )
    ),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'errorNichtAusgefuehrt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Ihre Anfrage konnte nicht ausgefuehrt werden.<br>Bitte wenden Sie sich an den IT-Support.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Your request could not be processed.<br>Please contact the IT Support team.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'empfehlungsanfrageAn',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Anfrage an",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Requested to",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'empfehlungsanfrageAm',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Empfehlung angefragt am",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Requested on",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'maxZeichen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Max. Zeichen",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Max. Characters",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
    array(
        'app' => 'core',
        'category' => 'ui',
        'phrase' => 'fehlendeMinZeichen',
        'insertvon' => 'system',
        'phrases' => array(
            array(
                'sprache' => 'German',
                'text' => "Fehlende min. Zeichen",
                'description' => '',
                'insertvon' => 'system'
            ),
            array(
                'sprache' => 'English',
                'text' => "Missing min. Characters",
                'description' => '',
                'insertvon' => 'system'
            )
        )
    ),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'bestaetigungstext',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Hiermit bestätige ich, dass ich die relevanten <a href='https://moodle.technikum-wien.at/course/view.php?id=15449' target='_blank'>Prozess-Informationen</a> gelesen habe und bestätige hiermit auch die Vollständigkeit und Richtigkeit meiner Angaben.<br>Ich nehme zur Kenntnis, dass der Antrag nur einmal hochgeladen werden kann und dass Unterlagen nicht nachgereicht werden können.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "I hereby confirm that I have read the relevant <a href='https://moodle.technikum-wien.at/course/view.php?id=15449' target='_blank'>process information</a> and hereby also confirm the accuracy and completeness of the information I have provided above.<br>I acknowledge that the application can only be uploaded once and that documents cannot be submitted later.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'errorBestaetigungFehlt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Ihre Bestaetigung fehlt.<br>Bitte aktivieren Sie das entsprechende Feld.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Your confirmation is missing.<br>Please confirm the corresponding field.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'neueAnrechnung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Neue Anrechnung",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "New Exemption",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'antragAnlegen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Antrag anlegen",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Create Application",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'keineLVzugeteilt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Dem Studierenden sind keine Lehrveranstaltungen zugeteilt.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "No courses assigned to this student yet.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'antragBearbeiten',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Antrag bearbeiten",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Go to application",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'anrechnung',
		'phrase' => 'antragBenotungBlockiert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Antrag kann aufgrund der vorhandenen Benotung nicht erstellt werden.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Application can not be created due to existing grade.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'eucovidqr',
		'phrase' => '3gNachweis',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Zertifikat hochladen",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "upload certificate",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'eucovidqr',
		'phrase' => 'QrViaWebcam',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "QR-Code via Webcam scannen",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "scan qr code via webcam",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'eucovidqr',
		'phrase' => 'oder',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "oder",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "or",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'eucovidqr',
		'phrase' => 'ZertifikatAlsPdfHochladen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Zertifikat als PDF hochladen (nur mit QR-Code, kein gescanntes Zertifikat)",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "upload certificate pdf (only with qrcode, no scanned certificate)",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'eucovidqr',
		'phrase' => 'ValidierungsergebnisAktuellesGueltigkeitsdatum',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Validierungsergebnis / gespeichertes Gültigkeitsdatum",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "validation result / stored valid date",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'eucovidqr',
		'phrase' => 'DateiZiehenUndAblegen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Datei hier hinziehen und ablegen",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "drag & drop file here",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'eucovidqr',
		'phrase' => 'KeinZugriffWebcam',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Zugriff auf die Webcam nicht möglich!",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "webcam access denied",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'eucovidqr',
		'phrase' => 'gueltigBis',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "gültig bis",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "valid to",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'eucovidqr',
		'phrase' => 'ZertifikatUngueltig',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Zertifikat ungültig",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "certificate invalid",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'eucovidqr',
		'phrase' => 'ZertifikatKonnteNichtGeprueftWerden',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Das Zertifikat konnte nicht verifiziert werden. Stellen Sie bitte sicher, dass ein QR-Code enthalten ist.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "certificate could not be verified. Please make sure it contains a qr-code.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'eucovidqr',
		'phrase' => 'Laedt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Lädt",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "loading",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'eucovidqr',
		'phrase' => '3G',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Covid19 Gültigkeitsdatum",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "covid19 valid date",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'eucovidqr',
		'phrase' => 'FehlerBeimSpeichernDesGueltigkeitsdatums',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Fehler beim Speichern des Gültigkeitsdatum",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "error saving valid date",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'eucovidqr',
		'phrase' => 'PersondatenInFH-CompleteStimmenNichtMitDemZertifikatUeberein',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Personendaten aus dem Zertifikat stimmen nicht dem angemeldeten Benutzer überein",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "person data from certificate does not match the logged in user",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'eucovidqr',
		'phrase' => 'UploadSuccessful',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Das Gültigkeitsdatum wurde erfolgreich gespeichert.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "validity date has been successfully stored.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'eucovidqr',
		'phrase' => 'UploadFailed',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Es wurde kein Gültigkeitsdatum gespeichert.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "validity date has not been stored.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'eucovidqr',
		'phrase' => 'maxtagebeschreibung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "ACHTUNG seit Februar 2022 werden Zutrittskarten für maximal 60 Tage freigeschalten.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "ATTENTION since February 2022 a maximum of 60 days validity is granted for accesscards",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'eucovidqr',
		'phrase' => 'uploadbeschreibung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Hier kann ein Digitales COVID-Zertifikat der EU mit QR-Code selbst erfasst werden.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "an EU Digital COVID Certificate with QR code can be self registered here.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'eucovidqr',
		'phrase' => 'manualbeschreibung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Falls das Zertifikat keinen QR-Code enthält oder die Selbst-Erfassung fehlschlägt, kann das Zertifkat beim Empfang manuell erfasst werden.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "if the certificate does not contain a QR code or self registration fails, the certificate can be manually registered at the front desk.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'eucovidqr',
		'phrase' => 'supportbeschreibung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Bei technischen Problemen kontaktieren Sie bitte: ",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "in case of technical issues please contact: ",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	//*******************	ÖH-Beitragsverwaltung
	array(
		'app' => 'core',
		'category' => 'oehbeitrag',
		'phrase' => 'oehbeitragsVerwaltung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "ÖH-Beitragsverwaltung",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Student Union Fee Management",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'oehbeitrag',
		'phrase' => 'oehbeitragHinzufuegen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Neuen ÖH-Beitrag hinzufügen",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Add new student union fee",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'gueltigVon',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "gültig von",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "valid from",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'gueltigBis',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "gültig bis",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "valid to",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'oehbeitrag',
		'phrase' => 'studierendenbetrag',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "studierendenbetrag",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "student amount",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'oehbeitrag',
		'phrase' => 'versicherungsbetrag',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "versicherungsbetrag",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "insurance amount",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'aktion',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Aktion",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "action",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'bearbeiten',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Bearbeiten",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Edit",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'schliessen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Schließen",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Close",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'unbeschraenkt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "unbeschränkt",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "unlimited",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'oehbeitrag',
		'phrase' => 'oehbeitraegeFestgelegt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "ÖH-Beiträge für alle Studiensemester festgelegt",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "show menu",
				'description' => 'Student union fees set for all semesters',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'oehbeitrag',
		'phrase' => 'fehlerHolenOehbeitraege',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Fehler beim Holen der Öhbeiträge",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Error when getting student union fees",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'oehbeitrag',
		'phrase' => 'fehlerHolenSemester',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Fehler beim Holen der Semester",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Error when getting semester",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'oehbeitrag',
		'phrase' => 'fehlerHinzufuegenOehbeitrag',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Fehler beim Hinzufügen des ÖH-Beitrags",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Error when adding student union fee",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'oehbeitrag',
		'phrase' => 'fehlerAktualisierenOehbeitrag',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Fehler beim Aktualisieren des ÖH-Beitrags",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Error when updating student union fee",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'oehbeitrag',
		'phrase' => 'fehlerLoeschenOehbeitrag',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Fehler beim Löschen des ÖH-Beitrags",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Error when deleting student union fee",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	//*******************	Issue/Fehler Monitoring
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'fehlerMonitoring',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Fehler Monitoring",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Error Monitoring",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'keinen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Keinen",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "None",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'statusFuerAusgewaehlteSetzen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Status für Ausgewählte setzen",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Set state for selected",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'meldungen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Meldungen",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "messages",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'behoben',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Behoben",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Resolved",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'inBearbeitung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "In Bearbeitung",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "In progress",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'inhalt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Inhalt",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Content",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'inhaltExtern',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Inhalt extern",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "External content",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'fehlerstatus',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Fehlerstatus",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Error state",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'fehlercode',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Fehlercode",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Error code",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'fehlercodeExtern',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Fehlercode extern",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "External error code",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'fehlertyp',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Fehlertyp",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Error type",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'verarbeitetVon',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Verarbeitet von",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Processed by",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'verarbeitetAm',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Verarbeitet am",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Processed on",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'applikation',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Applikation",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "application",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'fehlertypcode',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Fehlertypcode",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Error type code",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'statuscode',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Statuscode",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "State code",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'hauptzustaendig',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Hauptzuständig",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Main responsibility",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'bitteStatusWaehlen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Bitte wählen Sie den Status aus.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Please select the state.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'bitteFehlerWaehlen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Bitte wählen Sie die Fehler aus.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Please select the errors.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'statusAendernFehler',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Fehler beim Status Ändern",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Error when changing state",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'statusAendernUnbekannterFehler',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Unbekannter Fehler beim Status Ändern",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Unknown error when changing state",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'fehlerZustaendigkeiten',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Fehler Zuständigkeiten",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Error Responsibilities",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'zustaendigerMitarbeiter',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Mitarbeiter",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Employee",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'oder',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "oder",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "or",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'organisationseinheit',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Organisationseinheit",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Organisational Unit",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'funktion',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Funktion",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Function",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'zustaendigkeitZuweisen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Zuständigkeit zuweisen",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Assign Responsibility",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'fehlerkurzbz',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Fehler Kurzbezeichnung",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Error short name",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'fehlertext',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Fehlertext",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Error text",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'oeKurzbz',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Organisationseinheit Kurzbezeichung",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Organisational Unit Short Name",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'oeBezeichnung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Organisationseinheit",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Organisational Unit",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'funktionKurzbz',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Funktion Kurzbezeichnung",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Function Short Name",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'funktionBeschreibung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Funktion Beschreibung",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Function Description",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'fehlercodeFehlt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Fehlercode fehlt",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Error code missing",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'mitarbeiterUndOeFehlt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Mitarbeiter oder Organisationseinheit müssen gesetzt sein",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Employee or organisational unit must be set",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'nurOeOderMitarbeiterSetzen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Mitarbeiter und Organisationseinheit dürfen nicht gleichzeitig gesetzt sein",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Employee and organisational unit cannot be both set",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'ungueltigeMitarbeiterId',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Mitarbeiter person Id ist ungültig",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Employee Id is invalid",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'zustaendigkeitExistiert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Zuständigkeit existiert bereits",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Assignment already exists",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'ungueltigeZustaendigkeitenId',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Ungültige Zuständigkeiten Id",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Invalid assignement id",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'zustaendigkeitGespeichert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Zuständigkeit gespeichert",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Assignment saved",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'zustaendigkeitGespeichertFehler',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Fehler beim Speichern der Zuständigkeit",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Error when saving assignment",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'zustaendigkeitGeloescht',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Zuständigkeit gelöscht",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Assignment deleted",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'zustaendigkeitGeloeschtFehler',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Fehler beim Löschen der Zuständigkeit",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Assignment deleted",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'keineAuswahl',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "keine Auswahl",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "no selection",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'zustaendigePersonen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "zuständige Personen",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "responsible persons",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'zustaendigeOrganisationseinheiten',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "zuständige Organisationseinheiten",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "responsible organisation units",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'zugehoerigkeit',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Zugehörigkeit",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "belonging",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
    array(
        'app' => 'core',
        'category' => 'ui',
        'phrase' => 'nurLeseberechtigung',
        'insertvon' => 'system',
        'phrases' => array(
            array(
                'sprache' => 'German',
                'text' => "Nur Leseberechtigung",
                'description' => '',
                'insertvon' => 'system'
            ),
            array(
                'sprache' => 'English',
                'text' => "Read-Only Access",
                'description' => '',
                'insertvon' => 'system'
            )
        )
    ),
    //*******************	KVP
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'op.label.type',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Art der Einmeldung",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Art der Einmeldung",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'op.label.name',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Einreichende*r",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Einreichende*r",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'op.label.email',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Email",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Email",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'op.label.phone',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "DW",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "DW",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'op.label.implemented',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "bereits umgesetzt?",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "bereits umgesetzt?",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'op.label.items',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Betroffene items",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Betroffene items",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'new.title',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Neuer Eintrag",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "New entry",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'new.info',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Im Rahmen der Weiterentwicklung von Lehrveranstaltungen können Sie hier wichtige Ideen für die Weiterentwicklung und/oder Verbesserungen einmelden. Diese können sich auf technische, inhaltliche, medien-didaktische oder test- und prüfungsrelevante Aspekte beziehen. Eine mögliche Umsetzung Ihrer Einmeldungen für das folgende Studienjahr wird im Weiteren Ablauf geprüft und priorisiert, und ggf. im Anschluss vom Teamlead im Quellkurs entsprechend eingearbeitet.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Im Rahmen der Weiterentwicklung von Lehrveranstaltungen können Sie hier wichtige Ideen für die Weiterentwicklung und/oder Verbesserungen einmelden. Diese können sich auf technische, inhaltliche, medien-didaktische oder test- und prüfungsrelevante Aspekte beziehen. Eine mögliche Umsetzung Ihrer Einmeldungen für das folgende Studienjahr wird im Weiteren Ablauf geprüft und priorisiert, und ggf. im Anschluss vom Teamlead im Quellkurs entsprechend eingearbeitet.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'new.form.info',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Bitte füllen Sie pro Verbesserungspotential einen eigenen Eintrag aus!",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Bitte füllen Sie pro Verbesserungspotential einen eigenen Eintrag aus!",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'new.workpackages',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Vorhandene Einmeldungen",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Vorhandene Einmeldungen",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'list.empty',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Keine Einmeldungen vorhanden",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Keine Einmeldungen vorhanden",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'new.form.success',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Die Einmeldung würde erfolgreich versandt.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Die Einmeldung würde erfolgreich versandt.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'new.form.required',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Pflichtfeld",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Required field",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'new.error.required',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Sie müssen hier einen Wert eintragen.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "You must supply a value here.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'new.form.kurs_id',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Bitte geben Sie Ihre Kurs ID (i.e. 5 Zahlen in der URL im Browser oben) an.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Bitte geben Sie Ihre Kurs ID (i.e. 5 Zahlen in der URL im Browser oben) an.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'new.form.kurs_id.label',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Kurs Url",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Kurs Url",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'new.form.kurs_kurzbz',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Bitte geben Sie die Kurzbezeichnug Ihres Kurses an (zb. TEZEI).",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Bitte geben Sie die Kurzbezeichnug Ihres Kurses an (zb. TEZEI).",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'new.form.kurs_kurzbz.label',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Kurskrzl",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Kurskrzl",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'new.form.type',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Art der Einmeldung:",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Art der Einmeldung:",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'new.form.type.label',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Art der Einmeldung",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Art der Einmeldung",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'new.form.title',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Bitte geben Sie einen aussagekräftigen Titel der Einmeldung an",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Bitte geben Sie einen aussagekräftigen Titel der Einmeldung an",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'new.form.title.label',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Titel der Einmeldung",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Titel der Einmeldung",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'new.form.priority',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Bitte geben Sie an, wie hoch Sie den Aufwand für die Umsetzung einschätzen",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Bitte geben Sie an, wie hoch Sie den Aufwand für die Umsetzung einschätzen",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'new.form.priority.label',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Aufwand der Umsetzung",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Aufwand der Umsetzung",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'new.form.description',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => " Beschreiben Sie das Verbesserungspotential:<br><br>Geben Sie so konkret wie möglich an, auf welche Stelle im Kurs Sie sich beziehen (z.B. Eigenstudium C Test).",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => " Beschreiben Sie das Verbesserungspotential:<br><br>Geben Sie so konkret wie möglich an, auf welche Stelle im Kurs Sie sich beziehen (z.B. Eigenstudium C Test).",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'new.form.description.label',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Beschreiben Sie das Verbesserungspotenzial.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Beschreiben Sie das Verbesserungspotenzial.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'new.form.items',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Bitte spezifizieren Sie, welche Items (Aufgaben, Materialien...) von dem Verbesserungspotential betroffen sind.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Bitte spezifizieren Sie, welche Items (Aufgaben, Materialien...) von dem Verbesserungspotential betroffen sind.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'new.form.items.label',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "betroffene items",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "betroffene items",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'new.form.attachments',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Attachments",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Attachments",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'new.form.implemented',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Wurde das Verbesserungspotential im Quellkurs bereits umgesetzt?",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Wurde das Verbesserungspotential im Quellkurs bereits umgesetzt?",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'new.form.implemented.label',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "bereits umgesetzt?",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "bereits umgesetzt?",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'new.form.submit',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Speichern",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Save",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'new.error.nosourcecourse.title',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Fehler",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Error",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'new.error.nosourcecourse.msg',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Dieser Moodle Kurs ist keinem Quellkurs zugeordnet",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "This Moodle Course doesn't have a Source Course",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'new.error.nostandardcourse.title',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Fehler",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Error",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'new.error.nostandardcourse.msg',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Dieser Moodle Kurs ist keinem standardisierten Quellkurs zugeordnet",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "This Moodle Course is not derived from a standardized Source Course",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'admin.title.oes',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Organisations Einheiten",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Organisations Einheiten",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'admin.title.openproject',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "OpenProject Projekte",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "OpenProject Projects",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'admin.title.edit',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "%s (%s) ändern",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Edit %s (%s)",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'admin.title.delete',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Löschen?",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Delete?",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'admin.title.source_linked',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Link auflösen?",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Remove link?",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'admin.title.target_linked',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Link auflösen?",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Remove link?",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'admin.text.delete',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Soll diese Verknüpfung wirklich entfernt werden?",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Do you really want to remove this link?",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'admin.text.source_linked',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Das Objekt ist noch mit einem anderen Objekt verknüpft. Diese Verknüpfung wird entfernt!",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Your Source is already linked to another item. This connection will be removed!",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'admin.text.target_linked',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Das Zielobjekt ist bereits mit einem anderen Objekt verknüpft. Diese Verknüpfung wird entfernt!",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Your Target is already linked to another item. This connection will be removed!",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'admin.label.search',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Suchen",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Search",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'admin.label.none',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "-Keine-",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "-None-",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'admin.confirm.ok',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "OK",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "OK",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'admin.confirm.cancel',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Abbrechen",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Cancel",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'admin.search.error.none',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Keine Einträge gefunden",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "No entries found",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'admin.label.template',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Template",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Template",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'admin.label.oe',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Organisationseinheit",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Organisation unit",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'admin.label.language',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Sprache",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Language",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'admin.label.moodle',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Moodle Kurs",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Moodle Course",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'admin.label.project',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "OP Projekt",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "OP Project",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'admin.label.version',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "OP Version",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "OP Version",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'studiensemesterGeplant',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Studiensemester geplant',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Semester planned',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'internationalCredits',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'International Credits',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'International Credits',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'studentstatus',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Studentstatus',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Student status',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'alledurchgefuehrten',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Alle durchgeführten anzeigen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Show all performed',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'bestaetigungHochladen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bestätigung hochladen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Upload confirmation',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'massnahmeLoeschen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Maßnahme löschen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Delete measure',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'massnahmen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Maßnahmen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Measures',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'internationalskills',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'International skills',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'International skills',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'massnahmen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Internationalisierungsmaßnahmen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Internationalization measures',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'internationalbeschreibung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ab dem Studienjahr 2022/23 ist der Erwerb von internationalen und interkulturellen Kompetenzen Teil des Curriculums. <br />
							Auf der Grundlage der vorliegenden Maßnahmen absolvieren Sie im Laufe ihres Studiums Internationalisierungsaktivitäten, die mit unterschiedlichen International Credits hinterlegt sind. <br />
							In Summe müssen 5 International Credits erworben werden, die im 6. Semester wirksam werden. <br/>
							Das Modul „International skills“ wird mit der Beurteilung „Mit Erfolg teilgenommen“ abgeschlossen. <br />
							Bitte wählen Sie die für Sie in Frage kommenden Maßnahmen aus und planen Sie das entsprechende Semester. <br />
							Sobald die 5 International Credits erreicht wurden, überprüft der Studiengang die von Ihnen hochgeladenen Dokumente. <br /><br />
							Fragen zum Status Ihrer Maßnahme u.ä. richten Sie bitte an den Studiengang. <br />
							Bei allen weiteren Fragen zum Thema Organisation und Finanzierung des Auslandsaufenthalts und/oder Sprachkurs gibt Ihnen das International Office der FH Technikum Wien unter <a href="mailto:international.office@technikum-wien.at">international.office@technikum-wien.at</a> gerne Auskunft.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Starting with the study year 2022/23, the acquisition of international and intercultural competencies is part of the curriculum.<br />
							On the basis of the measures at-hand, you will complete internationalization activities during the course of your studies, which are assigned different International Credits.<br />
							In total, 5 International Credits must be acquired, which become effective in the 6th semester.<br />
							The module “International skills” is completed with the assessment "Successfully participated". <br />
							Please select the measures that apply to you and schedule the appropriate semester. <br />
							Once the 5 International Credits have been achieved, the degree program will review the documents you have uploaded. <br /><br />
							Please direct questions regarding the status of your measure and the like should be directed to the study program. <br />
							For all further questions regarding the organization and financing of your stay abroad and/or language course, please contact the International Office of the UAS Technikum Wien at <a href="mailto:international.office@technikum-wien.at">international.office@technikum-wien.at</a>.
							',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'nurBachelor',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nur für Bachelorstudiengänge.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Only for bachelor programmes.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'bezeichnung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bezeichnung Deutsch',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'title german',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'bezeichnungeng',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bezeichnung Englisch',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'title english',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'beschreibung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Beschreibung Deutsch',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'description german',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'beschreibungeng',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Beschreibung Englisch',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'description english',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'massnahmeBearbeiten',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Massnahme bearbeiten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Edit measure',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'massnahmeLoeschenConfirm',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Wollen Sie die ausgewählte Maßnahme wirklich löschen?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Do you really want to delete the measure?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'fileLoeschenConfirm',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Wollen Sie die ausgewählte Bestätigung wirklich löschen?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Do you really want to delete the confirmation?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'planAblehnen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Plan ablehnen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Reject plan',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'entbestaetigenConfirm',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bestätigung widerrufen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Revoke confirmation',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'entakzeptierenConfirm',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Wollen Sie die Planbestätigung wirklich widerrufen?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Do you really want to cancel the plan confirmation?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'allegeplanten',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Alle geplanten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'All planned',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'alleGeplantenMarkieren',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Alle geplanten markieren',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Mark all planned',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'alleMassnahmenJetzt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Alle Maßnahmen anzeigen die im jetzigen Studiensemester geplant sind',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Show all measures that are planned for the current study semester',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'alleStudierendeJetzt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Alle Studierende anzeigen aus dem jetzigen Studiensemester',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Show all students from the current study semester',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'lastSemester',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Alle Studierende anzeigen aus dem letzten Semester',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Show all students from the last semester"',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'meinMassnahmeplan',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Mein Maßnahmenplan',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'My action plan',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'ectsBestaetigt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'International Credits bestätigt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'International Credits confirmed',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'ectsMassnahme',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'International Credits - Maßnahme',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'International Credits - Measures',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'statusGeplant',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'geplant',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'planned',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'statusGeplantDesc',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Studierende hat die Maßnahme geplant.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The student has planned the measure.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'statusAkzeptiert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'akzeptiert',
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
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'statusAkzeptiertDesc',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Die geplante Maßnahme wurde akzeptiert.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The planned measure has been accepted.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'statusDurchgefuehrt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'durchgeführt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'performed',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'statusDurchgefuehrtDesc',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Eine Bestätigung wurde hochgeladen.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'A confirmation has been uploaded.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'statusBestaetigt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'bestätigt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'confirmed',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'statusBestaetigtDesc',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Die hochgeladene Bestätigung wurde akzeptiert.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The uploaded confirmation has been accepted.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'statusAbgelehnt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'abgelehnt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'declined',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'statusAbgelehntDesc',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Die Maßnahme wurde abgelehnt.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The measure was rejected.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'ampelRed',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Es wurden keine Maßnahmen geplant oder alle wurden abgelehnt.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No measures have been planned, or all have been rejected.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'ampelYellow',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Mindestens eine Maßnahme wurde akzeptiert.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'At least one measure has been accepted.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'ampelOrange',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Es gibt mindestens eine geplante oder durchgeführte Maßnahme.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'There is at least one planned or performed measure.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'ampelGreenyellow',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nur bestätigte Maßnahmen vorhanden, aber weniger als 5 International Credits.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Only confirmed measures, but fewer than 5 International Credits.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'ampelGreen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Es wurden 5 International Credits erreicht.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '5 International Credits have been achieved.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'mailMeldungzuviele',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Die Anzahl der Empfänger überschreitet 50. Bitte verwenden Sie einen Filter (z.B. Semester), um die Empfängeranzahl zu reduzieren.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The number of recipients exceeds 50. Please use a filter (e.g., semester) to reduce the number of recipients.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'mailMeldung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Keine Studierenden ohne geplante Maßnahmen gefunden.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No students without planned measures were found.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'mailButton',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'E-Mail nur an Studierende, die keine Maßnahmen geplant haben.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Send email only to students who have no planned measures.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'geplanteMassnahmen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Maßnahme - geplant',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Measure - planned',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'geplanteMassnahmenDesc',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Die Maßhname wurde geplant.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The measure was planned.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'akzpetierteMassnahmen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Plan - akzeptiert',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Plan - accepted',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'mailversenden',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Mail versenden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Send mail',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'durchgefuehrteMassnahmen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Maßnahme - durchgeführt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Measure - performed',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'bestaetigteMassnahmen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'International Credits - bestätigt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'International Credits - confirmed',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'abgelehnteMassnahmen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Maßnahme - abgelehnt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Measure - declined',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'planAkzeptieren',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Plan akzeptieren',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Accept plan',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'bestaetigungAkzeptieren',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bestätigung akzeptieren',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Accept confirmation',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'bestaetigungAblehnen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bestätigung ablehnen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Reject confirmation',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'grund',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Grund',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Reason',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'anmerkungstgl',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Begründung - Studiengangsleitung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Reason - Study course Director',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'mehrverplant',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '>=5 International Credits verplant',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '>=5 international credits planned',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'stgtodo',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nur offene Massnahmen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'only open measures',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'wenigerverplant',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '<5 International Credits verplant',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '<5 International Credits planned',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'mehrbestaetigt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '>=5 International Credits bestätigt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '>=5 International Credits confirmed',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'wenigerbestaetigt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '<5 International Credits bestätigt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '<5 International Credits confirmed',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'alleAkzeptierenPlan',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Alle markierten Pläne akzeptieren',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Accept all marked plans',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'downloadBestaetigung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bestätigung herunterladen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Download confirmation',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'international',
		'category' => 'international',
		'phrase' => 'addMassnahme',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Maßnahme hinzufügen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Add measure',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'gruppenmanagement',
		'phrase' => 'benutzerSchonZugewiesen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Benutzer ist bereits der Gruppe zugewiesen",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "User is already assigned to the group",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'gruppenmanagement',
		'phrase' => 'gruppenmanagement',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Gruppenmanagement",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Group management",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'gruppenmanagement',
		'phrase' => 'kurzbezeichnung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Kurzbezeichnung",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Short description",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'gruppenmanagement',
		'phrase' => 'bezeichnung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Bezeichnung",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Name",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'gruppenmanagement',
		'phrase' => 'beschreibung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Beschreibung",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Description",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'gruppenmanagement',
		'phrase' => 'zuweisenloeschen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Zuweisen/Entfernen",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Assign/Remove",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'gruppenmanagement',
		'phrase' => 'benutzergruppe',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Benutzergruppe",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "User group",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'gruppenmanagement',
		'phrase' => 'benutzerHinzufuegen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Benutzer hinzufügen",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Add user",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'gruppenmanagement',
		'phrase' => 'aktiv',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "aktiv",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "active",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'stammdatenFeldFehlt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bitte Nachname, Geschlecht und Geburtsdatum ausfüllen.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Please fill out the last name, gender and date of birth.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'statusSetzen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Status setzen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Set state',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'abgewiesenam',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Abgewiesen am',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Rejected on',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'statusAuswahl',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Status ausw&auml;hlen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Select status',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'infocenter',
		'category' => 'infocenter',
		'phrase' => 'statusZuruecksetzen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Status zur&uuml;cksetzen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Reset status',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'fehlerFehlerKonfigurationLaden',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehler beim Laden der Fehlerkonfiguration',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error when loading error configuration',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'fehlerFehlerLaden',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehler beim Laden der Fehler',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error when loading errors',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'ungueltigerKonfigurationstyp',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ungültiger Konfigurationstyp',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Invalid configuration type',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'ungueltigerKonfigurationswert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ungültiger Konfigurationswert für Datentyp {0}, Sonderzeichen nicht erlaubt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Invalid configuration value for data type {0}, special characters not allowed',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'fehlerKonfiguration',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehler Konfiguration',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error configuration',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'konfigurationstyp',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Konfigurationstyp',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'configuration type',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'konfigurationswert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Konfigurationswert',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'configuration value',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'konfigurationswertPlatzhalter',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'wert1;wert2;wert3',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'value1;value2;value3',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'konfigurationswertZuweisen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Konfigurationswert(e) zuweisen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Assign configuration value(s)',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'konfigurationsbeschreibung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Konfigurationsbeschreibung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Configuration description',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'konfigurationsdatentyp',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Konfigurationsdatentyp',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Configuration data type',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'konfigurationGespeichert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Konfiguration gespeichert',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Configuration saved',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'konfigurationGespeichertFehler',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehler beim Speichern der Konfiguration',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error when saving configuration',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'konfigurationGeloescht',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Konfiguration gelöscht',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Deleted configuration',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'konfigurationGeloeschtFehler',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehler beim Löschen der Konfiguration',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error when deleting configuration',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'fehlermonitoring',
		'phrase' => 'konfigurationswertLoeschen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Konfigurationswert(e) löschen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Delete configuration value(s)',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'uhstat',
		'phrase' => 'angabeFehlt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Angabe fehlt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Value is missing',

				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lvinfo',
		'phrase' => 'lehrveranstaltungsinformationen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Lehrveranstaltungsinformationen",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Course Information",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'rauminfo',
		'phrase' => 'rauminfo',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Raum Informationen",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Room Information",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'rauminfo',
		'phrase' => 'keineRaumReservierung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Keine Raum Reservierungen gefunden",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "No Room Reservations found",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'keinLektorZugeordnet',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Aktuell ist dieser Lehrveranstaltung noch kein Lektor zugeordnet",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "ENG Aktuell ist dieser Lehrveranstaltung noch kein Lektor zugeordnet",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'incomingplaetze',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Incomingplätze",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Places Available for Incoming Students",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'lehrbeauftragter',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Lehrbeauftragte*r",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Lecturer(s)",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'lvleitung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "LV-Leiter*in",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Head of Course",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'noLvFound',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Keine Lehrveranstaltungen gefunden",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "No LVs found",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'leitung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Leitung",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Head",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'koordination',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Koordination",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Coordination",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'sprache',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Sprache",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Language",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'englisch',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Englisch",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "English",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'deutsch',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Deutsch",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "German",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'verfuegbareSprachen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Verfügbare Sprachen",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Available languages",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'tools',
		'phrase' => 'dokumente',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Dokumente',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Documents',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'tools',
		'phrase' => 'dokument',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Dokument',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Document',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'tools',
		'phrase' => 'erstelldatum',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Erstelldatum',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Creation date',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'tools',
		'phrase' => 'bestaetigungenZeugnisse',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bestätigungen/Zeugnisse',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Certificates/Transcripts',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'tools',
		'phrase' => 'inskriptionsbestaetigung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Studienbestätigung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Enrollment Confirmation',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'tools',
		'phrase' => 'studienbeitragFuerSSBezahlt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Studienbeitrag für das %1$s bezahlt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'tuition fee for semester %1$s paid',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'tools',
		'phrase' => 'studienbeitragFuerSSNochNichtBezahlt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Studienbeitrag für das %1$s noch nicht bezahlt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'tuition fee for semester %1$s not yet paid',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'tools',
		'phrase' => 'studienbeitragFuerStgNochNichtBezahlt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Studienbeitrag für %1$s noch nicht bezahlt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'tuition fee for %1$s not yet paid',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'tools',
		'phrase' => 'studienbeitragNochNichtBezahlt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Studienbeitrag noch nicht bezahlt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'tuition fee not yet paid',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'tools',
		'phrase' => 'studienerfolgsbestaetigung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Studienerfolgsbestätigung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Student progress report',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'tools',
		'phrase' => 'studiensemesterAuswaehlen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bitte wählen Sie das entsprechende Studiensemester aus',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Please select the corresponding semester',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'tools',
		'phrase' => 'vorlageWohnsitzfinanzamt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'zur Vorlage beim Wohnsitzfinanzamt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'for submission to local tax office',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'tools',
		'phrase' => 'studienbuchblatt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Studienbuchblatt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Student Record', //Noch zu übersetzen
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'tools',
		'phrase' => 'alleStudiensemester',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Alle Studiensemester',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'All semester',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'tools',
		'phrase' => 'abschlussdokumente',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Abschlussdokumente/Zeugnisse',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Final documents/Transcripts',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'tools',
		'phrase' => 'nochKeineAbschlussdokumenteVorhanden',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Noch keine Abschlussdokumente vorhanden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No final documents available yet',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'tools',
		'phrase' => 'keinStatusImStudiensemester',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Für das übergebene Studiensemester %1$s existiert kein Status. Bitte wählen Sie ein gültiges Studiensemester aus dem DropDown.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No status found for %1$s. Please select a valid semester from the dropdown.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'tools',
		'phrase' => 'warnungDruckDigitaleSignatur',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '<b>Hinweis!</b> Digital signierte Dokumente werden in manchen Browsern und PDF-Readern nicht korrekt angezeigt. <br/>Bitte verwenden Sie den <a href="https://get.adobe.com/de/reader/" target="_blank">Adobe Acrobat Reader</a>, wenn Sie das Dokument ausdrucken möchten.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '<b>Note!</b> Digitally signed documents are not displayed correctly in some browsers and PDF readers. <br/>Please use the <a href="https://get.adobe.com/de/reader/" target="_blank">Adobe Acrobat Reader</a> if you want to print the document.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'uhstat',
		'phrase' => 'ausbildungBildungsstaatUebereinstimmung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Höchster Abschluss (unterteilt in Österreich oder im Ausland/unbekannt) passt nicht zum Staat des höchsten Abschlusses.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Highest completed level of education (divided into those acquired in Austria and those abroad/unknown) does not match country of the highest completed level of education',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'uhstat',
		'phrase' => 'erfolgreichGespeichert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Erfolgreich gespeichert',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Saved successfully',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'uhstat',
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
				'text' => 'Error when saving',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'uhstat',
		'phrase' => 'uhstat1AnmeldungUeberschrift',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Erhebung bei der Anmeldung zu einem Studium oder bei Studienbeginn',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Survey when applying for a study or at the start of studies',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'uhstat',
		'phrase' => 'rechtsbelehrung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'gemäß § 18 Absätzen 6 und 7 Bildungsdokumentationsgesetz 2020, BGBl. I  Nr. 20/2021, in der gültigen Fassung, sowie § 141 Absatz 3 Universitätsgesetz 2002, BGBl. I  Nr. 120/2002, in der gültigen Fassung.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'according to section 18 subsections 6 and 7 of the Bildungsdokumentationsgesetz 2020, Federal Law Gazette I  No. 20/2021, in the current version, and section 141 subsection 3 of the Universitätsgesetz 2002, Federal Law Gazette I  No. 120/2002, in the current version.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'uhstat',
		'phrase' => 'uhstat1AnmeldungEinleitungstext',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Das Senden der Daten ist nur möglich, wenn die Sozialversicherungsnummer (bzw. das Ersatzkennzeichen) gültig ist und alle Fragen beantwortet worden sind. Wenn Sie etwas nicht wissen, wählen Sie die Antwortmöglichkeit „unbekannt“, aber beantworten Sie bitte alle Fragen.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Sending the data is only possible if the social security number (or the substitute code) is valid and all questions have been answered. If you don\'t know something, choose the answer option “unknown”, but please answer all questions.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'uhstat',
		'phrase' => 'uhstat1EinleitungSvnrtext',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Laut Bildungsdokumentationsgesetz sind wir verpflichtet die Sozialversicherungsnummer zu erheben bzw. zu registrieren. Falls Sie über keine Sozialversicherungsnummer verfügen, fordert die FH Technikum Wien in Ihrem Namen ein Ersatzkenneichen an.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'According to the General Social Insurance Act, we are obliged to collect and register your national insurance number. If you do not have a national insurance number, UAS Technikum Wien will request a social insurance substitute code on your behalf.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'uhstat',
		'phrase' => 'angabenErziehungsberechtigte',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Angaben zu Ihren Erziehungsberechtigten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Information about your legal guardians',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'uhstat',
		'phrase' => 'angabenErziehungsberechtigteEinleitungstext',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Die folgenden Fragen beziehen sich auf Personen, welche für Sie erziehungsberechtigt waren oder sind (Eltern oder jene Personen, die für Sie eine entsprechende Rolle übernommen haben, wie z.B. Stief- oder Pflegeeltern).',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The following issues refer to your legal guardians (parents or persons who were in the role of the parents, e.g. stepparents or foster parents).',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'uhstat',
		'phrase' => 'bitteAuswaehlen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bitte auswählen...',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'please select...',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'uhstat',
		'phrase' => 'erziehungsberechtigtePersonEins',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Erziehungsberechtigte Person 1/Mutter',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Legal guardian 1/mother',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'uhstat',
		'phrase' => 'geburtsjahr',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Geburtsjahr',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'year of birth',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'uhstat',
		'phrase' => 'geburtsstaat',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Geburtsstaat',
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
		'category' => 'uhstat',
		'phrase' => 'inDenHeutigenGrenzen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'in den heutigen Grenzen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'in today\'s borders',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'uhstat',
		'phrase' => 'hoechsterAbschlussStaat',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Staat des höchsten Abschlusses',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Country of the highest completed level of education',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'uhstat',
		'phrase' => 'hoechsterAbschluss',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Höchster Abschluss',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Highest completed level of education',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'uhstat',
		'phrase' => 'wennAbschlussInOesterreich',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Falls der höchste Bildungsabschluss in Österreich erworben wurde',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'If the highest level of education was completed in Austria:',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'uhstat',
		'phrase' => 'wennAbschlussNichtInOesterreich',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Falls das Land des höchsten erworbenen Bildungsabschlusses unbekannt oder nicht Österreich ist',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'If the country of the highest completed level of education is unknown or not Austria:',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'uhstat',
		'phrase' => 'erziehungsberechtigtePersonZwei',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Erziehungsberechtigte Person 2/Vater',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Legal guardian 2/father',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'uhstat',
		'phrase' => 'pruefenUndSpeichern',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Prüfen und Speichern',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Check and submit',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'uhstat',
		'phrase' => 'datenLoeschen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Daten löschen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Delete data',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'uhstat',
		'phrase' => 'erfolgreichGeloescht',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Erfolgreich gelöscht',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Deleted successfully',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'uhstat',
		'phrase' => 'datenLoeschen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Daten löschen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Delete data',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'infotext_Wiederholung_0',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Gemäß § 16 Abs. 1 FHG steht Studierenden einmalig das Recht auf Wiederholung eines Studienjahres in Folge einer negativ beurteilten kommissionellen Prüfung zu.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'According to § 16 paragraph 1 FHG, students have the right to repeat an academic year as a result of a negative examination before a committee.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'infotext_Wiederholung_1',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Die Wiederholung ist bei der Studiengangsleitung binnen eines Monats ab Mitteilung des negativen Prüfungsergebnisses bekannt zu geben.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The head of the degree program must be notified of the repetition within one month of notification of the negative examination result.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'infotext_Wiederholung_2',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Infolge der Weiterentwicklung der Qualität des Studienganges kann es zu Änderungen der Studienbedingungen im Zuge der Wiederholung eines Studienjahres kommen (z.B. Studienplan, Prüfungsordnung etc.).',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'As a result of the further development of the quality of the course, there may be changes to the study conditions in the course of repeating an academic year (e.g. study plan, examination regulations, etc.).',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'infotext_Wiederholung_3',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Die Studiengangsleitung legt Prüfungen und Lehrveranstaltungen für die Wiederholung des Studienjahres fest.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The head of the degree program determines examinations and courses for the repetition of the academic year.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'infotext_Wiederholung_4',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nicht bestandene Prüfungen und Lehrveranstaltungen sind jedenfalls, bestandene Prüfungen und Lehrveranstaltungen nur, sofern es der Zweck des Studiums erforderlich macht, zu wiederholen oder erneut zu besuchen. Wird eine Lehrveranstaltung nach der letzten Wiederholungsmöglichkeit negativ beurteilt, darf dieder Studierende an keiner kommissionellen Wiederholungsprüfung im Semester der negativ beurteilen Lehrveranstaltung und im Folgesemester mehr teilnehmen (nicht-kommissionelle Wiederholungsprüfungen können wahrgenommen werden)',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'In any case, failed examinations and courses are to be repeated or attended again only if the purpose of the course makes it necessary. If a course is assessed negatively after the last opportunity to repeat it, the student may no longer take part in a board re-examination in the semester in which the course was assessed as negative and in the following semester (non-board re-examinations can be taken).',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
/*
	// es kann fuer jede Kombination Typ und Status eine Phrase der Form info_<Typ>_<Status
	// erstellt werden, die dann in der Studierendenansicht in einer infobox angezeigt werden
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'info_Wiederholung_Erstellt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Info für Wiederholung Erstellt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Info for Wiederholung Erstellt',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
*/
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'antrag_Wiederholung_pruefung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Negativ beurteilte kommissionelle Prüfung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Negative assessment by a committee',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'antrag_Wiederholung_pruefung_date',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Datum der Beurteilung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'date of assessment',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'antrag_Wiederholung_button_yes',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ich gebe hiermit die Wiederholung des Studienjahres bekannt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'I hereby announce the repetition of the academic year',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'antrag_Wiederholung_button_no',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ich werde das Studienjahr nicht wiederholen und Abbrechen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'I will not repeat the academic year and will drop out',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'antrag_header',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Verwaltung des Studierendenstatus',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Management of student status',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'btn_new',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Neu Anlegen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Create new',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'cancel',
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
				'text' => 'Cancel',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'ok',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ok',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Ok',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'suche',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Suche',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Search',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'btn_create',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anlegen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Create',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'btn_create_Abmeldung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Vom Studium abmelden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Deregister from your studies',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'btn_create_Unterbrechung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Das Studium unterbrechen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Interrupt your studies',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'btn_cancel',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bekanntgabe zurückziehen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Cancel announcement',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'filter_all',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'alle anzeigen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'show all',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'filter_todo',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'nur offene anzeigen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'only show open',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'warning_Abmeldung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ihr CIS-Account ist noch 21 Tage aktiv. Wir bitten Sie, alle benötigten Dateien (Zeugnisse, Studienerfolgsbestätigungen, Studienbestätigungen, etc.) innerhalb dieses Zeitraums herunterzuladen. Für die Ausstellung von Duplikaten fallen nach Inaktivsetzung des CIS-Accounts Kosten an.' . "<br>\n" .
					'Bitte retournieren Sie baldmöglichst entlehnte Bücher an die Bibliothek.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Your CIS account is still active for 21 days. We ask you to download all required files (certificates, confirmations of academic success, confirmation of studies, etc.) within this period. There is a charge for issuing duplicates after the CIS account has been deactivated.' . "<br>\n" .
					'Please return borrowed books to the library as soon as possible.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'warning_AbmeldungStgl',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bitte beachten Sie die Einspruchsfrist von 2 Wochen nach Bestätigung durch die Studiengangsleitung!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Please note the objection period of 2 weeks after confirmation by the head of the degree program!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'title_Abmeldung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Abmeldung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'De-registration',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'title_AbmeldungStgl',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Abmeldung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'De-registration',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'title_Unterbrechung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Unterbrechung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Interruption',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'title_Wiederholung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Wiederholung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Repetition',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'title_new_Abmeldung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Neue Abmeldung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'New de-registration',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'antrag_typ',
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
				'text' => 'Type',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'antrag_status',
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
		'category' => 'studierendenantrag',
		'phrase' => 'antrag_studiensemester',
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
				'text' => 'Semester',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'antrag_erstelldatum',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Erstelldatum',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Createdate',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'studierendenantraege',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Studierendenanträge',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Applications',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'antrag_datum_wiedereinstieg',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Wiedereinstieg',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Re-Entry',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'antrag_grund',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Grund',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Reason',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'antrag_unruly',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Unruly',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Unruly',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'antrag_unruly_updated',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Unruly Person Status wurde aktualisiert.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Unruly person status has been updated.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'antrag_dateianhaenge',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Dateianhänge',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Attachments',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'antrag_anhang',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anhang',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Attachment',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'antrag_typ_Abmeldung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Abmeldung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Deregistration',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'antrag_typ_AbmeldungStgl',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Abmeldung (durch Studiengangsleitung)',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Cancellation (by course director)',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'antrag_typ_Unterbrechung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Unterbrechung',
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
		'category' => 'studierendenantrag',
		'phrase' => 'antrag_typ_Wiederholung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Wiederholung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Repetition',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'btn_show_lvs',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'LVs anzeigen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Show LVs',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'btn_edit',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bearbeiten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Edit',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'btn_reopen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Erneut Freischalten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Reopen',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'btn_pause',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Pausieren',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Pause',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'btn_unpause',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fortsetzen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Resume',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'btn_object',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Beeinspruchen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Object',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'btn_objection_deny',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Einspruch abgelehnt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Objection rejected',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'btn_objection_approve',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Einspruch stattgegeben',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Objection granted',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'btn_lvzuweisen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'LVs zuweisen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Assign Lvs',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'btn_approve',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bestätigen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Confirm',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'btn_reject',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ablehnen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Reject',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'skip',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Überspringen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Skip',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'select_studiensemester',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Studiensemester auswählen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Select a semester',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'keineBerechtigung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Keine Berechtigung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No authority',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'btn_save_lvs',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zuweisungen speichern',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Save assignments',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),

	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'btn_download_antrag',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'PDF herunterladen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Download PDF',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'table',
		'phrase' => 'download',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Download',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Download',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'table',
		'phrase' => 'reload',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Tabelle neu laden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Reload table',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_no_student',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Sie sind derzeit in keinem Studium',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'You are currently not enrolled',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_no_student_for_prestudent',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kein Student gefunden für Prestudent #{prestudent_id}',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No student found for prestudent #{prestudent_id}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_no_student_no_failed_exam',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Sie sind derzeit in keinem Studium oder haben keine negativ beurteilte kommissionelle Prüfung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'You are not currently studying or have not passed a board examination with negative reasons',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'no_attachments',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Keine Dateianhänge',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No Attachments',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'prestudent',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Prestudent',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Prestudent',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'studienjahr',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Studienjahr',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Academic year',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'title_lvzuweisen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Lehrveranstaltungen zuweisen für {name}',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Assign Courses for {name}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'title_lv_nicht_zugelassen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Angabe aller Lehrveranstaltungen, zu denen die Person nicht zugelassen ist',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Details of all courses to which the person is not admitted',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'title_lv_wiederholen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Angabe aller zu wiederholenden bzw. erneut zu besuchenden Lehrveranstaltungen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Specification of all courses to be repeated or attended again',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'title_show_lvs',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Lehrveranstaltungen für {name}',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Courses for {name}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'my_lvs',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Meine Lehrveranstaltungen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'My Courses',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'table',
		'phrase' => 'with_selected',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Mit {count} ausgewählten: ',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'With {count} selected: ',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'lv_nicht_zulassen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nicht zulassen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Don\'t allow ',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'lv_wiederholen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Wiederholen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Repeat',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'title_history',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Statusverlauf für #{id} ',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'History for #{id}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'anmerkung_tooltip',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Die Spalte Anmerkung kann verwendet werden, um Besonderheiten z.B. bei einem Studienplanwechsel zu dokumentieren (Änderung von LV-Titel, ECTS…) ',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The Comment column can be used to document special features, e.g. when changing the curriculum (change of course title, ECTS...)',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'fuer_alle_uebernehmen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'für alle übernehmen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'apply for all',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'fuer_x_uebernehmen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Auswahl für {count} weitere(n) Wiederholungsanträge übernehmen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Adopt selection for {count} further repeat applications',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'title_grund',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Grund für Ablehnung von #{id}',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Reason for rejection of #{id}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_no_lvs',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Keine Lehrveranstaltungen zugewiesen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No courses assigned',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'status_x',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '<b>Status:</b> {status}',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '<b>Status:</b> {status}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'status_saving',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Daten werden gespeichert...',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Saving...',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'status_error',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehler',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'status_open',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Offen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Open',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'status_created',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Erstellt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Created',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'status_cancelling',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zurückziehen...',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Cancelling...',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'status_cancelled',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zurückgezogen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Cancelled',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'status_unpaused',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fortgesetzt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Resumed',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'status_stop',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Gestoppt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Stopped',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_stg_blacklist',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Für diesen Studiengang sind keine Bekanntgaben möglich',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No announcements are accepted for this course',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_antrag_exists',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Es gibt bereits eine bestehende Bekanntgabe',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'There is already an existing announcement',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_antrag_pending',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Es gibt bereits eine bestehende Bekanntgabe vom Typ {typ}',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'There is already an existing announcement of type {typ}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_no_antrag_found',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Keine Bekanntgabe mit Id {id} gefunden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No announcement found with Id {id}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_no_antrag_found_prestudent',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Keine {typ} Bekanntgabe gefunden für Prestudent {prestudent_id}',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No {typ} announcement found for prestudent {prestudent_id}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_no_stg_and_sem',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kein Studiengang und Ausbildungssemester gefunden für Bekanntgabe mit Id {id}',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No studiengang and ausbildungssemester found for announcement with id: {id}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_no_stg',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kein Studiengang gefunden: {studiengang_kz}',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No studiengang found: {studiengang_kz}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_no_stg_antrag',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kein Studiengang gefunden für Bekanntgabe #{id}',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No studiengang found for announcement #{id}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_no_stg_email',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Keine Studiengang-Email gefunden für Bekanntgabe #{id}',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No studiengang-email found for announcement #{id}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_no_studienplan',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kein Studienplan gefunden für Studiengang: {studiengang_kz}, Studiensemester: {studiensemester_kurzbz}, Ausbildungssemester: {semester}',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No studienplan found for stg: {studiengang_kz}, studiensemester: {studiensemester_kurzbz}, ausbildungssemester: {semester}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_no_antragstatus',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kein Status für Bekanntgabe #{id} gefunden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No status found announcement #{id}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_multiple_studienplan',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Mehrere Studienpläne gefunden für Studiengang: {studiengang_kz}, Studiensemester: {studiensemester_kurzbz}, Ausbildungssemester: {semester}',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Multiple studienplans found for stg: {studiengang_kz}, studiensemester: {studiensemester_kurzbz}, ausbildungssemester: {semester}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_no_sem_after',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kein Studiensemester nach {semester} gefunden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No studiensemester found after: {semester}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_no_stdsem',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Das Studiensemester {studiensemester_kurzbz} existiert nicht',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Semester {studiensemester_kurzbz} does not exist',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_no_status_in_prev_sem',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kein Status im letzten Semester gefunden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No status found in previous semester',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_antrag_locked',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Diese Bekanntgabe ist gesperrt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'This request is locked',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_no_lv',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Keine Lehrveranstaltung ausgewählt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No course selected',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_no_lv_in_application',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Keine Lehrveranstaltung in Bekanntgabe gefunden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No course found in announcement',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_no_right',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Keine Berechtigung, die Bekanntgabe zu bearbeiten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No authorization to edit the announcement',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_no_objection',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Keine Berechtigung, die Bekanntgabe zu beeinspruchen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No authorization to object the announcement',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_not_objected',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bekanntgabe ist nicht beeinsprucht',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Announcement is not objected',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_not_approved',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bakanntgabe ist nicht bestätigt worden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Announcement is not approved',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_not_pausable',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bekanntgabe mit Id {id} kann nicht pausiert werden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Announcement with Id {id} cannot be not paused',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_not_paused',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bekanntgabe mit Id {id} ist nicht pausiert',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Announcement with Id {id} is not paused',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_stg_last_semester',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Der Studiengang hat nicht genügend Semester',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The course does not have enough semesters',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_no_person',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Keine Person gefunden mit id {person_id}',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No Person found with id {person_id}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_no_person_prestudent',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Keine Person gefunden für Prestudent #{prestudent_id}',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No Person found for prestudent #{prestudent_id}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_no_email',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kein Email kontakt gefunden für Person mit id {person_id}',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No email contact found for Person with id {person_id}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_no_prestudent',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kein Prestudent gefunden: {prestudent_id}',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No Prestudent found: {prestudent_id}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_no_prestudent_in_sem',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kein Status für Prestudent #{prestudent_id} in Semester {studiensemester_kurzbz} gefunden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No status found for prestudent #{prestudent_id} in semester {studiensemester_kurzbz}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_no_stg_for_prestudent',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kein Studiengang für Prestudent #{prestudent_id} gefunden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No course found for prestudent #{prestudent_id}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_no_prestudentstatus',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kein Status gefunden für Prestudent: {prestudent_id}',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No status found for Prestudent: {prestudent_id}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_mail_to',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Email an {email} konnte nicht versandt werden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Failed to send email to {email}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_mail',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Die Email wurde nicht an den Studenten versendet<br>Details:<br>{message}',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Mail to student not sent<br>Details:<br>{message}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_name',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Name des Studenten nicht gefunden<br>Details:<br>{message}',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Name of student not found<br>Details:<br>{message}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_mail_and_name',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Die Email wurde nicht an den Studenten versendet da kein Name gefunden wurde<br>Details:<br>{message}',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Mail to student not sent and student name not found<br>Details:<br>{message}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'studentIn',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'StudentIn ({prestudent_id})',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Student ({prestudent_id})',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_U_Approve',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Unterbrechung mit id {studierendenantrag_id} konnte nicht genehmigt werden.<br>Details:<br>{message}',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Could not approve Unterbrechung for studierendenantrag_id: {studierendenantrag_id}<br>Details:<br>{message}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'error_U_Reject',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Unterbrechung mit id {studierendenantrag_id} konnte nicht abgelehnt werden.<br>Details:<br>{message}',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Could not reject Unterbrechung for studierendenantrag_id: {studierendenantrag_id}<br>Details:<br>{message}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'mail_subject_A_Approve',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Abmeldung freigegeben',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Unsubscribe released',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'mail_subject_A_Student',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Abmeldung freigegeben',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Unsubscribe released',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'mail_subject_A_Stgl',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Abmeldung durch Studiengangsleitung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'De-registration by the course director',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'mail_subject_A_ObjectionDenied',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ihr Einspruch wurde Abgelehnt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Your objection was denied',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'mail_subject_U_Approve',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Unterbrechung freigegeben',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Interruption enabled',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'mail_subject_U_Reject',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Unterbrechung abgelehnt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Interruption rejected',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'mail_subject_W_New',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Neue*r Wiederholer*in',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'New Repeater',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'mail_subject_W_Approve',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Wiederholung von Studiengangsleitung freigegeben',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Repetition approved by course director',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'mail_subject_W_Student',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Wiederholung von Studiengangsleitung freigegeben',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Repetition approved by course director',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'mail_part_table',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '<table><tr><th>{stg_bezeichnung} ({stg_orgform_kurzbz})</th></tr>{rows}</table>',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '<table><tr><th>{stg_bezeichnung} ({stg_orgform_kurzbz})</th></tr>{rows}</table>',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'mail_part_x_new_Abmeldung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '<tr><td>{count} neue Abmeldung(en)</td></tr>',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '<tr><td>{count} new De-registration(s)</td></tr>',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'mail_part_x_new_Unterbrechung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '<tr><td>{count} neue(r) Antrag/Anträge auf Unterbrechung</td></tr>',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '<tr><td>{count} new application(s) for Interruption</td></tr>',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'mail_part_x_new_Wiederholung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '<tr><td>{count} neue LV Zuweisung(en) für Wiederholer</td></tr>',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '<tr><td>{count} new LV assignment(s) for repeaters</td></tr>',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'mail_part_grund',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '<h4>Grund:</h4><p>{grund}</p>',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '<h4>Reason:</h4><p>{grund}</p>',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'mail_part_error_no_lvs',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Keine Lehrveranstaltungen gefunden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No courses found',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'calltoaction_Abmeldung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Die Abmeldung vom Studium kann hier durchgeführt werden.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'You can deregister from your studies here.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'calltoaction_Unterbrechung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Eine Unterbrechung des Studiums ist hier zu beantragen. Die Gründe der Unterbrechung und die beabsichtigte Fortsetzung des Studiums sind nachzuweisen oder glaubhaft zu machen. In der Entscheidung über den Antrag sind zwingende persönliche, gesundheitliche oder berufliche Gründe zu berücksichtigen. Während der Unterbrechung können keine Prüfungen abgelegt werden.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'You can apply for an interruption of your studies here. The reasons for the interruption and the intended continuation of the course must be proven or made credible. Compelling personal, health or professional reasons must be taken into account when deciding on the application. No exams can be taken during the interruption.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'calltoaction_Wiederholung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Studierenden steht einmalig das Recht auf Wiederholung eines Studienjahres in Folge einer negativ beurteilten kommissionellen Prüfung zu. Die Wiederholung ist bei der Studiengangsleitung binnen eines Monats ab Mitteilung des Prüfungsergebnisses bekannt zu geben. Die Studiengangsleitung hat Prüfungen und Lehrveranstaltungen für die Wiederholung des Studienjahres festzulegen, wobei nicht bestandene Prüfungen und Lehrveranstaltungen jedenfalls, bestandene Prüfungen und Lehrveranstaltungen nur, sofern es der Zweck des Studiums erforderlich macht, zu wiederholen oder erneut zu besuchen sind.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Students have the one-time right to repeat an academic year as a result of a negative examination by a committee. The head of the degree program must be notified of the repetition within one month of notification of the examination result. The head of the degree program must determine examinations and courses for the repetition of the academic year, whereby failed examinations and courses are to be repeated or attended again, in any case, passed examinations and courses only if the purpose of the course makes it necessary.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'textLong_studentNichtGezahlt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ausschlußgrund gemäß Ausbildungsvertrag (Punkt 7.4): Nichterfüllung finanzieller Verpflichtungen trotz Mahnung (Studienbeitrag)',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Reason for exclusion according to the training contract (point 7.4): Failure to meet financial obligations despite a reminder (tuition fees)',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'textLong_studentNichtAnwesend',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ausschlußgrund gemäß Ausbildungsvertrag (Punkt 7.4): mehrmalig unentschuldigtes Verletzen der Anwesenheitspflicht',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Reason for exclusion according to the training contract (point 7.4): multiple unexcused breaches of attendance requirements',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'textLong_PruefunstermineNichtEingehalten',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ausschlußgrund gemäß Ausbildungsvertrag (Punkt 7.4): wiederholtes Nichteinhalten von Prüfungsterminen bzw Abgabeterminen für Seminararbeiten bzw. Projektarbeiten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Reason for exclusion according to the training contract (point 7.4): repeated non-compliance with examination dates or deadlines for seminar papers or project work',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'textLong_plageat',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ausschlußgrund gemäß Ausbildungsvertrag (Punkt 7.4): Plagiieren im Rahmen wissenschaftlicher Arbeiten bzw. unerlaubte Verwendung KI generierter Hilfsmittel bzw. Quellen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Reason for exclusion according to the training contract (point 7.4): Plagiarism in the context of scientific work or unauthorized use of AI-generated tools or sources',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'textLong_ungenuegendeLeistung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ausschlußgrund gemäß Ausbildungsvertrag (Punkt 7.4): nicht genügende Leistung im Sinne der Prüfungsordnung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Reason for exclusion according to the training contract (point 7.4): insufficient performance in terms of the examination regulations',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'textLong_NichtantrittStudium',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ausschlußgrund gemäß Ausbildungsvertrag (Punkt 7.4): Nichtantritt des Studiums zu Beginn des Studienjahres (=Unbegründetes Nichterscheinen zur ersten Studienveranstaltung)',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Reason for exclusion according to the training contract (point 7.4): Failure to start the course at the beginning of the academic year (= unjustified non-attendance to the first course event)',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'textLong_MissingZgv',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zugangsvoraussetzung BA (bzw. MA) nicht erfüllt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Entry requirements BA (resp. MA) not fulfilled)',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'textLong_unruly',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Person ist unruly',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Person is unruly',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'dropdown_nichtGezahlt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Student*in hat nicht bezahlt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Student has not paid',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'dropdown_nichtAnwesend',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Student*in war mehrmals unentschuldigt abwesend',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Student was absent without excuse several times',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'dropdown_PruefunstermineNichtEingehalten',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Student*in hat Prüfunstermine nicht eingehalten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Student failed to meet exam dates',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'dropdown_plageat',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Student*in hat plagiiert',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Student failed to meet exam dates',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'dropdown_ungenuegendeLeistung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Leistung ungenügend',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'performance insufficient',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'dropdown_NichtantrittStudium',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nichtantritt des Studiums',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'non-commencement of the course',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'dropdown_MissingZgv',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zugangsvoraussetzung BA (bzw. MA) nicht erfüllt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Entry requirements BA (resp. MA) not fulfilled',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'dropdown_bitteWaehlen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'bitte auswählen, sofern zutreffend',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'please select if applicable',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'grund_Wiederholung_deadline',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'negative kommissionelle Beurteilung und keine fristgerechte Bekanntgabe der Wiederholung des Studienjahres',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'negative assessment by the committee and no timely announcement of the repetition of the academic year',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'dropdown_unruly',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Person ist unruly.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Person is unruly.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studierendenantrag',
		'phrase' => 'mark_person_as_unruly',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Person ist unruly.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Person is unruly.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'notiz',
		'phrase' => 'document',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anhänge',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Attachments',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'notiz',
		'phrase' => 'notiz_new',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Neue Notiz',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'New Note',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'notiz',
		'phrase' => 'notiz_edit',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Notiz bearbeiten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Edit Note',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'notiz',
		'phrase' => 'notiz_delete',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Notiz löschen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Delete Note',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'notiz',
		'phrase' => 'verfasser',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Verfasser*in',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'author',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'notiz',
		'phrase' => 'bearbeiter',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bearbeiter*in',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'editor',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'notiz',
		'phrase' => 'erledigt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'erledigt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'completed',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'notiz',
		'phrase' => 'letzte_aenderung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'letzte Änderung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Last updated',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'error_fieldRequired',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Das Eingabefeld {field} ist erforderlich',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The Input Field {field} is required',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'error_fieldNotInteger',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Das Eingabefeld {field} muss eine Ganzzahl enthalten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The Field {field} must contain an integer',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'notiz',
		'phrase' => 'tag_rueckgaengig',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Rückgängig',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Undo',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'notiz',
		'phrase' => 'tag_erledigt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Erledigt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Done',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'notiz',
		'phrase' => 'tag_verfasser',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'angelegt von {0} am {1}',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Created by {0} on {1}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'notiz',
		'phrase' => 'tag_bearbeiter',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'bearbeitet von {0} am {1}',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Edited by {0} on {1}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'error_fieldNotNumeric',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Das Eingabefeld {field} darf nur Zahlen enthalten.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The Field {Field} must contain only numbers.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'error_fieldNoValidEmail',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Das Eingabefeld {field} muss eine gültige Email-Adresse enthalten.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The field {field} must contain a valid email address.',
				'description' => '',
				'insertvon' => 'system'
				)
		)
	),
	// Personalverwaltung begin
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'warnung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Warnung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Warning',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'vornamen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Vornamen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'middle names',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'maennlich',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Männlich',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Male',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'weiblich',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Weiblich',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Female',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'sprache',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Sprache',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'language',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'gemeinde',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Gemeinde',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'municipality',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'heimatadresse',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Heimatadresse',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'home address',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'zustelladresse',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zustelladresse',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'postal address',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'kontaktinformation',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kontaktinformation',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'contact information',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'zustellung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zustellung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'delivery',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'abweichenderEmpfaenger',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Abweich.Empf. (c/o)',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'dissenting recipient (c/o)',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'stammdatenGespeichert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Stammdaten gespeichert',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Master data saved',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'stammdatenNochNichtGespeichert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Stammdaten schließen? Geänderte Daten gehen verloren!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Close master data? Changes will be lost!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'kontaktdatenGespeichert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kontaktdaten gespeichert',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Contact data saved',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'kontaktdatenGeloescht',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kontaktdaten gelöscht',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Contact data deleted',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'bankdatenGeaendert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bankdaten schließen? Geänderte Daten gehen verloren!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Close banking data? Changes will be lost!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'bankdatenGeloescht',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bankdaten gelöscht',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Banking data deleted',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'bankdatenGespeichert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bankdaten gespeichert',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Banking data saved',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'bank',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bank',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'bank',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'anschrift',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anschrift',
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
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'bic',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'BIC',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'BIC',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'iban',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'IBAN',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'IBAN',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'blz',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'BLZ',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'bank no',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'kontonr',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kontonr',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'account no',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'bankverbindung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bankverbindung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'bank details',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'verrechnung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Verrechnung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'billing',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'mitarbeiterdaten',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Mitarbeiterdaten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Employee Data',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'kontaktdaten',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kontaktdaten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Contact Data',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'bankdaten',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bankdaten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Banking Data',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'stundensaetze',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Stundensätze',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Hourly Rates',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'sachaufwand',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Sachaufwand',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Material Expenses',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'sachaufwandGespeichert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Sachaufwand gespeichert',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Material expenses saved',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'sachaufwandGeloescht',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Sachaufwand gelöscht',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Material expenses deleted',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'funktionGespeichert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Funktion gespeichert',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Job function saved',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'funktionGeloescht',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Funktion gelöscht',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Job function deleted',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'sachaufwandNochNichtGespeichert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Sachaufwand schließen? Geänderte Daten gehen verloren!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Close material expenses? Changes will be lost!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'funktionNochNichtGespeichert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Funktion schließen? Geänderte Daten gehen verloren!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Close job function? Changes will be lost!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'stundensatzGespeichert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Stundensatz gespeichert',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Hourly rate saved',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'stundensatzGeloescht',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'stundensatz gelöscht',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Hourly rate deleted',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'stundensatzNochNichtGespeichert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Stundensätze schließen? Geänderte Daten gehen verloren!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Close hourly rates? Changes will be lost!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'stundensatzWirklichLoeschen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Stundensatz mit dem Typ',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Delete hourly rate of type',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'wirklichLoeschen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => ' wirklich löschen?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'core',
		'phrase' => 'unternehmen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Unternehmen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'company',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'dv_unternehmen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'DV/Unternehmen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'ec/company',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),

	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'mitarbeiterdatenGespeichert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Mitarbeiterdaten gespeichert',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Employee data saved',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'adresseGespeichert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Adresse gespeichert',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Address saved',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'adresseGeloescht',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Adresse gelöscht',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Address deleted',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'kannNichtGeloeschtWerden',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'kann nicht gelöscht werden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'cannot be deleted',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'wirklichLoeschen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'wirklich löschen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'delete',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'alias',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Alias',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Alias',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'telefonklappe',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Telefonklappe',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Phone Ext.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'ausbildung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ausbildung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Higher Education',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'buero',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Büro',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Office',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'standort',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Standort',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Site',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'bismelden',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bismelden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'report BIS',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'mitarbeiterdatenGeandert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Mitarbeiterdaten schließen? Geänderte Daten gehen verloren!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Close employee data? Changes will bei lost!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'fixangestellt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fixangestellt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'permanent employment',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'habilitation',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Habilitation',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Habilitation',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'funktion',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Funktion',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'job function',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'funktionen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Funktionen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'job functions',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'fachbereich',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fachbereich',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'specialist field',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'hrrelevant',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'HR relevant',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'HR-relevant',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'vertragsrelevant',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Vertragsrelevant',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Contract relevant',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'zuordnung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zuordnung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Attribution',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'zuordnung_taetigkeit',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zuordnung/Tätigkeit',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Attribution/Job Title',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'wochenstunden',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Wochenstunden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'working hours',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'abteilung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Abteilung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Department',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'vbform',
		'phrase' => 'oder',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'oder',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'or',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'ok',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'OK',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'OK',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'abwesenheiten',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Abwesenheiten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'off time',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'zeiterfassung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zeiterfassung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'time recording',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'vertretung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Vertretung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'stand-in',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'erreichbarkeit',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Erreichbarkeit',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'off time',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'person',
		'phrase' => 'grund',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Grund',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'reason',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'zeitaufzeichnung',
		'phrase' => 'id',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'ID',
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
		'app' => 'personalverwaltung',
		'category' => 'fristenmanagement',
		'phrase' => 'frist',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Frist',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Deadline',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'fristenmanagement',
		'phrase' => 'todo',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'To Do',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'To Do',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'fristenmanagement',
		'phrase' => 'fristStatusGespeichert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Status gespeichert.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Status saved.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'fristenmanagement',
		'phrase' => 'fristGespeichert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Frist gespeichert.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Deadline saved.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'fristenmanagement',
		'phrase' => 'fristGeloescht',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Frist gelöscht.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Deadline deleted.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'fristenmanagement',
		'phrase' => 'fristenAktualisiert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fristen aktualisiert.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Deadlines updated.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'fristenmanagement',
		'phrase' => 'termin_frist',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Termin/Frist',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Date/Deadline',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'fristenmanagement',
		'phrase' => 'ereignis',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ereignis',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Event',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'fristenmanagement',
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
		'app' => 'personalverwaltung',
		'category' => 'fristenmanagement',
		'phrase' => 'mitarbeiterin',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'MitarbeiterIn',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Employee',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	// Gehaltsband
	array(
		'app' => 'personalverwaltung',
		'category' => 'gehaltsband',
		'phrase' => 'gehaltsband',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Gehaltsband',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Salary Range',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'gehaltsband',
		'phrase' => 'gueltig_von',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Gültig von',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Valid from',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'gehaltsband',
		'phrase' => 'gueltig_bis',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Gültig bis',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Valid to',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'gehaltsband',
		'phrase' => 'betrag_von',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Betrag von',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Amount from',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'gehaltsband',
		'phrase' => 'betrag_bis',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Gültig bis',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Valid to',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'gehaltsband',
		'phrase' => 'gehaltsband_gespeichert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Gehaltsband gespeichert.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Salary range saved.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'gehaltsband',
		'phrase' => 'gehaltsband_erstellt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Gehaltsband hinzugefügt.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Salary range created.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'personalverwaltung',
		'category' => 'gehaltsband',
		'phrase' => 'gehaltsband_geloescht',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Gehaltsband gelöscht.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Salary range deleted.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	// Personalverwaltung end
	array(
		'app' => 'core',
		'category' => 'bismeldestichtag',
		'phrase' => 'stichtagHinzufuegen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Meldestichtag hinzufügen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Add report target date',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'bismeldestichtag',
		'phrase' => 'stichtageVerwalten',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'BIS-Meldestichtage verwalten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Manage report target dates',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'bismeldestichtag',
		'phrase' => 'stichtagLoeschen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Meldestichtag löschen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Delete report target date',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
		array(
			'app' => 'lehrauftrag',
			'category' => 'ui',
			'phrase' => 'hinweistextLehrauftrag',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => '<strong>Hinweis:</strong> Das Akzeptieren von Lehraufträgen ersetzt alle vorhergehenden Lehraufträge dieses Studiensemesters.',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => '<strong>Note:</strong> Accepting teaching assignments replaces all previous teaching assignments for this study semester.',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		//Profil Phrasen start
		array(
			'app' => 'core',
			'category' => 'profil',
			'phrase' => 'NOTFALLKONTAKT',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Notfallkontakt',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Emergency contact',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profil',
			'phrase' => 'TELEFON',
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
					'text' => 'Telephone',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
	array(
		'app' => 'core',
		'category' => 'profil',
		'phrase' => 'MOBIL',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Mobiltelefonnummer',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Cell phone number',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
		array(
			'app' => 'core',
			'category' => 'profil',
			'phrase' => 'EMAIL',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'E-Mail',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'E-Mail',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profil',
			'phrase' => 'personenInformationen',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Personen Informationen',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Person Information',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profil',
			'phrase' => 'postnomen',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Postnomen',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'post-nominals',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profil',
			'phrase' => 'Username',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Benutzername',
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
			'category' => 'profil',
			'phrase' => 'Anrede',
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
					'text' => 'Salutation',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profil',
			'phrase' => 'Titel',
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
					'text' => 'Title',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profil',
			'phrase' => 'Postnomen',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Postnomen',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Postnominal',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profil',
			'phrase' => 'Geburtsdatum',
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
					'text' => 'Birthdate',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profil',
			'phrase' => 'Geburtsort',
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
					'text' => 'Birthplace',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profil',
			'phrase' => 'Büro',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Büro',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Office',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profil',
			'phrase' => 'Kurzzeichen',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Kurzzeichen',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Abbreviation',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profil',
			'phrase' => 'Telefon',
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
					'text' => 'Telephone',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profil',
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
					'text' => 'Telephone',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profil',
			'phrase' => 'intern',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Intern',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Internal',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profil',
			'phrase' => 'alias',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Alias',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Alias',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profil',
			'phrase' => 'email',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'E-Mail',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'E-Mail',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profil',
			'phrase' => 'Anmerkung',
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
					'text' => 'Remark',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profil',
			'phrase' => 'notfallkontakt',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Notfallkontakt',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Emergency contact',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profil',
			'phrase' => 'Straße',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Straße',
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
			'category' => 'profil',
			'phrase' => 'Strasse',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Straße',
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
			'category' => 'profil',
			'phrase' => 'PLZ',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'PLZ',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'PLZ',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profil',
			'phrase' => 'Ort',
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
					'text' => 'Locality',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profil',
			'phrase' => 'Typ',
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
					'text' => 'Type',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profil',
			'phrase' => 'privateKontakte',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Private Kontakte',
					'description' => 'Profil Kategorie in der die ganzen privaten Kontakte einer Person aufgelistet werden',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Private Contacts',
					'description' => 'Profile category in which all private contacts of a person are listed',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profil',
			'phrase' => 'privateAdressen',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Private Adressen',
					'description' => 'Profil Kategorie in der die ganzen privaten Adressen einer Person aufgelistet werden',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Private Addresses',
					'description' => 'Profile category in which all private addresses of a person are listed',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profil',
			'phrase' => 'profilBearbeiten',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Profil bearbeiten',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'edit profil',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profil',
			'phrase' => 'mitarbeiterIn',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'MitarbeiterIn',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Employee',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profil',
			'phrase' => 'entlehnteBetriebsmittel',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Entlehnte Betriebsmittel',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Borrowed Company Resources',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profil',
			'phrase' => 'mitarbeiterInformation',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Mitarbeiter Information',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Employee Information',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profil',
			'phrase' => 'studentIn',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'StudentIn',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Student',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profil',
			'phrase' => 'studentInformation',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Student Information',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Student Information',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profil',
			'phrase' => 'zutrittsGruppen',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Zutrittsgruppen',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Access groups',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profil',
			'phrase' => 'fhAusweisStatus',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Der FH Ausweis ist am {0} ausgegeben worden.',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'The FH ID card was issued on {0}',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profil',
			'phrase' => 'mailverteiler',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Mailverteiler',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Mailing list',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profil',
			'phrase' => 'mailverteilerMitglied',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Sie sind Mitglied in folgenden Mailverteilern:',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'You are a member of the following mailing lists:',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profil',
			'phrase' => 'quickLinks',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Quick Links',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Quick Links',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profil',
			'phrase' => 'zeitwuensche',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Zeitwünsche',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Time wishes',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profil',
			'phrase' => 'lehrveranstaltungen',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Lehrveranstaltungen',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Courses',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profil',
			'phrase' => 'zeitsperren',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Zeitsperren',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Time locks',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profil',
			'phrase' => 'inventarnummer',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Inventarnummer',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'inventory number',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
	array(
		'app' => 'core',
		'category' => 'profil',
		'phrase' => 'ausgabedatum',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ausgabedatum',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'issue date',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'profil',
		'phrase' => 'wochenstunden',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Wochenstunden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'working hours',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
		//Profil Phrasen ende
		//ProfilUpdate Phrasen start
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'profilUpdates',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Profil Updates',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Profile Updates',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'topic',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Thema',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'topic',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'zustell_adressen_warning',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Einer ihrer Adressen wird bereits zur Zustellung verwendet, möchten sie diese Adressen als Zustellungsadresse übernehmen?',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'One of your addresses is already used as a contact address, would you like to use this address as your new contact address?',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'zustell_kontakte_warning',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Einer ihrer Kontakte wird bereits zur Zustellung verwendet, möchten sie diesen Kontakt als Zustellungskontakt übernehmen?',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'One of your contacts is already used as a communication mean, would you like to use this contact as your new communication mean?',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),

		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'kontaktTyp',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Kontakttyp',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'contact type',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'nebenwohnsitz',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Nebenwohnsitz',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'secondary residence',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'hauptwohnsitz',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Hauptwohnsitz',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'main residence',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'homeoffice',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Homeoffice',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Homeoffice',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'rechnungsadresse',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Rechnungsadresse',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Billing address',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'notfallkontakt',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Notfallkontakt',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Emergency contact',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'mobiltelefonnummer',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Mobiltelefonnummer',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Cell phone number',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'homepage',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Homepage',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Homepage',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'faxnummer',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Faxnummer',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Fax number',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'zustellungsKontakt',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Zustellungs Kontakt',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Delivery contact',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'statusMessage',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Status Mitteilung',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Status message',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'profilUpdateInformationMessage',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Erneuern Sie ihr {0} und laden Sie die passenden Beweisdokumente hoch',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Please update your {0} and upload the corresponding Document of proof',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'profilUpdateRequest',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Profil Änderungs Anfrage',
					'description' => 'Eine Änderungs Anfrage',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Profil Update Request',
					'description' => 'One profil update request',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'profilUpdateRequests',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Profil Änderungs Anfragen',
					'description' => 'Mehrere Änderungs Anfragen',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Profil Update Requests',
					'description' => 'Multiple update requests',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'statusDate',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Status Datum',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Date of Status',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'userID',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'UserID',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'UserID',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'anfrageThema',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Thema der Anfrage',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Topic of Request',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'anfrageDatum',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Datum der Anfrage',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Date of Request',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'update',
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
					'text' => 'update',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'accept',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Annehmen',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'accept',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'deny',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Ablehnen',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'deny',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'UID',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Benutzer',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'User',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'pendingRequests',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Ausstehende Anfragen',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Pending Requests',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'acceptedRequests',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Angenommene Anfragen',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Accepted Requests',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'rejectedRequests',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Abgelehnte Anfragen',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Rejected Requests',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'allRequests',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Alle Anfragen',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'All Requests',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'deleteContact',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Kontakt löschen',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Delete contact',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'deleteAddress',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Adresse löschen',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Delete address',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'addContact',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Kontakt hinzufügen',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Add contact',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'addContact',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Adresse hinzufügen',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Add address',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
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
					'text' => 'First name',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
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
					'text' => 'Last name',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'title',
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
					'text' => 'Title',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'pending',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Ausstehend',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Pending',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'accepted',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Akzeptiert',
					'description' => 'Profil Änderungen die akzeptiert wurden',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Accepted',
					'description' => 'Profil updates that were accepted',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'rejected',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Abgelehnt',
					'description' => 'Profil Änderungen die abgelehnt wurden',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Rejected',
					'description' => 'Profil updates that were rejected',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'profilUdate_address_error',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Nicht möglich adressenID {0} für profil Änderung {1} hinzuzufügen',
					'description' => 'Fehlermeldung wenn die AdressenID bereits in einer Profil Änderung verwendet wurde',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'was not able to add addressID {0} to profilRequest {1}',
					'description' => 'Error message when the addressID was already used for another profil update',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'profilUpdate_permission_error',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Notwendinge Berechtigungen fehlen',
					'description' => 'Fehlermeldung wenn notwendige Berechtigungen für Aktionen fehlen',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Missing necessary permission',
					'description' => 'Error message if necessary permissions are missing',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'profilUpdate_dms_error',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Das angeforderte Dokument ist kein Anhang einer Profil Änderung',
					'description' => 'Fehlermeldung wenn ein Dokument angefordert wird, das nicht im zusammenhang mit einer Profil Änderung ist',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'The requested document is not an attachment for any profil update',
					'description' => 'Error message if a document is requested that is not connected with a profil update',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'profilUpdate_email_error',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Ein Fehler ist aufgetreten beim sender der Email',
					'description' => 'Fehlermeldung wenn ein Fehler beim senden einer Email auftritt',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'An error occurred when sending an email',
					'description' => 'Error message if an error occurred while sending emails',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'profilUpdate_studentCheck_error',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Ein Fehler ist aufgetreten beim Überprüfen ob die Person ein Student ist',
					'description' => 'Fehlermeldung wenn ein Fehler beim überprüfen eines Studenten auftritt',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'An error occurred while checking if the person is a student',
					'description' => 'Error message if an error occurred when checking if a person is a student',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'profilUpdate_mitarbeiterCheck_error',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Ein Fehler ist aufgetreten, es wurde kein Mitarbeiter mit der gleichen uid gefunden',
					'description' => 'Fehlermeldung wenn ein Fehler beim überprüfen eines Mitarbeiter auftritt',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'An error occurred while checking if the person is a mitarbeiter',
					'description' => 'Error message if an error occurred when checking if a person is a mitarbeiter',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'profilUpdate_loading_error',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Ein Fehler beim laden der Profil Änderung ist aufgetreten',
					'description' => 'Fehlermeldung wenn ein Fehler beim laden einer Profil Änderung auftritt',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'An error occurred while loading the profil update',
					'description' => 'Error message if an error occurred when loading a profil update',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'profilUpdate_dmsVersion_error',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Ein Fehler beim laden der Dms Version ist aufgetreten',
					'description' => 'Fehlermeldung wenn ein Fehler beim laden einer Dms Version auftritt',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'An error occurred while loading the dms version',
					'description' => 'Error message if an error occurred when loading a dms version',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'profilUpdate_deleteZustellung_error',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Ein Fehler ist aufgetretten, man kann keine Zustellung löschen',
					'description' => 'Fehlermeldung wenn versucht wird eine Zustellungsresource zu löschen',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'An error occurred, it is not possible to delete a resource marked as zustellung',
					'description' => 'Error message if someone tried to delete a resource that is marked as zustellung',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'profilUpdate_changeTwice_error',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Ein Fehler ist aufgetretten, man kann die gleiche Profil Information nicht zweimal ändern',
					'description' => 'Fehlermeldung wenn versucht wird eine Information zweimal zu ändern',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'An error occurred, it is not possible to change the same profil information twice',
					'description' => 'Error message if someone tried to change the same profil information twice',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'profilUpdate_changeTopicTwice_error',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Eine Anfrage {0} ist bereits geöffnet worden',
					'description' => 'Wenn die Profil Änderung nicht über Kontakte oder Adressen ist dann darf das Topic nur einmalig verändert werden',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'A request to change {0} is already open',
					'description' => 'if the profil update is not about contacts or addresses then the topic has to be unique',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'profilUpdate_loadingOE_error',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Ein Fehler beim laden der oe_einheit ist aufgetreten',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'An error occurred, when loading the oe_einheit',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'profilUpdate_requiredInformation_error',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Ein Fehler ist aufgetreten, notwendige Informationen fehlen',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'An error occurred, required informations are missing',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'profilUpdate_insert_error',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Ein Fehler ist beim hinzufügen der Profil Änderung aufgetreten',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'An error occurred during the insertion of the profil update',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'profilUpdate_insertKontakt_error',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Ein Fehler ist beim hinzufügen des Kontaktes aufgetreten',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'An error occurred during the insertion of the contact',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'profilUpdate_insertAdresse_error',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Ein Fehler ist beim hinzufügen der Adresse aufgetreten',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'An error occurred during the insertion of the address',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'profilUpdate_updateKontakt_error',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Ein Fehler ist beim ändern eines Kontaktes aufgetreten',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'An error occurred while updating a profil update contact',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'profilUpdate_updateAdresse_error',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Ein Fehler ist beim ändern einer Adresse aufgetreten',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'An error occurred while updating a profil update address',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'profilUpdate_loadingZustellkontakte_error',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Ein Fehler ist während dem laden der Zustellkontakte aufgetreten',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'An error occurred when querying zustellkontakte',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'profilUpdate_loadingZustellAdressen_error',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Ein Fehler ist während dem laden der Zustelladressen aufgetreten',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'An error occurred when querying zustelladressen',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'acceptUpdate',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Änderung annehmen',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Accept Request',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'denyUpdate',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Änderung ablehnen',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Deny Request',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'showRequest',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Änderung anzeigen',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Show request',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'profilUpdate_status_error',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Ein Fehler ist aufgetreten, der Status "{0}" ist unbekannt',
					'description' => 'Fehler der auftritt wenn es den Status nicht in der Datenbank gibt',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'An error occurred, the status "{0}" is not known',
					'description' => 'error that occurrs when the used status is not present in the database',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'profilUpdate_topic_error',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Ein Fehler ist aufgetreten, das Topic "{0}" existiert nicht',
					'description' => 'Fehler der auftritt wenn es das Topic nicht in der Datenbank gibt',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'An error occurred, the topic "{0}" is not known',
					'description' => 'error that occurrs when the used topic is not present in the database',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'profilUpdate_address_error',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Ein Fehler ist aufgetreten, Es war nicht möglich die Adresse mit ID {0} in der Datenbank anzulegen',
					'description' => 'Fehler der auftritt wenn es nicht möglich war eine neue Adresse anzulegen',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'An error occurred, it wasn\'t possible to add new address with ID {0} to the database',
					'description' => 'error that occurrs when it wasn\'t possible to add new address in db',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'profilUpdate_kontakt_error',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Ein Fehler ist aufgetreten, Es war nicht möglich den Kontakt mit ID {0} in der Datenbank anzulegen',
					'description' => 'Fehler der auftritt wenn es nicht möglich war einen neuen Kontakt anzulegen',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'An error occurred, it wasn\'t possible to add new contact with ID {0} to the database',
					'description' => 'error that occurrs when it wasn\'t possible to add new contact in db',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'Name',
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
					'text' => 'Name',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'Topic',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Thema',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Topic',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'insertamum',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Einfüge Datum',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Insert Date',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'actions',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Aktionen',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Actions',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
		array(
			'app' => 'core',
			'category' => 'profilUpdate',
			'phrase' => 'Status',
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

		//ProfilUpdate Phrasen ende
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'geloescht',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Gelöscht',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Deleted',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'aenderungGespeichert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Änderung gespeichert',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Saved changes',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'fhctemplate',
		'category' => 'global',
		'phrase' => 'datensatz',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Datensatz',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Dataset',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'fhctemplate',
		'category' => 'global',
		'phrase' => 'datensatzGenehmigen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Datensatz genehmigen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Approve dataset',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'fhctemplate',
		'category' => 'global',
		'phrase' => 'datensatzAblehnen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Datensatz ablehnen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Reject dataset',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'fhctemplate',
		'category' => 'global',
		'phrase' => 'datensatzAnlegen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Datensatz anlegen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Add dataset',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'fhctemplate',
		'category' => 'global',
		'phrase' => 'datensatzBearbeiten',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Datensatz bearbeiten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Edit dataset',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'alleGenehmigt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Alle genehmigt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'All accepted',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'alleAbgelehnt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Alle abgelehnt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'All rejected',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'dokument',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Dokument',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Document',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'aktionen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Aktionen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Actions',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	//
	// DIGITALE ANWESENHEITEN PHRASEN BEGIN
	//
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'anwesend',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'anwesend',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'attending',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'abwesend',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'abwesend',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'absent',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'entschuldigt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'entschuldigt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'excused',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'entschuldigungAbgelehnt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Entschuldigung abgelehnt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'excuse note declined',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'abgelehnt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Abgelehnt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'declined',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'entschuldigungAkzeptiert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Entschuldigung akzeptiert',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'excuse note accepted',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'akzeptiert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Akzeptiert',
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
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'entschuldigungOffen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Entschuldigung offen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'excuse note status open',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'hochgeladen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Hochgeladen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'uploaded',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'anwesenheiten',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anwesenheiten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Attendances',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'entschuldigungen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Entschuldigungen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Excuse Notes',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'codeEingeben',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Code eingeben',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Enter Code',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'codeSenden',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Code senden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Send Code',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'anwesenheitenverwaltung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anwesenheitenverwaltung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Attendance Management',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'bitteZugangscodeEingeben',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bitte Zugangscode eingeben.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Please enter an access code.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'code',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zugangscode',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Access code',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'eintragErfolgreich',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Eintrag erfolgreich!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Entry successful!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'wurdeRegistriert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'wurde registriert',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'has been registered.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'neueAnwKontrolle',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Neue Anwesenheitskontrolle starten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Start a new attendance check',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'endAnwKontrolle',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anwesenheitskontrolle beenden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'end attendance check',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'entschuldigungsmanagement',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Entschuldigungsmanagement',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Excuse note management',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'digitalesAnwManagement',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Digitales Anwesenheitsmanagement',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Digital attendance management',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'entschuldigungAutoEmailBetreff',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Entschuldigung zur Befreiung der Anwesenheitspflicht: Neues Dokument wurde hochgeladen.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Excuse note for digital attendances - a new document has been uploaded.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'entschuldigungStatusUpdateAutoEmailBetreff',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Entschuldigung Status Update.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Excuse note status update.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'entschuldigungAkzeptieren',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Entschuldigung akzeptieren',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Accept excuse note',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'entschuldigungAblehnen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Entschuldigung ablehnen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Decline excuse note',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'entschuldigungLöschen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Entschuldigung löschen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Delete excuse note',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'entschuldigungHochladen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Entschuldigung hochladen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Upload excuse note',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'entschuldigungLöschenErfolg',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Entschuldigung erfolgreich gelöscht!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Successfully deleted excuse note',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'foto',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Foto',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Picture',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'summe',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Summe',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Sum',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'errorStartAnwKontrolle',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehler beim dem Versuch eine neue Anwesenheitskontrolle zu starten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error when trying to start a new attendance check',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'errorAnwStartAndEndSet',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Beginn und Ende der Anwesenheitskontrolle müssen gesetzt sein!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The start and end of the attendance check must be set!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'anwKontrolleBeendet',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anwesenheitskontrolle erfolgreich beendet',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Attendance check successfully finished',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'errorDeleteQRCode',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehler bei dem Versuch den QR Code zu löschen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Something went wrong with deleting the QR Code',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'deleteAnwKontrolleConfirmation',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anwesenheitskontrolle erfolgreich gelöscht',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Attendance check successfully deleted',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'deleteAnwKontrolle',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anwesenheitskontrolle löschen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Delete attendance check',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'noAnwKontrolleFoundToDelete',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Keine Anwesenheitskontrolle gefunden zu löschen!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No attendance check found to delete!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'errorDeletingAnwKontrolle',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehler beim Löschen der Anwesenheitskontrolle!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error deleting an attendance check!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'errorValidateTimes',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Das Enddatum muss nach dem Startdatum liegen.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The end date must be after the start date.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'zugangscode',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zugangscode',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Access code',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'anwUserDeleteSuccess',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anwesenheitseinträge erfolgreich gelöscht.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Attendances deleted successfully',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'errorAnwUserDelete',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehler bei dem Versuch Anwesenheitseinträge zu löschen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error deleting Attendances',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'anwUserUpdateSuccess',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anwesenheitseinträge erfolgreich aktualisiert.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Attendances updated successfully.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'errorAnwUserUpdate',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehler bei dem Versuch Anwesenheitseinträge zu aktualisieren.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error updating Attendances',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'errorLoadingAnwesenheiten',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehler bei dem Versuch Anwesenheitsdaten zu laden.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error loading attendancy data.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'warningEnterVonZeit',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bitte von Zeit eingeben',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Please enter a from time',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'warningEnterBisZeit',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bitte bis Zeit eingeben',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Please enter a by time',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'warningChooseFile',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bitte Datei auswählen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Please choose a file',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'entschuldigungUploaded',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Entschuldigung hochgeladen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Excuse note uploaded',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'errorEntschuldigungUpload',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehler bei dem Versuch eine Entschuldigung hochzuladen!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error uploading an excuse note!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'addEntschuldigung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Entschuldigung hinzufügen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Add excuse note',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'noDataAvailable',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Keine Daten verfügbar',
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
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'noExistingKontrolleFound',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Keine Anwesenheitskontrolle gefunden.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No existing attendance check found.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'errorDegeneratingQRCode',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehler beim degenerieren des QRCode',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error degenerating QRCode',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'errorSavingNewQRCode',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehler beim anlegen eines neuen QRCode',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error saving a new QRCode',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'errorDeleteKontrolleKeineLEAnDatum',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Keine Anwesenheitskontrolle gefunden für LV-Teil {le_id} am {day}.{month}.{year}.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No attendance check found for teaching unit {le_id} on {day}.{month}.{year}.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'errorDeleteUserAnwEntriesAnDatum',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehler beim Löschen der User Anwesenheiten für LV-Teil {le_id} am {day}.{month}.{year}.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error deleting User Anwesenheiten for teaching unit {le_id} on {day}.{month}.{year}.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'errorDeleteKontrolleEntryAnDatum',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehler beim Löschen der Anwesenheitskontrolle für LV-Teil {le_id} am {day}.{month}.{year}.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error deleting attendance check for teaching unit {le_id} on {day}.{month}.{year}.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'successDeleteKontrolleEntryAnDatum',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Löschen der Anwesenheitskontrolle für LV-Teil {le_id} am {day}.{month}.{year} erfolgreich.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Success deleting attendance check for teaching unit {le_id} on {day}.{month}.{year}.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'wrongParameters',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Falsche Parameterübergabe',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Wrong parameters tranferred',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'missingParameters',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Unvollständige Parameterübergabe',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Parameters are missing',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'errorInvalidCode',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ungültiger Zugangscode eingegeben.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Wrong access code entered.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'errorCodeLinkedToInvalidKontrolle',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zugangscode hat eine fehlerhafte Anwesenheitskontrolle hinterlegt.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Access code is linked to an invalid attendance check.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'errorCodeLinkedToInvalidLE',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zugangscode hat einen fehlerhaften LV-Teil hinterlegt.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Access code is linked to an invalid teaching unit.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'errorLEhasNoStudentsAttending',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'LV-Teil des Zugangscodes hat keine Teilnehmer hinterlegt.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Teaching unit linked to access code has no participants.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'errorNotParticipantOfLE',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Sie sind nicht als Teilnehmer des LV-Teil eingetragen.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'You are not registered as a participant in the teaching unit.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'errorNoUserEntriesForAttendanceCheckFound',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Keine Anwesenheitseinträge gefunden für den LV-Teil an diesem Datum.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No attendance entries found for the teaching unit on this date.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'errorUpdateUserEntry',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Eintrag der Anwesenheit fehlgeschlagen, bitte wenden Sie sich an den Unterrichtenden.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Attendance entry failed, please contact the instructor.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'errorPersonStudentIDMismatch',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehlerhafte Verbindung von Person und Student ID',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Person und Student ID Mismatch.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'successDeleteEnschuldigung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Entschuldigung erfolgreich gelöscht.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Successfully deleted excuse note.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'errorCalculatingAnwQuota',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehler bei der Berechnung der Anwesenheitenquote.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error calculating attendancy quota.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'errorDeleteSingleAnwUserEntry',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehler bei dem Versuch den Anwesenheitseintrag zu löschen.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error trying to delete the attendancy entry.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'errorDeleteMultipleAnwUserEntry',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehler bei dem Versuch die Anwesenheitseinträge zu löschen.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error trying to delete the attendancy entries.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'errorNoSTGassigned',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Keine Studiengänge zugewiesen.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No study programs assigned.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'anteilAnw',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anteil',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Percentage',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'errorUpdateEntschuldigung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehler bei dem Versuch Entschuldigungsstatus zu verändern.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error when trying to change excuse note status.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'successUpdateEntschuldigung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Entschuldigungsstatus erfolgreich aktualisiert.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Excuse note status successfully updated.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'lehreinheitConfig',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'LV-Teil auswählen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Configurate teaching unit',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'keineAnwkontrolleMöglichWeilLEFehlt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Keine Anwesenheitskontrolle möglich weil ihnen im System keine LV-Teile für diese Lehrveranstaltung zugewiesen sind.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No attendance check possible because no teaching units are assigned to you in the system for this course.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'anwByLva',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anwesenheiten für {lva}',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Attendancies for {lva}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'anwByStg',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anwesenheiten für {stg}',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Attendancies for {stg}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'anwByLe',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anwesenheiten für {le}',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Attendancies for {le}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'leLaden',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'LV-Teil laden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Load teaching unit',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'errorCodeSentInTimeOutsideKontrolle',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Der Zugangscode wurde außerhalb der Kontrollzeiten gesendet.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The access code has been sent outside the time of the attendance check.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'errorNoRightsToChangeData',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehlende Berechtigung um den Datenstand zu verändern.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Missing authorization rights to change data.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'errorCodeTooOld',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Der Zugangscode ist zeitlich abgelaufen.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The access code has expired.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'studentConfig',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Studenten auswählen.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Configure student profile.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'studentLaden',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Student laden.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Load Student.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'students',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Studenten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Students',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'termineLautStundenplan',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Unterricht laut Stundenplan',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Lessons as per Timetable',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'unterrichtDauer',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Unterricht Dauer',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Lesson Duration',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'entschuldigungNotizAblehnen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Entschuldigung Ablehnen Begründung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Excuse note rejection reason',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'reject',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ablehnen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Reject',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'begruendungAnw',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Begründung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Reason',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'noStudentsFound',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Keine zugeteilten Studenten gefunden für den gewählten LV-Teil gefunden!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No assigned students for the selected teaching unit found!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'kontrolldatum',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kontrolldatum',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Attendance Check Date',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'showAllKontrollen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Alle Kontrollen anzeigen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Show All Attendance Checks',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'deletableKontrollen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Löschbare Anwesenheitskontrollen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Deletable Attendance Checks',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'anwInfoKeineKontrollenGefunden',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Keine Kontrollen zum LV-Teil gefunden!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No digital attendance checks found for teaching unit!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'digiAnw',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Digitale Anwesenheiten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Digital Attendances',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'anwNotizUpdated',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Notiz wurde erfolgreich bearbeitet.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Note has been edited sucessfully',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'errorInvalidFiletype',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ungültiges Dateiformat!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Invalid Filetype!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'anwCountTermin',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anwesend am gewählten Termin',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Attending at date',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'minuten',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Minuten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'minutes',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'einheiten',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Einheiten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'units',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'prestudentID',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Prestudent ID',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Prestudent ID',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'allowedEntschuldigungFileTypes',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Erlaubte Dateitypen sind .pdf, .png und .jpg.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Allowed file types are .pdf, .png und .jpg.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'notAuthorizedForLva',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Keine Berechtigung für die Lehrveranstaltung.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No Authorization for that Course.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'notAuthorizedForLe',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Keine Berechtigung für die Lehreinheit.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No Authorization for that Teaching Unit.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'noAuthorization',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Keine Berechtigung.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No Authorization.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'kontrollDauerUnterMindestwert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kontrolle muss für eine Unterrichtsdauer von mindestens {0} Minuten gelten.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Attendance check must apply for a lesson duration of at least {0} minutes.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'zeitNichtAusStundenplanBeginn',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Unterrichtbeginn entspricht keinem Unterrichtstermin laut Stundenplan.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Lecture Begin Time does not match a lesson as per timetable.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'zeitNichtAusStundenplanEnde',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Unterrichtende entspricht keinem Unterrichtstermin laut Stundenplan.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Lecture End Time does not match a lesson as per timetable.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'datumNichtAusStundenplan',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Datum entspricht keinem Stundenplan Termin.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Date does not match a lesson as per timetable.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'tooltipStudentEntschuldigung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Sie können eine Entschuldigung für einen Zeitraum von bis zu {0} Tagen in die Vergangenheit und bis zum Ende des aktuellen Semesters hochladen. Die erlaubten Dateitypen für das Dokument sind .pdf, .png und .jpg.

				Sobald Sie eine Entschuldigung hochladen wird die zugehörige Studiengangsassistenz informiert und Ihr Anliegen überprüft. Solange Ihre Entschuldigung noch keinen akzeptierten oder abgelehnten Status erhalten hat, steht es Ihnen frei diese inklusive Datei zu löschen. Sobald sie entweder akzeptiert oder abgelehnt wurde können Sie den Eintrag nichtmehr löschen.

				Bei einer akzeptierten Entschuldigung werden sämtliche digitalen Anwesenheiten in diesem Zeitraum als positiv gewertet. Eine abgelehnte Entschuldigung hat keine Auswirkungen auf ihre Anwesenheitsquote.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'You can upload an excuse for a period of up to {0} days in the past and up to the end of the current semester. The permitted file types for the document are .pdf, .png and .jpg.

				As soon as you upload an excuse, the relevant study program assistant will be informed and your request will be reviewed. As long as your excuse has not yet received an accepted or rejected status, you are free to delete this file including it. Once it has either been accepted or rejected, you can no longer delete the entry.

				If an excuse is accepted, all digital attendance during this period is counted as positive. A rejected excuse has no effect on your attendance rate.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'tooltipStudentAnwesenheit',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Hier sehen Sie sämtliche digitale Anwesenheiten zugeordnet nach Lehrveranstaltung. Sie können eine positive Anwesenheit erreichen, indem Sie den während einer laufenden Anwesenheitskontrolle gültigen Zugangscode eintragen. Sie können hierfür den angezeigten QR Code scannen, welcher Sie entsprechend weiterleitet oder Sie können den Code manuell eingeben.

				Sollte es Ihnen technisch nicht möglich sein einen Zugangscode einzugeben, können Sie die unterrichtende Person bitten Ihre digitale Anwesenheit zu setzen.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Here you can see all digital attendances assigned to the course. You can achieve positive attendance by entering the access code that is valid during an ongoing attendance check. You can do this by scanning the QR code displayed, which will redirect you accordingly, or you can enter the code manually.

				If it is not technically possible for you to enter an access code, you can ask the person teaching you to set your digital attendance.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'tooltipAssistenz',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Im Entschuldigungsmanagement können Sie als Studiengangsassistenz beziehungsweise als Administrator die von Studenten hochgeladenen Entschuldigungsdokumente überprüfen und den Status entsprechend vergeben.

				Bitte beachten Sie dass nur Entschuldigungen INNERHALB des angegebenen Zeitraumes angezeigt werden. Sollten Sie nach einer lang wirken Entschuldigung suchen, müssen Sie die Zeitspanne entsprechend weit setzen.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'In the excuse management, you as a course assistant or administrator can check the excuse documents uploaded by students and assign the status accordingly.

				Please note that only excuses WITHIN the specified time period are displayed. If you are looking for a long-lasting excuse, you must set the time period accordingly.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'tooltipLektorDeleteKontrolle',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Sollten Sie eine Anwesenheitskontrolle fälschlicherweise gestartet haben, können Sie diese löschen wenn sie nicht älter als {0} Tage ist. Dabei werden sämtliche mit dieser Kontrolle verknüpfte Anwesenheitseinträge Ihrer Studenten ebenfalls gelöscht und Ihre Anwesenheitsquoten neu berechnet.

				Sollten Sie eine Kontrolle, welche älter als {0} Tage ist, löschen wollen, wenden Sie sich an einen Administrator.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'If you have started an attendance check by mistake, you can delete it if it is not older than {0} days. All attendance entries of your students linked to this check will also be deleted and your attendance rates will be recalculated.

				If you want to delete a check that is older than {0} days, contact an administrator.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'tooltipLektorStartKontrolle',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Um eine Anwesenheitskontrolle für Ihre ausgewählte Unterrichtsgruppe durchzuführen, wählen Sie bitte einen Termin aus dem Stundenplan aus oder geben händisch die gewünschte Gültigkeitkeitsdauer der Kontrolle an.

				Die Gültigkeitsdauer bestimmt die Gewichtung der Anwesenheit in Relation zum Gesamtausmaß, sie können diese aber nach eigenem Ermessen anpassen und müssen sich nicht streng an die Termine im Stundenplan halten.

				Sie können pro Datum und Unterrichtsgruppe eine Anwesenheitskontrolle pro Tag eröffnen, welche jedoch beliebig oft aufgerufen und von Studenten eingecheckt werden kann. Es gelten dabei ihre zuletzt eingetragenen Zeiten. Ein Student muss nur einmal am Tag pro Gruppe einchecken um als anwesend registriert zu sein, egal wie oft Sie die Kontrolle starten.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'To carry out an attendance check for your selected class group, please select a date from the timetable or manually enter the desired validity period of the check.

				The validity period determines the weighting of attendance in relation to the overall extent, but you can adjust this at your own discretion and do not have to stick strictly to the dates in the timetable.

				You can open one attendance check per day for each date and class group, which can be called up and checked in by students as often as you like. The times they last entered apply. A student only has to check in once a day per group to be registered as present, regardless of how often you start the check.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'tooltipStudentByLva',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'In dieser Detailansicht können Sie einzelne Anwesenheiten eines Studenten bearbeiten, falls ein anwesender Student aus technischen Gründen den Zugangscode nicht eingeben kann. Ebenso steht es Ihnen frei Studenten auszutragen, welche nicht anwesend sind aber den Zugangscode mit Hilfe von anwesenden Studenten erhalten haben.

				Falls eine Anwesenheit durch eine akzeptierte Entschuldigung entstanden ist, können Sie den Status nicht verändern.

				Es steht Ihnen frei die Anwesenheitseinträge mit Notiztexten zu versehen, welche dem Studenten nicht zugänglich sind.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'In this detailed view, you can edit individual attendances of a student if a student who is present cannot enter the access code for technical reasons. You are also free to remove students who are not present but have received the access code with the help of students who are present.

				If an attendance was made due to an accepted excuse, you cannot change the status.

				You are free to add notes to the attendance entries that are not accessible to the student.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'tooltipStudentTestphase',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Hinweis: Es handelt sich um einen nicht repräsentativen Testbetrieb und die Ergebnisse werden nicht für die Benotung herangezogen.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Note: This is a non-representative test operation and the results are not used for grading.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'digiAnwEval',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Digitale Anwesenheiten Auswertung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Digital Attendances Evaluation',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'anwTimeline',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Digitale Anwesenheiten Timeline',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Digital Attendances Timeline',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'entschuldigungEdit',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Entschuldigung bearbeiten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Excuse Note Edit',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'excuseUploadNoFile',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Dokument wird nachgereicht',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Document will be submitted later',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'entschuldigungEditieren',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Entschuldigung bearbeiten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Edit Excuse Note',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'missingEntschuldigungFile',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehlendes Dokument!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Missing Document!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'anwKontrolleVon',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Unterricht von',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Lesson from',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'anwKontrolleBis',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Unterricht bis',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Lesson until',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'termineAusStundenplan',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Unterricht Termine laut Stundenplan',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Lesson dates according to timetable',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'tooltipUnterrichtZeitCustom',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Unterrichtszeiten werden aus dem Stundenplan geladen, überschreiben Sie diese nur falls Ihre tatsächliche Unterrichtszeiten davon abweichen!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Lesson times are loaded from the timetable, only overwrite them if your actual lesson times differ!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'tooltipUnterrichtDatumCustom',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Unterrichtsdaten werden aus dem Stundenplan geladen, überschreiben Sie dieses nur falls Ihr tatsächliches Unterrichtsdatum abweicht!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Class dates are loaded from the timetable, only overwrite this if your actual class date differs!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'unterrichtzeit',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Unterrichtszeiten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Lesson Times',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'statusLegende',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Status Legende',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Status Legend',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'download',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Herunterladen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Download',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'upload',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Hochladen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Upload',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'anwesenheiten',
		'category' => 'global',
		'phrase' => 'providedDateTooOld',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Angegebenes Datum ist älter als erlaubt.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Provided date is older than allowed date',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	//
	// DIGITALE ANWESENHEITEN PHRASEN END
	//
	// BOOKMARK PHRASEN ----------------------------------------------------------------------
	array(
		'app' => 'core',
		'category' => 'bookmark',
		'phrase' => 'newLink',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Neuer Link',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'New Link',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'bookmark',
		'phrase' => 'saveLink',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Link speichern',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Save link',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'bookmark',
		'phrase' => 'editLink',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Link bearbeiten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Edit link',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'bookmark',
		'phrase' => 'bookmarkDeleted',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Link gelöscht',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Link deleted',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'bookmark',
		'phrase' => 'bookmarkAdded',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Link hinzugefügt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Link added',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	)
	,array(
		'app' => 'core',
		'category' => 'bookmark',
		'phrase' => 'bookmarkAdded',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Link hinzugefügt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Link added',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	)
	,array(
		'app' => 'core',
		'category' => 'bookmark',
		'phrase' => 'bookmarkUpdated',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Link geändert',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Link updated',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'bookmark',
		'phrase' => 'myBookmarks',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Meine Urls',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'My Urls',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'bookmark',
		'phrase' => 'emptyBookmarks',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Du hast noch keine Bookmarks gesetzt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'You have not set any bookmarks yet',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'bookmark',
		'phrase' => 'invalidUrl',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ungültiger Link',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Invalid link',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'bookmark',
		'phrase' => 'invalidTitel',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ungültiger Titel',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Invalid title',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
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
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'softwareUndLizenzManagement',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Software- und Lizenzmanagement',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Software- and License Management',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'raumzuordnung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Raumzuordnung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Location Assignment',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'raum',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Raum',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Location',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'statusSetzen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Status setzen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Set status',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'hierarchieAnsicht',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Hierarchie-Ansicht',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Hierarchy view',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'aufgeklappt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'aufgeklappt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'unfolded',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'zugeklappt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'zugeklappt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'folded',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'lizenzserver',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Lizenzserver',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'License server',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'softwareZuordnung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Software-Zuordnung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Software assignment',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'zuordnungUeberSoftware',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zuordnung über Software',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Assignment via Software',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'zuordnungUeberImage',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zuordnung über Image',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Assignment via Image',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'verfuegbarkeitBearbeiten',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Verfügbarkeit bearbeiten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Edit availability',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'softwareAnlegen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Software anlegen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Add software',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'softwareAuswaehlen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Software auswählen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Select software',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'softwareBearbeiten',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Software bearbeiten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Edit software',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'softwareimageUndZugeordneteRaeumeKopieren',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Softwareimage und zugeordnete Räume kopieren',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Copy software image and assigned rooms',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'softwareimageAnlegen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Softwareimage anlegen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Add Softwareimage',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'imageAuswaehlen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Image auswählen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Select image',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'softwareimageBearbeiten',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Softwareimage bearbeiten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Edit Softwareimage',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'raumZuImageAnlegen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Raum zu Image anlegen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Add location to image',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'raumZuImageBearbeiten',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Raum zu Image bearbeiten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Edit location assigned to image',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'lizenzserverAnlegen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Lizenzserver anlegen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Add license server',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'lizenzserverBearbeiten',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Lizenzserver bearbeiten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Edit license server',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
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
				'text' => 'Name',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'betriebssystem',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Betriebssystem',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Operating System',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'verfuegbarkeitStart',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Verfügbarkeit Start',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Availability start',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'verfuegbarkeitEnde',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Verfügbarkeit Ende',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Availability end',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'softwaretyp',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Softwaretyp',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Softwaretype',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'softwaretypKurzbz',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Softwaretyp Kurzbz',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Softwaretype short',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'softwareKurzbz',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Software Kurzbz',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Software',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'hersteller',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Hersteller',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Producer',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'verantwortliche',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Verantwortliche',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Responsible',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'uebergeordneteSoftware',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Übergeordnete Software',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Parent Software',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'ansprechpartner',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ansprechpartner',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Contact person',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'ansprechpartnerIntern',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ansprechpartner intern',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Contact person internal',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'ansprechpartnerExtern',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ansprechpartner extern',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Contact person external',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'anmerkungIntern',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anmerkung intern',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Internal note',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'zugeordneteImages',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zugeordnete Images',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Assigned images',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'lizenzart',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Lizenz-Art',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'License type',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'lizenzserverKurzbz',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Lizenzserver-Kurzbz',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Licenseserver',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'lizenzserverPort',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Lizenzserver Port',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'License server port',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'raumSwZuordnung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Raum-/SW-Zuordnung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Location-/SW Assignment',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'lizenzAnzahl',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Lizenz-Anzahl',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'License number',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'lizenzLaufzeit',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Lizenz-Laufzeit',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'License term',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'kostentraegerOe',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kostenträger-OE',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Cost unit',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'lizenzKosten',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Lizenz-Kosten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'License costs',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'euroProJahr',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '€/Jahr',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '€/year',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'alleWaehlen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Alle wählen',
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
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'softwareverwaltung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Softwareverwaltung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Software Management',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'imageverwaltung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Imageverwaltung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Image Management',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'lizenzserververwaltung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Lizenzserververwaltung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Licenseserver Management',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'sucheNachRaum',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Suche nach Raum',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Search by location',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'existiertBereits',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Existiert bereits',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Already exists',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'datumEndeVorDatumStart',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Datumende vor Datumstart',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Enddate after startdate',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'ui',
		'phrase' => 'existiertBereitsInKombinationMitDerVersion',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Existiert bereits in Kombination mit der Version',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Already exists in combination with the Version',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'ui',
		'phrase' => 'nichtGleichzeitigUntergeordnetUndUebergeordnet',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Software kann einer anderen Software nicht gleichzeitig untergeordnet und übergeordnet sein',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Software cannot be both subordinate and superior to other software at the same time',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'imageverwaltungImageCopySuccessText',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Falls vorhanden, wurden zugeordnete Räume mitkopiert. Nicht jedoch zugeordnete Software. Diese muss dem neuen Image über die Softwareverwaltungstabelle zugeordnet werden.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'If available, assigned rooms were also copied. However, not associated software. This must be assigned to the new image via the software management table.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'loeschenNichtMoeglichSoftwareBereitsZugeordnet',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Löschen nicht möglich, da bereits Software zugeordnet wurde.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Deletion not possible because software has already been assigned.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
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
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'statusErfolgreichUebertragen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Status erfolgreich übertragen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Status successfully transferred',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'statusUebertragenMsg',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Der Status {status} wurde erfolgreich von der übergeordneten Software mit ID {parentSoftware} auch auf die untergeordnete Software übertragen.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Status {status} has been successfully transferred from the parent software with ID {parentSoftware} to the child software.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'softwareliste',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Softwareliste',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Softwarelist',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'raumverfuegbarkeit',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Raumverfügbarkeit',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Room Availability',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'firma_zusatz',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Firmenzusatz',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'add.company info',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'firma',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Firma',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'company',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'adresse_new',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Adresse anlegen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'create address',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'adresse_edit',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Adresse bearbeiten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'edit address',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'adresse_delete',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Adresse löschen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'delete address',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'adresse_confirm_delete',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Adresse wirklich löschen?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Really delete address?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'kontakt_new',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kontakt anlegen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'create contact data',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'kontakt_edit',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kontakt bearbeiten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'edit contact data',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'kontakt_delete',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kontakt löschen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'delete contact data',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'kontakt_confirm_delete',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kontakt wirklich löschen?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Really delete contact data?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'privatkonto',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Privatkonto',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Private account',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'firmenkonto',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Firmenkonto',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Company Account',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'bankvb_new',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bankverbindung anlegen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'create bank details',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'bankvb_edit',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bankverbindung bearbeiten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'edit bank details',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'bankvb_delete',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bankverbindung löschen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'delete bank details',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'bankvb_confirm_delete',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bankverbindung wirklich löschen?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Really delete bank details?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	//Status
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'status_rolle',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Rolle',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'role',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'status_new',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Neuen Status hinzufügen ({vorname} {nachname})',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'add new status ({vorname} {nachname})',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'status_edit',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Status bearbeiten ({vorname} {nachname})',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'edit status ({vorname} {nachname})',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'status_delete',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Status löschen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'delete status',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'bestaetigt_am',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bestätigt am',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'confirmed on',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'bestaetigt_von',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bestätigt von',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'confirmed by',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'insert_am',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'InsertAmUm',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'inserted on',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'insert_von',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'InsertVon',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'inserted by',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'bewerbung_abgeschickt_am',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bewerbung abgeschickt am',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'application sent on',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'aufnahmestufe',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Aufnahmestufe',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'entrance level',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'bismeldestichtag',
		'phrase' => 'meldestichtag_erreicht',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Meldestichtag erreicht - Bearbeiten nicht mehr möglich',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'report target date reached - editing no longer possible',
				'description' => '',
				'insertvon' => 'system'
			)
		)
		),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'status_confirm_delete',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Prestudentstatus wirklich löschen?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Really delete prestudent status?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
			'app' => 'core',
			'category' => 'lehre',
			'phrase' => 'last_status_confirm_delete',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Das Löschen der letzten Rolle löscht auch den gesamten Prestudent-Datensatz! Möchten Sie fortfahren?',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'Deleting the last role also deletes the entire Prestudent record! Do you want to continue?',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'modal_askAusbildungssem',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'In welches Ausbildungssemester soll dieser {status} verschoben werden?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'To which education semester should this {status} be moved?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'modal_askAusbildungssemPlural',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'In welches Semester sollen diese {count} PrestudentInnen ({status})verschoben werden?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'To which education semester should these {count} prestudents ({status}) be moved?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	//Prestudent
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'title_zgv',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zugangsvoraussetzungen für ',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Access requirements for ',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'zgvMaster',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'ZGV Master',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'ZGV master',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'zgvMasterOrt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'ZGV Master Ort',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'ZGV master place',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'zgvMasterDatum',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'ZGV Master Datum',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'ZGV master date',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'zgvMasterNation',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'ZGV Master Nation',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'ZGV master nation',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'zgvMasterErfuellt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'ZGV Master erfüllt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'ZGV master fulfilled',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'zgvDoktor',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'ZGV Doktor',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'ZGV doctor',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'zgvDoktorOrt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'ZGV Doktor Ort',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'ZGV doctor place',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'zgvDoktorDatum',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'ZGV Doktor Datum',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'ZGV doctor date',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'zgvDoktorNation',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'ZGV Doktor Nation',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'ZGV doctor nation',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'zgvDoktorErfuellt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'ZGV Doktor erfüllt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'ZGV doctor fulfilled',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'aufmerksamDurch',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Aufmerksam durch',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'been brought to attention by',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'berufstaetigkeit',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Berufstätigkeit',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'professional activity',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'facheinschlaegigBerufstaetig',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Facheinschlägig berufstätig',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'employed in a relevant field',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'bisstandort',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bisstandort',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'bis location',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'studientyp',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Studientyp',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'study type',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'dual',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Duales Studium',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'dual study',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'foerderrelevant',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'förderrelevant',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'relevant for funding',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'prioritaet',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Priorität',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'priority',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'title_history',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Gesamthistorie',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Overall history',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'error_keineSchreibrechte',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Sie haben keine Schreibrechte für diesen Studiengang!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'You do not have writing rights for this course!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
			'app' => 'core',
			'category' => 'lehre',
			'phrase' => 'error_rolleBereitsVorhanden',
			'insertvon' => 'system',
			'phrases' => array(
				array(
					'sprache' => 'German',
					'text' => 'Diese Rolle ist bereits vorhanden!',
					'description' => '',
					'insertvon' => 'system'
				),
				array(
					'sprache' => 'English',
					'text' => 'This role already exists!',
					'description' => '',
					'insertvon' => 'system'
				)
			)
		),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'error_rolleBereitsVorhandenMitNamen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '{name}: Diese Rolle ist bereits vorhanden!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '{name}: This role already exists!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'error_personBereitsStudent',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '{name}: Diese Person ist bereits Student!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '{name}: This person already is student!',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'error_keinReihungstestverfahren',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '{name}: Um einen Interessenten zum Bewerber zu machen, muss die Person das Reihungstestverfahren abgeschlossen haben!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '{name}: In order to turn an interested party into an applicant the person must have completed the ranking test process!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'error_ZGVNichtEingetragen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '{name}: Um einen Interessenten zum Bewerber zu machen, muss die Zugangsvoraussetzung eingetragen sein!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '{name}: In order to turn an interested party into an applicant, the entry requirements must be entered!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'error_ZGVMasterNichtEingetragen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '{name}: Um einen Interessenten zum Bewerber zu machen, muss die Zugangsvoraussetzung Master eingetragen sein!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '{name}: In order to turn an interested party into an applicant, the entry requirements for master studies must be entered!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'error_keinBewerber',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '{name} muss zuerst zum Bewerber gemacht werden!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '{name} must be made an applicant first!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'error_keinAufgenommener',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '{name} muss zuerst Aufgenommener sein, um zum Studenten gemacht werden zu können!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '{name} must first be admitted in order to be made a student!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'error_lastBewerberAndAufgenommenerSemesters',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Das Studiensemester oder Ausbildungsemester des Berwerberstatus und des Aufgenommenenstatus passen nicht überein',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The study semester or training semester of the applicant status and the accepted status do not match',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'error_noStudstatus',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ein Studentenstatus kann hier nur hinzugefuegt werden wenn die Person bereits Student ist. Um einen Bewerber zum Studenten zu machen waehlen Sie bitte unter "Status aendern" den Punkt "Student"',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Student status can only be added here if the person is already a student. To turn an applicant into a student, please select "Student" under "Change status".',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'error_dataVorMeldestichtag',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Studentstatus mit Datum oder Semesterende vor erreichtem Meldestichtag kann nicht hinzugefügt werden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Student status with a date or end of semester before the reporting deadline cannot be added',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'error_duringInsertUpdateLehrverband',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehler beim Ermitteln bzw Aktualisieren des Lehrverbands: es wurden keine Änderungen in der Datenbank gespeichert',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error during insert or update of Lehrverband: no changes were saved in the database',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'error_duringDeleteLehrverband',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehler beim Löschen des Lehrverbands: es wurden keine Änderungen in der Datenbank gespeichert',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error during deletion of Lehrverband: no changes were saved in the database',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'error_onlyAdminDeleteRolleStudent',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Studentenrolle kann nur durch den Administrator geloescht werden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error during insert or update of Lehrverband: no changes were saved in the database',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'error_onlyAdminDeleteLastStatus',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Die letzte Rolle kann nur durch den Administrator geloescht werden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The last role can only be deleted by the administrator',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'error_noStatusFound',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehler: Status nicht gefunden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error: Status not found',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'error_statusConfirmedYet',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Der Status ist bereits bestätigt!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The status is already confirmed!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'error_bewerbungNochNichtAbgeschickt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Die Bewerbung wurde noch nicht abgeschickt und kann deshalb nicht bestaetigt werden!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The application has not yet been sent and therefore cannot be confirmed!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'error_missingId',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Id {id} nicht gefunden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Missing Id {id}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'person',
		'phrase' => 'error_deleteHomeAdress',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Heimatadressen dürfen nicht gelöscht werden, da diese für die BIS-Meldung relevant sind. Um die Adresse dennoch zu löschen, entfernen sie das Häkchen bei Heimatadresse!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Home addresses may not be deleted as they are relevant for the BIS report. To delete the address anyway, uncheck the home address box!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'btn_statusVorruecken',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Status vorrücken',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'advance status',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'btn_confirmStatus',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Status bestätigen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'confirm status',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'btn_editStatus',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Status bearbeiten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'edit status',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'btn_deleteStatus',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Status löschen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'delete status',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),

	// Betriebsmittel begin
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'successSave',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Speichern erfolgreich',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Successfully saved',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'successDelete',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Löschen erfolgreich',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Delete successful',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'successAdvance',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Vorrückung Status erfolgreich',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Advance status successful',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'successConfirm',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bestätigung Status erfolgreich',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Confirmation status successful',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'betriebsmittel',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Betriebsmittel',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Resources',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'betriebsmittel_delete',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Betriebsmittel löschen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'delete operating resource',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'betriebsmittel_confirm_delete',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Betriebsmittel wirklich löschen?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Really delete operating resource?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'add_betriebsmittel',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Betriebsmittel anlegen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'create operating resource',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'edit_betriebsmittel',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Betriebsmittel bearbeiten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'edit operating resource',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'wawi',
		'phrase' => 'inventarnummer',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Inventarnummer',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'inventory number',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'wawi',
		'phrase' => 'nummer',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nummer',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'number',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'wawi',
		'phrase' => 'ausgegebenam',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ausgegeben am',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'issued on',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'wawi',
		'phrase' => 'retouram',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Retour am',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'returned on',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'wawi',
		'phrase' => 'retourdatum',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Retourdatum',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'return date',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'wawi',
		'phrase' => 'ausgabedatum',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ausgabedatum',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'issue date',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'wawi',
		'phrase' => 'error_zutrittskarteOhneNummer',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Eine Zutrittskarte muss eine Nummer haben. Um die Zuordnung zu dieser Karte zu löschen entfernen Sie bitte den ganzen Datensatz!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'An access card must have a number. To delete the assignment to this card, please remove the entire data record!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'wawi',
		'phrase' => 'error_bmZutrittskarteOccupied',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Diese Zutrittskarte ist bereits ausgegeben an: {vorname} {nachname} ({uid})',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'This access card has already been issued to: {vorname} {nachname} ({uid})',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'wawi',
		'phrase' => 'error_inventarWaehlen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bitte wählen Sie das entsprechende Inventar aus dem Drop Down Menü Inventarnummer aus!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Please select the appropriate inventory from the inventory number drop down menu!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'wawi',
		'phrase' => 'error_retourdatumVorAusgabe',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Retourdatum darf nicht vor Datum der Ausgabe liegen!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The return date must not be before the issue date!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	// Betriebsmittel end
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'softwareanforderung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Softwareanforderung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Software Request',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'softwarebereitstellungSubtitle',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Softwarebereitstellung für die Lehre',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Software delivery for Education',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'swAnforderungenUndLizenen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Softwareanforderungen & Lizenzen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Software Requirements & Licenses',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'anforderungNachSw',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anforderung nach Software',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Request by Software',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'anforderungNachLv',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anforderung nach LV',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Request by Course',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'swNichtGefundenHierBestellen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Software nicht gefunden?<br>Hier bei IT-Services bestellen",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Software not found?<br>Order here from IT Services",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'bereitsAngefordert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Bereits angefordert",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Requested already",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'mindEineZuorndungExistiertBereits',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Mindestens eine Zuordnung existiert bereits.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "At least one assignment already exists.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'swFuerLvAnfordern',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Software für LV anfordern",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Request software for courses",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'swWurdeBereitsAngefordert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Software wurde bereits angefordert",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Software has already been requested",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'swAnforderungUeberAuswahlVonSw',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Softwareanforderung über die Auswahl von Software",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Software Requirements based on the Selection of Software",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'swAnforderungUeberAuswahlVonLvs',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Softwareanforderung über die Auswahl von Lehrveranstaltungen",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Software Requirements based on the Selection of Courses",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'lizenzAnzahlNeu',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Lizenz-Anzahl NEU",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "License Number NEW",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'lizenzanzahlAendern',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Lizenzanzahl ändern",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Change Number of Licenses",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'eingabeFehlt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Eingabe fehlt",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Input missing",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'unveraendert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Unverändert",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Unchanged",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'anforderungenVorruecken',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Anforderungen vorrücken",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Take over Requirements",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'vorrueckenInStudiensemester',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Vorrücken in Studiensemester",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Take over to semester",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'anteiligInProzent',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Anteilig in %",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Percentage share",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'verwalten',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Verwalten",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Administrate",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'lvTemplatesVerwalten',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "LV Templates verwalten",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Administrate Course Templates",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'lvTemplatesUebersicht',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "LV Templates Übersicht",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Course Templates Overview",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	//****************************	 CORE/konto
	array(
		'app' => 'core',
		'category' => 'konto',
		'phrase' => 'buchung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Buchung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Booking',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'konto',
		'phrase' => 'buchungsdatum',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Buchungsdatum',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Booking date',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'konto',
		'phrase' => 'buchungstext',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Buchungstext',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Booking text',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'konto',
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
				'text' => 'Amount',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'konto',
		'phrase' => 'buchungstyp',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Buchungstyp',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Booking type',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'konto',
		'phrase' => 'buchungsnr',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Buchungs Nummer',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Booking number',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'konto',
		'phrase' => 'mahnspanne',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Mahnspanne',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Dunning margin',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'konto',
		'phrase' => 'credit_points',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Credit Points',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Credit Points',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'konto',
		'phrase' => 'reference',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zahlungsreferenz',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Payment reference',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'konto',
		'phrase' => 'confirm_overwrite',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Es ist bereits eine Buchung vorhanden:',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'A booking already exists:',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'konto',
		'phrase' => 'confirm_overwrite_1_add_pers',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'und einer weiteren Person.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'and one additional person.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'konto',
		'phrase' => 'confirm_overwrite_x_add_pers',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'und {x} weiteren Personen.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'and {x} additional persons.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'konto',
		'phrase' => 'confirm_overwrite_proceed',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Trotzdem fortfahren?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Proceed anyway?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'konto',
		'phrase' => 'error_missing',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Buchung #{buchungsnr} existiert nicht',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Booking #{buchungsnr} does not exist',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'konto',
		'phrase' => 'error_counter_level',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Gegenbuchungen koennen nur auf die obersten Buchungen getaetigt werden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Offsetting bookings can only be made on the top bookings',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'konto',
		'phrase' => 'error_delete_level',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bitte zuerst die zugeordneten Buchungen löschen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Please delete the assigned bookings first',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	//****************************	 CORE/stv
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'action_new',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'InteressentIn',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Candidate',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'tab_details',
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
				'text' => 'Details',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'tab_notes',
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
				'text' => 'Notes',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'tab_contact',
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
				'text' => 'Contact',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'tab_prestudent',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'PreStudentIn',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Prestudent',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'tab_status',
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
				'text' => 'State',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'tab_banking',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Konto',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Banking',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'tab_resources',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Betriebsmittel',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Resources',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'tab_grades',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Noten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Grades',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'tab_exam',
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
				'text' => 'Exam',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	// konto
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'konto_title_new',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Neue Buchung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'New Booking',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'konto_title_new_multi',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Neue Buchung ({x} Studenten)',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'New Booking ({x} Students)',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'konto_title_edit',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Buchung #{buchungsnr} bearbeiten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Edit Booking #{buchungsnr}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'konto_counter',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Gegenbuchen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Counter-book',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'konto_payment_confirmation',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zahlungsbestaetigung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Payment confirmation',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'konto_filter_count_0',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Liste filtern auf nicht belastet:',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Filter list to not charged:',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'konto_filter_missing_counter',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Liste filtern auf fehlende Gegenbuchungen:',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Filter the list for missing offsetting entries:',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'konto_all_types',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'alle Buchungstypen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'all booking types',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'konto_filter_open',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nur offene anzeigen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Display only open',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'konto_filter_current_stg',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nur aktuellen Studiengang anzeigen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Display only current Degree Program',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	// status
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'status_confirm_popup',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Diesen Status bestaetigen?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Confirm this status?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	// document
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'document_certificate',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zertifikat',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Certificate',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'document_coursecertificate',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'LV Zeugnis',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Course certificate',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'document_download',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Download',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Download',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'document_archive',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Archivieren',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Archive',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'document_signed_and_archived',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Dokument signiert und archiviert',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Document signed and archived',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	// grades
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'grades_zeugnis',
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
				'text' => 'Certificate',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'grades_takeoverdate',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Übernahmedatum',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Takeover date',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'grades_gradingdate',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Benotungsdatum',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Grading date',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'grades_approvaldate',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Freigabedatum',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Approval date',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'grades_numericgrade',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Note (Numerisch)',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Grade (numeric)',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'grades_studiengang_lv',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Studiengang (Lehrveranstaltung)',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Degree-program (Course)',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'grades_studiengang_kz_lv',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Studiengangskennzahl (Lehrveranstaltung)',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Study program number (Course)',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'grades_semester_lv',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Semester (Lehrveranstaltung)',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Semester (Course)',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'grades_points',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Punkte',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Points',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'grades_lehrveranstaltung_bezeichnung_english',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Lehrveranstaltungsname Englisch',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Coursename english',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'grades_setgrade',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Note setzen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Set grade',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'grades_updated',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Note gesetzt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Grade set',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'grades_title_zeugnis',
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
				'text' => 'Certificate',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'grades_title_teacher',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'LektorIn',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Lector',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'grades_title_repeater',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Wiederholer',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Repeater',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'grades_action_copy',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Übernehmen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Transfer',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'grades_error_sign',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Diese Vorlage darf nicht signiert werden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'This template must not be signed',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'grades_error_archive',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Dieses Dokument ist nicht archivierbar',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'This document cannot be archived',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'grades_error_overwrite',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nicht überschreibbar',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Not overwritable',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'grades_error_lehreinheit_id',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehler beim Ermitteln der Lehreinheit ID',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error determining the Teaching Unit ID',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'grades_error_prestudentstatus',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'StudentIn hat keinen Status in diesem Semester',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Student has no status this semester',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),

	//****************************	 CORE/document_export
	array(
		'app' => 'core',
		'category' => 'document_export',
		'phrase' => 'error_unoconv',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Unoconv nicht gefunden - Bitte installieren sie Unoconv',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Unoconv not found - Please install Unoconv',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'document_export',
		'phrase' => 'error_unoconv_version',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Unoconv Version konnte nicht ermittelt werden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Could not get Unoconv Version',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'document_export',
		'phrase' => 'error_conv_timeout',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Dokumentenkonvertierung ist derzeit nicht möglich. Bitte versuchen Sie es in einer Minute erneut oder kontaktieren Sie einen Administrator',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Document conversion is currently not possible. Please try again in a minute or contact an administrator',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'document_export',
		'phrase' => 'error_sign_timeout',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Signaturserver ist derzeit nicht erreichbar',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Signature server is currently unavailable',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'document_export',
		'phrase' => 'error_sign_pdf',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Derzeit können nur PDFs signiert werden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Currently only PDFs can be signed',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'document_export',
		'phrase' => 'error_outputformat_missing',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ausgabeformat fehlt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Outputformat is not defined',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'document_export',
		'phrase' => 'error_template_missing',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Keine Vorlage gefunden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No template found',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'document_export',
		'phrase' => 'error_headers',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Header wurden bereits gesendet -> Abbruch',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Header already sent -> Abort',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'document_export',
		'phrase' => 'error_file_copy',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kopieren fehlgeschlagen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Copy failed',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'document_export',
		'phrase' => 'error_file_load',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Datei konnte nicht geladen werden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Unable to load file',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'document_export',
		'phrase' => 'error_xml_load',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'XML konnte nicht geladen werden: {url} XML: {xml} PARAMETER: {params}',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Unable to load xml: {url} XML: {xml} PARAMS: {params}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'document_export',
		'phrase' => 'error_xsl_load',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'XSL konnte nicht geladen werden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Unable to load xsl',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'document_export',
		'phrase' => 'error_styles_load',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Styles XSL konnte nicht geladen werden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Unable to load styles xsl',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'document_export',
		'phrase' => 'error_manifest',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Manifest ungültig',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Manifest file invalid',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),

	//****************************	 FHC-Core-SAP
	array(
		'app' => 'core',
		'category' => 'sap',
		'phrase' => 'error_noBuchung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Die Buchungsnr #{buchungsnr} is ungültig',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The Buchungnr #{buchungsnr} is not valid',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'sap',
		'phrase' => 'error_buchungLocked',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Die Buchung wurde bereits übertragen und darf nicht geändert werden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The Buchung was already submitted and can not be changed',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'sap',
		'phrase' => 'msg_buchung_deleted',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Buchungszuordnung SAP geloescht: SalesOrder: {sap_sales_order_id}',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Buchungszuordnung SAP deleted: SalesOrder: {sap_sales_order_id}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	//****************************	 FHC-Core-Status
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'lehrverband',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Lehrverband',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Teaching Association',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'successNewStatus',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '{countSuccess} erfolgreiche Statusänderung(en) auf {status}, {countError} Fehler',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '{countSuccess} successful status changes to {status}, {countError} errors',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'error_updateLehrverband',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehler bei Aktualisierung Lehrverband',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error during insert/update lehrverband',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'error_noLehrverband',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kein Lehrverband vorhanden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error: no existing Lehrverband',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'error_updateStudentlehrverband',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Fehler bei Aktualisierung Studentlehrverband',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error during insert/update Studentlehrverband',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'error_noStudentlehrverband',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kein Studentlehrverband vorhanden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error: no existing Studentlehrverband',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'error_statuseintrag_zeitabfolge',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ungültige Zeitabfolge der Statuseinträge (Statusdatum oder Semester)',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error: Invalid date order of statuses (date or semester)',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'error_endstatus',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nach Abbrecher- und Absolventenstatus darf kein anderer Status mehr eingetragen werden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error: No other status may be entered after the dropout and graduate status',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'error_consecutiveUnterbrecher',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Aufeinanderfolgende Unterbrecher müssen gleiches Ausbildungssemester haben',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error: Successive interrupters must have the same education semester',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'error_consecutiveUnterbrecherAbbrecher',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Unterbrecher und folgender Abbrecher müssen gleiches Ausbildungssemester haben',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error: interrupter and following dropout must have the same education semester',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'error_consecutiveDiplomandStudent',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nach Diplomandenstatus darf kein Studentenstatus mehr eingetragen werden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error: Student status may not be entered after graduate status',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'error_noStudiensemester',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kein Eintrag für Studiensemester vorhanden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error: no entry for study semester',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'anzahl_existingRoles',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => '{anzahl} Rollen vorhanden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '{anzahl} existing roles',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'error_entryInPast',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Datum eines neuen Statuseintrags darf nicht in der Vergangenheit liegen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error: The date of a new status entry cannot be in the past',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'btn_statusAendern',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Status ändern',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Change status',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'error_lastRole',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Die letzte Rolle kann nur durch den Administrator gelöscht werden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error: The last role can only be deleted by the administrator',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'bismeldestichtag',
		'phrase' => 'info_MeldestichtagStatusgrund',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Meldestichtag erreicht - ausschließlich Bearbeiten Statusgrund möglich',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Reporting deadline reached - only editing status reason possible',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'bismeldestichtag',
		'phrase' => 'info_MeldestichtagStatusgrundSemester',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Meldestichtag erreicht - Bearbeiten Ausbildungssemester und Statusgrund möglich',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Edit education semester and status reason possible',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'error_location_combination',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ort, Gemeinde und Postleitzahl passen nicht zusammen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'City, municipality and postcode do not match',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'bittePlzWaehlen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bitte gültige PLZ wählen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Please select valid postcode',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'bitteGemeindeWaehlen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bitte gültige Gemeinde wählen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Please select valid municipality',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'bitteStandortWaehlen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bitte Standort wählen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Please select location',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'error_wrongStatusOrderBeforeStudent',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Vor dem Studentenstatus müssen folgende Status eingetragen werden: {0}',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error: Following status should be present before student status: {0}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'error_personenkennzeichenPasstNichtZuStudiensemester',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Personenkennzeichen passt nicht zu Studiensemester des ersten Studentstatus',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error: Personenkennzeichen does not fit with semester of first student status',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'error_bewerberOrgformUngleichStudentOrgform',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Erster Studentstatus muss gleiche Organisationsform haben wie Bewerberstatus (Studienplan Orgform)',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error: first student status should have same organisational form as bewerber status (Studienplan)',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'error_benutzerBereitsVorhanden',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Benutzer für uid {uid} bereits vorhanden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Error: User for uid {uid} already exists',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'error_noStatusgrund',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Statusgrund {statusgrundkurzbz} nicht gefunden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Statusgrund {statusgrundkurzbz} not found',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'modal_StatusactionSingle',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Den Status dieser Person wirklich auf {status} setzen?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Really set this person\'s status to {status}?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'modal_StatusactionPlural',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Den Status dieser {count} Personen wirklich auf {status} setzen?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Really set the status of these {count} people to {status}?',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'userAnzahl',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'User-Anzahl',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'User Number',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'userAnzahlAendern',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'User-Anzahl ändern',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Change User Number',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'userAnzahlNeu',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'User-Anzahl NEU',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'NEW User Number',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'standardLvTemplate',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Standard LV-Template',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Standard Course Template',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'anforderungNachStandardLvTemplate',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anforderung nach Standard LV-Template',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Request by Standard Course Template',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'swAnforderungUeberAuswahlVonStandardisiertenLvTemplates',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Softwareanforderung über die Auswahl von standardisierten LV-Templates (Quellkurse)",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Software Requirements based on the Selection of Standardized Course-Templates",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'swFuerLvAnfordern',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Software für LV anfordern",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Request software for courses",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	// Betriebsmittel end
	array(
		'app' => 'core',
		'category' => 'exam',
		'phrase' => 'add_pruefung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Prüfung hinzufügen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Add exam',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'exam',
		'phrase' => 'edit_pruefung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Prüfung bearbeiten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Edit exam',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'exam',
		'phrase' => 'delete_pruefung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Prüfung löschen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Delete exam',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'exam',
		'phrase' => 'newFromOld_pruefung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Prüfungskopie für neue Prüfung erstellen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Copy exam',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'exam',
		'phrase' => 'hinweis_kommPrfg',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bitte bei Neuanlage einer kommissionellen Prüfung das Datum der Noteneintragung (i. d. R. heute) eintragen, um den korrekten Fristenablauf der Wiederholung zu ermöglichen.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'When creating a new examination before a committee, please enter the date of the grade entry (usually today) to ensure that the deadline for repeating the exam is met correctly.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'exam',
		'phrase' => 'keineAuswahl',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "keine Auswahl",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "no selection",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'exam',
		'phrase' => 'hinweis_changeAfterExamDate',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'ACHTUNG! Diese Prüfungsnote wurde nicht ins Zeugnis übernommen da die Zeugnisnote nach dem Prüfungsdatum verändert wurde',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'ATTENTION! This exam grade was not included in the certificate because the certificate grade was changed after the exam date',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'exam',
		'phrase' => 'bitteLvteilWaehlen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bitte Lv_Teil wählen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Please select teaching unit',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'exam',
		'phrase' => 'punkte',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Punkte',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Points',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'exam',
		'phrase' => 'filter_currentSem',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Nur aktuelles Studiensemester anzeigen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Display only current Semester',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'student_uid',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Student UID',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'student UID',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'betriebsmittel',
		'phrase' => 'btn_printUebernahmebestaetigung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Übernahmebestätigung drucken',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Print acceptance confirmation',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'betriebsmittel',
		'phrase' => 'btn_editBetriebsmittel',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Betriebsmittel bearbeiten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Edit Resource',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'betriebsmittel',
		'phrase' => 'btn_deleteBetriebsmittel',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Betriebsmittel löschen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Delete Resource',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	// Betriebsmittel end
	array(
		'app' => 'core',
		'category' => 'uhstat',
		'phrase' => 'unbekannt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'unbekannt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'unknown',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'errorConfigFehlt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Config-Eintrag fehlt",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Missing config entry",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'errorUnbekannteUrl',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Unbekannte URL. Seite bzw. Link kann nicht geöffnet werden.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Unknown URL. Cannot open to site or link",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'zuordnungExistiertBereits',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Zuordnung existiert bereits.",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Assignment already exists.",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'swFuerLvAendern',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Software für LV ändern",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Change Software for courses",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'anforderungNachQuellkurs',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anforderung nach Quellkurs',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Request by Course-Template',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'swAnforderungFuerQuellkurs',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Softwareanforderung für Quellkurse",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Software Requirements for Course-Templates",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'swAnforderungFuerEinzelneLvs',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Softwareanforderung für einzelne Lehrveranstaltungen",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Software Requirements for individual Courses",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'softwarebereitstellung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Softwarebereitstellung",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Software Delivery",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	// AMPELN PHRASEN -----------------------------------------------------------------------------
	array(
		'app' => 'core',
		'category' => 'ampeln',
		'phrase' => 'newestAmpeln',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Neueste Ampeln',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Newest Ampeln',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ampeln',
		'phrase' => 'allAmpeln',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Alle Ampeln',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'All Ampeln',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ampeln',
		'phrase' => 'overdue',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Überfällig: {count}',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Overdue: {count}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ampeln',
		'phrase' => 'ampelnDeadline',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Stichtag: {value}',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Deadline: {value}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ampeln',
		'phrase' => 'noOpenAmpeln',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Keine offenen Ampeln.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No open Ampeln.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ampeln',
		'phrase' => 'openAmpeln',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Offene Ampeln',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Open Ampeln',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ampeln',
		'phrase' => 'allMyAmpeln',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Alle meine Ampeln',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'All my Ampeln',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ampeln',
		'phrase' => 'mandatory',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Pflicht',
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
		'category' => 'ampeln',
		'phrase' => 'ampelBestaetigt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ampel bestätigt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Ampel confirmed',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
    array(
		'app' => 'core',
		'category' => 'ampeln',
		'phrase' => 'super',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Super!',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Super!',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	// NEWS PHRASEN ---------------------------------------------------------------------------
	array(
		'app' => 'core',
		'category' => 'news',
		'phrase' => 'allNews',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Alle News',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'All News',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'news',
		'phrase' => 'topNews',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Top Nachrichten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Top News',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'news',
		'phrase' => 'noSubject',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kein Betreff vorhanden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No Subject available',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
// CIS4 phrases from legacy code begin
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'studiengangsleitung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Studiengangsleitung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Program Director',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'stellvertreter',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Stellvertretung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Deputy Program Director',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'sekretariat',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Sekretariat',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Administrative Assistant',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'hochschulvertretung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Hochschulvertretung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'UAS Representative',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'studentenvertreter',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Studienvertretung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Degree Program Representative',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'allgemeinerdownload',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Allgemeiner Download',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Global Download',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'nichtAngemeldet',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Sie sind nicht angemeldet. Es wurde keine Benutzer UID gefunden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'You are not logged in. No UID found',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'lvInfoBearbeiten',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bearbeiten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Edit',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'lehrveranstaltungsinformation',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Lehrveranstaltungsinformation',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Course Information',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'lehrveranstaltungsmenue',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Lehrveranstaltungsmenü',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Course Menu',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'lehrveranstaltungsmenueUnavailable',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kein Lehrveranstaltungsmenü verfügbar',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No Course Menu available',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'gesamtnote',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Gesamtnote',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Final Grade',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'noteneingabedeaktiviert',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Noteneingabe deaktiviert',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Grading disabled',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'keinMailverteiler',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Für die Gruppe(n) {nomail} existiert kein Verteiler! Die Studierenden in diesen Gruppen bekommen kein Mail.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'There is no e-mail distribution list for the group(s) {nomail}! The students in these groups will not receive an e-mail.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'mail',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'E-Mail an Studierende',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'E-Mail to students',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'abmelden',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Abmelden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Unsubscribe',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'anrechnung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anrechnung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Exemption',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'anrechnungen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Anrechnungen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Exemptions',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'stundenplan',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Stundenplan',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Timetable',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'personalGreeting',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "{0}'s persönliches Dashboard",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "{0}'s personal Dashboard",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'myLV',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Meine Lehrveranstaltungen",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "My Courses",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	// ### STUDIENGANG_INFORMATIONEN PHRASEN START
	array(
		'app' => 'core',
		'category' => 'studiengangInformation',
		'phrase' => 'assistenz',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Studiengangsassistenz",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "degree program assistant",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studiengangInformation',
		'phrase' => 'geschaeftsfuehrende_leitung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Geschäftsführende Leitung",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Managing director",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studiengangInformation',
		'phrase' => 'stellvertretende_leitung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Stellvertretende Leitung",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Representative director",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studiengangInformation',
		'phrase' => 'geschaeftsfuehrende_stellvertretende_leitung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Geschäftsführende / Stellvertretende Leitung",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Managing / Representative director",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studiengangInformation',
		'phrase' => 'Jahrgangsvertretung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Jahrgangsvertretung",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Study year representative",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studiengangInformation',
		'phrase' => 'Studienvertretung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Studienvertretung",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "Study representative",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'studiengangInformation',
		'phrase' => 'Hochschulvertretung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Hochschulvertretung",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "University representative",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	// ### STUDIENGANG_INFORMATIONEN PHRASEN ENDE
// CIS4 phrases from legacy code end
// FHC4 Phrases Abschlusspruefung
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'tab_finalexam',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Abschlussprüfung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Final exam',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'abschlussbeurteilung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Abschlussbeurteilung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Final assessment',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'pruefer1',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'PrüferIn 1',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'examiner 1',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'pruefer2',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'PrüferIn 2',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'examiner 2',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'pruefer3',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'PrüferIn 3',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'examiner 3',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'global',
		'phrase' => 'uhrzeit',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Uhrzeit',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'time',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'freigabe',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Freigabe',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Approval',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'sponsion',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Sponsion',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Graduation',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'abschlusspruefung_id',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Abschlusspruefung ID',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Final Examination ID',
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
		'category' => 'abschlusspruefung',
		'phrase' => 'vorsitz_header',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Vorsitz',
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
		'phrase' => 'zurBeurteilung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zur Beurteilung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'To the assessment',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'akadGrad',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Akademischer Grad',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Academic degree',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'error_studentOhneFinalExam',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Keine Abschlussprüfungen für Student_uid(s) {id} gefunden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'No final exams found for Student_uid(s) {id}',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'notekommpruefung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Note komm. Prüfung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Grade committee exam',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'abschluessPruefungAnlegen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Abschlussprüfung anlegen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'New Final Exam',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'abschlusspruefung',
		'phrase' => 'abschluessPruefungBearbeiten',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Abschlussprüfung bearbeiten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Edit Final Exam',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	// FHC4 Phrases Abschlusspruefung End
	array(
		'app' => 'core',
		'category' => 'gruppenmanagement',
		'phrase' => 'nichtZumEditierenDerGruppeBerechtigt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => "Nicht zum Editieren der Gruppe berechtigt",
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => "No authorization for editing the group",
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'pep',
		'category' => 'ui',
		'phrase' => 'maprojohneoe',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Mitarbeiter und Projekt sind der Organisationseinheit nicht zugeordnet.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Employee and project are not assigned to the organizational unit.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'pep',
		'category' => 'ui',
		'phrase' => 'infoandepl/kfl',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Info an DepL/KFL',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Info to DepL/KFL:',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
// FHC4 Phrases CleanUpTasks End
array(
	'app' => 'core',
	'category' => 'person',
	'phrase' => 'wahlname',
	'insertvon' => 'system',
	'phrases' => array(
		array(
			'sprache' => 'German',
			'text' => 'Wahlname',
			'description' => '',
			'insertvon' => 'system'
		),
		array(
			'sprache' => 'English',
			'text' => 'elective name',
			'description' => '',
			'insertvon' => 'system'
		)
	)
),
array(
	'app' => 'core',
	'category' => 'person',
	'phrase' => 'familienstand',
	'insertvon' => 'system',
	'phrases' => array(
		array(
			'sprache' => 'German',
			'text' => 'Familienstand',
			'description' => '',
			'insertvon' => 'system'
		),
		array(
			'sprache' => 'English',
			'text' => 'marital status',
			'description' => '',
			'insertvon' => 'system'
		)
	)
),
array(
	'app' => 'core',
	'category' => 'person',
	'phrase' => 'foto',
	'insertvon' => 'system',
	'phrases' => array(
		array(
			'sprache' => 'German',
			'text' => 'Foto',
			'description' => '',
			'insertvon' => 'system'
		),
		array(
			'sprache' => 'English',
			'text' => 'photo',
			'description' => '',
			'insertvon' => 'system'
		)
	)
),
array(
	'app' => 'core',
	'category' => 'person',
	'phrase' => 'homepage',
	'insertvon' => 'system',
	'phrases' => array(
		array(
			'sprache' => 'German',
			'text' => 'Homepage',
			'description' => '',
			'insertvon' => 'system'
		),
		array(
			'sprache' => 'English',
			'text' => 'homepage',
			'description' => '',
			'insertvon' => 'system'
		)
	)
),
array(
	'app' => 'core',
	'category' => 'lehre',
	'phrase' => 'verband',
	'insertvon' => 'system',
	'phrases' => array(
		array(
			'sprache' => 'German',
			'text' => 'Verband',
			'description' => '',
			'insertvon' => 'system'
		),
		array(
			'sprache' => 'English',
			'text' => 'verband',
			'description' => '',
			'insertvon' => 'system'
		)
	)
),
array(
	'app' => 'core',
	'category' => 'person',
	'phrase' => 'aktiv',
	'insertvon' => 'system',
	'phrases' => array(
		array(
			'sprache' => 'German',
			'text' => 'Aktiv',
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
	'category' => 'lehre',
	'phrase' => 'error_no_person',
	'insertvon' => 'system',
	'phrases' => array(
		array(
			'sprache' => 'German',
			'text' => 'Keine Person gefunden',
			'description' => '',
			'insertvon' => 'system'
		),
		array(
			'sprache' => 'English',
			'text' => 'No Person found',
			'description' => '',
			'insertvon' => 'system'
		)
	)
),
array(
	'app' => 'core',
	'category' => 'lehre',
	'phrase' => 'error_no_student',
	'insertvon' => 'system',
	'phrases' => array(
		array(
			'sprache' => 'German',
			'text' => 'Kein/e Student/in gefunden',
			'description' => '',
			'insertvon' => 'system'
		),
		array(
			'sprache' => 'English',
			'text' => 'No Student found',
			'description' => '',
			'insertvon' => 'system'
		)
	)
),
array(
	'app' => 'core',
	'category' => 'person',
	'phrase' => 'geschieden',
	'insertvon' => 'system',
	'phrases' => array(
		array(
			'sprache' => 'German',
			'text' => 'geschieden',
			'description' => '',
			'insertvon' => 'system'
		),
		array(
			'sprache' => 'English',
			'text' => 'divorced',
			'description' => '',
			'insertvon' => 'system'
		)
	)
),
array(
	'app' => 'core',
	'category' => 'person',
	'phrase' => 'ledig',
	'insertvon' => 'system',
	'phrases' => array(
		array(
			'sprache' => 'German',
			'text' => 'ledig',
			'description' => '',
			'insertvon' => 'system'
		),
		array(
			'sprache' => 'English',
			'text' => 'single',
			'description' => '',
			'insertvon' => 'system'
		)
	)
),
array(
	'app' => 'core',
	'category' => 'person',
	'phrase' => 'verheiratet',
	'insertvon' => 'system',
	'phrases' => array(
		array(
			'sprache' => 'German',
			'text' => 'verheiratet',
			'description' => '',
			'insertvon' => 'system'
		),
		array(
			'sprache' => 'English',
			'text' => 'married',
			'description' => '',
			'insertvon' => 'system'
		)
	)
),
array(
	'app' => 'core',
	'category' => 'person',
	'phrase' => 'verwitwet',
	'insertvon' => 'system',
	'phrases' => array(
		array(
			'sprache' => 'German',
			'text' => 'verwitwet',
			'description' => '',
			'insertvon' => 'system'
		),
		array(
			'sprache' => 'English',
			'text' => 'widowed',
			'description' => '',
			'insertvon' => 'system'
		)
	)
),
array(
	'app' => 'core',
	'category' => 'ui',
	'phrase' => 'firma_id',
	'insertvon' => 'system',
	'phrases' => array(
		array(
			'sprache' => 'German',
			'text' => 'Firma Id',
			'description' => '',
			'insertvon' => 'system'
		),
		array(
			'sprache' => 'English',
			'text' => 'Company Id',
			'description' => '',
			'insertvon' => 'system'
		)
	)
),
array(
	'app' => 'core',
	'category' => 'ui',
	'phrase' => 'adresse_id',
	'insertvon' => 'system',
	'phrases' => array(
		array(
			'sprache' => 'German',
			'text' => 'Adresse ID',
			'description' => '',
			'insertvon' => 'system'
		),
		array(
			'sprache' => 'English',
			'text' => 'Address ID',
			'description' => '',
			'insertvon' => 'system'
		)
	)
),
array(
	'app' => 'core',
	'category' => 'ui',
	'phrase' => 'kontakt_id',
	'insertvon' => 'system',
	'phrases' => array(
		array(
			'sprache' => 'German',
			'text' => 'Kontakt ID',
			'description' => '',
			'insertvon' => 'system'
		),
		array(
			'sprache' => 'English',
			'text' => 'Contact ID',
			'description' => '',
			'insertvon' => 'system'
		)
	)
),
array(
	'app' => 'core',
	'category' => 'ui',
	'phrase' => 'standort_id',
	'insertvon' => 'system',
	'phrases' => array(
		array(
			'sprache' => 'German',
			'text' => 'Standort ID',
			'description' => '',
			'insertvon' => 'system'
		),
		array(
			'sprache' => 'English',
			'text' => 'Location ID',
			'description' => '',
			'insertvon' => 'system'
		)
	)
),
array(
	'app' => 'core',
	'category' => 'ui',
	'phrase' => 'bankverbindung_id',
	'insertvon' => 'system',
	'phrases' => array(
		array(
			'sprache' => 'German',
			'text' => 'Bankverbindung ID',
			'description' => '',
			'insertvon' => 'system'
		),
		array(
			'sprache' => 'English',
			'text' => 'Bankdetails ID',
			'description' => '',
			'insertvon' => 'system'
		)
	)
),
array(
	'app' => 'core',
	'category' => 'ui',
	'phrase' => 'studienplan_id',
	'insertvon' => 'system',
	'phrases' => array(
		array(
			'sprache' => 'German',
			'text' => 'Studienplan ID',
			'description' => '',
			'insertvon' => 'system'
		),
		array(
			'sprache' => 'English',
			'text' => 'Studyplan ID',
			'description' => '',
			'insertvon' => 'system'
		)
	)
),
array(
	'app' => 'core',
	'category' => 'ui',
	'phrase' => 'prestudent_id',
	'insertvon' => 'system',
	'phrases' => array(
		array(
			'sprache' => 'German',
			'text' => 'PrestudentIn ID',
			'description' => '',
			'insertvon' => 'system'
		),
		array(
			'sprache' => 'English',
			'text' => 'Prestudent ID',
			'description' => '',
			'insertvon' => 'system'
		)
	)
),
array(
	'app' => 'core',
	'category' => 'ui',
	'phrase' => 'notiz_id',
	'insertvon' => 'system',
	'phrases' => array(
		array(
			'sprache' => 'German',
			'text' => 'Notiz ID',
			'description' => '',
			'insertvon' => 'system'
		),
		array(
			'sprache' => 'English',
			'text' => 'Notes ID',
			'description' => '',
			'insertvon' => 'system'
		)
	)
),
array(
	'app' => 'core',
	'category' => 'ui',
	'phrase' => 'notizzuordnung_id',
	'insertvon' => 'system',
	'phrases' => array(
		array(
			'sprache' => 'German',
			'text' => 'Notizzuordnung ID',
			'description' => '',
			'insertvon' => 'system'
		),
		array(
			'sprache' => 'English',
			'text' => 'Noteassignment ID',
			'description' => '',
			'insertvon' => 'system'
		)
	)
),
array(
	'app' => 'core',
	'category' => 'ui',
	'phrase' => 'extension_id',
	'insertvon' => 'system',
	'phrases' => array(
		array(
			'sprache' => 'German',
			'text' => 'Extension ID',
			'description' => '',
			'insertvon' => 'system'
		),
		array(
			'sprache' => 'English',
			'text' => 'Extension ID',
			'description' => '',
			'insertvon' => 'system'
		)
	)
),
array(
	'app' => 'core',
	'category' => 'ui',
	'phrase' => 'type_id',
	'insertvon' => 'system',
	'phrases' => array(
		array(
			'sprache' => 'German',
			'text' => 'Type ID',
			'description' => '',
			'insertvon' => 'system'
		),
		array(
			'sprache' => 'English',
			'text' => 'Type ID',
			'description' => '',
			'insertvon' => 'system'
		)
	)
),
array(
	'app' => 'core',
	'category' => 'ui',
	'phrase' => 'betriebsmittel_id',
	'insertvon' => 'system',
	'phrases' => array(
		array(
			'sprache' => 'German',
			'text' => 'Betriebsmittel ID',
			'description' => '',
			'insertvon' => 'system'
		),
		array(
			'sprache' => 'English',
			'text' => 'Ressource ID',
			'description' => '',
			'insertvon' => 'system'
		)
	)
),
array(
	'app' => 'core',
	'category' => 'ui',
	'phrase' => 'betriebsmittelperson_id',
	'insertvon' => 'system',
	'phrases' => array(
		array(
			'sprache' => 'German',
			'text' => 'Betriebsmittelperson ID',
			'description' => '',
			'insertvon' => 'system'
		),
		array(
			'sprache' => 'English',
			'text' => 'Ressourceperson ID',
			'description' => '',
			'insertvon' => 'system'
		)
	)
),
array(
	'app' => 'core',
	'category' => 'ui',
	'phrase' => 'dropdownLoading',
	'insertvon' => 'system',
	'phrases' => array(
		array(
			'sprache' => 'German',
			'text' => 'Daten werden geladen',
			'description' => '',
			'insertvon' => 'system'
		),
		array(
			'sprache' => 'English',
			'text' => 'Loading',
			'description' => '',
			'insertvon' => 'system'
		)
	)
),
array(
	'app' => 'core',
	'category' => 'ui',
	'phrase' => 'error_noInteger',
	'insertvon' => 'system',
	'phrases' => array(
		array(
			'sprache' => 'German',
			'text' => 'Nur Eingabe von ganzen Zahlen erlaubt',
			'description' => '',
			'insertvon' => 'system'
		),
		array(
			'sprache' => 'English',
			'text' => 'Error: Only input of whole numbers allowed',
			'description' => '',
			'insertvon' => 'system'
		)
	)
),
array(
	'app' => 'core',
	'category' => 'ui',
	'phrase' => 'error_maxSem',
	'insertvon' => 'system',
	'phrases' => array(
		array(
			'sprache' => 'German',
			'text' => 'Ausbildungssemester nicht zulässig: größer als Höchstsemester',
			'description' => '',
			'insertvon' => 'system'
		),
		array(
			'sprache' => 'English',
			'text' => 'Error: Semester not allowed: higher than max Semester',
			'description' => '',
			'insertvon' => 'system'
		)
	)
),
// FHC4 Phrases CleanUpTasks End
	// FHC4 Phrases Mobility Start
	array(
		'app' => 'core',
		'category' => 'stv',
		'phrase' => 'tab_mobility',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Mobilität',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Mobility',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'mobility',
		'phrase' => 'mobilitaetsprogramm',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Mobilitätsprogramm',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Mobility program',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'mobility',
		'phrase' => 'gastnation',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Gastnation',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Host nation',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'mobility',
		'phrase' => 'herkunftsland',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Herkunftsland',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Country of origin',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'mobility',
		'phrase' => 'universitaet',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Universität',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'University',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'mobility',
		'phrase' => 'ects_erworben',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Erworbene ECTS',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'ECTS acquired',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'mobility',
		'phrase' => 'ects_angerechnet',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Angerechnete ECTS',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'ECTS credited',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'mobility',
		'phrase' => 'zweck',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zweck',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Purpose',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'mobility',
		'phrase' => 'aufenthalt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Aufenthalt Förderung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Residency funding',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'mobility',
		'phrase' => 'zweck_neu',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Mobilitätszweck anlegen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Create motivation for mobility',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'mobility',
		'phrase' => 'foerderung_neu',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Förderung für Aufenthalt anlegen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Create funding for mobility',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'mobility',
		'phrase' => 'kurzbz_program',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Programmkurzbezeichnung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Program short description',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'mobility',
		'phrase' => 'bisio_id',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Bisio ID',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Bisio ID',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'mobility',
		'phrase' => 'kurzbz',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kurzbezeichnung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Short Name',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'error_entryExisting',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Eintrag bereits vorhanden',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Entry already existing',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'mobility',
		'phrase' => 'error_existingEntryInExtension',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Dieser Datensatz wird von der Mobility Extension verwendet und muss zuerst dort gelöscht werden.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'This record is used by the Mobility Extension and must first be deleted there.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'mobility',
		'phrase' => 'mobility_anlegen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Mobilität anlegen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Create mobility',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'mobility',
		'phrase' => 'mobility_bearbeiten',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Mobilität bearbeiten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Edit mobility',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	// FHC4 Phrases Mobility End
	// feature-55614 begin
	array(
		'app' => 'core',
		'category' => 'kvp',
		'phrase' => 'lvdev_uebersichten',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'LV-Weiterentwicklung Übersichten',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Course Development Overviews',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'kompetenzfeld',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Kompetenzfeld',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'competence field',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'expand_all',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'alle ausklappen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'expand all',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'ui',
		'phrase' => 'collapse_all',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'alle einklappen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'collapse all',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'core',
		'category' => 'lehre',
		'phrase' => 'quellkurs',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Quellkurs',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Source Course',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	// feature-55614 end
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'swAendern',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'SW ändern',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Change SW',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'quellkurs',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Quellkurs',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Source Course',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'softwareAbbestellt',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Software wurde abbestellt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Software was cancelled',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'lizenzkategorie',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Lizenz-Kategorie',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'License category',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'lizenzkategorieKurzbz',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Lizenz-Kategorie Kurzbz',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'License category short',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'softwarebereitstellung',
		'category' => 'global',
		'phrase' => 'lizenzkategorie',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Lizenz-Kategorie',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'License category',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	// PROJEKTARBEITSBEURTEILUNG SS2025 PHRASEN ---------------------------------------------------------------------------
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'maxPunkte',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Max. Punkte',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Max. points',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'bewertung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Erfüllungsgrad (Prozent) zum Ausfüllen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Degree of Fulfilment (Percentage) to Fill In',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'details',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Details (angemessener und korrekter Einsatz von
Werkzeugen und Technologien in jedem Schritt)',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Details
(appropriate and correct use of tools and technologies at each step)',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'problemstellungZieldefinition',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Problemstellung und Zieldefinition',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Problem Definition and Objective Setting',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'problemstellungZieldefinitionText',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Die Problemstellung ist klar und präzise definiert und in einen wissenschaftlichen Kontext eingebettet.
Die Zielsetzung sowie eventuelle Messgrößen sind eindeutig formuliert.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The problem is clearly and precisely defined and embedded in a scientific context.
The objective, along with any potential metrics, is clearly formulated.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'methodikLoesungsansatz',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Methodik und Lösungsansatz',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Methodology and Approach',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'methodikLoesungsansatzText',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Das methodische Vorgehen ist logisch und nachvollziehbar strukturiert, passend zur Zielsetzung,
und die angewandten Methoden sind korrekt und fundiert umgesetzt.
Die Methodik ist fachspezifisch angemessen, literaturbasiert begründet und wissenschaftlich vertretbar.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The methodological approach is logically and comprehensively structured,
aligned with the objective, and the applied methods are implemented correctly and soundly.
The methodology is appropriate to the field, justified based on literature, and scientifically valid.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'ergebnisseDiskussion',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Ergebnisse und Diskussion',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Results and Discussion',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'ergebnisseDiskussionText',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Die Qualität der Lösung ist bezogen auf die Zielsetzung ausreichend.
				Die Ergebnisse werden fundiert analysiert und in Bezug auf die Zielsetzung schlüssig interpretiert.
				Die Diskussion reflektiert die Relevanz und Grenzen der Ergebnisse kritisch und ist logisch strukturiert.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The quality of the solution sufficiently meets the objective.
The results are thoroughly analyzed and coherently interpreted with respect to the objective.
The discussion critically reflects on the relevance and limitations of the results and is logically structured.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'strukturAufbau',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Struktur und Aufbau',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Structure and Organization',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'strukturAufbauText',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Die Arbeit folgt einer logischen, klaren Gliederung und einem konsistenten roten Faden.
Verzeichnisse, Grafiken, Tabellen und der Text sind gemäß den aktuell gültigen wissenschaftlichen Richtlinien der FH Technikum Wien aufbereitet.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The work follows a logical and clear outline with a consistent narrative thread.
Directories, graphics, tables, and text are prepared in accordance with the currently valid scientific guidelines of FH Technikum Wien.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'stilAusdruck',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Stil und Ausdruck',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Style and Expression',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'stilAusdruckText',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Der sprachliche Ausdruck ist präzise,
fachlich korrekt und erfüllt die Anforderungen an gendergerechte Sprache gemäß den geltenden Richtlinien der FH Technikum Wien.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The linguistic expression is precise, professionally accurate,
and meets the requirements of gender-sensitive language as per the applicable guidelines of FH Technikum Wien.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'zitierregelnQuellenangaben',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Zitierregeln und Quellenangaben',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Citation Rules and References',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'zitierregelnQuellenangabenText',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Umfang, Qualität und Aktualität der verarbeiteten Quellen sind angemessen
und repräsentieren den aktuellen Stand der Forschung zum Thema. Die Zitierregeln (IEEE oder Harvard) werden konsequent und korrekt angewendet.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The scope, quality, and timeliness of the sources processed are appropriate
and represent the current state of research on the topic. The prescribed citation rules are consistently and correctly applied.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'notenschluesselHinweisGewichtungEinzeln',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Jedes Kriterium muss mit mind. 50% bewertet werden, sonst ist die gesamte Arbeit negativ.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Each criterion must receive a minimum score of 50%; otherwise, the entire work is rated negatively.',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'gesamtkommentar',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Gesamtkommentar',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Overall comment',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'gesamtkommentarVerpflichtend',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Gesamtkommentar verpflichtend auszufüllen',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'An overall comment is mandatory and must be completed',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'eingabefeld',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Eingabefeld',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Input field',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'universitaetLogo',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Universitätslogo',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'University logo',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'category' => 'projektarbeitsbeurteilung',
		'phrase' => 'textEingabefeldBewertung',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Text-Eingabefeld zur Bewertung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Text inut field for assessment',
				'description' => '',
				'insertvon' => 'system'
			)
		)
	)
	// PROJEKTARBEITSBEURTEILUNG SS2025 ENDE ---------------------------------------------------------------------------
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
