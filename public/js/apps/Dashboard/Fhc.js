import FhcDashboard from '../../components/Dashboard/Dashboard.js';
import FhcApi from '../../plugin/FhcApi.js';
import Phrasen from '../../plugin/Phrasen.js';
import { setScrollbarWidth } from "../../helpers/CssVarCalcHelpers";

const app = Vue.createApp({
  name: 'FhcApp',
  data: () => ({
      appSideMenuEntries: {}
    }),
  components: {
    FhcDashboard
  }
});

setScrollbarWidth();
app.use(FhcApi);
app.use(primevue.config.default, {
  zIndex: {
    overlay: 9000,
    tooltip: 8000
  }
})
app.use(Phrasen);
app.mount('#content');