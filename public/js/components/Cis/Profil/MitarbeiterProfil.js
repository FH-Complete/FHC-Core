
import { CoreFilterCmpt } from "../../../components/filter/Filter.js";
import EditProfil from "./EditProfil.js";
import {Adresse, Kontakt, FetchProfilUpdates} from "./ProfilComponents.js";


export default {
  components: {
    CoreFilterCmpt,
    EditProfil,
    Adresse,
    Kontakt,
    FetchProfilUpdates,
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
  
    sperre_foto_function() {
      if (!this.data) {
        return;
      }
      Vue.$fhcapi.UserData.sperre_foto_function(!this.data.foto_sperre).then((res) => {
        this.data.foto_sperre = res.data.foto_sperre;
      });
    },
    
  },

  computed: {

    //? legacy mailto link to create an email with information that should be changed
    refreshMailTo() {
      return `mailto:info.mio@technikum-wien.at?subject=Datenkorrektur&body=Die%20Profildaten%20für%20User%20'${this.data.username}'%20sind%20nicht%20korrekt.%0DHier, die richtigen Daten:%0A%0ANachname:%20${this.data.nachname}%0AVorname:%20${this.data.vorname}%0AGeburtsdatum:${this.data.gebdatum}%0AGeburtsort:%20${this.data.gebort}%0ATitelPre:${this.data.titel}%20%0ATitelPost:${this.data.postnomen}%20%0A%0A***%0DPlatz für weitere (nicht angeführte Daten)%0D***%0A%0A[Bitte%20übermitteln%20Sie%20uns%20etwaige%20Dokumente%20zum%20Beleg%20der%20Änderung]`;
    },

    get_image_base64_src() {
      if (!this.data) {
        return "";
      }
      return "data:image/jpeg;base64," + this.data.foto;
    },

    get_mitarbeiter_standort_telefon(){
      if(this.data.standort_telefon){
        return "tel:"+ this.data.telefonklappe + this.data.standort_telefon;
      }else{
        return null;
      }
    },
    //? this computed function returns all the informations for the first column in the profil
    personData() {
      if (!this.data) {
        return {};
      }

      return {
        Username: this.data.username,
        Anrede: this.data.anrede,
        Titel: this.data.titel,
        Postnomen: this.data.postnomen,
      };
    },

    personKontakt() {
      if (!this.data) {
        return {};
      }

      return {
        emails: this.data.emails,
        
      };
    },

    specialData() {
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
    

    kontaktInfo() {
      if (!this.data) {
        return {};
      }

      return {
        FhAusweisStatus: this.data.zutrittsdatum,
        emails: this.data.emails,
        Kontakte: this.data.kontakte,
        Adressen: this.data.adressen,
      };
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
                <p class="m-0">
                <div class="card">
                
                <a class=" w-100 btn " data-bs-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
                <u> Quick links</u>
               </a>
             
                
               </div>
                 
                </p>
                <div class="mt-1 collapse" id="collapseExample">
                  
                  <div class="list-group">
                   
                    <a href="#" class="list-group-item list-group-item-action">Zeitwünsche</a>
                    <a href="#" class="list-group-item list-group-item-action">Lehrveranstaltungen</a>
                    <a href="#" class="list-group-item list-group-item-action ">Zeitsperren</a>
                  </div>
                </div>
              </div>
            </div>

            <!-- HERE STARTS THE ROW WITH REQUESTED CHANGES FROM THE USER -->
            <div class="row mb-3">
            <div class="col">
              <div class="card">
              <div class="card-header">
              Profil Informations Änderungen Anfragen</div>
              <div class="card-body">
              <fetch-profil-updates @fetchUpdates="fetchProfilUpdates" :data="data.profilUpdates"></fetch-profil-updates>
                    
              </div>
              </div>
             
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
                     
                        
                      <div class="card h-100">
                      <div class="card-header">
                      MitarbeiterIn
                      </div>
                      <div class="card-body">
                      
                       


                  
                  <div  class="gy-3 row justify-content-center align-items-center">




                  <!-- SQUEEZING THE IMAGE INSIDE THE FIRST INFORMATION COLUMN -->
 <!-- START OF THE FIRST ROW WITH THE PROFIL IMAGE -->
          <div class="col-12 col-sm-6 mb-2">
           <div class="row justify-content-center">
                        <div class="col-auto " style="position:relative">
                          <img class=" img-thumbnail " style=" max-height:150px; "  :src="get_image_base64_src"></img>
                          <!-- LOCKING IMAGE FUNCTIONALITY -->
                          
                          
                          <div role="button" @click.prevent="sperre_foto_function" class="image-lock" >
                          <i :class="{'fa':true, ...(data.foto_sperre?{'fa-lock':true}:{'fa-lock-open':true})} " ></i>
                          </div>


                        </div>
                      </div>
                    <!-- END OF THE ROW WITH THE IMAGE -->
                    </div>
<!-- END OF SQUEEZE -->



<!-- COLUMNS WITH MULTIPLE ROWS NEXT TO PROFIL PICTURE -->
                  <div class="col-12 col-sm-6">
                  <div class="row gy-4">
                  <div class="col-12">
                  
                        
                  <div class="form-underline ">
                  <div class="form-underline-titel">Vorname</div>
                  <span class="form-underline-content">{{data.vorname}} </span>
                  </div>


                
                </div>
                <div class="col-12">
              
                <div class="form-underline ">
                <div class="form-underline-titel">Nachname</div>
                <span class="form-underline-content">{{data.nachname}} </span>
                </div>

                </div>
                </div>
             
                
                  </div>
                  







                  <div v-for="(wert,bez) in personData" class="col-md-6 col-sm-12 ">
                  
                  
                  <div class="form-underline ">
                  <div class="form-underline-titel">{{bez}}</div>
                  <span class="form-underline-content">{{wert?wert:'-'}} </span>
                  </div>
                


                  </div>

                  
                      </div>


                      
                      </div>
                    </div>
		    </div>
                    </div>
                     <div class="row mb-4">
                     

                     <div  class=" col-lg-12">
        
                     <div class="card">
                 
                         <div class="card-header">
                         Mitarbeiter Information
                         </div>
                         <div class="card-body">
                             <div class="gy-3 row">
                             <div v-for="(wert,bez) in specialData" class="col-md-6 col-sm-12 ">
                             
                            
                             
                             
                               
                                <div class="form-underline">
                                <div class="form-underline-titel">{{bez }}</div>

                                <!-- print Telefon link -->
                                <a :href="get_mitarbeiter_standort_telefon" v-if="bez=='Telefon'" :href="get_mitarbeiter_standort_telefon" class="form-underline-content">{{wert?wert:'-'}}</a>
                                
                                <!-- else print information -->
                                <span v-else class="form-underline-content">{{wert?wert:'-'}}</span>
                                </div>
                                

                         
                             </div>


                             <!-- Bearbeiten Button -->
                             <div class="col-md-6 col-sm-12 ">
                              <button @click="showModal" type="button" class="text-start  w-100 btn btn-outline-primary" >
                                <div class="row">
                                  <div class="col-2">
                                    <i class="fa fa-edit"></i>
                                  </div>
                                  <div class="col-10">Bearbeiten</div>
                                </div>
                              </button>

                             <!-- simml -->

                           
                              
                            

                             </div>
                             </div>
                         
                     </div>
                     
                 </div>

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


                    <div class="card ">
                    <div class="card-header">
                    Mails
                    </div>
                   
                    <div class="card-body">



                   

                      <div v-for="(wert,bezeichnung) in personKontakt">

                      
            
                      <!-- HIER SIND DIE EMAILS -->
                  

                  
                      <div  v-if="typeof wert === 'object' && bezeichnung == 'emails'" class="gy-3 row justify-content-center ">
                      <div v-for="email in wert" class="col-12 ">
                     
                            <div class="row align-items-center">
                            
                      <div class="col-1 text-center">

                      <i class="fa-solid fa-envelope" style="color:rgb(0, 100, 156)"></i>

                      </div>

                      
                      <div class="col-11">

                      <div class="form-underline">
                      <div class="form-underline-titel">{{email.type}}</div>
                      <a :href="'mailto:'+email.email" class="form-underline-content">{{email.email}} </a>
                      </div>
                      
                      
                      </div>
                      </div>
                      </div>
                          </div>

                    
                    
                     




                    
                      </div>


                    </div>
                    </div>
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


              <!-- SRART OF QUICK LINKS IN THE SIDE PANEL -->


              <!-- START OF THE FIRDT ROW IN THE SIDE PANEL -->
              <!-- THESE QUCK LINKS ARE ONLY VISIBLE UNTIL VIEWPORT MD -->
              <div v-if="data.profilUpdates" class="row d-none d-md-block mb-3">
              <div class="col mb-3">
                    <div  class="card">
                      <div class="card-header">
                      Profil Updates
                      </div>
                      <div class="card-body">
                      <fetch-profil-updates @fetchUpdates="fetchProfilUpdates" :data="data.profilUpdates"></fetch-profil-updates>
                      </div>
                      </div>

                   
                      
                  
                  </div>    
                  </div>
              <div  class="row d-none d-md-block mb-3">
                
                <div class="col">
                 
                    <div class="card">
                      <div class="card-header">
                      Quick Links
                      </div>
                      <div class="card-body">
                      
                       
                        <a style="text-decoration:none" class="my-1 d-block" href="#">Zeitwuensche</a>
                        <a style="text-decoration:none" class="my-1 d-block" href="#">Lehrveranstaltungen</a>
                        <a style="text-decoration:none" class="my-1 d-block" href="#">Zeitsperren</a>

                      </div>
                    </div>

                   
                      
                  
                  </div>
                </div>

                <div class="row mb-3" >
                
                
                <div class="col-12">
             
             
                
                  <div class="card">
                    <div class="card-body">
                      <span>Der FH Ausweis ist am <b>{{data.zutrittsdatum}}</b> ausgegeben worden.</span>
                    </div>
                
                </div>
      
         
                </div>
                
                </div>


                <!-- START OF THE SECOND ROW IN THE SIDE PANEL -->
                <div  class="row">
                
                  <div class="col">
                

                  
                  <!-- HIER SIND DIE MAILVERTEILER -->
                    <div class="card">
                      <div class="card-header">
                      Mailverteilers
                      </div>
                      <div class="card-body">
                      
                        <h6 class="card-title">Sie sind Mitgglied in folgenden Verteilern:</h6>
                        <div  class="card-text row text-break mb-2" v-for="verteiler in data?.mailverteiler">
                          <div class="col-12 ">
                            <div class="row">  
                              <div class="col-1 ">
                              
                              <i class="fa-solid fa-envelope" style="color: #00649C;"></i>
                              
                              </div>
                              <div class="col">
                                <a :href="verteiler.mailto"><b>{{verteiler.gruppe_kurzbz}}</b></a>
                              </div>
                            </div>
                           
                          </div> 
                          <div class="col-11 offset-1 ">{{verteiler.beschreibung}}</div>
                        </div>

                      </div>
                    </div>





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
