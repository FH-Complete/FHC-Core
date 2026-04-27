import CoreForm from "../Form/Form.js";
import FormInput from "../Form/Input.js";
import BsModal from "../Bootstrap/Modal.js";
import AbstractWidget from './Abstract.js';

import { useUrlStore } from '../../composables/Pseudostore/DashboardWidget/UrlStore.js';

export default {
	name: "WidgetsUrl",
	mixins: [AbstractWidget],
	inject: {
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
		validation: {
			invalidURL: false,
			invalidTitel: false,
		},
		selectedTags: [],
		newTag: null,
		selectedFilters: [],
		filter: [],
		filteredArray: []
	}),
	computed: {
		availableTags() {
			return (this.bookmarks || [])
				.map(bookmark => JSON.parse(bookmark.tag))
				.flat()
				.filter((v, i, a) => v && a.indexOf(v) === i);
		},
		filteredBookmarks() {
			if (!this.bookmarks)
				return [];

			if (!this.config.tags || !this.config.tags.length)
				return this.bookmarks;

			return this.bookmarks.filter(bookmark => {
				const tags = JSON.parse(bookmark.tag || "[]");
				return tags.some(tag => this.config.tags.includes(tag));
			});
		},
		newSort(){
			if(this.bookmarks.length == 0)
				return 1;
			else
				return Math.max(...this.bookmarks.map(b => b.sort)) + 1;
		},
		maxSort(){
			if(this.bookmarks.length == 0)
				return 0;
			else
				return Math.max(...this.filteredBookmarks.map(b => b.sort));
		},
		minSort(){
			if(this.bookmarks.length == 0)
				return 0;
			else
				return Math.min(...this.filteredBookmarks.map(b => b.sort));
		}
	},
	methods: {
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

			this.actions
				.update(
					this.bookmark_id,
					this.title_input,
					this.url_input,
					this.selectedTags
				)
				.then(() => {
					this.$fhcAlert.alertInfo(this.$p.t("bookmark", "bookmarkUpdated"));
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
			const sort = this.newSort;

			this.actions
				.insert(
					this.title_input,
					this.url_input,
					this.selectedTags,
					sort
				)
				.then(() => {
					this.$fhcAlert.alertInfo(this.$p.t("bookmark", "bookmarkAdded"));
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
		async removeLink(bookmark_id) {
			let isConfirmed = await this.$fhcAlert.confirmDelete();

			// early return if the confirm dialog was not confirmed
			if (!isConfirmed) return;

			this.actions
				.remove(bookmark_id)
				.then(() => {
					this.$fhcAlert.alertInfo(this.$p.t("bookmark", "bookmarkDeleted"));
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		sortDown(bookmark_id){
			const current = this.filteredBookmarks.find(b => b.bookmark_id === bookmark_id);

			const next = this.filteredBookmarks
				.filter(b => b.sort > current.sort)
				.sort((a, b) => a.sort - b.sort)[0];

			if (!next) {
				console.log("lowest sort item, no change");
				return;
			}
			this.changeOrder(current.bookmark_id, next.bookmark_id);
		},
		sortUp(bookmark_id){
			const current = this.filteredBookmarks.find(b => b.bookmark_id === bookmark_id);

			const next = this.filteredBookmarks
				.filter(b => b.sort < current.sort)
				.sort((a, b) => a.sort + b.sort)[0];

			if (!next) {
				console.log("highest sort item, no change");
				return;
			}
			this.changeOrder(current.bookmark_id, next.bookmark_id);
		},
		changeOrder(bookmark_id_1, bookmark_id_2) {
			this.actions.swap(bookmark_id_1, bookmark_id_2);
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
		openFilterModal() {
			if (this.config.tags && this.config.tags.length)
				this.selectedFilters = [ ...this.config.tags ];
			else
				this.selectedFilters = [];
			this.$refs.filterModal.show();
		},
		async handleAddingTagFilter(widgetId) {
			this.config.tags = this.selectedFilters;
			this.$emit('change');
			this.$fhcAlert.alertInfo(this.$p.t("bookmark", "filterUpdated"));
			this.$refs.filterModal.hide();
		},
		search(event) {
			const query = event.query ?? "";

			// Filter for text
			this.filteredArray = this.availableTags.filter(item =>
				item.toLowerCase().includes(query.toLowerCase())
			);

			// input if search not successful
			if (this.filteredArray.length === 0 && query) {
				this.filteredArray = [query];
			}
		},
	},
	setup() {
		const {
			bookmarks,
			getters: { tags },
			actions
		} = useUrlStore();

		return { bookmarks, tags, actions }
	},
	async mounted() {
		this.actions.fetch();
	},
	template: /*html*/ `
	<div class="widgets-url w-100 h-100 overflow-auto p-3">

		<div class="d-flex mt-2">
			<button
				class="btn btn-outline-secondary btn-sm flex-grow-1 me-2"
				@click="openCreateModal"
			>
				{{ $p.t('bookmark', 'newLink') }}
			</button>
			<button
				v-if="config.tags && config.tags.length"
				class="btn btn-secondary btn-sm"
				:title="$p.t('bookmark/editFilter')"
				@click="openFilterModal"
			>
				<i class="fa-solid fa-filter-circle-xmark"></i>
			</button>  
			<button
				v-else
				class="btn btn-outline-secondary btn-sm"
				:title="$p.t('bookmark/filterByTags')"
				@click="openFilterModal"
			>
				<i class="fa-solid fa-filter"></i>
			</button>
		</div>

		<template v-if="filteredBookmarks.length">
			<div
				v-for="link in filteredBookmarks"
				:key="link.id"
				class="d-flex mt-2"
			>
				<a target="_blank" :href="link.url" class="me-1">
					<i
						class="fa fa-solid fa-arrow-up-right-from-square me-1"
						aria-hidden="true"
					></i>
					{{ link.title }}
				</a>
				<span
					v-if="hasTags(link)"
					:title="hasTags(link)"
					style="color: silver;"
				>
					<i
						class="fa fa-solid fa-tag text-gray-500"
						aria-hidden="true"
					></i>
				</span>

				<div class="ms-auto">
					<!--EDIT BOOKMARK-->
					<a
						type="button"
						href="#"
						@click.prevent="openEditModal(link)"
						aria-label="edit bookmark"
						:title="$p.t('bookmark/editBookmark')"
					>
						<i class="fa fa-edit me-1" aria-hidden="true"></i>
					</a>
					<!--DELETE BOOKMARK-->
					<a
						type="button"
						id="deleteBookmark"
						href="#"
						aria-label="delete bookmark"
						:title="$p.t('bookmark/deleteBookmark')"
						@click.prevent="removeLink(link.bookmark_id)"
					>
						<i class="fa fa-regular fa-trash-can" aria-hidden="true"></i>
					</a>
					<!--SORT BOOKMARKS-->
					<a
						v-if="filteredBookmarks.length > 1"
						type="button"
						id="downsortBookmark"
						href="#"
						aria-label="sortdown bookmark"
						:title="$p.t('bookmark/sortDownwards')"
						@click.prevent="sortDown(link.bookmark_id)"
					>
						<i
							class="fa fa-arrow-down me-1"
							:class="{ 'text-light pointer-events-none': link.sort === maxSort }"
						></i>
					</a>
					<a
						v-if="filteredBookmarks.length > 1"
						type="button"
						id="upsortBookmark"
						href="#"
						aria-label="sortup bookmark"
						:title="$p.t('bookmark/sortToTop')"
						@click.prevent="sortUp(link.bookmark_id)"
					>
						<i
							class="fa fa-arrow-up me-1"
							:class="{ 'text-light pointer-events-none': link.sort === minSort }"
						></i>
					</a>
				</div>
			</div>
		</template>

		<div v-else class="d-flex mt-2">
			<span>{{ $p.t('bookmark', 'emptyBookmarks') }}</span>
		</div>

		<template v-else>
			<p v-for="i in 4" class="placeholder-glow">
				<span class="placeholder col-9"></span>
			</p>
		</template>
	</div>
	<!--EDIT MODAL-->
	<teleport to="body">
		<bs-modal
			ref="editModal"
			@hide-bs-modal="bookmark_id=null; clearInputs();"
		>
			<template #title>
				<h2>{{ $p.t('bookmark', 'editLink') }}</h2>
			</template>
			<template #default>

				<form-input
					:label="$p.t('profil', 'Titel')"
					:title="$p.t('profil','Titel')"
					id="editTitle"
					v-model="title_input"
					name="title"
					class="mb-2"
				></form-input>
				<form-input
					label="Url"
					title="Url"
					id="editUrl"
					v-model="url_input"
					name="url"
				></form-input>

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
				<button @click="editBookmark" class="btn btn-primary">
					{{ $p.t('bookmark', 'saveLink') }}
				</button>
			</template>
		</bs-modal>
	</teleport>
	<!--CREATE MODAL-->
	<teleport to="body">
		<bs-modal ref="createModal" @hide-bs-modal="clearInputs();">
			<template #title>
				<h2>{{ $p.t('bookmark', 'newLink') }}</h2>
			</template>
			<template #default>

				<form-input
					:label="$p.t('profil', 'Titel')"
					:title="$p.t('profil', 'Titel')"
					id="insertTitle"
					v-model="title_input"
					name="title"
					class="mb-2"
				></form-input>
				<form-input
					label="Url"
					title="Url"
					id="insertUrl"
					v-model="url_input"
					name="url"
				></form-input>

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
				<button @click="insertBookmark" class="btn btn-primary">
					{{ $p.t('bookmark', 'saveLink') }}
				</button>
			</template>
		</bs-modal>
	</teleport>
	<!--FILTER MODAL-->
	<teleport to="body">
		<bs-modal ref="filterModal" @hide-bs-modal="clearInputs();">
			<template #title>
				<h2>{{ $p.t('bookmark', 'headerFilterBookmark') }}</h2>
			</template>
			<template #default>

				<div class="mt-2 row">
					<div class="col-10">
						<PvMultiSelect
							v-model="selectedFilters"
							id="tagFilterUrl"
							:options="availableTags"
							display="chip"
							:placeholder="$p.t('bookmark', 'noFilter')"
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
					:title="$p.t('bookmark', 'filterByTags')"
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
