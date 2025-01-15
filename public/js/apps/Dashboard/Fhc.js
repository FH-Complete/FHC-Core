import FhcDashboard from '../../components/Dashboard/Dashboard.js';
import FhcApi from '../../plugin/FhcApi.js';
import Phrasen from '../../plugin/Phrasen.js';
import { setScrollbarWidth } from "../../helpers/CssVarCalcHelpers";
import Stundenplan from "../../components/Cis/Stundenplan/Stundenplan";

const ciPath = FHC_JS_DATA_STORAGE_OBJECT.app_root.replace(/(https:|)(^|\/\/)(.*?\/)/g, '') + FHC_JS_DATA_STORAGE_OBJECT.ci_router;

const router = VueRouter.createRouter({
  history: VueRouter.createWebHistory(`/${ciPath}`),
  routes: [
    {
      path: `/Cis/Stundenplan`,
      name: 'Stundenplan',
      component: Stundenplan,
      props: true
    },
    {
      path: `/`,
      name: 'FhcDashboard',
      component: FhcDashboard,
      props: {dashboard: 'CIS'},
      alias: ['/Cis4']
    },
    {
      path: '/:catchAll(.*)',
      redirect: { name: 'FhcDashboard'},
      props: true
    }
  ]
})

router.beforeEach((from, to) => {
  console.log('from', from)
  console.log('to', to)
})

const app = Vue.createApp({
  name: 'FhcApp',
  data: () => ({
      appSideMenuEntries: {}
    }),
  components: {
    FhcDashboard,
    Stundenplan
  },
  methods: {
    tryCis4Navigate(e) {
      this.$router.push({ name: e.detail });
    },
  },
  mounted() {
    window.addEventListener('fhcnavigate', this.tryCis4Navigate);
    this.$router.push({ name: 'FhcDashboard' });
  },
  beforeUnmount() {
    window.removeEventListener('fhcnavigate', this.tryCis4Navigate);
  },
});

setScrollbarWidth();
app.use(router);
window.fhcVueRouter = router
app.use(FhcApi);
app.use(primevue.config.default, {
  zIndex: {
    overlay: 9000,
    tooltip: 8000
  }
})
app.use(Phrasen);
app.mount('#fhccontent');