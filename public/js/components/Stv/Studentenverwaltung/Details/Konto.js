import {CoreFilterCmpt} from "../../../filter/Filter.js";

// TODO(chris): filter
// TODO(chris): multi pers
// TODO(chris): new header(multi pers), edit/row, gegenb.(date) multi, löschen multi, best. multi(recht)

export default {
	components: {
		CoreFilterCmpt
	},
	props: {
		modelValue: Object,
		config: {
			type: Object,
			default: {}
		}
	},
	data() {
		return {
			filter: 'alle'
		};
	},
	computed: {
		stg_kz() {
			if (this.modelValue.studiengang_kz)
				return this.modelValue.studiengang_kz;
			let values = this.modelValue.map(e => e.studiengang_kz).filter((v,i,a) => a.indexOf(v) === i);
			if (values.length != 1)
				return '';
			return values[0];
		},
		tabulatorOptions() {
			return {
				ajaxURL: 'api/frontend/v1/stv/konto/get/alle',
				ajaxParams: () => {
					const params = {
						person_id: this.modelValue.person_id || this.modelValue.map(e => e.person_id),
						only_open: (this.filter == 'offene')
					};
					return params;
				},
				ajaxRequestFunc: (url, config, params) => {
					return this.$fhcApi.post(url, params, config);
				},
				ajaxResponse: (url, params, response) => response.data,
				dataTree: true,
				columns: [
					{
						field: "buchungsdatum",
						title: "Buchungsdatum"
					},
					{
						field: "buchungstext",
						title: "Buchungstext"
					},
					{
						field: "betrag",
						title: "Betrag"
					},
					{
						field: "studiensemester_kurzbz",
						title: "StSem"
					},
					{
						field: "buchungstyp_kurzbz",
						title: "Typ",
						visible: false
					},
					{
						field: "buchungsnr",
						title: "buchungs_nr",
						visible: false
					},
					{
						field: "insertvon",
						title: "Angelegt von",
						visible: false
					},
					{
						field: "insertamum",
						title: "Anlagedatum",
						visible: false
					},
					{
						field: "kuerzel",
						title: "Studiengang",
						visible: false
					},
					{
						field: "anmerkung",
						title: "Anmerkung"
					}
				],
				index: 'buchungs_nr',
			};
		}
	},
	watch: {
		modelValue() {
			this.$refs.table.reloadTable();
		}
	},
	methods: {
		reload() {
			this.$refs.table.reloadTable();
		}
	},
	template: `
	<div class="stv-details-konto h-100 d-flex flex-column">
	{{config}}
		<div class="row">
			<div class="col-lg-2">
				<select class="form-select" v-model="filter" @input="() => $nextTick($refs.table.reloadTable)">
					<option value="alle">Alle</option>
					<option value="offene">Offene</option>
				</select>
			</div>
			<div class="col-lg-2">
				<select class="form-select" v-model="studiengang_kz" @input="() => $nextTick($refs.table.reloadTable)">
					<option value="">Alle</option>
					<option :value="stg_kz">Aktuelle</option>
				</select>
			</div>
		</div>
		<core-filter-cmpt
			ref="table"
			table-only
			:side-menu="false"
			:tabulator-options="tabulatorOptions"
			>
		</core-filter-cmpt>
	</div>`
};