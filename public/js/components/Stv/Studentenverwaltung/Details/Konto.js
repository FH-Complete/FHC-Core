import {CoreFilterCmpt} from "../../../filter/Filter.js";
import FormInput from "../../../Form/Input.js";
import KontoNew from "./Konto/New.js";
import KontoEdit from "./Konto/Edit.js";

// TODO(chris): Phrasen
// TODO(chris): multi pers
// TODO(chris): gegenb.(date) multi, löschen multi, best. multi(recht)

export default {
	components: {
		CoreFilterCmpt,
		FormInput,
		KontoNew,
		KontoEdit
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
			filter: false,
			studiengang_kz: false
		};
	},
	computed: {
		personIds() {
			if (this.modelValue.person_id)
				return [this.modelValue.person_id];
			return this.modelValue.map(e => e.person_id);
		},
		stg_kz() {
			if (this.modelValue.studiengang_kz)
				return this.modelValue.studiengang_kz;
			let values = this.modelValue.map(e => e.studiengang_kz).filter((v,i,a) => a.indexOf(v) === i);
			if (values.length != 1)
				return '';
			return values[0];
		},
		studiengang_kz_intern: {
			get() {
				if (this.stg_kz)
					return this.studiengang_kz;
				else
					return false;
			},
			set(value) {
				this.studiengang_kz = value;
			}
		},
		tabulatorColumns() {
			let columns = [];
			
			if (Array.isArray(this.modelValue)) {
				columns.push({
					field: "person_id",
					title: "Person ID"
				});
				columns.push({
					field: "anrede",
					title: "Anrede",
					visible: false
				});
				columns.push({
					field: "titelpost",
					title: "Titelpost",
					visible: false
				});
				columns.push({
					field: "titelpre",
					title: "Titelpre",
					visible: false
				});
				columns.push({
					field: "vorname",
					title: "Vorname"
				});
				columns.push({
					field: "vornamen",
					title: "Vornamen",
					visible: false
				});
				columns.push({
					field: "nachname",
					title: "Nachname"
				});
			}

			columns = [...columns, ...[
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
					title: "Buchungs Nr",
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
			]];

			columns = [...columns, ...this.config.additionalCols];

			columns.push({
				title: 'Actions',
				formatter: cell => {
					let container = document.createElement('div');
					container.className = "d-flex gap-2";

					let button = document.createElement('button');
					button.className = 'btn btn-outline-secondary';
					button.innerHTML = '<i class="fa fa-edit"></i>';
					button.addEventListener('click', () =>
						this.$refs.edit.open(cell.getData())
					);
					container.append(button);

					button = document.createElement('button');
					button.className = 'btn btn-outline-secondary';
					button.innerHTML = '<i class="fa fa-trash"></i>';
					button.addEventListener('click', () =>
						this.$fhcAlert
							.confirmDelete()
							.then(result => result ? cell.getData().buchungsnr : Promise.reject({handled:true}))
							.then(this.$fhcApi.factory.stv.konto.delete)
							.then(() => cell.getRow().delete())
							.catch(this.$fhcAlert.handleSystemError)
					);
					container.append(button);

					return container;
				},
				frozen: true
			});
			return columns;
		},
		tabulatorOptions() {
			return this.$fhcApi.factory.stv.konto.tabulatorConfig({
				dataTree: true,
				columns: this.tabulatorColumns,
				selectable: true,
				selectableRangeMode: 'click',
				index: 'buchungsnr',
			}, this);
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
		},
		updateData(data) {
			if (!data)
				return this.reload();
			this.$refs.table.tabulator.updateOrAddData(data);
		},
		actionNew() {
			this.$refs.new.open();
		}
	},
	created() {
		// TODO(chris): persist filter + studiengang_kz
	},
	template: `
	<div class="stv-details-konto h-100 d-flex flex-column">
		<div class="row justify-content-end">
			<div class="col-lg-3">
				<form-input
					container-class="form-switch"
					type="checkbox"
					label="Nur offene anzeigen"
					v-model="filter"
					@update:model-value="() => $nextTick($refs.table.reloadTable)"
					>
				</form-input>
			</div>
			<div class="col-lg-3">
				<form-input
					container-class="form-switch"
					type="checkbox"
					label="Nur aktuellen Stg anzeigen"
					v-model="studiengang_kz_intern"
					:disabled="!stg_kz"
					@update:model-value="() => $nextTick($refs.table.reloadTable)"
					>
				</form-input>
			</div>
		</div>
		<core-filter-cmpt
			ref="table"
			table-only
			:side-menu="false"
			:tabulator-options="tabulatorOptions"
			reload
			new-btn-show
			new-btn-label="Buchung"
			:new-btn-disabled="stg_kz === ''"
			@click:new="actionNew"
			>
		</core-filter-cmpt>
		<konto-new ref="new" :config="config" @saved="updateData" :person-ids="personIds" :stg-kz="stg_kz"></konto-new>
		<konto-edit ref="edit" :config="config" @saved="updateData"></konto-edit>
	</div>`
};