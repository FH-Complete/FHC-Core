import BsAlert from '../../../Bootstrap/Alert.js';
import BsModal from '../../../Bootstrap/Modal.js';

export default {
	components: {
		BsModal,
		AutoComplete: primevue.autocomplete
	},
	emits: [
		'reload'
	],
	data() {
		return {
			data: [],
			student: '',
			abortController: null
		}
	},
	computed: {
		newUrl() {
			return FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/lehre/Studierendenantrag/abmeldungStgl/' + this.student.prestudent_id;
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
			}, this.$p.t('studierendenantrag', 'antrag_header')).then(() => {
				this.$emit('reload');
				this.student = '';
			});
		},
		loadData(evt) {
                        if( evt.query.length < 2 )
                        {
                            return false;
                        }

			if (this.abortController instanceof AbortController
                            && this.abortController.signal.aborted === false)
                        {
                            this.abortController.abort();
                        }
			this.abortController = new AbortController();

			this.$fhcApi.factory
				.studstatus.leitung.getPrestudents(evt.query, this.abortController.signal)
				.then(result => {
					this.data = result.data;
                                        this.abortController = null;
				})
				.catch(error => {
                                    if (this.abortController instanceof AbortController 
                                        && this.abortController.signal.aborted === false)
                                    {
                                        this.abortController.abort();
                                    }
                                    this.$fhcAlert.handleSystemError(error);
                                });
		}
	},
	template: `
	<div class="studierendenantrag-leitung-actions-new" v-if="data">
		<button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#newAntragModal">
			<i class="fa fa-plus"></i>
			{{$p.t('studierendenantrag','btn_new')}}
		</button>
		<div ref="modal" class="modal fade" id="newAntragModal" tabindex="-1" aria-labelledby="newAntragModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="newAntragModalLabel">{{$p.t('studierendenantrag','title_new_Abmeldung')}}</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" :aria-label="$p.t('ui','schliessen')"></button>
					</div>
					<div class="modal-body">
						<label for="newAntragModalAutoComplete">{{$p.t('person','studentIn')}}</label>
						<div>
							<auto-complete
								class="w-100"
								v-model="student"
								:suggestions="data"
								option-label = "name"
								@complete="loadData"
								input-id="newAntragModalAutoComplete"
								dropdown
								dropdown-mode="current"
								>
								<template #option="slotProps">
									<div :title="slotProps.option.prestudent_id">
										{{slotProps.option.name}}
									</div>
								</template>
								<template #empty>
									<div class="text-muted px-3 py-2">
										{{ $p.t('ui/keineEintraegeGefunden') }}
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
							{{$p.t('studierendenantrag','btn_create')}}
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
	`
}
