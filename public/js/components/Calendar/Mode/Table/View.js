import {CoreFilterCmpt} from "../../../../components/filter/Filter.js";
import BsModal from '../../../Bootstrap/Modal.js';
import FormInput from "../../../Form/Input.js";
import ApiDetails from "../../../../api/lehrveranstaltung/details.js";


export default {
	name: "TableView",
	inject: {
		events: "events",
		timezone: "timezone"
	},
	components: {
		CoreFilterCmpt,
		BsModal,
		FormInput
	},
	props: {
		day: {
			type: luxon.DateTime,
			required: true
		}
	},
	data()
	{
		return {
			raumtyp_array: []
		}
	},
	computed: {
		start() {
			return this.day.startOf('week', { useLocaleWeeks: true });
		},
		preparedEvents() {
			const end = this.start.plus({ days: 7 });
			return this.events
				.filter(e => e.start < end && e.end > this.start)
				.sort((a, b) => a.start.ts - b.start.ts)
				.map(event => ({
					...event.orig,
					row_index: event.id,
				}));
		},
		tabulatorOptions() {
			return {
				index: "row_index",
				layout: 'fitDataStretch',
				placeholder: "Keine Daten verfügbar",
				persistenceID: "2026_03_09_table_view_v1",
				data: this.preparedEvents,
				columns: [
					{
						formatter: 'rowSelection',
						titleFormatter: 'rowSelection',
						titleFormatterParams: {
							rowRange: "active"
						},
						headerSort: false,
						width: 40
					},
					{title: 'Datum', field: 'datum', headerFilter: "input", formatter: (cell) => {
							let val = cell.getValue();
							if (!val)
								return '&nbsp;';
							return luxon.DateTime.fromISO(val).toFormat('dd.MM.yyyy')
						}
					},
					{title: 'Von', field: 'beginn', headerFilter: "input"},
					{title: 'Bis', field: 'ende', headerFilter: "input"},
					{title: 'Lehrfach', field: 'lehrfach', headerFilter: "input"},
					{title: 'Bezeichnung', field: 'lehrfach_bez', headerFilter: "input"},
					{title: 'Lehrform', field: 'lehrform', headerFilter: "input"},
					{title: 'Raum', field: 'ort_kurzbz', headerFilter: "input"},
					{
						title: 'Lektor',
						field: 'lektor',
						headerFilter: "input",
						mutator: (value) => {
							if (!value)
								return '';
							return value.map(l => l.kurzbz).join(', ') ?? '–'
						}
					},
					{title: 'OE', field: 'organisationseinheit', headerFilter: "input"},
					{title: 'Status', field: 'status_kurzbz', headerFilter: "input"},
				]
			}
		}
	},
	methods:
	{
		openModal() {
			this.$refs.raumModal.show();
		}
	},
	watch: {
		preparedEvents(newData) {
			this.$refs.tableViewTable?.tabulator?.setData(newData);
		}
	},
	mounted() {

		this.$api.call(ApiDetails.getRaumtyp())
			.then(result => {
				this.raumtyp_array = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);

	},
	template: /* html */`
	<div class="fhc-calendar-mode-table-view h-100 overflow-auto">
		<core-filter-cmpt
			ref="tableViewTable"
			:tabulator-options="tabulatorOptions"
			:table-only="true"
			:side-menu="false"
			:download="true"
		>
			<template #actions>
				<button class="btn btn-outline-secondary btn-sm">Verschieben</button>
				<button @click="openModal" class="btn btn-outline-secondary btn-sm">Raum wechsel</button>
			</template>
		</core-filter-cmpt>
		
		<bs-modal ref="raumModal" class="bootstrap-prompt" dialogClass="modal-lg">
			<template #title>Raum verschiebung</template>
				<form-input
					:label="$p.t('lehre', 'raumtyp')"
					type="select"
					container-class="col-3"
					name="raumtyp"
				>
				<option
					v-for="raumtyp in raumtyp_array"
					:value="raumtyp.raumtyp_kurzbz"
					:key="raumtyp.raumtyp_kurzbz"
				>
					{{ raumtyp.raumtyp_kurzbz }} {{ raumtyp.beschreibung }}
				</option>
			</form-input>
			<template #footer>
				<button type="button" class="btn btn-primary">{{ $p.t('ui', 'speichern') }}</button>
			</template>
		</bs-modal>
	</div>
	`
}