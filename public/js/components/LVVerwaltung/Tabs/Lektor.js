import CoreForm from '../../Form/Form.js';
import FormInput from '../../Form/Input.js';
import LektorTable from '../Lektor/Table.js'
import LektorDaten from '../Lektor/Daten.js'
import LektorVertrag from '../Lektor/Vertrag.js'
export default {
	name: "LVTabLektor",
	components: {
		CoreForm,
		FormInput,
		LektorTable,
		LektorDaten,
		LektorVertrag
	},
	props: {
		modelValue: Object,
		config: {
			type: Object,
			default: {}
		},
	},
	data() {
		return {
			mitarbeiter_uid: null
		}
	},
	methods: {
		changedLektor(uid) {
			const lektorTable = this.$refs.lektor_table;
			const tabulator = lektorTable.$refs.table.tabulator;

			const selectUID = () => {
				let row = tabulator.getRows().find(r => r.getData().mitarbeiter_uid === uid);
				if (row)
				{
					row.select();
				}
				tabulator.off('dataProcessed', selectUID);
			};
			tabulator.on('dataProcessed', selectUID);
			lektorTable.reload();
		},
		changedCosts() {
			this.$refs.lektor_vertrag?.getLektorVertrag();
		},
		canceledVertrag() {
			this.$refs.lektor_daten?.getLektorData();
		}
	},
	template: `
	<fieldset class="overflow-hidden">
		<div class="row">
			<div class="col-5">
				<legend>{{ $p.t('lehre', 'lektorInnen')}}</legend>
				<lektor-table ref="lektor_table" :lehreinheit_id="modelValue.lehreinheit_id" v-model:selected="mitarbeiter_uid"></lektor-table>
			</div>
			<div class="col-5">
				<lektor-daten ref="lektor_daten" :lehreinheit_id="modelValue.lehreinheit_id" :mitarbeiter_uid="mitarbeiter_uid" @changedLektor="changedLektor" @changedCosts="changedCosts"></lektor-daten>
			</div>
		</div>
		<div class="row">
			<div class="col-5">
			</div>
			<div class="col-5">
				<lektor-vertrag ref="lektor_vertrag" :lehreinheit_id="modelValue.lehreinheit_id" :mitarbeiter_uid="mitarbeiter_uid" @canceledVertrag="canceledVertrag"></lektor-vertrag>
			</div>
		</div>
	</fieldset>
	`
};