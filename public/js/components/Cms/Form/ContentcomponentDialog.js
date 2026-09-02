import ApiCmsAdmin from '../../../api/factory/cmsadmin.js';
import {
	catalog,
	getDescriptor,
	CONTENTCOMPONENT_CLASS,
	CONTENTCOMPONENT_ATTR
} from '../../Contentcomponents/catalog.js';

/**
 * The insert and edit dialogs for a contentcomponent marker.
 *
 * Lives apart from FieldWysiwyg so that file keeps to the editor itself. Everything that
 * knows about the catalog, the marker markup and the dialogs is here.
 */

// Helper fields a picker adds to a dialog. They must not collide with a prop name, and
// markerHtml ignores them because it walks the catalog, not the dialog data.
const FELD_KATEGORIE = '__kategorie';
const FELD_DOKUMENT = '__dokument';

const LEERE_AUSWAHL = { value: '', text: '— auswählen —' };

// A prop source fills a dialog field with a list from the server. The key is the
// "source" value in the catalog. A prop without a known source gets a plain text field.
const SOURCES = {
	oe: {
		request: () => ApiCmsAdmin.getOrganisationseinheiten(),
		item: oe => ({
			value: oe.oe_kurzbz,
			text: (oe.bezeichnung || oe.oe_kurzbz) + (oe.aktiv ? '' : ' (inaktiv)')
		})
	},
	dmskategorie: {
		request: () => ApiCmsAdmin.getDmsKategorien(),
		item: kat => ({
			value: kat.kategorie_kurzbz,
			text: (kat.bezeichnung || kat.kategorie_kurzbz) + ' (' + kat.anzahl + ')'
		})
	},
	// Surname first
	person: {
		request: () => ApiCmsAdmin.getMitarbeiter(),
		item: person => ({
			value: person.uid,
			text: person.nachname + ' ' + person.vorname + ' (' + person.uid + ')'
		})
	}
};

function escapeAttr(value)
{
	return String(value)
		.replace(/&/g, '&amp;')
		.replace(/"/g, '&quot;')
		.replace(/</g, '&lt;')
		.replace(/>/g, '&gt;');
}

// Builds the marker. It stays empty: CIS4 renders the component into it, and every other
// renderer shows nothing. NOTE: if a marker disappears on save, check the TinyMCE
// serializer first. noneditable keeps the node, but an empty block is the exposed part.
function markerHtml(name, descriptor, data)
{
	let attrs = '';

	for (const propName of Object.keys(descriptor.props || {}))
	{
		const value = data[propName];
		// false is an unchecked box. The absent attribute already means false.
		if (value === '' || value === false || value === undefined || value === null)
			continue;
		attrs += ' data-' + propName + '="' + escapeAttr(value) + '"';
	}

	return '<div class="' + CONTENTCOMPONENT_CLASS + '" '
		+ CONTENTCOMPONENT_ATTR + '="' + name + '"' + attrs + '></div>';
}

// Reads an existing marker back into dialog values.
function readMarker(node)
{
	const name = node.getAttribute(CONTENTCOMPONENT_ATTR);
	const descriptor = getDescriptor(name);

	if (!descriptor)
		return null;

	const values = {};
	for (const propName of Object.keys(descriptor.props || {}))
		values[propName] = node.getAttribute('data-' + propName) || '';

	return { name: name, values: values };
}

/**
 * @param {object} options { api: the $api plugin, onChanged: called after a write }
 */
export function createContentcomponentDialog(options)
{
	const api = options.api;
	const onChanged = options.onChanged || function () {};

	const sourceItems = {};
	const sourceRequests = {};

	// These lists are large and rarely needed. Load each one once, on demand.
	function loadSource(key)
	{
		if (sourceRequests[key])
			return sourceRequests[key];

		const source = SOURCES[key];

		sourceRequests[key] = api
			.call(source.request())
			.then(res => { sourceItems[key] = (res.data || []).map(source.item); })
			.catch(() => { sourceItems[key] = []; });

		return sourceRequests[key];
	}

	// Step one: pick the component. A second dialog then asks for its properties.
	// Two dialogs avoid the redial logic a single changing dialog would need.
	function openPicker(editor)
	{
		if (!catalog.length)
			return;

		editor.windowManager.open({
			title: 'Contentcomponent einfügen',
			initialData: { component: catalog[0].name },
			body: {
				type: 'panel',
				items: [{
					type: 'selectbox',
					name: 'component',
					label: 'Contentcomponent',
					items: catalog.map(entry => ({ value: entry.name, text: entry.label }))
				}]
			},
			buttons: [
				{ type: 'cancel', text: 'Abbrechen' },
				{ type: 'submit', text: 'Weiter', primary: true }
			],
			onSubmit(dialog) {
				const name = dialog.getData().component;
				dialog.close();
				openProperties(editor, name, null, null);
			}
		});
	}

	// Step two. node is null for an insert and the marker element for an edit.
	function openProperties(editor, name, values, node)
	{
		const descriptor = getDescriptor(name);

		if (!descriptor)
			return;

		const specs = Object.entries(descriptor.props || {});

		const sources = specs
			.map(([, spec]) => spec.source)
			.filter((key, i, all) => SOURCES[key] && all.indexOf(key) === i);

		// A document picker needs the category list even though no prop declares it.
		if (specs.some(([, spec]) => spec.picker === 'dms') && sources.indexOf('dmskategorie') === -1)
			sources.push('dmskategorie');

		Promise.all(sources.map(loadSource)).then(() => {
			// Documents of the category currently chosen in the dialog. A category change
			// refills this and redials, because the items of a selectbox are fixed once
			// the dialog is built.
			let dokumente = [];

			// A stored value that is no longer in the list, for example a person who left.
			// Without this extra option the select would fall back to its first entry and
			// silently replace the value on save. slice() keeps the cache clean.
			function listeFuer(propName, spec)
			{
				const liste = (sourceItems[spec.source] || []).slice();
				const wert = values && values[propName];

				if (wert && !liste.some(eintrag => eintrag.value === wert))
					liste.unshift({ value: wert, text: wert + ' (nicht in der Auswahl)' });

				return liste;
			}

			function itemsBauen()
			{
				return specs.reduce((alle, [propName, spec]) => {
					if (SOURCES[spec.source])
					{
						alle.push({
							type: 'selectbox',
							name: propName,
							label: spec.label || propName,
							items: listeFuer(propName, spec)
						});
						return alle;
					}

					if (spec.type === 'boolean')
					{
						alle.push({
							type: 'checkbox',
							name: propName,
							label: spec.label || propName
						});
						return alle;
					}

					alle.push({
						type: 'input',
						name: propName,
						label: spec.label || propName
					});

					if (spec.picker === 'dms')
					{
						alle.push({
							type: 'selectbox',
							name: propName + FELD_KATEGORIE,
							label: 'Kategorie',
							items: [LEERE_AUSWAHL].concat(sourceItems.dmskategorie || [])
						});
						alle.push({
							type: 'selectbox',
							name: propName + FELD_DOKUMENT,
							label: 'Dokument hinzufügen',
							items: [LEERE_AUSWAHL].concat(dokumente)
						});
					}

					return alle;
				}, []);
			}

			function konfigBauen(daten)
			{
				return {
					title: descriptor.label,
					initialData: daten,
					body: { type: 'panel', items: itemsBauen() },
					onChange(dialog, details) {
						aufAenderung(dialog, details.name);
					},
					buttons: [
						{ type: 'cancel', text: 'Abbrechen' },
						{ type: 'submit', text: 'Übernehmen', primary: true }
					],
					onSubmit(dialog) {
						const html = markerHtml(name, descriptor, dialog.getData());
						dialog.close();

						if (node)
							editor.dom.setOuterHTML(node, html);
						else
							editor.insertContent(html);

						onChanged();
					}
				};
			}

			function aufAenderung(dialog, feld)
			{
				// A new category means a new document list, and only redial can replace
				// the options of a selectbox.
				if (feld.endsWith(FELD_KATEGORIE))
				{
					const ziel = feld.slice(0, -FELD_KATEGORIE.length);
					const daten = dialog.getData();
					const kategorie = daten[feld];

					daten[ziel + FELD_DOKUMENT] = '';

					if (!kategorie)
					{
						dokumente = [];
						dialog.redial(konfigBauen(daten));
						return;
					}

					api
						.call(ApiCmsAdmin.getDmsKategorieDokumente(kategorie))
						.then(res => {
							dokumente = (res.data || []).map(dokument => ({
								value: String(dokument.dms_id),
								// A document outside the CIS search still renders here, so
								// the editor has to see which one that is.
								text: dokument.name
									+ (dokument.cis_suche ? '' : ' (nicht in der CIS-Suche)')
							}));
						})
						.catch(() => { dokumente = []; })
						.then(() => { dialog.redial(konfigBauen(daten)); });

					return;
				}

				// Choosing a document appends it and clears the select, so the next one can
				// be picked straight away. The reset fires this handler again with an empty
				// value, which the guard below drops.
				if (feld.endsWith(FELD_DOKUMENT))
				{
					const ziel = feld.slice(0, -FELD_DOKUMENT.length);
					const daten = dialog.getData();
					const wert = daten[feld];

					if (!wert)
						return;

					const ids = String(daten[ziel] || '')
						.split(',')
						.map(teil => teil.trim())
						.filter(teil => teil);

					if (ids.indexOf(wert) === -1)
						ids.push(wert);

					const patch = {};
					patch[ziel] = ids.join(', ');
					patch[feld] = '';
					dialog.setData(patch);
				}
			}

			// Every field needs a start value, otherwise the dialog reports it as unknown.
			const startdaten = {};
			for (const [propName, spec] of specs)
			{
				if (spec.type === 'boolean')
				{
					startdaten[propName] = !!values && values[propName] === 'true';
					continue;
				}

				const liste = sourceItems[spec.source];
				startdaten[propName] = (values && values[propName])
					|| (liste && liste.length ? liste[0].value : '');

				if (spec.picker === 'dms')
				{
					startdaten[propName + FELD_KATEGORIE] = '';
					startdaten[propName + FELD_DOKUMENT] = '';
				}
			}

			editor.windowManager.open(konfigBauen(startdaten));
		});
	}

	// Opens the property dialog of an existing marker.
	function openMarker(editor, node)
	{
		const marker = readMarker(node);

		if (marker)
			openProperties(editor, marker.name, marker.values, node);
	}

	return {
		openPicker: openPicker,
		openMarker: openMarker
	};
}
