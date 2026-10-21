import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import ZeugnisActions from './Zeugnis/Actions.js';
import ZeugnisDocuments from './Zeugnis/Documents.js';
import { debounce } from '../../../../../helpers/debounce.js';

export default {
	name: 'Zeugnis',
	components: {
		CoreFilterCmpt,
		ZeugnisActions,
		ZeugnisDocuments
	},
	emits: ['loaded'],
	inject: {
		config: {
			from: 'config',
			required: true
		},
		currentSemester: {
			from: 'currentSemester',
			required: true
		}
	},
	props: {
		endpoint: {
			type: Object,
			required: true
		},
		id: {
			type: [Number, String],
			required: true
		},
		optionalTabulatorOptions: Object,
		allSemester: Boolean
	},
	data() {
		return {
			renderTabulator: false,
			setOptinalTabulatorOptionsVisibility: false,
			tabulatorEvents: [
				{
					event: "dataLoaded",
					handler: data => this.data = data.map(item => {
						item.documentslist = document.createElement("div");
						return item;
					})
				},
				{
					event: "dataProcessed",
					handler: () => this.$emit("loaded")
				},
				{
					event: "rowSelected",
					handler: row => row.getElement().style.zIndex = 12
				},
				{
					event: "rowDeselected",
					handler: row => row.getElement().style.zIndex = ''
				},
				{
					event: "tableBuilt",
					handler: () => {
						if(this.optionalTabulatorOptions?.visibleColumns
							&& this.setOptinalTabulatorOptionsVisibility)
						{
							for(let key in this.optionalTabulatorOptions.visibleColumns) {
								if(this.optionalTabulatorOptions.visibleColumns[key])
									this.$refs.table.tabulator.showColumn(key);
								else
									this.$refs.table.tabulator.hideColumn(key);
							}
						}
					}
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
				.call(this.endpoint.list())
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
					const {lehrveranstaltung_id, uid: student_uid, studiensemester_kurzbz, _punkte: punkte = null} = cell.getData();

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
							punkte,
							note_bezeichnung
						}))
						// send to backend
						.then(data => this.$api.call(this.endpoint.updateCertificate(data)))
						// get bezeichnung again
						.then(() => listPromise)
						.then(list => list.find(el => el.value == note))
						.then(found => found?.label)
						// update other fields in row
						.then(note_bezeichnung => cell.getRow().update({note_bezeichnung, punkte, _punkte: null}))
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
							// the last typed Punkte go with the selected Note; a decimal comma counts as a point
							const punkte = filterTerm ? filterTerm.replace(',', '.') : null;
							cell.getData()._punkte = punkte;
							if (punkte) {
								return this.$api
									.call(
										this.endpoint.getGradeFromPoints(
											punkte,
											cell.getData().lehrveranstaltung_id,
											cell.getData().studiensemester_kurzbz
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
				gradeField.visible = this.optionalTabulatorOptions?.visibleColumns?.note ?? true
				gradeField.headerFiler = this.optionalTabulatorOptions?.headerFilter?.note || this.optionalTabulatorOptions?.note || false
			}

			let punkteField = {
				field: 'punkte',
				title: this.$p.t('stv/grades_points'),
				visible: !!this.config.usePoints,
				// numeric(8,4) arrives as "45.5000"
				formatter: cell => (cell.getValue() ?? '') === '' ? '' : String(Number(cell.getValue()))
			};
			if (this.config.usePoints && ['both', 'inline'].includes(this.config.edit)) {
				// saves the Punkte of one row; the server derives the Note, the row shows what it saved
				const savePunkte = (row, punkte, before) => {
					const data = row.getData();
					const saveId = data._punkteSaveId = (data._punkteSaveId || 0) + 1;
					const { lehrveranstaltung_id, uid: student_uid, studiensemester_kurzbz, lehrveranstaltung_bezeichnung } = data;

					this.$api
						.call(
							// without Punkte the Note before the edit stays; a preview of a shorter input must not stick
							this.endpoint.updateCertificate({ lehrveranstaltung_id, student_uid, studiensemester_kurzbz, note: punkte === null ? before.note : data.note, punkte }),
							{ errorHeader: lehrveranstaltung_bezeichnung }
						)
						.then(result => listPromise.then(list => ({
							...result.data,
							note_bezeichnung: list.find(el => el.value == result.data.note)?.label
						})))
						.then(saved => {
							// a later save of the same row wins
							if (row.getData()._punkteSaveId !== saveId)
								return;
							// update, not reformat: a reformat destroys an editor that is open in this row again
							row.update(saved);
							this.$fhcAlert.alertSuccess(this.$p.t('stv/grades_updated'));
						})
						.catch(err => {
							if (row.getData()._punkteSaveId === saveId)
								row.update(before);
							this.$fhcAlert.handleFormValidation(err);
						});
				};

				// the Note of the Notenschluessel shows while typing; Enter, Tab and a click elsewhere save
				punkteField.editor = (cell, onRendered, success, cancel) => {
					const row = cell.getRow();
					const { note, note_bezeichnung, punkte } = row.getData();

					const input = document.createElement('input');
					input.type = 'text';
					input.inputMode = 'decimal';
					input.style.cssText = 'width: 100%; height: 100%; box-sizing: border-box; padding: 4px;';

					// cellEditCancelled reads this state when Tabulator closes the editor itself
					const editor = row.getData()._punkteEditor = {
						open: true,
						initial: punkteField.formatter(cell),
						before: { note, note_bezeichnung, punkte },
						value: () => input.value.trim().replace(',', '.'),
						save: () => savePunkte(row, editor.value() === '' ? null : editor.value(), editor.before)
					};
					input.value = editor.initial;

					const showNote = debounce(() => {
						if (!editor.open)
							return;
						if (editor.value() === '')
							return row.update({ note, note_bezeichnung });
						const { lehrveranstaltung_id, studiensemester_kurzbz } = row.getData();
						this.$api
							.call(this.endpoint.getGradeFromPoints(editor.value(), lehrveranstaltung_id, studiensemester_kurzbz), { errorHandling: false })
							.then(result => listPromise.then(list => list.find(el => el.value == result.data)))
							.then(found => editor.open && found && row.update({ note: found.value, note_bezeichnung: found.label }))
							.catch(() => {});
					}, 500);

					const finish = save => {
						if (!editor.open)
							return;
						editor.open = false;
						if (save && editor.value() !== editor.initial) {
							success(editor.value());
							return editor.save();
						}
						cancel();
						row.update({ note, note_bezeichnung });
					};
					input.addEventListener('input', showNote);
					input.addEventListener('blur', () => finish(true));
					input.addEventListener('keydown', evt => {
						if (evt.key === 'Enter')
							finish(true);
						if (evt.key === 'Escape')
							finish(false);
						// like the Tabulator editors: else the table keybindings scroll and close the editor
						if (evt.key === 'Home' || evt.key === 'End')
							evt.stopPropagation();
					});
					onRendered(() => {
						input.focus();
						input.select();
					});
					return input;
				};
				// Tabulator closes an open editor itself when another edit starts; the typed Punkte still count
				punkteField.cellEditCancelled = cell => {
					const editor = cell.getData()._punkteEditor;
					if (!editor?.open)
						return;
					editor.open = false;
					if (editor.value() === editor.initial)
						return cell.getRow().update({ note: editor.before.note, note_bezeichnung: editor.before.note_bezeichnung });
					cell.getRow().update({ punkte: editor.value() });
					editor.save();
				};
				// one click opens the editor; without it the first click only selects the row
				punkteField.cellClick = (evt, cell) => {
					if (!cell.getElement().classList.contains('tabulator-editing'))
						cell.edit();
				};
			}

			const columns = [
				{ field: 'zeugnis', title: this.$p.t('stv/grades_zeugnis'), formatter: 'tickCross' },
				{ field: 'lehrveranstaltung_bezeichnung', title: this.$p.t('lehre/lehrveranstaltung') },
				gradeField,
				{ field: 'uebernahmedatum', title: this.$p.t('stv/grades_takeoverdate'), visible: false,
					formatter: function (cell) {
						const dateStr = cell.getValue();
						if (!dateStr) return "";

						const date = new Date(dateStr);
						return date.toLocaleString("de-DE", {
							day: "2-digit",
							month: "2-digit",
							year: "numeric",
							hour: "2-digit",
							minute: "2-digit",
							second: "2-digit",
							hour12: false
						});
					}},
				{ field: 'benotungsdatum', title: this.$p.t('stv/grades_gradingdate'), visible: false,
					formatter: function (cell) {
						const dateStr = cell.getValue();
						if (!dateStr) return "";

						const date = new Date(dateStr);
						return date.toLocaleString("de-DE", {
							day: "2-digit",
							month: "2-digit",
							year: "numeric",
							hour: "2-digit",
							minute: "2-digit",
							second: "2-digit",
							hour12: false
						});
					}},
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
				punkteField,
				{ field: 'lehrveranstaltung_bezeichnung_english', title: this.$p.t('stv/grades_lehrveranstaltung_bezeichnung_english'), visible: false },
				{ field: 'uid', title: this.$p.t('person/uid'), visible: false },
				{ field: 'vorname', title: this.$p.t('person/vorname'), visible: false },
				{ field: 'nachname', title: this.$p.t('person/nachname'), visible: false }
			];

			const hasDocuments = ['both', 'inline'].includes(this.config.documents);
			const hasDelete = ['both', 'inline'].includes(this.config.delete);

			if (hasDocuments || hasDelete) {
				columns.push({
					field: 'actions',
					title: this.$p.t('global/actions'),
					minWidth: 120,
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
							icon.title = this.$p.t('lehre/info_deleteMarkEntry');
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
				ajaxRequestFunc: () => this.$api.call(this.endpoint.getCertificate(
					this.id,
						(!this.allSemester ? this.currentSemester : null)
				)),
				ajaxResponse: (url, params, response) => {
					return response.data || [];
				},
				columns,
				height: '100%',
				layout: 'fitDataStretchFrozen',
				selectable: true,
				selectableRowsRangeMode: 'click',
				selectableRows: true,
				persistenceID: this.optionalTabulatorOptions?.persistenceZeugnisID ?? 'stv-details-noten-zeugnis-20260217',
				persistence:{
					columns: ["width", "visible", "frozen"]
				}
			};
		}
	},
	watch: {
		id()
		{
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
					this.endpoint.updateCertificate(data),
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
					this.endpoint.deleteCertificate(data),
					{ errorHeader: data.lehrveranstaltung_bezeichnung }
				))
				.then(this.$refs.table.reloadTable)
				.then(() => this.$fhcAlert.alertSuccess(this.$p.t('ui/successDelete')))
				.catch(this.$fhcAlert.handleSystemError);
		}
	},
	created() {
		this.$p.loadCategory(['global', 'stv', 'lehre', 'person'])
			.then(() => {
				this.renderTabulator = true;
			});

		if(this?.optionalTabulatorOptions)
		{
			const localStorageKey = 'tabulator-' + this.optionalTabulatorOptions.persistenceZeugnisID + '-columns';
			this.setOptinalTabulatorOptionsVisibility = (window.localStorage.getItem(localStorageKey) === null) ? true : false;
		}
	},
	template: `
	<div class="stv-details-noten-zeugnis h-100 d-flex flex-column">
		<core-filter-cmpt
			v-if="renderTabulator"
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
