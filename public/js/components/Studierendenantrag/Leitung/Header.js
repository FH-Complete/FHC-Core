import Phrasen from '../../../mixins/Phrasen.js';

export default {
	mixins: [Phrasen],
	props: {
		stgs: Array
	},
	emits: [
		'input'
	],
	template: `
	<div class="studierendenantrag-leitung-header fhc-table-header d-flex align-items-center mb-2 gap-2">
		<h3 class="h5 col m-0">{{p.t('studierendenantrag', 'studierendenantraege')}}</h3>
		<div v-if="stgs.length > 1" class="col-auto">
			<select ref="stg_select" class="form-select" @input="$emit('input', $event)">
				<option value="">{{p.t('global', 'alle')}}</option>
				<option v-for="stg in stgs" :key="stg.studiengang_kz" :value="stg.studiengang_kz">
					{{stg.bezeichnung}} ({{stg.orgform}})
				</option>
			</select>
		</div>
	</div>
	`
}
