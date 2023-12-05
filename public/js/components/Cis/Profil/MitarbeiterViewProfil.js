
import fhcapifactory from "../../../apps/api/fhcapifactory.js";
import {CoreFilterCmpt} from "../../../components/filter/Filter.js"

//? possible types of roles:
//! depending on the role of the current view, different content is being displayed and fetched
//* Student
//* Mitarbeiter
//* View_Student
//* View_Mitarbeiter

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

            funktionen_table_options: {
                height: 300,
                layout: 'fitColumns',
                data:[{Bezeichnung:"",Organisationseinheit:"",Gültig_von:"",Gültig_bis:"",Wochenstunden:""}],
                columns: [{title: 'Bezeichnung', field: 'Bezeichnung', headerFilter: true},
                {title: 'Organisationseinheit', field: 'Organisationseinheit', headerFilter: true},
                {title: 'Gültig_von', field: 'Gültig_von', headerFilter: true},
                {title: 'Gültig_bis', field: 'Gültig_bis', headerFilter: true},
                {title: 'Wochenstunden', field: 'Wochenstunden', headerFilter: true},]
                
            },
            betriebsmittel_table_options:{
                height: 300,
                layout: 'fitColumns',
                data:[{betriebsmittel:"",Nummer:"",Ausgegeben_am:""}],
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
    props:['uid','view'],
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
                Allgemein: (this.role =='Mitarbeiter' || this.role =='View_Mitarbeiter')?{
                    Username:this.index_information.username,
                    Anrede:this.index_information.anrede,
                    Titel:this.index_information.titel,
                    Vorname:this.index_information.vorname,
                    Nachname:this.index_information.nachname,
                    Postnomen:this.index_information.postnomen,
            }:{
                Username:this.index_information.username,
                Matrikelnummer: this.student_info?.matrikelnummer,
                Anrede:this.index_information.anrede,
                Titel:this.index_information.titel,
                Vorname:this.index_information.vorname,
                Nachname:this.index_information.nachname,
                Postnomen:this.index_information.postnomen,
            },
            GeburtsDaten:!this.role.includes("View")?{
                Geburtsdatum:this.index_information.gebdatum,
                Geburtsort: this.index_information.gebort,
            }: null,
                Adressen: this.index_information.adressen,
            SpecialInformation: this.role =='Mitarbeiter' || this.role === 'View_Mitarbeiter'?  {
                Kurzzeichen: this.mitarbeiter_info?.kurzbz,
                Telefon: this.mitarbeiter_info?.telefonklappe,
                //* Wird das Feld Ort_kurzbz noch gepflegt?
                ...(this.role === 'View_Mitarbeiter'?{Büro:this.mitarbeiter_info?.ort_kurzbz}:{}) ,
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
                emails: this.role === 'Mitarbeiter' || this.role === 'View_Mitarbeiter'?  this.mitarbeiter_info?.emails: this.index_information.emails,
                Kontakte:this.index_information.kontakte,
            };
        },
        
    },
  
     mounted(){

        console.log(this.uid);
        console.log(this.view);
        console.log(typeof this.view);
        if(this.view){console.log("view is true")}else{ console.log("view is false")}

        //? this function is to update the tabulator information only when the tabulator was build checking the tableBulit event
        //! only the tableBuilt event of the second tabulator was used to update the table informations
         

        fhcapifactory.UserData.isMitarbeiterOrStudent(this.uid).then((res) => {

            this.role = this.view? "View_"+res.data: res.data;
            if(!this.role.includes('View')){
                this.$refs.betriebsmittelTable.tabulator.on('tableBuilt', () => {
                }) 
             }


        console.log("the role of the current view: ", this.role);
        
    

        //? Die anderen api calls werden erst gemacht wenn der call zu isMitarbeiterOrStudent gemacht worden ist


        //! indexProfilInformationen werden immer gefetcht
        fhcapifactory.UserData.indexProfilInformaion(this.uid,this.view).then((res) => {
            console.log(res.data);
            this.index_information = res.data;
            if(!this.role.includes("View")){
            this.$refs.betriebsmittelTable.tabulator.setData(res.data.mittel);
        }
        });
        

        //? Danach werden die Informationen der Role gefetcht
        if(this.role === "Student" || this.role === "View_Student"){ 
            fhcapifactory.UserData.studentProfil(this.uid,this.view).then((res)=> {
                this.student_info = res.data;
                if(this.role ==="Student"){
                this.$refs.zutrittsgruppenTable.tabulator.setData(res.data.zuttritsgruppen);
                }
            })
        }
        
        if(this.role === "Mitarbeiter" || this.role === "View_Mitarbeiter"){
            fhcapifactory.UserData.mitarbeiterProfil(this.uid).then((res)=> {
                this.mitarbeiter_info = res.data;
                this.$refs.funktionenTable.tabulator.setData(res.data.funktionen);
            })
        }   
    });

        
        
    },
     
    template: `
   
    <p>MitarbeiterViewProfil</p>
    `,
};