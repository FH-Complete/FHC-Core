export default {
    name: 'CisMenuEntry',
    props: {
        entry: Object,
        level: {
            type: Number,
            default: 1
        },
		activeContent: [String, Number],
		highestMatchingUrlCount: Number,
    },
    data: () => {
		return {
			collapse: null,
			urlCount:0,
        }
    },
	inject: ['makeParentContentActive', 'setActiveEntry','addUrlCount'],
	watch:{
		highestMatchingUrlCount: function(newValue)
		{
			// if this entry has the most matching url parts then it should be active
			if (this.activeContent == null && newValue == this.urlCount)
			{
				this.setActiveEntry(this.entry.content_id);
			}
		},
		activeContent: function(newValue){
			if(newValue == this.entry.content_id){
				// wenn der Menupunkt nicht bereits offen ist
				if (!this.entry.menu_open){
					this.entry.menu_open = true;
				}
				
			}else{
				if (this.searchRecursiveChild(this.entry, 'content_id',newValue)) {
					this.entry.menu_open = true;
				} else {
					this.entry.menu_open = false;
				}
			}
		},
		'entry.menu_open': function (newValue,oldValue) {
			if (newValue) 
			{
				// only invokes .show if this.collapse is not null
				this.collapse && this.collapse.show();
			} 
			else 
			{
				// only invokes .hide if this.collapse is not null
				this.collapse && this.collapse.hide();
				if (this.activeContent == this.entry.content_id)
				{
					this.makeParentContentActive(this.entry.content_id);
				}
			}
			// debugging helpers - console.log(this.entry.titel, newValue ? "open" : "close")
			
		},
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
				else if (url.includes("../cms/content.php?")) {
					let content_regex = new RegExp("^\.\./cms/content.php\\?content_id=([0-9]+)");
					let content_regex_result = content_regex.exec(url);
					// content_regex_result[1] will be the first matched group
					return FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router + '/CisVue/Cms/content/' + content_regex_result[1];
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
		getUrlMatchPoints(url,link){
			let splitted_link = link.split('/');
			let splitted_url = url.href.split('/');

			let count = 0;

			for(let part_url of splitted_url)
			{
				for (let part_link of splitted_link)
				{
					if(part_url == part_link)
					{
						count++;
					}
				}
			}
			this.urlCount = count;
			this.addUrlCount(count);
		},
		checkActiveUrl(url){
			this.getUrlMatchPoints(url,this.link);
			
			let url_hash_spaceSymbol_regex = new RegExp("%20","gi");
			let url_hash_sharpSymbol_regex = new RegExp("^#");
			let url_hash = url.hash;
			url_hash = url_hash.replace(url_hash_spaceSymbol_regex, " ").replace(url_hash_sharpSymbol_regex,"");
			
			// if the url hash contains the titel of the menu 
			// or if the url equals the link of a menu 
			// then set the menu active 
			if (url_hash == this.entry.titel || url.href == this.link) {
					this.setActiveEntry(this.entry.content_id);
			}
		},
		// searches the childs of an entry recursively based on the value of a property
		searchRecursiveChild(entry,property,value){
			if (typeof entry.childs == 'object' && !Array.isArray(entry.childs) && Object.entries(entry.childs).length > 0){
				entry.childs = Object.values(entry.childs);
			}
			for (let child of entry.childs) {
				if (child[property] == value) {
					return true;
				}
				if ((child.childs instanceof Array && child.childs.length > 0) || Object.values(child.childs).length > 0) {
					if (this.searchRecursiveChild(child, property, value)){
						return true;
					}
				}
			}	
			return false;
		},
        toggleCollapse(evt) {
			if (this.active)
			{
				this.makeParentContentActive(this.entry.content_id); 
			}
			else
			{
				this.setActiveEntry(this.entry.content_id);
			}
        }
    },
    mounted() {
        if (this.$refs.children) {
            if (this.entry.menu_open)
                this.$refs.children.className += ' show';
            this.collapse = new bootstrap.Collapse(this.$refs.children, { toggle: false });
        }

		this.checkActiveUrl(new URL(window.location.href));
    },
    template: /*html*/`
	<!-- DEBUGGIING PRINTS
	<p>entry content_id: {{JSON.stringify(entry.content_id,null,2)}}</p>
	<p>entry menu: {{JSON.stringify(entry.menu_open,null,2)}}</p>
	<p>highest count : {{urlCount}}</p>
	-->
	<div v-if="entry.template_kurzbz == 'include'">
        INCLUDE
    </div>
    <template v-else>
        <template v-if="hasChilds">
			<div class="btn-group w-100">
                <a :href="(entry.menu_open)?link:null" :target="target" @click="toggleCollapse"
                    :class="{
                        'btn btn-default rounded-0 text-start': true,
                        ['btn-level-' + level]: true,
						'fw-bold':active
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
                <cis-menu-entry :highestMatchingUrlCount="highestMatchingUrlCount" :activeContent="activeContent" v-for="child in entry.childs" :key="child" :entry="child" :level="level + 1"/>
            </ul>
        </template>
        <a v-else
            :href="link"
            :target="target"
            :class="{
                'btn btn-default rounded-0 w-100 text-start': true,
                ['btn-level-' + level]: true,
				'fw-bold':active
            }">
            {{ entry.titel }}
        </a>
    </template>`
};