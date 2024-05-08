export default {
    name: 'CisMenuEntry',
    props: {
        entry: Object,
        level: {
            type: Number,
            default: 1
        }
    },
    data: () => {
        return {
            collapse: null
        }
    },
    computed: {
        link() {
            if (this.entry.template_kurzbz == 'redirect') {
                if (!this.entry.content)
                    return '';
                let xmlDoc = (new DOMParser()).parseFromString(this.entry.content,"text/xml");
                let url = xmlDoc.getElementsByTagName('url')[0];
                if (!url)
                    return '';
                // TODO(chris): replace get params
                url = url.childNodes[0].nodeValue + "";
                url = url.replace(/^\.\.\/cms\/news\.php/, FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/CisVue/Cms/news');
                url = url.replace(/^\.\.\//, FHC_JS_DATA_STORAGE_OBJECT.app_root);
                return url;
            }
            return FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/CisVue/Cms/content/' + this.entry.content_id;
        },
        target() {
            if (this.entry.template_kurzbz == 'redirect') {
                if (!this.entry.content)
                    return '';
                let xmlDoc = (new DOMParser()).parseFromString(this.entry.content,"text/xml");
                let target = xmlDoc.getElementsByTagName('target')[0];
                if (!target)
                    return '';
                
                target = target.childNodes[0].nodeValue + "";
                if (target == 'content' || target == '_self')
                    target = "";
                return target;
            }
            return ''
        },
        hasChilds() {
            return this.entry.childs && this.entry.childs.length !== 0;
        }
    },
    methods: {
        toggleCollapse(evt) {
            if (this.collapse !== null) {
                this.entry.menu_open = !this.entry.menu_open;
                this.collapse.toggle(evt.target);
            }
        }
    },
    mounted() {
        if (this.$refs.children) {
            if (this.entry.menu_open)
                this.$refs.children.className += ' show';
            this.collapse = new bootstrap.Collapse(this.$refs.children, { toggle: false });
        }
    },
    template: `
    <div v-if="entry.template_kurzbz == 'include'">
        INCLUDE
    </div>
    <template v-else>
        <template v-if="hasChilds">
            <a v-if="link.substr(0, 1) == '#'" 
                @click.prevent="toggleCollapse" 
                :aria-expanded="entry.menu_open" 
                :href="link" 
                :class="{
                    'btn btn-default rounded-0 w-100 text-start dropdown-toggle': true,
                    ['btn-level-' + level]: true,
                    collapsed: !entry.menu_open
                }">
                <span>{{ entry.titel }}</span>
            </a>
            <div v-else class="btn-group w-100">
                <a :href="link" :target="target" 
                    :class="{
                        'btn btn-default rounded-0 text-start': true,
                        ['btn-level-' + level]: true
                    }">
                    {{ entry.titel }}
                </a>
                <button @click.prevent="toggleCollapse" :aria-expanded="entry.menu_open" 
                    :class="{
                        'btn btn-default rounded-0 dropdown-toggle dropdown-toggle-split flex-grow-0': true,
                        collapsed: !entry.menu_open
                    }">
                    <span class="visually-hidden">Toggle Dropdown</span>
                </button>
            </div>
            <ul ref="children" 
                class="nav w-100 collapse">
                <cis-menu-entry v-for="child in entry.childs" :key="child" :entry="child" :level="level + 1"/>
            </ul>
        </template>
        <a v-else 
            :href="link" 
            :target="target" 
            :class="{
                'btn btn-default rounded-0 w-100 text-start': true, 
                ['btn-level-' + level]: true
            }">
            {{ entry.titel }}
        </a>
    </template>`
};