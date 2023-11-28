
import fhcapifactory from "../../../apps/api/fhcapifactory.js";
import {CoreFilterCmpt} from "../../../components/filter/Filter.js"


/* [
    {title: 'Log ID', field: 'LogId', headerFilter: true},
    {title: 'Request ID', field: 'RequestId', headerFilter: true},
    {title: 'Execution time', field: 'ExecutionTime', headerFilter: true},
    {title: 'Executed by', field: 'ExecutedBy', headerFilter: true},
    {title: 'Description', field: 'Description', headerFilter: true},
    {title: 'Data', field: 'Data', headerFilter: true},
    {title: 'Web service type', field: 'WebserviceType', headerFilter: true}
] */

//? old data
/* ajaxUrl: FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router+
                "/Cis/Profil/getBenutzerFunktionen", */

export default {
    components:{
        CoreFilterCmpt,
    },
    data() {
        return {
            index_information: null,
            mitarbeiter_info: null,
            student_info:null,
            //? beinhaltet die Information ob der angefragte user ein Student oder Mitarbeiter ist
            role: null,
            //"bf_bezeichnung", "oe_bezeichnung", "datum_von", "datum_bis", "wochenstunden" ]

            funktionen_table_options: {
                height: 300,
                layout: 'fitColumns',
                data:[{Bezeichnung:"test1",Organisationseinheit:"test2",Gültig_von:"test3",Gültig_bis:"test4",Wochenstunden:"test5"}],
                columns: [{title: 'Bezeichnung', field: 'Bezeichnung', headerFilter: true},
                {title: 'Organisationseinheit', field: 'Organisationseinheit', headerFilter: true},
                {title: 'Gültig_von', field: 'Gültig_von', headerFilter: true},
                {title: 'Gültig_bis', field: 'Gültig_bis', headerFilter: true},
                {title: 'Wochenstunden', field: 'Wochenstunden', headerFilter: true},]
                
            },
            betriebsmittel_table_options:{
                height: 300,
                layout: 'fitColumns',
                data:[{betriebsmittel:"test1",Nummer:"test2",Ausgegeben_am:"test3"}],
                columns: [{title: 'Betriebsmittel', field: 'betriebsmittel', headerFilter: true},
                {title: 'Nummer', field: 'Nummer', headerFilter: true},
                {title: 'Ausgegeben_am', field: 'Ausgegeben_am', headerFilter: true},]
                
            },
            zutrittsgruppen_table_options:{
                height: 300,
                layout: 'fitColumns',
                data:[{bezeichnung:"test1"}],
                columns: [{title: 'Zutritt', field: 'bezeichnung'}]
            }
        }
    },
    
    //? this props were passed in the Profil.php view file
    props:['uid','pid'],
    methods: {
        
        concatenate_addresses(address_array){
            let result = "";
            for (let i = 0; i < address_array.length; i++) {
                result += address_array[i].strasse + " " + address_array[i].plz + " " + address_array[i].ort + "\n";
            }
            return result;
        },
        render_unterelement(wert,bezeichnung){
            if (isArray(bezeichnung)){
                
            }
        },
        concatenate_kontakte(kontakt_array){
            let result = "";
            for (let i = 0; i < kontakt_array.length; i++) {
                result += kontakt_array[i].kontakttyp + " " + kontakt_array[i].kontakt + " " + kontakt_array[i].zustellung + "\n";
            }
            return result;
        },
        sperre_foto_function(value){
            if(!(this.mitarbeiter_info && this.index_information && this.student_info) ){
                return;
            }
            fhcapifactory.UserData.sperre_foto_function(value).then(res => {
                    
                    this.index_information.foto_sperre = res.data.foto_sperre;
                
            });
            
        },
        
        
    },
    computed:{
     
        
        get_image_base64_src(){
            if(!this.index_information){
                return "";
            }
            return "data:image/jpeg;base64,"+this.index_information.foto;
        },
        personData(){
            if(!this.index_information){
                return {};
            }
  
           return {
                Allgemein: this.role =='Mitarbeiter'?{
                    Username:this.index_information.username,
                    Anrede:this.index_information.anrede,
                    Titel:this.index_information.titel,
                    Vorname:this.index_information.vorname,
                    Nachname:this.index_information.nachname,
                    Postnomen:null,
            }:{
                Username:this.index_information.username,
                Matrikelnummer: this.student_info?.matrikelnummer,
                Anrede:this.index_information.anrede,
                Titel:this.index_information.titel,
                Vorname:this.index_information.vorname,
                Nachname:this.index_information.nachname,
                Postnomen:null,
            },
            GeburtsDaten:{
                Geburtsdatum:this.index_information.gebdatum,
                Geburtsort: this.index_information.gebort,
            },
                Adressen: this.index_information.adressen,
            SpecialInformation: this.role =='Mitarbeiter'?  {
                Kurzzeichen: this.mitarbeiter_info?.kurzbz,
                Telefon: this.mitarbeiter_info?.telefonklappe,
            } : {
                Studiengang:this.student_info?.studiengang,
                Semester:this.student_info?.semester,
                Verband:this.student_info?.verband,
                Gruppe:this.student_info?.gruppe,
                Personenkennzeichen:this.student_info?.personenkennzeichen
            },
            };
        },
        //? this computed conains all the information that is used for the second column that displays the information of the person
        kontaktInfo(){
            if(!this.index_information){
                return {};
            }
         
           return {
                FhAusweisStatus: this.index_information.zutrittsdatum,
                emails: this.role === 'Mitarbeiter'?  this.mitarbeiter_info?.emails: this.index_information.emails,
                Kontakte:this.index_information.kontakte,
            };
        },
        
    },
    
    created(){
        
   
        
        
        
        
    },
     mounted(){

        console.log(this.uid);
        console.log(this.pid);

        //? this function is to update the tabulator information only when the tabulator was build checking the tableBulit event
        //! only the tableBuilt event of the second tabulator was used to update the table informations
         this.$refs.betriebsmittelTable.tabulator.on('tableBuilt', () => {
           
            fhcapifactory.UserData.isMitarbeiterOrStudent(this.uid).then((res) => {
                this.role = res.data;
            

                //? Die anderen api calls werden erst gemacht wenn der call zu isMitarbeiterOrStudent gemacht worden ist


                //! indexProfilInformationen werden immer gefetcht
                fhcapifactory.UserData.indexProfilInformaion().then((res) => {
                    this.index_information = res.data;
                    this.$refs.betriebsmittelTable.tabulator.setData(res.data.mittel);
                });
                

                //? Danach werden die Informationen der Role gefetcht
                if(this.role === 'Student'){ 
                    fhcapifactory.UserData.studentProfil().then((res)=> {
                        this.student_info = res.data;
                        this.$refs.zutrittsgruppenTable.tabulator.setData(res.data.zuttritsgruppen);
                    })
                }
                
                if(this.role === 'Mitarbeiter'){
                    fhcapifactory.UserData.mitarbeiterProfil().then((res)=> {
                        this.mitarbeiter_info = res.data;
                        this.$refs.funktionenTable.tabulator.setData(res.data.funktionen);
                    })
                }   
            });
            

        }) 

        
        
    },
     
    template: `
   
    <div :class="{'container':true}">
    <div :class="{'row':true}">
    <div :class="{'col':true}">
    
    </div>
    <div :class="{'col':true}">
    
    </div>
    </div>
    </div>

            <div :class="{'container-fluid':true}">
            <!-- here starts the row of the whole window -->
            <div :class="{'row':true}">
            <!-- this is the left column of the window -->
            <div :class="{'col-9':true}">
            <div :class="{'row':true}">
            <div :class="{'col':true}">
            <img :class="{'img-thumbnail':true}" :src="get_image_base64_src"></img>
            <div v-if="index_information?.foto_sperre">
            <p style="margin:0">Profilfoto gesperrt</p>
            <a href="#" @click.prevent="sperre_foto_function(false)" style="text-decoration:none">Sperre des Profilfotos aufheben</a>
            </div>
            <a href="#" @click.prevent="sperre_foto_function(true)" style="display:block; text-decoration:none"  v-else>Profilfoto sperren</a>
            
            
            </div>
            <div :class="{'col':true}">
           
            <h3 v-if="role=='Mitarbeiter'">Mitarbeiter</h3>
            <h3 v-else >Student</h3>
            
            <div v-for="(wert,bezeichnung) in personData">
            
            <div class="mb-3"  v-if="typeof wert == 'object' && bezeichnung=='Adressen'"><span style="display:block" v-for="element in wert">{{element.strasse}} <b>({{element.adr_typ}})</b><br/>{{ element.plz}} {{element.ort}}</span></div>
            <div v-else class="mb-3" ><span style="display:block;" v-for="(val,bez) in wert">{{bez}}: {{val}}</span></div>
            
            </div>
            
            </div>
            <div :class="{'col':true}">
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
            
            <!--<pre v-else>{{JSON.stringify(wert,null,2)}}</pre>-->
            
            </li>
            </ol>


            </div>
            </div>
            
            <div :class="{'row':true}">
            
            <!-- order-1 classe wird nur bei der Studentenansicht hinzugefügt um die Zutrittsgruppen Tabelle hinter der Betriebsmittel aufzureihen -->
            <div :class="{'col-12':true, 'order-2':role==='Student'}">
          
            <core-filter-cmpt v-if="role === 'Mitarbeiter'" title="Funktionen"  ref="funktionenTable" :tabulator-options="funktionen_table_options" :tableOnly />
            <core-filter-cmpt v-else title="Zutrittsgruppen" ref="zutrittsgruppenTable" :tabulator-options="zutrittsgruppen_table_options" :tableOnly :noColFilter />
            
            </div>
            <div :class="{'col-12':true}">
               
            <core-filter-cmpt title="Entlehnte Betriebsmittel"  ref="betriebsmittelTable" :tabulator-options="betriebsmittel_table_options" :tableOnly />
       
            </div>
            
            </div>

           
            </div>

            <div  :class="{'col-3':true}">
            <div style="background-color:#EEEEEE" :class="{'row':true, 'py-4':true}">
            <a style="text-decoration:none" :class="{'my-1':true}" href="#">Zeitwuensche</a>
            <a style="text-decoration:none" :class="{'my-1':true}" href="#">Lehrveranstaltungen</a>
            <a style="text-decoration:none" :class="{'my-1':true}" href="#">Zeitsperren von Gschnell</a>
            </div>
            <div :class="{'row':true}">
            <h5 :class="{'fs-3':true}" style="margin-top:1em">Mailverteilers</h5>
            <p :class="{'fs-6':true}">Sie sind Mitgglied in folgenden Verteilern:</p>
            <div  :class="{'row':true, 'text-break':true}" v-for="verteiler in index_information?.mailverteiler">
            <div :class="{'col-6':true}"><a :href="verteiler.mailto"><b>{{verteiler.gruppe_kurzbz}}</b></a></div> 
            <div :class="{'col-6':true}">{{verteiler.beschreibung}}</div>
            </div>
            </div>
            </div>
            </div>
            </div>
    `,
};