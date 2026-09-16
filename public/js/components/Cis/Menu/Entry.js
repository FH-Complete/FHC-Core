import CisMenuLink from "./Link.js";

export default {
    name: 'CisMenuEntry',
	components: {
		CisMenuLink
	},
    props: {
        entry: Object,
        level: {
            type: Number,
            default: 1
        },
		activeContent: [String, Number],
		highestMatchingUrlCount: Number,
		openMenuHierarchy: Array,
		precedingMenuHierarchy: {
			type: Array,
			default: [],
		},
    },
    data: () => {
		return {
			collapse: null,
			urlCount:0,
        }
    },
	inject: ['setActiveEntry', 'setOpenMenuHierarchy', 'addUrlCount'],
	watch:{
		highestMatchingUrlCount: function(newValue)
		{
			// if this entry has the most matching url parts then it should be active
			if (this.activeContent == null && newValue == this.urlCount)
			{
				this.setActiveEntry(this.entry.content_id);
				this.setOpenMenuHierarchy(this.menuHierarchy);
			}
		},
		openMenuHierarchy() {
			if (!this.hasChilds) return;

			if (this.$props.openMenuHierarchy.length && this.$props.openMenuHierarchy[0] === this.$props.entry.content_id) {
				this.$props.entry.menu_open = true;				
			} else {
				this.$props.entry.menu_open = false;				
			}
		},
		'entry.menu_open': function (isMenuOpen) {
			if (!this.collapse)
				return;

			if (isMenuOpen) {
				this.collapse.show();
			} else {
				this.collapse.hide();
			}
		},
	},
    computed: {
		hasFullLink() {
			return this.entry.url.startsWith(FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router)
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
        },
		menuHierarchy() {
			return [...this.$props.precedingMenuHierarchy, this.$props.entry.content_id];
		},
		menuNodeHref() {
			if (this.hasChilds) {
				return this.hasFullLink ? this.$props.entry.url : null;
			} else {
				return this.$props.entry.url;
			}
		},
    },
    methods: {
		getUrlMatchPoints(url,link){
			let splitted_link = link.split('/');
			let splitted_url = url.href.split('/');
			let count = 0;

			for (let part_url of splitted_url) {
				for (let part_link of splitted_link) {
					if (part_url === part_link) {
						count++;
					}
				}
			}

			this.urlCount = count;
			this.addUrlCount(count);
		},
		checkActiveUrl(url){
			this.getUrlMatchPoints(url,this.entry.url);
			
			let url_hash_spaceSymbol_regex = new RegExp("%20","gi");
			let url_hash_sharpSymbol_regex = new RegExp("^#");
			let url_hash = url.hash;
			url_hash = url_hash.replace(url_hash_spaceSymbol_regex, " ").replace(url_hash_sharpSymbol_regex,"");
			
			if (url_hash == this.entry.titel || url.href == this.entry.url) {
					this.setActiveEntry(this.entry.content_id);
					this.setOpenMenuHierarchy(this.menuHierarchy);
			}
		},
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
        toggleCollapse() {
			if (!this.$props.entry.menu_open) {
				this.setOpenMenuHierarchy(this.menuHierarchy);
			} else {
				this.setOpenMenuHierarchy(this.precedingMenuHierarchy);
			}
        },
		handleClickOnMenuNode() {
			if (this.hasFullLink) {
				this.setActiveEntry(this.$props.entry.content_id);
				if (this.hasChilds) {
					this.setOpenMenuHierarchy(this.menuHierarchy);
				}
			} else if (this.hasChilds) {
				this.toggleCollapse();
			}
		},
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
	<div v-if="entry.template_kurzbz == 'include'">
        INCLUDE
    </div>
    <template v-else>
		<div class="btn-group w-100">
			<cis-menu-link
				@click="handleClickOnMenuNode($event)"
				:href="menuNodeHref"
				:target="target"
				class="btn btn-default rounded-0 text-start"
				:class="{
					['btn-level-' + level]: true,
					'fw-bold': $props.activeContent === $props.entry.content_id
				}"
				:style="'padding-left: calc(var(--bs-btn-padding-x) * ' + $props.level + ');'"
			>
				{{ entry.titel }}
			</cis-menu-link>
			<button
				v-if="hasChilds"
				@click.prevent="toggleCollapse()"
				:aria-expanded="entry.menu_open"
				class="btn btn-default rounded-0 dropdown-toggle dropdown-toggle-split flex-grow-0"
				:class="{ collapsed: !entry.menu_open }"
			>
				<span class="visually-hidden">Toggle Dropdown</span>
			</button>
		</div>
		<ul v-if="hasChilds" ref="children" class="nav w-100 collapse" >
			<cis-menu-entry
				v-for="child in entry.childs"
				:key="child"
				:highestMatchingUrlCount="highestMatchingUrlCount"
				:activeContent="activeContent"
				:entry="child"
				:level="level + 1"
				:openMenuHierarchy="$props.openMenuHierarchy.slice(1)"
				:precedingMenuHierarchy="menuHierarchy"
			/>
		</ul>
    </template>`
};