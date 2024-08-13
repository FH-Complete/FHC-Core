import {CoreFilterCmpt} from "../../filter/Filter.js";
import ListNew from './List/New.js';


export default {
	components: {
		CoreFilterCmpt,
		ListNew
	},
	inject: [
		'lists'
	],
	props: {
		selected: Array,
		studiengangKz: Number,
		studiensemesterKurzbz: String
	},
	emits: [
		'update:selected'
	],
	data() {
		function dateFormatter(cell)
		{
			let val = cell.getValue();
			if (!val)
				return '&nbsp;';
			let date = new Date(val);
			return date.toLocaleDateString();
		}

		return {
			tabulatorOptions: {
				columns:[
					{title:"UID", field:"uid"},
					{title:"TitelPre", field:"titelpre"},
					{title:"Nachname", field:"nachname"},
					{title:"Vorname", field:"vorname"},
					{title:"Wahlname", field:"wahlname", visible:false},
					{title:"Vornamen", field:"vornamen", visible:false},
					{title:"TitelPost", field:"titelpost"},
					{title:"SVNR", field:"svnr"},
					{title:"Ersatzkennzeichen", field:"ersatzkennzeichen"},
					{title:"Geburtsdatum", field:"gebdatum", formatter:dateFormatter},
					{title:"Geschlecht", field:"geschlecht"},
					{title:"Sem.", field:"semester"},
					{title:"Verb.", field:"verband"},
					{title:"Grp.", field:"gruppe"},
					{title:"Studiengang", field:"studiengang"},
					{title:"Studiengang_kz", field:"studiengang_kz", visible:false},
					{title:"Personenkennzeichen", field:"matrikelnr"},
					{title:"PersonID", field:"person_id"},
					{title:"Status", field:"status"},
					{title:"Status Datum", field:"status_datum", visible:false, formatter:dateFormatter},
					{title:"Status Bestaetigung", field:"status_bestaetigung", visible:false, formatter:dateFormatter},
					{title:"EMail (Privat)", field:"mail_privat", visible:false},
					{title:"EMail (Intern)", field:"mail_intern", visible:false},
					{title:"Anmerkungen", field:"anmerkungen", visible:false},
					{title:"AnmerkungPre", field:"anmerkung", visible:false},
					{title:"OrgForm", field:"orgform_kurzbz"},
					{title:"Aufmerksamdurch", field:"aufmerksamdurch_kurzbz", visible:false},
					{title:"Gesamtpunkte", field:"punkte", visible:false},
					{title:"Aufnahmegruppe", field:"aufnahmegruppe_kurzbz", visible:false},
					{title:"Dual", field:"dual", visible:false, formatter:'tickCross', formatterParams: {
						tickElement: '<i class="fas fa-check text-success"></i>',
						crossElement: '<i class="fas fa-times text-danger"></i>'
					}},
					{title:"Matrikelnummer", field:"matr_nr", visible:false},
					{title:"Studienplan", field:"studienplan_bezeichnung"},
					{title:"PreStudentInnenID", field:"prestudent_id"},
					{title:"Priorität", field:"priorisierung_relativ"},
					{title:"Mentor", field:"mentor", visible:false},
					{title:"Aktiv", field:"bnaktiv", visible:false, formatter:'tickCross', formatterParams: {
						allowEmpty:true,
						tickElement: '<i class="fas fa-check text-success"></i>',
						crossElement: '<i class="fas fa-times text-danger"></i>'
					}},
				],
				rowFormatter(row) {
					if (row.getData().bnaktiv === false) {
						row.getElement().classList.add('text-muted');
					}
				},

				ajaxResponse: (url, params, response) => response.data,

				layout: 'fitDataStretch',
				layoutColumnsOnNewData: false,
				height: '100%',
				selectable: true,
				selectableRangeMode: 'click',
				index: 'prestudent_id',
				persistenceID: 'stv-list'
			},
			tabulatorEvents: [
				{
					event: 'rowSelectionChanged',
					handler: this.rowSelectionChanged
				},
				{
					event: 'dataProcessed',
					handler: this.autoSelectRows
				},
				{
					event: 'rowClick',
					handler: this.handleRowClick // TODO(chris): this should be in the filter component
				}
			],
			focusObj: null, // TODO(chris): this should be in the filter component
			lastSelected: null,
			filterKontoCount0: undefined,
			filterKontoMissingCounter: undefined
		}
	},
	methods: {
		reload() {
			this.$refs.table.reloadTable();
		},
		actionNewPrestudent() {
			this.$refs.new.open();
		},
		rowSelectionChanged(data) {
			this.lastSelected = this.selected;
			this.$emit('update:selected', data);
		},
		autoSelectRows(data) {
			if (this.lastSelected) {
				// NOTE(chris): reselect rows on refresh
				let selected = this.lastSelected.map(el => this.$refs.table.tabulator.getRow(el.prestudent_id))
				// TODO(chris): unselect current item if it's no longer in the table?
				// or maybe reselect only the last one?
				selected = selected.filter(el => el);

				if (selected.length)
					this.$refs.table.tabulator.selectRow(selected);
			} else if(this.lastSelected === undefined) {
				// NOTE(chris): select row if it's the only one (preferably only on startup)
				if (data.length == 1) {
					this.$refs.table.tabulator.selectRow(this.$refs.table.tabulator.getRows());
				}
			}
		},
		updateUrl(url, first) {
			this.lastSelected = first ? undefined : this.selected;

			const params = {}, filter = {};
			if (this.filterKontoCount0)
				filter.konto_count_0 = this.filterKontoCount0;
			if (this.filterKontoMissingCounter)
				filter.konto_missing_counter = this.filterKontoMissingCounter;

			if (filter.konto_count_0 || filter.konto_missing_counter)
				params.filter = filter;

			if (!this.$refs.table.tableBuilt) {
				if (!this.$refs.table.tabulator) {
					this.tabulatorOptions.ajaxURL = url;
					this.tabulatorOptions.ajaxParams = params;
				} else
					this.$refs.table.tabulator.on("tableBuilt", () => {
						this.$refs.table.tabulator.setData(url, params);
					});
			} else
				this.$refs.table.tabulator.setData(url, params);
		},
		onKeydown(e) { // TODO(chris): this should be in the filter component
			if (!this.focusObj)
				return;
			switch (e.code) {
				case 'Enter':
				case 'Space':
					e.preventDefault();
					const e2 = new Event('click', e);
					e2.altKey = e.altKey;
					e2.ctrlKey = e.ctrlKey;
					e2.shiftKey = e.shiftKey;
					this.focusObj.dispatchEvent(e2);
					//row.component.toggleSelect();
					break;
				case 'ArrowUp':
					e.preventDefault();
					var next = this.focusObj.previousElementSibling;
					if (next)
						this.changeFocus(this.focusObj, next);
					break;
				case 'ArrowDown':
					e.preventDefault();
					var next = this.focusObj.nextElementSibling;
					if (next)
						this.changeFocus(this.focusObj, next);
					break;
			}
		},
		changeFocus(a, b) { // TODO(chris): this should be in the filter component
			if (b) {
				b.tabIndex = 0;
				this.focusObj = b;
				b.focus();
			} else {
				this.focusObj = null;
			}
			a.tabIndex = -1;
			return this.focusObj;
		},
		onFocus(e) { // TODO(chris): this should be in the filter component
			if (!this.focusObj) {
				var container, target;
				if (e.target.classList.contains('tabulator-container')) {
					container = e.target;
					target = container.querySelector('.tabulator-row');
				} else if (e.target.classList.contains('tabulator-row')) {
					container = e.target.closest('.tabulator-container');
					target = e.target;
				}
				if (container && target) {
					this.changeFocus(container, target);
				}
			}
		},
		handleRowClick(e, row) { // TODO(chris): this should be in the filter component
			if (this.focusObj) {
				let el = row.getElement();
				if (el != this.focusObj)
					this.changeFocus(this.focusObj, el);
			}
		}
	},
	// TODO(chris): focusin, focusout, keydown and tabindex should be in the filter component
	// TODO(chris): filter component column chooser has no accessibilty features
	template: `
	<div class="stv-list h-100 pt-3">
		<div class="tabulator-container d-flex flex-column h-100" :class="{'has-filter': filterKontoCount0 || filterKontoMissingCounter}" tabindex="0" @focusin="onFocus" @keydown="onKeydown">
			<core-filter-cmpt
				ref="table"
				:tabulator-options="tabulatorOptions"
				:tabulator-events="tabulatorEvents"
				table-only
				:side-menu="false"
				reload
				` + /* TODO(chris): Ausgeblendet für Testing
				new-btn-show
				*/`
				:new-btn-label="$p.t('stv/action_new')"
				@click:new="actionNewPrestudent"
			>
			<template #filter>
				<div class="card">
					<div class="card-body">
						<div class="input-group mb-3">
							<label class="input-group-text col-4" for="stv-list-filter-konto-count-0">{{ $p.t('stv/konto_filter_count_0') }}</label>
							<select class="form-select" id="stv-list-filter-konto-count-0" v-model="filterKontoCount0" @input="$nextTick(updateUrl)">
								<option v-for="typ in lists.buchungstypen" :key="typ.buchungstyp_kurzbz" :value="typ.buchungstyp_kurzbz">
									{{ typ.beschreibung }}
								</option>
							</select>
							<button v-if="filterKontoCount0" class="btn btn-outline-secondary" @click="filterKontoCount0 = undefined; updateUrl()">
								<i class="fa fa-times"></i>
							</button>
						</div>
						<div class="input-group">
							<label class="input-group-text col-4" for="stv-list-filter-konto-missing-counter">{{ $p.t('stv/konto_filter_missing_counter') }}</label>
							<select class="form-select" id="stv-list-filter-konto-missing-counter" v-model="filterKontoMissingCounter" @input="$nextTick(updateUrl)">
								<option value="alle">{{ $p.t('stv/konto_all_types') }}</option>
								<option v-for="typ in lists.buchungstypen" :key="typ.buchungstyp_kurzbz" :value="typ.buchungstyp_kurzbz">
									{{ typ.beschreibung }}
								</option>
							</select>
							<button v-if="filterKontoMissingCounter" class="btn btn-outline-secondary" @click="filterKontoMissingCounter = undefined; updateUrl()">
								<i class="fa fa-times"></i>
							</button>
						</div>
					</div>
				</div>
			</template>
			</core-filter-cmpt>
		</div>
		<list-new ref="new" :studiengang-kz="studiengangKz" :studiensemester-kurzbz="studiensemesterKurzbz"></list-new>
	</div>`
};