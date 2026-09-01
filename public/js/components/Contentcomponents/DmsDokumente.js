import ApiCms from '../../api/factory/cms.js';
import DokumentListe from './DokumentListe.js';

/**
 * Content component: named DMS documents, in the order the editor gave.
 */
export default {
	name: 'DmsDokumente',
	components: {
		DokumentListe
	},
	props: {
		dmsIds: {
			type: String,
			required: true
		}
	},
	data() {
		return {
			dokumente: [],
			loading: true,
			failed: false
		};
	},
	methods: {
		load() {
			this.loading = true;
			this.failed = false;
			this.$api
				.call(ApiCms.getDmsDokumente(this.dmsIds))
				.then(res => { this.dokumente = res.data || []; })
				.catch(() => { this.failed = true; })
				.finally(() => { this.loading = false; });
		}
	},
	watch: {
		dmsIds() { this.load(); }
	},
	created() {
		this.load();
	},
	template: /*html*/ `
		<div class="fhc-contentcomponent-dmsdokumente">
			<div v-if="loading" class="text-muted">...</div>
			<div v-else-if="failed" class="alert alert-warning py-2">
				Die Dokumentenliste konnte nicht geladen werden.
			</div>
			<!-- An empty list renders nothing. Every named document may have dropped out
			     through the category rule, and that is not the reader's business. -->
			<dokument-liste
				v-else-if="dokumente.length"
				:dokumente="dokumente"
			></dokument-liste>
		</div>
	`
};
