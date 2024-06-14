import FhcDashboard from '../../components/Dashboard/Dashboard.js';
import FhcApi from '../../plugin/FhcApi.js';

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
app.mount('#content');
