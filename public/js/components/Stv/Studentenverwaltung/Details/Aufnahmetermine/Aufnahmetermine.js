import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import BsModal from "../../../../Bootstrap/Modal.js";
import FormForm from '../../../../Form/Form.js';
import FormInput from '../../../../Form/Input.js';

import ApiStvAdmissionDates from '../../../../../api/factory/stv/admissionDates';

export default {
	name: 'ListAdmissionDates',
	components: {
		CoreFilterCmpt,
		BsModal,
		FormForm,
		FormInput
	},
	inject: {
		$reloadList: {
			from: '$reloadList',
			required: true
		},
	},
	props: {
		student: Object
	},
	data() {
		return {
			tabulatorOptions: {
				ajaxURL: 'dummy',
				ajaxRequestFunc: () => this.$api.call(
					ApiStvAdmissionDates.getAufnahmetermine(this.student.person_id)
				),
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title: "rt_id", field: "rt_id"},
					{title: "rt_person_id", field: "rt_person_id"},
					{title: "person_id", field: "person_id"},
					{title: "datum", field: "datum"},
					{title: "stufe", field: "stufe"},
					{title: "studiensemester", field: "studiensemester_kurzbz"},
					{title: "anmerkung", field: "anmerkung", visible: false},
					{title: "anmeldedatum", field: "anmeldedatum", visible: false},
					{title: "punkte", field: "punkte"},
					{title: "teilgenommen", field: "teilgenommen"},
					{title: "ort", field: "ort", visible: false},
					{title: "studienplan", field: "studienplan", visible: false},
					{title: "studienplan_id", field: "studienplan_id", visible: false},
					{title: "stg_kuerzel", field: "stg_kuerzel"},
				],
				layout: 'fitDataFill',
				layoutColumnsOnNewData: false,
				height: 'auto',
				minHeight: 200,
				index: 'aufnahmetermin_id',
				persistenceID: 'stv-details-table_admission-dates'
			},
			tabulatorEvents: [],
			formData: {},
			statusNew: true,
		}
	},
	methods: {},
	created() {
	},
	template: `
	<div class="stv-details-admission-dates-table h-100 pb-3">
		<h4>Allgemein</h4>
		
		<core-filter-cmpt
			ref="table"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			table-only
			:side-menu="false"
			reload
			new-btn-show
			:new-btn-label="this.$p.t('lehre', 'reihungstest')"
			@click:new="actionNewMobility"
			>
		</core-filter-cmpt>
		
	</div>
	`
}
