import BsAlert from '../../../Bootstrap/Alert.js';
import BsModal from '../../../Bootstrap/Modal.js';
import Phrasen from '../../../../mixins/Phrasen.js';

export default {
	components: {
		BsModal
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
			student: '',
			stg: ''
		}
	},
	computed: {
		newUrl() {
			return FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/lehre/Studierendenantrag/abmeldung/' + this.student;
		},
		students() {
			if (!this.stg)
				return [];
			if (!this.data[this.stg])
				return [];
			return this.data[this.stg].studenten.sort(
				(a, b) => a.nachname == b.nachname ?
					a.vorname > b.vorname :
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
				this.loadSelects();
				this.$emit('reload');
			});
		},
		loadSelects() {
			return axios.get(
				FHC_JS_DATA_STORAGE_OBJECT.app_root +
				FHC_JS_DATA_STORAGE_OBJECT.ci_router +
				'/components/Antrag/Abmeldung/getStudiengaengeAssistenz/'
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
	created() {
		return this.loadSelects();
	},
	template: `
	<div class="studierendenantrag-leitung-actions-new" v-if="data">
		<button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#newAntragModal" :disabled="hasNoData">
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
						<div class="mb-3">
							<label for="newAntragModalStg">{{p.t('lehre','studiengang')}}</label>
							<select id="newAntragModalStg" class="form-select" v-model="stg">
								<option v-for="(stg, stg_kz) in data" :value="stg_kz" :key="stg_kz">
									{{stg.bezeichnung}} ({{stg.orgform}})
								</option>
							</select>
						</div>
						<div class="mb-3">
							<label for="newAntragModalStudent">{{p.t('person','studentIn')}}</label>
							<select v-model="student" id="newAntragModalStudent" class="form-select">
								<option  v-for="(stg, stg_kz) in students" :value="stg.prestudent_id" :key="stg.prestudent_id">
									{{stg.nachname}} {{stg.vorname}}
								</option>
							</select>
						</div>
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
