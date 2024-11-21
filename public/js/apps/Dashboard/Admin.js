import {CoreNavigationCmpt} from '../../components/navigation/Navigation.js';
import DashboardAdmin from '../../components/Dashboard/Admin.js';
import FhcApi from '../../plugin/FhcApi.js';
import Phrasen from '../../plugin/Phrasen.js';

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
app.use(FhcApi);
app.use(Phrasen);
app.mount('#main');