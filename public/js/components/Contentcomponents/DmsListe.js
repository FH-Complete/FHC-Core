import ApiCms from '../../api/factory/cms.js';
import DokumentListe from './DokumentListe.js';

/**
 * Content component: the published documents of one DMS category.
 *
 * The low maintenance variant. It lists every document of the category whose newest
 * version carries cis_suche, so a replaced or added document appears without an edit in
 * the CMS. Use dms-dokumente when the editor must pick single files.
 */
export default {
	name: 'DmsListe',
	components: {
		DokumentListe
	},
	props: {
		kategorieKurzbz: {
			type: String,
			required: true
		}
	},
	data() {
		return {
			dokumente: [],
			zugriff: true,
			loading: true,
			failed: false
		};
	},
	methods: {
		load() {
			this.loading = true;
			this.failed = false;
			this.$api
				.call(ApiCms.getDmsKategorie(this.kategorieKurzbz))
				.then(res => {
					this.zugriff = res.data.zugriff;
					this.dokumente = res.data.dokumente || [];
				})
				.catch(() => { this.failed = true; })
				.finally(() => { this.loading = false; });
		}
	},
	watch: {
		kategorieKurzbz() { this.load(); }
	},
	created() {
		this.load();
	},
	template: /*html*/ `
		<div class="fhc-contentcomponent-dmsliste">
			<div v-if="loading" class="text-muted">...</div>
			<div v-else-if="failed" class="alert alert-warning py-2">
				Die Dokumentenliste konnte nicht geladen werden.
			</div>
			<!-- A reader without the entitlement sees nothing at all, not a hint that
			     something is hidden here. -->
			<template v-else-if="zugriff">
				<div v-if="!dokumente.length" class="text-muted">
					Keine Dokumente vorhanden.
				</div>
				<dokument-liste v-else :dokumente="dokumente"></dokument-liste>
			</template>
		</div>
	`
};
