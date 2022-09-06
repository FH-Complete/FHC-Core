import {CoreNavigationCmpt} from '../components/navigation/Navigation.js';
import CoreDashboard from '../components/CoreDashboard.js';

Vue.createApp({
  data: () => ({
      appSideMenuEntries: {}
    }),
  components: {
    CoreNavigationCmpt,
    CoreDashboard/*,
    "CoreFilterCmpt": CoreFilterCmpt,
    "verticalsplit": verticalsplit,
    "searchbar": searchbar*/
  }
}).mount('#main');
