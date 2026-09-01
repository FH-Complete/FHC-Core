import ApiCms from '../../api/factory/cms.js';
import PersonBlock from './PersonBlock.js';

/**
 * Content component: every person assigned to an organisation unit.
 *
 * The low maintenance variant. A new colleague appears without an edit in the CMS, and a
 * leaving one disappears. The price is that the editor cannot steer who is shown. Use
 * person-block when that control matters.
 *
 * The function label per person comes from tbl_benutzerfunktion.bezeichnung,
 * and falls back to tbl_funktion.beschreibung.
 */
export default {
	name: 'OePersonen',
	components: {
		PersonBlock
	},
	props: {
		oeKurzbz: {
			type: String,
			required: true
		},
		foto: {
			type: Boolean,
			default: false
		}
	},
	data() {
		return {
			personen: [],
			loading: true,
			failed: false
		};
	},
	methods: {
		load() {
			this.loading = true;
			this.failed = false;
			this.$api
				.call(ApiCms.getOePersonen(this.oeKurzbz, this.foto))
				.then(res => { this.personen = res.data || []; })
				.catch(() => { this.failed = true; })
				.finally(() => { this.loading = false; });
		}
	},
	watch: {
		oeKurzbz() { this.load(); },
		foto() { this.load(); }
	},
	created() {
		this.load();
	},
	template: /*html*/ `
		<div class="fhc-contentcomponent-oepersonen">
			<div v-if="loading" class="text-muted">...</div>
			<div v-else-if="failed" class="alert alert-warning py-2">
				Die Personen konnten nicht geladen werden.
			</div>
			<div v-else-if="!personen.length" class="text-muted">
				Keine Personen zugeordnet.
			</div>
			<template v-else>
				<person-block
					v-for="person in personen"
					:key="person.uid"
					:uid="person.uid"
					:name="person.name"
					:funktion="person.funktion"
					:telefon="person.telefon"
					:email="person.email"
					:ort="person.ort"
					:foto="person.foto"
				></person-block>
			</template>
		</div>
	`
};
