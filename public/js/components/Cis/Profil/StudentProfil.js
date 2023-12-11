import fhcapifactory from "../../../apps/api/fhcapifactory.js";
import { CoreFilterCmpt } from "../../../components/filter/Filter.js";


export default {
  components: {
    CoreFilterCmpt,
  },
  data() {
    return {
     

    
      betriebsmittel_table_options: {
        height: 300,
        layout: "fitColumns",
        data: [{ betriebsmittel: "", Nummer: "", Ausgegeben_am: "" }],
        columns: [
          {
            title: "Betriebsmittel",
            field: "betriebsmittel",
            headerFilter: true,
          },
          { title: "Nummer", field: "Nummer", headerFilter: true },
          {
            title: "Ausgegeben_am",
            field: "Ausgegeben_am",
            headerFilter: true,
          },
        ],
      },
      zutrittsgruppen_table_options: {
        height: 300,
        layout: "fitColumns",
        data: [{ bezeichnung: "test1" }],
        columns: [{ title: "Zutritt", field: "bezeichnung" }],
      },
    };
  },

  //? this is the prop passed to the dynamic component with the custom data of the view
  props: ["data"],
  methods: {
    sperre_foto_function(value) {
      if (!this.data) {
        return;
      }
      fhcapifactory.UserData.sperre_foto_function(value).then((res) => {
        this.data.foto_sperre = res.data.foto_sperre;
      });
    },
  },
  computed: {
    refreshMailTo() {
      return `mailto:info.mio@technikum-wien.at?subject=Datenkorrektur&body=Die%20Profildaten%20für%20User%20'${this.data.username}'%20sind%20nicht%20korrekt.%0DHier, die richtigen Daten:%0A%0ANachname:%20${this.data.nachname}%0AVorname:%20${this.data.vorname}%0AGeburtsdatum:${this.data.gebdatum}%0AGeburtsort:%20${this.data.gebort}%0ATitelPre:${this.data.titel}%20%0ATitelPost:${this.data.postnomen}%20%0A%0A***%0DPlatz für weitere (nicht angeführte Daten)%0D***%0A%0A[Bitte%20übermitteln%20Sie%20uns%20etwaige%20Dokumente%20zum%20Beleg%20der%20Änderung]`
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
        Allgemein: {
          Username: this.data.username,
          Matrikelnummer: this.data.matrikelnummer,
          Anrede: this.data.anrede,
          Titel: this.data.titel,
          Vorname: this.data.vorname,
          Nachname: this.data.nachname,
          Postnomen: this.data.postnomen,
          Geburtsdatum: this.data.gebdatum,
          Geburtsort: this.data.gebort,
          Studiengang: this.data.studiengang,
          Semester: this.data.semester,
          Verband: this.data.verband,
          Gruppe: this.data.gruppe,
          Personenkennzeichen: this.data.personenkennzeichen,
          FhAusweisStatus: this.data.zutrittsdatum,
        },
        
        
      };
    },
    //? this computed function returns the data for the second column in the profil 
    kontaktInfo() {
      if (!this.data) {
        return {};
      }

      return {
        emails: this.data.emails,
        Kontakte: this.data.kontakte,
        Adressen: this.data.adressen,
      };
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

  <!-- CONTAINER -->
  <div class="container-fluid">
    <!-- ROW --> 
          <div class="row">
          <!-- HIDDEN QUICK LINKS -->
              <div  class="d-md-none col-12 ">
             




                <div style="border:4px solid;border-color:#EEEEEE;" class="row py-4">
                  <div class="col">
                      <div class="dropdown">
                      <button style="width:100%" class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu2" data-bs-toggle="dropdown" aria-expanded="false">
                          Quick links
                      </button>
                      <ul class="dropdown-menu" aria-labelledby="dropdownMenu2">
                          <li><button class="dropdown-item" type="button">Zeitwuensche</button></li>
                          <li><button class="dropdown-item" type="button">Lehrveranstaltungen</button></li>
                          <li><button class="dropdown-item" type="button">Zeitsperren von Gschnell</button></li>
                      </ul>
                      </div>
                  </div>
                </div>


              
              
              </div>
              <!-- END OF HIDDEN QUCK LINKS -->



              <!-- MAIN PANNEL -->
              <div class="col-sm-12 col-md-9">
                <!-- ROW WITH PROFIL IMAGE AND INFORMATION -->
                <div class="row">
                  <!-- COLUMN WITH PROFIL IMAGE -->
                    <div class="col-md-2 col-sm-12" style="border:4px solid;border-color:lightgreen;">






                    <!-- START OF THE FIRST ROW WITH THE PROFIL IMAGE -->
                    <div class="row justify-content-center">
                    <div class="col-auto ">
                          <img class=" img-thumbnail " style=" max-height:150px"  :src="get_image_base64_src"></img>
                        </div>
                      </div>
                    <!-- END OF THE ROW WITH THE IMAGE -->






                    <!-- START OF THE SECOND ROW WITH THE IMAGE LINK -->
                    <div class="row justify-content-center">
                    <div class="col-auto text-center ">
                          

                        
                        <div v-if="data.foto_sperre ">
                          <p class="m-0">Profilfoto gesperrt</p>
                          <a href="#" @click.prevent="sperre_foto_function(false)" class="text-decoration-none">Sperre des Profilfotos aufheben</a>
                        </div>
                        <!-- DIESEN LINK KOENNTE MAN MIT EINEM ICON AUSTAUSCHEN WENN DIE VIEWPORT KLEIN IST -->
                        <a href="#" @click.prevent="sperre_foto_function(true)" class="text-decoration-none"  v-else>Profilfoto sperren</a>
                     



                        </div>
                      </div>
                    <!-- END OF THE ROW WITH THE IMAGE LINK -->






                    <!-- END OF THE COLUMN WITH PROFIL IMAGE AND LINK -->
                    </div>



                    <!-- COLUMN WITH THE PROFIL INFORMATION --> 
                    <div class="col-md-10 col-sm-12" style="border:4px solid;border-color:lightcoral;">
              

                    <!-- INFORMATION CONTENT START -->
                    <!-- ROW WITH THE PROFIL INFORMATION --> 
                    <div class="row">



                      <!-- FIRST COLUMN WITH PROFIL INFORMATION -->
                      <div style="word-wrap:break-word;border:4px solid;border-color:red" class="col-md-12 col-lg-6">





                        <div   v-for="(wert,bezeichnung) in personData">
                            <dl class="  mb-0" v-else v-for="(wert,bez) in wert">
                                <div class="row justify-content-center">
                                    <dt class="col-6" >{{bez}}</dt>
                                    <dd class=" col-6">{{wert?wert:"-"}}</dd>
                                </div>
                            </dl>
                            <div class="row justify-content-center" v-if="bez=='FhAusweisStatus'">
                                <dt class="col-6" >FH-Ausweis Status</dt>
                                <dd class=" col-6 m-0">{{"Der FH Ausweis ist am "+ wert+ " ausgegeben worden."}}</dd>
                            </div>
                        </div>



                      <!-- END OF THE FIRST INFORMATION COLUMN -->
                      </div>


                      <!-- START OF THE SECOND PROFIL INFORMATION COLUMN -->
                      <div style="word-wrap:break-word;border:4px solid;border-color:orange" class="col-lg-6 col-md-12">






                        <dl v-for="(wert,bezeichnung) in kontaktInfo">

                        <!-- HIER SIND DIE EMAILS -->
                    
                    
                          <div class="justify-content-center row mb-3" v-if="typeof wert === 'object' && bezeichnung == 'emails'">
                              <dt class="col-4  mb-0">eMail</dt>
                              <div class="col-8 ">
                                  <dd v-for="email in wert" class="mb-0 ">{{email.type}}: <a style="text-decoration:none" :href="'mailto:'+email.email">{{email.email}}</a></dd>
                              </div>
                          </div>
                      
                      
                          <!-- HIER SIND DIE PRIVATEN KONTAKTE -->
                          <div class="justify-content-center row mb-3" v-if="typeof wert === 'object' && bezeichnung=='Kontakte'">
                              <dt   class="col-4 mb-0">Private Kontakte</dt>
                              <div class="col-8 ">
                                  <dd class="row justify-end" v-for="element in wert" >
                                      <div class="col-8">{{element.kontakttyp + ":  " + element.kontakt+"  " }}</div>
                                      <div class="col-2"> {{element?.anmerkung}}</div>
                                      <div class="col-2"> 
                                          <i v-if="element.zustellung" class="fa-solid fa-check"></i>
                                          <i v-else="element.zustellung" class="fa-solid fa-xmark"></i>
                                      </div>
                                  </dd>
                              </div>
                          </div>
                      
                      
                      
                          <!-- HIER SIND DIE ADRESSEN -->
                          <div class=" justify-content-center row mb-3" v-if="typeof wert === 'object' && bezeichnung=='Adressen'">
                              <dt class="col-4">Adressen</dt>
                              <div class="col-8">
                                  <dd class="  m-0"  v-for="element in wert">
                                      {{element.strasse}} <b>({{element.adr_typ}})</b><br/>{{ element.plz}} {{element.ort}}
                                  </dd>
                              </div>
                          </div>
                    
                        </dl>




                      <!-- END OF THE SECOND INFORMATION COLUMN -->
                      </div>


              
                    <!-- END OF PROFIL INFORMATION ROW -->
                    <!-- INFORMATION CONTENT END -->
                    </div>


                    <!-- COLUMN WITH ALL PROFIL INFORMATION END -->
                  </div>
                  <!-- ROW WITH PROFIL IMAGE AND INFORMATION END -->
                </div  >



                <!-- LITTLE EXTRA ROW WITH INFORMATION REFRESHING LINK -->
                <div class="row">
                  <div style="border:4px solid;border-color:lightpink" class="col">
                    <p>Sollten Ihre Daten nicht mehr aktuell sein, klicken Sie bitte <a :href="refreshMailTo">hier</a></p>
                  </div>
                </div>
                <!-- END OF REFRESHING LINK ROW -->




                <!-- SECOND ROW UNDER THE PROFIL IMAGE AND INFORMATION WITH THE TABLES -->
                <div class="row">

                <!-- FIRST TABLE -->
                  <div class="col-12" style="border: 4px solid; border-color:lightskyblue">
                  <core-filter-cmpt title="Entlehnte Betriebsmittel"  ref="betriebsmittelTable" :tabulator-options="betriebsmittel_table_options" :tableOnly />
                  </div>

                <!-- SECOND TABLE -->
                  <div class="col-12" style="border:4px solid;border-color:orange">
                  <core-filter-cmpt title="Zutrittsgruppen" ref="zutrittsgruppenTable" :tabulator-options="zutrittsgruppen_table_options" :tableOnly :noColFilter />
                  </div>

                <!-- END OF THE ROW WITH THE TABLES UNDER THE PROFIL INFORMATION -->
                </div>







              <!-- END OF MAIN CONTENT COL -->
              </div>




              <!-- START OF SIDE PANEL -->
              <div class="col-md-3 col-sm-12">


              <!-- SRART OF QUICK LINKS IN THE SIDE PANEL -->


              <!-- START OF THE FIRDT ROW IN THE SIDE PANEL -->
              <!-- THESE QUCK LINKS ARE ONLY VISIBLE UNTIL VIEWPORT MD -->
                <div style="border:4px solid;border-color:#EEEEEE;" class="row d-none d-md-block">
                  <div class="col">
                    <div  class="row py-4">
                        <a style="text-decoration:none" class="my-1" href="#">Zeitwuensche</a>
                        <a style="text-decoration:none" class="my-1" href="#">Lehrveranstaltungen</a>
                        <a style="text-decoration:none" class="my-1" href="#">Zeitsperren von Gschnell</a>
                    </div>
                  </div>
                </div>


                <!-- START OF THE SECOND ROW IN THE SIDE PANEL -->
                <div style="border:4px solid;border-color: darkgray"  class="row">
                
                  <div class="col">
                

                  
                  <!-- HIER SIND DIE MAILVERTEILER -->
                    <h5 class="fs-3" style="margin-top:1em">Mailverteilers</h5>
                    <p class="fs-6">Sie sind Mitgglied in folgenden Verteilern:</p>
                    <div  class="row text-break" v-for="verteiler in data?.mailverteiler">
                      <div class="col-6"><a :href="verteiler.mailto"><b>{{verteiler.gruppe_kurzbz}}</b></a></div> 
                      <div class="col-6">{{verteiler.beschreibung}}</div>
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
