import {CoreFilterCmpt} from "../../../components/filter/Filter.js";
import Mailverteiler from "./ProfilComponents/Mailverteiler.js";
import QuickLinks from "./ProfilComponents/QuickLinks.js";
import RoleInformation from "./ProfilComponents/RoleInformation.js";
import ProfilEmails from "./ProfilComponents/ProfilEmails.js";
import ProfilInformation from "./ProfilComponents/ProfilInformation.js";

export default {
	components: {
		CoreFilterCmpt,
		Mailverteiler,
		QuickLinks,
		RoleInformation,
		ProfilEmails,
		ProfilInformation,
	},
	inject: ["collapseFunction", "language"],
	data() {
		return {
			collapseIconFunktionen: true,
			preloadedPhrasen:{},
			funktionen_table_options: {
				persistenceID: "filterTableMaViewProfilFunktionen",
				persistence: {
					columns: false
				},
				height: 300,
				layout: "fitColumns",
				responsiveLayout: "collapse",
				responsiveLayoutCollapseUseFormatters: false,
				responsiveLayoutCollapseFormatter: Vue.$collapseFormatter,
				columns: [
					//? option when wanting to hide the collapsed list

					{
						title:
							"<i id='collapseIconFunktionen' role='button' class='fa-solid fa-angle-down  '></i>",
						field: "collapse",
						headerSort: false,
						headerFilter: false,
						formatter: "responsiveCollapse",
						maxWidth: 40,
						headerClick: this.collapseFunction,
						visible: true
					},
					{
						title: Vue.computed(() => this.$p.t('ui/bezeichnung')),
						field: "Bezeichnung",
						headerFilter: true,
						minWidth: 200,
						visible: true
					},
					{
						title: Vue.computed(() => this.$p.t('lehre/organisationseinheit')),
						field: "Organisationseinheit",
						headerFilter: true,
						minWidth: 200,
						visible: true
					},
					{
						title: Vue.computed(() => this.$p.t('global/gueltigVon')),
						field: "Gültig_von",
						headerFilter: true,
						resizable: true,
						minWidth: 200,
						visible: true
					},
					{
						title: Vue.computed(() => this.$p.t('global/gueltigBis')),
						field: "Gültig_bis",
						headerFilter: true,
						resizable: true,
						minWidth: 200,
						visible: true
					},
					{
						title: Vue.computed(() => this.$p.t('profil/wochenstunden')),
						field: "Wochenstunden",
						headerFilter: true,
						minWidth: 200,
						visible: true
					},
				],
			},
		};
	},

	//? this is the prop passed to the dynamic component with the custom data of the view
	props: ["data"],
	methods: {
		funktionenTableBuilt: function () {
			this.$refs.funktionenTable.tabulator.setData(this.data.funktionen);
		},
	},
	watch: {
		'data.funktionen'(newVal) {
			if(this.$refs.funktionenTable) this.$refs.funktionenTable.tabulator.setData(newVal);
		},
		'language.value'(newVal) {  // reevaluates computed phrasen
			if(this.$refs.funktionenTable) this.$refs.funktionenTable.tabulator.setColumns(this.funktionen_table_options.columns)
		}
	},
	computed: {
		getTelefonValue() {
			if(this.data.standort_telefon?.kontakt) {
				return this.data.standort_telefon.kontakt + " " + this.data.telefonklappe
			} else if(this.data.standort_telefon) {
				return this.data.standort_telefon + " " + this.data.telefonklappe
			} else {
				return this.data.telefonklappe
			}
		},
		fotoStatus() {
			return this.data?.fotoStatus ?? null;
		},

		personEmails() {
			return this.data?.emails ? this.data.emails : [];
		},

		profilInformation() {
			if (!this.data) {
				return {};
			}

			return {
				Vorname: this.data.vorname,
				Nachname: this.data.nachname,
				Username: this.data.username,
				Anrede: this.data.anrede,
				Titel: this.data.titel,
				Postnomen: this.data.postnomen,
				foto_sperre: this.data.foto_sperre,
				foto: this.data.foto,
			};
		},

		roleInformation() {
			if (!this.data) {
				return {};
			}
			return {
				geburtsdatum: {
					label: `${this.$p.t('profil','Geburtsdatum')}`,
					value: this.data.gebdatum
				},
				geburtsort: {
					label: `${this.$p.t('profil','Geburtsort')}`,
					value: this.data.gebort
				},
				personenkennzeichen: {
					label: `${this.$p.t('profil','Kurzzeichen')}`,
					value: this.data.kurzbz
				},
				telefon: {
					label: `${this.$p.t('profil','Telefon')}`,
					value: this.getTelefonValue
				},
				office: {
					label: `${this.$p.t('profil','Büro')}`,
					value: this.data.ort_kurzbz
				}
			};
		},
	},
	created(){
		this.$p.loadCategory(["ui", "lehre", "global", "profil"]).then(() => {
			this.preloadedPhrasen.bezeichnungPhrase = this.$p.t('ui/bezeichnung');
			this.preloadedPhrasen.organisationseinheitPhrase = this.$p.t('lehre/organisationseinheit');
			this.preloadedPhrasen.gueltigVonPhrase = this.$p.t('global/gueltigVon');
			this.preloadedPhrasen.gueltigBisPhrase = this.$p.t('global/gueltigBis');
			this.preloadedPhrasen.wochenstundenPhrase = this.$p.t('profil/wochenstunden');
			this.preloadedPhrasen.loaded = true;
		});
	},

	template: /*html*/ `

<div class="container-fluid text-break fhc-form"  >
    <!-- ROW -->
    <div class="row">
        <!-- HIDDEN QUICK LINKS -->
        <!-- TODO: uncomment when implemented
            <div  class="d-md-none col-12 ">

            <quick-links :title="$p.t('profil','quickLinks')" :mobile="true" ></quick-links>

            </div>
            -->
        <!-- END OF HIDDEN QUCK LINKS -->
        <!-- MAIN PANNEL -->
        <div class="col-sm-12 col-md-8 col-xxl-9 ">
            <!-- ROW WITH PROFIL IMAGE AND INFORMATION -->
            <!-- INFORMATION CONTENT START -->
            <!-- ROW WITH THE PROFIL INFORMATION -->
            <div class="row mb-4">
                <!-- FIRST KAESTCHEN -->
                <div  class="col-lg-12 col-xl-6 ">
                    <div class="row mb-4">
                        <div class="col">
                            <!-- Profil Informationen -->
                            <profil-information :title="$p.t('profil','mitarbeiterIn')" :data="profilInformation" :fotoStatus="fotoStatus"></profil-information>
                        </div>
                    </div>
                    <!-- START OF SECOND PROFIL  INFORMATION COLUMN -->
                    <!-- END OF PROFIL INFORMATION ROW -->
                    <!-- INFORMATION CONTENT END -->
                </div>
                <div  class="col-xl-6 col-lg-12 ">
                    <div class="row mb-4">
                        <div class="col">
                            <!-- EMAILS -->
                            <profil-emails :title="this.$p.t('person','email')" :data="personEmails"></profil-emails>
                        </div>
                    </div>
                    <!-- SECOND ROW OF SECOND COLUMN IN MAIN CONTENT -->
                    <div class="row mb-4">
                        <div  class=" col-lg-12">
                            <!-- roleInformation -->
                            <role-information :data="roleInformation" :title="$p.t('profil','mitarbeiterInformation')"></role-information>
                        </div>
                    </div>
                    <!-- END OF SECOND ROW OF SECOND COLUMN IN MAIN CONTENT -->
                    <!-- END OF THE SECOND INFORMATION COLUMN -->
                </div>
                <!-- START OF THE SECOND PROFIL INFORMATION ROW -->
                <!-- ROW WITH PROFIL IMAGE AND INFORMATION END -->
            </div  >
            <!-- SECOND ROW UNDER THE PROFIL IMAGE AND INFORMATION WITH THE TABLES -->
            <div class="row">
                <!-- FIRST TABLE -->
                <div class="col-12 mb-4" >
                    <core-filter-cmpt v-if="preloadedPhrasen.loaded" @tableBuilt="funktionenTableBuilt" :title="$p.t('person','funktionen')"  ref="funktionenTable" :tabulator-options="funktionen_table_options"  tableOnly :sideMenu="false" />
                </div>
                <!-- END OF THE ROW WITH THE TABLES UNDER THE PROFIL INFORMATION -->
            </div>
            <!-- END OF MAIN CONTENT COL -->
        </div>
        <!-- START OF SIDE PANEL -->
        <div  class="col-md-4 col-xxl-3 col-sm-12 text-break" >
            <!-- VISIBLE UNTIL VIEWPORT MD -->
            <!--TODO: uncomment when implemented
                <div  class="row d-none d-md-block mb-3">
                  <div class="col">
                
                   <quick-links :title="$p.t('profil','quickLinks')" ></quick-links>
                
                  </div>
                </div>
                -->
            <div  class="row">
                <div class="col">
                    <!-- MAILVERTEILER -->
                    <mailverteiler :data="data?.mailverteiler" :title="$p.t('profil','mailverteiler')"></mailverteiler>
                </div>
            </div>
            <!-- END OF SIDE PANEL -->
        </div>
        <!-- END OF CONTAINER ROW-->
    </div>
    <!-- END OF CONTAINER -->
</div>
`,
};
