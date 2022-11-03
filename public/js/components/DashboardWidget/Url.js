import AbstractWidget from './Abstract';

export default {
    name: 'WidgetsUrl',
    data: () => ({
        links: []
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
        addLink(){
            let linkId = this.links.length;

            this.links.push({
                id: linkId,
                tag: this.config.tag,
                title: this.title,
                url: this.url
                })
        },
        removeLink(linkId){
            let indexToRemove = this.links.findIndex((obj => obj.id === linkId));
            this.links.splice(indexToRemove, 1);
        }
    },
    created() {
        this.links = TEST_LINKS;
        // this.links = TEST_KEINE_LINKS;
    },
    template: `
    <div class="widgets-url w-100 h-100">
        <div v-if="configMode">
            <div class="mb-3">

                <header><b>Neuer Link</b></header><br>
                <div>
                    <input class="form-control form-control-sm" placeholder="Titel" type="text" v-model="title" name="title" maxlength="50" required>
                    <input class="form-control form-control-sm mt-2" type="url" placeholder="URL" v-model="url" name="url" required>
                    <button class="btn btn-outline-secondary btn-sm w-100 mt-2" @click="addLink()" type="button">Link speichern</button>
                </div>
            </div>
        </div>
        <div class="d-flex flex-column justify-content-between">
            <header><b>{{ widgetTag }}</b></header>
            <div v-for="link in links" :key="link.id" class="d-flex mt-2">
                <a target="_blank" :href="link.url"><i class="fa fa-solid fa-arrow-up-right-from-square"></i> {{ link.title }}</a>
                <a class="ms-auto" href="#" @click.prevent="removeLink(link.id)" v-show="configMode"><i class="fa fa-regular fa-trash-can"></i></a>
            </div>
        </div>
    </div>`
}
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