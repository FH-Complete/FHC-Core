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
			tabulatorEvents: [
				{
					event: "dataProcessed",
					handler: () => this.$emit("loaded"),
				},
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
					{
						field: 'lehrveranstaltung_bezeichnung',
						title: this.$p.t('lehre/lehrveranstaltung'),
						visible: this.optionalTabulatorOptions?.visibleColumns?.lehrveranstaltung_bezeichnung ?? true,
						headerFilter: this.optionalTabulatorOptions?.headerFilter?.lehrveranstaltung_bezeichnung || this.optionalTabulatorOptions?.headerFilter || false
					},
					{
						field: 'note_bezeichnung',
						title: this.$p.t('lehre/note'),
						visible: this.optionalTabulatorOptions?.visibleColumns?.note_bezeichnung ?? true,
						headerFilter: this.optionalTabulatorOptions?.headerFilter?.note_bezeichnung || this.optionalTabulatorOptions?.headerFilter || false
					},
					{
						field: 'mitarbeiter_uid',
						title: this.$p.t('profil/mitarbeiterIn'),
						visible: this.optionalTabulatorOptions?.visibleColumns?.mitarbeiter_uid ?? false,
						headerFilter: this.optionalTabulatorOptions?.headerFilter?.mitarbeiter_uid || this.optionalTabulatorOptions?.headerFilter || false
					},
					{
						field: 'benotungsdatum',
						title: this.$p.t('stv/grades_gradingdate'),
						visible: this.optionalTabulatorOptions?.visibleColumns?.benotungsdatum ?? false,
						headerFilter: this.optionalTabulatorOptions?.headerFilter?.benotungsdatum || this.optionalTabulatorOptions?.headerFilter || false
					},
					{
						field: 'freigabedatum',
						title: this.$p.t('stv/grades_approvaldate'),
						visible: this.optionalTabulatorOptions?.visibleColumns?.freigabedatum ?? false,
						headerFilter: this.optionalTabulatorOptions?.headerFilter?.freigabedatum || this.optionalTabulatorOptions?.headerFilter || false
					},
					{
						field: 'studiensemester_kurzbz',
						title: this.$p.t('lehre/studiensemester'),
						visible: this.optionalTabulatorOptions?.visibleColumns?.studiensemester_kurzbz ?? false,
						headerFilter: this.optionalTabulatorOptions?.headerFilter?.studiensemester_kurzbz || this.optionalTabulatorOptions?.headerFilter || false
					},
					{
						field: 'note',
						title: this.$p.t('stv/grades_numericgrade'),
						visible: this.optionalTabulatorOptions?.visibleColumns?.note ?? false,
						headerFilter: this.optionalTabulatorOptions?.headerFilter?.note || this.optionalTabulatorOptions?.headerFilter || false
					},
					{
						field: 'student_uid',
						title: this.$p.t('profil/studentIn'),
						visible: this.optionalTabulatorOptions?.visibleColumns?.student_uid ?? false,
						headerFilter: this.optionalTabulatorOptions?.headerFilter?.student_uid || this.optionalTabulatorOptions?.headerFilter || false
					},
					{
						field: 'lehrveranstaltung_id',
						title: this.$p.t('lehre/lehrveranstaltung_id'),
						visible: this.optionalTabulatorOptions?.visibleColumns?.lehrveranstaltung_id ?? false,
						headerFilter: this.optionalTabulatorOptions?.headerFilter?.lehrveranstaltung_id || this.optionalTabulatorOptions?.headerFilter || false
					},
					{
						field: 'punkte',
						title: this.$p.t('stv/grades_points'),
						visible: this.optionalTabulatorOptions?.visibleColumns?.punkte ?? false,
						headerFilter: this.optionalTabulatorOptions?.headerFilter?.punkte || this.optionalTabulatorOptions?.headerFilter || false
					},
					{
						field: 'vorname',
						title: this.$p.t('person/vorname'),
						visible: this.optionalTabulatorOptions?.visibleColumns?.vorname ?? false,
						headerFilter: this.optionalTabulatorOptions?.headerFilter?.vorname || this.optionalTabulatorOptions?.headerFilter || false
					},
					{
						field: 'nachname',
						title: this.$p.t('person/nachname'),
						visible: this.optionalTabulatorOptions?.visibleColumns?.nachname ?? false,
						headerFilter: this.optionalTabulatorOptions?.headerFilter?.nachname || this.optionalTabulatorOptions?.headerFilter || false
					}
				],
				columnDefaults: {
					headerFilter: this.optionalTabulatorOptions?.headerFilter ?? false,
				},
				layout: 'fitDataStretch',
				height: '100%',
				selectable: true,
				selectableRows: true, // needed for Tabulator v6
				selectableRowsRangeMode: 'click', // needed for Tabulator v6
				selectableRangeMode: 'click',
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
				if (this.$refs.table.tableBuilt)
					this.$refs.table.tabulator.columnManager.setColumns(this.tabulatorOptions.columns);
			});
	},
	template: `
	<div class="stv-details-noten-teacher d-flex flex-column">
		<core-filter-cmpt
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