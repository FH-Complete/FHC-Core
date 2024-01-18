
import { CoreFilterCmpt } from "../../../components/filter/Filter.js";
import EditProfil from "./EditProfil.js";
import {Adresse, Kontakt, FetchProfilUpdates} from "./ProfilComponents.js";
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
            headerClick: this.$parent.collapseFunction,
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
            headerClick: this.$parent.collapseFunction,
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

  //? this is the prop passed to the dynamic component with the custom data of the view
  
  props: {
		data: Object,
	},
  
  methods: {

    fetchProfilUpdates: function(){
      Vue.$fhcapi.UserData.selectProfilRequest().then((res)=>{
        
        if(!res.error){
          this.data.profilUpdates = res.data.retval?.length ? res.data.retval : null ; 
        }
      });
    },
  
    showModal() {

      EditProfil.popup({ 
          value:JSON.parse(JSON.stringify(this.data.editData)),
          timestamp:this.data.editDataTimestamp
        }).then((popup_result) => {
          if(popup_result){
            Vue.$fhcapi.UserData.selectProfilRequest()
            .then((res) =>{
              if(!res.error){
                this.data.profilUpdates = res.data.retval;
              }else{
                alert("Error when fetching profile updates: " +res.data.retval);
              }
            })
            .catch(err=>alert(err));
          }
          
        }).catch((e) => {
          console.log(e);
         
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

    personEmails() {
      return this.data?.emails ? this.data.emails : [];
    },

    privateKontakte() {
      if (!this.data) {
        return {};
      }

      return this.data.kontakte;
      
    },

    privateAdressen() {
      if (!this.data) {
        return {};
      }

      return this.data.adressen;
      
    },
    

  },
 
  created() {
    
    
    
    
    

      this.data.editData = {
        view:null,
        data:{
        Personen_Informationen : {
          title:"Personen Informationen",
          view:null,
          data:{
            username:{
              title:"username",
              view:"text_input",
              data:{
                titel:"username",
                value:this.data.username,
              }
            },
            vorname: {
              title:"vorname",
              view:"text_input",
              data:{
                titel:"vorname",
                value:this.data.vorname,
              }},
              nachname: {
                title:"nachname",
                view:"text_input",
                data:{
                  titel:"nachname",
                  value:this.data.nachname,
                }
              }
            }
          },
          Private_Kontakte: {
            title:"Private Kontakte" ,
            data:this.privateKontakte.map(kontakt => {
              return {
                listview:'Kontakt',
                view:'EditKontakt',
                data:kontakt
              }})
           },
          Private_Adressen: {
            title: "Private Adressen",
            data:this.privateAdressen.map(kontakt => {
              return {
                listview:'Adresse',
                view:'EditAdresse',
                data:kontakt
              }})
           },
          },
       
      };


      console.log(JSON.stringify(this.data.editData,null,2));

  
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
    <!-- ROW --> 
          <div class="row">
          <!-- HIDDEN QUICK LINKS -->
              <div  class="d-md-none col-12 ">
             
              <div class="row mb-3">
              <div class="col">
               
                <quick-links :mobile="true"></quick-links>
              </div>
            </div>

            <!-- HERE STARTS THE ROW WITH REQUESTED CHANGES FROM THE USER -->
            <div class="row mb-3">
            <div class="col">
              
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

















<!-- FIRST KAESTCHEN -->
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
                            



<!-- END OF THE FIRST KAESTCHEN -->
                      </div>


                      <!-- START OF SECOND PROFIL  INFORMATION COLUMN -->
                     


                     


              
                    <!-- END OF PROFIL INFORMATION ROW -->
                    <!-- INFORMATION CONTENT END -->
                    </div>

                    <div  class="col-xl-6 col-lg-12 ">
                    <div class="row mb-4">
                    <div class="col">

                    
                    <!-- EMAILS -->
                    <profil-emails :data="personEmails" ></profil-emails>

                   
                    </div></div>

                    <!-- HIER SIND DIE PRIVATEN KONTAKTE-->
                    <div class="row mb-4 ">
                      <div class="col">
                        <div class="card">
                          <div class="card-header">
                            Private Kontakte
                          </div>
                          <div class="card-body ">
                            
                            <div  class="gy-3  row ">
                            <div v-for="element in privateKontakte" class="col-12">
                            
                            <Kontakt :data="element"></Kontakt>
                            
                            </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <!-- -->

                    <!-- HIER SIND DIE PRIVATEN ADRESSEN-->
                    <div class="row mb-4">
                      <div class="col">
                        <div class="card">
                          <div class="card-header">Private Adressen</div>
                            <div class="card-body">
                            
                              <div class="gy-3 row ">
                                <div v-for="element in privateAdressen" class="col-12">
                                <Adresse :data="element"></Adresse>
                                 
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    
                    <!--   -->



                    <!-- END OF THE SECOND INFORMATION COLUMN -->
                    </div>


                    <!-- START OF THE SECOND PROFIL INFORMATION ROW --> 
                  
                    
                  <!-- ROW WITH PROFIL IMAGE AND INFORMATION END -->
                </div  >



                




                <!-- SECOND ROW UNDER THE PROFIL IMAGE AND INFORMATION WITH THE TABLES -->
                <div class="row">

                <!-- FIRST TABLE -->
                  <div class="col-12 mb-4" >
                 
                    <core-filter-cmpt title="Funktionen"  ref="funktionenTable" :tabulator-options="funktionen_table_options"  tableOnly :sideMenu="false" />
                  
                    </div>

                <!-- SECOND TABLE -->
                  <div class="col-12 mb-4" >
                    <core-filter-cmpt title="Entlehnte Betriebsmittel"  ref="betriebsmittelTable" :tabulator-options="betriebsmittel_table_options" tableOnly :sideMenu="false" />
                  </div>

                <!-- END OF THE ROW WITH THE TABLES UNDER THE PROFIL INFORMATION -->
                </div>







              <!-- END OF MAIN CONTENT COL -->
              </div>




              <!-- START OF SIDE PANEL -->
              <div  class="col-md-4 col-xxl-3 col-sm-12 text-break" >
              
              <div v-if="data.profilUpdates" class="row d-none d-md-block mb-3">
              <div class="col mb-3">


                      <!-- PROFIL UPDATES -->
                      <fetch-profil-updates @fetchUpdates="fetchProfilUpdates" :data="data.profilUpdates"></fetch-profil-updates>
                  
                  </div>    
                  </div>
              <div  class="row d-none d-md-block mb-3">
                
                <div class="col">
                 
                    <!-- QUICK LINKS --> 
                    <quick-links></quick-links>
                   
                      
                  
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
