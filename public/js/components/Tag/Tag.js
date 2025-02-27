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
		},
		confirmLimit: {
			type: Number,
			default: 20
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
			this.tagData.insertamum = this.formatDateTime(item.insertamum)
			this.tagData.updateamum = this.formatDateTime(item.updateamum)
			this.tagData.bearbeiter = item.bearbeiter;
			this.tagData.verfasser = item.verfasser;

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
				if (this.$fhcAlert && postData.values.length >= this.confirmLimit)
				{
					if (await this.$fhcAlert.confirm({message: `Der Tag wird für ${postData.values.length} Einträge gesetzt. Sind Sie sicher?`}) === false)
						return;
				}

				this.endpoint.addTag(postData)
					.then(response => response.data)
					.then(response => {
						if (typeof response === 'number') {
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
				verfasser: "",
				updateamum: "",
				bearbeiter: "",
				response: ""
			};
			this.selectedTagId = null;
			this.mode = "create";
		},
		formatDateTime: (dateString) => {
			if (!dateString) return null;
			return new Date(dateString).toLocaleString('de-AT', {
				year: "numeric",
				month: "2-digit",
				day: "2-digit",
				hour: "2-digit",
				minute: "2-digit",
				second: "2-digit"
			});
		}
	},
	template: `
		<div class="plus_button_container" @mouseleave="hideList">
			<span :title="values.length === 0 ? 'Bitte Zeilen markieren' : ''">
			<button @mouseover="showList = true" 
					:disabled="!values || values.length === 0"
					class="btn btn-sm">
				<i class="fa-solid fa-tag fa-xl"></i>
			</button>
			</span>
			<ul v-if="showList" class="dropdown_list">
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
				<span :class="['tag', tagData.style]">{{ tagData.beschreibung }}</span>
			</template>
			<template #default>
				<div class="col">
					<form-input
						v-model="tagData.notiz"
						type="textarea"
						field="notiz"
						placeholder="Notiz..."
					></form-input>
					<div class="modificationdate">
						<span v-if="tagData.verfasser">
							{{ $p.t('notiz', 'tag_verfasser', { 0: tagData.verfasser, 1: tagData.insertamum }) }}
						</span>
						<br />
						<span v-if="tagData.bearbeiter && tagData.insertamum !== tagData.updateamum">
							{{ $p.t('notiz', 'tag_bearbeiter', { 0: tagData.bearbeiter, 1: tagData.updateamum }) }}
						</span>
					</div>
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
							{{ tagData.done ? $p.t('notiz', 'tag_rueckgaengig') : $p.t('notiz', 'tag_erledigt') }}
						</button>
						<button v-if="mode === 'edit'" class="btn btn-danger" @click="deleteTag">{{ $p.t('global', 'loeschen' )}}</button>
					</div>
					<button type="button" class="btn btn-primary" @click="saveTag">
						{{ mode === "edit" ? $p.t('global', 'speichern') : $p.t('global', 'create') }}
					</button>
				</div>
			</template>
		</bs-modal>`,
}