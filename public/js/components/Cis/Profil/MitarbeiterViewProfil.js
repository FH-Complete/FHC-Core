import fhcapifactory from "../../../apps/api/fhcapifactory.js";
import { CoreFilterCmpt } from "../../../components/filter/Filter.js";


export default {
  components: {
    CoreFilterCmpt,
  },
  data() {
    return {
      collapseIconFunktionen:true,

      funktionen_table_options: {
        height: 400,
        layout:"fitColumns",
        responsiveLayout:"collapse",
        responsiveLayoutCollapseUseFormatters:false,
        responsiveLayoutCollapseFormatter:Vue.$collapseFormatter,
        
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
         
         {
          title: "<i id='collapseIconFunktionen' role='button' class='fa-solid fa-angle-down  '></i>",
          field: "collapse",
          headerSort: false,
          headerFilter:false,
          formatter:"responsiveCollapse",
          maxWidth:40,
          headerClick:this.collapseFunction,
        }, 
          { title: "Bezeichnung", field: "Bezeichnung", headerFilter: true,minWidth:200, },
          {
            title: "Organisationseinheit",
            field: "Organisationseinheit",
            headerFilter: true,minWidth:200,
          },
          { title: "Gültig_von", field: "Gültig_von", headerFilter: true, resizable:true,minWidth:200, },
          { title: "Gültig_bis", field: "Gültig_bis", headerFilter: true, resizable:true,minWidth:200, },
          {
            title: "Wochenstunden",
            field: "Wochenstunden",
            headerFilter: true,
            minWidth:200,
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
    collapseFunction (e, column){

      //* the if of the column has to match with the name of the responsive data in the vue component
      this[e.target.id] = !this[e.target.id];

      //* gets all event icons of the different rows to use the onClick event later
      let allClickableIcons = column._column.cells.map(row => {
        return row.element.children[0];
      });
      
      //* changes the icon that shows or hides all the collapsed columns
      //* if the replace function does not find the class to replace, it just simply returns false
      if(this[e.target.id]){
        e.target.classList.replace("fa-angle-up","fa-angle-down");
      }else{
        e.target.classList.replace("fa-angle-down","fa-angle-up");
      }
      
      //* changes the icon for every collapsed column to open or closed
        if(this[e.target.id]){
          allClickableIcons.filter(column => {
            return !column.classList.contains("open");
          }).forEach(col => {col.click();})
        }else{
          allClickableIcons.filter(column => {
            return column.classList.contains("open");
          }).forEach(col => {col.click();})
        }
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
          View:"MitarbeiterIn",
          Username: this.data.username,
          Anrede: this.data.anrede,
          Titel: this.data.titel,
          Vorname: this.data.vorname,
          Nachname: this.data.nachname,
          Postnomen: this.data.postnomen,
        },
     
       
        SpecialInformation:  {
            Kurzzeichen: this.data.kurzbz,
            Telefon: this.data.telefonklappe,
            Büro:this.data.ort_kurzbz,
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


    this.$refs.funktionenTable.tabulator.on('tableBuilt', () => {
    
        this.$refs.funktionenTable.tabulator.setData(this.data.funktionen);
        
    }) 

  },

  template: `


  <!-- CONTAINER -->
  <div class="container-fluid text-break" >
    <!-- ROW --> 
          <div class="row">
          <!-- HIDDEN QUICK LINKS -->
              <div  class="d-md-none col-12 ">
             
              <div style="border:4px solid;border-color:#EEEEEE;" class="row py-2">
              <div class="col">
                <p class="m-0">
                  <a class="border w-100 btn " data-bs-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
                   <u> Quick links</u>
                  </a>
                 
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
                    <div class="col-md-10 col-sm-12 text-break" style="border:4px solid;border-color:lightcoral;">
              

                    <!-- INFORMATION CONTENT START -->
                    <!-- ROW WITH THE PROFIL INFORMATION --> 
                    <div class="row">



                      <!-- FIRST COLUMN WITH PROFIL INFORMATION -->
                      <div style="border:4px solid;border-color:red" class="col-lg-12 col-xl-6">

                        <dl class="  mb-0"  >

                        

                          <div v-for="(wert,bez) in personData.Allgemein" class="row justify-content-center">
                          <!-- MITARBEITER TITEL -->
                            <dt class="col-xl-10 col-12 " v-if="bez=='View'" ><b>{{wert}}</b></dt>
                            <template v-else>
                              <dt class="col-xl-4 col-lg-6 col-md-6 col-6  " >{{bez}}</dt>
                              <dd class=" col-lg-8 col-xl-6 col-6 ">{{wert?wert:"-"}}</dd>
                            </template>
                          </div>
                        
                    
                        </dl>



                      <!-- END OF THE FIRST INFORMATION COLUMN -->
                      </div>


                      <!-- START OF THE SECOND PROFIL INFORMATION COLUMN -->
                      <div style="border:4px solid;border-color:orange" class="col-xl-6 col-lg-12">






                        <dl v-for="(wert,bezeichnung) in kontaktInfo">

                        <!-- HIER SIND DIE EMAILS -->
                    
                    
                          <div class="justify-content-center row mb-3" v-if="typeof wert === 'object' && bezeichnung == 'emails'">
                              <dt class="col-lg-4 col-6  mb-0">eMail</dt>
                              <div class="col-lg-8 col-6">
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




                <!-- SECOND ROW UNDER THE PROFIL IMAGE AND INFORMATION WITH THE TABLES -->
                <div class="row">

                <!-- FIRST TABLE -->
                  <div class="col-12" style="border: 4px solid; border-color:lightskyblue">
                    <core-filter-cmpt title="Funktionen"  ref="funktionenTable" :tabulator-options="funktionen_table_options" tableOnly :sideMenu="false" />
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
                    <p >Sie sind Mitgglied in folgenden Verteilern:</p>
                    <div  class="row text-break" v-for="verteiler in data?.mailverteiler">
                      <div class="col-lg-12 col-xl-6"><a :href="verteiler.mailto"><b>{{verteiler.gruppe_kurzbz}}</b></a></div> 
                      <div class="col-lg-12 col-xl-6">{{verteiler.beschreibung}}</div>
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
