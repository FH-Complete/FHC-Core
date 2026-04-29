import ApiRoom from "../../../js/api/factory/ort.js";
import ApiLocation from "../../../js/api/factory/location.js";
import ApiOrganizationalUnit from "../../../js/api/factory/organizationalUnit.js";

import { CoreFilterCmpt } from "../filter/Filter.js";
import CoreForm from "../Form/Form.js";
import FormInput from "../Form/Input.js";
import RoomFormModal from "./RoomFormModal.js";

import ApiCms from "../../../js/api/factory/cms.js";

export default {
  name: "RoomManagerOverview",
  components: {
    CoreFilterCmpt,
    CoreForm,
    FormInput,
    RoomFormModal,
  },
  watch: {
    filterData: {
      handler(newValue) {
        this.$refs.roomManagerOverviewTable.tabulator.setData("/", {
          organizationalUnitShortCode: newValue.organizationalUnit?.value,
          locationId: newValue.locationId,
          buildingComponent: newValue.buildingComponent,
          isForTrainingProgram: newValue.isForTrainingProgram,
          isReservationNeeded: newValue.isReservationNeeded,
          isActive: newValue.isActive,
        });
      },
      deep: true,
    },
  },
  data() {
    return {
      phrasesLoaded: false,
      filterData: {
        locationId: null,
        organizationalUnit: null,
        buildingComponent: null,
        isForTrainingProgram: false,
        isReservationNeeded: false,
        isActive: false,
      },
      locations: [],
      organizationalUnits: [],
      filteredOrganizationalUnits: [],
      buildingComponents: ["A", "B", "C", "D", "E", "F"],
      isRoomFormModalVisible: false,
      editedRoomShortCode: null,
    };
  },
  computed: {
    tabulatorOptions() {
      const options = {
        ajaxURL: "dummy",
        ajaxRequestFunc: async () =>
          this.$api.call(
            ApiRoom.getAllRooms({
              organizationalUnitShortCode:
                this.filterData.organizationalUnit?.value,
              locationId: this.filterData.locationId,
              buildingComponent: this.filterData.buildingComponent,
              isForTrainingProgram: this.filterData.isForTrainingProgram,
              isReservationNeeded: this.filterData.isReservationNeeded,
              isActive: this.filterData.isActive,
            }),
          ),
        ajaxResponse: (url, params, response) => response.data,
        persistenceID: "core_class_schedule_validity_periods",
        selectableRows: true,
        columns: [
          {
            title: this.$capitalize(this.$p.t("gruppenmanagement", "kurzbezeichnung")),
            field: "ort_kurzbz",
          },
          {
            title: this.$capitalize(this.$p.t("gruppenmanagement", "bezeichnung")),
            field: "bezeichnun",
          },
          {
            title: this.$capitalize(this.$p.t("ui", "planbezeichnung")),
            field: "planbezeichnung",
          },
          {
            title: this.$capitalize(this.$p.t("ui", "maxPersons")),
            field: "max_person",
          },
          {
            title: this.$capitalize(this.$p.t("ui", "arbeitsplaetze")),
            field: "arbeitsplaetze",
          },
          {
            title: this.$capitalize(this.$p.t("ui", "quadratmeter")),
            field: "m2",
          },
          {
            title: this.$capitalize(this.$p.t("lehre", "organisationseinheit")),
            field: "oe_kurzbz",
          },
          {
            title: this.$capitalize(this.$p.t("ui", "lehre")),
            field: "lehre",
          },
          {
            title: this.$capitalize(this.$p.t("ui", "reservieren")),
            field: "reservieren",
          },
          {
            title: this.$capitalize(this.$p.t("gruppenmanagement", "aktiv")),
            field: "aktiv",
          },
          {
            title: this.$capitalize(this.$p.t("ui", "kosten")),
            field: "kosten",
          },
          {
            title: this.$capitalize(this.$p.t("ui", "stockwerk")),
            field: "stockwerk",
          },
          {
            title: this.$capitalize(this.$p.t("global", "actions")),
            field: "actions",
            minWidth: 150,
            maxWidth: 150,
            formatter: (cell, formatterParams, onRendered) => {
              let container = document.createElement("div");
              container.className = "d-flex gap-2";

              let button = document.createElement("button");

              button = document.createElement("button");
              button.className = "btn btn-outline-secondary btn-action";
              button.innerHTML = '<i class="fa fa-edit"></i>';
              button.title = this.$p.t(
                "ui",
                "btn_editRoom",
              );
              button.addEventListener("click", (event) =>
                this.editRoom(
                  cell.getData().ort_kurzbz,
                ),
              );
              container.append(button);

              button = document.createElement("button");
              button.className =
                "btn btn-outline-secondary btn-action bg-danger";
              button.innerHTML = '<i class="fa fa-xmark text-white"></i>';
              button.title = this.$p.t(
                "ui",
                "btn_deleteRoom",
              );
              button.addEventListener("click", () => {
                let isDeletionConfirmed = confirm(
                  this.$p.t(
                    "ui",
                    "deleteRoomConfirmation",
                  ),
                );
                if (!isDeletionConfirmed) return;

                this.deleteRoom(
                  cell.getData().ort_kurzbz
                );
              });
              container.append(button);

              return container;
            },
            frozen: true,
          },
        ],
      };
      return options;
    },
    tabulatorEvents() {
      const events = [
        {
          event: "renderComplete",
          handler: async () => {},
        },
      ];
      return events;
    },
    dropdownParsedOrganizationalUnits() {
      return this.organizationalUnits.map((unit) => {
        return {
          label: `${unit.bezeichnung} (${unit.organisationseinheittyp_kurzbz})`,
          value: unit.oe_kurzbz,
        };
      });
    },
  },
  methods: {
    filterOrganizationalUnits(event) {
      let defaultItem = {
        label: "----------",
        value: null,
      };

      const query = event.query.toLowerCase();
      if (!query) {
        return (this.filteredOrganizationalUnits = [
          defaultItem,
          ...this.dropdownParsedOrganizationalUnits,
        ]);
      }

      return (this.filteredOrganizationalUnits = [defaultItem].concat(
        this.dropdownParsedOrganizationalUnits.filter((unit) => {
          return unit.label.toLowerCase().includes(query);
        }),
      ));
    },
    showRoomFormModal() {
      this.isRoomFormModalVisible = true;
    },
    editRoom(roomShortCode) {
      this.editedRoomShortCode = roomShortCode;
    },
    deleteRoom(roomShortCode) {
      this.$api
        .call(ApiRoom.deleteRoom(roomShortCode))
        .then((response) => {
          if (response.meta.status === "success") {
            this.$refs.roomManagerOverviewTable.reloadTable();
            alert(this.$p.t("ui", "roomDeletedSuccessfully"));
          } else {
            console.error("Error deleting room:", response.meta.message);
            alert(this.$p.t("ui", "errorDeletingRoom"));
          }
        })
        .catch((error) => {
          console.error("Error deleting room:", error);
          alert(this.$p.t("ui", "errorDeletingRoom"));
        });
    }
  },
  async created() {
    let getContent = await this.$api.call(ApiCms.content(7601));
    if (getContent.meta.status === "success") {
      console.log;
    } else {
      console.error("Error fetching locations:", getContent.meta.message);
    }

    let getLocationsResponse = await this.$api.call(
      ApiLocation.getLocationsByCompanyType("Intern"),
    );
    if (getLocationsResponse.meta.status === "success") {
      this.locations = getLocationsResponse.data;
    } else {
      console.error(
        "Error fetching locations:",
        getLocationsResponse.meta.message,
      );
    }

    let getAllOrganizationalUnitsResponse = await this.$api.call(
      ApiOrganizationalUnit.getAllOrganizationalUnits(),
    );
    if (getAllOrganizationalUnitsResponse.meta.status === "success") {
      this.organizationalUnits = getAllOrganizationalUnitsResponse.data.sort(
        (a, b) => a.bezeichnung.localeCompare(b.bezeichnung),
      );
    } else {
      console.error(
        "Error fetching organizational units:",
        getAllOrganizationalUnitsResponse.meta.message,
      );
    }
  },
  mounted() {
    this.$p
      .loadCategory(["global", "lehre", "ui", "gruppenmanagement", "core", "person"])
      .then(() => {
        this.phrasesLoaded = true;
      });
  },
  template: /* html */ `
  <div class="container mt-4">
    <h1 class='mb-5'>{{ $capitalize($p.t("ui", "roomManagerOverviewHeading")) }}</h1>
    <div class="row mb-3">
      <div class="col d-flex justify-content-between">
        <a class="btn btn-primary mb-3" @click="showRoomFormModal">{{$capitalize($p.t('ui', 'addRoomButton'))}}</a>
      </div>
    </div>
    <core-filter-cmpt  
        v-if="phrasesLoaded"
        ref="roomManagerOverviewTable"
        table-only	 
        :side-menu="false"	 
        :tabulator-options="tabulatorOptions"
        :tabulator-events="tabulatorEvents"	 
    >
      <template #search>
        <slot name="filterzuruecksetzen">
          <core-form class="d-flex flex-column flex-md-row align-items-md-end gap-3">
            <div>
              <form-input
                v-model="filterData.organizationalUnit"
                :label="$capitalize($p.t('lehre/organisationseinheit'))"
                :suggestions="filteredOrganizationalUnits"
                :optionValue="(option) => option.value"
                :optionLabel="(option) => option.label" 
                @complete="filterOrganizationalUnits"
                dropdown
                forceSelection
                type="autocomplete"
                name="organizationalUnitShortCode"  
                >
              </form-input>
            </div>
            <div>
              <form-input
                v-model="filterData.locationId"
                :label="$capitalize($p.t('global', 'raum'))"
                type="select"
                id="location"
                name="location"
                >
                <option
                  v-for="location in locations"
                  :key="location.standort_id"
                  :value="location.standort_id"
                  >
                  {{location.kurzbz}}
                </option>
              </form-input>
            </div>
            <div>
              <form-input
                v-model="filterData.buildingComponent"
                :label="$capitalize($p.t('ui', 'buildingComponent'))"
                type="select"
                id="buildingComponent"
                name="buildingComponent"
                >
                <option
                  v-for="component in buildingComponents"
                  :key="component"
                  :value="component"
                  >
                  {{component}}
                </option>
              </form-input>
            </div>
            <div>
               <form-input
                v-model="filterData.isForTrainingProgram"
                :label="$capitalize($p.t('ui', 'lehre'))"
                type="checkbox"
                name="filterIsForTrainingProgram"
                dropdown
              ></form-input>
            </div>
            <div>
              <form-input
                v-model="filterData.isReservationNeeded"
                :label="$capitalize($p.t('ui', 'reservieren'))"
                type="checkbox"
                name="filterIsReservationNeeded"
                dropdown
              ></form-input>
            </div>
            <div>
              <form-input
                v-model="filterData.isActive"
                :label="$capitalize($p.t('person', 'aktiv'))"
                type="checkbox"
                name="filterIsActive"
                dropdown
              ></form-input>
            </div>
          </core-form>
        </slot>
      </template>
    </core-filter-cmpt> 
    <room-form-modal
      :isVisible="isRoomFormModalVisible"
      :editedRoomShortCode="editedRoomShortCode"
      @hideBsModal="() => { isRoomFormModalVisible = false; editedRoomShortCode = null; }"
      @roomCreated="() => { $refs.roomManagerOverviewTable.reloadTable(); editedRoomShortCode = null; }"
      @roomUpdated="() => { $refs.roomManagerOverviewTable.reloadTable(); editedRoomShortCode = null; }"
    />
  </div>
  `,
};
