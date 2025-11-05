import CoreForm from "../Form/Form.js";
import FormInput from "../Form/Input.js";
import BsModal from "../Bootstrap/Modal.js";
import AbstractWidget from './Abstract.js';

import ApiBookmark from '../../api/factory/widget/bookmark.js';
import ApiDashboard from '../../api/factory/cis/dashboard.js'; //nutzt aber nicht viel

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
		tagsArray: [],
		selectedTags: [],
		newTag: null,
		selectedFilters: [], //die aber im benutzer_override speichern
		items:[],
		value: '',
		//viewData: []
		ArrayAC: [],
		selectedTagsAC: [],
		tagsInput2: [],
		filteredArrayAC: [],
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
				return Math.max(...this.shared.map(b => b.sort));
		},
		minSort(){
			if(this.shared.length == 0)
				return 0;
			else
				return Math.min(...this.shared.map(b => b.sort));
		},
		tagsInput(){
			return this.selectedTags.map(item => item.tag);
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
			//but the JSON string to array
			this.selectedTagsAC = JSON.parse(bookmark.tag);
		//	this.selectedTags = this.prepareTag(bookmark.tag);

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
					tag: this.selectedTagsAC,
					//tag: this.tagsInput, //old version
				}))
				.then((res) => res.data)
				.then((result) => {
					this.$fhcAlert.alertInfo(this.$p.t("bookmark", "bookmarkUpdated"));
					// refetch the bookmarks to see the updates
					this.fetchBookmarks();
					this.getAllBookmarkTags();
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
					//tag: this.config.tag,
					//tag: this.tagsInput, //old version
					tag: this.selectedTagsAC,
					title: this.title_input,
					url: this.url_input,
					sort: this.sort
				}))
				.then((res) => res.data)
				.then((result) => {
					this.$fhcAlert.alertInfo(this.$p.t("bookmark", "bookmarkAdded"));
					// refetch the bookmarks to see the updates
					this.fetchBookmarks();
					this.getAllBookmarkTags();
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
					this.getAllBookmarkTags();
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		sortDown(bookmark_id){
			const current = this.shared.find(b => b.bookmark_id === bookmark_id);

			const next = this.shared
				.filter(b => b.sort > current.sort)
				.sort((a, b) => a.sort - b.sort)[0];

			if (!next) {
				console.log("lowest sort item, no change");
				return;
			}
			this.changeOrder(current.bookmark_id, next.bookmark_id);
		},
		sortUp(bookmark_id){
			const current = this.shared.find(b => b.bookmark_id === bookmark_id);

			const next = this.shared
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
				this.tagsArray.push({tag: this.newTag, code: this.newTag});
				this.selectedTags.push({tag: this.newTag, code: this.newTag});
				this.newTag = null;
			}
			else
				this.$fhcAlert.alertError("Eingabe eines Zeichens erforderlich!");
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
			return Array.isArray(tags) && tags.length > 0;
		},
		prepareTag(bookmarkArr){
			// parse if string
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
					//Variante Chips
					this.tagsArray = this.prepareTag(result.data);

					//Variante Autocomplete
/*
					this.ArrayAC = result.data;
						console.log("type of " + typeof this.ArrayAC);
						console.log(Array.isArray(this.ArrayAC));*/
				//	this.ArrayAC = Object.values(result.data);
				//	this.ArrayAC = Object.keys(result.data);
						this.ArrayAC = result.data; //in object umwandeln
/*					this.ArrayAC = {...this.ArrayAC};

					console.log("type of " + typeof this.ArrayAC);
					console.log(Array.isArray(this.ArrayAC)); // true*/
/*					Array.isArray(result.data)
						? result.data
						: JSON.parse(result.data); */
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		addBookmarkFilter(){
			console.log("in addFilterLogic: save in benutzeroverride " +  this.selectedFilters.map(tag => tag.tag).join(", "));

			console.log("widget_id " + this.manu2);
			console.log("tags " + this.filterInput);

			console.log(`${this.apiurl}/dashboard/Config/insertAndUpdateTagsForBookmarksUser`, {
				db: "CIS",
				funktion_kurzbz: this.sectionName,
				widget_id: this.manu2,
				tags: this.filterInput
			});

			axios.post(this.apiurl + '/dashboard/Config/insertAndUpdateTagsForBookmarksUser', {
				db: "CIS",
				funktion_kurzbz: this.sectionName,
				widget_id: this.manu2,
				tags: this.filterInput
			})
				.then(res => {
					console.log("API Response:", res.data);
				})
				.catch(err => {
					console.error("ERROR:", err);
				});
		},
		getTagFilter(widget_id){

/*			console.log(`${this.apiurl}/dashboard/Config/getTagFilter`, {
				db: "CIS",
				funktion_kurzbz: this.sectionName,
				widget_id: widget_id
			});*/

			axios.get(this.apiurl + '/dashboard/Config/getTagFilter', {
				params: {
					db: "CIS",
					funktion_kurzbz: this.sectionName,
					widget_id: widget_id
				}
			})
				.then(res => {
				//	console.log("API Response:", res.data);

					//old version
/*					const raw = res.data?.retval?.retval?.[0]?.tags;
					const filterArray = raw ? JSON.parse(raw) : [];
					this.selectedFilters = this.prepareTag(filterArray);*/


					//unter config
					const rawTags = res.data.retval.retval[0].tags; // string
					const filterArray = JSON.parse(rawTags);
					//this.selectedFilters = res.data.retval;
					this.selectedFilters = this.prepareTag(filterArray);
				})
				.catch(err =>
					console.error('ERROR: ', err));
		},
		//Variante mit AutocompleteFeld
/*		search(event) {
			this.ArrayAC = this.ArrayAC.map((item) => item) || event;
		},*/
		search(event) {
			const query = event.query ?? ""; // PrimeVue liefert query

			// Filter ArrayAC nach eingegebenem Text
			this.filteredArrayAC = this.ArrayAC.filter(item =>
				item.toLowerCase().includes(query.toLowerCase())
			);

			// Wenn kein Treffer → manuellen Text erlauben
			if (this.filteredArrayAC.length === 0 && query) {
				this.filteredArrayAC = [query];
			}

			console.log("Filtered suggestions:", this.filteredArrayAC);

		},
/*		addCustomTag(event) {
			const value = event.value;  // das ist der vom User eingegebene Text
			console.log("Custom Tag:", value);

			// Füge den neuen Tag zu selectedTags hinzu
		//	this.selectedTags = [...this.selectedTags, value];
			const newTag = { tag: value };

			// Optional: Füge den Tag auch in ArrayAC hinzu, damit er zukünftig auswählbar ist
			if (!this.tagsArray.includes(value)) {
				this.tagsArray.push(value);
			}



		}*/
	},
	async mounted() {
		await this.fetchBookmarks();
		this.getAllBookmarkTags();
		if(this.manu2) {
			this.getTagFilter(this.manu2);
		}
/*		console.log("config " + this.config);
		console.log("widgetID " + this.widgetId);*/
	//	console.log("apiurl " + this.apiurl );
/*		console.log("widgetinfo " + this.widgetInfo);
		console.log("config " + this.config);
		console.log(JSON.stringify(this.config));
		console.log("width " + this.width);
		console.log("height " + this.height);
		console.log("configMode " + this.configMode);
		console.log("sharedData " + this.sharedData);
		console.log(JSON.stringify(this.sharedData));*/

		//console.log( this.config.map(tag => tag.tag).join(", "));
/*		console.log("manu " + this.manu);
		console.log("manu2 " + this.manu2);
		console.log("widgetID " + this.widgetID);*/
	},
	created() {
		// 
		// this.$emit('setConfig', true); // -> use this to enable widget config mode if needed

		/*

		widgetId: {{widgetId}}
		    	{{manu2}} {{sectionName}}
		 */
	},


	template: /*html*/ `
    <div class="widgets-url w-100 h-100 overflow-auto" style="padding: 1rem 1rem;">


		<div class="d-flex flex-column justify-content-between">
        
        <!--TODO Manu eigener button mit modal für Filter, Autocomplete Variante !-->
			<!-- div class="mt-2 row">
				<div class="col-10">
					<PvMultiSelect
						v-model="selectedFilters"
						id="tagFilterUrl"
						:options="tagsArray"
						optionLabel="tag"
						display="chip"
						placeholder="Show only tags"
						:maxSelectedLabels="3"
						class="p-inputtext-sm w-100 me-2"
						/>
				</div>
				<div class="col-1">
					<button
						class="btn btn-secondary"
						@click="addBookmarkFilter"
						title="nach Tags filtern"
						>
						<i class="fa-solid fa-filter"></i>
					</button>
				</div>
			</div -->
		</div>
		<button class="btn btn-outline-secondary btn-sm w-100 mt-2 card" @click="openCreateModal" type="button">{{$p.t('bookmark','newLink')}}</button>

            <template v-if="shared">
                <template v-if="!emptyBookmarks">
					<div v-for="link in shared" :key="link.id" class="d-flex mt-2">
						<a target="_blank" :href="link.url" class="me-1">
							<i class="fa fa-solid fa-arrow-up-right-from-square me-1"></i>{{ link.title }}
						</a>
						<span
							v-if="hasTags(link)"
							 :title="link.tag"
							 style="color: silver;"
							>
								<i class="fa fa-solid fa-tag text-gray-500" aria-hidden="true"></i>
							</span>

						<div class="ms-auto">
							<!--EDIT BOOKMARK-->
							<a type="button" href="#" @click.prevent="openEditModal(link)" aria-label="edit bookmark" v-tooltip="{showDelay:1000,value:'edit bookmark'}">
								<i class="fa fa-edit me-1" aria-hidden="true"></i>
							</a>
							<!--DELETE BOOKMARK-->
							<a type="button" id="deleteBookmark" href="#" aria-label="delete bookmark" v-tooltip="{showDelay:1000,value:'delete bookmark'}" @click.prevent="removeLink(link.bookmark_id)">
								<i class="fa fa-regular fa-trash-can" aria-hidden="true"></i>
							</a>
							<!--SORT BOOKMARKS-->
							<a
								v-if="shared.length > 1"
								type="button"
								id="downsortBookmark"
								href="#"
								aria-label="sortdown bookmark"
								v-tooltip="{showDelay:1000,value:'sort down bookmark'}"
								@click.prevent="sortDown(link.bookmark_id)"
								>
								<i :class="[ 'fa', 'fa-arrow-down', 'me-1', link.sort === maxSort ? 'text-light pointer-events-none' : '' ]"></i>
							</a>
							<a
								v-if="shared.length > 1"
								type="button"
								id="upsortBookmark"
								href="#"
								aria-label="sortup bookmark"
								v-tooltip="{showDelay:1000,value:'sort up bookmark'}"
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

				<form-input  :label="$p.t('profil','Titel')" :title="$p.t('profil','Titel')" id="editTitle" v-model="title_input" name="title" class="mb-2"></form-input>
				<form-input label="Url" title="Url" id="editUrl" v-model="url_input" name="url"></form-input>

				<label class="mt-2">Tags</label>
				<div class="mt-2">
					<PvAutoComplete
						v-model="selectedTagsAC"
						multiple
						dropdown
						:suggestions="filteredArrayAC"
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
						v-model="selectedTagsAC"
						multiple
						dropdown
						:suggestions="filteredArrayAC"
						@complete="search" 
					/>
				</div>					

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
