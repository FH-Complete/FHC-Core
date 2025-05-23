import {CoreFilterCmpt} from "../../../../filter/Filter.js";

import ApiStvGrades from '../../../../../api/factory/stv/grades.js';

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
				ajaxRequestFunc: () => this.$api.call(ApiStvGrades.getTeacherProposal(
					this.student.prestudent_id,
					this.allSemester
				)),
				ajaxResponse: (url, params, response) => {
					return response.data || [];
				},
				columns: [
					{ field: 'lehrveranstaltung_bezeichnung', title: this.$p.t('lehre/lehrveranstaltung') },
					{ field: 'note_bezeichnung', title: this.$p.t('lehre/note') },
					{ field: 'mitarbeiter_uid', title: this.$p.t('profil/mitarbeiterIn'), visible: false },
					{ field: 'benotungsdatum', title: this.$p.t('stv/grades_gradingdate'), visible: false },
					{ field: 'freigabedatum', title: this.$p.t('stv/grades_approvaldate'), visible: false },
					{ field: 'studiensemester_kurzbz', title: this.$p.t('lehre/studiensemester'), visible: false },
					{ field: 'note', title: this.$p.t('stv/grades_numericgrade'), visible: false },
					{ field: 'student_uid', title: this.$p.t('profil/studentIn'), visible: false },
					{ field: 'lehrveranstaltung_id', title: this.$p.t('lehre/lehrveranstaltung_id'), visible: false },
					{ field: 'punkte', title: this.$p.t('stv/grades_points'), visible: false }
				],
				layout: 'fitDataStretch',
				height: '100%',
				selectable: true,
				selectableRangeMode: 'click',
				persistenceID: 'stv-details-noten-teacher'
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
				grade => this.$api
					.call(ApiStvGrades.copyTeacherProposalToCertificate(grade), {
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
			>
			<template #actions="{selected}">
				<button class="btn btn-primary" :disabled="!selected.length" @click="copyGrades(selected)">
					<i class="fa-solid fa-arrow-left"></i> {{ $p.t('stv/grades_action_copy') }}
				</button>
			</template>
		</core-filter-cmpt>
	</div>`
};