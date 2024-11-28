import AbstractWidget from './Abstract';

export default {
	name: "WidgetsUrl",
	mixins: [AbstractWidget],
	inject: {
		editModeIsActive: {
			type: Boolean,
			default: false
		}
	},
	data: () => ({
		title_input: "",
		url_input: "",
		validation: {
			invalidURL: false,
			invalidTitel: false,
		}
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
			await this.$fhcApi.factory.bookmark
				.getBookmarks()
				.then((res) => res.data)
				.then((result) => {
					this.shared = result;
				})
				.catch(this.$fhcAlert.handleSystemError);
		},
		addLink() {
			// reset is-invalid css on url input field
			for (let key of Object.keys(this.validation)) {
				this.validation[key] = false;
			}

			// early return if validation failed
			if (!this.isValidationSuccessfull()) return;

			this.$fhcApi.factory.bookmark
				.insert({
					tag: this.config.tag,
					title: this.title_input,
					url: this.url_input,
				})
				.then((res) => res.data)
				.then((result) => {
					this.$fhcAlert.alertInfo(this.$p.t("bookmark", "bookmarkAdded"));
					// refetch the bookmarks to see the updates
					this.fetchBookmarks();
				})
				.catch(this.$fhcAlert.handleSystemError);

			// reset the values for the title and url inputs
			this.title_input = "";
			this.url_input = "";
		},
		async removeLink(bookmark_id) {
			let isConfirmed = await this.$fhcAlert.confirmDelete();

			// early return if the confirm dialog was not confirmed
			if (!isConfirmed) return;

			this.$fhcApi.factory.bookmark
				.delete(bookmark_id)
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
    <div class="widgets-url w-100 h-100" style="padding: 1rem 1rem;">
        <div class="d-flex flex-column justify-content-between">
        <!-- todo: widgetTag ?? -->
            <template v-if="shared">
                <template v-if="!emptyBookmarks">
					<div v-for="link in shared" :key="link.id" class="d-flex mt-2">
						<a target="_blank" :href="link.url">
							<i class="fa fa-solid fa-arrow-up-right-from-square"></i>{{ link.title }}
						</a>
						<a class="ms-auto" href="#" @click.prevent="removeLink(link.bookmark_id)" v-show="configMode || editModeIsActive">
							<i class="fa fa-regular fa-trash-can" style="color: #e01b24;"></i>
						</a>
					</div>
                </template>
                <div v-else class="d-flex mt-2">
                    <span>{{$p.t('bookmark','emptyBookmarks')}}</span>
                </div>
                <div v-if="editModeIsActive " class="mt-2">
                	<div class="form-group">
						<input maxlength="255" required class="form-control form-control-sm" :class="{'is-invalid':validation.invalidTitel}" placeholder="Titel" type="text" v-model="title_input" name="title" >
						<!-- validation html for titel -->
						<div class="invalid-feedback">
							{{$p.t("bookmark", "invalidTitel")}}.
						</div>
					</div>

					<div class="form-group">
						<input required id="bookmark_link" class="form-control form-control-sm mt-2" :class="{'is-invalid':validation.invalidURL}" type="url" placeholder="URL" v-model="url_input" name="url">
						<!-- validation html for url -->
						<div class="invalid-feedback">
							{{$p.t("bookmark", "invalidUrl")}}.
						</div>
					</div>

                    <button class="btn btn-outline-secondary btn-sm w-100 mt-2" @click="addLink" type="button">{{$p.t('bookmark','saveLink')}}</button>
				</div>
            </template>
            <template v-else>
                <p v-for="i in 4" class="placeholder-glow">
                    <span class="placeholder" :class="{'col-9' : true}"></span>
                </p>
            </template>
        </div>
    </div>`,
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
