import {CoreNavigationCmpt} from '../../components/navigation/Navigation.js';
import DashboardAdmin from '../../components/Dashboard/Admin.js';

const app = Vue.createApp({
  data: () => ({
      appSideMenuEntries: {}
    }),
  components: {
    CoreNavigationCmpt,
    DashboardAdmin
  }
});
app.config.unwrapInjectedRef = true;
app.mount('#main');
