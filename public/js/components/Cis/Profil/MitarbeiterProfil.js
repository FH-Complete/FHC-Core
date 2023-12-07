import fhcapifactory from "../../../apps/api/fhcapifactory.js";
import { CoreFilterCmpt } from "../../../components/filter/Filter.js";


export default {
  components: {
    CoreFilterCmpt,
  },
  data() {
    return {
      

      funktionen_table_options: {
        height: 300,
        layout: "fitColumns",
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
          { title: "Bezeichnung", field: "Bezeichnung", headerFilter: true },
          {
            title: "Organisationseinheit",
            field: "Organisationseinheit",
            headerFilter: true,
          },
          { title: "Gültig_von", field: "Gültig_von", headerFilter: true },
          { title: "Gültig_bis", field: "Gültig_bis", headerFilter: true },
          {
            title: "Wochenstunden",
            field: "Wochenstunden",
            headerFilter: true,
          },
        ],
      },
      betriebsmittel_table_options: {
        height: 300,
        layout: "fitColumns",
        data: [{ betriebsmittel: "<a href='#'>test</a>", Nummer: "", Ausgegeben_am: "" }],
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
          Anrede: this.data.anrede,
          Titel: this.data.titel,
          Vorname: this.data.vorname,
          Nachname: this.data.nachname,
          Postnomen: this.data.postnomen,
          Geburtsdatum: this.data.gebdatum,
          Geburtsort: this.data.gebort,
          Kurzzeichen: this.data.kurzbz,
          Telefon: this.data.telefonklappe,
          Büro:this.data.ort_kurzbz,
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

    this.$refs.funktionenTable.tabulator.on('tableBuilt', () => {
    
        this.$refs.funktionenTable.tabulator.setData(this.data.funktionen);
        
    }) 
 
  },

  template: `

            <div class="container-fluid">
            <!-- here starts the row of the whole window -->
                <div class="row ">
                <!-- this is the left column of the window -->
                    <div class="col-md-12 col-lg-9">
                    
                        <div class="row align-items-center">
                            


                            <div style="background-color:lightgreen" class="col-md-12 col-lg-2">
                         
                            <div class="row">
                                <div class="align-middle ">
                                <h3 >Mitarbeiter</h3>
                                    <img class=" img-thumbnail " :height="" :src="get_image_base64_src"></img>
                                   
                                </div>
                                <div  style="background-color:#EEEEEE" class=" lg-invisible col row py-4">
                                <a style="text-decoration:none" class="my-1" href="#">Zeitwuensche</a>
                                <a style="text-decoration:none" class="my-1" href="#">Lehrveranstaltungen</a>
                                <a style="text-decoration:none" class="my-1" href="#">Zeitsperren von Gschnell</a>
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



                            
                          
                            <div style="background-color:lightblue" class="col-lg-10 col-md-12 ">
                        
                        
                                
                            
                                
                                <div class="row">
                                  <div style="background-color:lightsalmon" :class="{ 'col-lg-12':true, 'col-xl-6':true, 'order-1':bezeichnung=='Allgemein',  'order-4':bezeichnung=='Adressen'}" v-for="(wert,bezeichnung) in personData">
                                                                  
                                    

                                    <dl class="  mb-3" v-else v-for="(wert,bez) in wert">
                                    
                                    
                                    <div class="row justify-content-center" v-if="bez=='FhAusweisStatus'">
                                    <dt class="col-md-6 col-xxl-4 m-0" >FH-Ausweis Status</dt>
                                    <dd class=" col-md-6 col-xxl-4 m-0">{{"Der FH Ausweis ist am "+ wert+ " ausgegeben worden."}}</dd>
                                  
                                  </div>
                                  <div v-else class="row justify-content-center">
                                        <dt class="col-md-6 col-xxl-4 m-0" >{{bez}}</dt>
                                        <dd class=" col-md-6 col-xxl-4 m-0">{{wert?wert:"-"}}</dd>
                                        </div>
                                    </dl>
                                
                                  </div>
                                
                                
                            
                           
                                  <div style="background-color: lightcoral" class="col-lg-12 col-xl-6 order-2  ">
                                  
                                      <dl v-for="(wert,bezeichnung) in kontaktInfo">
                                      
                                      <!-- HIER IST DAS DATUM DES FH AUSWEIS -->
                                      <!-- WAS MOVED TO THE FIRST INFO BOX -->


                                          <div class="justify-content-center row mb-3" v-if="bezeichnung=='FhAusweisStatus'">
                                            <dt class="col-md-6 col-xxl-4  mb-0">FH-Ausweis Status</dt>
                                            <div class="col-md-6 col-xxl-4">
                                              <dd class="mb-0 ">{{"Der FH Ausweis ist am "+ wert+ " ausgegeben worden."}}</dd>
                                            </div>
                                          </div>

                                      <!-- HIER SIND DIE EMAILS -->
                              

                                          <div class="justify-content-center row mb-3" v-if="typeof wert === 'object' && bezeichnung == 'emails'">
                                              <dt class="col-md-6 col-xxl-4 mb-0">eMail</dt>
                                              <div class="col-md-6 col-xxl-4">
                                              <dd v-for="email in wert" class="mb-0 ">{{email.type}}: <a style="text-decoration:none" :href="'mailto:'+email.email">{{email.email}}</a></dd>
                                              </div>
                                            </div>

                                          <!-- HIER SIND DIE PRIVATEN KONTAKTE -->
                                          <div class="justify-content-center row mb-3" v-if="typeof wert === 'object' && bezeichnung=='Kontakte'">
                                          <dt   class="col-md-6 col-xxl-4 mb-0">Private Kontakte</dt>
                                          <div class="col-md-6 col-xxl-4">
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
                                            <dt class="col-md-6 col-xxl-4">Adressen</dt>
                                              <div class="col-md-6 col-xxl-4">
                                                <dd class="  m-0"  v-for="element in wert">
                                                  {{element.strasse}} <b>({{element.adr_typ}})</b><br/>{{ element.plz}} {{element.ort}}
                                                </dd>
                                              </div>
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
                            <core-filter-cmpt title="Funktionen"  ref="funktionenTable" :tabulator-options="funktionen_table_options" :tableOnly />
                            
                            </div>
                            <div class="col-12">
                            <core-filter-cmpt title="Entlehnte Betriebsmittel"  ref="betriebsmittelTable" :tabulator-options="betriebsmittel_table_options" :tableOnly />
                            </div>
                        
                        </div>

                
                    </div>

                    <div  class="col-md-12 col-lg-3 ">
                        <div style="background-color:#EEEEEE" class="row md-invisible py-4">
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
