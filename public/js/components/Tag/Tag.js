import CoreForm from '../Form/Form.js';
import FormInput from "../Form/Input.js";
import BsModal from '../Bootstrap/Modal.js';

export default {
	components: {
		CoreForm,
		FormInput,
		BsModal,
	},
	emits: [
		'added',
		'updated',
		'deleted',
	],
	props: {
		endpoint: {
			type: Object,
			required: true
		},
		zuordnung_typ: String,
		savepoint: {},
		values: {
			type: Array,
			required: true
		}
	},
	data() {
		return {
			showList: false,
			selectedTagId: null,
			tagData: {
				beschreibung: "",
				tag_typ_kurzbz: "",
				notiz: "",
				style: "",
				zuordnung_typ: "",
				id: "",
				insertamum: "",
				insertvon: "",
				updateamum: "",
				updatevon: "",
				response: ""
			},
			mode: "create"
		};
	},
	created() {
		this.init();
	},
	mounted() {},
	methods: {
		init() {
			if (!this.endpoint)
				return;
			this.endpoint.getTags()
				.then(response => response.data)
				.then(response => {
					this.tags = response
				})
		},
		hideList() {
			this.showList = false;
		},
		async editTag(tag_id) {
			let getData = {
				'id': tag_id
			};

			this.endpoint.getTag(getData)
				.then(result => result.data)
				.then(result => this.openModal(result))
		},
		openModal(item = null)
		{
			this.tagData.beschreibung = item.bezeichnung;
			this.tagData.tag_typ_kurzbz = item.tag_typ_kurzbz;
			this.tagData.style = item.style;
			this.tagData.zuordnung_typ = this.zuordnung_typ;
			this.tagData.done = item.done;
			this.tagData.insertamum = item.insertamum;
			this.tagData.updateamum = item.updateamum;
			this.tagData.updatevon = item.updatevon;
			this.tagData.insertvon = item.insertvon;

			if (item && item.notiz_id)
			{
				this.selectedTagId = item.notiz_id;
				this.tagData.notiz = item.text;
				this.mode = "edit";
			}
			else
			{
				this.selectedTagId = null;
				this.tagData.notiz = "";
				this.mode = "create";
			}

			if (this.mode === "create" && item.tag)
				this.saveTag()
			else
				this.$refs.tagModal.show();
		},
		async saveTag()
		{
			let postData = {
				tag_typ_kurzbz: this.tagData.tag_typ_kurzbz,
				notiz: this.tagData.notiz,
				zuordnung_typ: this.tagData.zuordnung_typ,
				values: this.values
			}

			if (this.mode === "edit")
			{
				postData.id = this.selectedTagId;
				this.tagData.id = this.selectedTagId;
				this.endpoint.updateTag(postData);
				this.$emit("updated", this.tagData);
				this.$refs.tagModal.hide();
			}
			else
			{
				this.endpoint.addTag(postData)
					.then(response => response.data)
					.then(response => {
						if (typeof response === 'number') {
							console.log(response);
							this.tagData.id = response;
						} else {
							this.tagData.response = response;
						}
					})
					.then(() => {
						this.$emit("added", this.tagData);
					})
					.then(() => {
						this.$refs.tagModal.hide();
					});
			}


		},
		async doneTag()
		{
			this.tagData.id = this.selectedTagId;
			this.tagData.done = !this.tagData.done;

			let postData = {
				id: this.selectedTagId,
				done: !this.tagData.done
			}
			this.endpoint.doneTag(postData)
			this.$emit("updated", this.tagData);
			this.$refs.tagModal.hide();
		},
		async deleteTag()
		{
			let postData = {
				id: this.selectedTagId
			}
			this.endpoint.deleteTag(postData)
			this.$emit("deleted", this.selectedTagId)
			this.$refs.tagModal.hide();
		},
		reset() {
			this.tagData = {
				beschreibung: "",
				tag_typ_kurzbz: "",
				notiz: "",
				style: "",
				zuordnung_typ: "",
				id: "",
				done: false,
				insertamum: "",
				insertvon: "",
				updateamum: "",
				updatevon: "",
				response: ""
			};
			this.selectedTagId = null;
			this.mode = "create";
		}
	},
	template: `
		<div class="plus-button-container" @mouseleave="hideList">
			<button @mouseover="showList = true" 
					:disabled="!values || values.length === 0"
					class="btn btn-sm">
				<i class="fa-solid fa-tag fa-xl"></i>
			</button>
			<ul v-if="showList" class="dropdown-list">
				<li v-for="(item, index) in tags" :key="index" @click="openModal(item)" :title="item.bezeichnung">
					{{ item.bezeichnung }}
				</li>
			</ul>
		</div>
		
		<bs-modal
			ref="tagModal"
			class="fade text-center"
			dialog-class="modal-dialog-centered"
			@hidden-bs-modal="reset"
		>
			<template #title>
				<span :class="tagData.style">{{ tagData.beschreibung }}</span>
			</template>
			<template #default>
				<div class="col">
					<form-input
						v-model="tagData.notiz"
						type="textarea"
						field="notiz"
						placeholder="Notiz..."
					></form-input>
					<div class="modificationdate">angelegt von {{ tagData.insertvon }} am {{ tagData.insertamum }}</div>
				</div>
			</template>
			<template #footer>
				<div class="d-flex justify-content-between w-100">
					<div>
						<button 
							v-if="mode === 'edit'" 
							class="btn btn-success me-2" 
							@click="doneTag"
						>
							{{ tagData.done ? 'Rückgängig' : 'Erledigt' }}
						</button>
						<button v-if="mode === 'edit'" class="btn btn-danger" @click="deleteTag">Löschen</button>
					</div>
					<button type="button" class="btn btn-primary" @click="saveTag">
						{{ mode === "edit" ? $p.t('ui', 'bearbeiten') : $p.t('studierendenantrag', 'btn_create') }}
					</button>
				</div>
			</template>
		</bs-modal>`,
}