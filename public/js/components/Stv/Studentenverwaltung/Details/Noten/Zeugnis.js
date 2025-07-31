import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import ZeugnisActions from './Zeugnis/Actions.js';
import ZeugnisDocuments from './Zeugnis/Documents.js';

import ApiStvGrades from '../../../../../api/factory/stv/grades.js';

export default {
	components: {
		CoreFilterCmpt,
		ZeugnisActions,
		ZeugnisDocuments
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
			tabulatorEvents: [
				{
					event: "dataLoaded",
					handler: data => this.data = data.map(item => {
						item.documentslist = document.createElement("div");
						return item;
					})
				},
				{
					event: "rowSelected",
					handler: row => row.getElement().style.zIndex = 12
				},
				{
					event: "rowDeselected",
					handler: row => row.getElement().style.zIndex = ''
				}
			],
			stdsem: '',
			lastGradeList: [],
			lastClickedRow: null,
			data: []
		};
	},
	computed: {
		tabulatorOptions() {
			const listPromise = this.$api
				.call(ApiStvGrades.list())
				.then(res => res.data.map(({bezeichnung: label, note: value}) => ({label, value})));

			let gradeField = {
				field: 'note',
				title: this.$p.t('lehre/note'),
				formatter: cell => cell.getData().note_bezeichnung,
				tooltip: (evt, cell) => cell.getData().note_bezeichnung
			};
			if (['both', 'inline'].includes(this.config.edit)) {
				gradeField.editor = 'list';
				gradeField.cellEdited = cell => {
					// get changed value
					const note = cell.getValue();
					if (note === '')
						return;

					// get row data
					const {lehrveranstaltung_id, uid: student_uid, studiensemester_kurzbz} = cell.getData();

					listPromise
						// get bezeichnung
						.then(list => list.find(el => el.value == note))
						.then(found => found ? found.label : Promise.reject({message: 'grade ' + note + ' not found in list'}))
						// prepare data object
						.then(note_bezeichnung => ({
							lehrveranstaltung_id,
							student_uid,
							studiensemester_kurzbz,
							note,
							note_bezeichnung
						}))
						// send to backend
						.then(data => this.$api.call(ApiStvGrades.updateCertificate(data)))
						// get bezeichnung again
						.then(() => listPromise)
						.then(list => list.find(el => el.value == note))
						.then(found => found?.label)
						// update other fields in row
						.then(note_bezeichnung => cell.getRow().update({note_bezeichnung}))
						.then(() => cell.getRow().reformat())
						// cleanup
						.then(cell.clearEdited)
						.then(() => this.$fhcAlert.alertSuccess(this.$p.t('stv/grades_updated')))
						.catch(err => {
							cell.restoreOldValue();
							cell.clearEdited();
							this.$fhcAlert.handleFormValidation(err);
						});
				};
				if (this.config.usePoints) {
					gradeField.editorParams = {
						valuesLookup: (cell, filterTerm) => {
							if (filterTerm) {
								return this.$api
									.call(
										ApiStvGrades.getGradeFromPoints(
											filterTerm,
											cell.getData().lehrveranstaltung_id
										),
										{ errorHandling: false }
									)
									.then(result => 
										result.data === null
										? []
										: listPromise.then(res => res.filter(grade => grade.value === result.data))
									)
									.catch(err => []);
							}
							return listPromise;
						},
						autocomplete: true,
						filterRemote: true,
						allowEmpty: true, 
						listOnEmpty: true
					};
					gradeField.cellEditing = cell => cell.setValue('');
					gradeField.cellEditCancelled = cell => cell;
				} else {
					gradeField.editorParams = {
						valuesLookup: (cell, filterTerm) => listPromise
					};
				}
				const node = document.createElement('span');
				this.$p.loadCategory('stv')
					.then(() => node.innerText = this.$p.t('ui/loading'))
					.catch(this.$fhcAlert.handleSystemError);
				gradeField.editorParams.placeholderLoading = node;
			}

			const columns = [
				{ field: 'zeugnis', title: this.$p.t('stv/grades_zeugnis'), formatter: 'tickCross' },
				{ field: 'lehrveranstaltung_bezeichnung', title: this.$p.t('lehre/lehrveranstaltung') },
				gradeField,
				{ field: 'uebernahmedatum', title: this.$p.t('stv/grades_takeoverdate'), visible: false },
				{ field: 'benotungsdatum', title: this.$p.t('stv/grades_gradingdate'), visible: false },
				{ field: 'studiensemester_kurzbz', title: this.$p.t('lehre/studiensemester'), visible: false },
				{ field: 'note_number', title: this.$p.t('stv/grades_numericgrade'), visible: false, formatter: cell => cell.getData().note, tooltip: (evt, cell) => cell.getData().note },
				{ field: 'lehrveranstaltung_id', title: this.$p.t('lehre/lehrveranstaltung_id'), visible: false },
				{ field: 'studiengang', title: this.$p.t('lehre/studiengang'), visible: false },
				{ field: 'studiengang_kz', title: this.$p.t('lehre/studiengangskennzahlLehre'), visible: false },
				{ field: 'studiengang_lv', title: this.$p.t('stv/grades_studiengang_lv'), visible: false },
				{ field: 'studiengang_kz_lv', title: this.$p.t('stv/grades_studiengang_kz_lv'), visible: false },
				{ field: 'semester_lv', title: this.$p.t('stv/grades_semester_lv'), visible: false },
				{ field: 'ects_lv', title: this.$p.t('lehre/ects'), visible: false },
				{ field: 'lehrform', title: this.$p.t('lehre/lehrform'), visible: false },
				{ field: 'kurzbz', title: this.$p.t('lehre/kurzbz'), visible: false },
				{ field: 'punkte', title: this.$p.t('stv/grades_points'), visible: false },
				{ field: 'lehrveranstaltung_bezeichnung_english', title: this.$p.t('stv/grades_lehrveranstaltung_bezeichnung_english'), visible: false }
			];

			const hasDocuments = ['both', 'inline'].includes(this.config.documents);
			const hasDelete = ['both', 'inline'].includes(this.config.delete);

			if (hasDocuments || hasDelete) {
				columns.push({
					field: 'actions',
					title: this.$p.t('global/actions'),
					cssClass: "overflow-visible",
					headerSort: false,
					formatter: cell => {
						// get row data
						const data = cell.getData();
						data.student_uid = data.uid;

						let container = document.createElement('div');
						container.className = "d-flex gap-2 justify-content-end";

						if (hasDocuments) {
							container.append(data.documentslist);
						}

						if (hasDelete) {
							let deleteButton = document.createElement('button');
							deleteButton.className = 'btn btn-outline-secondary';
							const icon = document.createElement('i');
							icon.className = 'fa fa-trash';
							icon.title = this.$p.t('ui/loeschen');
							deleteButton.append(icon);
							deleteButton.addEventListener('click', evt => {
								evt.stopPropagation();
								this.deleteGrade(data);
							});
							container.append(deleteButton);
						}

						return container;
					},
					frozen: true
				});
			}

			return {
				ajaxURL: 'dummy',
				ajaxRequestFunc: () => this.$api.call(ApiStvGrades.getCertificate(
					this.student.prestudent_id,
					this.allSemester
				)),
				ajaxResponse: (url, params, response) => {
					return response.data || [];
				},
				columns,
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
		setGrade(data) {
			this.$api
				.call(
					ApiStvGrades.updateCertificate(data),
					{ errorHeader: data.lehrveranstaltung_bezeichnung }
				)
				.then(this.$refs.table.reloadTable)
				.then(() => this.$fhcAlert.alertSuccess(this.$p.t('stv/grades_updated')))
				.catch(this.$fhcAlert.handleFormValidation);
		},
		deleteGrade(data) {
			// NOTE(chris): There is no check if there is an entry to be deleted, but it works anyway
			return this.$fhcAlert
				.confirmDelete()
				.then(result => result ? data : Promise.reject({handled:true}))
				.then(data => this.$api.call(
					ApiStvGrades.deleteCertificate(data),
					{ errorHeader: data.lehrveranstaltung_bezeichnung }
				))
				.then(this.$refs.table.reloadTable)
				.then(() => this.$fhcAlert.alertSuccess(this.$p.t('ui/successDelete')))
				.catch(this.$fhcAlert.handleSystemError);
		}
	},
	created() {
		this.$p.loadCategory(['global', 'stv', 'lehre'])
			.then(() => {
				if (this.$refs.table.tableBuilt)
					this.$refs.table.tabulator.columnManager.setColumns(this.tabulatorOptions.columns);
			});
	},
	template: `
	<div class="stv-details-noten-zeugnis h-100 d-flex flex-column">
		<core-filter-cmpt
			ref="table"
			:title="$p.t('stv/grades_title_zeugnis')"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			table-only
			:side-menu="false"
			reload
			:reload-btn-infotext="this.$p.t('table', 'reload')"
			>
			<template v-if="['both', 'header'].includes(config.edit) || ['both', 'header'].includes(config.delete)" #actions="{selected}">
				<zeugnis-actions :selected="selected" @set-grade="setGrade" @delete-grade="deleteGrade"></zeugnis-actions>
			</template>
		</core-filter-cmpt>
		<Teleport
			v-for="grade in data"
			:key="grade.uid + '_' + grade.studiensemester_kurzbz + '_' + grade.lehrveranstaltung_id"
			:to="grade.documentslist"
			>
			<zeugnis-documents :data="grade" :list="config.documentslist"></zeugnis-documents>
		</Teleport>
	</div>`
};