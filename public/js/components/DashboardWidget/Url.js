import AbstractWidget from './Abstract';

export default {
  name: "WidgetsUrl",
  data: () => ({
    title_input: "",
    url_input: "",
    isPhrasenLoaded: false,
  }),
  mixins: [AbstractWidget],
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
    async fetchBookmarks() {
      await this.$fhcApi.factory.bookmark
        .getBookmarks()
        .then((res) => res.data)
        .then((result) => {
          this.shared = result;
        })
        .catch();
    },
    async confirmDelete() {
      if ((await this.$fhcAlert.confirmDelete()) === false) return;
    },
    addLink() {
      this.$fhcApi.factory.bookmark
        .insertBookmark({
          tag: this.config.tag,
          title: this.title_input,
          url: this.url_input,
        })
        .then((res) => res.data)
        .then((result) => {
          this.$fhcAlert.alertInfo("bookmark added");
        })
        .catch();

      // reset the values for the title and url inputs
      this.title_input = "";
      this.url_input = "";

      // refetch the bookmarks to see the updates
      this.fetchBookmarks();
    },
    async removeLink(bookmark_id) {
      await this.confirmDelete();
      this.$fhcApi.factory.bookmark
        .deleteBookmark(bookmark_id)
        .then((res) => res.data)
        .then((result) => {
          this.$fhcAlert.alertInfo(this.$p.t("bookmark", "bookmarkDeleted"));
        })
        .catch();

      // refetch the bookmarks to see the updates
      this.fetchBookmarks();
    },
  },

  created() {
    this.$p.loadCategory("bookmark").then(() => {
      this.isPhrasenLoaded = true;
    });
  },
  async mounted() {
    await this.fetchBookmarks();
  },
  template: /*html*/ `
    <div class="widgets-url w-100 h-100">
        <div v-if="configMode">
            <div class="mb-3">

                <header><b>{{$p.t('bookmark','newLink')}}</b></header><br>
                <div>
                    <input class="form-control form-control-sm" placeholder="Titel" type="text" v-model="title_input" name="title" maxlength="50" required>
                    <input class="form-control form-control-sm mt-2" type="url" placeholder="URL" v-model="url_input" name="url" required>
                    <button class="btn btn-outline-secondary btn-sm w-100 mt-2" @click="addLink" type="button">{{$p.t('bookmark','saveLink')}}</button>
                </div>
            </div>
        </div>
        <div class="d-flex flex-column justify-content-between">
        <!-- todo: widgetTag ?? -->
            <template v-if="shared">
                <header><b>{{ tagName }}</b></header>
                <div v-if="!emptyBookmarks" v-for="link in shared" :key="link.id" class="d-flex mt-2">
                    <a target="_blank" :href="link.url"><i class="fa fa-solid fa-arrow-up-right-from-square"></i> {{ link.title }}</a>
                    <a class="ms-auto" href="#" @click.prevent="removeLink(link.bookmark_id)" v-show="configMode"><i class="fa fa-regular fa-trash-can"></i></a>
                </div>
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
