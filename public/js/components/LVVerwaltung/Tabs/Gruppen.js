import GruppenTable from '../Details/Gruppen.js';
import GruppenDirektTable from '../Details/Direktinskription.js';

export default {
	name: "LVTabGruppen",
	components: {
		GruppenTable,
		GruppenDirektTable,
	},
	props: {
		modelValue: Object,
		config: {
			type: Object,
			default: {}
		},
	},
	inject: {
		dropdowns: {
			from: 'dropdowns'
		}
	},

	template: `

		<fieldset class="overflow-hidden">
			<div class="row">
				<div class="col-6">
					<legend>{{this.$p.t('lehre', 'gruppen')}}</legend>
					<gruppen-table ref="gruppen_table" :lehreinheit_id="modelValue.lehreinheit_id"></gruppen-table>
				</div>
				<div class="col-6">
					<legend>{{this.$p.t('lehre', 'assignedPersons')}}</legend>
					<gruppen-direkt-table ref="gruppen_direkt_table" :lehreinheit_id="modelValue.lehreinheit_id"></gruppen-direkt-table>
				</div>
			</div>
			
		</fieldset>
	`
};