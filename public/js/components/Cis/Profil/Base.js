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
                <div  class="row">
                    <div class="col-12"> 
                  
                    <div class="row">
                        <div  class="d-md-none col-12 ">
                        <!-- DROP DOWN LINKS -->




                        <div style="background-color:#EEEEEE;" class="row py-4">
                        <div class="col">
                            <div class="dropdown">
                            <button style="width:100%" class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu2" data-bs-toggle="dropdown" aria-expanded="false">
                                Quick links
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenu2">
                                <li><button class="dropdown-item" type="button">Action</button></li>
                                <li><button class="dropdown-item" type="button">Another action</button></li>
                                <li><button class="dropdown-item" type="button">Something else here</button></li>
                            </ul>
                            </div>
                        </div>
                        </div>


                        
                        
                        </div>
                        <div class="col-sm-12 col-md-9">
                        <div class="row">
                        
                        <div class="col-md-3 col-sm-12" style="background-color:lightgreen;">
                        <img class=" img-thumbnail "  :src="get_image_base64_src"></img>
                        </div>
                        <div class="col-md-9 col-sm-12" style="background-color:lightcoral;">
                        



                        <div class="row">
                        <!-- HERE COMES THE INFORMATION -->
                        <div style="background-color:red" class="col-md-12 col-lg-6">



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


                        </div>



                        <div style="background-color:orange" class="col-lg-6 col-md-12">

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
                        </div>


                        

                        </div>

                        </div>
                        </div>
                        <div class="row">

                        <!-- HIER SIND DIE TABELLEN -->
                        <div class="col-12" style="background-color:purple">
                        <core-filter-cmpt title="Funktionen"  ref="funktionenTable" :tabulator-options="funktionen_table_options" :tableOnly />
                        
                        </div>
                        <div class="col-12" style="background-color:orange">
                        <core-filter-cmpt title="Entlehnte Betriebsmittel"  ref="betriebsmittelTable" :tabulator-options="betriebsmittel_table_options" :tableOnly />
                        </div>


                    </div>

                        
                        </div>

                        <div class="col-md-3 col-sm-12">



                        <div style="background-color:#EEEEEE;height:200px" class="row d-none d-md-block">
                        <div class="col">
                           <div style="background-color:#EEEEEE" class="row py-4">
                               <a style="text-decoration:none" class="my-1" href="#">Zeitwuensche</a>
                               <a style="text-decoration:none" class="my-1" href="#">Lehrveranstaltungen</a>
                               <a style="text-decoration:none" class="my-1" href="#">Zeitsperren von Gschnell</a>
                           </div>
                        </div>
                        </div>
                        <div style="background-color: darkgray"  class="  row">
                        <div class="col">
                        
                           <!-- HIER SIND DIE MAILVERTEILER -->
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
                       
                         
                    </div>



                    <!-- HIER IST DIE RECHTE SEITE MIT DEN LINKS UND DEN MAILVERTEILERN -->
                    
                </div>
                
        
            </div>
            
    `,
};
