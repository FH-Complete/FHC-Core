import {CoreFilterCmpt} from "../../filter/Filter.js";
import {CoreRESTClient} from '../../../RESTClient.js';

export default {
	components: {
		CoreFilterCmpt
	},
	props: {
		selected: Array
	},
	emits: [
		'update:selected'
	],
	data() {
		return {
			tabulatorOptions: {
				columns:[
					{title:"UID", field:"uid"},
					{title:"TitelPre", field:"titelpre"},
					{title:"Nachname", field:"nachname"},
					{title:"Vorname", field:"vorname"},
					{title:"Wahlname", field:"wahlname", visible:false},
					{title:"Vornamen", field:"vornamen", visible:false},
					{title:"TitelPost", field:"titelpost"},
					{title:"SVNR", field:"svnr"},
					{title:"Ersatzkennzeichen", field:"ersatzkennzeichen"},
					{title:"Geburtsdatum", field:"geburtsdatum_iso"},
					{title:"Geschlecht", field:"geschlecht"},
					{title:"Sem.", field:"semester"},
					{title:"Verb.", field:"verband"},
					{title:"Grp.", field:"gruppe"},
					{title:"Studiengang", field:"studiengang"},
					{title:"Studiengang_kz", field:"studiengang_kz", visible:false},
					{title:"Personenkennzeichen", field:"matrikelnummer"},
					{title:"PersonID", field:"person_id"},
					{title:"Status", field:"status"},
					{title:"Status Datum", field:"status_datum_iso", visible:false},
					{title:"Status Bestaetigung", field:"status_bestaetigung_iso", visible:false},
					{title:"Status Datum ISO", field:"status_datum_iso", visible:false},
					{title:"Status Bestaetigung ISO", field:"status_bestaetigung_iso", visible:false},
					{title:"EMail (Privat)", field:"mail_privat", visible:false},
					{title:"EMail (Intern)", field:"mail_intern", visible:false},
					{title:"Anmerkungen", field:"anmerkungen", visible:false},
					{title:"AnmerkungPre", field:"anmerkungpre", visible:false},
					{title:"OrgForm", field:"orgform"},
					{title:"Aufmerksamdurch", field:"orgform", visible:false},
					{title:"Gesamtpunkte", field:"punkte", visible:false},
					{title:"Aufnahmegruppe", field:"aufnahmegruppe_kurzbz", visible:false},
					{title:"Dual", field:"dual_bezeichnung", visible:false},
					{title:"Matrikelnummer", field:"matr_nr", visible:false},
					{title:"Studienplan", field:"studienplan_bezeichnung"},
					{title:"PreStudentInnenID", field:"prestudent_id"},
					{title:"Priorität", field:"priorisierung_realtiv"},
					{title:"Mentor", field:"mentor", visible:false},
					{title:"Aktiv", field:"aktiv", visible:false},
					{title:"GeburtsdatumISO", field:"geburtsdatum_iso", visible:false},
				],

				layout: 'fitDataFill',
				layoutColumnsOnNewData: false,
				height: 'auto',
				selectable: true,
				//persistence: true
			},
			tabulatorEvents: [
				{
					event: 'rowSelectionChanged',
					handler: this.rowSelectionChanged
				}
			]
		}
	},
	methods: {
		actionNewPrestudent() {
			console.log('actionNewPrestudent');
		},
		rowSelectionChanged(data) {
			this.$emit('update:selected', data);
		},
		updateUrl(url) {
			this.$refs.table.tabulator.on("dataProcessed", () => {
				let rows = this.$refs.table.tabulator.getRows();
				if (rows.length && rows.length == 1) {
					this.$refs.table.tabulator.selectRow();
				}
			});
			
			if (!this.$refs.table.tableBuilt)
				this.$refs.table.tabulator.on("tableBuilt", () => {
					this.$refs.table.tabulator.setData(CoreRESTClient._generateRouterURI(url));
				});
			else
				this.$refs.table.tabulator.setData(CoreRESTClient._generateRouterURI(url));
		}
	},
	mounted() {
	},
	template: `
	<div class="stv-list h-100 pt-3">
		<core-filter-cmpt
			ref="table"
			:tabulator-options="tabulatorOptions"
			:tabulator-events="tabulatorEvents"
			table-only
			:side-menu="false"
			reload
			new-btn-show
			new-btn-label="Prestudent"
			@click:new="actionNewPrestudent"
		>
		</core-filter-cmpt>
	</div>`
};