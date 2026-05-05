import ApiRoomType from "../../../js/api/factory/roomType.js";
import ApiRoomToRoomType from "../../../js/api/factory/roomToRoomType.js";

import { CoreFilterCmpt } from "../filter/Filter.js";

import BsModal from "../Bootstrap/Modal.js";
import CoreForm from "../Form/Form.js";
import FormInput from "../Form/Input.js";

export default {
  name: "RoomTypeFormModal",
  components: {
    BsModal,
    CoreForm,
    FormInput,
    CoreFilterCmpt,
  },
  props: {
    isVisible: {
      type: Boolean,
      required: true,
    },
    editedRoomShortCode: {
      type: String,
      default: null,
    },
  },
  emits: [
    "hideBsModal",
    "roomTypeCreated",
    "roomToRoomTypeCreated",
    "roomToRoomTypeDeleted",
  ],
  watch: {
    isVisible(newValue) {
      if (newValue) {
        this.$refs.roomTypeFormModal.show();
      } else {
        this.$refs.roomTypeFormModal.hide();
      }
    },
    async editedRoomShortCode(newValue) {
      if (newValue) {
        await this.$refs.roomTypesTable.reloadTable();
        this.$refs.roomTypeFormModal.show();
      } else {
        this.resetRoomTypeForm();
      }
    },
  },
  data: () => {
    return {
      phrasesLoaded: false,
      isEditInProgress: false,
      editedRoom: null,
      isRoomTypeFormVisible: false,
      roomTypeFormData: {
        aktiv: true,
      },
      roomToRoomTypeFormData: {},
      roomTypes: [],
      filteredRoomTypes: [],
    };
  },
  computed: {
    tabulatorOptions() {
      const options = {
        ajaxURL: "dummy",
        ajaxRequestFunc: async () =>
          this.$api.call(
            ApiRoomToRoomType.getRoomToRoomTypeRelationsByRoomShortCode(
              this.editedRoomShortCode,
            ),
          ),
        ajaxResponse: (url, params, response) => response.data,
        persistenceID: "room_type_assignment_table",
        selectableRows: true,
        columns: [
          {
            title: this.$capitalize(this.$p.t("ui", "roomType")),
            field: "raumtyp_kurzbz",
          },
          {
            title: this.$capitalize(this.$p.t("ui", "hierarchy")),
            field: "hierarchie",
          },
          {
            title: this.$capitalize(this.$p.t("gruppenmanagement", "beschreibung")),
            field: "raumtyp_beschreibung",
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
              button.className =
                "btn btn-outline-secondary btn-action bg-danger";
              button.innerHTML = '<i class="fa fa-xmark text-white"></i>';
              button.title = this.$p.t(
                "ui",
                "btn_deleteRoomToRoomTypeRelation",
              );
              button.addEventListener("click", () => {
                let isDeletionConfirmed = confirm(
                  this.$p.t("ui", "deleteRoomToRoomTypeRelationConfirmation"),
                );
                if (!isDeletionConfirmed) return;

                this.deleteRoomToRoomTypeRelation(
                  cell.getData().ort_kurzbz,
                  cell.getData().raumtyp_kurzbz,
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
    dropdownParsedRoomTypes() {
      return this.roomTypes.map((roomType) => {
        return {
          label: `${roomType.raumtyp_kurzbz} - ${roomType.beschreibung}`,
          value: roomType.raumtyp_kurzbz,
        };
      });
    },
  },
  methods: {
    filterRoomTypes(event) {
      let defaultItem = {
        label: "----------",
        value: null,
      };

      const query = event.query.toLowerCase();
      if (!query) {
        return (this.filteredRoomTypes = [
          defaultItem,
          ...this.dropdownParsedRoomTypes,
        ]);
      }

      return (this.filteredRoomTypes = [defaultItem]
        .concat(this.dropdownParsedRoomTypes)
        .filter((roomType) => {
          return roomType.label?.toLowerCase().includes(query);
        }));
    },
    createRoomType() {
      return this.$refs.roomTypeForm
        .call(
          ApiRoomType.createRoomType({
            kurzbezeichnung: this.roomTypeFormData.shortCode,
            beschreibung: this.roomTypeFormData.description,
          }),
        )
        .then((response) => {
          this.$fhcAlert.alertSuccess(this.$p.t("ui", "successSave"));
          this.$emit("roomTypeCreated");
          this.resetRoomTypeForm();
          this.isRoomTypeFormVisible = false;
          this.fetchRoomTypes();
        });
    },
    createRoomToRoomTypeRelation() {
      return this.$refs.roomToRoomTypeForm
        .call(
          ApiRoomToRoomType.createRoomToRoomTypeRelation(
            this.editedRoomShortCode,
            this.roomToRoomTypeFormData.roomType?.value,
            this.roomToRoomTypeFormData.hierarchy,
          ),
        )
        .then((response) => {
          this.$fhcAlert.alertSuccess(this.$p.t("ui", "successSave"));
          this.$emit("roomToRoomTypeCreated");
          this.resetRoomTypeForm();
          this.$refs.roomTypesTable.tabulator.replaceData("/");
        });
    },
    deleteRoomToRoomTypeRelation(roomShortCode, roomTypeShortCode) {
      return this.$api
        .call(
          ApiRoomToRoomType.deleteRoomToRoomTypeRelation(
            roomShortCode,
            roomTypeShortCode,
          ),
        )
        .then((response) => {
          this.$fhcAlert.alertSuccess(this.$p.t("ui", "successDelete"));
          this.$emit("roomToRoomTypeDeleted");
          this.resetRoomTypeForm();
          this.$refs.roomTypesTable.tabulator.replaceData("/");
        });
    },
    hideRoomTypeFormModal() {
      this.$refs.roomTypeFormModal.hide();
      this.$emit("hideBsModal");
      this.resetRoomTypeForm();
    },
    resetRoomTypeForm() {
      this.$refs.roomTypeForm?.clearValidation();

      this.isEditInProgress = false;
      this.isRoomTypeFormVisible = false;

      this.editedRoom = null;
      this.roomTypeFormData = {
        aktiv: true,
      };
    },
    async fetchRoomTypes() {
      let getRoomTypesResponse = await this.$api.call(
        ApiRoomType.getAllRoomTypes(),
      );
      if (getRoomTypesResponse.meta.status === "success") {
        this.roomTypes = getRoomTypesResponse.data;
      } else {
        console.error(
          "Error fetching room types:",
          getRoomTypesResponse.meta.message,
        );
      }
    },
  },
  async created() {
    this.fetchRoomTypes();

    this.$p
      .loadCategory(["global", "lehre", "ui", "gruppenmanagement", "core", "person"])
      .then(() => {
        this.phrasesLoaded = true;
      });
  },
  template: /* html */ `
  <bs-modal ref="roomTypeFormModal" size="sm" @hideBsModal="() => { $emit('hideBsModal'); resetRoomTypeForm(); }" class="modal-lg">
			<template #title>
				<p class="fw-bold mt-3">{{$capitalize($p.t('ui', 'assignRoomTypeToRoomModalTitle'))}}</p>
			</template>
      <template #default>
        <div class="justify-content-end d-flex mb-1">
          <a 
            v-if="!isRoomTypeFormVisible" 
            :title='$p.t("ui", "createRoomType")' 
            @click.prevent="isRoomTypeFormVisible = !isRoomTypeFormVisible"
            href="#"
            class="btn btn-primary rounded-circle">
            <i
              class="fa fa-plus"
            ></i> 
          </a>
        </div>
        <core-form v-if="isRoomTypeFormVisible" ref="roomTypeForm" class="row g-3 pb-3">
          <div class="row">
            <div class="col">
              <p class="fw-bold">{{$capitalize($p.t('ui', 'createRoomTypeFormTitle'))}}</p>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col">
              <form-input
                v-model="roomTypeFormData.shortCode"
                :label="$capitalize($p.t('gruppenmanagement', 'kurzbezeichnung'))"
                type="text"
                name="kurzbezeichnung"  
                >
              </form-input>
            </div>
            <div class="col">
              <form-input
                v-model="roomTypeFormData.description"
                :label="$capitalize($p.t('gruppenmanagement', 'beschreibung'))"
                type="text"
                name="beschreibung"  
                >
              </form-input>
            </div>
          </div>
          <div class="col d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-secondary" @click="isRoomTypeFormVisible = false">{{$p.t('ui', 'abbrechen')}}</button>
            <button type="button" class="btn btn-primary" @click="createRoomType()">{{$p.t('ui', 'speichern')}}</button>
          </div>
        </core-form>
        <core-form  v-if="!isRoomTypeFormVisible" ref="roomToRoomTypeForm" class="row g-3 pb-3">
          <div class="col-8">
            <form-input
              v-model="roomToRoomTypeFormData.roomType"
              :label="$capitalize($p.t('ui/parentRoom'))"
              :suggestions="filteredRoomTypes"
              :optionValue="(option) => option.value"
              :optionLabel="(option) => option.label" 
              @complete="filterRoomTypes"
              dropdown
              forceSelection
              type="autocomplete"
              name="roomTypeShortCode"  
              >
            </form-input>
          </div>
          <div class="col">
              <form-input
                v-model="roomToRoomTypeFormData.hierarchy"
                :label="$capitalize($p.t('ui', 'hierarchy'))"
                type="number"
                name="hierarchy"  
                >
              </form-input>
            </div>
          <div class="col justify-content-end align-items-end d-flex">
            <button type="button" class="btn btn-primary" @click="createRoomToRoomTypeRelation()">{{$p.t('ui', 'speichern')}}</button>
          </div>
        </core-form>
        <hr>
        <div class="row my-1">
          <div class="col">
            <p class="fw-bold">{{$capitalize($p.t('ui', 'assignedRoomTypesTitle'))}}</p>
          </div>
        </div>
        <core-filter-cmpt
            v-if="phrasesLoaded"
            ref="roomTypesTable"
            table-only	 
            :side-menu="false"
            :tabulator-options="tabulatorOptions"
            :tabulator-events="tabulatorEvents"	 
        >
      </core-filter-cmpt> 
      </template>
		</bs-modal>
  `,
};
