import StudentProfil from "./StudentProfil.js";
import MitarbeiterProfil from "./MitarbeiterProfil.js";
import ViewStudentProfil from "./StudentViewProfil.js";
import ViewMitarbeiterProfil from "./MitarbeiterViewProfil.js";
import Loading from "../../Loader.js";

import ApiProfil from '../../../api/factory/profil.js';
import ApiProfilUpdate from '../../../api/factory/profilUpdate.js';

Vue.$collapseFormatter = function (data) {
	//data - an array of objects containing the column title and value for each cell
	var container = document.createElement("div");
	container.classList.add("tabulator-collapsed-row");
	container.classList.add("text-break");

	var list = document.createElement("div");
	list.classList.add("row");

	container.appendChild(list);

	data.forEach(function (col) {
		let item = document.createElement("div");
		item.classList.add("col-6");
		let item2 = document.createElement("div");
		item2.classList.add("col-6");

		item.innerHTML = "<strong>" + col.title + "</strong>";
		item2.innerHTML = col.value ? col.value : "-";

		list.appendChild(item);
		list.appendChild(item2);
	});

	return Object.keys(data).length ? container : "";
};

export const Profil = {
	name: 'Profil',
	components: {
		StudentProfil,
		MitarbeiterProfil,
		ViewStudentProfil,
		ViewMitarbeiterProfil,
		Loading,
	},
	props: {
		uid: {
			type: String,
			required:false,
		},
		viewData: {
			type: Object,
		}
	},
	data() {
		return {
			//? loading property is used for showing/hiding the loading modal
			loading: false,
			profilUpdateStates: null,
			profilUpdateTopic: null,
			view: null,
			data: null,
			// notfound is null by default, but contains an UID if no user exists with that UID
			notFoundUID: null,
			isEditable: this.viewData.editable ?? false,
		};
	},
	provide() {
		return {
			isEditable: Vue.computed(()=>this.isEditable),
			profilUpdateStates: Vue.computed(() =>
				this.profilUpdateStates ? this.profilUpdateStates : false
			),
			profilUpdateTopic: Vue.computed(() =>
				this.profilUpdateTopic ? this.profilUpdateTopic : false
			),
			setLoading: (newValue) => {
				this.loading = newValue;
			},
			getZustellkontakteCount: this.zustellKontakteCount,
			getZustelladressenCount: this.zustellAdressenCount,
			collapseFunction: (e, column) => {
				//* check if property doesn't exist already and add it to the reactive this properties
				if (this[e.target.id] === undefined) {
					this[e.target.id] = true;
				}
				this[e.target.id] = !this[e.target.id];

				//* gets all event icons of the different rows to use the onClick event later
				let allClickableIcons = column._column.cells.map((row) => {
					return row.element.children[0];
				});

				//* changes the icon that shows or hides all the collapsed columns
				//* if the replace function does not find the class to replace, it just simply returns false
				if (this[e.target.id]) {
					e.target.classList.replace("fa-angle-up", "fa-angle-down");
				} else {
					e.target.classList.replace("fa-angle-down", "fa-angle-up");
				}

				//* changes the icon for every collapsed column to open or closed
				if (this[e.target.id]) {
					allClickableIcons
						.filter((column) => {
							return !column.classList.contains("open");
						})
						.forEach((col) => {
							col.click();
						});
				} else {
					allClickableIcons
						.filter((column) => {
							return column.classList.contains("open");
						})
						.forEach((col) => {
							col.click();
						});
				}
			},
			sortProfilUpdates: (ele1, ele2) => {
				let result = 0;
				if (ele1.status === "pending") {
					result = -1;
				} else if (ele1.status === "accepted") {
					result = ele2.status === "rejected" ? -1 : 1;
				} else {
					result = 1;
				}
				//? if they have the same status the insert date is used for ordering
				if (ele1.status === ele2.status) {
					result =
						new Date(ele2.insertamum.split(".").reverse().join("-")) -
						new Date(ele1.insertamum.split(".").reverse().join("-"));
				}
				return result;
			},
		};
	},
	methods: {
		async load() {
			// fetch profilUpdateStates to provide them to children components
			await this.$api
				.call(ApiProfilUpdate.getStatus())
				.then((response) => {
					this.profilUpdateStates = response.data;
				})
				.catch((error) => {
					console.error(error);
				});

			this.$api
				.call(ApiProfilUpdate.getTopic())
				.then((response) => {
					this.profilUpdateTopic = response.data;
				})
				.catch((error) => {
					console.error(error);
				});
			
			
			this.$api
				.call(ApiProfil.profilViewData(this.$route.params.uid??null))
				.then((response) => response.data).then(data=>{
					this.view = data?.profil_data.view;
					this.data = data?.profil_data.data;
					this.isEditable = data?.editable ?? false;
				})
				.catch((error) => {
					console.error(error);
				});
			
			
		},
		zustellAdressenCount() {
			if (!this.data || !this.data.adressen) {
				return null;
			}

			let adressenArray = [];
			if (this.data.profilUpdates?.length) {
				adressenArray = adressenArray.concat(
					this.data.profilUpdates
						.filter((update) => {
							return update.requested_change.zustelladresse;
						})
						.map((adresse) => {
							return adresse.requested_change.adresse_id;
						})
				);
			}

			if (
				!this.data.profilUpdates?.length ||
				!this.data.adressen
					.filter((adresse) => adresse.zustelladresse)
					.every((adresse) =>
						this.data.profilUpdates.some(
							(update) =>
								update.requested_change.adresse_id == adresse.adresse_id
						)
					)
			) {
				adressenArray = adressenArray.concat(
					this.data.adressen
						.filter((adresse) => {
							return adresse.zustelladresse;
						})
						.map((adr) => {
							return adr.adresse_id;
						})
				);
			}

			return [...new Set(adressenArray)];
			
		},
		zustellKontakteCount() {
			if (!this.data || !this.data.kontakte) {
				return null;
			}

			let kontakteArray = [];

			if (this.data.profilUpdates?.length) {
				kontakteArray = kontakteArray.concat(
					this.data.profilUpdates
						.filter((update) => {
							return update.requested_change.zustellung;
						})
						.map((kontant) => {
							return kontant.requested_change.kontakt_id;
						})
				);
			}

			if (
				!this.data.profilUpdates?.length ||
				!this.data.kontakte
					.filter((kontakt) => kontakt.zustellung)
					.every((kontakt) =>
						this.data.profilUpdates.some(
							(update) =>
								update.requested_change.kontakt_id == kontakt.kontakt_id
						)
					)
			) {
				kontakteArray = kontakteArray.concat(
					this.data.kontakte
						.filter((kontakt) => {
							return kontakt.zustellung;
						})
						.map((kon) => {
							return kon.kontakt_id;
						})
				);
			}

			return [...new Set(kontakteArray)];
		},
	},
	computed: {
		
		filteredEditData() {
			if (!this.data) {
				return;
			}

			return {
				view: null,
				data: {
					Personen_Informationen: {
						title: this.$p.t("profil", "personenInformationen"),
						topic: "Personen_informationen",
						view: null,
						data: {
							vorname: {
								title: this.$p.t("person", "vorname"),
								topic: this.profilUpdateTopic?.["Vorname"],
								view: "TextInputDokument",
								withFiles: true,
								data: {
									titel: "vorname",
									value: this.data.vorname,
								},
							},
							nachname: {
								title: this.$p.t("person", "nachname"),
								topic: this.profilUpdateTopic?.["Nachname"],
								view: "TextInputDokument",
								withFiles: true,
								data: {
									titel: "nachname",
									value: this.data.nachname,
								},
							},
							titel: {
								title: this.$p.t("global", "titel"),
								topic: this.profilUpdateTopic?.["Titel"],
								view: "TextInputDokument",
								withFiles: true,
								data: {
									titel: "titel",
									value: this.data.titel,
								},
							},
							postnomen: {
								title: this.$p.t("profil", "postnomen"),
								topic: this.profilUpdateTopic?.["Postnomen"],
								view: "TextInputDokument",
								withFiles: true,
								data: {
									titel: "postnomen",
									value: this.data.postnomen,
								},
							},
						},
					},
					Private_Kontakte: {
						title: this.$p.t("profil", "privateKontakte"),
						topic: this.profilUpdateTopic?.["Private Kontakte"],
						data: this.data.kontakte
							?.filter((item) => {
								// excludes all contacts that are already used in pending profil update requests
								return !this.data.profilUpdates?.some(
									(update) =>
										update.status === this.profilUpdateStates["Pending"] &&
										update.requested_change?.kontakt_id === item.kontakt_id
								);
							})
							.map((kontakt) => {
								return {
									listview: "Kontakt",
									view: "EditKontakt",
									data: kontakt,
								};
							}),
					},
					Private_Adressen: {
						title: this.$p.t("profil", "privateAdressen"),
						topic: this.profilUpdateTopic?.["Private Adressen"],
						data: this.data.adressen
							?.filter((item) => {
								return !this.data.profilUpdates?.some((update) => {
									return (
										update.status === this.profilUpdateStates["Pending"] &&
										update.requested_change?.adresse_id == item.adresse_id
									);
								});
							})
							.map((adresse) => {
								return {
									listview: "Adresse",
									view: "EditAdresse",
									data: adresse,
								};
							}),
					},
				},
			};
		},
	},
	watch: {
		loading: function (newValue) {
			if (newValue) {
				this.$refs.loadingModalRef.show();
			} else {
				this.$refs.loadingModalRef.hide();
			}
		},
		uid (newVal, oldVal) {
			this.load()
		}
	},
	created() {
		this.load()
	},
	template: `
	<div>
		<div v-if="notFoundUID">
			<h3>Es wurde keine Person mit der UID {{this.notFoundUID}} gefunden</h3>
		</div>
		<div v-else>
            <loading ref="loadingModalRef" :timeout="0"></loading>
            <component  :is="view" :data="data" :editData="filteredEditData" ></component>
		</div>
	</div>`,
}

export default Profil