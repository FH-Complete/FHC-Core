import {CoreFilterCmpt} from "../../../filter/Filter.js";
import FormInput from "../../../Form/Input.js";
import KontoNew from "./Konto/New.js";
import KontoEdit from "./Konto/Edit.js";

const LOCAL_STORAGE_ID_FILTER = 'stv_details_konto_2024-01-11_filter';

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
			studiengang_kz: false,
			counterdate: new Date()
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
			const columns = { ...this.config.columns };
			if (!columns.actions)
				columns.actions = {
					title: '',
					frozen: true
				};
			columns.actions.formatter = cell => {
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
				button.addEventListener('click', evt => {
					evt.stopPropagation();
					this.$fhcAlert
						.confirmDelete()
						.then(result => result ? cell.getData().buchungsnr : Promise.reject({handled:true}))
						.then(this.$fhcApi.factory.stv.konto.delete)
						.then(() => {
							// TODO(chris): deleting a child also removes the siblings!
							//cell.getRow().delete();
							this.reload();
						})
						.catch(this.$fhcAlert.handleSystemError);
				});
				container.append(button);

				return container;
			};

			return Object.values(columns);
		},
		tabulatorOptions() {
			return this.$fhcApi.factory.stv.konto.tabulatorConfig({
				dataTree: true,
				columns: this.tabulatorColumns,
				selectable: true,
				selectableRangeMode: 'click',
				index: 'buchungsnr',
				persistenceID: 'stv-details-konto'
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
			// TODO(chris): check children (!delete?, multiple children)
			//this.$refs.table.tabulator.updateOrAddData(data.map(row => row.buchungsnr_verweis ? {buchungsnr:row.buchungsnr_verweis, _children:row} : row));
			this.$refs.table.tabulator.updateOrAddData(data);
		},
		actionNew() {
			this.$refs.new.open();
		},
		actionCounter(selected) {
			this.$fhcApi
				.factory.stv.konto.counter({
					buchungsnr: selected.map(e => e.buchungsnr),
					buchungsdatum: this.counterdate
				})
				.then(result => result.data)
				.then(this.updateData)
				.then(() => this.$p.t('ui/gespeichert'))
				.then(this.$fhcAlert.alertSuccess)
				.catch(this.$fhcAlert.handleSystemError);
		},
		downloadPdf(selected) {
			if (Array.isArray(this.modelValue)) {
				let id_uid = this.modelValue.reduce((a,c) => {
					if (c.uid)
						a[c.person_id] = c.uid;
					return a
				}, {});
				let persons = selected.reduce((a,c) => {
					if (!a[c.person_id]) {
						let uid = id_uid[c.person_id] || '';
						a[c.person_id] = uid + '&buchungsnummern=' + c.buchungsnr;
					} else {
						a[c.person_id] += ';' + c.buchungsnr;
					}
					return a;
				}, {});
				Object.values(persons).forEach(part => window.open(
					FHC_JS_DATA_STORAGE_OBJECT.app_root +
					'content/pdfExport.php?xml=konto.rdf.php&xsl=Zahlung&uid=' +
					part,
					'_blank'
				));
			} else {
				window.open(
					FHC_JS_DATA_STORAGE_OBJECT.app_root +
					'content/pdfExport.php?xml=konto.rdf.php&xsl=Zahlung&uid=' +
					(this.modelValue.uid || '') +
					'&buchungsnummern=' +
					selected.map(row => row.buchungsnr).join(';'),
					'_blank'
				);
			}
		},
		setFilter(type) {
			if (type == 'open')
				window.localStorage.setItem(LOCAL_STORAGE_ID_FILTER, this.filter ? 1 : 0);
			else if (type == 'current_stg')
				this.$fhcApi.factory
					.stv.filter.setStg(this.studiengang_kz)
					.catch(this.$fhcAlert.handleSystemError);

			this.$nextTick(this.$refs.table.reloadTable);
		}
	},
	created() {
		this.filter = window.localStorage.getItem(LOCAL_STORAGE_ID_FILTER) == 1;
		this.$fhcApi.factory
			.stv.filter.getStg()
			.then(result => this.studiengang_kz = result.data)
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: `
	<div class="stv-details-konto h-100 d-flex flex-column">
		<div class="row justify-content-end">
			<div class="col-lg-3">
				<form-input
					container-class="form-switch"
					type="checkbox"
					:label="$p.t('stv/konto_filter_open')"
					v-model="filter"
					@update:model-value="setFilter('open')"
					>
				</form-input>
			</div>
			<div class="col-lg-3">
				<form-input
					container-class="form-switch"
					type="checkbox"
					:label="$p.t('stv/konto_filter_current_stg')"
					v-model="studiengang_kz_intern"
					:disabled="!stg_kz"
					@update:model-value="setFilter('current_stg')"
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
			:new-btn-label="$p.t('konto/buchung')"
			:new-btn-disabled="stg_kz === ''"
			@click:new="actionNew"
			>
			<template #actions="{selected}">
				<div class="input-group w-auto">
					<form-input
						type="DatePicker"
						v-model="counterdate"
						input-group
						:enable-time-picker="false"
						auto-apply
						@cleared="counterdate = new Date()"
						>
					</form-input>
					<button
						class="btn btn-outline-secondary"
						@click="actionCounter(selected)"
						:disabled="!selected.length"
						>
						{{ $p.t('stv/konto_counter') }}
					</button>
				</div>
				<button
					v-if="config.showZahlungsbestaetigung"
					class="btn btn-outline-secondary"
					@click="downloadPdf(selected)"
					:disabled="!selected.length"
					>
					<i class="fa fa-download"></i> {{ $p.t('stv/konto_payment_confirmation') }}
				</button>
			</template>
		</core-filter-cmpt>
		<konto-new ref="new" :config="config" @saved="updateData" :person-ids="personIds" :stg-kz="stg_kz"></konto-new>
		<konto-edit ref="edit" :config="config" @saved="updateData"></konto-edit>
	</div>`
};