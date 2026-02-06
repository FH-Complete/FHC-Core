import {CoreFilterCmpt} from "../../../../filter/Filter.js";

import ApiStvPrestudent from '../../../../../api/factory/stv/prestudent.js';

export default{
	components: {
		CoreFilterCmpt
	},
	props: {
		personId: Number,
		prestudentId: Number
	},
	data() {
		return {
			layout: 'fitDataFill',
			layoutColumnsOnNewData:	false,
			height:	'auto',
			selectableRows:	false
		}
	},
	computed: {
		tabulatorOptions() {
			const options = {
				ajaxURL: 'dummy',
				ajaxRequestFunc: () => this.$api.call(ApiStvPrestudent.getHistoryPrestudents(this.personId)),
				ajaxResponse: (url, params, response) => response.data,
				//autoColumns: true,
				persistenceID: 'stv-details-prestudent-history-20260204',
				columns:[
					{title:"StSem", field:"studiensemester_kurzbz"},
					{title:"Prio", field:"priorisierung"},
					{title:"Stg", field:"kurzbzlang"},
					{title:"Orgform", field:"orgform_kurzbz"},
					{title:"Studienplan", field:"bezeichnung"},
					{title:"UID", field:"student_uid"},
					{title:"Status", field:"status"},
					{title:"Prestudent ID", field:"prestudent_id", visible:false}
				],
				rowFormatter: row => {
					const rowData = row.getData();
					const element = row.getElement();
					if (["Abgewiesener","Abbrecher","Absolvent"].includes(rowData.status_kurzbz)) {
						element.classList.add('disabled');
					}
					if (rowData.prestudent_id == this.prestudentId) {
						element.classList.add('fw-bold');
					}
				},
			};
			return options;
		},
		tabulatorEvents() {
			const events = [
				{
					event: 'tableBuilt',
					handler: async () => {
						await this.$p.loadCategory(['lehre']);

						const setHeader = (field, text) => {
							const col = this.$refs.table.tabulator.getColumn(field);
							if (!col) return;

							const el = col.getElement();
							if (!el || !el.querySelector) return;

							const titleEl = el.querySelector('.tabulator-col-title');
							if (titleEl) {
								titleEl.textContent = text;
							}
						};

						setHeader('orgform_kurzbz', this.$p.t('lehre', 'organisationsform'));
						setHeader('bezeichnung', this.$p.t('lehre', 'studienplan'));
						setHeader('prestudent_id', this.$p.t('ui', 'prestudent_id'));

					}
				}
			];
			return events;
		},
	},
	watch: {
		personId() {
			this.$refs.table.reloadTable();
		},
	},
	template: `
	<div class="stv-details-prestudent-history h-100 pt-3">
		<core-filter-cmpt
			ref="table"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			table-only
			:side-menu="false"
			>
		</core-filter-cmpt>
	</div>`
}