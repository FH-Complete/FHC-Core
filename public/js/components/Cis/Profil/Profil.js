
import fhcapifactory from "../../../apps/api/fhcapifactory.js";
export default {
   
    data: function() {
        return {
            person: null,
            person_info: null,
            role: null,
        }
    },
    //? this prop was passed in the Profil.php view file
    props:['uid','pid'],
    methods: {
        
    },
    computed:{
        cis_profil_info(){
            return {
                anrede:this.person_info.anrede,
                titelpre:this.person_info.titelpre,
                titelpost:this.person_info.titelpost,
                vorname:this.person_info.vorname,
                nachname:this.person_info.nachname,
                gebdatum:this.person_info.gebdatum,
                gebort:this.person_info.gebort,
                adresse:this.person_info.adressen[0].strasse + " " + this.person_info.adressen[0].plz,
               
            };
        },
        cis_profil_info_no_foto(){
            return {
                ...this.person_info,
                foto:null, 
               
            };
        }
    },
    created(){
    fhcapifactory.UserData.getUser().then(res => this.person = res.data);
    fhcapifactory.UserData.isMitarbeiterOrStudent(this.uid).then(res => this.role = res.data);
    fhcapifactory.UserData.getPersonInformation(this.pid).then(res => this.person_info = res.data);
    
    },
     
    template: `
            <div>
            <h1>test</h1>
            <p>{{"here is the uid "+uid}} </p>
            <p>{{"here is the pid "+pid}} </p>
            <!--
            //! printing 2 computed functions
            //* one to output the collected need information for the cis page
            //* and the other returns all the information retrieved from the model without the foto data
            -->
            <pre style="color:blue">{{JSON.stringify(cis_profil_info,null,2)}}</pre>
            <pre style="color:purple">{{JSON.stringify(cis_profil_info_no_foto,null,2)}}</pre>
            
            <br/>
            <pre style="color:red">{{JSON.stringify(person)}}</pre>
            <br/>
            <pre>{{JSON.stringify(role)}}</pre>
            </div>
    `,
};