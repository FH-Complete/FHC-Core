import ApiCms from '../../api/factory/cms.js';
import PersonBlock from './PersonBlock.js';

/**
 * Content component: one person, named by UID.
 *
 * The flexible variant. The editor decides who appears and in which order by placing
 * several markers. Use oe-personen instead when the list should follow the organizational structure.
 *
 * The function label comes from the marker, not from the database, because the database
 * name does not always fit. It needs no phrase key: the CMS holds one content per
 * language, so the German page carries the German label and the English page the English
 * one.
 */
export default {
	name: 'PersonKontakt',
	components: {
		PersonBlock
	},
	props: {
		uid: {
			type: String,
			required: true
		},
		funktion: {
			type: String,
			default: ''
		},
		foto: {
			type: Boolean,
			default: false
		}
	},
	data() {
		return {
			person: null,
			loading: true,
			failed: false
		};
	},
	methods: {
		load() {
			this.loading = true;
			this.failed = false;
			this.$api
				.call(ApiCms.getPerson(this.uid, this.foto))
				.then(res => { this.person = res.data; })
				.catch(() => { this.failed = true; })
				.finally(() => { this.loading = false; });
		}
	},
	watch: {
		uid() { this.load(); },
		foto() { this.load(); }
	},
	created() {
		this.load();
	},
	template: /*html*/ `
		<div class="fhc-contentcomponent-personkontakt">
			<div v-if="loading" class="text-muted">...</div>
			<div v-else-if="failed" class="alert alert-warning py-2">
				Die Person konnte nicht geladen werden.
			</div>
			<person-block
				v-else-if="person"
				:uid="person.uid"
				:name="person.name"
				:funktion="funktion"
				:telefon="person.telefon"
				:email="person.email"
				:ort="person.ort"
				:foto="person.foto"
			></person-block>
		</div>
	`
};
