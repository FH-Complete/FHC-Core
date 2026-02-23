import ApiStvAbschlusspruefung from '../../../../../api/factory/stv/abschlusspruefung.js';

export default {
	inject: {
		$reloadList: {
			from: '$reloadList',
			required: true
		},
		config: {
			from: 'config',
			required: true
		},
	},
	emits: [
		'reloadTable'
	],
	props: {
		studentUids: {
			type: [Array, String],
			required: true,
			default: () => []
		},
		showDropDownMulti: {
			type: Boolean,
			required: true
		},
		stgTyp: {
			type: String,
			required: false
		},
		cisRoot: {
			type: String,
			required: true
		},
		stgKz: {
			type: Number,
			required: true
		},
		abschlusspruefung_id: {
			type: Number,
			required: false
		},
		stgPrfgTyp: {
			type: String,
			required: false
		},
		showAllFormats: {
			type: Boolean,
			required: true
		}
	},
	data() {
		return {
			documentCategories: [
				{ key: "pruefungsprotokoll", label: "Prüfungsprotokoll" },
				{ key: "pruefungszeugnis", label: "Prüfungszeugnis" },
				{ key: "urkunde", label: "Urkunde" },
			],
			languages: [
				{ key: "de", label: "Deutsch" },
				{ key: "en", label: "Englisch" },
			],
			formats:[
				{ key: "pdf", label: "PDF" },
				{ key: "odt", label: "ODT" },
				{ key: "doc", label: "DOC" },
			],
			data: {}
		};
	},
	methods: {
		getLabel(documentKey) {
			const labels = {
				pruefungsprotokoll: "Prüfungsprotokoll",
				pruefungszeugnis: "Prüfungszeugnis",
				urkunde: "Urkunde",
			};
			return labels[documentKey] || documentKey;
		},
		checkUidsIfExistingFinalExams(uids) {
			return this.$api
				.call(ApiStvAbschlusspruefung.checkForExistingExams(uids))
				.catch(this.$fhcAlert.handleSystemError);
		},
		printDocument(document, lang, format) {
			let xsl = '';
			if (this.stgPrfgTyp == 'Bachelor' || this.stgTyp == 'b')
				xsl = this.config.documents?.[document]?.[lang]?.Bakk;

			else if (this.stgPrfgTyp == 'Diplom' || this.stgTyp == 'm')
				xsl = this.config.documents?.[document]?.[lang]?.Master;

			if(!format)
				format = 'pdf';

			let output= this.showAllFormats ? format : 'pdf';

			this.checkUidsIfExistingFinalExams(this.studentUids);

			let uids = "";

			uids = !Array.isArray(this.studentUids) ? this.studentUids : this.studentUids.join(";");

			let linkToPdf = this.showDropDownMulti
				? FHC_JS_DATA_STORAGE_OBJECT.app_root +
				'content/pdfExport.php?xml=abschlusspruefung.rdf.php&xsl=' + xsl + '&uid=' + uids + '&xsl_stg_kz=' + this.stgKz + '&output=' + output
				: FHC_JS_DATA_STORAGE_OBJECT.app_root +
				'content/pdfExport.php?xml=abschlusspruefung.rdf.php&xsl=' + xsl + '&abschlusspruefung_id=' +  this.abschlusspruefung_id + '&uid=' + uids + '&xsl_stg_kz=' + this.stgKz + '&output=' + output;

			this.$emit('linkGenerated', linkToPdf);
		}
	},
	template: `
	<div class="stv-abschlusspruefung-dropdown">
		<div class="action-print-dropdown" ref="dropDown">
			<div class="btn-group">
				<button
					v-if="!showDropDownMulti"
					ref="toolbarButton"
					type="button"
					class="btn btn-secondary dropdown-toggle"
					data-bs-toggle="dropdown"
					data-bs-auto-close="outside"
					aria-expanded="false"
					@[\`show.bs.dropdown\`]="event => { if (event.target.closest('.tabulator-row')) event.target.closest('.tabulator-row').style.zIndex = 12 }"
					@[\`hidden.bs.dropdown\`]="event => { if (event.target.closest('.tabulator-row')) event.target.closest('.tabulator-row').style.zIndex = '' }"
					>
					{{this.$p.t('tools','dokumente')}}
				</button>
				<button
					v-else
					ref="toolbarButton"
					type="button"
					class="btn btn-secondary dropdown-toggle"
					data-bs-toggle="dropdown"
					data-bs-auto-close="outside"
					aria-expanded="false"
					>
					{{this.$p.t('tools','dokumente')}}
				</button>

				<!-- Version with permission to download as odt and doc, 2 levels-->
				<ul v-if="showAllFormats" class="dropdown-menu dropdown-menu-right">
				  <template v-for="(documents, documentKey) in config.documents" :key="documentKey">
					<div v-if="documents" class="dropend">
					  <!-- 1st level: documents and language -->
						<div v-for="language in languages" :key="documentKey.key + '-' + language.key">
						  <a
							class="dropdown-item dropdown-toggle d-flex justify-content-between align-items-center"
							href="#"
							aria-expanded="false"
							data-bs-toggle="dropdown"
						  >
							{{ getLabel(documentKey) }} {{ language.label }}
						  </a>
						  <!-- 2nd level: formats -->
						  <ul class="dropdown-menu dropdown-menu-right">
							<li v-for="format in formats" :key="format.key">
							  <a
								class="dropdown-item"
								href="#"
								@click="printDocument(documentKey, language.key, format.key)"
							  >
								{{format.label}}
							  </a>
							</li>
						  </ul>
						</div>
					</div>
				  </template>
				</ul>

				<!-- Version without permissions, 2 levels-->
				<ul v-else class="dropdown-menu dropdown-menu-right">
				  <template v-for="(documents, documentKey) in config.documents" :key="documentKey">
					<div v-if="documents" class="dropend">
					  <a
						class="dropdown-item dropdown-toggle d-flex justify-content-between align-items-center"
						data-bs-toggle="dropdown"
						aria-expanded="false"
						href="#"
					  >
						{{ getLabel(documentKey) }}
					  </a>
					  <ul class="dropdown-menu dropdown-menu-right">
						<li v-for="(lang) in languages">
						  <a
							class="dropdown-item"
							@click="printDocument(documentKey, lang.key)"
							href="#"
						  >
							{{lang.label}}
						  </a>
						</li>
					  </ul>
					</div>
				  </template>
				</ul>
			</div>
		</div>
	</div>`
};
