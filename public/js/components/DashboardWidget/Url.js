import AbstractWidget from './Abstract';

export default {
    name: 'WidgetsUrl',
    data: () => ({
        links: null,
        title_input: '',
        url_input: '',
        isPhrasenLoaded: false,
    }),
    mixins: [
        AbstractWidget
    ],
    computed: {
        tagName() {
            return this.config.tag !== undefined && this.config.tag.length > 0 ? this.config.tag : 'Meine Urls';
        }
    },
    methods: {
        async fetchBookmarks(){
            await this.$fhcApi.factory.bookmark.getBookmarks()
            .then(res => res.data)
            .then(result => {
                this.links = result;
            })
            .catch();
        },
        async confirmDelete() {
            if (await this.$fhcAlert.confirmDelete() === false)
                return;
        },
        addLink(){
            //let linkId = this.links.length;

            this.$fhcApi.factory.bookmark.insertBookmark({
                tag: this.config.tag,
                title: this.title_input,
                url: this.url_input
                })
                .then(res => res.data)
                .then(result => {
                    this.$fhcAlert.alertInfo("bookmark added");
                })
                .catch();

            // reset the values for the title and url inputs
            this.title_input = '';
            this.url_input = '';

            // refetch the bookmarks to see the updates
            this.fetchBookmarks();

        },
        async removeLink(bookmark_id){
            await this.confirmDelete();
            this.$fhcApi.factory.bookmark.deleteBookmark(bookmark_id)
            .then(res => res.data)
            .then(result => {
                this.$fhcAlert.alertInfo(this.$p.t('bookmark','bookmarkDeleted'));
            })
            .catch();

            // refetch the bookmarks to see the updates
            this.fetchBookmarks();
        }
    },
    
    created() {
        this.$p.loadCategory('bookmark').then(()=>{
            this.isPhrasenLoaded = true;
        })
        //this.links = TEST_LINKS;
        // this.links = TEST_KEINE_LINKS;
    },
    async mounted(){
        await this.fetchBookmarks();
    },
    template: /*html*/`
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
            <template v-if="links">
                <header><b>{{ widgetTag }}</b></header>
                <div v-for="link in links" :key="link.id" class="d-flex mt-2">
                    <a target="_blank" :href="link.url"><i class="fa fa-solid fa-arrow-up-right-from-square"></i> {{ link.title }}</a>
                    <a class="ms-auto" href="#" @click.prevent="removeLink(link.bookmark_id)" v-show="configMode"><i class="fa fa-regular fa-trash-can"></i></a>
                </div>
            </template>
            <template v-else>
                <p v-for="i in 4" class="placeholder-glow">
                    <span class="placeholder" :class="{'col-9' : true}"></span>
                </p>
            </template>
        </div>
    </div>`
}

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


const TEST_KEINE_LINKS = [];
const TEST_LINKS = [
    {
        id: 0,
        tag: 'Zeitverwaltung',
        title: 'Zeitverwaltung' + 'link 0',
        url: 'https://www.technikum-wien.at'
    },
    {
        id: 1,
        tag: 'Zeitverwaltung',
        title: 'Zeitverwaltung' + 'link 1',
        url: 'https://www.technikum-wien.at'
    },
    {
        id: 2,
        tag: 'Zeitverwaltung',
        title: 'Zeitverwaltung' + 'link 2',
        url: 'https://www.technikum-wien.at'
    },
    {
        id: 3,
        tag: 'Zeitverwaltung',
        title: 'Zeitverwaltung' + 'link 3',
        url: 'https://www.technikum-wien.at'
    }
];