import {CoreNavigationCmpt} from '../components/navigation/Navigation.js';
import CoreDashboard from '../components/Dashboard/Dashboard.js';

Vue.createApp({
  data: () => ({
      appSideMenuEntries: {}
    }),
  components: {
    CoreNavigationCmpt,
    CoreDashboard
  }
}).mount('#main');
