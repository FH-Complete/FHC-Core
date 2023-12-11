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


  <!-- CONTAINER -->
  <div class="container-fluid" style="overflow-wrap:break-word">
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






               




                    <!-- END OF THE COLUMN WITH PROFIL IMAGE AND LINK -->
                    </div>



                    <!-- COLUMN WITH THE PROFIL INFORMATION --> 
                    <div class="col-md-10 col-sm-12" style="border:4px solid;border-color:lightcoral;">
              

                    <!-- INFORMATION CONTENT START -->
                    <!-- ROW WITH THE PROFIL INFORMATION --> 
                    <div class="row">



                      <!-- FIRST COLUMN WITH PROFIL INFORMATION -->
                      <div style="border:4px solid;border-color:red" class="col-lg-12 col-xl-6">

                        <dl class="  mb-0"  >

                          <!-- STUDENTEN TITEL -->
                          <div class="row mb-2">
                              <dt class="col-12 " ><b>StudentIn</b></dt>
                          </div>

                          <div v-for="(wert,bez) in personData.Allgemein" class="row">
                              <dt class="col-lg-4 col-6  " >{{bez}}</dt>
                              <dd class=" col-lg-8 col-6 ">{{wert?wert:"-"}}</dd>
                          </div>
                      
                  
                        </dl>



                      <!-- END OF THE FIRST INFORMATION COLUMN -->
                      </div>


                      <!-- START OF THE SECOND PROFIL INFORMATION COLUMN -->
                      <div style="border:4px solid;border-color:orange" class="col-xl-6 col-lg-12">






                        <dl v-for="(wert,bezeichnung) in kontaktInfo">

                        <!-- HIER SIND DIE EMAILS -->
                    
                    
                          <div class="justify-content-center row mb-3" v-if="typeof wert === 'object' && bezeichnung == 'emails'">
                              <dt class="col-4  mb-0">eMail</dt>
                              <div class="col-8 ">
                                  <dd v-for="email in wert" class="mb-0 ">{{email.type}}: <a style="text-decoration:none" :href="'mailto:'+email.email">{{email.email}}</a></dd>
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



            



              <!-- END OF MAIN CONTENT COL -->
              </div>




              <!-- START OF SIDE PANEL -->
              <div  class="col-md-3 col-sm-12">


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
