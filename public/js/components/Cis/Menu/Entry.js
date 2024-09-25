export default {
    name: 'CisMenuEntry',
    props: {
        entry: Object,
        level: {
            type: Number,
            default: 1
        },
		activeContent: String
    },
    data: () => {
        return {
            collapse: null,
			url:null,
        }
    },
	emits: ["activeEntry"],
	watch:{
		activeContent: function(newValue){
			if(newValue == this.entry.content_id){
				this.entry.menu_open = true;
			}else{
				if (this.searchRecursiveChild(this.entry, newValue)) {
					this.entry.menu_open = true;
				} else {
					this.entry.menu_open = false;
					if (this.entry.childs instanceof Array) {
						for (let child of this.entry.childs) {
							child.menu_open = false;

						}
					}
				}
			}
		},
		'entry.menu_open': function (newValue,oldValue) {
			if (newValue) {
				console.log(this.entry.titel,"open")
				this.collapse && this.collapse.show();
			} else {
				console.log(this.entry.titel,"close")
				this.collapse && this.collapse.hide();
			}
		}
	},
		
    computed: {
		active: function () {
			if (this.entry.menu_open){
				return true;
			}
			else if (this.activeContent) {
				return this.activeContent == this.entry.content_id;
			} else {
				return false;
			}
		},
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
				if (url.includes("../cms/news.php")) {
					let news_regex = new RegExp("^\.\./cms/news\.php");
					url = url.replace(news_regex, FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/CisVue/Cms/news');
				}
				else if(url.includes("../index.ci.php")){
					let index_regex = new RegExp("^\.\./index\.ci\.php");
					url = url.replace(index_regex, FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router);
				}
				else if (url.includes("../")) {
					let relative_regex = new RegExp("^\.\./");
					url = url.replace(relative_regex, FHC_JS_DATA_STORAGE_OBJECT.app_root);
				}
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
		searchRecursiveChild(entry,child_content_id){
			if (typeof entry.childs == 'object' && !Array.isArray(entry.childs) && Object.entries(entry.childs).length > 0){
				entry.childs = Object.values(entry.childs);
			}
			for (let child of entry.childs) {
				if (child.content_id == child_content_id) {
					return true;
				}
				if ((child.childs instanceof Array && child.childs.length > 0) || Object.values(child.childs).length > 0) {
					if (this.searchRecursiveChild(child, child_content_id)){
						return true;
					}
				}
			}	
			return false;
		},
		resendEmit(event){
			this.entry.menu_open = true;
			this.$emit('activeEntry',event);
		},
        toggleCollapse(evt) {
            if (this.level > 1 && this.collapse !== null) {
                this.entry.menu_open = !this.entry.menu_open;
                this.collapse.toggle(evt.target);
            }else{
				this.active ? 
				this.$emit("activeEntry", null): 
				this.$emit("activeEntry", this.entry.content_id);
			}
        }
    },
    mounted() {
        if (this.$refs.children) {
            if (this.entry.menu_open)
                this.$refs.children.className += ' show';
            this.collapse = new bootstrap.Collapse(this.$refs.children, { toggle: false });
        }

		this.url = new URL(window.location.href);
		if (this.url.hash && this.url.hash.slice(1) == this.entry.titel){
			this.$emit("activeEntry", this.entry.content_id);
		}
		else if (this.url.href == this.link) {
			this.$emit("activeEntry", this.entry.content_id);
		}
    },
    template: /*html*/`
	<!-- DEBUGGIING PRINTS
	<p>entry content_id: {{JSON.stringify(entry.content_id,null,2)}}</p>
	<p>entry menu: {{JSON.stringify(entry.menu_open,null,2)}}</p>
	-->
	<div v-if="entry.template_kurzbz == 'include'">
        INCLUDE
    </div>
    <template v-else>
        <template v-if="hasChilds">
			<div class="btn-group w-100">
                <a :href="link" :target="target"
                    :class="{
                        'btn btn-default rounded-0 text-start': true,
                        ['btn-level-' + level]: true,
						'text-decoration-underline':active
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
                <cis-menu-entry @activeEntry="resendEmit" :activeContent="activeContent" v-for="child in entry.childs" :key="child" :entry="child" :level="level + 1"/>
            </ul>
        </template>
        <a v-else
            :href="link"
            :target="target"
            :class="{
                'btn btn-default rounded-0 w-100 text-start': true,
                ['btn-level-' + level]: true,
				'text-decoration-underline':active
            }">
            {{ entry.titel }}
        </a>
    </template>`
};