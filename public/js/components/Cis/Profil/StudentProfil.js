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
        },
        
        Adressen: this.data.adressen,
        SpecialInformation: {
          Studiengang: this.data.studiengang,
          Semester: this.data.semester,
          Verband: this.data.verband,
          Gruppe: this.data.gruppe,
          Personenkennzeichen: this.data.personenkennzeichen,
        },
      };
    },
    //? this computed function returns the data for the second column in the profil 
    kontaktInfo() {
      if (!this.data) {
        return {};
      }

      return {
        FhAusweisStatus: this.data.zutrittsdatum,
        emails: this.data.emails,
        Kontakte: this.data.kontakte,
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

  <div class="container-fluid">
  <!-- here starts the row of the whole window -->
      <div class="row">
      <!-- this is the left column of the window -->
          <div class="col-9">
              <div class="row">
                  


                  <div class="col-2">
                  <div class="row">
                      <div class="col">
                      <h3 >Student</h3>
                          <img class="img-thumbnail" :src="get_image_base64_src"></img>
                      </div>
                  </div>
                  <div class="row">
                      <div class="col">
                          <div v-if="data.foto_sperre ">
                              <p class="m-0">Profilfoto gesperrt</p>
                              <a href="#" @click.prevent="sperre_foto_function(false)" class="text-decoration-none">Sperre des Profilfotos aufheben</a>
                          </div>
                          <a href="#" @click.prevent="sperre_foto_function(true)" class="text-decoration-none"  v-else>Profilfoto sperren</a>
                      </div>
                  </div>
              </div>



                  
                
                  <div class="col m-4">
              
              
                      
                  
                      
                      <div class="row">
                        <div :class="{'col-lg-12':true, 'col-xl-6':true, 'order-1':bezeichnung=='Allgemein', 'order-3':bezeichnung=='SpecialInformation', 'order-4':bezeichnung=='Adressen'}" v-for="(wert,bezeichnung) in personData">
                                                        
                          <dl class="m-0"  v-if="bezeichnung=='Adressen'">
                            <dt>Adressen</dt>
                            <dd class="text-end m-0"  v-for="element in wert">
                            {{element.strasse}} <b>({{element.adr_typ}})</b><br/>{{ element.plz}} {{element.ort}}
                            </dd>
                          </dl>

                          <dl class="m-0" v-else v-for="(wert,bez) in wert">
                            <dt >{{bez}}</dt>
                            <dd class="text-end m-0">{{wert?wert:"-"}}</dd>
                          </dl>
                      
                        </div>
                      
                      
                  
                 
                        <div class="col-lg-12 col-xl-6 order-2">
                        
                            <dl v-for="(wert,bezeichnung) in kontaktInfo">
                            
                            <!-- HIER IST DAS DATUM DES FH AUSWEIS -->
                                <div class="mb-3" v-if="bezeichnung=='FhAusweisStatus'">
                                    <dt class="mb-0">FH-Ausweis Status</dt>
                                    <dd class="mb-0 text-end">{{"Der FH Ausweis ist am "+ wert+ " ausgegeben worden."}}</dd>
                                </div>

                            <!-- HIER SIND DIE EMAILS -->
                    

                                <div class="mb-3" v-if="typeof wert === 'object' && bezeichnung == 'emails'">
                                    <dt class="mb-0">eMail</dt>
                                    <dd v-for="email in wert" class="mb-0 text-end">{{email.type}}: <a style="text-decoration:none" :href="'mailto:'+email.email">{{email.email}}</a></dd>
                                </div>

                                <!-- HIER SIND DIE PRIVATEN KONTAKTE -->
                                <div class="mb-3" v-if="typeof wert === 'object' && bezeichnung=='Kontakte'">
                                <dt class="mb-0">Private Kontakte</dt>
                                    <dd class="row text-end" v-for="element in wert" >
                                        <div class="col-8">{{element.kontakttyp + ":  " + element.kontakt+"  " }}</div>
                                        <div class="col-2"> {{element?.anmerkung}}</div>
                                        <div class="col-2"> 
                                        <i v-if="element.zustellung" class="fa-solid fa-check"></i>
                                        <i v-else="element.zustellung" class="fa-solid fa-xmark"></i>
                                        </div>
                                    </dd>
                                </div>
                            
                            </dl>
                        </div>
                      </div>


                  </div>
              </div>
            
              <div class="row">
              <div class="col">
              <p>Sollten Ihre Daten nicht mehr aktuell sein, klicken Sie bitte <a :href="refreshMailTo">hier</a></p>
              </div>
              </div>
              <div class="row my-5">
                        
                
                            <div class="col-12">
                            <core-filter-cmpt title="Entlehnte Betriebsmittel"  ref="betriebsmittelTable" :tabulator-options="betriebsmittel_table_options" :tableOnly />
                            </div>
                            <div class="col-12">
                            <core-filter-cmpt title="Zutrittsgruppen" ref="zutrittsgruppenTable" :tabulator-options="zutrittsgruppen_table_options" :tableOnly :noColFilter />
                            </div>
                        
                        </div>

      
          </div>

          <div  class="col-3">
              <div style="background-color:#EEEEEE" class="row py-4">
              <a style="text-decoration:none" class="my-1" href="#">Zeitwuensche</a>
              <a style="text-decoration:none" class="my-1" href="#">Lehrveranstaltungen</a>
              <a style="text-decoration:none" class="my-1" href="#">Zeitsperren von Gschnell</a>
              </div>
              <div class="row">
                  <h5 class="fs-3" style="margin-top:1em">Mailverteilers</h5>
                  <p class="fs-6">Sie sind Mitgglied in folgenden Verteilern:</p>
                  <div  class="row text-break" v-for="verteiler in data?.mailverteiler">
                      <div class="col-6"><a :href="verteiler.mailto"><b>{{verteiler.gruppe_kurzbz}}</b></a></div> 
                      <div class="col-6">{{verteiler.beschreibung}}</div>
                  </div>
              </div>
          </div>
      </div>
  </div>


           
            
    `,
};
