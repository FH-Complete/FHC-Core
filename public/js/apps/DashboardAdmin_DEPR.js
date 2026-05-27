import {CoreNavigationCmpt} from '../components/navigation/Navigation.js';
import DashboardAdmin from '../components/Dashboard/Admin.js';
import Phrases from "../plugin/Phrasen.js"

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
}).use(Phrases).mount('#main');