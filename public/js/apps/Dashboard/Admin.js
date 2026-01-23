import {CoreNavigationCmpt} from '../../components/navigation/Navigation.js';
import DashboardAdmin from '../../components/Dashboard/Admin.js';
import FhcBase from "../../plugins/FhcBase/FhcBase.js";

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
app.use(FhcBase);
app.mount('#main');