import {CoreFilterCmpt} from "../../../../filter/Filter.js";

export default {
	components: {
		CoreFilterCmpt
	},
	emits: [
		"copied"
	],
	props: {
		student: Object,
		allSemester: Boolean
	},
	data() {
		return {
			tabulatorEvents: []
		};
	},
	computed: {
		tabulatorOptions() {
			return {
				ajaxURL: 'dummy',
				ajaxRequestFunc: (url, config, params) => {
					return this.$fhcApi.factory.stv.grades.getRepeaterGrades(params.prestudent_id, params.stdsem);
				},
				ajaxParams: () => {
					return {
						prestudent_id: this.student.prestudent_id,
						stdsem: this.allSemester
					};
				},
				ajaxResponse: (url, params, response) => {
					return response.data || [];
				},
				// TODO(chris): phrasen
				columns: [
					{ field: 'lv_bezeichnung', title: 'Lehrveranstaltung' },
					{ field: 'note_bezeichnung', title: 'Note' },
					{ field: 'insertvon', title: 'MitarbeiterInUID', visible: false },
					{ field: 'benotungsdatum', title: 'Benotungsdatum', visible: false },
					{ field: 'freigabedatum', title: 'Freigabedatum', visible: false },
					{ field: 'studiensemester_kurzbz', title: 'Studiensemester', visible: false },
					{ field: 'stg_bezeichnung', title: 'Studiengang', visible: false },
					{ field: 'note', title: 'Note', visible: false },
					{ field: 'prestudent_uid', title: 'PrestudentID', visible: false },
					{ field: 'lehrveranstaltung_id', title: 'Lehrveranstaltung ID', visible: false },
					{ field: 'studierendenantrag_lehrveranstaltung_id', title: 'StudstatusLvID', visible: false }
				],
				layout: 'fitDataStretch',
				height: '100%',
				selectable: true,
				selectableRangeMode: 'click',
				persistenceID: 'stv-details-noten-repeater'
			};
		}
	},
	watch: {
		student(n) {
			this.$refs.table.reloadTable();
		},
		allSemester(n) {
			this.$refs.table.reloadTable();
		}
	},
	methods: {
		copyGrades(selected) {
			const promises = selected.map(
				grade => this.$fhcApi.factory
					.stv.grades.copyRepeaterGradeToCertificate(grade)
					.then(() => {
						this.$refs.table.tabulator.deselectRow(this.$refs.table.tabulator.getRows().find(el => el.getData() == grade).getElement());
					})
			);
			Promise
				.allSettled(promises)
				.then(results => {
					if (results.some(res => res.status == "fulfilled")) {
						// TODO(chris): phrase
						this.$fhcAlert.alertSuccess("updated");
						this.$emit('copied');
					}
				});
		}
	},
	template: `
	<div class="stv-details-noten-repeater d-flex flex-column">
		<!-- TODO(chris): phrase -->
		<core-filter-cmpt
			ref="table"
			title="Repeater Grades"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			table-only
			:side-menu="false"
			reload
			>
			<template #actions="{selected}">
				<button class="btn btn-primary" :disabled="!selected.length" @click="copyGrades(selected)">
					<!-- TODO(chris): phrase -->
					<i class="fa-solid fa-arrow-left"></i> copy
				</button>
			</template>
		</core-filter-cmpt>
	</div>`
};