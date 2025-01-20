import {CoreNavigationCmpt} from '../components/navigation/Navigation.js';
import DashboardAdmin from '../components/Dashboard/Admin.js';

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
}).mount('#main');