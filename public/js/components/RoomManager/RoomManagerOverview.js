import ApiRoom from "../../../js/api/factory/ort.js";
import ApiLocation from "../../../js/api/factory/location.js";
import ApiOrganizationalUnit from "../../../js/api/factory/organizationalUnit.js";

import { CoreFilterCmpt } from "../filter/Filter.js";
import CoreForm from "../Form/Form.js";
import FormInput from "../Form/Input.js";
import RoomFormModal from "./RoomFormModal.js";
import RoomTypeFormModal from "./RoomTypeFormModal.js";

export default {
  name: "RoomManagerOverview",
  components: {
    CoreFilterCmpt,
    CoreForm,
    FormInput,
    RoomFormModal,
    RoomTypeFormModal,
  },
  props: {
    permissions: Object,
  },
  provide() {
    return {
      cisRoot: this.cisRoot,
      hasBasisOrtWPermission:
        this.permissions["basis/ort_w"] || false,
    };
  },
  watch: {
    filterData: {
      handler(newValue) {
        this.reloadTableData();
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
      buildingComponents: [
        {
          label: "----------",
          value: null,
        }, 
        {
          label: "A",
          value: "A",
        }, 
        {
          label: "B",
          value: "B",
        },
        {
          label: "C",
          value: "C",
        },
        {
          label: "D", 
          value: "D"
        },
        {
          label: "E",
          value: "E"
        },
        {
          label: "F",
          value: "F"
        },
      ],
      isRoomFormModalVisible: false,
      isRoomTypeFormModalVisible: false,
      editedRoomShortCode: null,
      editedRoomForRoomTypeManagement: null,
    };
  },
  computed: {
    hasBasisOrtWPermission() {
      return this.permissions["basis/ort_w"] || false;
    },
    tabulatorOptions() {
      const options = {
        ajaxURL: "dummy",
        ajaxRequestFunc: async (url, config, params) => {
          let shortCodeFilter = params?.filter?.find((filter) => filter.field === "ort_kurzbz");
          let descriptionFilter = params?.filter?.find((filter) => filter.field === "bezeichnung");
          let planDescriptionFilter = params?.filter?.find((filter) => filter.field === "planbezeichnung");
          let maxPersonsFilter = params?.filter?.find((filter) => filter.field === "max_person");
          let workplaceFilter = params?.filter?.find((filter) => filter.field === "arbeitsplaetze");
          let squareMetersFilter = params?.filter?.find((filter) => filter.field === "m2");
          let orgUnitFilter = params?.filter?.find((filter) => filter.field === "org_bezeichnung");
          let isForTrainingProgramFilter = params?.filter?.find((filter) => filter.field === "lehre");
          let reservationNeededFilter = params?.filter?.find((filter) => filter.field === "reservieren");
          let isActiveFilter = params?.filter?.find((filter) => filter.field === "aktiv");
          let costsFilter = params?.filter?.find((filter) => filter.field === "kosten");
          let floorFilter = params?.filter?.find((filter) => filter.field === "stockwerk");
          let parentRoomFilter = params?.filter?.find((filter) => filter.field === "pr_ort_kurzbz");

          let isForTrainingProgramValue = this.filterData.isForTrainingProgram ? "true" : isForTrainingProgramFilter?.value ? "true" : "false";
          let reservationNeededValue = this.filterData.isReservationNeeded ? "true" : reservationNeededFilter?.value ? "true" : "false";
          let isActiveValue = this.filterData.isActive ? "true" : isActiveFilter?.value ? "true" : "false";

          return this.$api.call(
            ApiRoom.getAllRooms({
              organizationalUnitShortCode:
                this.filterData.organizationalUnit?.value,
              locationId: this.filterData.locationId,
              buildingComponent: this.filterData.buildingComponent,
              isForTrainingProgram: isForTrainingProgramValue,
              isReservationNeeded: reservationNeededValue,
              isActive: isActiveValue,
              shortCode: shortCodeFilter?.value,
              description: descriptionFilter?.value,
              planDescription: planDescriptionFilter?.value,
              maxPersons: maxPersonsFilter?.value,
              workplace: workplaceFilter?.value,
              squareMeters: squareMetersFilter?.value,
              orgUnitDescription: orgUnitFilter?.value,
              costs: costsFilter?.value,
              floor: floorFilter?.value,
              parentRoomShortCode: parentRoomFilter?.value,
              pagination: {
                page: params.page,
                size: params.size,
              },
            }),
          );
        },
        ajaxResponse: (url, params, response) => response,
        persistenceID: "room_manager_overview_table1111222233333",
        selectableRows: true,
        index: "ort_kurzbz",
        columns: [
          {
            title: this.$capitalize(
              this.$p.t("gruppenmanagement", "kurzbezeichnung"),
            ),
            field: "ort_kurzbz",
            headerFilter: true,
            width: 100,
          },
          {
            title: this.$capitalize(
              this.$p.t("gruppenmanagement", "bezeichnung"),
            ),
            field: "bezeichnung",
            headerFilter: true,
            width: 200,
          },
          {
            title: this.$capitalize(this.$p.t("ui", "planbezeichnung")),
            field: "planbezeichnung",
            headerFilter: true,
            width: 150,
          },
          {
            title: this.$capitalize(this.$p.t("ui", "maxPersons")),
            field: "max_person",
            headerFilter: true,
            width: 80,
          },
          {
            title: this.$capitalize(this.$p.t("ui", "arbeitsplaetze")),
            field: "arbeitsplaetze",
            headerFilter: true,
            width: 80,
          },
          {
            title: this.$capitalize(this.$p.t("ui", "quadratmeter")),
            field: "m2",
            headerFilter: true,
            width: 100
          },
          {
            title: this.$capitalize(this.$p.t("lehre", "organisationseinheit")),
            field: "org_bezeichnung",
            headerFilter: true,
            width: 180,
          },
          {
            title: this.$capitalize(this.$p.t("ui", "lehre")),
            field: "lehre",
            headerFilter: true,
            formatter: "tickCross",
            hozAlign: "center",
            formatterParams: {
              tickElement: '<i class="fa fa-check text-success"></i>',
              crossElement: '<i class="fa fa-xmark text-danger"></i>',
            },
          },
          {
            title: this.$capitalize(this.$p.t("ui", "reservieren")),
            field: "reservieren",
            headerFilter: true,
            formatter: "tickCross",
            hozAlign: "center",
            formatterParams: {
              tickElement: '<i class="fa fa-check text-success"></i>',
              crossElement: '<i class="fa fa-xmark text-danger"></i>',
            },
          },
          {
            title: this.$capitalize(this.$p.t("gruppenmanagement", "aktiv")),
            field: "aktiv",
            headerFilter: true,
            formatter: "tickCross",
            hozAlign: "center",
            formatterParams: {
              tickElement: '<i class="fa fa-check text-success"></i>',
              crossElement: '<i class="fa fa-xmark text-danger"></i>',
            },
          },
          {
            title: this.$capitalize(this.$p.t("ui", "kosten")),
            field: "kosten",
            headerFilter: true,
          },
          {
            title: this.$capitalize(this.$p.t("ui", "stockwerk")),
            field: "stockwerk",
            headerFilter: true,
          },
          {
            title: this.$capitalize(this.$p.t("ui", "parentRoom")),
            field: "pr_ort_kurzbz",
            headerFilter: true,
            width_: 120,
          },
          {
            title: this.$capitalize(this.$p.t("global", "actions")),
            field: "actions",
            width: 120,
            formatter: (cell, formatterParams, onRendered) => {
              let container = document.createElement("div");
              container.className = "d-flex gap-2 justify-content-center";

              let roomTypeBtn = document.createElement("button");
              roomTypeBtn.className = "btn btn-outline-secondary btn-action";
              roomTypeBtn.innerHTML = '<i class="fa fa-layer-group"></i>';
              roomTypeBtn.title = this.$capitalize(this.$p.t("ui", "btn_editRoomType"));
              roomTypeBtn.addEventListener("click", (event) =>
                this.editRoomType(cell.getData().ort_kurzbz),
              );

              if (!this.hasBasisOrtWPermission) {
                container.append(roomTypeBtn);
                return container;
              }2222

              let button = document.createElement("button");

              button = document.createElement("button");
              button.className = "btn btn-outline-secondary btn-action";
              button.innerHTML = '<i class="fa fa-edit"></i>';
              button.title = this.$capitalize(this.$p.t("ui", "btn_editRoom"));
              button.addEventListener("click", (event) =>
                this.editRoom(cell.getData().ort_kurzbz),
              );
              container.append(button);

              container.append(roomTypeBtn);

              button = document.createElement("button");
              button.className =
                "btn btn-outline-secondary btn-action bg-danger";
              button.innerHTML = '<i class="fa fa-xmark text-white"></i>';
              button.title = this.$capitalize(this.$p.t("ui", "btn_deleteRoom"));
              button.addEventListener("click", () => {
                let isDeletionConfirmed = confirm(
                  this.$p.t("ui", "deleteRoomConfirmation"),
                );
                if (!isDeletionConfirmed) return;

                this.deleteRoom(cell.getData().ort_kurzbz);
              });
              container.append(button);

              return container;
            },
            frozen: true,
          },
        ],
        layout: "fitColumns",
        pagination:true,
        paginationMode:"remote",
        paginationSize: 100,
        maxHeight:"700px",
        filterMode:"remote", 
      };
      return options;
    },
    tabulatorEvents() {
      const events = [
        {
          event: "renderComplete",
          handler: async () => {},
        },
        {
          event: "cellClick",
          handler: async (e, cell) => {
            let updateableFieldsByClick = ["lehre", "reservieren", "aktiv"];
            for (let field of updateableFieldsByClick) {
              if (cell.getField() === field) {
                let updatedValue = !cell.getValue();
                this.$refs.roomManagerOverviewTable.tabulator.updateData([
                  {
                    ort_kurzbz: cell.getData().ort_kurzbz,
                    [field]: updatedValue,
                  },
                ]);
                this.partialRoomUpdate(
                  cell.getData().ort_kurzbz,
                  field,
                  updatedValue,
                );
                this.$refs.roomManagerOverviewTable.tabulator.replaceData("/");
              }
            }
          },
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

      return (this.filteredOrganizationalUnits = [defaultItem]
        .concat(this.dropdownParsedOrganizationalUnits)
        .filter((unit) => {
          return unit.label.toLowerCase().includes(query);
        }));
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
            this.reloadTableData();
            this.$fhcAlert.alertSuccess(
              this.$p.t("ui", "roomDeletedSuccessfully"),
            );
          } else {
            console.error("Error deleting room:", response.meta.message);
            this.reloadTableData();
            this.$fhcAlert.alertError(this.$p.t("ui", "errorDeletingRoom"));
          }
        })
        .catch((error) => {
          console.error("Error deleting room:", error);
          this.$fhcAlert.alertError(this.$p.t("ui", "errorDeletingRoom"));
        });
    },
    showRoomTypeFormModal() {
      this.isRoomTypeFormModalVisible = true;
    },
    editRoomType(roomShortCode) {
      this.editedRoomForRoomTypeManagement = roomShortCode;
    },
    async reloadTableData() {
      this.$refs.roomManagerOverviewTable.tabulator.replaceData("/");
    },
    handleRoomUpdated() {
      this.editedRoomShortCode = null;
      this.reloadTableData();
    },
    async partialRoomUpdate(roomShortCode, attribute, value) {
      let response = await this.$api.call(
        ApiRoom.updateRoom(roomShortCode, {
          [attribute]: value,
        }),
      );
      if (response.meta.status === "success") {
        this.$fhcAlert.alertSuccess(this.$p.t("ui", "successUpdate"));
        this.reloadTableData();
      } else {
        console.error("Error updating room:", response.meta.message);
        this.$fhcAlert.alertError(this.$p.t("ui", "errorUpdatingRoom"));
      }
    },
  },
  async created() {
    let getLocationsResponse = await this.$api.call(
      ApiLocation.getLocationsByCompanyType("Intern"),
    );
    if (getLocationsResponse.meta.status === "success") {
      this.locations = getLocationsResponse.data;
      this.locations.unshift({ standort_id: null, kurzbz: "----------" });
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
      .loadCategory([
        "global",
        "lehre",
        "ui",
        "gruppenmanagement",
        "core",
        "person",
      ])
      .then(() => {
        this.phrasesLoaded = true;
      });
  },
  template: /* html */ `
  <div class="container mt-4">
    <h1 class='mb-5'>{{ $capitalize($p.t("ui", "roomManagerOverviewHeading")) }}</h1>
    <div v-if="hasBasisOrtWPermission" class="row mb-3">
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
                :label="$capitalize($p.t('lehre/organisationseinheit'))"
                :suggestions="filteredOrganizationalUnits"
                :optionValue="(option) => option.value"
                :optionLabel="(option) => option.label" 
                @complete="filterOrganizationalUnits"
                @itemSelect="(option) => { filterData.organizationalUnit = option.value; }"
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
                  :value="component.value"
                  >
                  {{component.label}}
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
      @roomCreated="handleRoomUpdated"
      @roomUpdated="handleRoomUpdated"
    />
    <room-type-form-modal
      :isVisible="isRoomTypeFormModalVisible"
      :editedRoomShortCode="editedRoomForRoomTypeManagement"
      @hideBsModal="() => { isRoomTypeFormModalVisible = false; editedRoomForRoomTypeManagement = null; }"
    />
  </div>
  `,
};
