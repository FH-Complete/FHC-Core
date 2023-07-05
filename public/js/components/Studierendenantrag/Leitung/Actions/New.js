import BsAlert from '../../../Bootstrap/Alert.js';
import BsModal from '../../../Bootstrap/Modal.js';
import Phrasen from '../../../../mixins/Phrasen.js';

export default {
	components: {
		BsModal,
		AutoComplete: primevue.autocomplete
	},
	mixins: [
		Phrasen
	],
	emits: [
		'reload'
	],
	data() {
		return {
			data: [],
			student: ''
		}
	},
	computed: {
		newUrl() {
			return FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/lehre/Studierendenantrag/abmeldung/' + this.student;
		},
		students() {
			return this.data.sort(
				(a, b) => a.nachname == b.nachname ?
					(
						a.vorname == b.vorname ?
							a.bezeichnung > b.bezeichnung :
							a.vorname > b.vorname
					) :
					a.nachname > b.nachname
			);
		},
		hasNoData() {
			return !Object.values(this.data).length;
		}
	},
	methods: {
		openForm() {
			bootstrap.Modal.getInstance(this.$refs.modal).hide();
			BsModal.popup(Vue.h('iframe', {
				src: this.newUrl,
				class: 'position-absolute top-0 start-0 w-100 h-100'
			}), {
				dialogClass: 'modal-fullscreen'
			}, this.p.t('studierendenantrag', 'antrag_header')).then(() => {
				this.data = [];
				this.loadSelects();
				this.$emit('reload');
			});
		},
		loadData(evt) {
			axios.post(
				FHC_JS_DATA_STORAGE_OBJECT.app_root +
				FHC_JS_DATA_STORAGE_OBJECT.ci_router +
				'/components/Antrag/Abmeldung/getStudiengaengeAssistenz/',
				evt
			).then(
				result => {
					if (result.data.error) {
						BsAlert.popup(result.data.retval, {dialogClass: 'alert alert-danger'});
					} else {
						this.data = result.data.retval;
					}
					return result;
				}
			);
		},
		loadSelects() {
			if (this.hasNoData) {
				return axios.post(
					FHC_JS_DATA_STORAGE_OBJECT.app_root +
					FHC_JS_DATA_STORAGE_OBJECT.ci_router +
					'/components/Antrag/Abmeldung/getStudiengaengeAssistenz/',
					{query: 'felix'}
				).then(
					result => {
						if (result.data.error) {
							BsAlert.popup(result.data.retval, {dialogClass: 'alert alert-danger'});
						} else {
							this.data = result.data.retval;
						}
						return result;
					}
				);
			}
		}
	},
	template: `
	<div class="studierendenantrag-leitung-actions-new" v-if="data">
		<button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#newAntragModal">
			<i class="fa fa-plus"></i>
			{{p.t('studierendenantrag','btn_new')}}
		</button>
		<div ref="modal" class="modal fade" id="newAntragModal" tabindex="-1" aria-labelledby="newAntragModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="newAntragModalLabel">{{p.t('studierendenantrag','title_new_Abmeldung')}}</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" :aria-label="p.t('ui','schliessen')"></button>
					</div>
					<div class="modal-body">
						<label for="newAntragModalAutoComplete">{{p.t('person','studentIn')}}</label>
						<!-- TODO(chris): IMPLEMENT!! -->
						<auto-complete
							v-model="student"
							:suggestions="data"
							@complete="loadData"
							inputId="newAntragModalAutoComplete"
							dropdown
							>
							<template #option="slotProps">
								<div>
									{{slotProps.nachname}} {{slotProps.vorname}} ({{slotProps.bezeichnung}}) [{{slotProps.prestudent_id}}]
								</div>
							</template>
						</auto-complete>
						<!--div v-if="hasNoData">
							loading...
						</div>
						<div v-else class="mb-3">
							<label for="newAntragModalStudent">{{p.t('person','studentIn')}}</label>
							<select id="newAntragModalStudent" class="form-select" v-model="student">
								<option v-for="item in students" :value="item.prestudent_id" :key="item.prestudent_id">
									{{item.nachname}} {{item.vorname}} ({{item.bezeichnung}}) [{{item.prestudent_id}}]
								</option>
							</select>
						</div-->
					</div>
					<div class="modal-footer">
						<a :href="newUrl"
							class="btn btn-primary"
							target="_blank"
							@click.prevent="openForm">
							{{p.t('studierendenantrag','btn_create')}}
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	`
}
