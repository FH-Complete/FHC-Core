
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
            <code style="color:purple">{{JSON.stringify(cis_profil_info)}}</code>
            <!--<code style="color:purple">{{JSON.stringify(person_info)}}</code>-->
            <br/>
            <code style="color:red">{{JSON.stringify(person)}}</code>
            <br/>
            <code>{{JSON.stringify(role)}}</code>
            </div>
    `,
};