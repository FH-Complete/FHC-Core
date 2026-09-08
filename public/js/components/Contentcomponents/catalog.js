/**
 * Catalog of the components that may be placed inside CMS content.
 *
 * A component is available to CMS editors only if it has an entry here. An arbitrary
 * project component cannot work in content: it expects props from a parent, inject keys
 * and route params. Content supplies attribute strings only. A component listed here is
 * written for content use: lightweight, one fetch, props chosen in the CMS.
 *
 * Marker convention in the content HTML:
 *
 *   <div class="fhc-contentcomponent" data-fhc-component="NAME" data-PROP="VALUE"></div>
 *
 * A div is used instead of a custom tag on purpose. TinyMCE 5 (CIS4 admin) and TinyMCE 3
 * (legacy admin) both treat a div as a block. An unknown tag counts as inline and the
 * legacy admin can split or nest it on save, which would corrupt shared content.
 */

// Class on every marker. TinyMCE uses it for noneditable and for the editor styling.
export const CONTENTCOMPONENT_CLASS = 'fhc-contentcomponent';

// Attribute holding the component name.
export const CONTENTCOMPONENT_ATTR = 'data-fhc-component';

// Upper bound per content. A paste accident must not start dozens of requests.
export const CONTENTCOMPONENT_MAX = 20;

/**
 * One entry per available component.
 *
 * name         value of data-fhc-component, and the key in components.js
 * labelPhrase  phrase in category cms, shown in the insert dialog
 * props        attribute name (kebab-case) => { type, required, labelPhrase, source }
 *
 * Attribute names must be kebab-case. The HTML parser lowercases every attribute name,
 * so camelCase would not survive. Vue maps the kebab key back to the camelCase prop.
 *
 * TODO evaluate where "source" belongs. It tells the insert dialog how to fill the field.
 * Only 'text' and 'oe' exist today. A growing list may deserve its own place.
 */
export const catalog = [
	{
		name: 'oe-personen',
		labelPhrase: 'ccOePersonen',
		props: {
			'oe-kurzbz': {
				type: 'string',
				required: true,
				labelPhrase: 'ccOrganisationseinheit',
				source: 'oe'
			},
			'foto': {
				type: 'boolean',
				required: false,
				labelPhrase: 'ccFotosAnzeigen'
			},
			'tiefe': {
				type: 'number',
				required: false,
				labelPhrase: 'ccTiefeOe'
			}
		}
	},
	{
		name: 'person-block',
		labelPhrase: 'ccPersonBlock',
		props: {
			'uid': {
				type: 'string',
				required: true,
				labelPhrase: 'ccPerson',
				source: 'person'
			},
			'funktion': {
				type: 'string',
				required: false,
				labelPhrase: 'ccFunktionsbezeichnung'
			},
			'foto': {
				type: 'boolean',
				required: false,
				labelPhrase: 'ccFotoAnzeigen'
			}
		}
	},
	{
		name: 'dms-dokumente',
		labelPhrase: 'ccDmsDokumente',
		props: {
			'dms-ids': {
				type: 'string',
				required: true,
				labelPhrase: 'ccDmsIds',
				// Adds a category filter and a document list to the dialog. Choosing a
				// document appends its id to this field.
				picker: 'dms'
			}
		}
	},
	{
		name: 'dms-liste',
		labelPhrase: 'ccDmsListe',
		props: {
			'kategorie-kurzbz': {
				type: 'string',
				required: true,
				labelPhrase: 'ccDmsKategorie',
				source: 'dmskategorie'
			},
			'tiefe': {
				type: 'number',
				required: false,
				labelPhrase: 'ccTiefeDms'
			}
		}
	},
	{
		name: 'contentchild-menu',
		labelPhrase: 'ccContentchildMenu',
		props: {
			'content-id': {
				type: 'number',
				required: false,
				labelPhrase: 'ccContentId'
			}
		}
	}
];

export function getDescriptor(name)
{
	return catalog.find(entry => entry.name === name) || null;
}
