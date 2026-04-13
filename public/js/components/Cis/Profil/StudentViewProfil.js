import Mailverteiler from "./ProfilComponents/Mailverteiler.js";
import ProfilEmails from "./ProfilComponents/ProfilEmails.js";
import RoleInformation from "./ProfilComponents/RoleInformation.js";
import ProfilInformation from "./ProfilComponents/ProfilInformation.js";
import QuickLinks from "./ProfilComponents/QuickLinks.js";

export default {
	data() {
		return {
			quickLinks: [
				{
					icon: "fa-calendar-days",
					phrase: "lehre/stundenplan",
					action: () => {
						this.$router.push({
      					  	name: "OtherLvPlan",
      					  	params: { otherUid: this.$props.data.username },
      					})
					},
				}
			],
		};
	},
	components: {
		Mailverteiler,
		ProfilEmails,
		RoleInformation,
		ProfilInformation,
		QuickLinks,
	},

	props: ["data"],
	provide() {
		return {
			studiengang_kz: Vue.computed({ get: () => this.data.studiengang_kz }),
		}
	},
	
	methods: {},

	computed: {
		fotoStatus() {
			return this.data?.fotoStatus ?? null;
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

		personEmails() {
			return this.data?.emails ? this.data.emails : [];
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
	template: /*html*/ ` 

<div class="container-fluid text-break fhc-form"  >
    <!-- ROW --> 
    <div class="row">
        <!-- MAIN PANNEL -->
        <div class="col-sm-12 col-md-8 col-xxl-9 ">
            <!-- ROW WITH PROFIL IMAGE AND INFORMATION -->
            <!-- INFORMATION CONTENT START -->
            <!-- ROW WITH THE PROFIL INFORMATION --> 
            <div class="row mb-4">
                <!-- FIRST KAESTCHEN -->
                <div class="col-lg-12 col-xl-6 ">
                    <div class="row mb-4">
                        <div class="col">
                            <profil-information :data="profilInformation" :title="$p.t('profil','studentIn')" :fotoStatus="fotoStatus"></profil-information>
                        </div>
                    </div>
                    <!-- SECOND ROW OF FIRST COLUMN -->
					<div class="row mb-4">
                        <div class="col">
                            
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
                    <div class=" row mb-4">
                        <div  class=" col-lg-12">
                            <role-information :title="$p.t('profil','studentInformation')" :data="roleInformation"></role-information>
                        </div>
                    </div>
                    <!-- END OF SECOND ROW OF SECOND COLUMN IN MAIN CONTENT -->
                    <!-- END OF THE SECOND INFORMATION COLUMN -->
                </div>
                <!-- START OF THE SECOND PROFIL INFORMATION ROW --> 
                <!-- ROW WITH PROFIL IMAGE AND INFORMATION END -->
            </div  >
            <!-- END OF MAIN CONTENT COL -->
        </div>
        <!-- START OF SIDE PANEL -->
        <div  class="col-md-4 col-xxl-3 col-sm-12 text-break" >
            <!-- START OF THE FIRST ROW IN THE SIDE PANEL -->
            <div v-if="quickLinks.length" class="row mb-4">
				<div class="col">
					<quick-links :title="$p.t('profil/quickLinks')" :links="quickLinks" />
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
