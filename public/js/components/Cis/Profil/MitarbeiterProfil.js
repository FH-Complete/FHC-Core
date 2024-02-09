
import { CoreFilterCmpt } from "../../../components/filter/Filter.js";
import EditProfil from "./EditProfil.js";
import Adresse from "./ProfilComponents/Adresse.js";
import Kontakt from "./ProfilComponents/Kontakt.js";
import FetchProfilUpdates from "./ProfilComponents/FetchProfilUpdates.js";
import Mailverteiler from "./ProfilComponents/Mailverteiler.js"; 
import AusweisStatus from "./ProfilComponents/FhAusweisStatus.js";
import QuickLinks from "./ProfilComponents/QuickLinks.js";
import ProfilEmails from "./ProfilComponents/ProfilEmails.js"
import RoleInformation from "./ProfilComponents/RoleInformation.js";
import ProfilInformation from "./ProfilComponents/ProfilInformation.js";


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
  inject: ['sortProfilUpdates','collapseFunction'],
  data() {
    return {

     
      
      funktionen_table_options: {
        height: 300,
        layout: "fitColumns",
        responsiveLayout: "collapse",
        responsiveLayoutCollapseUseFormatters: false,
        responsiveLayoutCollapseFormatter: Vue.$collapseFormatter,
        data: [
          {
            Bezeichnung: "",
            Organisationseinheit: "",
            Gültig_von: "",
            Gültig_bis: "",
            Wochenstunden: "",
          },
        ],
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
          },
          {
            title: "Bezeichnung",
            field: "Bezeichnung",
            headerFilter: true,
            minWidth: 200,
          },
          {
            title: "Organisationseinheit",
            field: "Organisationseinheit",
            headerFilter: true,
            minWidth: 200,
          },
          {
            title: "Gültig_von",
            field: "Gültig_von",
            headerFilter: true,
            resizable: true,
            minWidth: 200,
          },
          {
            title: "Gültig_bis",
            field: "Gültig_bis",
            headerFilter: true,
            resizable: true,
            minWidth: 200,
          },
          {
            title: "Wochenstunden",
            field: "Wochenstunden",
            headerFilter: true,
            minWidth: 200,
          },
        ],
      },
      betriebsmittel_table_options: {
        height: 300,
        layout: "fitColumns",
        responsiveLayout: "collapse",
        responsiveLayoutCollapseUseFormatters: false,
        responsiveLayoutCollapseFormatter: Vue.$collapseFormatter,
        data: [{ betriebsmittel: "", Nummer: "", Ausgegeben_am: "" }],
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
            title: "Betriebsmittel",
            field: "betriebsmittel",
            headerFilter: true,
            minWidth: 200,
          },
          {
            title: "Nummer",
            field: "Nummer",
            headerFilter: true,
            resizable: true,
            minWidth: 200,
          },
          {
            title: "Ausgegeben_am",
            field: "Ausgegeben_am",
            headerFilter: true,
            minWidth: 200,
          },
        ],
      },
    };
  },

  props: {
		data: Object,
    editData: Object,
	},
  
  methods: {

   

    

    fetchProfilUpdates: function(){
      Vue.$fhcapi.ProfilUpdate.selectProfilRequest().then((res)=>{
        
        if(!res.error){
          this.data.profilUpdates = res.data.retval?.length ? res.data.retval.sort(this.sortProfilUpdates) : null ; 
        }
      });
    },
  
    showModal() {

      EditProfil.popup({ 
          value:JSON.parse(JSON.stringify(this.editData)),
          title:"Profil bearbeiten",
        }).then((popup_result) => {
          if(popup_result){
            Vue.$fhcapi.ProfilUpdate.selectProfilRequest()
            .then((res) =>{
              if(!res.error){
                this.data.profilUpdates = res.data.retval;
                this.data.profilUpdates.sort(this.sortProfilUpdates);
                
              }else{
                alert("Error when fetching profile updates: " +res.data.retval);
              }
            })
            .catch(err=>alert(err));
          }
        }).catch((e) => {
          // Wenn der User das Modal abbricht ohne Änderungen
         
        });
    },
  
    
    
  },

  computed: {

    

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
        foto_sperre:this.data.foto_sperre,
        foto:this.data.foto,

      };
    },

    roleInformation() {
      if (!this.data) {
        return {};
      }

      return {
        Geburtsdatum: this.data.gebdatum,
        Geburtsort: this.data.gebort,
        Kurzzeichen: this.data.kurzbz,
        Telefon:  (this.data.standort_telefon?this.data.standort_telefon:"") + " " + this.data.telefonklappe,
        Büro: this.data.ort_kurzbz,
      };
    },


  },
 
  created() {
    //? sorts the profil Updates: pending -> accepted -> rejected
    this.data.profilUpdates?.sort(this.sortProfilUpdates);
  },
  mounted() {
    
    this.$refs.betriebsmittelTable.tabulator.on("tableBuilt", () => {
      this.$refs.betriebsmittelTable.tabulator.setData(this.data.mittel);
    });

    this.$refs.funktionenTable.tabulator.on("tableBuilt", () => {
      this.$refs.funktionenTable.tabulator.setData(this.data.funktionen);
    });

  },

  template: ` 

  <div class="container-fluid text-break fhc-form"  >
    
          <div class="row">
          
              <div  class="d-md-none col-12 ">
             
              <div class="row mb-3">
                <div class="col">
                <!-- MOBILE QUICK LINKS -->     
                  <quick-links :mobile="true"></quick-links>
                </div>
              </div>

              <!-- Bearbeiten Button -->

              <div class="row mb-3 ">
              <div class="col">
              <button @click="showModal" type="button" class="text-start  w-100 btn btn-outline-secondary" >
                <div class="row">
                  <div class="col-auto">
                    <i class="fa fa-edit"></i>
                  </div>
                  <div class="col-auto">Bearbeiten</div>
                </div>
              </button>
              </div>
              </div>
            
              <div v-if="data.profilUpdates" class="row mb-3">
                <div class="col">
                  <!-- MOBILE PROFIL UPDATES -->  
                  <fetch-profil-updates @fetchUpdates="fetchProfilUpdates" :data="data.profilUpdates"></fetch-profil-updates>
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
                     <profil-information title="MitarbeiterIn" :data="profilInformation"></profil-information>


		                 </div>
                    </div>
                     <div class="row mb-4">
                     

                     <div  class=" col-lg-12">
        
                    <!-- MITARBEITER INFO -->
                    <role-information title="Mitarbeiter Information" :data="roleInformation"></role-information>


                     </div> 
                      </div>


                      <!-- START OF SECOND PROFIL  INFORMATION COLUMN -->
                     
                    
                    </div>

                    <div  class="col-xl-6 col-lg-12 ">
                    <div class="row mb-4">
                    <div class="col">
                    <!-- EMAILS -->
                    <profil-emails :data="data.emails" ></profil-emails>
                    </div>
                    </div>

                    
                    <div class="row mb-4 ">
                      <div class="col">
                        
                      <!-- PRIVATE KONTAKTE-->
                      
                      <div class="card">
                          <div class="card-header">
                            Private Kontakte
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
                          <div class="card-header">Private Adressen</div>
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
                 
                    <core-filter-cmpt title="Funktionen"  ref="funktionenTable" :tabulator-options="funktionen_table_options"  tableOnly :sideMenu="false" />
                  
                    </div>

                  <div class="col-12 mb-4" >

                  <!-- BETRIEBSMITTEL TABELLE -->
                    <core-filter-cmpt title="Entlehnte Betriebsmittel"  ref="betriebsmittelTable" :tabulator-options="betriebsmittel_table_options" tableOnly :sideMenu="false" />
                  </div>

                </div>

              </div>

              <!-- START OF SIDE PANEL -->
              <div  class="col-md-4 col-xxl-3 col-sm-12 text-break" >
              
            

             
              <div  class="row d-none d-md-block mb-3">
                
                <div class="col">
                 
                    <!-- QUICK LINKS --> 
                    <quick-links></quick-links>
                   
                      
                  
                  </div>
                </div>

                <!-- Bearbeiten Button -->

                <div class="row d-none d-md-block ">
                <div class="col mb-3">
                <button @click="showModal" type="button" class="text-start  w-100 btn btn-outline-secondary" >
                  <div class="row">
                    <div class="col-auto">
                      <i class="fa fa-edit"></i>
                    </div>
                    <div class="col-auto">Bearbeiten</div>
                  </div>
                </button>
                </div>
                </div>

                <div v-if="data.profilUpdates" class="row d-none d-md-block mb-3">
                <div class="col mb-3">
  
  
                    <!-- PROFIL UPDATES -->
                    <fetch-profil-updates @fetchUpdates="fetchProfilUpdates" :data="data.profilUpdates"></fetch-profil-updates>
                
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
                    <mailverteiler  :data="data?.mailverteiler"></mailverteiler>
                    
                  </div>
                </div>
              </div>          
      </div>
  </div>
            
    `,
};
