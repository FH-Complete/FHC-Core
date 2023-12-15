//import ProfilView from "../../components/Cis/Profil/Profil.js";
import StudentProfil from "../../components/Cis/Profil/StudentProfil.js";
import MitarbeiterProfil from "../../components/Cis/Profil/MitarbeiterProfil.js";
import ViewStudentProfil from "../../components/Cis/Profil/StudentViewProfil.js";
import ViewMitarbeiterProfil from "../../components/Cis/Profil/MitarbeiterViewProfil.js";
import Base from "../../components/Cis/Profil/Base.js";
import fhcapifactory from "../api/fhcapifactory.js";

Vue.$fhcapi = fhcapifactory;
Vue.$collapseFormatter  = function(data){
	//data - an array of objects containing the column title and value for each cell
	var container = document.createElement("div");
	container.classList.add("tabulator-collapsed-row");
	container.classList.add("text-break");
  
	var list = document.createElement("div");
	list.classList.add("row");
	
	
	container.appendChild(list);
  
	data.forEach(function(col){
		let item = document.createElement("div");
		item.classList.add("col-6");
		let item2 = document.createElement("div");
		item2.classList.add("col-6");
		
		item.innerHTML = "<strong>" + col.title + "</strong>";
		item2.innerHTML = col.value?col.value:"-";
		
		list.appendChild(item);
		list.appendChild(item2);
	});
  
	return Object.keys(data).length ? container : "";
  };

const app = Vue.createApp({
	
	components: {
		Base,
		StudentProfil,
		MitarbeiterProfil,
		ViewStudentProfil,
		ViewMitarbeiterProfil,
	},
	data() {
		return {
			view:null,
			data:null,
			// notfound is null by default, but contains an UID if no user exists with that UID
			notFoundUID:null,
			
		}
	},
	methods: {
        
	},
	created(){

		let path = location.pathname;
		
		let uid = path.substring(path.lastIndexOf('/')).replace("/","");

		Vue.$fhcapi.UserData.getView(uid).then((res)=>{
			if(!res.data){
				this.notFoundUID=uid;
			}
			this.view = res.data?.view;
			this.data = res.data?.data;
			//* only for testing purposes and needs to be deleted after
			this.data.base = "Base";
		});
	},
	template:`
	<div>

	<div v-if="notFoundUID">
	
	<h3>Es wurden keine oder mehrere Profile für {{this.notFoundUID}} gefunden</h3>
	</div>
	<component v-else :is="data.base" :data="data" ></component>
	</div>`
	
	
});
app.mount("#content");