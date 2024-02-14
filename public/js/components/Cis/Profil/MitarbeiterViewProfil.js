
import { CoreFilterCmpt } from "../../../components/filter/Filter.js";
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
  inject: ['collapseFunction'],
  data() {
    return {
      collapseIconFunktionen: true,
     
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
     
    };
  },

  //? this is the prop passed to the dynamic component with the custom data of the view
  props: ["data"],
  methods: {
   
  },

  computed: {

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

  mounted() {
  

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
             
              <quick-links :mobile="true" ></quick-links>

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
                     
                        <!-- Profil Informationen -->
                        <profil-information title="MitarbeiterIn" :data="profilInformation"></profil-information>
                     
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
                    <profil-emails :data="personEmails"></profil-emails>
                   
                    </div></div>


                    <!-- SECOND ROW OF SECOND COLUMN IN MAIN CONTENT -->
                    <div class="row mb-4">
                     

                    <div  class=" col-lg-12">
       
                    <!-- roleInformation -->
                    <role-information :data="roleInformation" title="Mitarbeiter Information"></role-information>

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
                    <core-filter-cmpt title="Funktionen"  ref="funktionenTable" :tabulator-options="funktionen_table_options"  tableOnly :sideMenu="false" />
                  </div>

               

                <!-- END OF THE ROW WITH THE TABLES UNDER THE PROFIL INFORMATION -->
                </div>







              <!-- END OF MAIN CONTENT COL -->
              </div>




              <!-- START OF SIDE PANEL -->
              <div  class="col-md-4 col-xxl-3 col-sm-12 text-break" >

              <!-- VISIBLE UNTIL VIEWPORT MD -->
                <div  class="row d-none d-md-block mb-3">
                  <div class="col">
                 
                   <!-- QUICKLINKS -->
                   <quick-links ></quick-links>

                  </div>
                </div>

                <div  class="row">
                
                  <div class="col">

                  <!-- MAILVERTEILER -->

                  <mailverteiler :data="data?.mailverteiler"></mailverteiler>

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
