import {CoreFilterCmpt} from "../../../../filter/Filter.js";

export default {
	components: {
		CoreFilterCmpt
	},
	emits: [
		"copied",
		"loaded"
	],
	inject: {
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
		allSemester: Boolean,
		optionalTabulatorOptions: Object,
	},
	data() {
		return {
			renderTabulator: false,
			setOptinalTabulatorOptionsVisibility: false,
			tabulatorEvents: [
				{
					event: "dataProcessed",
					handler: () => this.$emit("loaded"),
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
			]
		};
	},
	computed: {
		tabulatorOptions() {
			return {
				ajaxURL: 'dummy',
				ajaxRequestFunc: () => this.$api.call(this.endpoint.getTeacherProposal(
					this.id,
						(!this.allSemester ? this.currentSemester : null)
				)),
				ajaxResponse: (url, params, response) => {
					return response.data || [];
				},
				columns: [
					{ field: 'lehrveranstaltung_bezeichnung', title: this.$p.t('lehre/lehrveranstaltung') },
					{ field: 'note_bezeichnung', title: this.$p.t('lehre/note') },
					{ field: 'mitarbeiter_uid', title: this.$p.t('profil/mitarbeiterIn'), visible: false },
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
					{ field: 'freigabedatum', title: this.$p.t('stv/grades_approvaldate'), visible: false,
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
					{ field: 'note', title: this.$p.t('stv/grades_numericgrade'), visible: false },
					{ field: 'student_uid', title: this.$p.t('profil/studentIn'), visible: false },
					{ field: 'vorname', title: this.$p.t('person/vorname'), visible: false },
					{ field: 'nachname', title: this.$p.t('person/nachname'), visible: false },
					{ field: 'lehrveranstaltung_id', title: this.$p.t('lehre/lehrveranstaltung_id'), visible: false },
					{ field: 'punkte', title: this.$p.t('stv/grades_points'), visible: false }
				],
				columnDefaults: {
					headerFilter: this.optionalTabulatorOptions?.headerFilter ?? false,
				},
				layout: 'fitDataStretch',
				height: '100%',
				selectableRows: true,
				selectableRowsRangeMode: 'click',
				persistenceID: this.optionalTabulatorOptions?.persistenceTeacherID ?? 'stv-details-noten-teacher-2025120401',
			};
		}
	},
	watch: {
		id() {
			this.$refs.table.reloadTable();
		},
		allSemester(n) {
			this.$refs.table.reloadTable();
		}
	},
	methods: {
		copyGrades(selected) {
			const promises = selected.map(
				grade => this.$api
					.call(this.endpoint.copyTeacherProposalToCertificate(grade), {
						errorHeader: grade.lehrveranstaltung_bezeichnung
					})
					.then(() => {
						this.$refs.table.tabulator.deselectRow(this.$refs.table.tabulator.getRows().find(el => el.getData() == grade).getElement());
					})
			);
			Promise
				.allSettled(promises)
				.then(results => {
					if (results.some(res => res.status == "fulfilled")) {
						this.$fhcAlert.alertSuccess(this.$p.t('stv/grades_updated'));
						this.$emit('copied');
					}
				});
		}
	},
	created() {
		this.$p.loadCategory(['stv', 'lehre', 'profil'])
			.then(() => {
				this.renderTabulator = true;
			});

		if(this?.optionalTabulatorOptions)
		{
			const localStorageKey = 'tabulator-' + this.optionalTabulatorOptions.persistenceTeacherID + '-columns';
			this.setOptinalTabulatorOptionsVisibility = (window.localStorage.getItem(localStorageKey) === null) ? true : false;
		}
	},
	template: `
	<div class="stv-details-noten-teacher d-flex flex-column">
		<core-filter-cmpt
			v-if="renderTabulator"
			ref="table"
			:title="$p.t('stv/grades_title_teacher')"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			table-only
			:side-menu="false"
			reload
			:reload-btn-infotext="this.$p.t('table', 'reload')"
			>
			<template #actions="{selected}">
				<button class="btn btn-primary" :disabled="!selected.length" @click="copyGrades(selected)">
					<i class="fa-solid fa-arrow-left"></i> {{ $p.t('stv/grades_action_copy') }}
				</button>
			</template>
		</core-filter-cmpt>
	</div>`
};