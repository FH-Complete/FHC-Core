import { catalog } from './catalog.js';
import OePersonen from './OePersonen.js';
import DmsListe from './DmsListe.js';
import DmsDokumente from './DmsDokumente.js';
import PersonKontakt from './PersonKontakt.js';
import ContentchildMenu from './ContentchildMenu.js';

/**
 * Component name => component.
 *
 * Static imports. At the current scale this is simpler than a dynamic import and it
 * survives a rollup bundle. Switch to `(await import(file)).default`, the way
 * Dashboard/Item.js loads a widget, once the catalog gets long.
 */
export const components = {
	'oe-personen': OePersonen,
	'person-block': PersonKontakt,
	'dms-liste': DmsListe,
	'dms-dokumente': DmsDokumente,
	'contentchild-menu': ContentchildMenu
};

// The catalog and this map are written by hand and must name the same set.
for (const entry of catalog)
{
	if (!components[entry.name])
		console.error('Contentcomponent "' + entry.name + '" is in the catalog but has no component.');
}
for (const name of Object.keys(components))
{
	if (!catalog.some(entry => entry.name === name))
		console.error('Contentcomponent "' + name + '" has a component but no catalog entry.');
}
