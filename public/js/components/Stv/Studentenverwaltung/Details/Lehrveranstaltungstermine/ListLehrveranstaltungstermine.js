import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import FormInput from "../../../../Form/Input.js";
import FormForm from '../../../../Form/Form.js';
import { capitalize } from '../../../../../helpers/StringHelpers.js';

export default {
	name: "TblCourseList",
	components: {
		CoreFilterCmpt,
		FormInput,
		FormForm
	},
	inject: {
		currentSemester: {
			from: 'currentSemester',
		},
	},
	props: {
		id: {
			type: [Number, String],
			required: true
		},
		endpoint: {
			type: Object,
			required: true
		},
	},
	data(){
		return {
			tabulatorOptions: null,
			tabulatorEvents: [],
			listStudiensemester: [],
			dataSem: {},
			stundenplanEnvironment: 'prod'
		};
	},
	computed: {
		downloadLink: function(){
			if(!this.dataSem.start || !this.dataSem.ende || !this.student.uid) return;
			let start = new Date(this.dataSem.start);
			start = Math.floor(start.getTime()/1000);
			let ende = new Date(this.dataSem.ende);
			ende = Math.floor(ende.getTime() / 1000);

			let link =
				FHC_JS_DATA_STORAGE_OBJECT.app_root + "cis/private/lvplan/stpl_kalender.php?type=student&pers_uid=" + this.student.uid + "&begin=" + start + "&ende= " +ende + "&format=excel";
			return link;
		},
		dbStundenplanTable: function (){
			return this.stundenplanEnvironment === 'dev' ? 'stundenplandev' : 'stundenplan';
		},
	},
	methods: {
		initTabulatorOptions(){
			this.tabulatorOptions = {
				ajaxURL: 'dummy',
				ajaxRequestFunc: () => this.$api.call(this.endpoint.getCourselist(this.id, this.dataSem.start, this.dataSem.ende, this.dbStundenplanTable)),
				ajaxResponse: (url, params, response) => {
					return response.data;
				},
				persistenceID: 'stv-details-lvtermine-20260217',
				columns: [
					{title: "lv_id", field: "lehrveranstaltung_id", visible: false},
					{title: "lehreinheit_id", field: "lehreinheit_id", visible: false},
					{title: "datum", field: "datum",
						formatter: function (cell) {
							const dateStr = cell.getValue();
							if (!dateStr) return "";

							const date = new Date(dateStr);
							return date.toLocaleString("de-DE", {
								day: "2-digit",
								month: "2-digit",
								year: "numeric",
								hour12: false
							});
						}},
					{title: "beginn", field: "beginn"},
					{title: "ende", field: "ende"},
					{title: "farbe", field: "farbe", visible: false},
					{title: "Gruppen", field: "gruppen_kuerzel"},
					{title: "ort", field: "ort_kurzbz"},
					{title: "lektorIn", field: "lektorname", sorter: "string"},
					{title: "Lehrfach", field: "lehrfach_bez"}
				],
				rowFormatter: function(row){
					var data = row.getData();
					//highlight background of row if color red in table
					if(data.farbe == "A4A7FC"){
						let el = row.getElement();
						row.getElement().classList.add("highlight-row");
						row.getElement().classList.remove("tabulator-row-odd");
						row.getElement().classList.remove("tabulator-row-even");
					}
				}
			};
			this.tabulatorEvents = [
				{
					event: 'tableBuilt',
					handler: async() => {
						await this.$p.loadCategory(['global', 'ui', 'lehre']);

						const setHeader = (field, text) => {
							const col = this.$refs.table.tabulator.getColumn(field);
							if (!col) return;

							const el = col.getElement();
							if (!el || !el.querySelector) return;

							const titleEl = el.querySelector('.tabulator-col-title');
							if (titleEl) {
								titleEl.textContent = text;
							}
						};

						setHeader('lehrveranstaltung_id', capitalize(this.$p.t('lehre','lehrveranstaltung_id')));
						setHeader('lehreinheit_id', capitalize(this.$p.t('global','lehreinheit_id')));
						setHeader('datum', capitalize(this.$p.t('global','datum')));
						setHeader('beginn', capitalize(this.$p.t('ui','dateFrom')));
						setHeader('ende', capitalize(this.$p.t('ui','dateTo')));
						setHeader('gruppen_kuerzel', capitalize(this.$p.t('global','gruppen')));
						setHeader('ort_kurzbz', capitalize(this.$p.t('global','ortLocation')));
						setHeader('lektorname', capitalize(this.$p.t('lehre','lektor')));
						setHeader('lehrfach_bez', capitalize(this.$p.t('global','lehrfach')));
					}
				}
			];
		},
		getDatesOfSemester(studiensemester_kurzbz) {
			this.dataSem = this.listStudiensemester.find(item => item.studiensemester_kurzbz === studiensemester_kurzbz);
		},
		exportToExcel(){
			window.open(this.endpoint.exportCalendar(this.id, this.dbStundenplanTable), '_blank');
		},
		reload() {
			this.$refs.table.reloadTable();
		},
		switchStundenplan(){
			this.reload();
		}
	},
	watch: {
		currentSemester(newVal, oldVal) {
			this.getDatesOfSemester(newVal);
		},
		id() {
			this.reload();
		}
	},
	created(){
		this.$api
			.call(this.endpoint.getStudiensemester())
			.then(result => {
				this.listStudiensemester = result.data;
				this.getDatesOfSemester(this.currentSemester);
				this.initTabulatorOptions();

			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	template: `
		<div class="stv-details-courselist h-100 pb-3">
			<h4>{{$p.t('global', 'termine')}}</h4>

			<core-filter-cmpt
				v-if="tabulatorOptions"
				ref="table"
				:tabulator-options="tabulatorOptions"
				:tabulator-events="tabulatorEvents"
				table-only
				:side-menu="false"
				reload
				:reload-btn-infotext="this.$p.t('table', 'reload')"
				>
					<template #actions>
						<button
							class="btn btn-outline-secondary me-2"
							@click="exportToExcel">
								{{$p.t('ui', 'export')}}
						</button>

						<div class="btn-group" role="group">
							<input
								type="radio"
								class="btn-check"
								name="btnradio"
								id="btnradio_prod"
								value="prod"
								v-model="stundenplanEnvironment"
								@change="switchStundenplan()"
							>
							<label
								class="btn btn-outline-secondary"
								for="btnradio_prod"
							>
								{{$p.t('lehre', 'btn_stundenplanProd')}}
							</label>
							<input
								type="radio"
								class="btn-check"
								name="btnradio"
								id="btnradio_dev"
								value="dev"
								v-model="stundenplanEnvironment"
								@change="switchStundenplan()"
							>
							<label
								class="btn btn-outline-secondary"
								for="btnradio_dev"
							>
								{{$p.t('lehre', 'btn_stundenplanDev')}}
							</label>
						</div>
					</template>
			</core-filter-cmpt>
		</div>
	`
}
