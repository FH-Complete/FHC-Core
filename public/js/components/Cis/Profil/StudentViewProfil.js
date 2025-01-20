import QuickLinks from "./ProfilComponents/QuickLinks.js";
import Mailverteiler from "./ProfilComponents/Mailverteiler.js";
import ProfilEmails from "./ProfilComponents/ProfilEmails.js";
import RoleInformation from "./ProfilComponents/RoleInformation.js";
import ProfilInformation from "./ProfilComponents/ProfilInformation.js";

export default {
  data() {
    return {};
  },
  components: {
    QuickLinks,
    Mailverteiler,
    ProfilEmails,
    RoleInformation,
    ProfilInformation,
  },

  props: ["data"],
  methods: {},

  computed: {
    editable() {
      return this.data?.editAllowed ?? false;
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
        Geburtsdatum: this.data.gebdatum,
        Geburtsort: this.data.gebort,
        Personenkennzeichen: this.data.personenkennzeichen,
        Studiengang: this.data.studiengang,
        Semester: this.data.semester,
        Verband: this.data.verband,
        Gruppe: this.data.gruppe.trim(),
      };
    },
  },

  mounted() {},

  template: /*html*/ ` 

  <div class="container-fluid text-break fhc-form"  >
    <!-- ROW --> 
          <div class="row">
          <!-- HIDDEN QUICK LINKS -->
              <div  class="d-md-none col-12 ">
             
             <quick-links :title="$p.t('profil','quickLinks')" :mobile="true"></quick-links>

              </div>
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
                     
                        <profil-information :data="profilInformation" :title="$p.t('profil','studentIn')" :editable="editable"></profil-information>
                     
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
                  
                    </div></div>

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


              <!-- SRART OF QUICK LINKS IN THE SIDE PANEL -->


              <!-- START OF THE FIRDT ROW IN THE SIDE PANEL -->
              <!-- THESE QUCK LINKS ARE ONLY VISIBLE UNTIL VIEWPORT MD -->
                <div  class="row d-none d-md-block mb-3">
                  <div class="col">
                 
                   <quick-links :title="$p.t('profil','quickLinks')"></quick-links>

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
