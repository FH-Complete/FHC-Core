import ProfilUpdateView from "../../components/Cis/ProfilUpdate/ProfilUpdateView.js";
import ApiProfilUpdate from '../../api/factory/profilUpdate.js';
import FhcBase from "../../plugins/FhcBase/FhcBase.js";

// TODO: sobald in verwendung den vue router pfad zu ProfilUpdateView definieren und diese app in component auslagern
const app = Vue.createApp({
  name: 'ProfilUpdateRequestsApp',
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
    this.$api
      .call(ApiProfilUpdate.getStatus())
      .then((response) => {
        this.profilUpdateStates = response.data;
      })
      .catch((error) => {
        console.error(error);
      });
  },
});
app.use(FhcBase).mount("#content");