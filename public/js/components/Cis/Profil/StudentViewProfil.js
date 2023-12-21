
export default {
  
  data() {
    return {
      
     
    };
  },

  //? this is the prop passed to the dynamic component with the custom data of the view
  props: ["data"],
  methods: {
    
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
        Personenkennzeichen: this.data.personenkennzeichen,
        Studiengang: this.data.studiengang,
        Semester: this.data.semester,
        Verband: this.data.verband,
        Gruppe: this.data.gruppe.trim(),
      };
    },

    

  
  },

  mounted() {
   

   
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
                      StudentIn
                      </div>
                      <div class="card-body">
                      
                       


                  
                  <div  class="row align-items-center">




                  <!-- SQUEEZING THE IMAGE INSIDE THE FIRST INFORMATION COLUMN -->
 <!-- START OF THE FIRST ROW WITH THE PROFIL IMAGE -->
          <div class="col-12 col-sm-6 mb-2">
           <div class="row justify-content-center">
                        <div class="col-auto " style="position:relative">
                          <img class=" img-thumbnail " style=" max-height:150px; "  :src="get_image_base64_src"></img>
                         
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
                        
                  <input  readonly class="form-control form-control-plaintext border-bottom" id="floatingVorname"  :value="data.vorname">
                  <label for="floatingVorname">Vorname</label>
                </div>
                </div>
                <div class="col-12">
                <div class=" form-floating mb-2">
                        
                  <input  readonly class="form-control form-control-plaintext border-bottom" id="floatingNachname"  :value="data.nachname">
                  <label for="floatingNachname">Nachname</label>
                </div>
                </div>
                </div>
             
                
                  </div>
                  







                  <div v-for="(wert,bez) in personData" class="col-md-6 col-sm-12 ">
                  <div class=" form-floating mb-2">
                        
                  <input  readonly class="form-control form-control-plaintext border-bottom" :id="'floating'+bez"  :value="wert?wert:'-'">
                  <label :for="'floating'+bez">{{bez}}</label>
                  </div>
                  </div>

                  
                      </div>


                      
                      </div>
                    </div>
		    </div>
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
                  
                  
                      <div v-for="email in wert" v-if="typeof wert === 'object' && bezeichnung == 'emails'" class="row justify-content-center ">
                      <div  class="col-12 ">
                     
                            <div class="row align-items-center">
                      <div class="col-1 text-center">

                      <i class="fa-solid fa-envelope" style="color:rgb(0, 100, 156)"></i>

                      </div>
                      <div class="col">
                      <div class=" form-floating mb-2">
                           
                            <a :href="'mailto:'+email.email" class="form-control form-control-plaintext border-bottom" :id="'floating'+email.type">
                              {{email.email}}
                            </a>
                            <div class="floating-title">{{email.type }}</div>

                      </div>
                      </div>
                      </div>
                      </div>
                          </div>

                    
                    
                     




                    
                      </div>


                    </div>
                    </div>
                    </div></div>

                   <!-- SECOND ROW OF SECOND COLUMN IN MAIN CONTENT -->
                    <div class="row mb-4">
                     

                    <div  class=" col-lg-12">
       
                    <div class="card">
                
                        <div class="card-header">
                        Studenten Information
                        </div>
                        <div class="card-body">
                            <div class="row">
                            <div v-for="(wert,bez) in specialData" class="col-md-6 col-sm-12 ">
                            
                           
                            <div  class=" form-floating mb-2">  
                           
                                <input   readonly class="form-control form-control-plaintext border-bottom" :id="'floating'+bez"  :value="wert?wert:'-'">
                                <label :for="'floating'+bez">{{bez}}</label>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    
                </div>

                    </div>  
                           


                     </div>

                     <!-- END OF SECOND ROW OF SECOND COLUMN IN MAIN CONTENT -->
                  



                    <!-- END OF THE SECOND INFORMATION COLUMN -->
                    </div>


                    <!-- START OF THE SECOND PROFIL INFORMATION ROW --> 
                  
                    
                  <!-- ROW WITH PROFIL IMAGE AND INFORMATION END -->
                </div  >



                




        






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
