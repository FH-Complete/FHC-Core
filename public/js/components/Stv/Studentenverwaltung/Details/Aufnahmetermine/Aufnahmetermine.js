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
					ApiStvAdmissionDates.getAufnahmetermin(this.student.prestudent_id)
				),
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title: "aufnahmetermin_id", field: "aufnahmetermin_id"},
					{title: "prestudent_id", field: "prestudent_id"},
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
		
	</div>
	`
}
