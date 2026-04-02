import {CoreNavigationCmpt} from '../../components/navigation/Navigation.js';
import CoreDashboard from '../../components/Dashboard/Dashboard.js';
import PluginsPhrasen from '../../plugins/Phrasen.js';

const app = Vue.createApp({
  name: 'DashboardPreviewApp',
  data: () => ({
      appSideMenuEntries: {}
    }),
  components: {
    CoreNavigationCmpt,
    CoreDashboard
  }
});
app.use(PluginsPhrasen);
app.directive('tooltip', primevue.tooltip);
app.mount('#main');