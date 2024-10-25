import {CoreNavigationCmpt} from '../components/navigation/Navigation.js';
import CoreDashboard from '../components/Dashboard/Dashboard.js';
import FhcApi from '../../plugin/FhcApi.js';
import Phrasen from '../../plugin/Phrasen.js';

const app = Vue.createApp({
  name: 'DashboardApp',
  data: () => ({
      appSideMenuEntries: {}
    }),
  components: {
    CoreNavigationCmpt,
    CoreDashboard
  }
})
app.use(FhcApi);
app.use(Phrasen);
app.mount('#main');