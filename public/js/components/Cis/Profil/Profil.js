
import fhcapifactory from "../../../apps/api/fhcapifactory.js";
//"<ul><li v-for='element in wert'>{{element}}</li></ul>"

export default {
    
    data() {
        return {
           
            person_info: null,
            //? beinhaltet die Information ob der angefragte user ein Student oder Mitarbeiter ist
            role: null,
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
            fhcapifactory.UserData.sperre_foto_function(value).then(res => {
                console.log(res.data);
                if(res.data){
                    
                    this.person_info.foto_sperre = res.data.foto_sperre;
                }
            });
            
        },
        
    },
    computed:{
        
        get_image_base64_src(){
            return "data:image/jpeg;base64,"+this.person_info.foto;
        },
        first_col(){
            //! postnomen is still missing
           return {
                Username:this.uid,
                Anrede:this.person_info.anrede,
                Titel:(this.person_info.titelpre&&this.person_info.titelpost)?this.person_info.titelpre.concat(this.person_info.titelpost):"null",
                Vorname:this.person_info.vorname,
                Nachname:this.person_info.nachname,
                Postnomen:null,
                Geburtsdatum:this.person_info.gebdatum,
                Geburtsort: this.person_info.gebort,
                Adresse: this.person_info.adressen,
                Kurzzeichen: this.person_info.kurzbz,
                Telefon: this.person_info.telefonklappe,
            };
        },
        second_col(){
            //! postnomen is still missing
           return {
                Intern:this.person_info.email_intern,
                Alias:this.person_info.email_extern,
                Kontakte:this.person_info.kontakte,
            };
        },

        
        
        
    },
    created(){
    //error //! fhcapifactory.UserData.getUser().then(res => this.person = res.data);
    fhcapifactory.UserData.isMitarbeiterOrStudent(this.uid).then(res => {console.log(res.data);this.role = res.data;});
    fhcapifactory.UserData.getMitarbeiterAnsicht().then(res => {this.person_info = res.data;});
    
    },
     
    template: `
    
    <p v-for="element in person_info.funktionen"><span v-for="(wert,bezeichnung) in element">{{wert + ": " + bezeichnung}}</span></p>
    <p>{{JSON.stringify(first_col)}}</p>
    <p>{{"here is the uid "+uid}} </p>
    <p>{{"here is the pid "+pid}} </p>
            <div :class={'container':true}>
            <div :class={'row':true}>
            <div :class={'col':true}>
            <img :src="get_image_base64_src"></img>
            <div v-if="person_info.foto_sperre">
            <p style="margin:0">Profilfoto gesperrt</p>
            <a href="#" @click.prevent="sperre_foto_function(false)">Sperre des Profilfotos aufheben</a>
            </div>
            <a href="#" @click.prevent="sperre_foto_function(true)" style="display:block"  v-else>Profilfoto sperren</a>
            
            
            </div>
            <div :class={'col':true}>
            
           
            <ol style="list-style:none">
            <li  v-for="(wert,bezeichnung) in first_col">
            
            <p v-for="element in wert" v-if="typeof wert == 'object' && bezeichnung=='Adresse'">{{element.strasse +" "+element.adr_typ+" " + element.plz+" "+element.ort}}</p>
           
            <p v-else>{{bezeichnung +": " +wert}}</p>
            </li>
            </ol>
            </div>
            <div :class={'col':true}>
            <ol style="list-style:none">
            <!--render_unterelement(wert,bezeichnung)-->
            <li v-for="(wert,bezeichnung) in second_col">
            
            <p v-for="element in wert" v-if="typeof wert === 'object' && bezeichnung=='Kontakte'">
            {{element.kontakttyp + "  " + element.kontakt+"  " }}
            <i v-if="element.zustellung" class="fa-solid fa-check"></i>
            <i v-else="element.zustellung" class="fa-solid fa-xmark"></i>
            
            </p>
            
            <p v-else >{{bezeichnung +": "+wert}}</p>
            
            </li>
            </ol>


            </div>
            </div>
            </div>
    `,
};