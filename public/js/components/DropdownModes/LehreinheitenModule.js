import ApiLehre from "../../api/factory/lehre.js";

const options = Vue.ref([]);
const params = Vue.ref({});
let appContext = null;

export function setupContext(globalProps) {
	appContext = globalProps
}

// bind and watch api params via reference
export function bindParams(paramsRef) {
	Vue.watch(
		paramsRef,
		(newVal) => {
			params.value = { ...newVal };
			fetchLehreinheiten(newVal.lv_id, newVal.sem_kurzbz);
		},
		{ immediate: true, deep: true }
	);
}

async function fetchLehreinheiten(lv_id, sem_kurzbz) {
	appContext.$api.call(ApiLehre.getLeForLv(lv_id, sem_kurzbz)).then(res => {

		const data =  []
		// TODO: could be done on server in some shared function, copied from anw extension for now
		res.data?.retval?.forEach(entry => {

			const existing = data.find(e => e.lehreinheit_id === entry.lehreinheit_id)
			if (existing) {
				// supplement info
				existing.infoString += ', '
				if (entry.gruppe_kurzbz !== null && entry.direktinskription == false) {
					existing.infoString += entry.gruppe_kurzbz
				} else {
					existing.infoString += entry.kurzbzlang + '-' + entry.semester
						+ (entry.verband ? entry.verband : '')
						+ (entry.gruppe ? entry.gruppe : '')
				}
			} else {
				// entries are supposed to be fetched ordered by non null gruppe_kurzbz first
				// so a new entry will always start with those groups, others are appended afterwards
				entry.infoString = entry.kurzbz + ' - ' + entry.lehrform_kurzbz + ' - '
				if (entry.gruppe_kurzbz !== null && entry.direktinskription == false) {
					entry.infoString += entry.gruppe_kurzbz
				} else {
					entry.infoString += entry.kurzbzlang + '-' + entry.semester
						+ (entry.verband ? entry.verband : '')
						+ (entry.gruppe ? entry.gruppe : '')
				}

				data.push(entry)
			}
		})

		options.value = [...data]

	})
}

// export the module and relevant fields via reactive
const LehreinheitenModule = Vue.reactive({
	options,
	optionLabel: 'infoString',
	placeholder: Vue.computed(()=>appContext?.$p.t('lehre/lehreinheit')),
	setupContext,
	bindParams
});

export default LehreinheitenModule;