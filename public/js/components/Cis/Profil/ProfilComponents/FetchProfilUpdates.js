import EditProfil from "../ProfilModal/EditProfil.js";
//? EditProfil is the modal used to edit the profil updates
export default {
	components: {EditProfil},
	props: {
		data: {
			type: Object,
		},
	},

	inject: [
		"getZustellkontakteCount",
		"getZustelladressenCount",
		"profilUpdateStates",
		"profilUpdateTopic",
	],

	emits: ["fetchUpdates"],

	data() {
		return {
			showUpdateModal: false,
			content: null,
			editProfilTitle: this.$p.t("profil", "profilBearbeiten"),
		};
	},

	methods: {
		hideEditProfilModal: function () {
			//? checks the editModal component property result, if the user made a successful request or not
			if (this.$refs.updateEditModal.result) {
				this.$emit("fetchUpdates");
			} else {
				// when modal was closed without submitting request
			}
			this.showUpdateModal = false;
		},

		async showEditProfilModal(updateRequest) {

			let view = this.getView(updateRequest.topic, updateRequest.status);

			let data = null;
			let content = null;
			let files = null;
			let withFiles = false;

			if (view === "TextInputDokument") {
				data = {
					titel: updateRequest.topic,
					value: updateRequest.requested_change.value,
				};

				const filesFromDatabase =
					await this.$fhcApi.factory.profilUpdate.getProfilRequestFiles(
						updateRequest.profil_update_id
					).then((res) => {
						return res.data;
					});

				files = filesFromDatabase;
				if (files) {
					withFiles = true;
				}
			} else {
				data = updateRequest.requested_change;
			}

			content = {
				updateID: updateRequest.profil_update_id,
				view: view,
				data: data,
				withFiles: withFiles,
				topic: updateRequest.topic,
				files: files,
			};

			if (view === "EditAdresse") {

				const isMitarbeiter = await this.$fhcApi.factory.profil.isMitarbeiter(updateRequest.uid).then((res) => res.data);

				if (isMitarbeiter) {
					content["isMitarbeiter"] = isMitarbeiter;
				}
			}

			//? adds the status information if the profil update request was rejected or accepted
			if (updateRequest.status !== this.profilUpdateStates["Pending"]) {
				content["status"] = updateRequest.status;
				content["status_message"] = updateRequest.status_message;
				content["status_timestamp"] = updateRequest.status_timestamp;
			}

			//? update data of the reactive content
			this.content = content;
			this.editProfilTitle = updateRequest.topic;

			//? only show the popup if also the right content is available
			if (content) {
				this.showUpdateModal = true;
				// after a state change, wait for the DOM updates to complete
				Vue.nextTick(() => {
					this.$refs.updateEditModal.show();
				});
			}
		},

		deleteRequest: function (item) {
			this.$fhcApi.factory.profilUpdate.deleteProfilRequest(item.profil_update_id).then(
				(res) => {
					if (res.data.error) {
						//? open alert
						console.error("error happened", res.data);
					} else {
						this.$emit("fetchUpdates");
					}
				}
			);
		},

		getView: function (topic, status) {
			if (!(status === this.profilUpdateStates["Pending"])) {
				return "Status";
			}

			switch (topic) {
				case this.profilUpdateTopic["Private Kontakte"]:
					return "EditKontakt";
				case this.profilUpdateTopic["Add Kontakt"]:
					return "EditKontakt";
				case this.profilUpdateTopic["Delete Kontakt"]:
					return "Kontakt";
				case this.profilUpdateTopic["Private Adressen"]:
					return "EditAdresse";
				case this.profilUpdateTopic["Add Adresse"]:
					return "EditAdresse";
				case this.profilUpdateTopic["Delete Adresse"]:
					return "Adresse";
				default:
					return "TextInputDokument";
			}
		},

	},
	created() {
	},

	computed: {},

	template: /*html*/ `
<div class="card">
    <edit-profil v-if="showUpdateModal" ref="updateEditModal" @hideBsModal="hideEditProfilModal" :value="content" :title="editProfilTitle"></edit-profil>
    <div class="card-header">{{$p.t('profilUpdate','profilUpdates')}}</div>
    <div class="card-body">
        <div class="table-responsive text-nowrap">
            <table class="m-0  table  table-hover align-middle">
                <thead>
                    <tr>
                        <th scope="col">{{$p.t('profilUpdate','topic')}}</th>
                        <th scope="col">{{$p.t('global','status')}}</th>
                        <th scope="col">{{$p.t('global','datum')}}</th>
                        <th class="text-center" scope="col">{{$p.t('ui','aktion')}}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in data" :style="item.status==profilUpdateStates['Accepted']?'background-color:lightgreen':item.status===profilUpdateStates['Rejected']?'background-color:lightcoral':''">
                        <td class="align-middle text-wrap ">{{item.topic}}</td>
                        <td class="align-middle " >{{item.status}}</td>
                        <td class="align-middle">{{item.status_timestamp?item.status_timestamp:item.insertamum}}</td>
                        <template v-if="item.status === profilUpdateStates['Pending']">
                            <td>
                                <div class="d-flex flex-row justify-content-evenly">
                                    <template v-if="item.topic.toLowerCase().includes('delete')">
                                        <div  class="align-middle text-center"><i role="button" @click="showEditProfilModal(item)" class="fa fa-eye"></i></div>
                                    </template>
                                    <template v-else >
                                        <div class="align-middle text-center" ><i style="color:#00639c" @click="showEditProfilModal(item)" role="button" class="fa fa-edit"></i></div>
                                    </template>
                                    <div class="align-middle text-center"><i style="color:red" role="button" @click="deleteRequest(item)" class="fa fa-trash"></i></div>
                                </div>
                            </td>
                        </template>          
                        <template v-else>
                            <td class="align-middle text-center">
                                <div class="d-flex flex-row justify-content-evenly">
                                    <i role="button" @click="showEditProfilModal(item)" class="fa fa-eye"></i>
                                </div>
                            </td>
                        </template>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
`,
};
