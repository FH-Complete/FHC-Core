import FormForm from '../../Form/Form.js';
import FormInput from '../../Form/Input.js';
import ListBox from "../../../../../index.ci.php/public/js/components/primevue/listbox/listbox.esm.min.js";
import DropdownComponent from '../../VorlagenDropdown/VorlagenDropdown.js';


export default {
	components: {
		FormForm,
		FormInput,
		ListBox,
		DropdownComponent
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
			isVisible: true,
			fieldsUser: [],
			fieldsPerson: [],
			fieldsPrestudent: [],
			selectedFieldPrestudent: null,
			selectedFieldUser: null,
			selectedFieldPerson: null,
			itemsPrestudent: [],
			itemsPerson: [],
			itemsUser: [],
			selectedFieldStudent: null,
			itemsStudent: [
				{ label: "Variable 1", value: "var1" },
				{ label: "Variable 2", value: "var2" },
				{ label: "Variable 3", value: "var3" }
			]
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
		sendMessage(){
			//TODO(Manu) check default recipient(s)
			const data = new FormData();

			data.append('data', JSON.stringify(this.formData));
			return this.$fhcApi.factory.messages.person.sendMessage(this.$refs.formVorlage, this.id, data)
				.then(response => {
					this.$fhcAlert.alertSuccess(this.$p.t('ui', 'successSent'));
					//this.hideModal('messageModal');
					this.resetForm();
				}).catch(this.$fhcAlert.handleSystemError)
				.finally(() => {
					//this.resetForm();
					//closeModal
					//closewindwo
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
		insertVariable(selectedItem){
			if (this.editor) {
				this.editor.insertContent(selectedItem.value + " ");
			} else {
				console.error("Editor instance is not available.");
			}
		},
		//TODO(Manu) refactor
/*		insertVariablePrestudent() {
				if (this.editor) {
					//const lastVariable = this.selectedFieldsPrestudent[this.selectedFieldsPrestudent.length - 1].value;
					const lastVariable = this.selectedFieldPrestudent.value;

				//Use insertContent method
				this.editor.insertContent(lastVariable + " ");
				} else {
				console.error("Editor instance is not available.");
			}
		},*/
/*		insertVariablePrestudentByClick(selectedItem) {

			if (selectedItem) {
				this.editor.insertContent(selectedItem.value + " ");
				console.log("Eingefügte Variable:", selectedItem);
			} else {
				console.warn("Keine Variable ausgewählt!");
			}
		},

		insertVariablePerson() {
				if (this.editor) {
					//const lastVariable = this.selectedFieldsPrestudent[this.selectedFieldsPrestudent.length - 1].value;
					const lastVariable = this.selectedFieldPerson.value;

				//Use insertContent method
				this.editor.insertContent(lastVariable + " ");
				} else {
				console.error("Editor instance is not available.");
			}
		},
		insertVariableUser() {
			if (this.editor) {
				console.log(this.selectedFieldUser.value);
				const lastVariable = this.selectedFieldUser.value;

				//Multiple
				//const lastVariable = this.selectedFieldsPrestudent[this.selectedFieldsPrestudent.length - 1].value;

				//Use insertContent method
				this.editor.insertContent(lastVariable + " ");
				this.selectedFieldUser = null;
			} else {
				console.error("Editor instance is not available.");
			}
		},
		insertVariableStudent(selectedItem) {
			if (selectedItem) {
				console.log("Eingefügte Variable:", selectedItem);
				this.editor.insertContent(selectedItem.value + " ");
			} else {
				console.warn("Keine Variable ausgewählt!");
			}
		},*/
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
		toggleDivNewMessage(){
			this.isVisible = !this.isVisible;
		},
		handleSelectedVorlage(vorlage_kurzbz) {
			if (typeof vorlage_kurzbz === "string") {
				this.getVorlagentext(vorlage_kurzbz);
				this.formData.subject = vorlage_kurzbz;
			}
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
				//TODO(Manu) own function or retval to getVorlagentext
				//component VorlagenComponent

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
						label: key,
						value: '{' + key + '}'
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
	
		<div v-show="isVisible">
			<h4>New Message</h4>
			
				{{typeId}} {{id}}
			
				{{formData.subject}}
				{{formData.vorlage_kurzbz}}
	
			<p v-if="formData.body">
				formData.body befüllt
			</p>

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
		</div>
		
		<hr>
		<button type="button" class="btn btn-warning" @click="toggleDivNewMessage()">Toggle NewMessage</button>
		<hr>
	</div>
	`

}