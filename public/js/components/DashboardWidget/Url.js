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
		},
		sectionName: {
			type: String,
			default: false
		}
	},
	components:{
		CoreForm,
		FormInput,
		BsModal,
		PvChips: primevue.chips,
		PvMultiSelect: primevue.multiselect,
		PvAutoComplete: primevue.autocomplete,
	},
	data: () => ({
		bookmark_id: null,
		title_input: "",
		url_input: "",
		sort: null,
		validation: {
			invalidURL: false,
			invalidTitel: false,
		},
		tagsArrayMS: [],
		tagsArrayAC: [],
		selectedTags: [],
		newTag: null,
		selectedFilters: [],
		filter: [],
		filteredArray: [],
		sharedFiltered: {},
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
		newSort(){
			if(this.shared.length == 0)
				return 1;
			else
				return Math.max(...this.shared.map(b => b.sort)) + 1;
		},
		maxSort(){
			if(this.shared.length == 0)
				return 0;
			else
				return Math.max(...this.sharedFiltered.map(b => b.sort));
		},
		minSort(){
			if(this.shared.length == 0)
				return 0;
			else
				return Math.min(...this.sharedFiltered.map(b => b.sort));
		},
		filterInput(){
			return this.selectedFilters.map(item => item.tag);
		},
	},
	methods: {
		stopDrag(event){
			event.preventDefault();
		},
		clearInputs(){
			this.title_input = "";
			this.url_input = "";
			this.selectedTags = [];
		},
		openCreateModal() {
			this.$refs.createModal.show()
		},
		openEditModal(bookmark) {
			this.title_input = bookmark.title;
			this.url_input = bookmark.url;
			this.bookmark_id = bookmark.bookmark_id;
			this.selectedTags = JSON.parse(bookmark.tag);

			this.$refs.editModal.show();
		},
		editBookmark(event){
			event.preventDefault();
			if(!this.bookmark_id || !this.url_input || !this.title_input) return;

			this.$api
				.call(ApiBookmark.update({
					bookmark_id: this.bookmark_id,
					title: this.title_input,
					url: this.url_input,
					tag: this.selectedTags,
				}))
				.then((res) => res.data)
				.then((result) => {
					this.$fhcAlert.alertInfo(this.$p.t("bookmark", "bookmarkUpdated"));
					// refetch the bookmarks to see the updates
					this.getAllBookmarkTags();
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

			// get highest Sort
			this.sort = this.newSort;

			this.$api
				.call(ApiBookmark.insert({
					tag: this.selectedTags,
					title: this.title_input,
					url: this.url_input,
					sort: this.sort
				}))
				.then((res) => res.data)
				.then((result) => {
					this.$fhcAlert.alertInfo(this.$p.t("bookmark", "bookmarkAdded"));
					// refetch the bookmarks to see the updates
					this.getAllBookmarkTags();
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

					this.$nextTick(() => {
						this.sharedFiltered = this.filterBookmarksByTags(this.shared);
					});
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
					this.getAllBookmarkTags();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		filterBookmarksByTags(bookmarks) {
			const filter = this.filter;
			if (!filter || filter.length === 0 || filter == "[]") return bookmarks;

			return bookmarks.filter(b => {
				const tags = JSON.parse(b.tag || "[]");
				return tags.some(tag => filter.includes(tag));
			});
		},
		sortDown(bookmark_id){
			const current = this.sharedFiltered.find(b => b.bookmark_id === bookmark_id);

			const next = this.sharedFiltered
				.filter(b => b.sort > current.sort)
				.sort((a, b) => a.sort - b.sort)[0];

			if (!next) {
				console.log("lowest sort item, no change");
				return;
			}
			this.changeOrder(current.bookmark_id, next.bookmark_id);
		},
		sortUp(bookmark_id){
			const current = this.sharedFiltered.find(b => b.bookmark_id === bookmark_id);

			const next = this.sharedFiltered
				.filter(b => b.sort < current.sort)
				.sort((a, b) => a.sort + b.sort)[0];

			if (!next) {
				console.log("highest sort item, no change");
				return;
			}
			this.changeOrder(current.bookmark_id, next.bookmark_id);
		},
		addNewTag(){
			if(this.newTag != null && this.newTag.length) {
				this.tagsArrayMS.push({tag: this.newTag, code: this.newTag});
				this.selectedTags.push({tag: this.newTag, code: this.newTag});
				this.newTag = null;
			}
			else
				this.$fhcAlert.alertError(this.$p.t("bookmark", "errorInputNecessary"));
		},
		changeOrder(bookmark_id_1, bookmark_id_2){
			this.$api
				.call(ApiBookmark.changeOrder(bookmark_id_1, bookmark_id_2))
				.then((res) => res.data)
				.then((result) => {
					// refetch the bookmarks to see the updates
					this.fetchBookmarks();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		hasTags(link) {
			if (!link || !link.tag) return false;

			let tags = link.tag;
			if (typeof tags === 'string') {
				try {
					tags = JSON.parse(tags)
				} catch {
					return false;
				}
			}
			if (Array.isArray(tags) && tags.length > 0) {
				return tags.join(' ');
			}
		},
		prepareTag(bookmarkArr){
			const parsedArr = Array.isArray(bookmarkArr)
				? bookmarkArr
				: JSON.parse(bookmarkArr);

			const result = parsedArr.map(tag => ({
				tag,
				code: tag
			}));

			return result;
		},
		getAllBookmarkTags(){
			this.$api
				.call(ApiBookmark.getAllBookmarkTags())
				.then((res) => res.data)
				.then((result) => {
					//Version Chips
					this.tagsArrayMS = this.prepareTag(result.data);

					//Version Autocomplete
					this.tagsArrayAC = result.data;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		openFilterModal(){
			this.$refs.filterModal.show();
		},
		async handleAddingTagFilter(widgetId){

			const result = await this.isInOverride(widgetId);

			if (!result) {
				return;
			}
			const [status, reason] = result;

			if (status) {
				this.addTagFilter(widgetId);
			} else {
				this.addWidgetToOverride(widgetId, reason);
			}
		},
		addTagFilter(widgetId){
			this.$api
				.call(ApiBookmark.addTagFilter(widgetId, this.sectionName, this.filterInput))
				.then((res) => res.data)
				.then((result) => {
					this.$fhcAlert.alertInfo(this.$p.t("bookmark", "filterUpdated"));
					this.$refs.filterModal.hide();
					this.getTagFilter(this.widgetId);

					this.$nextTick(() => {
						this.getAllBookmarkTags();
					});
					this.fetchBookmarks();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		async isInOverride(widgetId) {
			try {
				const res = await this.$api.call(ApiBookmark.isInOverride(widgetId, this.sectionName));
				const result = res.data;
				return result;
			} catch (err) {
				this.$fhcAlert.handleSystemError(err);
				return null;
			}
		},
		addWidgetToOverride(widgetId, reason){
			this.$api
				.call(ApiBookmark.addWidgetToOverride(
					widgetId,
					this.sectionName,
					reason,
					this.item_data.x,
					this.item_data.y,
					this.item_data.h,
					this.item_data.w
				))
				.then((res) => res.data)
				.then((result) => {
					this.addTagFilter(widgetId);
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		getTagFilter(widget_id){
			this.$api
				.call(ApiBookmark.getTagFilter(widget_id, this.sectionName))
				.then((res) => res.data)
				.then((result) => {
					const rawTags = result.tags; // string
					this.filter = rawTags;
					if(rawTags != null) {
						this.rawTagsParsed = JSON.parse(rawTags);
						this.selectedFilters = this.prepareTag(this.rawTagsParsed);
					}
					this.fetchBookmarks();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		search(event) {
			const query = event.query ?? "";

			// Filter for text
			this.filteredArray = this.tagsArrayAC.filter(item =>
				item.toLowerCase().includes(query.toLowerCase())
			);

			// input if search not successful
			if (this.filteredArray.length === 0 && query) {
				this.filteredArray = [query];
			}
		},
	},
	async mounted() {
		if(this.widgetId) {
			this.getTagFilter(this.widgetId);
		}
		await this.fetchBookmarks();
		this.getAllBookmarkTags();
	},
	created() {
		// this.$emit('setConfig', true); // -> use this to enable widget config mode if needed
	},


	template: /*html*/ `
    <div class="widgets-url w-100 h-100 overflow-auto" style="padding: 1rem 1rem;">

		<div class="d-flex mt-2">
		  <button class="btn btn-outline-secondary btn-sm flex-grow-1 me-2" @click="openCreateModal">
			{{$p.t('bookmark','newLink')}}
		  </button>
		  <button v-if="selectedFilters.length" class="btn btn-secondary btn-sm" :title="this.$p.t('bookmark/editFilter')" @click="openFilterModal">
			<i class="fa-solid fa-filter-circle-xmark"></i>
		  </button>  
		  <button v-else class="btn btn-outline-secondary btn-sm" :title="this.$p.t('bookmark/filterByTags')" @click="openFilterModal">
			<i class="fa-solid fa-filter"></i>
		  </button>
		</div>

            <template v-if="sharedFiltered">

                <template v-if="!emptyBookmarks">
					<div v-for="link in sharedFiltered" :key="link.id" class="d-flex mt-2">
						<a target="_blank" :href="link.url" class="me-1">
							<i class="fa fa-solid fa-arrow-up-right-from-square me-1"></i>{{ link.title }}
						</a>
						<span
							v-if="hasTags(link)"
							 :title="hasTags(link)"
							 style="color: silver;"
							>
								<i class="fa fa-solid fa-tag text-gray-500" aria-hidden="true"></i>
							</span>

						<div class="ms-auto">
							<!--EDIT BOOKMARK-->
							<a type="button" href="#" @click.prevent="openEditModal(link)" aria-label="edit bookmark" :title="this.$p.t('bookmark/editBookmark')">
								<i class="fa fa-edit me-1" aria-hidden="true"></i>
							</a>
							<!--DELETE BOOKMARK-->
							<a type="button" id="deleteBookmark" href="#" aria-label="delete bookmark" :title="this.$p.t('bookmark/deleteBookmark')" @click.prevent="removeLink(link.bookmark_id)">
								<i class="fa fa-regular fa-trash-can" aria-hidden="true"></i>
							</a>
							<!--SORT BOOKMARKS-->
							<a
								v-if="sharedFiltered.length > 1"
								type="button"
								id="downsortBookmark"
								href="#"
								aria-label="sortdown bookmark"
								:title="this.$p.t('bookmark/sortDownwards')"
								@click.prevent="sortDown(link.bookmark_id)"
								>
								<i :class="[ 'fa', 'fa-arrow-down', 'me-1', link.sort === maxSort ? 'text-light pointer-events-none' : '' ]"></i>
							</a>
							<a
								v-if="sharedFiltered.length > 1"
								type="button"
								id="upsortBookmark"
								href="#"
								aria-label="sortup bookmark"
								:title="this.$p.t('bookmark/sortToTop')"
								@click.prevent="sortUp(link.bookmark_id)"
								>
								<i :class="[ 'fa', 'fa-arrow-up', 'me-1', link.sort === minSort ? 'text-light pointer-events-none' : '' ]"></i>
							</a>
						</div>
					</div>
                </template>

                <div v-else class="d-flex mt-2">
                    <span>{{$p.t('bookmark','emptyBookmarks')}}</span>
                </div>


            <template v-else>
                <p v-for="i in 4" class="placeholder-glow">
                    <span class="placeholder" :class="{'col-9' : true}"></span>
                </p>
            </template>
            
         </template>
	</div>
	<!--EDIT MODAL-->
	<teleport to="body">
		<bs-modal @[\`hide.bs.modal\`]="bookmark_id=null; clearInputs();" ref="editModal">
			<template #title>
				<h2>{{$p.t('bookmark','editLink')}}</h2>
			</template>
			<template #default>

				<form-input :label="$p.t('profil','Titel')" :title="$p.t('profil','Titel')" id="editTitle" v-model="title_input" name="title" class="mb-2"></form-input>
				<form-input label="Url" title="Url" id="editUrl" v-model="url_input" name="url"></form-input>

				<label class="mt-2">Tags</label>
				<div class="mt-2">
					<PvAutoComplete
						v-model="selectedTags"
						multiple
						dropdown
						:suggestions="filteredArray"
						@complete="search" 
						/>
				</div>				
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

				<label class="mt-2">Tags</label>
				<div class="mt-2">
					<PvAutoComplete
						v-model="selectedTags"
						multiple
						dropdown
						:suggestions="filteredArray"
						@complete="search" 
					/>
				</div>					

			</template>
			<template #footer>
				<button @click="insertBookmark" class="btn btn-primary">{{$p.t('bookmark','saveLink')}}</button>
			</template>
		</bs-modal>
	</teleport>
	<!--FILTER MODAL-->
	<teleport to="body">
		<bs-modal @[\`hide.bs.modal\`]="clearInputs();" ref="filterModal">
			<template #title>
				<h2>{{$p.t('bookmark','headerFilterBookmark')}}</h2>
			</template>
			<template #default>

			<div class="mt-2 row">
				<div class="col-10">
					<PvMultiSelect
						v-model="selectedFilters"
						id="tagFilterUrl"
						:options="tagsArrayMS"
						optionLabel="tag"
						display="chip"
						:placeholder="$p.t('bookmark','noFilter')"
						:maxSelectedLabels="3"
						class="p-inputtext-sm w-100 me-2"
						/>
				</div>
			</div>				

			</template>
			<template #footer>
				<button
					class="btn btn-secondary"
					@click="handleAddingTagFilter(widgetId)"
					:title="$p.t('bookmark','filterByTags')"
					>
					OK
				</button>
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
