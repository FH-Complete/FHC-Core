import {
	catalog,
	getDescriptor,
	CONTENTCOMPONENT_ATTR,
	CONTENTCOMPONENT_MAX
} from './catalog.js';

// Runs over the lifetime of the page. Two identical markers in one content therefore get
// two distinct keys, which Vue needs to keep their Teleports apart.
let keyCounter = 0;

// One log line per problem, not one per render.
const reported = new Set();

function report(message)
{
	if (reported.has(message))
		return;
	reported.add(message);
	console.error('Contentcomponent: ' + message);
}

/**
 * Turns one attribute string into the declared type. Returns undefined on a bad value.
 */
function coerce(raw, spec)
{
	switch (spec.type)
	{
		case 'number':
		{
			const value = Number(raw);
			return (raw === '' || Number.isNaN(value)) ? undefined : value;
		}
		case 'boolean':
			return raw === 'true' || raw === '1';
		default:
			return raw;
	}
}

/**
 * Reads the props of one marker. Only an attribute named in the schema is read. Every
 * other attribute is dropped. This allow-list is the boundary between the content and
 * the component.
 */
function readProps(el, descriptor)
{
	const props = {};

	for (const [name, spec] of Object.entries(descriptor.props || {}))
	{
		const raw = el.getAttribute('data-' + name);

		if (raw === null)
		{
			if (spec.required)
			{
				report('"' + descriptor.name + '" misses the required property "' + name + '".');
				return null;
			}
			continue;
		}

		const value = coerce(raw, spec);
		if (value === undefined)
		{
			report('"' + descriptor.name + '" got the value "' + raw + '" for "' + name
				+ '", which is not a ' + spec.type + '.');
			return null;
		}

		props[name] = value;
	}

	return props;
}

/**
 * Finds every marker below root and returns the ones that can be rendered.
 * An unknown name or a bad property is logged and skipped, never shown to a reader.
 *
 * @param {Element} root
 * @return {Array<{el: Element, key: string, name: string, props: Object}>}
 */
export function scanContentcomponents(root)
{
	if (!root)
		return [];

	const elements = Array.from(root.querySelectorAll('[' + CONTENTCOMPONENT_ATTR + ']'));

	if (elements.length > CONTENTCOMPONENT_MAX)
	{
		report('a content holds ' + elements.length + ' components. Only the first '
			+ CONTENTCOMPONENT_MAX + ' are rendered.');
	}

	const markers = [];

	for (const el of elements.slice(0, CONTENTCOMPONENT_MAX))
	{
		const name = el.getAttribute(CONTENTCOMPONENT_ATTR);
		const descriptor = getDescriptor(name);

		if (!descriptor)
		{
			report('"' + name + '" is not in the catalog. Known: '
				+ catalog.map(entry => entry.name).join(', ') + '.');
			continue;
		}

		const props = readProps(el, descriptor);
		if (props === null)
			continue;

		// A fresh marker gets a key and loses its inner HTML. Teleport adds its children
		// and keeps the existing ones, so a fallback text would stay under the component.
		// A marker that already carries a key holds a mounted component. Do not clear it.
		if (!el.dataset.fhcComponentKey)
		{
			el.dataset.fhcComponentKey = String(++keyCounter);
			el.innerHTML = '';
		}

		markers.push({
			el: el,
			key: el.dataset.fhcComponentKey,
			name: name,
			props: props
		});
	}

	return markers;
}
