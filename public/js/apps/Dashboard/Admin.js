import {CoreNavigationCmpt} from '../../components/navigation/Navigation.js';
import DashboardAdmin from '../../components/Dashboard/Admin.js';
import PluginsPhrasen from '../../plugins/Phrasen.js';

const app = Vue.createApp({
  name: 'AdminApp',
  data: () => ({
      appSideMenuEntries: {}
    }),
  components: {
    CoreNavigationCmpt,
    DashboardAdmin
  }
});
app.use(PluginsPhrasen);
app.directive('tooltip', primevue.tooltip);
app.mount('#main');