import BsModal from "../../../Bootstrap/Modal.js";
import FormForm from "../../../Form/Form.js";
import FormInput from '../../../Form/Input.js';
import ListBox from "../../../../../../index.ci.php/public/js/components/primevue/listbox/listbox.esm.min.js";
import DropdownComponent from "../../../VorlagenDropdown/VorlagenDropdown.js";

export default {
	name: "ModalNewMessages",
	components: {
		BsModal,
		FormForm,
		DropdownComponent,
		FormInput,
		ListBox
	},
	props: {
		endpoint: {
			type: Object,
			required: true
		},
		typeId: String,
		id: {
			type: [Number, String],
			required: true
		},
		messageId: {
			type: Number,
			required: false,
		},
		openMode: String,
	},
	data(){
		return {
			formData: {
				recipient: null,
				subject: null,
				body: null,
				vorlage_kurzbz: null,
				selectedValue: '',
				relationmessage_id: null
			},
			statusNew: true,
			vorlagen: [],
			recipientsArray: [],
			defaultRecipient: null,
			editor: null,
			fieldsUser: [],
			fieldsPerson: [],
			fieldsPrestudent: [],
			selectedFieldPrestudent: null,
			selectedFieldUser: null,
			selectedFieldPerson: null,
			itemsPrestudent: [],
			itemsPerson: [],
			itemsUser: [],
			previewText: null,
			previewBody: "",
			replyData: null,
			uid: null,
		}
	},
	methods: {
		initTinyMCE() {
			const vm = this;
			tinymce.init({
				target: this.$refs.editor.$refs.input, //Important: not selector: to enable multiple import of component
				//height: 800,
				//plugins: ['lists'],
				toolbar: 'styleselect | bold italic underline | alignleft aligncenter alignright alignjustify | link',
				plugins: 'link',
				link_context_toolbar: true,
				automatic_uploads: true,
				default_link_target: "_blank",
				link_title: true,
				target_list: [
					{ title: 'New tab', value: '_blank' },
					{ title: 'Same tab', value: '_self' }
				],
				style_formats: [
					{title: 'Blocks', block: 'div'},
					{title: 'Paragraph', block: 'p'},
					{title: 'Heading 1', block: 'h1'},
					{title: 'Heading 2', block: 'h2'},
					{title: 'Heading 3', block: 'h3'},
					{title: 'Heading 4', block: 'h4'},
					{title: 'Heading 5', block: 'h5'},
					{title: 'Heading 6', block: 'h6'},
				],
				autoresize_bottom_margin: 16,

				setup: (editor) => {
					vm.editor = editor;

					editor.on('input', () => {
						const newContent = editor.getContent();
						vm.formData.body = newContent;
					});

					// Prevent Bootstrap dialog from blocking focusin
					document.addEventListener('focusin', (e) => {
						if (e.target.closest(".tox-tinymce-aux, .moxman-window, .tam-assetmanager-root") !== null) {
							e.stopImmediatePropagation();
						}
					});
				},

			});
		},
		updateText(value) {
			this.formData.body = value;
		},
		sendMessage() {
			const data = new FormData();
			const params = {
				id: this.id,
				type_id: this.typeId
			};
			const merged = {
				...this.formData,
				...params
			};
			data.append('data', JSON.stringify(merged));

			return this.$refs.formMessage
				.call(this.endpoint.sendMessageFromModalContext(this.uid, data))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSent'));
					this.hideModal('modalNewMessage');
					this.resetForm();
				}).catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
						//this.resetForm();
						//closeModal
						//closewindwo
						this.$emit('reloadTable');
					}
				);
		},
		getVorlagentext(vorlage_kurzbz){
			return this.$api
				.call(this.endpoint.getVorlagentext(vorlage_kurzbz))
				.then(response => {
					this.formData.body = response.data;
				}).catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					//this.resetForm();
					//closeModal
					//closewindwo
				});
		},
		getPreviewText(){
			const data = new FormData();

			data.append('data', JSON.stringify(this.formData.body));
			return this.$api
				.call(this.endpoint.getPreviewText({
					id: this.id,
					type_id: this.typeId}, data))
				.then(response => {
					this.previewText = response.data;
				}).catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					//this.resetForm();
					//closeModal
					//closewindwo
				});
		},
		insertVariable(selectedItem){
			if (this.editor) {
				this.editor.insertContent(selectedItem.value + " ");

				this.editor.fire('input');
				this.editor.fire('change');
				this.editor.setDirty(true);
				this.editor.save();

			} else {
				console.error("Editor instance is not available.");
			}
		},
		resetForm(){
			this.formData = {
				vorlage_kurzbz: null,
				body: null,
				subject: null,
			};
			this.$emit('resetMessageId');

			if (this.editor) {
				this.editor.setContent("");
			}

			this.$refs.dropdownComp.setValue(null);

			this.previewBody = null;

		},
		handleSelectedVorlage(vorlage_kurzbz) {
			if (typeof vorlage_kurzbz === "string") {
				this.getVorlagentext(vorlage_kurzbz);
				this.formData.subject = vorlage_kurzbz;
			}
		},
		showPreview(){
			this.getPreviewText().then(() => {
				this.previewBody = this.previewText;
			});
		},
		getUid(id, typeId){
			const params = {
				id: id,
				type_id: typeId
			};
			this.$api
				.call(this.endpoint.getUid(params))
				.then(result => {
					this.uid = result.data;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		show(){
			this.$refs.modalNewMessage.show();
		},
		hideModal(modalRef){
			this.$refs[modalRef].hide();
		},
	},
	watch: {
		'formData.body': {
			handler(newVal) {
				const tinymcsVal = this.editor.getContent();

				if (newVal && tinymcsVal != newVal) {
					//Inhalt des Editors aktualisieren
					this.editor.setContent(newVal);
				}
			}
		},
		'formData.vorlage_kurzbz': {
			handler(newVal){
				if (newVal && newVal != null) {
					this.formData.subject = newVal;
					return this.getVorlagentext(newVal);
				}
			}
		},
		messageId: {
			immediate: true,
			handler: async function (newMessageId) {
				if (!newMessageId) return;

				try {
					const result = await this.$api.call(this.endpoint.getReplyData(newMessageId));
					this.replyData = result.data;

					if (this.replyData.length > 0) {
						this.formData.subject = this.replyData[0].replySubject;
						this.formData.body = this.replyData[0].replyBody;
						this.formData.relationmessage_id = newMessageId;
					}
				} catch (error) {
					this.$fhcAlert.handleSystemError(error);
				}
			}
		}
	},
	created(){
		this.getUid(this.id, this.typeId);

		if(this.typeId == 'person_id' || this.typeId == 'mitarbeiter_uid'){
			const params = {
				id: this.id,
				type_id: this.typeId
			};
			this.$api
				.call(this.endpoint.getMessageVarsPerson(params))
				.then(result => {
					this.fieldsPerson = result.data;
					const person = this.fieldsPerson[0];
					this.itemsPerson = Object.entries(person).map(([key, value]) => ({
						label: key.toLowerCase(),
						value: '{' + key.toLowerCase() + '}'
					}));
				})
				.catch(this.$fhcAlert.handleSystemError);
		}

		if(this.typeId == 'prestudent_id' || this.typeId == 'uid'){
			const params = {
				id: this.id,
				type_id: this.typeId
			};
			this.$api
				.call(this.endpoint.getMsgVarsPrestudent(params))
				.then(result => {
					this.fieldsPrestudent = result.data;
					const prestudent = this.fieldsPrestudent[0];
					this.itemsPrestudent = Object.entries(prestudent).map(([key, value]) => ({
						label: key.toLowerCase(),
						value: '{' + key.toLowerCase() + '}'
					}));
				})
				.catch(this.$fhcAlert.handleSystemError);
		}

		this.$api
			.call(this.endpoint.getMsgVarsLoggedInUser())
			.then(result => {
				this.fieldsUser = result.data;
				const user = this.fieldsUser;
				this.itemsUser = Object.entries(user).map(([key, value]) => ({
					label: value,
					value: '{' + value + '}'
				}));
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$api
			.call(this.endpoint.getNameOfDefaultRecipient({
				id: this.id,
				type_id: this.typeId}))
			.then(result => {
				this.defaultRecipient = result.data;
				this.recipientsArray.push({
					'uid': this.uid,
					'details': this.defaultRecipient});
			})
			.catch(this.$fhcAlert.handleSystemError);

		//case of reply
		if(this.messageId) {
			this.$api
				.call(this.endpoint.getReplyData(this.messageId))
				.then(result => {
					this.replyData = result.data;
					this.formData.subject = this.replyData[0].replySubject;
					this.formData.body = this.replyData[0].replyBody;
					this.formData.relationmessage_id = this.messageId;
				})
				.catch(this.$fhcAlert.handleSystemError);
		}
	},
	async mounted() {
		this.initTinyMCE();
	},
	beforeDestroy() {
		this.editor.destroy();
	},
	template: `
		<bs-modal
			class="messages-detail-newmessage-modal"
			ref="modalNewMessage" 
			dialog-class=" modal-dialog-scrollable modal-xl modal-msg"
			header-class="flex-wrap pb-0"
			body-class="px-3 py-2"
			@hidden.bs.modal="resetForm"
			>

			<template #title>
				{{ $p.t('messages', 'neueNachricht') }}
			</template>

			<template #modal-header-content>
			<ul class="nav nav-tabs w-100 mt-3 msg_preview" id="msg_preview" role="tablist">
				<li class="nav-item" role="presentation">
					<button class="nav-link active" id="msg-tab" data-bs-toggle="tab" data-bs-target="#msg" type="button" role="tab" aria-controls="msg" aria-selected="true">Nachricht</button>
				</li>
				<li class="nav-item" role="presentation">
					<button class="nav-link" id="preview-tab" data-bs-toggle="tab" data-bs-target="#preview" type="button" role="tab" aria-controls="preview" aria-selected="false">Vorschau</button>
				</li>
			</ul>
			</template>

			<form-form ref="formNewMassage">

			<div class="tab-content" id="msg_preview_content">
				<div class="tab-pane fade show active" id="msg" role="tabpanel" aria-labelledby="msg-tab">

				<div class="row">
					<div class="col-sm-8">
						<form-form class="row g-3 mt-2 h-100" ref="formMessage">

							<div class="row mb-3">

								<form-input
									type="text"
									name="recipient"
									:label="$p.t('messages/recipient')"
									v-model="defaultRecipient"
									disabled
								>
								</form-input>
							</div>

							<div class="row mb-3">
								<form-input
									type="text"
									name="subject"
									:label="$p.t('global/betreff') + ' *'"
									v-model="formData.subject"
								>
								</form-input>
							</div>

							<!--Tiny MCE-->
							<div class="row mb-3 h-100 tiny-90">
								<form-input
									ref="editor"
									:label="$p.t('global','nachricht')  + ' *'"
									type="textarea"
									v-model="formData.body"
									name="body"
									rows="35"
									cols="75"
									>
								</form-input>
							</div>

						</form-form>
					</div>

					<div class="col-sm-4">

						<div class="row">
							<dropdown-component
								ref="dropdownComp"
								:label="$p.t('global/vorlage')"
								@change="handleSelectedVorlage"
								useLoggedInUserOe
							>
							</dropdown-component>
						</div>

						<div v-if="this.fieldsPrestudent.length > 0">
							<div class="mt-2"><strong>{{$p.t('ui', 'felder')}} {{$p.t('lehre', 'prestudent')}}</strong></div>

							<list-box
								v-model="selectedFieldPrestudent"
								:options="itemsPrestudent"
								optionLabel="label"
								listStyle="max-height:200px; overscroll-behavior: none;"
							>
							  <template #option="slotProps">
								<div @dblclick="insertVariable(slotProps.option)">
								  {{ slotProps.option.label }}
								</div>
							  </template>
							</list-box>

						</div>

						<div v-if="this.fieldsPerson.length > 0">
							<div class="mt-2"><strong>Felder Person</strong></div>

							<list-box
								v-model="selectedFieldPerson"
								:options="itemsPerson"
								optionLabel="label"
								listStyle="max-height:200px; overscroll-behavior: none;"
							>
								<template #option="slotProps">
									<div @dblclick="insertVariable(slotProps.option)">
									  {{ slotProps.option.label }}
									</div>
							  </template>
							</list-box>

						</div>

						<div>
							<div class="mt-2"><strong>{{$p.t('messages', 'meineFelder')}}</strong></div>

							<list-box
								v-model="selectedFieldUser"
								:options="itemsUser"
								optionLabel="label"
								listStyle="max-height:200px; overscroll-behavior: none;"
							>
								<template #option="slotProps">
									<div @dblclick="insertVariable(slotProps.option)">
									  {{ slotProps.option.label }}
									</div>
							  </template>
							</list-box>

						</div>

					</div>

				</div>

				</div>
				<div class="tab-pane fade" id="preview" role="tabpanel" aria-labelledby="preview-tab">

				<div class="row mt-4">

					<h4>{{ $p.t('global', 'vorschau') }}:</h4>
					<div>
						<form-form class="row g-3 mt-2" ref="formPreview">

							<div class="col-sm-2 mb-3">
								<form-input
									type="select"
									name="recipient"
									:label="$p.t('messages/recipient')"
									v-model="defaultRecipient"
								>
									<option :value="null">{{ $p.t('messages', 'recipient') }}...</option>
									<option
										v-for="recipient in recipientsArray"
										:key="recipient.uid"
										:value="recipient.uid" 
										>{{recipient.details}}
									</option>
								</form-input>
							</div>

							<div class="col-md-2 mt-4">
								<br>
								<button type="button" class="btn btn-secondary" @click="showPreview()">{{ $p.t('ui', 'btnAktualisieren') }}</button>
							</div>
						</form-form>

						<div class="col-sm-12 overflow-scroll">
								<div ref="preview">
									<div v-html="previewBody" class="p-3 border rounded overflow-scroll twoColumns"></div>
								</div>
						</div>

					</div>

				</div>

				</div>
			</div>

			</form-form>

			<template #footer>
				<div class="d-grid gap-2 d-md-flex justify-content-md-end">

					<button class="btn btn-secondary" @click="resetForm">{{$p.t('ui', 'reset')}}</button>

					<button v-if="statusNew" type="button" class="btn btn-primary" @click="sendMessage()">{{$p.t('ui', 'nachrichtSenden')}}</button>
					<button v-else type="button" class="btn btn-primary" @click="replyMessage(formData.message_id)">{{$p.t('global', 'reply')}}</button>
				</div>
			</template>

		</bs-modal>
	`,
}