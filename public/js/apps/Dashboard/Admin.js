import {CoreNavigationCmpt} from '../../components/navigation/Navigation.js';
import DashboardAdmin from '../../components/Dashboard/Admin.js';
import Phrasen from "../../plugin/Phrasen.js"
import FhcAlert from "../../plugin/FhcAlert.js"

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
app.use(Phrasen).use(FhcAlert);
app.mount('#main');
