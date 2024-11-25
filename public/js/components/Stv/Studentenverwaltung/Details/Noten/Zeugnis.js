import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import ZeugnisActions from './Zeugnis/Actions.js';

export default {
	components: {
		CoreFilterCmpt,
		ZeugnisActions
	},
	props: {
		student: Object,
		allSemester: Boolean
	},
	data() {
		return {
			tabulatorEvents: [],
			stdsem: ''
		};
	},
	computed: {
		tabulatorOptions() {
			return {
				ajaxURL: 'dummy',
				ajaxRequestFunc: (url, config, params) => {
					return this.$fhcApi.factory.stv.grades.getCertificate(params.prestudent_id, params.stdsem);
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
				columns: [
					{ field: 'zeugnis', title: 'Zeugnis', formatter: 'tickCross' },
					{ field: 'lehrveranstaltung_bezeichnung', title: 'Lehrveranstaltung' },
					{ field: 'note_bezeichnung', title: 'Note' },
					{ field: 'uebernahmedatum', title: 'Übernahmedatum', visible: false },
					{ field: 'benotungsdatum', title: 'Benotungsdatum', visible: false },
					{ field: 'benotungsdatum-iso', title: 'Benotungsdatum ISO', visible: false },
					{ field: 'studiensemester_kurzbz', title: 'Studiensemester', visible: false },
					{ field: 'note', title: 'Note Numerisch', visible: false },
					{ field: 'lehrveranstaltung_id', title: 'Lehrveranstaltung ID', visible: false },
					{ field: 'studiengang', title: 'Studiengang', visible: false },
					{ field: 'studiengang_kz', title: 'Studiengang Kennzahl', visible: false },
					{ field: 'studiengang_lv', title: 'StudiengangLV', visible: false },
					{ field: 'studiengang_kz_lv', title: 'Studiengang_kzLV', visible: false },
					{ field: 'semester_lv', title: 'SemesterLV', visible: false },
					{ field: 'ects_lv', title: 'ECTS', visible: false },
					{ field: 'lehrform', title: 'Lehrform', visible: false },
					{ field: 'kurzbz', title: 'Kurzbz', visible: false },
					{ field: 'punkte', title: 'Punkte', visible: false },
					{ field: 'lehrveranstaltung_bezeichnung_english', title: 'Englisch', visible: false }
				],
				layout: 'fitDataStretch',
				height: '100%',
				selectable: true,
				selectableRangeMode: 'click',
				persistenceID: 'stv-details-noten-zeugnis'
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
		setGrades(selected) {
			this.$fhcApi.factory
				.stv.grades.updateCertificate(selected)
				.then(this.$refs.table.reloadTable)
				.catch(this.$fhcAlert.handleFormValidation);
		}
	},
	// TODO(chris): phrasen
	template: `
	<div class="stv-details-noten-zeugnis h-100 d-flex flex-column">
		<!-- TODO(chris): phrase -->
		<core-filter-cmpt
			ref="table"
			title="Certificate"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			table-only
			:side-menu="false"
			reload
			>
			<template #actions="{selected}">
				<zeugnis-actions :selected="selected" @set-grades="setGrades"></zeugnis-actions>
			</template>
		</core-filter-cmpt>
	</div>`
};