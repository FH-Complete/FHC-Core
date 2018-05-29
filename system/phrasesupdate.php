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
				'text' => '',
				'description' => 'last editor',
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
				'text' => 'messages',
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
				'text' => '',
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
				'text' => '',
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
				'text' => 'message',
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
				'text' => '',
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
				'text' => '',
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
				'text' => '',
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
				'text' => '',
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
				'text' => '',
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
				'text' => '',
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
				'text' => '',
				'description' => 'social security number',
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
				'text' => '',
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
				'text' => '',
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
				'text' => '',
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
				'text' => '',
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
				'text' => '',
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
				'text' => '',
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
				'text' => '',
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
				'text' => '',
				'description' => 'notes on the application',
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
				'text' => '',
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
				'text' => '',
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
				'text' => '',
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
				'text' => 'reason for cancellation',
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
				'text' => 'Bei Absage von InteressentInnen	erhalten diese den Status "Abgewiesener" und deren '
				. 'ZGV-Daten können im Infocenter nicht mehr bearbeitet oder freigegeben werden. '
				. 'Alle nicht gespeicherten ZGV-Daten gehen verloren. Fortfahren?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '',
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
				'text' => 'an Ausgewählte',
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
				'text' => '',
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
				'text' => '',
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
				'text' => 'Bei Freigabe von InteressentInnen wird deren Interessentenstatus bestätigt 
					und deren Zgvdaten können im Infocenter nicht mehr bearbeitet oder freigegeben werden.<br/>
					Alle nicht gespeicherten Zgvdaten gehen verloren.<br/>
					Fortfahren?',
				'description' => '',
				'insertvon' => 'system'
			),
			array(
				'sprache' => 'English',
				'text' => '',
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
				'text' => 'change note',
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
				app=" . $db->db_add_param($phrase['app']) . " AND
				category=" . $db->db_add_param($phrase['category']) . " AND
				phrase=" . $db->db_add_param($phrase['phrase']);

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
								$db->db_add_param($phrase['app']) . ','.
								$db->db_add_param($phrase['phrase']) . ','.
								' now(),'.
								$db->db_add_param($phrase['insertvon']) . ','.
								$db->db_add_param($phrase['category']) . ');';

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
				echo 'Kategorie/Phrase: <b>' . $phrase['category'] . '/' . $phrase['phrase'] . ' hinzugefügt</b><br>';
			}
			else
				echo '<span class="error">Fehler: ' . $phrase['category'] . '/' . $phrase['phrase'] . ' hinzufügen nicht möglich</span><br>';

		}
		//phrase existing -> get phrase_id
		else
		{
			if ($obj = $db->db_fetch_object($result))
			{
				$phrase_id = $obj->phrase_id;
			}
			echo 'Kategorie/Phrase: ' . $phrase['category'] . '/' . $phrase['phrase'] . ' vorhanden.<br>';
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
					phrase_id=" . $phrase_id . " AND
					sprache='" . $language . "'";
			
			
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
										$db->db_add_param($phrase_id, FHC_INTEGER) . ','.
										$db->db_add_param($phrase_phrases['sprache']) . ','.
										' NULL,'.
										' NULL,'.
										$db->db_add_param($phrase_phrases['text']) . ','.
										$db->db_add_param($phrase_phrases['description']) . ','.
										' now(),'.
										$db->db_add_param($phrase_phrases['insertvon']) . ');';

					if ($db->db_query($qry_insert))
					{
						echo '-- Phrasentext ' . strtoupper(substr($phrase_phrases['sprache'], 0, 3)) . ': <b>' . $phrase_phrases['text'] . ' hinzugefügt</b><br>';
					}
					else
					{
						echo '<span class="error">Fehler: Phrasentext '  . strtoupper(substr($phrase_phrases['sprache'], 0, 3)) . ': '. $phrase_phrases['text'] . ' hinzufügen nicht möglich</span><br>';
					}
				}
			}
		}
	}
}

if(!$new)
	echo '<b>Keine neuen Phrasen</b><br>';
