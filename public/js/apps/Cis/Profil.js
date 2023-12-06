//import ProfilView from "../../components/Cis/Profil/Profil.js";
import StudentProfil from "../../components/Cis/Profil/StudentProfil.js";
import MitarbeiterProfil from "../../components/Cis/Profil/MitarbeiterProfil.js";
import ViewStudentProfil from "../../components/Cis/Profil/StudentViewProfil.js";
import ViewMitarbeiterProfil from "../../components/Cis/Profil/MitarbeiterViewProfil.js";
import fhcapifactory from "../api/fhcapifactory.js";

Vue.$fhcapi = fhcapifactory;


const app = Vue.createApp({
	
	components: {
		
		StudentProfil,
		MitarbeiterProfil,
		ViewStudentProfil,
		ViewMitarbeiterProfil,
	},
	data() {
		return {
			view:null,
			data:null,
			
		}
	},
	methods: {
        
	},
	created(){

		let path = location.pathname;
		
		let uid = path.substring(path.lastIndexOf('/')).replace("/","");
		
		/* const payload = {
			...(uid != "Profil" ? {uid} : {})
		};
 */
		Vue.$fhcapi.UserData.getView(uid).then((res)=>{
			this.view = res.data.view;
			this.data = res.data.data;
		});
	},
	template:`
	<div>
	
	
	<component :is="view" :data="data" ></component>
	</div>`
	
	
});
app.mount("#content");