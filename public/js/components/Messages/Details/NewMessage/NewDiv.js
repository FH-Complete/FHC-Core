import FormForm from '../../../Form/Form.js';
import FormInput from '../../../Form/Input.js';
import ListBox from "../../../../../../index.ci.php/public/js/components/primevue/listbox/listbox.esm.min.js";
import DropdownComponent from '../../../VorlagenDropdown/VorlagenDropdown.js';
import ApiMessages from "../../../../api/factory/messages/messages.js";

export default {
	name: "ComponentNewMessages",
	components: {
		FormForm,
		FormInput,
		ListBox,
		DropdownComponent,
	},
	props: {
		endpoint: {
			type: String,
			required: true
		},
		openMode: String,
		tempTypeId: String,
		tempId: {
			type: [Number, String],
			required: false
		},
		tempMessageId: {
			type: Number,
			required: false,
		}
	},
	computed: {
		//params with routes for new tab and new window AND props for inSamePage
		id(){
			return this.$props.tempId || this.$route.params.id;
		},
		typeId(){
			return this.$props.tempTypeId || this.$route.params.typeId;
		},
		messageId(){
			return this.$props.tempMessageId ||this.$route.params.messageId;
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
			uid: null,
			messageSent: false
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

			const params = {
				id: this.id,
				type_id: this.typeId
			};

			const merged = {
				...this.formData,
				...params
			};
			data.append('data', JSON.stringify(merged));
			return this.$api
				.call(ApiMessages.sendMessage(this.uid, data))
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSent'));
					this.hideTemplate();
					this.resetForm();
					this.messageSent = true;
				}).catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					//TODO(Manu) hier route definieren für openmode in Tab, Page?
					// ist kein child sondern mit route aufgerufen
					//würde allerdings neues fenster aktualisiert öffnen, altes bleibt ohne reload gleich
					//Reload vorheriges tab???
					if(this.openMode == "inSamePage"){
						this.$emit('reloadTable');
						}
					}
				);
		},
		getVorlagentext(vorlage_kurzbz){
			return this.$api
				.call(ApiMessages.getVorlagentext(vorlage_kurzbz))
				.then(response => {
					this.formData.body = response.data;
				}).catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					//this.resetForm();
				});
		},
		getPreviewText(id, typeId){
			const data = new FormData();

			data.append('data', JSON.stringify(this.formData.body));
			return this.$api
				.call(ApiMessages.getPreviewText({
					id: this.id,
					type_id: this.typeId}, data))
				.then(response => {
					this.previewText = response.data;
				}).catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					//this.resetForm();
				});
		},
		insertVariable(selectedItem){
			if (this.editor) {
				this.editor.insertContent(selectedItem.value + " ");
				//TODO(Manu) check: Laden von Variblen geht nicht wenn kein Zeichen danach kommt
				// nicht mal mit Punkt adden gehts ohne eintrag nach vars
								//this.editor.focus();
							//	this.editor.setDirty(true);

				this.editor.setDirty(true);//seting dirty true if changes appear
			//	console.log(tinyMCE.activeEditor.isDirty());//dirty output  = true


				//this.editor.undoManager.add();

				//this.editor.insertContent(selectedItem.value + "\u00A0");
				//this.editor.insertContent(`<span>${selectedItem.value}&nbsp;</span>`);
				//this.editor.selection.setCursorLocation(this.editor.getBody(), 1);

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
			if (this.editor) {
				this.editor.setContent("");
			}
			this.$refs.dropdownComp.setValue(null);

			this.previewBody = null;

		},
		toggleDivNewMessage(){
			this.isVisible = !this.isVisible;
		},
		handleSelectedVorlage(vorlage_kurzbz) {
			if (typeof vorlage_kurzbz === "string") {
				this.getVorlagentext(vorlage_kurzbz);
				this.formData.subject = vorlage_kurzbz;
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
		showPreview(id, typeId){
			this.getPreviewText(id, typeId).then(() => {
				this.previewBody = this.previewText;
			});
		},
		getUid(id, typeId){
			const params = {
				id: id,
				type_id: typeId
			};
			this.$api
				.call(ApiMessages.getUid(params))
				.then(result => {
					this.uid = result.data;
				})
				.catch(this.$fhcAlert.handleSystemError);
		}
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
	},
	created(){
		this.getUid(this.id, this.typeId);

		if (['person_id', 'mitarbeiter_uid'].includes(this.typeId)){
				const params = {
					id: this.id,
					type_id: this.typeId
				};

				this.$api
				.call(ApiMessages.getMessageVarsPerson(params))
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

		if (['prestudent_id', 'uid'].includes(this.typeId)){
				const params = {
				id: this.id,
				type_id: this.typeId
			};
			this.$api
				.call(ApiMessages.getMsgVarsPrestudent(params))
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
			.call(ApiMessages.getNameOfDefaultRecipient({
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
										v-for="recipient in recipientsArray"
										:key="recipient.uid" 
										:value="recipient.uid" 
										>{{recipient.details}}
									</option>
								</form-input>
							</div>

							<div class="col-md-2 mt-4">
								<br>
								<button type="button" class="btn btn-secondary" @click="showPreview(id, typeId)">{{ $p.t('ui', 'btnAktualisieren') }}</button>
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
