import fhcapifactory from "../../../apps/api/fhcapifactory.js";


export default {
 
  data() {
    return {
   

      
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
        },
      
        
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
       
        emails: this.data.emails,
      
      };
    },
  },

  mounted() {
   

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
                      
                    </div>



                        
                      
                        <div class="col m-4">
                    
                    
                            
                        
                            
                            <div class="row">
                              <div :class="{'col-lg-12':true, 'col-xl-6':true, 'order-1':bezeichnung=='Allgemein', 'order-3':bezeichnung=='SpecialInformation'}" v-for="(wert,bezeichnung) in personData">
                                                              
                              
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
