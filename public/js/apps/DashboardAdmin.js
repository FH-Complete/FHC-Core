import {CoreNavigationCmpt} from '../components/navigation/Navigation.js';
import DashboardAdmin from '../components/Dashboard/Admin.js';
import FhcBase from "../plugins/FhcBase/FhcBase.js";

Vue.createApp({
  name: 'DashboardAdminApp',
  data: () => ({
      appSideMenuEntries: {}
    }),
  components: {
    CoreNavigationCmpt,
    DashboardAdmin
  },
  mounted() {
  }
}).use(FhcBase).mount('#main');