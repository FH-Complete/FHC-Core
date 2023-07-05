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
			return FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/lehre/Studierendenantrag/abmeldung/' + this.student.prestudent_id;
		},
		students() {
			return this.data.map((current)=>
			{
				current.name = current.nachname.toUpperCase() + " " + current.vorname + " (" + current.bezeichnung + ")";
				return current;
			}).sort(
				(a, b) => a.name > b.name ? 1 : -1
			);
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
				this.$emit('reload');
				this.student = '';
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
						<div>
							<auto-complete
								class="w-100"
								v-model="student"
								:suggestions="students"
								optionLabel = "name"
								@complete="loadData"
								inputId="newAntragModalAutoComplete"
								dropdown
								>
								<template #option="slotProps">
									<div :title="slotProps.option.prestudent_id">
									{{slotProps.option.name}}
									</div>
								</template>
							</auto-complete>
						</div>
					</div>
					<div class="modal-footer">
						<button
							class="btn btn-primary"
							:disabled="!this.student"
							@click.prevent="openForm">
							{{p.t('studierendenantrag','btn_create')}}
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
	`
}
