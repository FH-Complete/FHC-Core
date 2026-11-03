import {CoreFilterCmpt} from '../filter/Filter.js';
import {CoreNavigationCmpt} from '../navigation/Navigation.js';
import CoreBaseLayout from '../../components/layout/BaseLayout.js';
import ApiTempusIssues from '../../api/factory/tempus/issues.js';
import FormInput from '../Form/Input.js';
import { extendedHeaderFilter } from "../../tabulator/filters/extendedHeaderFilter.js";

export default {
	name: 'TempusIssues',
	inheritAttrs: false,
	props: {
		config: Object,
		defaultSemester: String,
	},
	components: {
		CoreFilterCmpt,
		CoreNavigationCmpt,
		CoreBaseLayout,
		FormInput
	},
	data() {
		return {
			appSideMenuEntries: {},
			isChecking: false,
			count: 0,
			von: luxon.DateTime.now().startOf('week').toISODate(),
			bis: luxon.DateTime.now().plus({ days: 14 }).toISODate(),
		};
	},
	computed: {
		tabulatorOptions() {
			return {
				index: 'issue_id',
				height: '70vh',
				pagination: true,
				paginationSize: 100,
				paginationSizeSelector: [50, 100, 250],
				paginationCounter: 'rows',

				columnDefaults: {
					headerFilter: true,
					headerFilterFunc: extendedHeaderFilter,
				},
				layout: 'fitDataStretch',
				placeholder: 'Keine offenen Kollisionen',
				persistenceID: 'tempus_issues_2026_11_03_v1',
				locale: true,
				columns: [
					{
						title: 'Termin',
						titlePhrase: "ui/from",
						field: 'von',
						formatter: (cell) => {
							const value = cell.getValue();
							if (!value) return '';
							const datum = luxon.DateTime.fromSQL(value);
							return datum.isValid ? datum.toFormat('dd.MM.yyyy HH:mm') : value;
						},
					},
					{
						title: 'Kategorie',
						titlePhrase: "ui/kategorie",
						field: 'kategorie',
						headerFilter: 'list',
						headerFilterParams: { values: ['Kollision', 'AZG'], clearable: true },
					},
					{ title: 'Art', titlePhrase: 'tabulator/error', field: 'fehler_kurzbz', visible: false },
					{ title: 'Meldung', titlePhrase: 'global/nachricht', field: 'inhalt', minWidth: 300 },
					{ title: 'OE', titlePhrase: 'lehre/organisationseinheit', field: 'oe_anzeige'},
					{ title: 'Status', titlePhrase: 'global/status', field: 'status_kurzbz'},
					{ title: 'Tage bis Termin', field: 'tage_bis_termin', },
					{ title: 'Issue', field: 'issue_id' },
					{
						title: 'Aktion',
						field: 'aktion',
						headerSort: false,
						hozAlign: 'center',
						width: 130,
						formatter: () => '<button type="button" class="btn btn-sm btn-outline-success">Akzeptieren</button>',
						cellClick: (e, cell) => this.akzeptieren(cell.getRow().getData()),

					},
				],
			};
		},

		tabulatorEvents() {
			return [
				{
					event: 'rowClick',
					handler: (e, row) => {
						if (e.target.closest('button'))
							return;
						this.zumTermin(row.getData());
					},
				},
				{
					event: 'dataProcessed',
					handler: (data) => {this.count = data.length}
				},
			];
		},
	},
	methods: {
		newSideMenuEntryHandler: function(payload) {
			this.appSideMenuEntries = payload;
		},
		pruefen() {
				this.isChecking = true;
				this.$api
					.call(ApiTempusIssues.check(this.von, this.bis))
					.then(result => {
						this.$refs.issueTable.reloadTable();
						const errors = result.meta?.errors || [];
						if (errors.length)
							this.$fhcAlert.alertError(errors.join('\n'));
					})
					.catch(this.$fhcAlert.handleSystemError)
					.finally(() => {
						this.isChecking = false;
					});
			},
		zumTermin(issue) {
			if (!issue.von) return;
			const date = issue.von.substring(0, 10);
			const id = Date.now() + '-' + Math.random();

			const issueChannel = new BroadcastChannel('tempus-issues');
			let beantwortet = false;

			issueChannel.addEventListener('message', (e) => {
				if (e.data?.type === 'ok' && e.data.id === id)
					beantwortet = true;
			});

			issueChannel.postMessage({ type: 'jump', id, date, kalender_id: issue.kalender_id_a });

			setTimeout(() => {
				issueChannel.close();
				if (!beantwortet)
					window.open(this.$router.resolve({ name: 'tempus', query: { date } }).href, '_blank');
			}, 300);
		},
		async akzeptieren(issue) {
			const ok = await this.$fhcAlert.confirm({
				header: 'Kollision akzeptieren',
				message: 'Diese Kollision als gewollt markieren?',
				acceptLabel: 'Akzeptieren',
			});
			if (!ok)
				return;

			this.$api
				.call(ApiTempusIssues.akzeptieren(issue.issue_id))
				.then(() => this.$refs.issueTable.reloadTable())
				.catch(this.$fhcAlert.handleSystemError);
		},
	},
	template: `
		<core-navigation-cmpt v-bind:add-side-menu-entries="appSideMenuEntries"></core-navigation-cmpt>
		<core-base-layout>
			<template #main>
				<div class="d-flex align-items-center gap-2 mb-3">
					<h4 class="m-0">{{ count }} Offene Kollisionen</h4>
				</div>
				<core-filter-cmpt
					ref="issueTable"
					filter-type="TempusIssues"
					:tabulator-events="tabulatorEvents"
					:tabulator-options="tabulatorOptions"
					@nw-new-entry="newSideMenuEntryHandler"
				>
					<template #actions>
						<div class="d-flex align-items-end gap-2">
							<form-input
								type="datepicker"
								v-model="von"
								name="von"
								format="dd.MM.yyyy"
								model-type="yyyy-MM-dd"
								:enable-time-picker="false"
								text-input
								auto-apply
								:label="$p.t('ui', 'von')"
								:teleport="true"
							/>
							<form-input
								type="datepicker"
								v-model="bis"
								name="bis"
								format="dd.MM.yyyy"
								model-type="yyyy-MM-dd"
								:enable-time-picker="false"
								:min-date="von"
								text-input
								auto-apply
								:label="$p.t('global', 'bis')"
								:teleport="true"
							/>
							<button type="button" class="btn btn-outline-secondary" :disabled="isChecking || !von || !bis" @click="pruefen">
								<span v-if="isChecking" class="spinner-border spinner-border-sm"></span>
									<i v-else class="fa-solid fa-rotate"></i>
									Erneut prüfen
							</button>
						</div>
					</template>
				</core-filter-cmpt>
			</template>
		</core-base-layout>
	`
};