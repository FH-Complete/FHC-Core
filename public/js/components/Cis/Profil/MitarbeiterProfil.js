import {CoreFilterCmpt} from "../../../components/filter/Filter.js";
import EditProfil from "./ProfilModal/EditProfil.js";
import Adresse from "./ProfilComponents/Adresse.js";
import Kontakt from "./ProfilComponents/Kontakt.js";
import FetchProfilUpdates from "./ProfilComponents/FetchProfilUpdates.js";
import Mailverteiler from "./ProfilComponents/Mailverteiler.js";
import AusweisStatus from "./ProfilComponents/FhAusweisStatus.js";
import QuickLinks from "./ProfilComponents/QuickLinks.js";
import ProfilEmails from "./ProfilComponents/ProfilEmails.js";
import RoleInformation from "./ProfilComponents/RoleInformation.js";
import ProfilInformation from "./ProfilComponents/ProfilInformation.js";

import ApiProfilUpdate from '../../../api/factory/profilUpdate.js';

export default {
	components: {
		CoreFilterCmpt,
		EditProfil,
		Adresse,
		Kontakt,
		FetchProfilUpdates,
		AusweisStatus,
		Mailverteiler,
		QuickLinks,
		ProfilEmails,
		RoleInformation,
		ProfilInformation,
	},

	inject: ["sortProfilUpdates", "collapseFunction", "language","isEditable"],

	data() {
		return {
			showModal: false,
			editDataFilter: null,
			preloadedPhrasen:{},
			// tabulator options
			funktionen_table_options: {
				persistenceID: "filterTableMaProfilFunktionen",
				persistence: {
					columns: false
				},
				height: 300,
				layout: "fitColumns",
				responsiveLayout: "collapse",
				responsiveLayoutCollapseUseFormatters: false,
				responsiveLayoutCollapseFormatter: Vue.$collapseFormatter,
				columns: [
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
						title: Vue.computed(() => this.preloadedPhrasen.bezeichnungPhrase),
						field: "Bezeichnung",
						headerFilter: true,
						minWidth: 200,
						visible: true
					},
					{
						title: Vue.computed(() => this.preloadedPhrasen.organisationseinheitPhrase),
						field: "Organisationseinheit",
						headerFilter: true,
						minWidth: 200,
						visible: true
					},
					{
						title: Vue.computed(() => this.preloadedPhrasen.gueltigVonPhrase),
						field: "Gültig_von",
						headerFilter: true,
						resizable: true,
						minWidth: 200,
						visible: true
					},
					{
						title: Vue.computed(() => this.preloadedPhrasen.gueltigBisPhrase),
						field: "Gültig_bis",
						headerFilter: true,
						resizable: true,
						minWidth: 200,
						visible: true
					},
					{
						title: Vue.computed(() => this.preloadedPhrasen.wochenstundenPhrase),
						field: "Wochenstunden",
						headerFilter: true,
						minWidth: 200,
						visible: true
					},
				],
			},

			betriebsmittel_table_options: {
				persistenceID: "filterTableMaProfilBetriebsmittel",
				persistence: {
					columns: false
				},
				height: 300,
				layout: "fitColumns",
				responsiveLayout: "collapse",
				responsiveLayoutCollapseUseFormatters: false,
				responsiveLayoutCollapseFormatter: Vue.$collapseFormatter,
				data: [{betriebsmittel: "", Nummer: "", Ausgegeben_am: ""}],
				columns: [
					{
						title:
							"<i id='collapseIconBetriebsmittel' role='button' class='fa-solid fa-angle-down  '></i>",
						field: "collapse",
						headerSort: false,
						headerFilter: false,
						formatter: "responsiveCollapse",
						maxWidth: 40,
						headerClick: this.collapseFunction,
						visible: true
					},
					{
						title: Vue.computed(() => this.preloadedPhrasen.entlehnteBetriebsmittelPhrase),
						field: "betriebsmittel",
						headerFilter: true,
						minWidth: 200,
						visible: true
					},
					{
						title: Vue.computed(() => this.preloadedPhrasen.inventarnummerPhrase),
						field: "Nummer",
						headerFilter: true,
						resizable: true,
						minWidth: 200,
						visible: true
					},
					{
						title: Vue.computed(() => this.preloadedPhrasen.ausgabedatumPhrase),
						field: "Ausgegeben_am",
						headerFilter: true,
						minWidth: 200,
						visible: true
					},
				],
			}
		};
	},

	props: {
		data: Object,
		editData: Object,
	},
	
	methods: {
		betriebsmittelTableBuilt: function () {
			this.$refs.betriebsmittelTable.tabulator.setColumns(this.betriebsmittel_table_options.columns)
			this.$refs.betriebsmittelTable.tabulator.setData(this.data.mittel);
		},
		funktionenTableBuilt: function () {
			this.$refs.funktionenTable.tabulator.setColumns(this.funktionen_table_options.columns)
			this.$refs.funktionenTable.tabulator.setData(this.data.funktionen);
		},
		hideEditProfilModal: function () {
			//? checks the editModal component property result, if the user made a successful request or not
			if (this.$refs.editModal.result) {
				this.$api
					.call(ApiProfilUpdate.selectProfilRequest())
					.then((request) => {
						if (!request.error && request) {
							this.data.profilUpdates = request.data;
							this.data.profilUpdates.sort(this.sortProfilUpdates);
						} else {
							console.error("Error when fetching profile updates: " + res.data);
						}
					})
					.catch((err) => {
						console.error(err);
					});
			} else {
				// when modal was closed without submitting request
			}
			this.showModal = false;
			this.editDataFilter = null;
		},

		showEditProfilModal(view) {
			if (view) {
				this.editDataFilter = view;
			}

			this.showModal = true;
			Vue.nextTick(() => {
				this.$refs.editModal.show();
			});

			// after a state change, wait for the DOM updates to complete
		},

		fetchProfilUpdates: function () {
			this.$api
				.call(ApiProfilUpdate.selectProfilRequest())
				.then((res) => {
					if (!res.error && res) {
						this.data.profilUpdates = res.data?.length
							? res.data.sort(this.sortProfilUpdates)
							: null;
					}
				});
		},
		setTableColumnTitles() { // reevaluates computed phrasen
			if(this.$refs.betriebsmittelTable) this.$refs.betriebsmittelTable.tabulator.setColumns(this.betriebsmittel_table_options.columns)
			if(this.$refs.funktionenTable) this.$refs.funktionenTable.tabulator.setColumns(this.funktionen_table_options.columns)
		}
	},

	computed: {
		fotoStatus() {
			return this.data?.fotoStatus ?? false;
		},
		getTelefonValue() {
			if(this.data.standort_telefon?.kontakt) {
				return this.data.standort_telefon.kontakt + " " + this.data.telefonklappe
			} else if(this.data.standort_telefon) {
				return this.data.standort_telefon + " " + this.data.telefonklappe
			} else {
				return this.data.telefonklappe
			}
		},
		filteredEditData() {
			return this.editDataFilter
				? this.editData.data[this.editDataFilter]
				: this.editData;
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

	created() {
		// preload phrasen
		this.$p.loadCategory(["ui","lehre","global","profil"]).then(() => {
			this.preloadedPhrasen.bezeichnungPhrase = this.$p.t('ui/bezeichnung');
			this.preloadedPhrasen.organisationseinheitPhrase = this.$p.t('lehre/organisationseinheit');
			this.preloadedPhrasen.gueltigVonPhrase = this.$p.t('global/gueltigVon');
			this.preloadedPhrasen.gueltigBisPhrase = this.$p.t('global/gueltigBis');
			this.preloadedPhrasen.wochenstundenPhrase = this.$p.t('profil/wochenstunden');
			this.preloadedPhrasen.entlehnteBetriebsmittelPhrase = this.$p.t('profil/entlehnteBetriebsmittel');
			this.preloadedPhrasen.inventarnummerPhrase = this.$p.t('profil/inventarnummer');
			this.preloadedPhrasen.ausgabedatumPhrase = this.$p.t('profil/ausgabedatum');
			this.preloadedPhrasen.loaded=true;
		});
		//? sorts the profil Updates: pending -> accepted -> rejected
		this.data.profilUpdates?.sort(this.sortProfilUpdates);

	},
	watch: {
		'data.funktionen'(newVal) {
			if(this.$refs.funktionenTable) this.$refs.funktionenTable.tabulator.setData(newVal);
		},
		'data.mittel'(newVal) {
			if(this.$refs.betriebsmittelTable) this.$refs.betriebsmittelTable.tabulator.setData(newVal);
		},
		'language.value'(newVal) {
			this.setTableColumnTitles()
		}
	},
	template: /*html*/ `
<div class="container-fluid text-break fhc-form"  >
    <edit-profil v-if="showModal" ref="editModal" @hideBsModal="hideEditProfilModal" :value="JSON.parse(JSON.stringify(filteredEditData))" :title="$p.t('profil','profilBearbeiten')"></edit-profil>
    <div class="row">
        <div  class="d-md-none col-12 ">
            <!--TODO: uncomment when implemented
                <div class="row mb-3">
                           <div class="col">
                           <quick-links :title="$p.t('profil','quickLinks')" :mobile="true"></quick-links>
                           </div>
                         </div>-->
            <!-- Bearbeiten Button -->
            <div v-if="isEditable" class="row mb-3 ">
                <div class="col">
                    <button @click="()=>showEditProfilModal()" type="button" class="text-start card w-100 btn btn-outline-secondary" >
                        <div class="row">
                            <div class="col-auto">
                                <i class="fa fa-edit"></i>
                            </div>
                            <div class="col-auto">{{$p.t('ui','bearbeiten')}}</div>
                        </div>
                    </button>
                </div>
            </div>
            <div v-if="data.profilUpdates" class="row mb-3">
                <div class="col">
                    <!-- MOBILE PROFIL UPDATES -->
                    <fetch-profil-updates v-if="data.profilUpdates && data.profilUpdates.length" @fetchUpdates="fetchProfilUpdates"  :data="data.profilUpdates" ></fetch-profil-updates>
                </div>
            </div>
        </div>
        <!-- END OF HIDDEN ROW (HIDDEN IN VIEWPORTS GREATER THEN-EQUAL MD) -->
        <!-- MAIN PANNEL -->
        <div class="col-sm-12 col-md-8 col-xxl-9 ">
            <!-- ROW WITH PROFIL IMAGE AND INFORMATION -->
            <!-- INFORMATION CONTENT START -->
            <!-- ROW WITH THE PROFIL INFORMATION -->
            <div class="row mb-4">
                <div  class="col-lg-12 col-xl-6 ">
                    <div class="row mb-4">
                        <div class="col">
                            <!-- PROFIL INFORMATION -->
                            <profil-information @showEditProfilModal="showEditProfilModal" :title="$p.t('profil','mitarbeiterIn')" :data="profilInformation" :fotoStatus="fotoStatus"></profil-information>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div  class=" col-lg-12">
                            <!-- MITARBEITER INFO -->
                            <role-information :title="$p.t('profil','mitarbeiterInformation')" :data="roleInformation"></role-information>
                        </div>
                    </div>
                    <!-- START OF SECOND PROFIL  INFORMATION COLUMN -->
                </div>
                <div  class="col-xl-6 col-lg-12 ">
                    <div class="row mb-4">
                        <div class="col">
                            <!-- EMAILS -->
                            <profil-emails :title="this.$p.t('person','email')" :data="data.emails" ></profil-emails>
                        </div>
                    </div>
                    <div class="row mb-4 ">
                        <div class="col">
                            <!-- PRIVATE KONTAKTE-->
                            <div class="card">
                                <div class="card-header">
                                    <div class="row">
                                        <div @click="showEditProfilModal('Private_Kontakte')" class="col-auto" type="button">
                                            <i class="fa fa-edit"></i>
                                        </div>
                                        <div class="col">
                                            <span>{{$p.t('profil','privateKontakte')}}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body ">
                                    <div  class="gy-3  row ">
                                        <div v-for="element in data.kontakte" class="col-12">
                                            <Kontakt :data="element"></Kontakt>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col">
                            <!-- PRIVATE ADRESSEN-->
                            <div class="card">
                                <div class="card-header">
                                    <div class="row">
                                        <div @click="showEditProfilModal('Private_Adressen')" class="col-auto" type="button">
                                            <i class="fa fa-edit"></i>
                                        </div>
                                        <div class="col">
                                            <span>{{$p.t('profil','privateAdressen')}}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="gy-3 row ">
                                        <div v-for="element in data.adressen" class="col-12">
                                            <Adresse :data="element"></Adresse>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div  >
            <div class="row">
                <div class="col-12 mb-4" >
                    <!-- FUNKTIONEN TABELLE -->
                    <core-filter-cmpt
						v-if="preloadedPhrasen.loaded"
                    	@tableBuilt="funktionenTableBuilt"
						:title="$p.t('person','funktionen')"
						ref="funktionenTable"
						:tabulator-options="funktionen_table_options"
						tableOnly
						:sideMenu="false"
                     />
                </div>
                <div class="col-12 mb-4" >
                    <!-- BETRIEBSMITTEL TABELLE -->
                    <core-filter-cmpt
						v-if="preloadedPhrasen.loaded"
                    	@tableBuilt="betriebsmittelTableBuilt"
						:title="$p.t('profil','entlehnteBetriebsmittel')"
						ref="betriebsmittelTable"
						:tabulator-options="betriebsmittel_table_options"
						tableOnly
						:sideMenu="false"
                    />
                </div>
            </div>
        </div>
        <!-- START OF SIDE PANEL -->
        <div  class="col-md-4 col-xxl-3 col-sm-12 text-break" >
            <!--TODO: uncomment when implemented
                <div  class="row d-none d-md-block mb-3">
                           
                           <div class="col">
                            
                               <quick-links :title="$p.t('profil','quickLinks')"></quick-links>
                              
                                 
                             
                             </div>
                           </div>-->
            <!-- Bearbeiten Button -->
            <div class="row d-none d-md-block ">
                <div class="col mb-3">
                    <button @click="()=>showEditProfilModal()" type="button" class="text-start card w-100 btn btn-outline-secondary" >
                        <div class="row">
                            <div class="col-auto">
                                <i class="fa fa-edit"></i>
                            </div>
                            <div class="col-auto">{{$p.t('ui','bearbeiten')}}</div>
                        </div>
                    </button>
                </div>
            </div>
            <div v-if="data.profilUpdates" class="row d-none d-md-block mb-3">
                <div class="col mb-3">
                    <!-- PROFIL UPDATES -->
                    <fetch-profil-updates v-if="data.profilUpdates && data.profilUpdates.length" @fetchUpdates="fetchProfilUpdates" :data="data.profilUpdates"></fetch-profil-updates>
                </div>
            </div>
            <div class="row mb-3" >
                <div class="col-12">
                    <!-- AUSWEIS STATUS -->
                    <ausweis-status :data="data.zutrittsdatum"></ausweis-status>
                </div>
            </div>
            <div  class="row">
                <div class="col">
                    <!-- MAILVERTEILER -->
                    <mailverteiler  :data="data?.mailverteiler" :title="$p.t('profil','mailverteiler')"></mailverteiler>
                </div>
            </div>
        </div>
    </div>
</div>

            
    `,
};
