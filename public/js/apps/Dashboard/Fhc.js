import FhcDashboard from '../../components/Dashboard/Dashboard.js';

Vue.createApp({
  data: () => ({
      appSideMenuEntries: {}
    }),
  components: {
    FhcDashboard
  }
}).mount('#content');
