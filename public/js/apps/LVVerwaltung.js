import LVVerwaltung from "../components/LVVerwaltung/LVVerwaltung.js";
import Phrasen from "../plugins/Phrasen.js";

const ciPath = FHC_JS_DATA_STORAGE_OBJECT.app_root.replace(/(https:|)(^|\/\/)(.*?\/)/g, '') + FHC_JS_DATA_STORAGE_OBJECT.ci_router;

const router = VueRouter.createRouter({
	history: VueRouter.createWebHistory(`/${ciPath}/LVVerwaltung`),
	routes: [
		{
			name: 'index',
			path: `/`,
			component: LVVerwaltung
		},
		{
			name: `byEmp`,
			path: `/emp/:studiensemester_kurzbz/:emp/:stg?/:semester?`,
			component: LVVerwaltung,
			props: route => {
				let {emp, stg, semester, studiensemester_kurzbz} = route.params;

				if (emp === '')
					emp = undefined;

				if (stg === '')
					stg = undefined;

				if (studiensemester_kurzbz === '')
					studiensemester_kurzbz = undefined;

				return {
					studiensemester_kurzbz: studiensemester_kurzbz,
					emp: emp,
					stg: stg,
				};
			},
			beforeEnter: (to, from, next) => {
				const { studiensemester_kurzbz } = to.params;
				const isSemester = /^(SS|WS)\d{4}$/.test(studiensemester_kurzbz);

				if (!isSemester)
					return next({ path: '/' });
				else
					next();
			}
		},
		/*{
			name: `byFachbereich`,
			path: `/fachbereich/:fachbereich/:emp?`,
			component: LVVerwaltung
		},*/
		{
			name: `byStg`,
			path: '/stg/:studiensemester_kurzbz/:stg?/:semester?/',
			component: LVVerwaltung,
			props: route => {
				let { studiensemester_kurzbz, stg, semester } = route.params;

				if (semester === '')
					semester = undefined;

				if (studiensemester_kurzbz === '')
					studiensemester_kurzbz = undefined;

				if (stg === '')
					semester = undefined;

				return {
					studiensemester_kurzbz: studiensemester_kurzbz,
					stg: stg,
					semester: semester != null ? Number(semester) : null,
				};
			},
			beforeEnter: (to, from, next) => {
				const studiensemester_kurzbz = to.params?.studiensemester_kurzbz
				const isSemester = /^(SS|WS)\d{4}$/.test(studiensemester_kurzbz);

				if (!isSemester)
					return next({ path: '/' });
				else
					next();
			}
		},
		{
			path: '/:pathMatch(.*)*',
			redirect: '/'
		},

	]
});

const app = Vue.createApp();

app
	.use(router)
	.use(primevue.config.default, {
		zIndex: {
			overlay: 1100
		}
	})
	.use(Phrasen)
	.mount('#main');
