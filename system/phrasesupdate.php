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
						der Rechnung im CIS kommen.
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
							Otherwise, no automatic payment allocation will take place and there may be a delay in displaying the current payment status of the invoice in CIS.
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
											In the case of foreign bank transfers, the charges are to be paid by the
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
The invoice will be sent to you again. <u><strong>The amount is only to be transferred after receipt of the invoice</strong></u>',
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
				'text' => 'Please contact your study program assistant. The outstanding invoice will be canceled.',
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
				'text' => 'Der Plagiatscheck wurde durchgeführt und bestätigt, dass der zentrale Inhalt der Arbeit im erforderlichen Ausmaß eigenständig verfasst wurde (vgl. Satzungsteil Studienrechtliche Bestimmungen / Prüfungsordnung, § 18 Abs. 2 und 3).',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'The plagiarism check has been carried out and confirms that the central content of the thesis has been written independently to the required extent (cf. part of the Statutes on Studies Act Provisions / Examination Regulations, § 18 Para. 2 and 3).',
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
                'text' => '<br>Die Höchstgrenze für Anrechnungen gem. § 12 Abs. 3 Fachhochschulgesetz wird überschritten.<br><b>Bisherige ECTS + ECTS dieser LV: Total: {0} [ Schulisch: {1}  | Beruflich: {2}  ]</b> ',
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
							Auf der Grundlage der vorliegenden Maßnahmen absolvieren Sie im Laufe ihres Studiums Internationalisierungsaktivitäten, die mit unterschiedlichen ECTS-Punkten hinterlegt sind. <br />
							In Summe müssen 5 ECTS erworben werden, die im 6. Semester wirksam werden. <br/>
							Das Modul „International skills“ wird mit der Beurteilung „Mit Erfolg teilgenommen“ abgeschlossen. <br />
							Bitte wählen Sie die für Sie in Frage kommenden Maßnahmen aus und planen Sie das entsprechende Semester. <br />
							Sobald die 5 ECTS erreicht wurden, überprüft der Studiengang die von Ihnen hochgeladenen Dokumente. <br /><br />
							Fragen zum Status Ihrer Maßnahme u.ä. richten Sie bitte an den Studiengang. <br />
							Bei allen weiteren Fragen zum Thema Organisation und Finanzierung des Auslandsaufenthalts und/oder Sprachkurs gibt Ihnen das International Office der FH Technikum Wien unter <a href="mailto:international.office@technikum-wien.at">international.office@technikum-wien.at</a> gerne Auskunft.',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Starting with the study year 2022/23, the acquisition of international and intercultural competencies is part of the curriculum.<br />
							On the basis of the measures at-hand, you will complete internationalization activities during the course of your studies, which are assigned different ECTS credits.<br />
							In total, 5 ECTS must be acquired, which become effective in the 6th semester.<br />
							The module “International skills” is completed with the assessment "Successfully participated". <br />
							Please select the measures that apply to you and schedule the appropriate semester. <br />
							Once the 5 ECTS have been achieved, the degree program will review the documents you have uploaded. <br /><br />
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
				'text' => 'ECTS bestätigt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'ECTS confirmed',
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
				'text' => 'ECTS - Maßnahme',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'ECTS - Measures',
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
				'text' => 'Maßnahmen - geplant',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Measures - planned',
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
		'phrase' => 'durchgefuehrteMassnahmen',
		'insertvon' => 'system',
		'phrases' => array(
			array(
				'sprache' => 'German',
				'text' => 'Maßnahmen - durchgeführt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Measures - performed',
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
				'text' => 'ECTS - bestätigt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'ECTS - confirmed',
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
				'text' => 'Maßnahmen - abgelehnt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Measures - declined',
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
				'text' => 'Anmerkung - Studiengangsleitung',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => 'Note - Study course Director',
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
				'text' => '>=5 ECTS verplant',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '>=5 ECTS planned',
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
				'text' => '<5 ECTS verplant',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '<5 ECTS planned',
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
				'text' => '>=5 ECTS bestätigt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '>=5 ECTS confirmed',
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
				'text' => '<5 ECTS bestätigt',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '<5 ECTS confirmed',
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
