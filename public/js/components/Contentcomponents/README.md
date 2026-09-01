# Contentcomponents

Vue components that CMS editors can place inside CMS content. The editor inserts a marker
div, CIS4 finds it and teleports a real component into it. The component fetches its own
data and has everything a normal component has: `$api`, `$p`, the router, the provided
keys.

## The marker

```html
<div class="fhc-contentcomponent" data-fhc-component="dms-liste" data-kategorie-kurzbz="rl"></div>
```

Editors do not write this. The **Contentcomponent** button in TinyMCE 5 opens a dialog
built from `catalog.js`.

## Add a component

| File | What to add |
|---|---|
| `MeinBaustein.js` | the component |
| `catalog.js` | an entry in `catalog`: name, label, props |
| `components.js` | the import plus `'mein-baustein': MeinBaustein` |

Catalog and map are two hand-written lists. `components.js` logs a mismatch on load.

### The component

Copy `DmsListe.js`, the shortest complete example. Requirements:

- It stands alone. No parent props, no route params.
- It fetches in `created()`, and again from a `watch` on every prop.
- It handles loading, failure and empty.
- Root class `fhc-contentcomponent-<name>`.
- `template` string. There is no `.vue` build.

To get the id of the page the marker sits on:

```js
inject: { contentcomponentContentId: { default: null } }
```

`ContentchildMenu.js` shows the full pattern.

Presentational helpers belong in this folder but not in the catalog. See `PersonBlock.js`
and `DokumentListe.js`.

### The catalog entry

```js
{
	name: 'mein-baustein',    // data-fhc-component, and the key in components.js
	label: 'Mein Baustein',   // shown in the picker
	props: {
		'mein-wert': { type: 'string', required: true, label: 'Mein Wert' }
	}
}
```

The catalog is an allow-list. `scan.js` reads only the attributes declared here and drops
every other one.

| Prop key | Meaning |
|---|---|
| `type` | `string`, `number` or `boolean`. Anything else stays a string. |
| `required` | A missing attribute makes `scan.js` skip the whole marker. The dialog does not enforce it. |
| `label` | Field label in the dialog. It defaults to the attribute name. |
| `source` | Fills the field from a server list: `oe`, `person`, `dmskategorie`. |
| `picker` | Only `'dms'`. Adds category and document selects that append ids to the field. |

## Gotchas

- **kebab-case attribute names.** The HTML parser lowercases them, so `data-meinWert` does
  not survive. Vue maps `mein-wert` to the prop `meinWert`.
- **`boolean` means `"true"` or `"1"`.** Everything else is false, and the dialog never
  writes a false into the marker.
- **Catalog name is not the file name.** `person-block` resolves to `PersonKontakt.js`.
- **The marker is a `div`, never a custom tag.** TinyMCE 3 in the legacy admin treats an
  unknown tag as inline and can split it on save.
- **20 components per content** — `CONTENTCOMPONENT_MAX` in `catalog.js`.
- **Content is never compiled as a Vue template.** An editor cannot write expressions.
- **News renders no components on purpose.** A list shows many contents at once, and every
  component would start its own request.
- A throwing component does not take the content down. The host catches it and logs it.

## Server data

Model, controller, factory. Follow `getDmsKategorie`.

1. `application/models/content/Dms_model.php` — the query.
2. `application/controllers/api/frontend/v1/Cms.php` — the method, plus
   `'getMeineDaten' => self::PERM_LOGGED` in the constructor. Validate with
   `form_validation`.
3. `public/js/api/factory/cms.js` — the request function.

A missing entitlement answers with success and an empty list, not with an error. The
reader gets no hint that something is hidden.

## A new dialog source

1. `CmsAdmin.php` — the endpoint, plus `['basis/cms:r']` in the constructor.
2. `cmsadmin.js` — the request function.
3. `ContentcomponentDialog.js` — an entry in `SOURCES`:

```js
meineliste: {
	request: () => ApiCmsAdmin.getMeineListe(),
	item: e => ({ value: e.kurzbz, text: e.bezeichnung })
}
```

4. `source: 'meineliste'` in the catalog prop.

