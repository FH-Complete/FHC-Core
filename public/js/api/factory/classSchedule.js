/**
 * Copyright (C) 2025 fhcomplete.org
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

export default {
  getAllClassTimeValidityPeriods() {
    return {
      method: "get",
      url: "/api/frontend/v1/ClassScheduleApi/getAllClassTimeValidityPeriods",
    };
  },
  getClassTimeValidityPeriod(classTimeSlotValidityPeriodId) {
    return {
      method: "get",
      url: `/api/frontend/v1/ClassScheduleApi/getClassTimeValidityPeriod/${classTimeSlotValidityPeriodId}`,
    };
  },
  createClassTimeSlotValidityPeriod(userId, params) {
    return {
      method: "post",
      url: "api/frontend/v1/ClassScheduleApi/createClassTimeSlotValidityPeriod/",
      params,
    };
  },
  deleteClassTimeSlotValidityPeriod(userId, classTimeSlotValidityPeriodId) {
    return {
      method: "post",
      url: `api/frontend/v1/ClassScheduleApi/deleteClassTimeSlotValidityPeriod/${classTimeSlotValidityPeriodId}`,
    };
  },
  getClassTimeSlotsForValidityPeriod(classTimeSlotValidityPeriodId) {
    return {
      method: "get",
      url: `api/frontend/v1/ClassScheduleApi/getClassTimeSlotsForValidityPeriod/${classTimeSlotValidityPeriodId}`,
    };
  },
  createClassTimeSlotsForValidityPeriod(
    userId,
    classTimeSlotValidityPeriodId,
    params,
  ) {
    return {
      method: "post",
      url: `api/frontend/v1/ClassScheduleApi/createClassTimeSlotsForValidityPeriod/${classTimeSlotValidityPeriodId}`,
      params,
    };
  },
  editClassTimeSlotsForValidityPeriod(
    userId,
    classTimeSlotValidityPeriodId,
    params,
  ) {
    return {
      method: "post",
      url: `api/frontend/v1/ClassScheduleApi/editClassTimeSlotsForValidityPeriod/${classTimeSlotValidityPeriodId}`,
      params,
    };
  },
  updateClassTimeSlotValidityPeriod(
    userId,
    classTimeSlotValidityPeriodId,
    params,
  ) {
    return {
      method: "post",
      url: `api/frontend/v1/ClassScheduleApi/updateClassTimeSlotValidityPeriod/${classTimeSlotValidityPeriodId}`,
      params,
    };
  },
  deleteClassTimeSlotsForValidityPeriodPerGroup(
    userId,
    classTimeSlotValidityPeriodId,
    groupIdentifikator,
  ) {
    return {
      method: "post",
      url: `api/frontend/v1/ClassScheduleApi/deleteClassTimeSlotsForValidityPeriodPerGroup/${classTimeSlotValidityPeriodId}/${groupIdentifikator}`,
    };
  },
  getAllClassScheduleTypes(queryParams) {
    return {
      method: "get",
      url:
        "/api/frontend/v1/ClassScheduleApi/getAllClassScheduleTypes?" +
        new URLSearchParams(queryParams).toString(),
    };
  },
  createClassTimeSlotType(params) {
    return {
      method: "post",
      url: "api/frontend/v1/ClassScheduleApi/createClassTimeSlotType/",
      params,
    };
  },
  updateClassTimeSlotType(classTimeSlotTypeId, params) {
    return {
      method: "post",
      url: `api/frontend/v1/ClassScheduleApi/updateClassTimeSlotType/${classTimeSlotTypeId}`,
      params,
    };
  },
  deleteClassTimeSlotType(userId, classTimeSlotTypeId) {
    return {
      method: "post",
      url: `api/frontend/v1/ClassScheduleApi/deleteClassTimeSlotType/${classTimeSlotTypeId}`,
    };
  },
};
