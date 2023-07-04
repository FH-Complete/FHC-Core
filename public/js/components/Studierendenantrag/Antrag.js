import StudierendenantragAbmeldung from './Form/Abmeldung.js';
import StudierendenantragUnterbrechung from './Form/Unterbrechung.js';
import StudierendenantragWiederholung from './Form/Wiederholung.js';
import Phrasen from '../../mixins/Phrasen.js';

export default {
	components: {
		StudierendenantragAbmeldung,
		StudierendenantragUnterbrechung,
		StudierendenantragWiederholung
	},
	mixins: [
		Phrasen
	],
	emits: [
		'update:infoArray',
		'update:statusMsg',
		'update:statusSeverity'
	],
	props: {
		antragType: String,
		prestudentId: Number,
		studierendenantragId: Number,
		infoArray: Array,
		statusMsg: String,
		statusSeverity: String
	},
	data() {
		return {
			status: ''
		};
	},
	computed: {
		typeComponent() {
			return 'Studierendenantrag' + this.antragType;
		},
		infoText() {
			return this.p.t('studierendenantrag/info_' + this.antragType + '_' + this.status);
		}
	},
	template: `
	<div class="studierendenantrag-antrag card">
		<div class="card-header">
		    {{p.t('studierendenantrag', 'title_' + antragType)}}
		</div>
		<div v-if="infoText && infoText.substr(0, 9) != '<< PHRASE'" class="alert alert-primary m-3" role="alert" v-html="infoText">
		</div>
		<component
			:is="typeComponent"
			class="card-body"
			v-model:status="status"
			:prestudent-id="prestudentId"
			:studierendenantrag-id="studierendenantragId"
			@setInfos="$emit('update:infoArray', $event)"
			@setStatus="$emit('update:statusMsg', $event.msg);$emit('update:statusSeverity', $event.severity)"
			>
		</component>
	</div>
	`
}
