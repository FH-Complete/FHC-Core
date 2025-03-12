import FormForm from '../../Form/Form.js';
import FormInput from '../../Form/Input.js';
import ListBox from "../../../../../index.ci.php/public/js/components/primevue/listbox/listbox.esm.min.js";
import DropdownComponent from '../../VorlagenDropdown/VorlagenDropdown.js';
import MessageModal from "../Details/NewMessage/Modal.js";

export default {
	components: {
		FormForm,
		FormInput,
		ListBox,
		DropdownComponent,
		MessageModal,
	},
	props: {
		endpoint: {
			type: String,
			required: true
		},
		typeId: String,
		id: {
			type: [Number, String],
			required: true
		},
		openMode: String,
	},
	data(){
		return {
			formData: {
				recipient: this.id,
				subject: null,
				body: null,
				vorlage_kurzbz: null,
				selectedValue: '',
			},
			statusNew: true,
			vorlagen: [],
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
			previewBody: ""
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
			//TODO(Manu) check default recipient(s)
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

			return this.$fhcApi.factory.messages.person.sendMessage(
				this.$refs.formMessage,
				this.id,
				data)
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSent'));
					//this.hideModal('messageModal');
					this.hideTemplate();
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
			//console.log(typeof vorlage_kurzbz);
			return this.$fhcApi.factory.messages.person.getVorlagentext(vorlage_kurzbz)
				.then(response => {
					//this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSent'));
					//this.hideModal('messageModal');
					//this.resetForm();
					//TODO(Manu) CHECK
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
			return this.$fhcApi.factory.messages.person.getPreviewText({
				id: this.id,
				type_id: this.typeId}, data)
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
				//TODO(Manu) check: nicht mal mit Punkt adden gehts ohne eintrag nach vars
/*				this.editor.focus();
				this.editor.setDirty(true);*/

				//this.editor.fire('change'); //forces

				//this.editor.undoManager.add();

				//this.editor.insertContent(selectedItem.value + "\u00A0");
				//this.editor.insertContent(`<span>${selectedItem.value}&nbsp;</span>`);
				//this.editor.selection.setCursorLocation(this.editor.getBody(), 1);

			} else {
				console.error("Editor instance is not available.");
			}
		},
		replyMessage(message_id){
			console.log("auf message " + message_id + " antworten");
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

		},
/*		toggleDivNewMessage(){
			this.isVisible = !this.isVisible;
		},*/
		handleSelectedVorlage(vorlage_kurzbz) {
			if (typeof vorlage_kurzbz === "string") {
				this.getVorlagentext(vorlage_kurzbz);
				this.formData.subject = vorlage_kurzbz;
			}
		},
		hideTemplate(){
			if (this.openMode == "showDiv")
				this.isVisible = false;
		},
		showTemplate(id, typeId){
			if (this.openMode == "showDiv")
				this.isVisible = true;
			//just for testing:
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
			//	console.log("Vorlage: " + newVal);

				if (newVal && newVal != null) {
					this.formData.subject = newVal;
					return this.getVorlagentext(newVal);
				}
			}
		}
	},
	created(){
		if(this.typeId == 'person_id'){
			this.$fhcApi.factory.messages.person.getMessageVarsPerson()
				.then(result => {
					this.fieldsPerson = result.data;
					this.itemsPerson = Object.entries(this.fieldsPerson).map(([key, value]) => ({
						label: value,
						value: '{' + value + '}'
					}));
				})
				.catch(this.$fhcAlert.handleSystemError);
		}
		if(this.typeId == 'uid') {
			this.$fhcApi.factory.messages.person.getMsgVarsPrestudent(this.id)
				.then(result => {
					this.fieldsPrestudent = result.data;
					const prestudent = this.fieldsPrestudent[0];
					//Just for testing with inserting values
/*					this.itemsPrestudent = Object.entries(prestudent).map(([key, value]) => ({
						label: key,
						value: value
					}));*/
					this.itemsPrestudent = Object.entries(prestudent).map(([key, value]) => ({
						label: key.toLowerCase(),
						value: '{' + key.toLowerCase() + '}'
					}));
				})
				.catch(this.$fhcAlert.handleSystemError);
		}

		this.$fhcApi.factory.messages.person.getMsgVarsLoggedInUser()
			.then(result => {
				this.fieldsUser = result.data;
				const user = this.fieldsUser;
				this.itemsUser = Object.entries(user).map(([key, value]) => ({
					label: value,
					value: '{' + value + '}'
				}));
			})
			.catch(this.$fhcAlert.handleSystemError);

		this.$fhcApi.factory.messages.person.getNameOfDefaultRecipient({
			id: this.id,
			type_id: this.typeId})
			.then(result => {
				this.defaultRecipient = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
	},
	async mounted() {
		this.initTinyMCE();
	},
	beforeDestroy() {
		this.editor.destroy();
	},
	template: `
	<div class="messages-detail-newmessage">

			<message-modal
				ref="modalMsg"
				:type-id="typeId"
				:id="id"
				:endpoint="endpoint"
				:openMode="openMode"
				@reloadTable="reloadTable"
				>
			</message-modal>
<!--			<hr>
		<button type="button" class="btn btn-warning" @click="toggleDivNewMessage()">Toggle NewMessage</button>
		<hr>-->

		<div v-show="isVisible">
			<div class="overflow-auto" style="max-height: 500px; border: 1px solid #ccc;">

				<h4>New Message</h4>
	<!--			{{formData.body}}
				||
				{{previewText}}-->

				<div class="row">
					<div class="col-sm-8">
						<form-form class="row g-3 mt-2" ref="formMessage">

						<!--TODO(Manu) ist eigentlich ein Array, hier werden alle Einträge angegeben als String-->
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
						<div v-if="this.fieldsPrestudent.length > 0">
							<strong>Felder Prestudent</strong>
							<div class="border p-3 overflow-auto" style="height: 200px;">

								<list-box
									v-model="selectedFieldPrestudent"
									:options="itemsPrestudent"
									optionLabel="label"
								>
								  <template #option="slotProps">
									<div @dblclick="insertVariable(slotProps.option)">
									  {{ slotProps.option.label }}
									</div>
								  </template>
								</list-box>

							</div>

							<button class="m-3" @click="insertVariablePrestudent">Insert Variable</button>
							<p>{{selectedFieldPrestudent}}</p>

						</div>

						<div v-if="this.fieldsPerson.length > 0">
							<strong>Felder Person</strong>
							<div class="border p-3 overflow-auto" style="height: 200px;">

								<list-box
									v-model="selectedFieldPerson"
									:options="itemsPerson"
									optionLabel="label"
								>
									<template #option="slotProps">
										<div @dblclick="insertVariable(slotProps.option)">
										  {{ slotProps.option.label }}
										</div>
								  </template>
								</list-box>

							</div>
							<button class="m-3" @click="insertVariablePerson">Insert Variable</button>
							<p>{{selectedFieldPerson}}</p>
						</div>

						<div>
							<strong>Meine Felder</strong>
							<div class="border p-3 overflow-auto" style="height: 200px;">

								<list-box
									v-model="selectedFieldUser"
									:options="itemsUser"
									optionLabel="label"
								>
									<template #option="slotProps">
										<div @dblclick="insertVariable(slotProps.option)">
										  {{ slotProps.option.label }}
										</div>
								  </template>
								</list-box>

							</div>
							<button class="m-3" @click="insertVariableUser">Insert Variable</button>
						</div>

						<div class="d-grid gap-2 d-md-flex justify-content-md-end">

							<button class="btn btn-secondary" @click="resetForm">Reset All</button>

							<button v-if="statusNew" type="button" class="btn btn-primary" @click="sendMessage()">{{$p.t('ui', 'nachrichtSenden')}}</button>
							<button v-else type="button" class="btn btn-primary" @click="replyMessage(formData.message_id)">{{$p.t('global', 'reply')}}</button>
						</div>

					</div>

				</div>

				<div class="row mt-4">

					<h4>Vorschau:</h4>
					<div>
						<form-form class="row g-3 mt-2" ref="formPreview">

							<div class="col-sm-2 mb-3">
								<form-input
									type="select"
									name="recipient"
									:label="$p.t('messages/recipient')"
									v-model="defaultRecipient"
								>
								</form-input>
							</div>

							<div class="col-md-2 mt-4">
								<br>
								<button type="button" class="btn bt		n-secondary" @click="showPreview()">Aktualisieren</button>
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

	</div>
	`

}