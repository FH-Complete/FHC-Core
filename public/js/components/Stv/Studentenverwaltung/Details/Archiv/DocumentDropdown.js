export default {
	name: "DocumentDropdown",
	props: {
		documents: {
			type: [Object, Array],
			required: true,
		},
		studentUids: {
			type: [Array, String],
			required: true,
			default: () => []
		},
		showDropDownMulti: {
			type: Boolean,
			required: true
		},
		cisRoot: {
			type: String,
			required: true
		},
		stgKz: {
			type: Number,
			required: true
		},
		showAllFormats: {
			type: Boolean,
			required: true
		}
	},
	data() {
		return {};
	},
	methods: {
		printDokument(url, scope){
			//TODO Manu(check if logic not in content (Zutrittkarte also in content folder))
			let linkToPdf = this.cisRoot + 'content/' + url;
			window.open(linkToPdf, '_blank');
		}
	},
	template: `
		<div class="stv-document-dropdown btn-group">
			<button
				ref="toolbarButton"
				type="button"
				class="btn btn-secondary dropdown-toggle px-5 ms-5"
				data-bs-toggle="dropdown"
				data-bs-auto-close="outside"
				aria-expanded="false"
				>
				{{this.$p.t('dokumente','dokument_erstellen')}}
			</button>

			<ul class="dropdown-menu dropdown-menu-right">
				<template v-for="doc in documents" :key="doc.id">
					<li v-if="doc.type === 'documenturl'">
					  <button class="dropdown-item" type="button" @click="printDokument(doc.url, doc.scope)">
					  {{ doc.name }}
					  </button>
					</li>

					<li v-else-if="doc.type === 'submenu'" class="dropend">
					  <a
					  class="dropdown-item dropdown-toggle"
					  href="#"
					  role="button"
					  data-bs-toggle="dropdown"
					  aria-expanded="false"
					  >
					  {{ doc.name }}
					  </a>

					  <ul class="dropdown-menu">
						  <template v-for="child in doc.data" :key="child.id">
							<li v-if="child.type === 'documenturl'">
							<button class="dropdown-item" type="button" @click="printDokument(child.url, child.scope)">
							  {{ child.name }}
							</button>
							</li>
							<li v-else-if="child.type === 'submenu'" class="dropend">
							<a
							  class="dropdown-item dropdown-toggle"
							  href="#"
							  role="button"
							  data-bs-toggle="dropdown"
							  aria-expanded="false"
							>
							  {{ child.name }}
							</a>
							</li>
						  </template>
					  </ul>
					</li>
				</template>
		  </ul>

	</div>`
};


