import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import ZeugnisActions from './Zeugnis/Actions.js';

export default {
	components: {
		CoreFilterCmpt,
		ZeugnisActions
	},
	inject: [
		'config'
	],
	props: {
		student: Object,
		allSemester: Boolean
	},
	data() {
		return {
			tabulatorEvents: [],
			stdsem: '',
			lastGradeList: []
		};
	},
	computed: {
		tabulatorOptions() {
			const listPromise = this.$fhcApi.factory
				.stv.grades.list()
				.then(res => res.data.map(({bezeichnung: label, note: value}) => ({label, value})));

			let gradeField = {
				field: 'note',
				title: 'Note',
				formatter: cell => cell.getData().note_bezeichnung,
				tooltip: (evt, cell) => cell.getData().note_bezeichnung
			};
			if (['both', 'inline'].includes(this.config.edit)) {
				gradeField = {...gradeField, ...{
					editor: 'list',
					editorParams: {
						valuesLookup: (cell, filterTerm) => listPromise,
						placeholderLoading: "Loading Remote Data...", // TODO(chris): phrase
					},
					cellEdited: cell => {
						// get row data
						const {lehrveranstaltung_id, uid: student_uid, studiensemester_kurzbz} = cell.getData();
						// get changed value
						const note = cell.getValue();
						
						listPromise
							// get bezeichnung
							.then(list => list.find(el => el.value == note))
							.then(found => found ? found.label : Promise.reject({message: 'not found'}))
							// prepare data object
							.then(note_bezeichnung => ({
								lehrveranstaltung_id,
								student_uid,
								studiensemester_kurzbz,
								note,
								note_bezeichnung
							}))
							// send to backend
							.then(this.$fhcApi.factory.stv.grades.updateCertificate)
							// get bezeichnung again
							.then(() => listPromise)
							.then(list => list.find(el => el.value == note))
							.then(found => found ? found.label : Promise.reject({message: 'not found'}))
							// update other fields in row
							.then(note_bezeichnung => cell.getRow().update({note_bezeichnung}))
							.then(() => cell.getRow().reformat())
							// cleanup
							.then(cell.clearEdited)
							.catch(err => {
								cell.restoreOldValue();
								cell.clearEdited();
								this.$fhcAlert.handleFormValidation(err);
							});
					}
				}};
			}

			const columns = [
				{ field: 'zeugnis', title: 'Zeugnis', formatter: 'tickCross' },
				{ field: 'lehrveranstaltung_bezeichnung', title: 'Lehrveranstaltung' },
				gradeField,
				{ field: 'uebernahmedatum', title: 'Übernahmedatum', visible: false },
				{ field: 'benotungsdatum', title: 'Benotungsdatum', visible: false },
				{ field: 'benotungsdatum-iso', title: 'Benotungsdatum ISO', visible: false },
				{ field: 'studiensemester_kurzbz', title: 'Studiensemester', visible: false },
				{ field: 'note_number', title: 'Note Numerisch', visible: false, formatter: cell => cell.getData().note, tooltip: (evt, cell) => cell.getData().note },
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
			];

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
				columns,
				layout: 'fitDataStretch',
				height: '100%',
				selectable: 1,
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
				.stv.grades.updateCertificate(selected.find(Boolean))
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
			<template v-if="['both', 'header'].includes(config.edit)" #actions="{selected}">
				<zeugnis-actions :selected="selected" @set-grades="setGrades"></zeugnis-actions>
			</template>
		</core-filter-cmpt>
	</div>`
};