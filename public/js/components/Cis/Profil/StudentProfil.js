import {CoreFilterCmpt} from "../../../components/filter/Filter.js";
import Mailverteiler from "./ProfilComponents/Mailverteiler.js";
import AusweisStatus from "./ProfilComponents/FhAusweisStatus.js";
import QuickLinks from "./ProfilComponents/QuickLinks.js";
import Adresse from "./ProfilComponents/Adresse.js";
import Kontakt from "./ProfilComponents/Kontakt.js";
import ProfilEmails from "./ProfilComponents/ProfilEmails.js";
import RoleInformation from "./ProfilComponents/RoleInformation.js";
import ProfilInformation from "./ProfilComponents/ProfilInformation.js";
import FetchProfilUpdates from "./ProfilComponents/FetchProfilUpdates.js";
import EditProfil from "./ProfilModal/EditProfil.js";

export default {
	components: {
		CoreFilterCmpt,
		Mailverteiler,
		AusweisStatus,
		QuickLinks,
		Adresse,
		Kontakt,
		ProfilEmails,
		RoleInformation,
		ProfilInformation,
		FetchProfilUpdates,
		EditProfil,
	},
	inject: ["sortProfilUpdates", "collapseFunction", "language"],
	data() {
		return {
			showModal: false,
			collapseIconBetriebsmittel: true,
			editDataFilter: null,

			// tabulator options
			zutrittsgruppen_table_options: {
				persistenceID: "filterTableStudentProfilZutrittsgruppen",
				persistence: {
					columns: false
				},
				height: 200,
				layout: "fitColumns",
				columns: [{
					title: Vue.computed(() => this.$p.t('profil/zutrittsGruppen')),
					field: "bezeichnung"
				}],
			},
			betriebsmittel_table_options: {
				persistenceID: "filterTableStudentProfilBetriebsmittel",
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
							"<i id='collapseIconBetriebsmittel' role='button' class='fa-solid fa-angle-down  '></i>",
						field: "collapse",
						headerSort: false,
						headerFilter: false,
						formatter: "responsiveCollapse",
						maxWidth: 40,
						headerClick: this.collapseFunction,
					},
					{
						title:  Vue.computed(() => this.$p.t('profil/entlehnteBetriebsmittel')),
						field: "betriebsmittel",
						headerFilter: true,
						minWidth: 200,
						visible: true
					},
					{
						title:  Vue.computed(() => this.$p.t('profil/inventarnummer')),
						field: "Nummer",
						headerFilter: true,
						resizable: true,
						minWidth: 200,
						visible: true
					},
					{
						title: Vue.computed(() => this.$p.t('profil/ausgabedatum')),
						field: "Ausgegeben_am",
						headerFilter: true,
						minWidth: 200,
						visible: true
					},
				],
			},
		};
	},

	props: {
		data: Object,
		editData: Object,
	},
	provide() {
		return {
			studiengang_kz: Vue.computed({ get: () => this.data.studiengang_kz }),
		}
	},
	methods: {

		betriebsmittelTableBuilt: function () {
			this.$refs.betriebsmittelTable.tabulator.setColumns(this.betriebsmittel_table_options.columns)
			this.$refs.betriebsmittelTable.tabulator.setData(this.data.mittel);
		},
		zutrittsgruppenTableBuilt: function () {
			this.$refs.zutrittsgruppenTable.tabulator.setColumns(this.zutrittsgruppen_table_options.columns)
			this.$refs.zutrittsgruppenTable.tabulator.setData(
				this.data.zuttritsgruppen
			);
		},
		fetchProfilUpdates: function () {
			this.$fhcApi.factory.profilUpdate.selectProfilRequest().then((res) => {
				if (!res.error && res) {
					this.data.profilUpdates = res.data?.length
						? res.data.sort(this.sortProfilUpdates)
						: null;
				}
			});
		},

		hideEditProfilModal: function () {
			//? checks the editModal component property result, if the user made a successful request or not
			if (this.$refs.editModal.result) {
				this.$fhcApi.factory.profilUpdate.selectProfilRequest()
					.then((request) => {
						if (!request.error && res) {
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
			// after a state change, wait for the DOM updates to complete
			Vue.nextTick(() => {
				this.$refs.editModal.show();
			});
		},
	},

	computed: {
		editable() {
			return this.data?.editAllowed ?? false;
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
					label: `${this.$p.t('person','personenkennzeichen')}`,
					value: this.data.personenkennzeichen
				},
				studiengang: {
					label: `${this.$p.t('lehre','studiengang')}`,
					value: this.data.studiengang
				},
				semester: {
					label: `${this.$p.t('lehre','semester')}`,
					value: this.data.semester
				},
				verband: {
					label: `${this.$p.t('lehre','lehrverband')}`,
					value: this.data.verband
				},
				gruppe: {
					label: `${this.$p.t('lehre','gruppe')}`,
					value: this.data.gruppe.trim()
				}
			};
		},
	},
	created() {
		//? sorts the profil Updates: pending -> accepted -> rejected
		this.data.profilUpdates?.sort(this.sortProfilUpdates);
	},
	watch: {
		'language.value'(newVal) {
			if(this.$refs.betriebsmittelTable) this.$refs.betriebsmittelTable.tabulator.setColumns(this.betriebsmittel_table_options.columns)
			if(this.$refs.zutrittsgruppenTable) this.$refs.zutrittsgruppenTable.tabulator.setColumns(this.zutrittsgruppen_table_options.columns)
		}
	},
	template: /*html*/ `
<div class="container-fluid text-break fhc-form">
    <edit-profil v-if="showModal" ref="editModal" @hideBsModal="hideEditProfilModal" 
    :value="JSON.parse(JSON.stringify(filteredEditData))" :title="$p.t('profil','profilBearbeiten')"></edit-profil>
    <!-- ROW --> 
    <div class="row">
        <!-- HIDDEN QUICK LINKS -->
        <div  class="d-md-none col-12 ">
            <!--TODO: uncomment when implemented
			<div class="row py-2">
                <div class="col">
                    <quick-links :title="$p.t('profil','quickLinks')" :mobile="true"></quick-links>
                </div>
            </div>-->
            
			<!-- Bearbeiten Button -->
			<div v-if="editable" class="row ">
				<div class="col mb-3">
					<button @click="showEditProfilModal" type="button" class="text-start  w-100 btn btn-outline-secondary" >
						<div class="row">
							<div class="col-2">
								<i class="fa fa-edit"></i>
							</div>
							<div class="col-10">{{$p.t('ui','bearbeiten')}}</div>
						</div>
					</button>
				</div>
			</div>
				<div v-if="data.profilUpdates" class="row mb-3">
					<div class="col">
						<!-- MOBILE PROFIL UPDATES -->  
						<fetch-profil-updates v-if="data.profilUpdates && data.profilUpdates.length" @fetchUpdates="fetchProfilUpdates"  :data="data.profilUpdates"></fetch-profil-updates>
					</div>
				</div>
			</div>
			<!-- END OF HIDDEN QUCK LINKS -->
			
			<!-- MAIN PANNEL -->
			<div class="col-sm-12 col-md-8 col-xxl-9 ">
				<!-- ROW WITH PROFIL IMAGE AND INFORMATION -->
				<!-- INFORMATION CONTENT START -->
				<!-- ROW WITH THE PROFIL INFORMATION --> 
				<div class="row mb-4 ">
					<div  class="col-lg-12 col-xl-6 ">
						<div class="row mb-4">
							<div class="col">
								<!-- PROFIL INFORMATION -->
								<profil-information @showEditProfilModal="showEditProfilModal" :title="$p.t('profil','studentIn')" :data="profilInformation" :editable="editable"></profil-information>
							</div>
						</div>
						<div class="row mb-4">
							<div  class=" col-lg-12">
								<!-- STUDENT INFO -->
								<role-information :title="$p.t('profil','studentInformation')" :data="roleInformation"></role-information>
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
									<div class="gy-3 row">
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
			<!-- SECOND ROW UNDER THE PROFIL IMAGE AND INFORMATION WITH THE TABLES -->
			<div class="row">
				<div class="col-12 mb-4" >
					<core-filter-cmpt 
					@tableBuilt="betriebsmittelTableBuilt" 
					:title="$p.t('profil','entlehnteBetriebsmittel')"  
					ref="betriebsmittelTable" 
					:tabulator-options="betriebsmittel_table_options" 
					tableOnly 
					:sideMenu="false" />
				</div> 
				<div class="col-12 mb-4" >
					<core-filter-cmpt 
					@tableBuilt="zutrittsgruppenTableBuilt" 
					:title="$p.t('profil','zutrittsGruppen')" 
					ref="zutrittsgruppenTable" 
					:tabulator-options="zutrittsgruppen_table_options"  
					tableOnly 
					:sideMenu="false" 
					noColumnFilter />
				</div>
			</div>
		<!-- END OF MAIN CONTENT COL -->
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
			<div class="row d-none d-md-block">
				<div class="col mb-3">
					<button @click="()=>showEditProfilModal()" type="button" class="text-start  w-100 btn btn-outline-secondary" >
						<div class="row">
							<div class="col-2">
								<i class="fa fa-edit"></i>
							</div>
							<div class="col-10">{{$p.t('ui','bearbeiten')}}</div>
						</div>
					</button>
				</div>
			</div>
			<div v-if="data.profilUpdates" class="row d-none d-md-block mb-3">
				<div class="col mb-3">
					<!-- PROFIL UPDATES -->
					<fetch-profil-updates v-if="data.profilUpdates && data.profilUpdates.length" @fetchUpdates="fetchProfilUpdates"  :data="data.profilUpdates"></fetch-profil-updates>
				</div>
			</div>
			<div class="row mb-3" >
				<div class="col-12">
					<ausweis-status :data="data.zutrittsdatum"></ausweis-status>
				</div>
			</div>
			<!-- START OF THE SECOND ROW IN THE SIDE PANEL -->
			<div  class="row">
				<div class="col">
					<!-- HIER SIND DIE MAILVERTEILER -->
					<mailverteiler :title="$p.t('profil','mailverteiler')" :data="data?.mailverteiler"></mailverteiler>
				</div>
            <!-- END OF THE SECOND ROW IN THE SIDE PANEL -->
            </div>
        <!-- END OF SIDE PANEL -->
        </div>
    <!-- END OF CONTAINER ROW-->
    </div> 
<!-- END OF CONTAINER -->
</div>
`,
};
