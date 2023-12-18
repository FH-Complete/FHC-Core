import fhcapifactory from "../../../apps/api/fhcapifactory.js";
import { CoreFilterCmpt } from "../../../components/filter/Filter.js";

export default {
  components: {
    CoreFilterCmpt,
  },
  data() {
    return {
      collapseIconFunktionen: true,
      collapseIconBetriebsmittel: true,
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
    collapseFunction(e, column) {
      //* the if of the column has to match with the name of the responsive data in the vue component
      this[e.target.id] = !this[e.target.id];

      //* gets all event icons of the different rows to use the onClick event later
      let allClickableIcons = column._column.cells.map((row) => {
        return row.element.children[0];
      });

      //* changes the icon that shows or hides all the collapsed columns
      //* if the replace function does not find the class to replace, it just simply returns false
      if (this[e.target.id]) {
        e.target.classList.replace("fa-angle-up", "fa-angle-down");
      } else {
        e.target.classList.replace("fa-angle-down", "fa-angle-up");
      }

      //* changes the icon for every collapsed column to open or closed
      if (this[e.target.id]) {
        allClickableIcons
          .filter((column) => {
            return !column.classList.contains("open");
          })
          .forEach((col) => {
            col.click();
          });
      } else {
        allClickableIcons
          .filter((column) => {
            return column.classList.contains("open");
          })
          .forEach((col) => {
            col.click();
          });
      }
    },
  },

  computed: {
    refreshMailTo() {
      return `mailto:info.mio@technikum-wien.at?subject=Datenkorrektur&body=Die%20Profildaten%20für%20User%20'${this.data.username}'%20sind%20nicht%20korrekt.%0DHier, die richtigen Daten:%0A%0ANachname:%20${this.data.nachname}%0AVorname:%20${this.data.vorname}%0AGeburtsdatum:${this.data.gebdatum}%0AGeburtsort:%20${this.data.gebort}%0ATitelPre:${this.data.titel}%20%0ATitelPost:${this.data.postnomen}%20%0A%0A***%0DPlatz für weitere (nicht angeführte Daten)%0D***%0A%0A[Bitte%20übermitteln%20Sie%20uns%20etwaige%20Dokumente%20zum%20Beleg%20der%20Änderung]`;
    },

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
        Telefon: this.data.telefonklappe,
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

  mounted() {
    this.$refs.betriebsmittelTable.tabulator.on("tableBuilt", () => {
      this.$refs.betriebsmittelTable.tabulator.setData(this.data.mittel);
    });

    this.$refs.funktionenTable.tabulator.on("tableBuilt", () => {
      this.$refs.funktionenTable.tabulator.setData(this.data.funktionen);
    });
  },

  template: ` 

  <div class="container-fluid text-break"  >
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


              <div class="col-12">
             
             
                    <div class=" form-floating mb-2">
                      <div class="card">
                        <div class="card-body">
                          <span>Der FH Ausweis ist am <b>{{data.zutrittsdatum}}</b> ausgegeben worden.</span>
                        </div>
                      </div>
                    </div>
          
              </div>


              <!-- MAIN PANNEL -->
              <div class="col-sm-12 col-md-9">
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
                      
                       


                  
                  <div  class="row justify-content-center align-items-center">




                  <!-- SQUEEZING THE IMAGE INSIDE THE FIRST INFORMATION COLUMN -->
 <!-- START OF THE FIRST ROW WITH THE PROFIL IMAGE -->
          <div class="col-12 col-sm-6 mb-2">
           <div class="row justify-content-center">
                        <div class="col-auto " style="position:relative">
                          <img class=" img-thumbnail " style=" max-height:150px; "  :src="get_image_base64_src"></img>
                          <!-- LOCKING IMAGE FUNCTIONALITY -->
                          
                          
                          <div role="button" @click.prevent="sperre_foto_function" style="height:22px; width:21px; background-color:white; position:absolute; top:0; right:12px; display:flex; align-items:center; justify-content:center;" >
                          <i :class="{'fa':true, ...(data.foto_sperre?{'fa-lock':true}:{'fa-lock-open':true})} " ></i>
                          
                          
                          </div>
                        </div>
                      </div>
                    <!-- END OF THE ROW WITH THE IMAGE -->
                    </div>
<!-- END OF SQUEEZE -->



<!-- COLUMNS WITH MULTIPLE ROWS NEXT TO PROFIL PICTURE -->
                  <div class="col-12 col-sm-6">
                  <div class="row">
                  <div class="col-12">
                  <div class=" form-floating mb-2">
                        
                  <input  readonly class="form-control form-control-plaintext border-bottom" id="floatingInputValue"  :value="data.vorname">
                  <label for="floatingInputValue">Vorname</label>
                </div>
                </div>
                <div class="col-12">
                <div class=" form-floating mb-2">
                        
                  <input  readonly class="form-control form-control-plaintext border-bottom" id="floatingInputValue"  :value="data.nachname">
                  <label for="floatingInputValue">Nachname</label>
                </div>
                </div>
                </div>
             
                
                  </div>
                  







                  <div v-for="(wert,bez) in personData" class="col-md-6 col-sm-12 ">
                  <div class=" form-floating mb-2">
                        
                  <input  readonly class="form-control form-control-plaintext border-bottom" id="floatingInputValue" placeholder="name@example.com" :value="wert?wert:'-'">
                  <label for="floatingInputValue">{{bez}}</label>
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
                             <div class="row">
                             <div v-for="(wert,bez) in specialData" class="col-md-6 col-sm-12 ">
                                 <div class=" form-floating mb-2">    
                                 <input  readonly class="form-control form-control-plaintext border-bottom" id="floatingInputValue" placeholder="name@example.com" :value="wert?wert:'-'">
                                 <label for="floatingInputValue">{{bez}}</label>
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
                  
                  
                      <div v-for="email in wert" v-if="typeof wert === 'object' && bezeichnung == 'emails'" class="row justify-content-center">
                      <div  class="col-12 ">
                      <div class=" form-floating mb-2">
                            
                      <a  :href="'mailto:'+email.email" readonly class="form-control form-control-plaintext border-bottom" id="floatingFhAusweis" >{{email.email}}</a>
                      <label for="floatingFhAusweis">{{email.type }}</label>
                    
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
                          <div class="card-body text-center">
                            
                            <div v-for="element in privateKontakte" class="align-items-center row justify-content-center">
                           
                              <div  :class="{...(element.anmerkung? {'col-10':true, 'col-md-6':true} : {'col-10':true, 'col-xl-11':true})}">
                                  <div class=" form-floating mb-2">
                                      <input  readonly class="form-control form-control-plaintext border-bottom" id="floatingKontakt" :value="element.kontakt">
                                      <label for="floatingKontakt">{{element.kontakttyp}}</label>
                                  </div>
                              </div>
                              <div v-if="element?.anmerkung" class="col-12 col-md-4 col-lg-4 col-xl-5 order-2 order-md-1 ">
                                  <div class=" form-floating mb-2">
                                      <input   readonly class="form-control form-control-plaintext border-bottom" id="floatingAnmerkung" :value="element.anmerkung">
                                      <label for="floatingAnmerkung">Anmerkung</label>
                                  </div>
                              </div>
                              <div class="col-2 order-1 order-md-2 col-xl-1 allign-middle">
                                  <i v-if="element.zustellung" class="fa-solid fa-check"></i>
                                  <i v-else="element.zustellung" class="fa-solid fa-xmark"></i>
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
                          
                            <div v-for="element in privateAdressen" class="row justify-content-center align-items-center">
                            <!-- column 1 in the address row -->
                                <div class="col-2 col-sm-1 text-center">
                                 
                                  <i class="fa fa-location-dot fa-lg" style="color:#00649C "></i>
                                
                                </div>
                                <div  class="col-10 col-sm-11 ">
                                    <div class=" form-floating mb-2">
                                        <input   readonly class="form-control form-control-plaintext border-bottom" id="floatingFhAusweis" :value="element.strasse">
                                        <label for="floatingFhAusweis">Strasse</label>
                                    </div>
                                </div>
                            <!-- column 2 in the address row -->
                                <div  class="col-lg-4 col-md-4 col-sm-6 col-xs-6 order-sm-1">
                                    <div class=" form-floating mb-2">
                                        <input   readonly class="form-control form-control-plaintext border-bottom" id="floatingFhAusweis" :value="element.adr_typ">
                                        <label for="floatingFhAusweis">Typ</label>
                                    </div>
                                </div>
                                <div  class="col-md-5 col-sm-12 col-xs-12 ">
                                    <div class=" form-floating mb-2">
                                        <input   readonly class="form-control form-control-plaintext border-bottom" id="floatingFhAusweis" :value="element.ort">
                                        <label for="floatingFhAusweis">Ort</label>
                                    </div>
                                </div>
                                <div  class="col-lg-3 col-md-3 col-sm-6 col-xs-6 order-sm-2">
                                    <div class=" form-floating mb-2">
                                        <input   readonly class="form-control form-control-plaintext border-bottom" id="floatingFhAusweis" :value="element.plz">
                                        <label for="floatingFhAusweis">PLZ</label>
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



                <!-- LITTLE EXTRA ROW WITH INFORMATION REFRESHING LINK -->
                <div class="row">
                  <div   class="col ">
                    <p>Sollten Ihre Daten nicht mehr aktuell sein, klicken Sie bitte <a :href="refreshMailTo">hier</a></p>
                  </div>
                </div>
                <!-- END OF REFRESHING LINK ROW -->




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
              <div  class="col-md-3 col-sm-12 text-break" >


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
                              <div class="col-1">
                              
                              <i class="fa-solid fa-envelope" style="color: #00649C;"></i>
                              
                              </div>
                              <div class="col-11">
                                <a :href="verteiler.mailto"><b>{{verteiler.gruppe_kurzbz}}</b></a>
                              </div>
                            </div>
                           
                          </div> 
                          <div class="col-12 ">{{verteiler.beschreibung}}</div>
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
