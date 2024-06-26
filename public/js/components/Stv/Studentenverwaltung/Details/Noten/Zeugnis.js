import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import {CoreRESTClient} from '../../../../../RESTClient.js';
import ZeugnisActions from './Zeugnis/Actions.js';

const LOCAL_STORAGE_ID = 'stv_details_noten_zeugnis_2024-01-11_stdsem_all';

export default {
	components: {
		CoreFilterCmpt,
		ZeugnisActions
	},
	props: {
		student: Object
	},
	data() {
		return {
			validStudent: true,
			tabulatorEvents: [],
			stdsem: ''
		};
	},
	computed: {
		ajaxURL() {
			return CoreRESTClient._generateRouterURI('components/stv/Noten/getZeugnis/' + this.student.prestudent_id + this.stdsem);
		},
		tabulatorOptions() {
			return {
				ajaxURL: this.ajaxURL,
				ajaxResponse: (url, params, response) => {
					if (!response.retval)
						this.validStudent = false;
					else
						this.validStudent = true;
					return response.retval || [];
				},
				columns: [
					{ field: 'zeugnis', title: 'Zeugnis', formatter: 'tickCross' },
					{ field: 'lehrveranstaltung_bezeichnung', title: 'Lehrveranstaltung' },
					{ field: 'note_bezeichnung', title: 'Note' },
					{ field: 'uebernahmedatum', title: 'Übernahmedatum', visible: false },
					{ field: 'benotungsdatum', title: 'Benotungsdatum', visible: false },
					{ field: 'benotungsdatum-iso', title: 'Benotungsdatum ISO', visible: false },
					{ field: 'studiensemester_kurzbz', title: 'Studiensemester', visible: false },
					{ field: 'note', title: 'Note Numerisch', visible: false },
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
				],
				layout: 'fitDataStretch',
				height: '100%',
				selectable: true,
				selectableRangeMode: 'click',
				persistenceID: 'stv-details-noten-zeugnis'
			};
		}
	},
	watch: {
		ajaxURL(n) {
			if (this.$refs.table)
				this.$refs.table.tabulator.setData(n);
		}
	},
	methods: {
		setGrades(selected) {
			CoreRESTClient
				.post('components/stv/Noten/update', selected)
				.then(this.$refs.table.reloadTable)
				.catch(this.$fhcAlert.handleFormValidation);
		},
		saveStdsem(event) {
			window.localStorage.setItem(LOCAL_STORAGE_ID, event.target.value ? 'true' : '');
		}
	},
	created() {
		const savedPath = window.localStorage.getItem(LOCAL_STORAGE_ID);
		this.stdsem = savedPath ? '/all' : '';
	},
	// TODO(chris): phrasen
	template: `
	<div class="stv-details-noten-zeugnis h-100 d-flex flex-column">
		<div v-if="!validStudent">Kein Student</div>
		<template v-else>
			<div class="mb-3">
				<select class="form-select" v-model="stdsem" @input="saveStdsem">
					<option value="">Aktuelles Semester</option>
					<option value="/all">Alle Semester</option>
				</select>
			</div>
			<core-filter-cmpt
				ref="table"
				:tabulator-options="tabulatorOptions"
				:tabulator-events="tabulatorEvents"
				table-only
				:side-menu="false"
				reload
				>
				<template #actions="{selected}">
					<zeugnis-actions :selected="selected" @set-grades="setGrades"></zeugnis-actions>
				</template>
			</core-filter-cmpt>
		</template>
	</div>`
};