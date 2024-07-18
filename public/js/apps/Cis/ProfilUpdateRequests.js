import fhcapifactory from "../api/fhcapifactory.js";
import ProfilUpdateView from "../../components/Cis/ProfilUpdate/ProfilUpdateView.js";
import Phrasen from "../../plugin/Phrasen.js";
Vue.$fhcapi = fhcapifactory;

const app = Vue.createApp({
  components: {
    ["profil-update-view"]: ProfilUpdateView,
  },

  data() {
    return {
      profilUpdateStates: null,
    };
  },
  provide() {
    return {
      profilUpdateStates: Vue.computed(() =>
        this.profilUpdateStates ? this.profilUpdateStates : false
      ),
    };
  },
  methods: {},
  created() {
    console.log("this is the place i am searching for")
    Vue.$fhcapi.ProfilUpdate.getStatus()
      .then((response) => {
        this.profilUpdateStates = response.data;
      })
      .catch((error) => {
        console.error(error);
      });
  },
});
app.use(Phrasen).mount("#content");
