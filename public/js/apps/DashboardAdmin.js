import {CoreNavigationCmpt} from '../components/navigation/Navigation.js';
import DashboardAdmin from '../components/Dashboard/Admin.js';

Vue.createApp({
  data: () => ({
      appSideMenuEntries: {}
    }),
  components: {
    CoreNavigationCmpt,
    DashboardAdmin
  },
  mounted() {
  }
}).mount('#main');
