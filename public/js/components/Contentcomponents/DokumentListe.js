// Mimetype to Font Awesome 6 icon. The first match wins.
const ICONS = [
	[/pdf/,                             'fa-file-pdf'],
	[/word|opendocument\.text/,         'fa-file-word'],
	[/excel|sheet|csv/,                 'fa-file-excel'],
	[/powerpoint|presentation/,         'fa-file-powerpoint'],
	[/^image\//,                        'fa-file-image'],
	[/zip|compressed|tar|rar|7z/,       'fa-file-zipper'],
	[/^video\//,                        'fa-file-video'],
	[/^audio\//,                        'fa-file-audio'],
	[/^text\//,                         'fa-file-lines']
];

/**
 * Renders a list of DMS documents as download links.
 *
 * It fetches nothing. dms-liste and dms-dokumente both fill it, so a document reads the
 * same whether the editor named a category or single ids.
 */
export default {
	name: 'DokumentListe',
	props: {
		dokumente: {
			type: Array,
			default: () => []
		}
	},
	methods: {
		icon(mimetype) {
			const typ = (mimetype || '').toLowerCase();
			const treffer = ICONS.find(([muster]) => muster.test(typ));
			return treffer ? treffer[1] : 'fa-file';
		},
		href(dokument) {
			return FHC_JS_DATA_STORAGE_OBJECT.app_root + 'cms/dms.php?id=' + dokument.dms_id;
		},
		datum(wert) {
			if (!wert) return '';
			const teile = String(wert).slice(0, 10).split('-');
			return teile.length === 3 ? teile[2] + '.' + teile[1] + '.' + teile[0] : '';
		}
	},
	template: /*html*/ `
		<ul class="list-unstyled mb-0">
			<li v-for="dokument in dokumente" :key="dokument.dms_id" class="mb-2">
				<a :href="href(dokument)" target="_blank" rel="noopener">
					<i class="fa-regular" :class="icon(dokument.mimetype)"></i>
					{{ dokument.name }}
				</a>
				<span v-if="dokument.geaendert" class="text-muted small ms-2">
					{{ datum(dokument.geaendert) }}
				</span>
				<div v-if="dokument.beschreibung" class="text-muted small">
					{{ dokument.beschreibung }}
				</div>
			</li>
		</ul>
	`
};
