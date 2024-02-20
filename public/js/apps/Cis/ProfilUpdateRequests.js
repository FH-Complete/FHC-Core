import fhcapifactory from "../api/fhcapifactory.js";
import ProfilUpdateView from "../../components/Cis/ProfilUpdate/ProfilUpdateView.js";

Vue.$fhcapi = fhcapifactory;



const app = Vue.createApp({
  components: {
    ['profil-update-view']:ProfilUpdateView,
 
  },
 
  data() {
    return {
   
    }
  },methods:{
   
  }
  
});

app.mount("#content");
