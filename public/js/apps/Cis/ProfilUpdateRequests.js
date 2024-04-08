import fhcapifactory from "../api/fhcapifactory.js";
import ProfilUpdateView from "../../components/Cis/ProfilUpdate/ProfilUpdateView.js";
import Phrasen from "../../plugin/Phrasen.js";
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
app.use(Phrasen).mount("#content");

