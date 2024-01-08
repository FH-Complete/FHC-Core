import fhcapifactory from "../../../apps/api/fhcapifactory.js";
import { CoreFilterCmpt } from "../../../components/filter/Filter.js";

export default {
  components: {
    CoreFilterCmpt,
  },
  data() {
    return {
      
      collapseIconBetriebsmittel: true,
      zutrittsgruppen_table_options: {
        height: 200,
        layout: "fitColumns",
        data: [{ bezeichnung: "" }],
        columns: [{ title: "Zutritt", field: "bezeichnung" }],
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
  props: ["data"],
  methods: {
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
 

    get_image_base64_src() {
      if (!this.data) {
        return "";
      }
      return "data:image/jpeg;base64," + this.data.foto;
    },

   
    //? this computed function returns all the informations for the first column in the profil
    personData() {
      if (!this.data) {
        return {};
      }

      return {
        Username: this.data.username,
        Anrede: this.data.anrede,
        Matrikelnummer: this.data.matrikelnummer,
        Titel: this.data.titelpre,
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
        Personenkennzeichen: this.data.personenkennzeichen,
        Studiengang: this.data.studiengang,
        Semester: this.data.semester,
        Verband: this.data.verband,
        Gruppe: this.data.gruppe.trim(),
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
    

  
  },

  mounted() {
    this.$refs.betriebsmittelTable.tabulator.on('tableBuilt', () => {
    
      this.$refs.betriebsmittelTable.tabulator.setData(this.data.mittel);
      
  }) 

  this.$refs.zutrittsgruppenTable.tabulator.on('tableBuilt', () => {
  
      this.$refs.zutrittsgruppenTable.tabulator.setData(this.data.zuttritsgruppen);
      
  }) 

   
  },

  template: ` 

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
                    <a href="#" class="list-group-item list-group-item-action ">Zeitsperren</a>
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
                    <div class="row mb-4 ">

















<!-- FIRST KAESTCHEN -->
                      <div  class="col-lg-12 col-xl-6 ">
                     <div class="row mb-4 ">
                     <div class="col">
                     
                        
                      <div class="card h-100">
                      <div class="card-header">
                      StudentIn
                      </div>
                      <div class="card-body">
                      
                       


                  
                  <div  class="row gy-3 align-items-center">




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
                 
                        
                  <div  class="form-underline ">
                  <div class="form-underline-titel">Vorname</div>
                  <span class="form-underline-content">{{data.vorname}} </span>
                  
                  </div>

                  
               
                </div>
                <div class="col-12">
                
                     

                  <div  class="form-underline ">
                  <div class="form-underline-titel">Nachname</div>
                  <span class="form-underline-content">{{data.nachname}} </span>
                  
                  </div>
                
                </div>
                </div>
             
                
                  </div>
                  

                  <div v-for="(wert,bez) in personData" class="col-md-6 col-sm-12 ">
          


                  <div  class="form-underline ">
                  <div class="form-underline-titel">{{bez}}</div>
                  <span class="form-underline-content">{{wert?wert:'-'}} </span>
                  
                  </div>
                  
                  </div>

                  
                      </div>


                      
                      </div>
                    </div>
		    </div>
                    </div>
                     <div class="row mb-4 ">
                     

                     <div  class=" col-lg-12">
        
                     <div class="card">
                 
                         <div class="card-header">
                         Studenten Information
                         </div>
                         <div class="card-body">
                             <div class="row gy-3">
                             <div v-for="(wert,bez) in specialData" class="col-md-6 col-sm-12 ">
                             
                          
                             <div  class="form-underline ">
                             <div class="form-underline-titel">{{bez}}</div>
                             <span class="form-underline-content">{{wert?wert:'-'}} </span>
                             
                             </div>
                           
                               
                               
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
                  
                  
                      <div  v-if="typeof wert === 'object' && bezeichnung == 'emails'" class="row gy-3 justify-content-center ">
                      <div v-for="email in wert" class="col-12 ">
                     
                            <div class="row align-items-center">
                            <!--
                      <div class="col-1 text-center">

                      <i class="fa-solid fa-envelope" style="color:rgb(0, 100, 156)"></i>

                      </div>
                      -->
                      <div class="col">
                      <div class=" form-floating mb-2">
                            
                            <div class="form-underline ">
                            <div class="form-underline-titel">{{email.type}}</div>
                            <a :href="'mailto'+email.email" class="form-underline-content">{{email.email}} </a>
                            </div>

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
                    <core-filter-cmpt title="Entlehnte Betriebsmittel"  ref="betriebsmittelTable" :tabulator-options="betriebsmittel_table_options" tableOnly :sideMenu="false" />
                  </div> 
                  
                  <!-- SECOND TABLE -->
                  <div class="col-12 mb-4" >
                    <core-filter-cmpt title="Zutrittsgruppen" ref="zutrittsgruppenTable" :tabulator-options="zutrittsgruppen_table_options"  tableOnly :sideMenu="false" noColumnFilter />
            
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
