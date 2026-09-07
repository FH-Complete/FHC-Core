/**
 * Copyright (C) 2024 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
import BsModal from "../Bootstrap/Modal.js";

export default {
  name: "HistoryModal",
  components: {
    BsModal,
  },
  props: {
    entries: {
      type: Array,
      default: () => [],
    },
  },
  methods: {
    show() {
      this.$refs.modal.show();
    },
    hide() {
      this.$refs.modal.hide();
    },
  },
  template: `
    <bs-modal
      ref="modal"
      class="bootstrap-prompt"
      dialog-class="modal-lg"
      data-cy="historyModal"
    >
      <template #title>History</template>
      <template #default>
        <table v-if="entries.length" class="table table-bordered table-hover">
          <thead class="table-light">
            <tr>
              <th>Von</th>
              <th>Bis</th>
              <th>Status</th>
              <th>Ort</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="entry in entries" :key="entry.id">
              <td>{{ entry.von }}</td>
              <td>{{ entry.bis }}</td>
              <td>{{ entry.status_kurzbz }}</td>
              <td>{{ entry.ort }}</td>
            </tr>
          </tbody>
        </table>
      </template>
    </bs-modal>
  `,
};
