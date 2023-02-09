import {CoreNavigationCmpt} from '../components/navigation/Navigation.js';
import CoreDashboard from '../components/Dashboard/Dashboard.js';
import DashboardAdmin from '../components/Dashboard/Admin.js';

Vue.createApp({
  data: () => ({
      appSideMenuEntries: {}
    }),
  components: {
    CoreNavigationCmpt,
    DashboardAdmin,
    CoreDashboard/*,
    "CoreFilterCmpt": CoreFilterCmpt,
    "verticalsplit": verticalsplit,
    "searchbar": searchbar*/
  },
  mounted() {
  }
}).mount('#main');
