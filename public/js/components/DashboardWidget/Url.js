import CoreForm from "../Form/Form.js";
import FormInput from "../Form/Input.js";
import BsModal from "../Bootstrap/Modal.js";
import AbstractWidget from './Abstract.js';

import ApiBookmark from '../../api/factory/widget/bookmark.js';

export default {
	name: "WidgetsUrl",
	mixins: [AbstractWidget],
	inject: {
		editModeIsActive: {
			type: Boolean,
			default: false
		}
	},
	components:{
		CoreForm,
		FormInput,
		BsModal
	},
	data: () => ({
		bookmark_id: null,
		title_input: "",
		url_input: "",
		validation: {
			invalidURL: false,
			invalidTitel: false,
		},
	}),

	computed: {
		tagName() {
			return this.config.tag !== undefined && this.config.tag.length > 0
				? this.config.tag
				: this.$p.t("bookmark", "myBookmarks");
		},
		emptyBookmarks() {
			if (this.shared instanceof Array && this.shared.length == 0) return true;

			if (!this.shared) return true;

			return false;
		},
	},
	methods: {
		stopDrag(event){
			event.preventDefault();
		},
		clearInputs(){
			this.title_input = "";
			this.url_input = "";	
		},
		openCreateModal() {
			this.$refs.createModal.show()
		},
		openEditModal(bookmark) {
			this.title_input = bookmark.title;
			this.url_input = bookmark.url;
			this.bookmark_id = bookmark.bookmark_id;
			this.$refs.editModal.show()
		},
		editBookmark(event){
			event.preventDefault();
			if(!this.bookmark_id || !this.url_input || !this.title_input) return;
			
			this.$api
				.call(ApiBookmark.update({
					bookmark_id: this.bookmark_id,
					title: this.title_input,
					url: this.url_input,
				}))
				.then((res) => res.data)
				.then((result) => {
					this.$fhcAlert.alertInfo(this.$p.t("bookmark", "bookmarkUpdated"));
					// refetch the bookmarks to see the updates
					this.fetchBookmarks();
					// reset the values for the title and url inputs
					this.clearInputs();
					this.$refs.editModal.hide();
					this.bookmark_id = null;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},

		insertBookmark(event) {
			event.preventDefault();

			// reset is-invalid css on url input field
			for (let key of Object.keys(this.validation)) {
				this.validation[key] = false;
			}

			// early return if validation failed
			if (!this.isValidationSuccessfull()) return;

			this.$api
				.call(ApiBookmark.insert({
					tag: this.config.tag,
					title: this.title_input,
					url: this.url_input,
				}))
				.then((res) => res.data)
				.then((result) => {
					this.$fhcAlert.alertInfo(this.$p.t("bookmark", "bookmarkAdded"));
					// refetch the bookmarks to see the updates
					this.fetchBookmarks();
					this.$refs.createModal.hide();
					// reset the values for the title and url inputs
					this.clearInputs();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},

		isValidationSuccessfull() {
			// validate the input fields
			if (this.title_input.length === 0) {
				this.$fhcAlert.alertError(this.$p.t("bookmark", "invalidTitel"));
				this.validation.invalidTitel = true;
			}
			if (!URL.canParse(this.url_input)) {
				this.$fhcAlert.alertError(this.$p.t("bookmark", "invalidUrl"));
				this.validation.invalidURL = true;
			}

			return !Object.values(this.validation).some(value => value === true);
		},
		async fetchBookmarks() {
			await this.$api
				.call(ApiBookmark.getBookmarks())
				.then((res) => res.data)
				.then((result) => {
					this.shared = result;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		async removeLink(bookmark_id) {
			let isConfirmed = await this.$fhcAlert.confirmDelete();

			// early return if the confirm dialog was not confirmed
			if (!isConfirmed) return;

			this.$api
				.call(ApiBookmark.delete(bookmark_id))
				.then((res) => res.data)
				.then((result) => {
					this.$fhcAlert.alertInfo(this.$p.t("bookmark", "bookmarkDeleted"));
					// refetch the bookmarks to see the updates
					this.fetchBookmarks();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
	},
	async mounted() {
		await this.fetchBookmarks();
	},
	created() {
		// 
		// this.$emit('setConfig', true); -> use this to enable widget config mode if needed
	},
	template: /*html*/ `
    <div class="widgets-url w-100 h-100 overflow-scroll" style="padding: 1rem 1rem;">
        <div class="d-flex flex-column justify-content-between">
        	<button class="btn btn-outline-secondary btn-sm w-100 mt-2 card" @click="openCreateModal" type="button">{{$p.t('bookmark','newLink')}}</button>

            <template v-if="shared">

                <template v-if="!emptyBookmarks">
					<div v-for="link in shared" :key="link.id" class="d-flex mt-2">
						<a target="_blank" :href="link.url">
							<i class="fa fa-solid fa-arrow-up-right-from-square me-1"></i>{{ link.title }}
						</a>

						<div class="ms-auto">
							<!--EDIT BOOKMARK-->
							<a type="button" href="#" @click.prevent="openEditModal(link)" aria-label="edit bookmark" v-tooltip="{showDelay:1000,value:'edit bookmark'}">
								<i class="fa fa-edit me-1" aria-hidden="true"></i>
							</a>
							<!--DELETE BOOKMARK-->
							<a type="button" id="deleteBookmark" href="#" aria-label="delete bookmark" v-tooltip="{showDelay:1000,value:'delete bookmark'}" @click.prevent="removeLink(link.bookmark_id)">
								<i class="fa fa-regular fa-trash-can" aria-hidden="true"></i>
							</a>
						</div>
					</div>
                </template>

                <div v-else class="d-flex mt-2">
                    <span>{{$p.t('bookmark','emptyBookmarks')}}</span>
                </div>

            </template>

            <template v-else>
                <p v-for="i in 4" class="placeholder-glow">
                    <span class="placeholder" :class="{'col-9' : true}"></span>
                </p>
            </template>
        </div>
    </div>
	<!--EDIT MODAL-->
	<teleport to="body">
		<bs-modal @[\`hide.bs.modal\`]="bookmark_id=null; clearInputs();" ref="editModal">
			<template #title>
				<h2>{{$p.t('bookmark','editLink')}}</h2>
			</template>
			<template #default>
				<form-input  :label="$p.t('profil','Titel')" :title="$p.t('profil','Titel')" id="editTitle" v-model="title_input" name="title" class="mb-2"></form-input>
				<form-input label="Url" title="Url" id="editUrl" v-model="url_input" name="url"></form-input>
			</template>
			<template #footer>
				<button @click="editBookmark" class="btn btn-primary">{{$p.t('bookmark','saveLink')}}</button>
			</template>
		</bs-modal>
	</teleport>
	<!--CREATE MODAL-->
	<teleport to="body">
		<bs-modal @[\`hide.bs.modal\`]="clearInputs();" ref="createModal">
			<template #title>
				<h2>{{$p.t('bookmark','newLink')}}</h2>
			</template>
			<template #default>
				<form-input :label="$p.t('profil','Titel')" :title="$p.t('profil','Titel')" id="insertTitle" v-model="title_input" name="title" class="mb-2"></form-input>
				<form-input label="Url" title="Url" id="insertUrl" v-model="url_input" name="url"></form-input>
			</template>
			<template #footer>
				<button @click="insertBookmark" class="btn btn-primary">{{$p.t('bookmark','saveLink')}}</button>
			</template>
		</bs-modal>
	</teleport>
	`,
};

/*
Link JSON structure:
{ 
    "bookmark_id": number,
    "uid": string,
    "url": string,
    "title": string,
    "tag": string,
    "insertamum": "2024-07-30 14:33:03.699318",
    "insertvon": null,
    "updateamum": null,
    "updatevon": null
}
*/
