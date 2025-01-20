import Kontakt from "../ProfilComponents/Kontakt.js";
import EditKontakt from "./EditProfilComponents/EditKontakt.js";
import Adresse from "../ProfilComponents/Adresse.js";
import EditAdresse from "./EditProfilComponents/EditAdresse.js";
import Status from "./EditProfilComponents/Status.js";
import TextInputDokument from "./EditProfilComponents/TextInputDokument.js";

export default {
	components: {
		Kontakt,
		EditKontakt,
		Adresse,
		EditAdresse,
		Status,
		TextInputDokument,
	},
	inject: ["profilUpdateTopic"],
	props: {
		list: Object,

		//? Prop used to determine how many options the select should initially show
		size: {
			type: Number,
			default: null,
		},
		//? Content for the aria label of the select
		ariaLabel: {
			type: String,
			required: true,
		},
		profilUpdate: String,
		topic: String,
		breadcrumb: String,
	},
	emits: {
		//? update:modelValue event is needed to notify the v-model when the value has changed
		["update:profilUpdate"]: null,
		["update:topic"]: null,
		["update:breadcrumb"]: null,
		submit: null,
		select: null,
	},
	data() {
		return {
			view: null,
			data: null,
			breadcrumbItems: [],
			modal_topic: this.topic,
			properties: null,
		};
	},

	methods: {
		addItem: function () {
			this.view =
				this.modal_topic == this.profilUpdateTopic["Private Kontakte"]
					? "EditKontakt"
					: "EditAdresse";

			//? updates the topic when a Kontakt or an Address should be added
			this.modal_topic =
				this.modal_topic == this.profilUpdateTopic["Private Kontakte"]
					? this.profilUpdateTopic["Add Kontakt"]
					: this.profilUpdateTopic["Add Adresse"];
			this.$emit("update:topic", this.modal_topic);
			this.breadcrumbItems.push(this.modal_topic);
			this.$emit("update:breadcrumb", this.breadcrumbItems);

			this.data =
				this.view == "EditAdresse"
					? {
						//? add flag
						add: true,
						adresse_id: null,
						strasse: null,
						typ: null,
						plz: null,
						ort: null,
						zustelladresse: false,
					}
					: {
						//? add flag
						add: true,
						kontakt_id: null,
						kontakttyp: null,
						kontakt: null,
						anmerkung: null,
						zustellung: false,
					};
		},

		deleteItem: function (item) {
			//? delete flag
			item.data.delete = true;
			this.$emit("update:profilUpdate", item.data);
			//? updates the topic when a Kontakt or an Address should be deleted

			this.modal_topic = this.modal_topic == this.profilUpdateTopic["Private Adressen"]
				? this.profilUpdateTopic["Delete Adresse"]
				: this.profilUpdateTopic["Delete Kontakt"];
			this.$emit("update:topic", this.modal_topic);
			this.$emit("submit");
		},

		profilUpdateEmit: function (event) {
			//? passes the updated profil information to the parent component
			this.$emit("update:profilUpdate", event);
		},

		updateOptions: function (event, item) {
			this.properties = item;
			this.data = item.data;
			this.view = item.view;
			if (item.title) {
				//? emits the selected topic to the parent component
				this.modal_topic = item.topic;
				this.$emit("update:topic", this.modal_topic);

				//? emits the new item for the breadcrumb in the parent component
				this.breadcrumbItems.push(item.title);
			} else {
				if (item.data.kontakttyp) {
					this.breadcrumbItems.push(item.data.kontakttyp);
					this.breadcrumbItems.push(item.data.kontakt);
				} else if (item.data.strasse) {
					this.breadcrumbItems.push(item.data.strasse);
				}
			}
			this.$emit("update:breadcrumb", this.breadcrumbItems);
		},
	},
	computed: {},
	created() {
		//? JSON parse and stringify are used to deep clone the objects
		this.properties = {...this.list};
		this.data = this.list.data
			? JSON.parse(JSON.stringify(this.list.data))
			: null;
		this.view = this.list.view
			? JSON.parse(JSON.stringify(this.list.view))
			: null;
	},
	mounted() {
	},

	template: /*html*/ `
    <template v-if="!view">
        <div  class="list-group">
            <template v-for="item in data">
                <div class="d-flex flex-row align-items-center">
                    <button style="position:relative" type="button" class=" list-group-item list-group-item-action" @click="updateOptions($event,item)" >
                        <!-- render title of options -->
                        <p v-if="item.title" class="my-1"   >{{item.title}}</p>
                        <!-- else render list view of items -->
                        <div v-else class="my-2 me-4" >
                            <component :is="item.listview" v-bind="item"></component>
                        </div>
                    </button>
                    <button v-if="item.listview" @click="deleteItem(item)" type="button" class="mx-3 btn btn-danger btn-circle"><i class="fa fa-trash"></i>
                </div>
            </template>
        </div>
        <div v-if="Array.isArray(data)" class="mt-4 d-flex justify-content-center">
          <button @click="addItem" type="button" class="btn btn-primary btn-circle"><i class="fa fa-plus"></i>
        </div>
    </template>
    <template v-else>
      <component @profilUpdate="profilUpdateEmit" :is="view" v-bind="properties" :data="data" ></component>
    </template>
   `,
};
