
import fhcapifactory from "../../../apps/api/fhcapifactory.js";


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
        
    },
    computed:{
        computed_placeholder(){
            return {
                //
            };
        },
        
    },
    created(){
    //error //! fhcapifactory.UserData.getUser().then(res => this.person = res.data);
    fhcapifactory.UserData.isMitarbeiterOrStudent(this.uid).then(res => this.role = res.data);
    fhcapifactory.UserData.getMitarbeiterAnsicht().then(res => {this.person_info = res.data;});
    
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
            
            <pre style="color:blue">{{JSON.stringify(cis_profil_info,null,2)}}</pre>
            <pre style="color:purple">{{JSON.stringify(cis_profil_info_no_foto,null,2)}}</pre>
            
            <br/>
            <pre style="color:red">{{JSON.stringify(person)}}</pre>
            -->
            
            
            <p>test</p>
            </div>
    `,
};