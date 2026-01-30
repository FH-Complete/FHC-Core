import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import FormInput from "../../../../Form/Input.js";
import FormForm from '../../../../Form/Form.js';

import ApiStvCoursedates from "../../../../../api/factory/stv/coursedates.js";

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
		student: Object
	},
	data(){
		return {
			tabulatorOptions: null,
			tabulatorEvents: [],
			listStudiensemester: [],
			dataSem: {},
			showStundenplanDev: false
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
			return this.showStundenplanDev ? 'stundenplandev' : 'stundenplan';
		},
	/*	tabulatorOptions() {
			const options = {
				ajaxURL: 'dummy',
				ajaxRequestFunc: () => this.$api.call(
					ApiStvCoursedates.getCourselist({
						student_uid: this.student.uid,
						start_date: this.dataSem.start,
						end_date: this.dataSem.ende,
						group_consecutiveHours: true,
						dbStundenplanTable: this.dbStundenplanTable})
				),
				ajaxResponse: (url, params, response) => {
					return response.data;
				},
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
					{title: "lektorIn", field: "lektorname", sorter:"string"},
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
			return options;
		},
		tabulatorEvents() {
			const events = [
				{
					event: 'tableBuilt',
					handler: async() => {
						await this.$p.loadCategory(['global', 'ui', 'lehre']);

						let cm = this.$refs.table.tabulator.columnManager;

						cm.getColumnByField('lehrveranstaltung_id').component.updateDefinition({
							title: this.$p.t('lehre', 'lehrveranstaltung_id')
						});
						cm.getColumnByField('lehreinheit_id').component.updateDefinition({
							title: this.$p.t('global', 'lehreinheit_id')
						});
						cm.getColumnByField('datum').component.updateDefinition({
							title: this.$p.t('global', 'datum')
						});
						cm.getColumnByField('beginn').component.updateDefinition({
							title: this.$p.t('ui', 'dateFrom')
						});
						cm.getColumnByField('ende').component.updateDefinition({
							title: this.$p.t('ui', 'dateTo')
						});
						cm.getColumnByField('gruppen_kuerzel').component.updateDefinition({
							title: this.$p.t('global', 'gruppen')
						});
						cm.getColumnByField('ort_kurzbz').component.updateDefinition({
							title: this.$p.t('global', 'ortLocation')
						});
						cm.getColumnByField('lektorname').component.updateDefinition({
							title: this.$p.t('lehre', 'lektor')
						});
						cm.getColumnByField('lehrfach_bez').component.updateDefinition({
							title: this.$p.t('global', 'lehrfach')
						});
					}

				}];
			return events;
		},*/
	},
	methods: {
		initTabulatorOptions(){
			this.tabulatorOptions = {
				ajaxURL: 'dummy',
				ajaxRequestFunc: () => this.$api.call(
					ApiStvCoursedates.getCourselist({
						student_uid: this.student.uid,
						start_date: this.dataSem.start,
						end_date: this.dataSem.ende,
						group_consecutiveHours: true,
						dbStundenplanTable: this.dbStundenplanTable})
				),
				ajaxResponse: (url, params, response) => {
					return response.data;
				},
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
					{title: "lektorIn", field: "lektorname", sorter:"string"},
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

						let cm = this.$refs.table.tabulator.columnManager;

						cm.getColumnByField('lehrveranstaltung_id').component.updateDefinition({
							title: this.$p.t('lehre', 'lehrveranstaltung_id')
						});
						cm.getColumnByField('lehreinheit_id').component.updateDefinition({
							title: this.$p.t('global', 'lehreinheit_id')
						});
						cm.getColumnByField('datum').component.updateDefinition({
							title: this.$p.t('global', 'datum')
						});
						cm.getColumnByField('beginn').component.updateDefinition({
							title: this.$p.t('ui', 'dateFrom')
						});
						cm.getColumnByField('ende').component.updateDefinition({
							title: this.$p.t('ui', 'dateTo')
						});
						cm.getColumnByField('gruppen_kuerzel').component.updateDefinition({
							title: this.$p.t('global', 'gruppen')
						});
						cm.getColumnByField('ort_kurzbz').component.updateDefinition({
							title: this.$p.t('global', 'ortLocation')
						});
						cm.getColumnByField('lektorname').component.updateDefinition({
							title: this.$p.t('lehre', 'lektor')
						});
						cm.getColumnByField('lehrfach_bez').component.updateDefinition({
							title: this.$p.t('global', 'lehrfach')
						});
					}
				}
			];
		},
		getDatesOfSemester(studiensemester_kurzbz) {
			this.dataSem = this.listStudiensemester.find(item => item.studiensemester_kurzbz === studiensemester_kurzbz);
			},
		exportToExcel(){
			window.open(this.downloadLink, '_blank');
		},
		reload() {
			this.$refs.table.reloadTable();
		},
		switchStundenplan(){
			this.showStundenplanDev = !this.showStundenplanDev;
			this.reload();
		}
	},
	watch: {
		currentSemester(newVal, oldVal) {
			this.getDatesOfSemester(newVal);
		},
	},
	created(){
		this.$api
			.call(ApiStvCoursedates.getStudiensemester())
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
							class="btn btn-outline-secondary"
							@click="exportToExcel">
								{{$p.t('ui', 'export')}}
						</button>
						<button
							class="btn btn-outline-secondary"
							@click="switchStundenplan">
								<span v-if="!showStundenplanDev">{{$p.t('lehre', 'stundenplan')}}</span>
								<span v-else>{{$p.t('lehre', 'stundenplanDev')}}</span>
						</button>
					</template>
			</core-filter-cmpt>
		</div>
	`
}
