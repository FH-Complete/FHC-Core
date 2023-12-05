import fhcapifactory from "../../../apps/api/fhcapifactory.js";
import { CoreFilterCmpt } from "../../../components/filter/Filter.js";

//? possible types of roles:
//! depending on the role of the current view, different content is being displayed and fetched
//* Student
//* Mitarbeiter
//* View_Student
//* View_Mitarbeiter

export default {
  components: {
    CoreFilterCmpt,
  },
  data() {
    return {
      index_information: null,
      mitarbeiter_info: null,
      student_info: null,
      //? beinhaltet die Information ob der angefragte user ein Student oder Mitarbeiter ist
      role: null,

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

  //? this props were passed in the Profil.php view file
  props: ["data"],
  methods: {
    concatenate_addresses(address_array) {
      let result = "";
      for (let i = 0; i < address_array.length; i++) {
        result +=
          address_array[i].strasse +
          " " +
          address_array[i].plz +
          " " +
          address_array[i].ort +
          "\n";
      }
      return result;
    },
    render_unterelement(wert, bezeichnung) {
      if (isArray(bezeichnung)) {
      }
    },
    concatenate_kontakte(kontakt_array) {
      let result = "";
      for (let i = 0; i < kontakt_array.length; i++) {
        result +=
          kontakt_array[i].kontakttyp +
          " " +
          kontakt_array[i].kontakt +
          " " +
          kontakt_array[i].zustellung +
          "\n";
      }
      return result;
    },
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
    get_image_base64_src() {
      if (!this.data) {
        return "";
      }
      return "data:image/jpeg;base64," + this.data.foto;
    },
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
        },
        GeburtsDaten: {
          Geburtsdatum: this.data.gebdatum,
          Geburtsort: this.data.gebort,
        },
        Adressen: this.data.adressen,
        SpecialInformation:  {
            Kurzzeichen: this.data.kurzbz,
            Telefon: this.data.telefonklappe,
            Büro:this.data.ort_kurzbz,
        },
      };
    },
    //? this computed conains all the information that is used for the second column that displays the information of the person
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

    this.$refs.funktionenTable.tabulator.on('tableBuilt', () => {
    
        this.$refs.funktionenTable.tabulator.setData(this.data.funktionen);
        
    }) 

  },

  template: `


            <div class="container-fluid">
            <!-- here starts the row of the whole window -->
                <div class="row">
                <!-- this is the left column of the window -->
                    <div class="col-9">
                        <div class="row">
                            <div class="col">
                                <img class="img-thumbnail" :src="get_image_base64_src"></img>
                                
                                <div v-if="data.foto_sperre ">
                                    <p style="margin:0">Profilfoto gesperrt</p>
                                    <a href="#" @click.prevent="sperre_foto_function(false)" style="text-decoration:none">Sperre des Profilfotos aufheben</a>
                                </div>
                                <a href="#" @click.prevent="sperre_foto_function(true)" style="display:block; text-decoration:none"  v-else>Profilfoto sperren</a>
                            </div>
                            
                          
                            <div class="col">
                        
                        
                                <h3 >Mitarbeiter</h3>
                                
                                <div v-for="(wert,bezeichnung) in personData">
                                
                                <div class="mb-3"  v-if="typeof wert == 'object' && bezeichnung=='Adressen'"><span style="display:block" v-for="element in wert">{{element.strasse}} <b>({{element.adr_typ}})</b><br/>{{ element.plz}} {{element.ort}}</span></div>
                                <div v-else class="mb-3" ><span style="display:block;" v-for="(val,bez) in wert">{{bez}}: {{val}}</span></div>
                                
                                </div>
                                
                            </div>
                            <div class="col">
                                <ol style="list-style:none">
                                
                                    <li v-for="(wert,bezeichnung) in kontaktInfo">
                                    
                                    <!-- HIER IST DAS DATUM DES FH AUSWEIS -->
                                        <div class="mb-3" v-if="bezeichnung=='FhAusweisStatus'">
                                            <p class="mb-0"><b>FH-Ausweis Status</b></p>
                                            <p class="mb-0">{{"Der FH Ausweis ist am "+ wert+ " ausgegeben worden."}}</p>
                                        </div>

                                    <!-- HIER SIND DIE EMAILS -->
                            

                                        <div class="mb-3" v-if="typeof wert === 'object' && bezeichnung == 'emails'">
                                            <p class="mb-0"><b>eMail</b></p>
                                            <p v-for="email in wert" class="mb-0">{{email.type}}: <a style="text-decoration:none" :href="'mailto:'+email.email">{{email.email}}</a></p>
                                        </div>

                                        <!-- HIER SIND DIE PRIVATEN KONTAKTE -->
                                        <div class="mb-3" v-if="typeof wert === 'object' && bezeichnung=='Kontakte'">
                                        <p class="mb-0"><b>Private Kontakte</b></p>
                                            <div class="row" v-for="element in wert" >
                                                <div class="col-8">{{element.kontakttyp + ":  " + element.kontakt+"  " }}</div>
                                                <div class="col-2"> {{element?.anmerkung}}</div>
                                                <div class="col-2"> 
                                                <i v-if="element.zustellung" class="fa-solid fa-check"></i>
                                                <i v-else="element.zustellung" class="fa-solid fa-xmark"></i>
                                                </div>
                                            </div>
                                        </div>
                                    
                                    </li>
                                </ol>


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
