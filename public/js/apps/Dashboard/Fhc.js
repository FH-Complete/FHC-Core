import FhcDashboard from '../../components/Dashboard/Dashboard.js';

const app = Vue.createApp({
  data: () => ({
      appSideMenuEntries: {}
    }),
  components: {
    FhcDashboard
  }
});
app.config.unwrapInjectedRef = true;
app.mount('#content');
