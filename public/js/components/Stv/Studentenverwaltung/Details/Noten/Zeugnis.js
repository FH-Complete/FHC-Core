import {CoreFilterCmpt} from "../../../../filter/Filter.js";
import {CoreRESTClient} from '../../../../../RESTClient.js';

export default {
	components: {
		CoreFilterCmpt
	},
	props: {
		student: Object
	},
	data() {
		return {
			validStudent: true,
			tabulatorEvents: []
		};
	},
	computed: {
		ajaxURL() {
			return CoreRESTClient._generateRouterURI('components/stv/Noten/getZeugnis/' + this.student.prestudent_id);
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
				persistence: true
			};
		}
	},
	template: `
	<div class="stv-details-noten-zeugnis h-100 d-flex flex-column">
		<div v-if="!validStudent">Kein Student</div>
		<core-filter-cmpt
			v-else
			ref="table"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			table-only
			:side-menu="false"
			reload
			>
		</core-filter-cmpt>
	</div>`
};