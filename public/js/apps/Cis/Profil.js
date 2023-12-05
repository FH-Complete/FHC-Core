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
			
		}
	},
	methods: {
        
	},
	created(){

		let path = location.pathname;
		console.log(path);
		console.log(path.lastIndexOf('/'));
		let uid = path.substring(path.lastIndexOf('/')).replace("/","");
		console.log("i am passing this uid: ", uid);
		const payload = {
			...(uid != "Profil" ? {uid} : {})
		};

		Vue.$fhcapi.UserData.getView(payload).then((res)=>{
			//this.view = res.data.view;
			//this.data = res.data.data;
			console.log(res.data);
		});
	},
	template:`
	<div>
	<p>test element</p>
	<!--<StudentProfil></StudentProfil>-->
	<component :is="view"></component>
	</div>`
	
	
});
app.mount("#content");