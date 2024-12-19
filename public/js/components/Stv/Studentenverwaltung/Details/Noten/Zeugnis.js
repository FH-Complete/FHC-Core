import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import ZeugnisActions from './Zeugnis/Actions.js';
import ZeugnisDocuments from './Zeugnis/Documents.js';

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
						.then(() => this.$fhcAlert.alertSuccess('updated')) // TODO(chris): phrase
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
								return this.$fhcApi.factory
									.stv.grades.getGradeFromPoints(filterTerm, cell.getData().lehrveranstaltung_id, true)
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
				gradeField.editorParams.placeholderLoading = "Loading Remote Data..." // TODO(chris): phrase
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

			const hasDocuments = ['both', 'inline'].includes(this.config.documents);
			const hasDelete = ['both', 'inline'].includes(this.config.delete);

			if (hasDocuments || hasDelete) {
				columns.push({
					field: 'actions',
					title: 'Actions',
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
			this.$fhcApi.factory
				.stv.grades.updateCertificate(data)
				.then(this.$refs.table.reloadTable)
				.then(() => this.$fhcAlert.alertSuccess('updated')) // TODO(chris): phrase
				.catch(this.$fhcAlert.handleFormValidation);
		},
		deleteGrade(data) {
			// NOTE(chris): There is no check if there is an entry to be deleted, but it works anyway
			return this.$fhcAlert
				.confirmDelete()
				.then(result => result ? data : Promise.reject({handled:true}))
				.then(this.$fhcApi.factory.stv.grades.deleteCertificate)
				.then(this.$refs.table.reloadTable)
				.then(() => this.$fhcAlert.alertSuccess(this.$p.t('ui/successDelete')))
				.catch(this.$fhcAlert.handleSystemError);
		}
	},
	// TODO(chris): phrasen (title)
	template: `
	<div class="stv-details-noten-zeugnis h-100 d-flex flex-column">
		<core-filter-cmpt
			ref="table"
			title="Certificate"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			table-only
			:side-menu="false"
			reload
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