import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import FormInput from "../../../../Form/Input.js";

export default{
	components: {
		CoreFilterCmpt,
		FormInput
	},
	inject: {
		defaultSemester: {
			from: 'defaultSemester',
		},
	},
	props: {
		uid: Number
	},
	data(){
		return {
			tabulatorOptions: {
				ajaxURL: 'api/frontend/v1/stv/pruefung/getPruefungen/' + this.uid,
				ajaxRequestFunc: this.$fhcApi.get,
				ajaxResponse: (url, params, response) => response.data,
				columns: [
					{title: "Datum", field: "format_datum"},
					{title: "Lehrveranstaltung", field: "lehrveranstaltung_bezeichnung"},
					{title: "Note", field: "note_bezeichnung"},
					{title: "Anmerkung", field: "anmerkung"},
					{title: "Typ", field: "pruefungstyp_kurzbz"},
					{title: "PruefungId", field: "pruefung_id", visible:false},
					{title: "LehreinheitId", field: "lehreinheit_id", visible:false},
					{title: "Student_uid", field: "student_uid", visible:false},
					{title: "Mitarbeiter_uid", field: "mitarbeiter_uid", visible:false},
					{title: "Punkte", field: "punkte", visible:false},
				],
				layout: 'fitDataFill',
				layoutColumnsOnNewData: false,
				height: 'auto',
			},
			tabulatorEvents: [{}],
			pruefungData: {},
			filter: false
		}
	},
	computed:{},
/*	watch: {
		modelValue() {
			this.$refs.table.reloadTable();
		}
	},*/
	methods:{ },
	template: `
	<div class="stv-details-pruefung-pruefung-list 100 pt-3">
		
		<div class="justify-content-end pb-3">
				<form-input
					container-class="form-switch"
					type="checkbox"
					label="Aktuelles Studiensemester Anzeigen"
					v-model="filter"
					@update:model-value="setFilter('open')"
					>
				</form-input>
<!--			<div class="col-lg-3">
				<form-input
					container-class="form-switch"
					type="checkbox"
					:label="$p.t('stv/konto_filter_current_stg')"
					v-model="studiengang_kz_intern"
					:disabled="!stg_kz"
					@update:model-value="setFilter('current_stg')"
					>
				</form-input>
			</div>-->
		</div>
	
		<div class="row">
					
			<div class="col-sm-6 pt-6">			
				<core-filter-cmpt
					ref="table"
					:tabulator-options="tabulatorOptions"
					table-only
					:side-menu="false"
					reload
					new-btn-show
					new-btn-label="Pruefung"
					@click:new="actionNewPruefung"
					>
				</core-filter-cmpt>
			</div>
			
			<div class="col-sm-6">
				<p>Form </p>
				
				aktuelles Sem: {{defaultSemester}}
							
			</div>
		</div>
	</div>`
};

