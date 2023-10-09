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
				// TODO(chris): select only one? selectMultiple with click?
				index: 'prestudent_id',
				//persistence: true
			},
			tabulatorEvents: [
				{
					event: 'rowSelectionChanged',
					handler: this.rowSelectionChanged
				},
				{
					event: 'dataProcessed',
					handler: this.autoSelectRows
				}
			],
			lastSelected: null
		}
	},
	methods: {
		actionNewPrestudent() {
			console.log('actionNewPrestudent');
		},
		rowSelectionChanged(data) {
			this.$emit('update:selected', data);
		},
		autoSelectRows(data) {
			if (this.lastSelected) {
				// NOTE(chris): reselect rows on refresh
				let selected = this.lastSelected.map(el => this.$refs.table.tabulator.getRow(el.prestudent_id))
				// TODO(chris): unselect current item if it's no longer in the table?
				// or maybe reselect only the last one?
				selected = selected.filter(el => el);

				this.lastSelected = null;

				if (selected.length)
					this.$refs.table.tabulator.selectRow(selected);
			} else if(this.lastSelected === undefined) {
				// NOTE(chris): select row if it's the only one (preferably only on startup)
				if (data.length == 1) {
					this.$refs.table.tabulator.selectRow(this.$refs.table.tabulator.getRows());
				}
			}
		},
		updateUrl(url, first) {
			this.lastSelected = first ? undefined : this.selected;
			if (url)
				url = CoreRESTClient._generateRouterURI(url);
			if (!this.$refs.table.tableBuilt)
				this.$refs.table.tabulator.on("tableBuilt", () => {
					this.$refs.table.tabulator.setData(url);
				});
			else
				this.$refs.table.tabulator.setData(url);
		}
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