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
    this.$fhcApi.factory.profilUpdate.getStatus()
      .then((response) => {
        this.profilUpdateStates = response.data;
      })
      .catch((error) => {
        console.error(error);
      });
  },
});
app.use(Phrasen).mount("#content");
