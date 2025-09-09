import {CoreFilterCmpt} from "../../../../filter/Filter.js";

import ApiStvGrades from '../../../../../api/factory/stv/grades.js';

export default {
	components: {
		CoreFilterCmpt
	},
	emits: [
		"copied"
	],
	inject: {
		currentSemester: {
			from: 'currentSemester',
			required: true
		}
	},
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
				ajaxRequestFunc: () => this.$api.call(ApiStvGrades.getRepeaterGrades(
					this.student.prestudent_id,
						(!this.allSemester ? this.currentSemester : null)
				)),
				ajaxResponse: (url, params, response) => {
					return response.data || [];
				},
				columns: [
					{ field: 'lv_bezeichnung', title: this.$p.t('lehre/lehrveranstaltung') },
					{ field: 'note_bezeichnung', title: this.$p.t('lehre/note') },
					{ field: 'insertvon', title: this.$p.t('profil/mitarbeiterIn'), visible: false },
					{ field: 'benotungsdatum', title: this.$p.t('stv/grades_gradingdate'), visible: false },
					{ field: 'freigabedatum', title: this.$p.t('stv/grades_approvaldate'), visible: false },
					{ field: 'studiensemester_kurzbz', title: this.$p.t('lehre/studiensemester'), visible: false },
					{ field: 'stg_bezeichnung', title: this.$p.t('lehre/studiengang'), visible: false },
					{ field: 'note', title: this.$p.t('stv/grades_numericgrade'), visible: false },
					{ field: 'prestudent_uid', title: this.$p.t('global/prestudentID'), visible: false },
					{ field: 'lehrveranstaltung_id', title: this.$p.t('lehre/lehrveranstaltung_id'), visible: false }
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
				grade => this.$api
					.call(
						ApiStvGrades.copyRepeaterGradeToCertificate(grade),
						{ errorHeader: grade.lv_bezeichnung }
					)
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
		this.$p.loadCategory(['global', 'stv', 'lehre', 'profil'])
			.then(() => {
				if (this.$refs.table.tableBuilt)
					this.$refs.table.tabulator.columnManager.setColumns(this.tabulatorOptions.columns);
			});
	},
	template: `
	<div class="stv-details-noten-repeater d-flex flex-column">
		<core-filter-cmpt
			ref="table"
			:title="$p.t('stv/grades_title_repeater')"
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