import fhcapifactory from "../../../apps/api/fhcapifactory.js";
import { CoreFilterCmpt } from "../../../components/filter/Filter.js";
import BsModal from "../../Bootstrap/Modal.js";


export default {
  components: {
    CoreFilterCmpt,
    BsModal,
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
          //? option when wanting to hide the collapsed list

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
		
		/*
		 * NOTE(chris): 
		 * Hack to expose in "emits" declared events to $props which we use 
		 * in the v-bind directive to forward all events.
		 * @see: https://github.com/vuejs/core/issues/3432
		
		onHideBsModal: Function,
		onHiddenBsModal: Function,
		onHidePreventedBsModal: Function,
		onShowBsModal: Function,
		onShownBsModal: Function
    */
	},
  
  methods: {
    showModal() {
      this.$refs.bsmodal.show()
    },

    hideModal() {
      // You can call the hide method of the modal component if needed
      this.$refs.bsmodal.hide();
    },


    submitProfilChange(){
      if(this.isEditDataChanged){
        //? inserts new row in public.tbl_cis_profil_update 
        Vue.$fhcapi.UserData.editProfil(this.data.editData);
     
      }
    },
    sperre_foto_function() {
      if (!this.data) {
        return;
      }
      fhcapifactory.UserData.sperre_foto_function(!this.data.foto_sperre).then((res) => {
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

    
    isEditDataChanged: function(){
      return JSON.stringify(this.data.editData) != this.originalEditData;
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
    
    if(this.data.editData){
      this.originalEditData = JSON.stringify(this.data.editData);
    }else{
      //? storing an original version of the editData to check if the editData was changed by the user and is not in the original state
      this.originalEditData = JSON.stringify(
        {
          Personen_Informationen : {...this.personData, vorname: this.data.vorname, nachname: this.data.nachname},
          Mitarbeiter_Informatinen: this.specialData,
          Emails:this.data.emails,
          Private_Kontakte: this.data.kontakte,
          Private_Adressen:this.privateAdressen,
        });

      this.data.editData = JSON.parse(this.originalEditData);

    }
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

  <div  class="row"><div class="col"><pre>{{JSON.stringify(data.editData,null,2)}}</pre></div><div class="col"><pre>{{JSON.stringify(data.emails,null,2)}}</pre></div></div>

  <div class="container-fluid text-break fhc-form"  >
    <!-- ROW --> 
          <div class="row">
          <!-- HIDDEN QUICK LINKS -->
              <div  class="d-md-none col-12 ">
             
              <div class="row py-2">
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
                    <a href="#" class="list-group-item list-group-item-action ">Zeitsperren von Gschnell</a>
                  </div>
                </div>
              </div>
            </div>

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
                              <button @click="showModal" type="button" class="text-start w-100 btn btn-outline-primary" >
                                <div class="row">
                                  <div class="col-2">
                                    <i class="fa fa-edit"></i>
                                  </div>
                                  <div class="col-10">Bearbeiten</div>
                                </div>
                              </button>

                             <bs-modal ref="bsmodal" backdrop="false" >
                                <template v-slot:title>
                                  {{"Profil bearbeiten" }}
                                </template>
                                <template v-slot:default>
                                
                                <!-- START OF THE ACCORDION -->

                               


                                <div class="accordion accordion-flush" id="accordionFlushExample" >
                                  <div class="accordion-item" v-for="(value,key) in data.editData ">
                                    <h2 class="accordion-header" :id="'flush-headingOne'+key">
                                      <button style="font-weight:500" class="accordion-button collapsed" type="button" data-bs-toggle="collapse" :data-bs-target="'#flush-collapseOne'+key" aria-expanded="false" :aria-controls="'flush-collapseOne'+key">
                                        {{key.replace("_"," ")}}
                                      </button>
                                    </h2>
                                    <!-- SHOWING ALL MAILS IN THE FIRST PART OF THE ACCORDION -->
                                    <div :id="'flush-collapseOne'+key" class="accordion-collapse collapse" aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample">
                                      <div class="accordion-body">
                                     
                                      <div v-if="Array.isArray(value)" class="row gy-5">
                                      
                                        <template  v-for="(object,objectkey) in value"  >
                                        <div class="col-12 ">
                                          <div class="row gy-3">
                                          <div v-for="(propertyValue,propertyKey) in object" class="col-12" >
                                          
                                          <div  class="form-underline ">
                                          <div class="form-underline-titel">
                                          <label :for="propertyKey+'input'" >{{propertyKey}}</label>
                                          </div>
                                          <div>
                                            
                                            <input  class="form-control" :id="propertyKey+'input'" v-model="data.editData[key][objectkey][propertyKey]" :placeholder="propertyValue">
                                          </div>
                                          </div>

                                          </div>
                                          </div>

                                          </div>
                                          <hr class="mb-0" v-if="value[value.length-1] != object">
                                        </template>
                                        

                                      
                                      </div>
                                      <div v-else class="row gy-3">
                                      <div  v-for="(propertyValue,propertyKey) in value" class="col-12">
                                    
                                      <div  class="form-underline ">
                                      <div class="form-underline-titel">
                                      <label :for="propertyKey+'input'" >{{propertyKey}}</label>
                                      </div>
                                      <div>
                                        
                                        <input type="email" class="form-control" :id="propertyKey+'input'" v-model="data.editData[key][propertyKey]" :placeholder="propertyValue">
                                      </div>
                                      </div>
                                      </div>
                                      </div>

                                      
                                      
                                      
                                      

                                      </div>
                                    </div>
                                  </div>

                                  <!-- -->

                               
                        
                        
                                <!-- END OF THE ACCORDION -->

                                </template>
                                <!-- optional footer -->
                                <template v-if="isEditDataChanged" v-slot:footer>
                                  <button @click="submitProfilChange" role="button" class="btn btn-primary">submit</button>
                                </template>
                                <!-- end of optional footer -->
                                </bs-modal>

                           
                              
                            

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
                            <div class="gy-3 row align-items-center justify-content-center">
                              <div class="col-1 text-center" >
                              
                              <i class="fa-solid " :class="{...(element.kontakt.includes('@')?{'fa-envelope':true}:{'fa-phone':true})}" style="color:rgb(0, 100, 156)"></i>
                              </div>
                              <div  :class="{...(element.anmerkung? {'col-11':true, 'col-md-6':true, 'col-xl-11':true, 'col-xxl-6':true} : {'col-10':true, 'col-xl-9':true, 'col-xxl-10':true})}">
                                  
                                  <!-- rendering KONTAKT emails -->
                             

                                  <div  class="form-underline ">
                                  <div class="form-underline-titel">{{element.kontakttyp}}</div>
                                  <a  :href="'mailto:'+element.kontakt" v-if="element.kontakt.includes('@')" class="form-underline-content">{{element.kontakt}} </a>
                                  <a  v-else :href="'tel:'+element.kontakt" class="form-underline-content">{{element.kontakt}} </a>
                                  </div>
                                    
                                 

                              </div>
                              <div v-if="element?.anmerkung" class="offset-1 offset-md-0 offset-xl-1 offset-xxl-0 order-2 order-sm-1 col-10  col-md-4   col-xl-9 col-xxl-4   ">
                                  
                              <div  class="form-underline ">
                              <div class="form-underline-titel">Anmerkung</div>
                              <span  class="form-underline-content">{{element.anmerkung}} </span>
                              </div>

                            
                              </div>
                              <div class="col-1 col-sm-1 order-2  order-lg-1 col-xl-2 col-xxl-1 allign-middle">
                                  <i v-if="element.zustellung" class="fa-solid fa-check"></i>
                                  <i v-else="element.zustellung" class="fa-solid fa-xmark"></i>
                              </div>
                            </div>
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
                            <div class="gy-3 row justify-content-center align-items-center">
                            
                            <!-- column 1 in the address row -->
                            
                                <div class="col-1 text-center">
                                 
                                  <i class="fa fa-location-dot fa-lg" style="color:#00649C "></i>
                                
                                </div>
                                <div  class="col-11 col-sm-8 col-xl-11 col-xxl-8 order-1">

                                <div class="form-underline ">
                                <div class="form-underline-titel">Strasse</div>
                                <span class="form-underline-content">{{element.strasse}} </span>
                                </div>


                                </div>
                                
                            <!-- column 2 in the address row -->
                                <div  class="offset-1 offset-sm-0 offset-xl-1 offset-xxl-0 order-2 order-sm-4 order-xl-2 order-xxl-4 col-11 col-sm-5  col-xl-11 col-xxl-5  ">
                                    

                                    <div class="form-underline ">
                                    <div class="form-underline-titel">Typ</div>
                                    <span class="form-underline-content">{{element.adr_typ}} </span>
                                    </div>

                                </div>
                                <div  class="offset-1 order-3 order-sm-3 col-11 col-sm-6  col-xl-7 col-xxl-6 ">
                                    
                                    <div class="form-underline ">
                                    <div class="form-underline-titel">Ort</div>
                                    <span class="form-underline-content">{{element.ort}} </span>
                                    </div>
                                </div>
                                <div  class="offset-1 offset-sm-0 order-4 order-sm-2 order-xl-4 order-xxl-2 col-11 col-sm-3 col-xl-4 col-xxl-3 ">
                                    <div class="form-underline ">
                                    <div class="form-underline-titel">PLZ</div>
                                    <span class="form-underline-content">{{element.plz}} </span>
                                    </div>
                                </div>
                            </div>
                          </div>
                          </div>
                            </div>
                        </div>
                      </div>
                    </div>
                    <!-- -->



                    <!-- END OF THE SECOND INFORMATION COLUMN -->
                    </div>


                    <!-- START OF THE SECOND PROFIL INFORMATION ROW --> 
                  
                    
                  <!-- ROW WITH PROFIL IMAGE AND INFORMATION END -->
                </div  >



                




                <!-- SECOND ROW UNDER THE PROFIL IMAGE AND INFORMATION WITH THE TABLES -->
                <div class="row">

                <!-- FIRST TABLE -->
                  <div class="col-12 mb-4" >
                  <div class="card">
                  <div class="card-header">Funktionen </div>
                  <div class="card-body">
                    <core-filter-cmpt   ref="funktionenTable" :tabulator-options="funktionen_table_options"  tableOnly :sideMenu="false" />
                    </div>
                    </div>
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
                <div  class="row d-none d-md-block mb-3">
                  <div class="col">
                 
                    <div class="card">
                      <div class="card-header">
                      Quick Links
                      </div>
                      <div class="card-body">
                      
                       
                        <a style="text-decoration:none" class="my-1 d-block" href="#">Zeitwuensche</a>
                        <a style="text-decoration:none" class="my-1 d-block" href="#">Lehrveranstaltungen</a>
                        <a style="text-decoration:none" class="my-1 d-block" href="#">Zeitsperren von Gschnell</a>

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
