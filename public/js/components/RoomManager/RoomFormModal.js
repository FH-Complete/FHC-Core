import ApiRoom from "../../../js/api/factory/ort.js";
import ApiLocation from "../../../js/api/factory/location.js";
import ApiOrganizationalUnit from "../../../js/api/factory/organizationalUnit.js";

import BsModal from "../Bootstrap/Modal.js";
import CoreForm from "../Form/Form.js";
import FormInput from "../Form/Input.js";

export default {
  name: "RoomFormModal",
  components: {
    BsModal,
    CoreForm,
    FormInput,
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
  emits: ["hideBsModal", "roomCreated", "roomUpdated"],
  watch: {
    isVisible(newValue) {
      if (newValue) {
        this.$refs.roomFormModal.show();
      } else {
        this.$refs.roomFormModal.hide();
      }
    },
    editedRoomShortCode(newValue) {
      if (newValue) {
        this.editRoom(newValue);
      } else {
        this.resetRoomForm();
      }
    },
  },
  data: () => {
    return {
      isEditInProgress: false,
      organizationalUnits: [],
      filteredOrganizationalUnits: [],
      locations: [],
      rooms: [],
      filteredRooms: [],
      editedRoom: null,
      roomFormData: {
        aktiv: true,
      },
    };
  },
  computed: {
    dropdownParsedOrganizationalUnits() {
      return this.organizationalUnits.map((unit) => {
        return {
          label: `${unit.bezeichnung} (${unit.organisationseinheittyp_kurzbz})`,
          value: unit.oe_kurzbz,
        };
      });
    },
    dropdownParsedRooms() {
      return this.rooms.map((room) => {
        return {
          label: `${room.ort_kurzbz} - ${room.bezeichnung}`,
          value: room.ort_kurzbz,
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
    async filterRooms(event) {
      this.rooms = await this.fetchRooms(event.query);

      let defaultItem = {
        label: "----------",
        value: null,
      };

      const query = event.query.toLowerCase();
      if (!query) {
        return (this.filteredRooms = [
          defaultItem,
          ...this.dropdownParsedRooms,
        ]);
      }

      return (this.filteredRooms = [defaultItem]
        .concat(this.dropdownParsedRooms)
        .filter((room) => {
          return room.label?.toLowerCase().includes(query);
        }));
    },
    createRoom() {
      return this.$refs.roomForm
        .call(ApiRoom.createRoom(this.getApiCallParsedRoomFormData()))
        .then((response) => {
          this.$fhcAlert.alertSuccess(this.$p.t("ui", "successSave"));
          this.$emit("roomCreated");
          this.resetRoomForm();
          this.hideRoomFormModal();
        });
    },
    async editRoom(roomShortCode) {
      let getLocationsResponse = await this.$api.call(
        ApiRoom.getRoom(roomShortCode),
      );
      if (getLocationsResponse.meta.status === "success") {
        this.editedRoom = getLocationsResponse.data;
      } else {
        this.$fhcAlert.alertError(this.$p.t("ui", "errorLoadingRoomData"));
        return;
      }

      this.isEditInProgress = true;

      let orgUnitData = null;
      let orgUnit = this.organizationalUnits.find(
        (unit) => unit.oe_kurzbz === this.editedRoom.oe_kurzbz,
      );
      if (orgUnit) {
        orgUnitData = {
          label: `${orgUnit.bezeichnung} (${orgUnit.organisationseinheittyp_kurzbz})`,
          value: orgUnit.oe_kurzbz,
        };
      }

      let potentialParentRooms = await this.fetchRooms(this.editedRoom.parent_ort_kurzbz);

      let parentRoomData = null;
      let parentRoom = potentialParentRooms.find(
        (room) => room.ort_kurzbz === this.editedRoom.parent_ort_kurzbz,
      );
      if (parentRoom) {
        this.rooms.push(parentRoom);
        parentRoomData = {
          label: `${parentRoom.ort_kurzbz} - ${parentRoom.bezeichnung}`,
          value: parentRoom.ort_kurzbz,
        };
      }

      this.roomFormData = {
        parentRoom: parentRoomData,
        locationId: this.editedRoom.standort_id,
        organizationalUnit: orgUnitData,
        contentId: this.editedRoom.content_id,
        kurzbezeichnung: this.editedRoom.ort_kurzbz,
        bezeichnung: this.editedRoom.bezeichnung,
        planbezeichnung: this.editedRoom.planbezeichnung,
        aktiv: this.editedRoom.aktiv,
        lehre: this.editedRoom.lehre,
        reservieren: this.editedRoom.reservieren,
        maxPerson: this.editedRoom.max_person,
        stockwerk: this.editedRoom.stockwerk,
        lageplan: this.editedRoom.lageplan,
        dislozierung: this.editedRoom.dislozierung,
        kosten: this.editedRoom.kosten,
        ausstattung: this.editedRoom.ausstattung,
        telefonklappe: this.editedRoom.telefonklappe,
        quadratmeter: this.editedRoom.m2,
        gebaudeteil: this.editedRoom.gebteil,
        arbeitsplatze: this.editedRoom.arbeitsplaetze,
      };

      this.$refs.roomFormModal.show();
    },
    updateRoom() {
      return this.$refs.roomForm
        .call(
          ApiRoom.updateRoom(
            this.editedRoom.ort_kurzbz,
            this.getApiCallParsedRoomFormData(),
          ),
        )
        .then((response) => {
          this.$fhcAlert.alertSuccess(this.$p.t("ui", "successSave"));
          this.$emit("roomUpdated");
          this.resetRoomForm();
          this.hideRoomFormModal();
        });
    },
    getApiCallParsedRoomFormData() {
      return {
        parent_ort_kurzbz: this.roomFormData.parentRoom?.value,
        standort_id: this.roomFormData.locationId,
        oe_kurzbz: this.roomFormData.organizationalUnit?.value,
        content_id: this.roomFormData.contentId,
        ort_kurzbz: this.roomFormData.kurzbezeichnung,
        bezeichnung: this.roomFormData.bezeichnung,
        planbezeichnung: this.roomFormData.planbezeichnung,
        aktiv: this.roomFormData.aktiv,
        lehre: this.roomFormData.lehre,
        reservieren: this.roomFormData.reservieren,
        max_person: this.roomFormData.maxPerson,
        stockwerk: this.roomFormData.stockwerk,
        lageplan: this.roomFormData.lageplan,
        dislozierung: this.roomFormData.dislozierung,
        kosten: this.roomFormData.kosten,
        ausstattung: this.roomFormData.ausstattung,
        telefonklappe: this.roomFormData.telefonklappe,
        m2: this.roomFormData.quadratmeter,
        gebteil: this.roomFormData.gebaudeteil,
        arbeitsplatze: this.roomFormData.arbeitsplatze,
      };
    },
    hideRoomFormModal() {
      this.$refs.roomFormModal.hide();
      this.$emit("hideBsModal");
      this.resetRoomForm();
    },
    resetRoomForm() {
      this.$refs.roomForm.clearValidation();
      this.isEditInProgress = false;
      this.editedRoom = null;
      this.roomFormData = {
        aktiv: true,
      };
    },
    async fetchRooms(searchedRoomShortCode) {
      let getRoomsResponse = await this.$api.call(ApiRoom.getAllRooms({
        shortCode: searchedRoomShortCode,
        pagination: {
          page: 1,
          size: 100,
        },
      }));
      if (getRoomsResponse.meta.status === "success") {
        return getRoomsResponse.data;
      } else {
        this.$fhcAlert.alertError(this.$p.t("ui", "errorLoadingRooms"));
      }

      return [];
    },
  },
  async created() {
    let getLocationsResponse = await this.$api.call(
      ApiLocation.getLocationsByCompanyType("Intern"),
    );
    if (getLocationsResponse.meta.status === "success") {
      this.locations = getLocationsResponse.data;
      this.locations.unshift({
        standort_id: null,
        kurzbz: "----------",
      });
    } else {
      this.$fhcAlert.alertError(this.$p.t("ui", "errorLoadingLocations"));
    }

    let getAllOrganizationalUnitsResponse = await this.$api.call(
      ApiOrganizationalUnit.getAllOrganizationalUnits(),
    );
    if (getAllOrganizationalUnitsResponse.meta.status === "success") {
      this.organizationalUnits = getAllOrganizationalUnitsResponse.data.sort(
        (a, b) => a.bezeichnung.localeCompare(b.bezeichnung),
      );
    } else {
      this.$fhcAlert.alertError(
        this.$p.t("ui", "errorLoadingOrganizationalUnits"),
      );
    }

    this.rooms = await this.fetchRooms();
  },
  template: /* html */ `
  <bs-modal ref="roomFormModal" size="sm" @hideBsModal="() => { $emit('hideBsModal'); resetRoomForm(); }" class="modal-lg">
			<template #title>
				<p v-if="!editedRoom" class="fw-bold mt-3">{{$capitalize($p.t('ui', 'createRoomModalTitle'))}}</p>
				<p v-else class="fw-bold mt-3">{{$capitalize($p.t('ui', 'editRoomModalTitle'))}}</p>
			</template>
      <template #default>
        <core-form ref="roomForm" class="row g-3 pb-3">
          <div class="row mb-3">
            <form-input
              v-model="roomFormData.parentRoom"
              :label="$capitalize($p.t('ui/parentRoom'))"
              :suggestions="filteredRooms"
              :optionValue="(option) => option.value"
              :optionLabel="(option) => option.label"
              :delay="500"
              @complete="filterRooms"
              dropdown
              forceSelection
              type="autocomplete"
              name="parentRoomShortCode"
              >
            </form-input>
          </div>
          <div class="row mb-3">
            <div class="col">
              <form-input
                v-model="roomFormData.organizationalUnit"
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
            <div class="col">
              <form-input
                v-model="roomFormData.locationId"
                :label="$capitalize($p.t('global/ortLocation'))"
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
          </div>
          <div class="row mb-3">
            <div class="col">
              <form-input
                v-model="roomFormData.aktiv"
                :label="$capitalize($p.t('person', 'aktiv'))"
                type="checkbox"
                name="aktiv"  
                >
              </form-input>
            </div>
            <div class="col">
              <form-input
                  v-model="roomFormData.lehre"
                  :label="$capitalize($p.t('ui', 'lehre'))"
                  type="checkbox"
                  name="lehre"  
                >
              </form-input>
            </div>
            <div class="col">
              <form-input
                v-model="roomFormData.reservieren"
                :label="$capitalize($p.t('ui', 'reservieren'))"
                type="checkbox"
                name="reservieren"  
                >
              </form-input>
            </div>
          </div>
          <div v-if='!this.editedRoom' class="row mb-3">
            <form-input
              v-model="roomFormData.kurzbezeichnung"
              :label="$capitalize($p.t('gruppenmanagement', 'kurzbezeichnung'))"
              type="text"
              name="ort_kurzbz"  
              >
            </form-input>
          </div>
          <div class="row mb-3">
            <form-input
              v-model="roomFormData.bezeichnung"
              :label="$capitalize($p.t('ui', 'bezeichnung'))"
              type="text"
              name="bezeichnung"  
              >
            </form-input>
          </div>
          <div class="row mb-3">
            <form-input
              v-model="roomFormData.planbezeichnung"
              :label="$capitalize($p.t('ui', 'planbezeichnung'))"
              type="text"
              name="planbezeichnung"  
              >
            </form-input>
          </div>
          <div class="row mb-3">
            <div class="col">
              <form-input
                v-model="roomFormData.maxPerson"
                :label="$capitalize($p.t('ui', 'maxPersons'))"
                type="number"
                name="maxPerson"  
                >
              </form-input>
            </div>
            <div class="col">
              <form-input
                  v-model="roomFormData.stockwerk"
                  :label="$capitalize($p.t('ui', 'stockwerk'))"
                  type="number"
                  name="stockwerk"  
                  >
              </form-input>
            </div>
            <div class="col">
              <form-input
                v-model="roomFormData.quadratmeter"
                :label="$capitalize($p.t('ui', 'quadratmeter'))"
                type="number"
                name="quadratmeter"  
                >
              </form-input>
            </div>
          </div>
          <div class="row mb-3">
            <div class='col'>
              <form-input
                v-model="roomFormData.telefonklappe"
                :label="$capitalize($p.t('person', 'telefonklappe'))"
                type="number"
                name="telefonklappe"  
                >
              </form-input>
            </div>
            <div class='col'>
              <form-input
                v-model="roomFormData.arbeitsplatze"
                :label="$capitalize($p.t('ui', 'arbeitsplaetze'))"
                type="number"
                name="arbeitsplatze"  
                >
              </form-input>
            </div>
            <div class='col'>
              <form-input
                v-model="roomFormData.kosten"
                :label="$capitalize($p.t('ui', 'kosten'))"
                type="text"
                name="kosten"  
                >
              </form-input>
            </div>
          </div>
          <div class="row mb-3">
            <div class='col'>
              <form-input
                v-model="roomFormData.gebaudeteil"
                :label="$capitalize($p.t('ui', 'gebaudeteil'))"
                type="text"
                name="gebaudeteil"  
                >
              </form-input>
            </div>
            <div class='col'>
              <form-input
                v-model="roomFormData.contentId"
                :label="$capitalize($p.t('ui', 'contentId'))"
                type="number"
                name="contentId"  
                >
              </form-input>
            </div>
            <div class='col'>
              <form-input
                v-model="roomFormData.dislozierung"
                :label="$capitalize($p.t('ui', 'dislozierung'))"
                type="text"
                name="dislozierung"  
                >
              </form-input>
            </div>
          </div>
          <div class="row mb-3">
            <form-input
              v-model="roomFormData.lageplan"
              :label="$capitalize($p.t('ui', 'lageplan'))"
              type="textarea"
              name="lageplan"  
              >
            </form-input>
          </div>
          <div class="row mb-3">
            <form-input
              v-model="roomFormData.ausstattung"
              :label="$capitalize($p.t('ui', 'ausstattung'))"
              type="textarea"
              name="ausstattung"  
              >
            </form-input>
          </div>
          <div class="col d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-secondary" @click="hideRoomFormModal">{{$p.t('ui', 'abbrechen')}}</button>
            <button type="button" class="btn btn-primary" @click="isEditInProgress ? updateRoom() : createRoom()">{{$p.t('ui', 'speichern')}}</button>
          </div>
        </core-form>
      </template>
		</bs-modal>
  `,
};
