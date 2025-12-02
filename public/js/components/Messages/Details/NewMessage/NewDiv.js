import FormForm from '../../../Form/Form.js';
import FormInput from '../../../Form/Input.js';
import ListBox from "../../../../../../index.ci.php/public/js/components/primevue/listbox/listbox.esm.min.js";
import DropdownComponent from '../../../VorlagenDropdown/VorlagenDropdown.js';
import ApiMessages from "../../../../api/factory/messages/messages.js"; //props not working with route

export default {
	name: "ComponentNewMessages",
	components: {
		FormForm,
		FormInput,
		ListBox,
		DropdownComponent,
	},
	props: {
/*
		endpoint: {
			type: Object,
			required: true
		},
*/
		openMode: String,
		typeId: String,
		id: {
			type: Array,
			required: false
		},
		messageId: {
			type: Number,
			required: false,
		}
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
			defaultRecipients: [],
			defaultRecipientString: null,
			editor: null,
			isVisible: false,
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
			messageSent: false,
		}
	},
	methods: {
		initTinyMCE() {
			const vm = this;
			tinymce.init({
				target: this.$refs.editor.$refs.input, //Important: not selector: to enable multiple import of component
				//height: 800,
				//plugins: ['lists'],
				toolbar: 'styleselect | bold italic underline | alignleft aligncenter alignright alignjustify',
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
				},
			});
		},
		updateText(value) {
			this.formData.body = value;
		},
		sendMessage() {
			const data = new FormData();
			data.append('data', JSON.stringify(this.formData));
			data.append('ids', JSON.stringify(this.id));

			return this.$api
				.call(ApiMessages.sendMessage(this.typeId, data))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSent'));
					this.hideTemplate();
					this.resetForm();
					this.messageSent = true;
				}).catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					if(this.openMode == "inSamePage" && this.id.length == 1 ){
						this.$emit('reloadTable');
						}
					this.resetForm();
					}
				);
		},
		getDataVorlage(vorlage_kurzbz){
			return this.$api
				.call(ApiMessages.getDataVorlage(vorlage_kurzbz))
				.then(response => {
					this.formData.body = response.data.text;
					this.formData.subject = response.data.subject;
				}).catch(this.$fhcAlert.handleSystemError);
		},
		getPreviewText(){
			const data = new FormData();
			data.append('data', JSON.stringify(this.formData.body));
			data.append('ids', JSON.stringify(this.id));

			return this.$api
				.call(ApiMessages.getPreviewText(
					this.typeId, data))
				.then(response => {
					const previews = response.data;
					this.previewText = previews[this.defaultRecipient];
				}).catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
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
				recipient: null,
				selectedValue: null
			};
			if (this.editor) {
				this.editor.setContent("");
			}
		//	this.$refs.dropdownComp.setValue(null);

			this.previewBody = null;

		},
		toggleDivNewMessage(){
			this.isVisible = !this.isVisible;
		},
		handleSelectedVorlage(vorlage_kurzbz) {
			if (typeof vorlage_kurzbz === "string") {
				this.getDataVorlage(vorlage_kurzbz);
			}
		},
		hideTemplate(){
			if (this.openMode == "inSamePage")
				this.isVisible = false;
		},
		showTemplate(){
			if (this.openMode == "inSamePage")
				this.isVisible = true;
		},
		showPreview(){
			this.getPreviewText().then(() => {
				this.previewBody = this.previewText;
			});
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
					return this.getDataVorlage(newVal);
				}
			}
		},
	},
	created(){
		const missingparamsmsgs = [];
		if(!this.typeId)
		{
			// TODO(bh) Phrase
			missingparamsmsgs.push('Fehlender oder ungültiger Parameter Empfänger-Id-Typ.');
		}

		if(!this.id || this.id.length < 1)
		{
			// TODO(bh) Phrase
			missingparamsmsgs.push('Fehlender oder ungültiger Parameter Empfänger-Id(s).');
		}

		if(missingparamsmsgs.length > 0)
		{
			this.$fhcAlert.alertMultiple(missingparamsmsgs, 'warn', 'Warning', true);
			return;
		}

		if(this.typeId == 'person_id' || this.typeId == 'mitarbeiter_uid'){
			this.$api
				.call(ApiMessages.getMessageVarsPerson(this.id, this.typeId))
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
			this.$api
				.call(ApiMessages.getMsgVarsPrestudent(this.id, this.typeId))
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
			.call(ApiMessages.getMsgVarsLoggedInUser())
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
			.call(ApiMessages.getNameOfDefaultRecipients(this.id, this.typeId))
			.then(result => {
				this.defaultRecipients = result.data;
				this.defaultRecipientString = Object.values(this.defaultRecipients).join("; ");

			})
			.catch(this.$fhcAlert.handleSystemError);

		//case of reply
		if(this.messageId != null) {
			this.$api
				.call(ApiMessages.getReplyData(this.messageId))
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

	<div class="messages-detail-newmessage-newdiv">
			<!--new page-->
			<div v-if="!messageSent" class="overflow-auto m-3">
				<h4>{{ $p.t('messages', 'neueNachricht') }}</h4>

				<div class="row">
					<div class="col-sm-8">
						<form-form class="row g-3 mt-2" ref="formMessage">

							<div class="row mb-3">

								<form-input
									type="text"
									name="recipient"
									:label="$p.t('messages/recipient')"
									v-model="defaultRecipientString"
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
							<div class="row mb-3">
								<form-input
									ref="editor"
									:label="$p.t('global','nachricht')  + ' *'"
									type="textarea"
									v-model="formData.body"
									name="text"
									rows="15"
									cols="75"
									>
								</form-input>
							</div>

							<div class="row">
								<DropdownComponent
									ref="dropdownComp"
									:label="$p.t('global/vorlage')"
									@change="handleSelectedVorlage"
									useLoggedInUserOe
								>
								</DropdownComponent>
							</div>

						</form-form>
					</div>

					<div class="col-sm-4">
						<div v-if="this.fieldsPrestudent.length > 0"  class="mt-3">
							<strong>{{$p.t('ui', 'felder')}} {{$p.t('lehre', 'prestudent')}}</strong>

							<list-box
								v-model="selectedFieldPrestudent"
								:options="itemsPrestudent"
								optionLabel="label"
								listStyle="max-height:250px"
							>
							  <template #option="slotProps">
								<div @dblclick="insertVariable(slotProps.option)">
								  {{ slotProps.option.label }}
								</div>
							  </template>
							</list-box>

						</div>

						<br>

						<div v-if="this.fieldsPerson.length > 0" class="mt-3">
							<strong>Felder Person</strong>

							<list-box
								v-model="selectedFieldPerson"
								:options="itemsPerson"
								optionLabel="label"
								listStyle="max-height:250px"
							>
								<template #option="slotProps">
									<div @dblclick="insertVariable(slotProps.option)">
									  {{ slotProps.option.label }}
									</div>
							  </template>
							</list-box>

						</div>

						<div>
							<strong>{{$p.t('messages', 'meineFelder')}}</strong>

							<list-box
								v-model="selectedFieldUser"
								:options="itemsUser"
								optionLabel="label"
								listStyle="max-height:200px"
							>
								<template #option="slotProps">
									<div @dblclick="insertVariable(slotProps.option)">
									  {{ slotProps.option.label }}
									</div>
							  </template>
							</list-box>

						</div>

						<br>

						<div class="d-grid gap-2 d-md-flex justify-content-md-end">

							<button class="btn btn-secondary" @click="resetForm">{{$p.t('ui', 'reset')}}</button>

							<button v-if="statusNew" type="button" class="btn btn-primary" @click="sendMessage()">{{$p.t('ui', 'nachrichtSenden')}}</button>
							<button v-else type="button" class="btn btn-primary" @click="replyMessage(formData.message_id)">{{$p.t('global', 'reply')}}</button>
						</div>

					</div>

				</div>

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
										v-for="(name, id) in defaultRecipients"
										  :key="id" 
										  :value="Number(id)"
										> {{name}}
									</option>
								</form-input>
							</div>

							<div class="col-md-2 mt-4">
								<br>
								<button type="button" class="btn btn-secondary" @click="showPreview(defaultRecipient)">{{ $p.t('ui', 'btnAktualisieren') }}</button>
							</div>
						</form-form>

						<div class="col-sm-12 overflow-scroll">
								<div ref="preview">
									<div v-html="previewBody" class="p-3 border rounded overflow-scroll" style="height: 300px;"></div>
								</div>
						</div>

					</div>

				</div>

		</div>
		
					
		<div v-if="messageSent && openMode!='inSamePage'" class="container d-flex justify-content-center align-items-center m-3">
			<div class="card" style="width: 80%">
			  <div class="card-body alert alert-success text-dar p-5 rounded">
						<div class="row">
							<div class="col-6">
								Message sent successfully!
							</div>
							<div class="col-6">
								Nachricht erfolgreich versandt!
							</div>
						</div>
						
						<div class="row">
							<div class="col-6" style="border-right: 1px">
								You can safely close this window.
							</div>
							<div class="col-6">
								Sie können dieses Fenster schließen.
							</div>
						</div>
				</div>
				<div class="text-center">
					<p class="signatureblock">
						Fachhochschule Technikum Wien | University of Applied Sciences Technikum Wien
						<br>Hoechstaedtplatz 6, 1200 Wien, AUSTRIA
						<br><a class="signatureblocklink" href="https://www.technikum-wien.at">www.technikum-wien.at</a>
					</p>
				
				</div>
						

			</div>
	
	</div>


	</div>
	`

}
