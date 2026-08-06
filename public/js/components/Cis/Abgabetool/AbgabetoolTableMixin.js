import { multiSelectHeaderFilter } from '../../../tabulator/filters/multiSelectHeaderFilter.js';
import { formatISODate, toViennaDate } from './dateUtils.js';

// Shared projektarbeiten table logic of the Assistenz & Betreuer view: column formatters, sorters,
// header filters, quality gate status and the localStorage persistence of the table state.
export const AbgabetoolTableMixin = {
	data() {
		return {
			// selected values of the multiselect header filters, keyed by column field
			headerFilterSelections: {}
		}
	},
	computed: {
		countsToHTML() {
			return this.$p.t('global/ausgewaehlt')
				+ ': <strong>' + (this.selectedcount || 0) + '</strong>'
				+ ' | '
				+ this.$p.t('global/gefiltert')
				+ ': '
				+ '<strong>' + (this.filteredcount || 0) + '</strong>'
				+ ' | '
				+ this.$p.t('global/gesamt')
				+ ': <strong>' + (this.count || 0) + '</strong>';
		}
	},
	methods: {
		headerFilterSelection(field) {
			return {
				get: () => this.headerFilterSelections[field] ?? [],
				set: (values) => { this.headerFilterSelections[field] = [...values] }
			}
		},
		getStatusFilterOptions() {
			return [
				{ label: this.$p.t('abgabetool/c4positivBenotet'),    value: 'bestanden' },
				{ label: this.$p.t('abgabetool/c4negativBenotet'),    value: 'nichtbestanden' },
				{ label: this.$p.t('abgabetool/c4tooltipVerspaetet'), value: 'verspaetet' },
				{ label: this.$p.t('abgabetool/c4tooltipVerpasst'),   value: 'verpasst' },
				{ label: this.$p.t('abgabetool/c4tooltipAbzugeben'),  value: 'abzugeben' },
				{ label: this.$p.t('abgabetool/c4tooltipAbgegeben'),  value: 'abgegeben' },
				{ label: this.$p.t('abgabetool/c4tooltipBeurteilungerforderlich'), value: 'beurteilungerforderlich' },
				{ label: this.$p.t('abgabetool/c4tooltipStandardv2'), value: 'standard' },
			].map(opt => ({
				...opt,
				badge: { cssClass: opt.value + '-header', html: this.getDateStyleHtml(opt.value) }
			}));
		},
		statusHeaderFilterEditor(cell, onRendered, success, cancel) {
			return multiSelectHeaderFilter(cell, onRendered, success, cancel, {
				options: () => this.getStatusFilterOptions(),
				selected: this.headerFilterSelection(cell.getField()),
				minWidth: '220px'
			});
		},
		statusHeaderFilterFunc(filterVal, rowVal, rowData, filterParams) {
			if (!filterVal || !filterVal.length) return true;
			// rowVal is the raw dateStyle string on the flat table
			return filterVal.some(val => val === rowVal);
		},
		getQgateFilterOptions() {
			return [
				{ label: '[+] ' + this.$p.t('abgabetool/c4positivBenotet'), value: 'positive' },
				{ label: '[-] ' + this.$p.t('abgabetool/c4negativBenotet'), value: 'negative' },
				{ label: '[~] ' + this.$p.t('abgabetool/c4notYetGraded'), value: 'not_graded' },
				{ label: '[?] ' + this.$p.t('abgabetool/c4notSubmitted'), value: 'not_submitted' },
				{ label: '[o] ' + this.$p.t('abgabetool/c4notHappenedYet'), value: 'not_happened' },
				{ label: '[--] ' + this.$p.t('abgabetool/c4keinTerminVorhanden'), value: 'no_termin' },
			];
		},
		qgateHeaderFilterEditor(cell, onRendered, success, cancel) {
			return multiSelectHeaderFilter(cell, onRendered, success, cancel, {
				options: () => this.getQgateFilterOptions(),
				selected: this.headerFilterSelection(cell.getField())
			});
		},
		qgateHeaderFilterFunc(filterVal, rowVal, rowData, filterParams) {
			if (!filterVal || !filterVal.length) return true;

			const matches = (val) => {
				switch (val) {
					case 'positive':     return rowVal === this.$p.t('abgabetool/c4positivBenotet');
					case 'negative':     return rowVal === this.$p.t('abgabetool/c4negativBenotet');
					case 'not_graded':   return rowVal === this.$p.t('abgabetool/c4notYetGraded');
					case 'not_submitted':return rowVal === this.$p.t('abgabetool/c4notSubmitted');
					case 'not_happened': return rowVal === this.$p.t('abgabetool/c4notHappenedYet');
					case 'no_termin':    return rowVal === this.$p.t('abgabetool/c4keinTerminVorhanden');
					default:             return true;
				}
			};

			// OR logic — row passes if it matches any selected filter
			return filterVal.some(val => matches(val));
		},
		getNotenFilterOptions(field) {
			// mapping evaluated at render time, not at column definition time
			const fieldOptionsMap = {
				'pa_note': this.notenOptions,
				'note': this.allowedNotenOptions,
			};
			const options = fieldOptionsMap[field] ?? this.notenOptions;

			return (options ?? []).map(opt => ({ value: opt.note, label: opt.bezeichnung }));
		},
		notenHeaderFilterEditor(cell, onRendered, success, cancel) {
			return multiSelectHeaderFilter(cell, onRendered, success, cancel, {
				options: () => this.getNotenFilterOptions(cell.getField()),
				selected: this.headerFilterSelection(cell.getField())
			});
		},
		notenHeaderFilterFunc(filterVal, rowVal, rowData, filterParams) {
			if (!filterVal || !filterVal.length) return true;
			// columns showing the bezeichnung (note_bez) keep the note id in another field
			const value = filterParams?.idField ? rowData?.[filterParams.idField] : rowVal;
			// rowVal is the raw integer note id or a note object
			const noteId = typeof value === 'object' ? value?.note : value;
			return filterVal.some(val => val == noteId); // loose equality: filter vals are numbers, noteId might be string
		},
		notenSorter(a, b, aRow, bRow, column, dir, sorterParams) {
			const aData = aRow.getData()
			const bData = bRow.getData()
			return aData.note - bData.note
		},
		sortFuncTerminCol(a, b, aRow, bRow, column, dir, params) {
			if (a === null || typeof a === "undefined") return 1;
			if (b === null || typeof b === "undefined") return -1;

			// try to handle the prev/next interpretation consistently
			// can only make this wrong UX whise so whatever
			if(column._column.field == 'prevTermin') {
				return Math.abs(b.diffMs) - Math.abs(a.diffMs)
			} else if (column._column.field == 'nextTermin') {
				return Math.abs(a.diffMs) - Math.abs(b.diffMs)
			}

			// just in case someone reuses this
			return Math.abs(b.diffMs) - Math.abs(a.diffMs)
		},
		headerFilterTerminCol(filterVal, rowVal) {
			if (!rowVal || !rowVal.luxonDate || !rowVal.luxonDate.isValid) {
				return false;
			}

			const rowDate = rowVal.luxonDate;

			const toLuxon = (val) => {
				if (!val) return null;
				let dt;
				if (val instanceof Date) {
					dt = luxon.DateTime.fromJSDate(val);
				} else if (typeof val === "string") {
					dt = toViennaDate(val);
				} else { // fallback
					dt = luxon.DateTime.fromMillis(Number(val));
				}

				return dt.isValid ? dt : null;
			};

			const von = toLuxon(filterVal[0]);
			const bis = toLuxon(filterVal[1]);

			// specific day
			if (von && !bis) {
				return rowDate.hasSame(von, "day");
			}

			// range case
			if (von && bis) {
				return rowDate >= von.startOf("day") && rowDate <= bis.endOf("day");
			}

			return false
		},
		getDateStyleHtml(dateStyle) {
			const iconMap = {
				'verspaetet':              '<i class="fa-solid fa-triangle-exclamation"></i>',
				'verpasst':                '<i class="fa-solid fa-calendar-xmark"></i>',
				'abzugeben':               '<i class="fa-solid fa-hourglass-half"></i>',
				'standard':                '<i class="fa-solid fa-clock"></i>',
				'abgegeben':               '<i class="fa-solid fa-paperclip"></i>',
				'beurteilungerforderlich': '<i class="fa-solid fa-list-check"></i>',
				'bestanden':               '<i class="fa-solid fa-check"></i>',
				'nichtbestanden':          '<i class="fa-solid fa-circle-exclamation"></i>',
			};
			return iconMap[dateStyle] ?? '';
		},
		abgabeterminFormatter(cell, formatterParams, onRendered) {
			const val = cell.getValue()
			const dateStyle = val?.dateStyle ?? val


			if(val) {
				let icon = ''
				switch(dateStyle) {
					case 'verspaetet':
						icon = '<i style="color: #000000 !important;" class="fa-solid fa-triangle-exclamation"></i>'
						break
					case 'verpasst':
						icon = '<i style="color: #000000 !important;" class="fa-solid fa-calendar-xmark"></i>'
						break
					case 'abzugeben':
						icon = '<i style="color: #000000 !important;" class="fa-solid fa-hourglass-half"></i>'
						break
					case 'standard':
						icon = '<i style="color: #000000 !important;" class="fa-solid fa-clock"></i>'
						break
					case 'abgegeben':
						icon = '<i style="color: #000000 !important;" class="fa-solid fa-paperclip"></i>'
						break
					case 'beurteilungerforderlich':
						icon = '<i style="color: #000000 !important;" class="fa-solid fa-list-check"></i>'
						break
					case 'bestanden':
						icon = '<i style="color: #000000 !important;" class="fa-solid fa-check"></i>'
						break
					case 'nichtbestanden':
						icon = '<i style="color: #000000 !important;" class="fa-solid fa-circle-exclamation"></i>'
						break
				}

				const typKurzbz = val.paabgabetyp_kurzbz ?? val.bezeichnung?.paabgabetyp_kurzbz
				const bezeichnung = this.$p.t('abgabetool/c4paatyp' + typKurzbz)

				if(formatterParams?.iconOnly) {
					return '<div style="display: flex; height: 20px;">' +
						'<div class=' + dateStyle + "-header" + ' style="min-width:48px; height: 100%; padding: 0px; display: flex; align-items: center; justify-content: center;">' +
						icon +
						'</div>' +
						'</div>'
				}

				return '<div style="display: flex; height: 20px;">' +
					'<div class=' + dateStyle + "-header" + ' style="min-width:48px; height: 100%; padding: 0px; display: flex; align-items: center; justify-content: center;">' +
						icon +
					'</div>' +
					'<div style="margin-left: 4px;">' +
						'<p style="max-width: 100%; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">'+bezeichnung+' - '+ this.formatDate(val.datum)+'</p>' +
					'</div>'+
					'</div>'

			} else {
				return ''
			}

		},
		centeredTextFormatter(cell) {
			const longForm = cell.getValue()
			if(!longForm) return
			const data = cell.getData()
			const entry = Object.entries(data).find(entry => entry[1] == longForm)

			// shortFormKey must have same keyname as longForm but with 'Short' appended
			const shortForm = data[entry[0]+'Short']

			if(shortForm && longForm) {
				return `<div style="display: flex; justify-content: start; align-items: center; height: 100%; width: 100%;">
				<span class="full-text" style="max-width: 100%; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; margin: 0px;">
					${longForm}
				</span>
				<span class="short-text" style="font-weight: bold; display: none;">
					${shortForm}
				</span>
				</div>`;
			} else {
				return '<div style="display: flex; justify-content: start; align-items: center; height: 100%">' +
					'<p style="max-width: 100%; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; margin: 0px;">'+longForm+'</p></div>'
			}
		},
		pkzTextFormatter(cell) {
			const val = cell.getValue()

			return '<div style="display: flex; justify-content: start; align-items: center; height: 100%">' +
				'<a style="max-width: 100%; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">'+val+'</a></div>'
		},
		shortLongTitleFormatter(cell, formatterParams, onRendered) {
			const longForm = cell.getValue()
			const shortForm = formatterParams?.shortForm

			if(longForm && shortForm) {
				return `<span class="full-text" style="max-width: 100%; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; margin: 0px;">
					${longForm}
				</span>
				<span class="short-text" style="font-weight: bold; display: none;">
					${shortForm}
				</span>`
			} else {
				return `<span class="full-text" style="max-width: 100%; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; margin: 0px;">
					${longForm}
				</span>`
			}

		},
		toolTipFuncPrevTermin(e, cell, onRendered) {
			const data = cell.getData();
			if(!data.prevTermin) return ''
			return this.mapDateStyleToTabulatorTooltip(data.prevTermin.dateStyle);
		},
		toolTipFuncNextTermin(e, cell, onRendered) {
			const data = cell.getData();
			if(!data.nextTermin) return ''
			return this.mapDateStyleToTabulatorTooltip(data.nextTermin.dateStyle);
		},
		mapDateStyleToTabulatorTooltip(dateStyleString) {
			switch(dateStyleString) {
				case 'bestanden':
					return this.$p.t('abgabetool/c4tooltipBestanden')
				break;
				case 'nichtbestanden':
					return this.$p.t('abgabetool/c4tooltipNichtBestanden')
				break;
				case 'beurteilungerforderlich':
					return this.$p.t('abgabetool/c4tooltipBeurteilungerforderlich')
				break;
				case 'verspaetet':
					return this.$p.t('abgabetool/c4tooltipVerspaetet')
				break;
				case 'abgegeben':
					return this.$p.t('abgabetool/c4tooltipAbgegeben')
				break;
				case 'verpasst':
					return this.$p.t('abgabetool/c4tooltipVerpasst')
				break;
				case 'abzugeben':
					return this.$p.t('abgabetool/c4tooltipAbzugeben')
				break;
				case 'standard':
					return this.$p.t('abgabetool/c4tooltipStandardv2')
				break;
				default: return ''
			}
		},
		checkQualityGateStatus(projekt) {
			const qgate1Termine = []
			const qgate2Termine = []

			projekt.qgate1Status = this.$p.t('abgabetool/c4keinTerminVorhanden')// 'Kein Termin vorhanden'
			projekt.qgate1StatusRank = 0
			projekt.qgate2Status = this.$p.t('abgabetool/c4keinTerminVorhanden')
			projekt.qgate2StatusRank = 0

			projekt.abgabetermine.forEach(termin => {
				if(termin.paabgabetyp_kurzbz == 'qualgate1') qgate1Termine.push(termin)
				if(termin.paabgabetyp_kurzbz == 'qualgate2') qgate2Termine.push(termin)
			})

			// calculate qgateStatusRank and display the highest order status rank of all quality gate termine until one
			// counts as passed, which is just a positive note no matter if anything has been uploaded

			// reuse luxon calculated diffMs (termin.datum in relation to today) from previous datestyle check
			qgate1Termine.forEach(qgate => {
				if(qgate.note != null && projekt.qgate1StatusRank <= 5) {
					const noteOpt = typeof qgate.note !== 'object' ? this.notenOptions.find(opt => opt.note == qgate.note) : qgate.note
					if(noteOpt.positiv) {
						projekt.qgate1Status = this.$p.t('abgabetool/c4positivBenotet')
						projekt.qgate1StatusRank = 5
					} else {
						projekt.qgate1Status = this.$p.t('abgabetool/c4negativBenotet')
						projekt.qgate1StatusRank = 4
					}
				} else if (qgate.note == null && projekt.qgate1StatusRank <= 3) {
					projekt.qgate1Status = this.$p.t('abgabetool/c4notYetGraded')
					projekt.qgate1StatusRank = 3
				} else if(qgate.upload_allowed == true && qgate.abgabedatum == null && projekt.qgate1StatusRank <= 2) {
					projekt.qgate1Status = this.$p.t('abgabetool/c4notSubmitted')
					projekt.qgate1StatusRank = 2
				} else if (qgate.upload_allowed == false && qgate.diffMs <= 0 && projekt.qgate1StatusRank <= 1) {
					projekt.qgate1Status = this.$p.t('abgabetool/c4notHappenedYet')
					projekt.qgate1StatusRank = 1
				}
			})

			qgate2Termine.forEach(qgate => {
				if(qgate.note != null && projekt.qgate1StatusRank <= 5) {
					const noteOpt = typeof qgate.note !== 'object' ? this.notenOptions.find(opt => opt.note == qgate.note) : qgate.note
					if(noteOpt.positiv) {
						projekt.qgate2Status = this.$p.t('abgabetool/c4positivBenotet')
						projekt.qgate2StatusRank = 5
					} else {
						projekt.qgate2Status = this.$p.t('abgabetool/c4negativBenotet')
						projekt.qgate2StatusRank = 4
					}
				} else if (qgate.note == null && projekt.qgate2StatusRank <= 3) {
					projekt.qgate2Status = this.$p.t('abgabetool/c4notYetGraded')
					projekt.qgate2StatusRank = 3
				} else if(qgate.upload_allowed == true && qgate.abgabedatum == null && projekt.qgate2StatusRank <= 2) {
					projekt.qgate2Status = this.$p.t('abgabetool/c4notSubmitted')
					projekt.qgate2StatusRank = 2
				} else if (qgate.upload_allowed == false && qgate.diffMs <= 0 && projekt.qgate2StatusRank <= 1) {
					projekt.qgate2Status = this.$p.t('abgabetool/c4notHappenedYet')
					projekt.qgate2StatusRank = 1
				}
			})

			// set shorthand statuscode once real status has been determined
			projekt.qgate1StatusShort = this.mapRankToShortStatus(projekt.qgate1StatusRank)
			projekt.qgate2StatusShort = this.mapRankToShortStatus(projekt.qgate2StatusRank)
		},
		mapRankToShortStatus(rank) {
				switch(rank){
					case 0: // kein termin vorhanden
						return '--'
						break;
					case 1: // noch nicht stattgefunden
						return 'o'
						break;
					case 2: // noch nicht abgegeben
						return '?'
						break;
					case 3: // noch nicht benotet
						return '~'
						break;
					case 4: // negativ benotet
						return '-'
						break;
					case 5: // positiv benotet
						return '+'
						break;
				}
		},
		loadTableState(persistenceID) {
			return JSON.parse(localStorage.getItem(persistenceID) || "null");
		},
		saveTableState(table, persistenceID, state) {
			// avoid storing state after first restore part happened
			if(!state.stateRestored) return
			const rawLayout = table.getColumnLayout();
			const savedState = {
				columns: rawLayout.map(col => ({
					field: col.field,
					visible: col.visible,
					width: col.width,
				})),
				sort: table.getSorters().map(s => ({
					field: s.field,
					dir: s.dir,
				})),
				filters: table.getFilters(),
				headerFilters: table.getHeaderFilters()
			};

			localStorage.setItem(persistenceID, JSON.stringify(savedState));
		},
		// attaches the localStorage persistence (column layout, sorters, filters) to a table.
		// state holds the restore flags of that table, since a component can hold multiple tables
		initTablePersistence(tableCmpt, persistenceID, state) {
			const table = tableCmpt.tabulator

			Object.assign(state, {
				stateRestored: false,
				colLayoutRestored: false,
				filtersRestored: false,
				headerFiltersRestored: false,
				sortRestored: false
			})

			const save = () => this.saveTableState(table, persistenceID, state);

			table.on("columnMoved", save);
			table.on("columnResized", save);
			table.on("columnVisibilityChanged", save);
			table.on("filterChanged", save);
			table.on("headerFilterChanged", save);
			table.on("dataSorted", save);
			table.on("columnSorted", save);
			table.on("sortersChanged", save);

			const saved = this.loadTableState(persistenceID);

			table.on("renderComplete", () => {
				if(state.stateRestored) return

				if (saved?.columns && !state.colLayoutRestored) {
					const layout = saved.columns.map(col => ({
						field: col.field,
						width: col.width,
						visible: col.visible,
						// add more if needed, but keep it simple
					}));

					table.setColumnLayout(layout);

					state.colLayoutRestored = true;
				}

				if (saved?.filters && !state.filtersRestored) {
					state.filtersRestored = true // instantly avoid retriggers
					table.setFilter(saved.filters);
				}

				if (saved?.headerFilters && !state.headerFiltersRestored) {
					state.headerFiltersRestored = true // instantly avoid retriggers
					for (let hf of saved.headerFilters) {
						// keep the checkboxes of the multiselect header filters in sync with the restored value
						if (Array.isArray(hf.value)) this.headerFilterSelections[hf.field] = [...hf.value];
						table.setHeaderFilterValue(hf.field, hf.value);
					}
				}

				if (saved?.sort?.length && !state.sortRestored) {
					state.sortRestored = true;

					setTimeout(() => {
						const sortList = saved.sort.map(s => {
							const col = table.columnManager.findColumn(s.field);
							if (!col) {
								return null;
							}
							return { column: col, dir: s.dir };
						}).filter(Boolean);

						table.setSort(sortList);
					}, 100);
				}
				state.stateRestored = true

				// ensure that the filterCollapseables thingy has the correct values
				tableCmpt.setSelectedFields();
			});
		},
		formatDate(dateParam) {
			return formatISODate(dateParam);
		},
		getOptionLabelAbgabetyp(option){
			return this.$p.t('abgabetool/c4paatyp' + option.paabgabetyp_kurzbz)
		},
		buildStg(projekt) {
			return (projekt.typ + projekt.kurzbz)?.toUpperCase()
		},
		handleToggleFullscreenDetail() {
			this.detailIsFullscreen = !this.detailIsFullscreen
		},
		openAddSeriesModal() {
			this.$refs.modalContainerAddSeries.show()
		},
		tableResolve(resolve) {
			this.tableBuiltResolve = resolve
		},
		handleUuidDefined(uuid) {
			this.tabulatorUuid = uuid
		}
	}
};

export default AbgabetoolTableMixin;
