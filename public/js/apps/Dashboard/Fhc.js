import FhcDashboard from '../../components/Dashboard/Dashboard.js';
import FhcApi from '../../plugin/FhcApi.js';
import Phrasen from '../../plugin/Phrasen.js';

const app = Vue.createApp({
  data: () => ({
      appSideMenuEntries: {}
    }),
  components: {
    FhcDashboard
  }
});
app.config.unwrapInjectedRef = true;
app.use(FhcApi);
app.use(Phrasen);
app.mount('#content');
