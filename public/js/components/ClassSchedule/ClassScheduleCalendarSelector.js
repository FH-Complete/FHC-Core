import draggable from "../../directives/draggable.js";
import drop from "../../directives/drop.js";

export default {
  name: "ClassScheduleCalendarSelector",
  directives: {
    draggable,
    drop,
  },
  props: {
    isPreviewMode: {
      type: Boolean,
      required: false,
      default: false,
    },
    classTimeSlotTypes: {
      type: [Array, null],
      required: true,
    },
    editedOverlays: {
      type: [Array, null],
      required: false,
      default: () => [],
    },
  },
  emits: ["overlaysChanged"],
  watch: {
    editedOverlays: {
      async handler(newVal) {
        await this.$nextTick();

        this.overlays = [];
        await this.$nextTick();
        this.$refs.calendarContainer
          .querySelectorAll("div[id^='overlay-']")
          .forEach((element) => {
            element.remove();
          });
        newVal
          .map((slot) => {
            return {
              ...slot,
              startingTimeSlot: this.timeSlotsInDay.find((timeSlot) =>
                timeSlot.startsWith(slot.startTime.substr(0, 5)),
              ),
              endingTimeSlot: this.timeSlotsInDay.find((timeSlot) =>
                timeSlot.endsWith(slot.endTime.substr(0, 5)),
              ),
            };
          })
          .forEach((overlay) => {
            let firstElementDataNumber =
              (overlay.weekday - 1) * this.timeSlotsInDay.length +
              this.timeSlotsInDay.indexOf(overlay.startingTimeSlot);
            let lastElementDataNumber =
              (overlay.weekday - 1) * this.timeSlotsInDay.length +
              this.timeSlotsInDay.indexOf(overlay.endingTimeSlot);

            let firstSelectedElement =
              this.$refs.calendarSelectorContainer.querySelector(
                "div[data-number='" + firstElementDataNumber + "']",
              );
            let lastSelectedElement =
              this.$refs.calendarSelectorContainer.querySelector(
                "div[data-number='" + lastElementDataNumber + "']",
              );
            if (!firstSelectedElement || !lastSelectedElement) {
              this.$fhcAlert.alertError(
                this.$p.t("ui", "classTimeSlotLoadingErrorMessage"),
              );
              return;
            }

            firstSelectedElement.style.backgroundColor =
              this.selectedTimeSlotLabelColor;
            lastSelectedElement.style.backgroundColor =
              this.selectedTimeSlotLabelColor;

            this.currentFirstSelectedElementNumber = firstSelectedElement
              ? parseInt(firstSelectedElement.getAttribute("data-number"))
              : null;

            this.currentLastSelectedElementNumber = lastSelectedElement
              ? parseInt(lastSelectedElement.getAttribute("data-number"))
              : null;

            this.createOverlay();

            this.$refs.calendarSelectorContainer
              .querySelectorAll("div[class*='part-body']")
              .forEach((child) => {
                child.style.backgroundColor = this.defaultTimeSlotLabelColor;
              });

            this.currentFirstSelectedElementNumber = null;
            this.currentLastSelectedElementNumber = null;
          });

        this.overlays = this.overlays.map((existingOverlay, index) => {
          let existingOverlayInNewVal = newVal[index];

          existingOverlay.databaseId = existingOverlayInNewVal.databaseId;
          existingOverlay.type = existingOverlayInNewVal.type;
          existingOverlay.hexColor =
            this.classTimeSlotTypes.find(
              (type) =>
                type.unterrichtszeitentyp_kurzbz ===
                existingOverlayInNewVal.type,
            )?.hintergrundfarbe || null;
          return {
            ...existingOverlay,
          };
        });
      },
      deep: true,
      immediate: true,
    },
    classTimeSlotTypes() {
      this.overlays = this.overlays.map((overlay) => {
        let type = this.classTimeSlotTypes.find(
          (type) => type.unterrichtszeitentyp_kurzbz === overlay.type
        );
        if (type) {
          overlay.hexColor = type.hintergrundfarbe;
        }
        return overlay;
      });
    },
    overlays: {
      handler(newVal) {
        this.$emit("overlaysChanged", newVal);
      },
      deep: true,
    },
  },
  data() {
    return {
      daysInWeek: [
        this.$p.t("ui", "monday"),
        this.$p.t("ui", "tuesday"),
        this.$p.t("ui", "wednesday"),
        this.$p.t("ui", "thursday"),
        this.$p.t("ui", "friday"),
        this.$p.t("ui", "saturday"),
        this.$p.t("ui", "sunday"),
      ],
      timeSlotsInDay: [
        "08:00-08:45",
        "08:45-09:30",
        "09:40-10:25",
        "10:25-11:10",
        "11:20-12:05",
        "12:05-12:50",
        "12:50-13:35",
        "13:35-14:20",
        "14:30-15:15",
        "15:15-16:00",
        "16:10-16:55",
        "16:55-17:40",
        "17:50-18:35",
        "18:35-19:20",
        "19:30-20:15",
        "20:15-21:00",
      ],
      isTimeElementCreationInProgress: false,
      overlays: [],
      defaultTimeSlotLabelColor: "var(--fhc-calendar-bg-body)",
      selectedTimeSlotLabelColor: "rgb(236, 235, 189)",
      defaultOverlayColor: "rgb(232, 226, 226)",
      overlayTextColor: "rgb(0, 0, 0)",
      currentFirstSelectedElementNumber: null,
      currentLastSelectedElementNumber: null,
      selected: [],
      isTimeElementResizingInProgress: false,
      currentResizer: {
        id: null,
        overlayId: null,
      },
      oldMousePosition: {
        x: null,
        y: null,
      },
      currentlyEditedOverlayId: null,
      visiblePopover: null,
    };
  },
  computed: {
    selectedDragObject() {
      return this.selected.map((item) => ({
        name: "test",
        type: "calendar_selector_overlay",
        id: 2,
      }));
    },
    currentlySelectedTimeSlotSpan() {
      if (
        !this.currentFirstSelectedElementNumber ||
        !this.currentLastSelectedElementNumber
      )
        return null;

      const firstTimeSlot =
        this.timeSlotsInDay[
          this.currentFirstSelectedElementNumber % this.timeSlotsInDay.length
        ];
      const lastTimeSlot =
        this.timeSlotsInDay[
          this.currentLastSelectedElementNumber % this.timeSlotsInDay.length
        ];

      let firstTimeSlotFragment = firstTimeSlot.split("-")[0];
      let lastTimeSlotFragment = lastTimeSlot.split("-")[1];

      return firstTimeSlotFragment + "-" + lastTimeSlotFragment;
    },
    userLanguage() {
      return Vue.ref(FHC_JS_DATA_STORAGE_OBJECT.user_language);
    }
  },
  methods: {
    createOverlay() {
      this.hideOverlayClassTimeTypePopover();

      let overlayElement;

      overlayElement = this.$refs.calendarSelectorContainer.querySelector(
        "#overlays-container",
      ).children[0];

      let firstSelectedChild =
        this.$refs.calendarSelectorContainer.querySelector(
          `div[style*='background-color: ${this.selectedTimeSlotLabelColor};']`,
        );

      let lastSelectedChild =
        this.$refs.calendarSelectorContainer.querySelectorAll(
          `div[style*='background-color: ${this.selectedTimeSlotLabelColor};']`,
        );
      lastSelectedChild = lastSelectedChild[lastSelectedChild.length - 1];

      if (!firstSelectedChild || !lastSelectedChild) {
        return;
      }

      let firstSelectedElementNumber = parseInt(
        firstSelectedChild.getAttribute("data-number"),
      );
      let lastSelectedElementNumber = parseInt(
        lastSelectedChild.getAttribute("data-number"),
      );
      if (
        firstSelectedElementNumber === null ||
        lastSelectedElementNumber === null
      ) {
        console.error("Selected elements do not have data-number attribute");
        return;
      }

      this.currentFirstSelectedElementNumber = firstSelectedElementNumber;
      this.currentLastSelectedElementNumber = lastSelectedElementNumber;

      let gridLineNumber;
      try {
        gridLineNumber = this.getLineNumberFromSelectedElementsNumbers(
          firstSelectedElementNumber,
          lastSelectedElementNumber,
        );
      } catch (error) {
        this.$fhcAlert.alertError(
          this.$p.t("ui", "classTimeSlotMultipleWeeksSelectedErrorMessage"),
        );

        return;
      }

      let gridLine = this.$refs.calendarSelectorContainer.querySelectorAll(
        ".fhc-calendar-base-grid-line",
      )[gridLineNumber];
      if (!gridLine) return;

      overlayElement.classList.remove("d-none");
      overlayElement.style.display = "flex";
      overlayElement.style.position = "absolute";
      overlayElement.style.top = firstSelectedChild.offsetTop + "px";
      overlayElement.style.left = "0px";
      overlayElement.style.width = "100%";
      overlayElement.style.height =
        lastSelectedChild.offsetTop +
        lastSelectedChild.offsetHeight -
        firstSelectedChild.offsetTop +
        "px";
      overlayElement.style.backgroundColor = this.defaultOverlayColor;

      if (
        this.currentFirstSelectedElementNumber >
        this.currentLastSelectedElementNumber
      ) {
        let temp = this.currentFirstSelectedElementNumber;
        this.currentFirstSelectedElementNumber =
          this.currentLastSelectedElementNumber;
        this.currentLastSelectedElementNumber = temp;
      }

      let overlayId = overlayElement.id;

      let hasCollidingOverlays = this.overlays.some((overlay) => {
        if (overlay.weekday !== gridLineNumber + 1) return false;
        if (overlay.id === overlayId) return false;
        if (
          (overlay.startingTimeSlotElementNumber <=
            this.currentFirstSelectedElementNumber &&
            overlay.endingTimeSlotElementNumber >=
              this.currentFirstSelectedElementNumber) ||
          (overlay.startingTimeSlotElementNumber <=
            this.currentLastSelectedElementNumber &&
            overlay.endingTimeSlotElementNumber >=
              this.currentLastSelectedElementNumber)
        ) {
          return true;
        }
        return false;
      });

      if (hasCollidingOverlays) {
        this.$fhcAlert.alertError(
          this.$p.t("ui", "classTimeSlotOverlapErrorMessage"),
        );

        this.currentFirstSelectedElementNumber = null;
        this.currentLastSelectedElementNumber = null;
        return;
      }

      gridLine.appendChild(overlayElement);

      if (!this.overlays.some((overlay) => overlay.id === overlayId)) {
        this.overlays.push({
          id: overlayId,
          startingTimeSlotElementNumber: this.currentFirstSelectedElementNumber,
          endingTimeSlotElementNumber: this.currentLastSelectedElementNumber,
          startingTimeSlot:
            this.timeSlotsInDay[
              this.currentFirstSelectedElementNumber %
                this.timeSlotsInDay.length
            ],
          endingTimeSlot:
            this.timeSlotsInDay[
              this.currentLastSelectedElementNumber % this.timeSlotsInDay.length
            ],
          type: null,
          hexColor: null,
          weekday: gridLineNumber + 1,
        });
      }
    },
    deleteOverlay(overlayId) {
      let confirm = window.confirm(
        this.$p.t("ui", "classTimeSlotDeletionConfirmationMessage"),
      );
      if (!confirm) return;

      this.overlays = this.overlays.filter(
        (overlay) => overlay.id !== overlayId,
      );

      this.$refs.calendarSelectorContainer
        .querySelector(`#${overlayId}`)
        .remove();

      this.hideOverlayClassTimeTypePopover();
    },
    getLineNumberFromSelectedElementNumber(selectedElementNumber) {
      let timeSlotsCount = this.timeSlotsInDay.length;

      let gridLineNumber = parseInt(selectedElementNumber / timeSlotsCount);

      return gridLineNumber;
    },
    getLineNumberFromSelectedElementsNumbers(
      firstSelectedElementNumber,
      lastSelectedElementNumber,
    ) {
      let timeSlotsCount = this.timeSlotsInDay.length;

      let firstNumberGridLine = parseInt(
        firstSelectedElementNumber / timeSlotsCount,
      );
      let lastNumberGridLine = parseInt(
        lastSelectedElementNumber / timeSlotsCount,
      );

      if (firstNumberGridLine !== lastNumberGridLine) {
        throw new Error("Selected elements are not in the same grid line");
      }

      return firstNumberGridLine;
    },
    getOverlayTimeSlotSpan(overlayId) {
      let overlay = this.overlays.find((overlay) => overlay.id === overlayId);
      if (!overlay) return null;

      let startingTimeSlotFragment = overlay.startingTimeSlot.split("-")[0];
      let endingTimeSlotFragment = overlay.endingTimeSlot.split("-")[1];

      return startingTimeSlotFragment + " - " + endingTimeSlotFragment;
    },
    handleMouseDown(event) {
      if (this.$props.isPreviewMode) return;

      let isLeftMouseButton = event.buttons === 1;
      if (!isLeftMouseButton) return;

      this.isTimeElementCreationInProgress = true;

      let parent =
        this.$refs.calendarSelectorContainer.querySelector(".grid-body");
      if (!parent.contains(event.target)) {
        this.isTimeElementCreationInProgress = false;
        return;
      }

      let mouseY = event.clientY;
      let mouseX = event.clientX;

      const partBodies = this.$refs.calendarSelectorContainer.querySelectorAll(
        `div[class*='part-body']`,
      );

      let closestXPartBody = null;
      let closestXDistance = Infinity;
      let weekday = null;
      partBodies.forEach((partBody) => {
        const rect = partBody.getBoundingClientRect();
        const distanceX = Math.abs(mouseX - rect.left - rect.width / 2);

        let gridLineHeight = this.$refs.calendarSelectorContainer.querySelector(
          ".fhc-calendar-base-grid-line",
        ).style.height;
        let seeIfAdjacentElementIsGridLine =
          partBody.nextElementSibling.classList.contains(
            "fhc-calendar-base-grid-line",
          );
        if (
          distanceX < closestXDistance ||
          (distanceX + gridLineHeight === closestXDistance &&
            seeIfAdjacentElementIsGridLine)
        ) {
          closestXDistance = distanceX;
          closestXPartBody = partBody;
        }
      });

      if (closestXPartBody) {
        let number = parseInt(closestXPartBody.getAttribute("data-number"));
        weekday = parseInt(number / this.timeSlotsInDay.length) + 1;
      }

      if (!weekday) {
        this.$fhcAlert.alertError(
          this.$p.t("ui", "classTimeSlotSelectionErrorMessage"),
        );
        this.isTimeElementCreationInProgress = false;
        return;
      }

      let closestPartBody = null;
      let closestYDistance = Infinity;
      const newPartBodies =
        this.$refs.calendarSelectorContainer.querySelectorAll(
          `div[data-weekday='${weekday}']`,
        );
      newPartBodies.forEach((partBody) => {
        const rect = partBody.getBoundingClientRect();
        const distanceY = Math.abs(mouseY - rect.top - rect.height / 2);

        let gridLineHeight = this.$refs.calendarSelectorContainer.querySelector(
          ".fhc-calendar-base-grid-line",
        ).style.height;
        let seeIfAdjacentElementIsGridLine =
          partBody.nextElementSibling.classList.contains(
            "fhc-calendar-base-grid-line",
          );
        if (
          distanceY < closestYDistance ||
          (distanceY + gridLineHeight === closestYDistance &&
            seeIfAdjacentElementIsGridLine)
        ) {
          closestYDistance = distanceY;
          closestPartBody = partBody;
        }
      });

      if (closestPartBody) {
        let number = parseInt(closestPartBody.getAttribute("data-number"));
        this.currentFirstSelectedElementNumber = number;
        closestPartBody.style.backgroundColor = this.selectedTimeSlotLabelColor;
      }
    },
    handleMouseMove(event) {
      if (this.$props.isPreviewMode) return;
      if (!this.isTimeElementCreationInProgress) return;

      let weekday =
        parseInt(
          this.currentFirstSelectedElementNumber / this.timeSlotsInDay.length,
        ) + 1;
      let mouseY = event.clientY;

      const partBodies = this.$refs.calendarSelectorContainer.querySelectorAll(
        `div[data-weekday='${weekday}']`,
      );
      let closestPartBody = null;
      let closestDistance = Infinity;

      partBodies.forEach((partBody) => {
        const rect = partBody.getBoundingClientRect();
        const distance = Math.abs(mouseY - rect.top - rect.height / 2);

        let gridLineHeight = this.$refs.calendarSelectorContainer.querySelector(
          ".fhc-calendar-base-grid-line",
        ).style.height;
        let seeIfAdjacentElementIsGridLine =
          partBody.nextElementSibling.classList.contains(
            "fhc-calendar-base-grid-line",
          );
        if (
          distance < closestDistance ||
          (distance + gridLineHeight === closestDistance &&
            seeIfAdjacentElementIsGridLine)
        ) {
          closestDistance = distance;
          closestPartBody = partBody;
        }
      });

      if (closestPartBody) {
        this.$refs.calendarSelectorContainer
          .querySelectorAll(`div[data-weekday='${weekday}']`)
          .forEach((child) => {
            let itemNumber = parseInt(child.getAttribute("data-number"));

            if (
              itemNumber >= this.currentFirstSelectedElementNumber &&
              itemNumber <=
                parseInt(closestPartBody.getAttribute("data-number"))
            ) {
              child.style.backgroundColor = this.selectedTimeSlotLabelColor;
            } else if (
              itemNumber <= this.currentFirstSelectedElementNumber &&
              itemNumber >=
                parseInt(closestPartBody.getAttribute("data-number"))
            ) {
              child.style.backgroundColor = this.selectedTimeSlotLabelColor;
            } else {
              child.style.backgroundColor = this.defaultTimeSlotLabelColor;
            }
          });
      }
    },
    handleMouseUp(event) {
      if (this.$props.isPreviewMode) return;

      let isLeftMouseButton = event.buttons === 0;
      if (!isLeftMouseButton) return;

      if (!this.isTimeElementCreationInProgress) {
        this.$refs.calendarSelectorContainer
          .querySelectorAll("div[id^='overlay-']")
          .forEach((element) => {
            element.classList.remove("fhc-pointer-events-none");
            element.classList.add("fhc-pointer-events-all");
          });
        this.isTimeElementCreationInProgress = false;
        return;
      }

      if (this.isTimeElementResizingInProgress) {
        return;
      }

      this.isTimeElementCreationInProgress = false;

      let weekday =
        parseInt(
          this.currentFirstSelectedElementNumber / this.timeSlotsInDay.length,
        ) + 1;
      let mouseY = event.clientY;

      const partBodies = this.$refs.calendarSelectorContainer.querySelectorAll(
        `div[data-weekday='${weekday}']`,
      );
      let closestPartBody = null;
      let closestDistance = Infinity;

      partBodies.forEach((partBody) => {
        const rect = partBody.getBoundingClientRect();
        const distance = Math.abs(mouseY - rect.top - rect.height / 2);

        let gridLineHeight = this.$refs.calendarSelectorContainer.querySelector(
          ".fhc-calendar-base-grid-line",
        ).style.height;
        let seeIfAdjacentElementIsGridLine =
          partBody.nextElementSibling.classList.contains(
            "fhc-calendar-base-grid-line",
          );
        if (
          distance < closestDistance ||
          (distance + gridLineHeight === closestDistance &&
            seeIfAdjacentElementIsGridLine)
        ) {
          closestDistance = distance;
          closestPartBody = partBody;
        }
      });

      if (closestPartBody) {
        this.$refs.calendarSelectorContainer
          .querySelectorAll(`div[data-weekday='${weekday}']`)
          .forEach((child) => {
            let itemNumber = parseInt(child.getAttribute("data-number"));

            if (
              itemNumber >= this.currentFirstSelectedElementNumber &&
              itemNumber <=
                parseInt(closestPartBody.getAttribute("data-number"))
            ) {
              child.style.backgroundColor = this.selectedTimeSlotLabelColor;
            } else if (
              itemNumber <= this.currentFirstSelectedElementNumber &&
              itemNumber >=
                parseInt(closestPartBody.getAttribute("data-number"))
            ) {
              child.style.backgroundColor = this.selectedTimeSlotLabelColor;
            } else {
              child.style.backgroundColor = this.defaultTimeSlotLabelColor;
            }
          });
      }

      this.createOverlay();

      this.$refs.calendarSelectorContainer
        .querySelectorAll("div[class*='part-body']")
        .forEach((child) => {
          child.style.backgroundColor = this.defaultTimeSlotLabelColor;
        });

      this.$refs.calendarSelectorContainer
        .querySelectorAll("div[id^='overlay-']")
        .forEach((element) => {
          element.classList.remove("fhc-pointer-events-none");
          element.classList.add("fhc-pointer-events-all");
        });

      this.currentFirstSelectedElementNumber = null;
      this.currentLastSelectedElementNumber = null;
    },
    handleLeave(event) {
      if (this.$props.isPreviewMode) return;

      if (!this.isTimeElementCreationInProgress) return;
      const rect = event.target.getBoundingClientRect();

      let hasLeftFromTop = undefined;
      const fromTop = event.clientY <= rect.top + 15;

      if (fromTop) {
        hasLeftFromTop = true;
      } else {
        hasLeftFromTop = false;
      }

      const child = event.target;
      let number = parseInt(child.getAttribute("data-number"));

      if (number > this.currentFirstSelectedElementNumber) {
        if (hasLeftFromTop) {
          child.style.backgroundColor = this.defaultTimeSlotLabelColor;
        }
      } else if (number < this.currentFirstSelectedElementNumber) {
        if (!hasLeftFromTop) {
          child.style.backgroundColor = this.defaultTimeSlotLabelColor;
        }
      }
    },
    overlaySelectionChanged(event, overlayId) {
      if (this.$props.isPreviewMode) return;

      let isLeftMouseButton = event.buttons === 1;
      if (!isLeftMouseButton) return;

      this.$refs.calendarSelectorContainer
        .querySelector(`#${overlayId}`)
        .classList.remove("fhc-pointer-events-all");
      this.$refs.calendarSelectorContainer
        .querySelector(`#${overlayId}`)
        .classList.add("fhc-pointer-events-none");
      this.selected = [
        {
          type: "calendar_selector_overlay",
          id: overlayId,
        },
      ];
    },
    handleMouseUpOnOverlay(overlayId) {
      if (this.$props.isPreviewMode) return;

      this.$refs.calendarSelectorContainer
        .querySelector(`#${overlayId}`)
        .classList.remove("fhc-pointer-events-none");
      this.$refs.calendarSelectorContainer
        .querySelector(`#${overlayId}`)
        .classList.add("fhc-pointer-events-all");
      this.selected = [];
    },
    handleOverlayDrop(event) {
      this.hideOverlayClassTimeTypePopover();
      if (this.$props.isPreviewMode) return;

      let dropzoneItem = event.target;
      if (!dropzoneItem) return;

      if (!dropzoneItem.getAttribute("data-time")) {
        let dropzoneOverlay = this.overlays.find(
          (overlay) => overlay.id === dropzoneItem.id,
        );
        if (!dropzoneOverlay) {
          console.error(
            "Could not find overlay for dropzone item with id " +
              dropzoneItem.id,
          );
          return;
        }

        if (dropzoneOverlay.id !== this.selected[0].id) {
          this.$fhcAlert.alertError(
            this.$p.t("ui", "classTimeSlotOverlapErrorMessage"),
          );
          return;
        }

        let startElementNumber = dropzoneOverlay.startingTimeSlotElementNumber;
        let startElement = this.$refs.calendarSelectorContainer.querySelector(
          `[data-number='${startElementNumber}']`,
        );

        const mouseY = event.clientY;

        const dropzoneItemRect = dropzoneItem.getBoundingClientRect();
        const deltaY = mouseY - dropzoneItemRect.top;

        const startElementRect = startElement.getBoundingClientRect();
        const startElementTop = startElementRect.top;

        const newTop = startElementTop + deltaY;

        const partBodies =
          this.$refs.calendarSelectorContainer.querySelectorAll(
            "div[data-weekday='" + dropzoneOverlay.weekday + "']",
          );


        let closestPartBody = null;
        partBodies.forEach((partBody) => {
          const rect = partBody.getBoundingClientRect();
          if (newTop >= rect.top && newTop <= rect.bottom) {
            closestPartBody = partBody;
          }
        });
        if (!closestPartBody) return;

        dropzoneItem = closestPartBody;
      }

      let newStartTimeSlot =
        this.timeSlotsInDay[
          dropzoneItem.getAttribute("data-number") % this.timeSlotsInDay.length
        ];

      const draggedItemId =
        this.selected.length > 0 ? this.selected[0].id : null;
      if (!draggedItemId) return;

      const draggedItem = this.$refs.calendarSelectorContainer.querySelector(
        `#${draggedItemId}`,
      );
      if (!draggedItem) return;

      let overlay = this.overlays.find(
        (overlay) => overlay.id === draggedItemId,
      );
      if (!overlay) return;

      let gridLineNumber;
      try {
        gridLineNumber = this.getLineNumberFromSelectedElementNumber(
          dropzoneItem.getAttribute("data-number"),
        );
      } catch (error) {
        this.$fhcAlert.alertError(
          this.$p.t("ui", "classTimeSlotMultipleWeeksSelectedErrorMessage"),
        );

        return;
      }

      let gridLine = this.$refs.calendarSelectorContainer.querySelectorAll(
        ".fhc-calendar-base-grid-line",
      )[gridLineNumber];
      if (!gridLine) return;

      let overlayElement = this.$refs.calendarSelectorContainer.querySelector(
        `#${draggedItemId}`,
      );
      gridLine.appendChild(overlayElement);

      draggedItem.style.top = dropzoneItem.offsetTop + "px";
      let draggedItemRectBottom = draggedItem.getBoundingClientRect().bottom;

      let weekday = gridLineNumber + 1;

      const partBodies = this.$refs.calendarSelectorContainer.querySelectorAll(
        "div[data-weekday='" + weekday + "']",
      );
      let closestPartBody = null;
      let closestDistance = Infinity;

      partBodies.forEach((partBody) => {
        const rect = partBody.getBoundingClientRect();
        const distance = Math.abs(
          draggedItemRectBottom - rect.top - rect.height / 2,
        );

        let gridLineHeight = this.$refs.calendarSelectorContainer.querySelector(
          ".fhc-calendar-base-grid-line",
        ).style.height;
        let seeIfAdjacentElementIsGridLine =
          partBody.nextElementSibling.classList.contains(
            "fhc-calendar-base-grid-line",
          );
        if (
          distance < closestDistance ||
          (distance + gridLineHeight === closestDistance &&
            seeIfAdjacentElementIsGridLine)
        ) {
          closestDistance = distance;
          closestPartBody = partBody;
        }
      });

      if (closestPartBody) {
        const closestPartBodyNumber = parseInt(
          closestPartBody.getAttribute("data-number"),
        );

        const rect = closestPartBody.getBoundingClientRect();
        const deltaY = rect.bottom - draggedItemRectBottom;

        draggedItem.style.height =
          parseInt(draggedItem.style.height) + deltaY + "px";

        this.overlays = this.overlays.map((overlay) => {
          if (overlay.id === draggedItemId) {
            const timeSlot =
              this.timeSlotsInDay[
                closestPartBodyNumber % this.timeSlotsInDay.length
              ];
            overlay.endingTimeSlotElementNumber = closestPartBodyNumber;
            overlay.endingTimeSlot = timeSlot;

            overlay.weekday = gridLineNumber + 1;
            return overlay;
          }
          return overlay;
        });
      }

      this.overlays = this.overlays.map((overlay) => {
        if (overlay.id === draggedItemId) {
          overlay.startingTimeSlotElementNumber = parseInt(
            dropzoneItem.getAttribute("data-number"),
          );
          overlay.startingTimeSlot = newStartTimeSlot;
          return overlay;
        }
        return overlay;
      });

      let firstSkippedOverElement = this.overlays.find((innerOverlay) => {
        if (innerOverlay.id === overlay.id) return false;

        if (
          overlay.startingTimeSlotElementNumber <=
            innerOverlay.startingTimeSlotElementNumber &&
          overlay.endingTimeSlotElementNumber >=
            innerOverlay.startingTimeSlotElementNumber
        ) {
          return true;
        }
        return false;
      });

      if (firstSkippedOverElement) {
        this.$fhcAlert.alertError(
          this.$p.t("ui", "classTimeSlotOverlapErrorMessage"),
        );
        let elementBeforeFirstSkippedOverElement =
          this.$refs.calendarSelectorContainer.querySelector(
            `div[data-number='${firstSkippedOverElement.startingTimeSlotElementNumber - 1}']`,
          );
        if (!elementBeforeFirstSkippedOverElement) return;

        let elementBeforeFirstSkippedOverElementNumber = parseInt(
          elementBeforeFirstSkippedOverElement.getAttribute("data-number"),
        );

        const rect =
          elementBeforeFirstSkippedOverElement.getBoundingClientRect();
        const overlayElementRect = overlayElement.getBoundingClientRect();
        const deltaY = rect.bottom - overlayElementRect.bottom;

        overlayElement.style.height =
          parseInt(overlayElement.style.height) + deltaY + "px";

        this.overlays = this.overlays.map((innerOverlay) => {
          if (innerOverlay.id === overlay.id) {
            innerOverlay.endingTimeSlotElementNumber =
              elementBeforeFirstSkippedOverElementNumber;
            innerOverlay.endingTimeSlot =
              this.timeSlotsInDay[
                elementBeforeFirstSkippedOverElementNumber %
                  this.timeSlotsInDay.length
              ];
          }
          return innerOverlay;
        });

        this.oldMousePosition.x = null;
        this.oldMousePosition.y = null;

        this.overlays = this.overlays.map((overlay) => {
          if (overlay.id === draggedItemId) {
            const timeSlot =
              this.timeSlotsInDay[
                elementBeforeFirstSkippedOverElementNumber %
                  this.timeSlotsInDay.length
              ];
            overlay.endingTimeSlotElementNumber =
              elementBeforeFirstSkippedOverElementNumber;
            overlay.endingTimeSlot = timeSlot;

            overlay.weekday = gridLineNumber + 1;
            return overlay;
          }
          return overlay;
        });
      }

      draggedItem.classList.remove("fhc-pointer-events-none");
      draggedItem.classList.add("fhc-pointer-events-all");
    },
    handleMouseDownOnResizer(event, resizerId, overlayId) {
      if (this.$props.isPreviewMode) return;

      let isLeftMouseButton = event.buttons === 1;
      if (!isLeftMouseButton) return;

      this.isTimeElementResizingInProgress = true;

      this.currentResizer = {
        id: resizerId,
        overlayId: overlayId,
      };

      this.$refs.calendarSelectorContainer
        .querySelector(`#${overlayId}`)
        .setAttribute("draggable", "false");
    },
    handleMouseMoveOnResizerColumn(event) {
      if (this.$props.isPreviewMode) return;

      if (!this.isTimeElementResizingInProgress) {
        this.$refs.calendarSelectorContainer
          .querySelector(".fhc-calendar-base-grid-line")
          .classList.add("fhc-pointer-events-none");
        return;
      }

      let overlayElement1 = this.$refs.calendarSelectorContainer.querySelector(
        `#${this.currentResizer.overlayId}`,
      );
      let overlayTop = parseInt(overlayElement1.getBoundingClientRect().top);
      let mouseY = event.clientY;

      if (mouseY < overlayTop + 5) {
        this.isTimeElementResizingInProgress = false;

        this.oldMousePosition.x = null;
        this.oldMousePosition.y = null;
        this.$fhcAlert.alertError(
          this.$p.t("ui", "classTimeSlotMinimumSizeErrorMessage"),
        );
        this.handleMouseUpOnResizerColumn(event);

        let currentStartingTimeSlotElement =
          this.$refs.calendarSelectorContainer.querySelector(
            `div[data-number='${
              this.overlays.find(
                (overlay) => overlay.id === this.currentResizer.overlayId,
              ).startingTimeSlotElementNumber
            }']`,
          );
        if (!currentStartingTimeSlotElement) return;

        const rect = currentStartingTimeSlotElement.getBoundingClientRect();
        const resizerRect = overlayElement1.getBoundingClientRect();
        const deltaY = rect.bottom - resizerRect.bottom;

        overlayElement1.style.height =
          parseInt(overlayElement1.style.height) + deltaY + "px";

        this.overlays = this.overlays.map((overlay) => {
          if (overlay.id === this.currentResizer.overlayId) {
            overlay.endingTimeSlotElementNumber = parseInt(
              currentStartingTimeSlotElement.getAttribute("data-number"),
            );
            overlay.endingTimeSlot =
              this.timeSlotsInDay[
                parseInt(
                  currentStartingTimeSlotElement.getAttribute("data-number"),
                ) % this.timeSlotsInDay.length
              ];
            return overlay;
          }
          return overlay;
        });

        this.currentResizer = {
          id: null,
          overlayId: null,
        };
        return;
      }

      this.$refs.calendarSelectorContainer
        .querySelector(".fhc-calendar-base-grid-line")
        .classList.remove("fhc-pointer-events-none");

      if (!this.oldMousePosition.x || !this.oldMousePosition.y) {
        this.oldMousePosition.x = event.clientX;
        this.oldMousePosition.y = event.clientY;
        return;
      }

      const resizerElement = this.$refs.calendarSelectorContainer.querySelector(
        `#${this.currentResizer.id}`,
      );
      const overlayElement = this.$refs.calendarSelectorContainer.querySelector(
        `#${this.currentResizer.overlayId}`,
      );

      if (!resizerElement || !overlayElement) return;

      const newMousePosition = {
        x: event.clientX,
        y: event.clientY,
      };

      const deltaY = newMousePosition.y - this.oldMousePosition.y;
      if (deltaY > 0) {
        resizerElement.style.bottom =
          parseInt(resizerElement.style.bottom || 0) - deltaY + "px";
        overlayElement.style.height =
          parseInt(overlayElement.style.height) + deltaY + "px";
      } else {
        resizerElement.style.bottom =
          parseInt(resizerElement.style.bottom || 0) - deltaY + "px";
        overlayElement.style.height =
          parseInt(overlayElement.style.height) + deltaY + "px";
      }

      this.oldMousePosition.x = newMousePosition.x;
      this.oldMousePosition.y = newMousePosition.y;
    },
    handleMouseUpOnResizerColumn(event) {
      if (this.$props.isPreviewMode) return;

      if (!this.isTimeElementResizingInProgress) {
        this.$refs.calendarSelectorContainer
          .querySelector(".fhc-calendar-base-grid-line")
          .classList.add("fhc-pointer-events-none");
        return;
      }

      let isLeftMouseButton = event.buttons === 0;
      if (!isLeftMouseButton) return;

      this.isTimeElementResizingInProgress = false;
      this.$refs.calendarSelectorContainer
        .querySelector(".fhc-calendar-base-grid-line")
        .classList.add("fhc-pointer-events-none");

      const resizerElement = this.$refs.calendarSelectorContainer.querySelector(
        `#${this.currentResizer.id}`,
      );
      const overlayElement = this.$refs.calendarSelectorContainer.querySelector(
        `#${this.currentResizer.overlayId}`,
      );

      if (!resizerElement || !overlayElement) {
        this.oldMousePosition.x = null;
        this.oldMousePosition.y = null;
        return;
      }

      let editedOverlay = this.overlays.find(
        (overlay) => overlay.id === this.currentResizer.overlayId,
      );

      const partBodies = this.$refs.calendarSelectorContainer.querySelectorAll(
        "div[data-weekday='" + editedOverlay.weekday + "']",
      );
      let closestPartBody = null;
      let closestDistance = Infinity;

      partBodies.forEach((partBody) => {
        const rect = partBody.getBoundingClientRect();
        const distance = Math.abs(event.clientY - rect.top - rect.height / 2);

        if (distance < closestDistance) {
          closestDistance = distance;
          closestPartBody = partBody;
        }
      });

      if (closestPartBody) {
        const closestPartBodyNumber = parseInt(
          closestPartBody.getAttribute("data-number"),
        );

        let firstSkippedOverElement = this.overlays.find((overlay) => {
          if (overlay.id === this.currentResizer.overlayId) return false;

          if (
            editedOverlay.startingTimeSlotElementNumber <=
              overlay.startingTimeSlotElementNumber &&
            closestPartBodyNumber >= overlay.startingTimeSlotElementNumber
          ) {
            return true;
          }
          return false;
        });

        if (firstSkippedOverElement) {
          this.$fhcAlert.alertError(
            this.$p.t("ui", "classTimeSlotOverlapErrorMessage"),
          );
          let elementBeforeFirstSkippedOverElement =
            this.$refs.calendarSelectorContainer.querySelector(
              `div[data-number='${firstSkippedOverElement.startingTimeSlotElementNumber - 1}']`,
            );
          if (!elementBeforeFirstSkippedOverElement) return;

          let elementBeforeFirstSkippedOverElementNumber = parseInt(
            elementBeforeFirstSkippedOverElement.getAttribute("data-number"),
          );

          const rect =
            elementBeforeFirstSkippedOverElement.getBoundingClientRect();
          const resizerRect = resizerElement.getBoundingClientRect();
          const deltaY = rect.bottom - resizerRect.bottom;

          overlayElement.style.height =
            parseInt(overlayElement.style.height) + deltaY + "px";

          this.overlays = this.overlays.map((overlay) => {
            if (overlay.id === this.currentResizer.overlayId) {
              overlay.endingTimeSlotElementNumber =
                elementBeforeFirstSkippedOverElementNumber;
              overlay.endingTimeSlot =
                this.timeSlotsInDay[
                  elementBeforeFirstSkippedOverElementNumber %
                    this.timeSlotsInDay.length
                ];
            }
            return overlay;
          });

          this.oldMousePosition.x = null;
          this.oldMousePosition.y = null;

          return;
        }
        const rect = closestPartBody.getBoundingClientRect();
        const resizerRect = resizerElement.getBoundingClientRect();
        const deltaY = rect.bottom - resizerRect.bottom;

        overlayElement.style.height =
          parseInt(overlayElement.style.height) + deltaY + "px";

        this.overlays = this.overlays.map((overlay) => {
          if (overlay.id === this.currentResizer.overlayId) {
            const closestPartBodyTimeSlot =
              this.timeSlotsInDay[
                closestPartBodyNumber % this.timeSlotsInDay.length
              ];

            overlay.endingTimeSlotElementNumber = closestPartBodyNumber;
            overlay.endingTimeSlot = closestPartBodyTimeSlot;
            return overlay;
          }
          return overlay;
        });
      }

      this.oldMousePosition.x = null;
      this.oldMousePosition.y = null;

      this.$refs.calendarSelectorContainer
        .querySelector(`#${this.currentResizer.overlayId}`)
        .setAttribute("draggable", "true");

      this.currentResizer = {
        id: null,
        overlayId: null,
      };
    },
    handleMouseOverOnOverlay(overlayId) {
      if (this.$props.isPreviewMode) return;

      if (!this.isTimeElementCreationInProgress) return;

      let hitOverlay = this.overlays.find(
        (overlay) => overlay.id === overlayId,
      );

      let startingTimeSlotElementNumber =
        this.currentFirstSelectedElementNumber;
      let hitOverlayStartingTimeSlotElementNumber =
        hitOverlay.startingTimeSlotElementNumber;

      if (
        startingTimeSlotElementNumber < hitOverlayStartingTimeSlotElementNumber
      ) {
        this.currentLastSelectedElementNumber =
          hitOverlayStartingTimeSlotElementNumber - 1;
      } else {
        this.currentFirstSelectedElementNumber =
          hitOverlayStartingTimeSlotElementNumber + 1;
      }
      this.createOverlay();
      this.isTimeElementCreationInProgress = false;
      this.$refs.calendarSelectorContainer
        .querySelectorAll("div[class*='part-body']")
        .forEach((child) => {
          child.style.backgroundColor = this.defaultTimeSlotLabelColor;
        });

      this.currentFirstSelectedElementNumber = null;
      this.currentLastSelectedElementNumber = null;
    },
    handleMouseLeaveOnCalendar(event) {
      if (this.$props.isPreviewMode) return;

      if (this.isTimeElementCreationInProgress) {
        this.isTimeElementCreationInProgress = false;
        this.$refs.calendarSelectorContainer
          .querySelectorAll("div[class*='part-body']")
          .forEach((child) => {
            child.style.backgroundColor = this.defaultTimeSlotLabelColor;
          });
        this.currentFirstSelectedElementNumber = null;
        this.currentLastSelectedElementNumber = null;
      }

      if (this.isTimeElementResizingInProgress) {
        let overlayElement1 =
          this.$refs.calendarSelectorContainer.querySelector(
            `#${this.currentResizer.overlayId}`,
          );

        this.isTimeElementResizingInProgress = false;
        this.$refs.calendarSelectorContainer
          .querySelector(".fhc-calendar-base-grid-line")
          .classList.add("fhc-pointer-events-none");

        this.isTimeElementResizingInProgress = false;

        this.oldMousePosition.x = null;
        this.oldMousePosition.y = null;
        this.$fhcAlert.alertError(
          this.$p.t("ui", "classTimeSlotResizeOutOfScopeErrorMessage"),
          1000,
        );
        this.handleMouseUpOnResizerColumn(event);

        let previousEndingTimeSlotElement =
          this.$refs.calendarSelectorContainer.querySelector(
            `div[data-number='${
              this.overlays.find(
                (overlay) => overlay.id === this.currentResizer.overlayId,
              ).endingTimeSlotElementNumber
            }']`,
          );
        if (!previousEndingTimeSlotElement) return;

        const rect = previousEndingTimeSlotElement.getBoundingClientRect();
        const resizerRect = overlayElement1.getBoundingClientRect();
        const deltaY = rect.bottom - resizerRect.bottom;

        overlayElement1.style.height =
          parseInt(overlayElement1.style.height) + deltaY + "px";

        this.overlays = this.overlays.map((overlay) => {
          if (overlay.id === this.currentResizer.overlayId) {
            overlay.endingTimeSlotElementNumber = parseInt(
              previousEndingTimeSlotElement.getAttribute("data-number"),
            );
            overlay.endingTimeSlot =
              this.timeSlotsInDay[
                parseInt(
                  previousEndingTimeSlotElement.getAttribute("data-number"),
                ) % this.timeSlotsInDay.length
              ];
            return overlay;
          }
          return overlay;
        });

        this.$refs.calendarSelectorContainer
          .querySelector(".fhc-calendar-base-grid-line")
          .classList.remove("fhc-pointer-events-none");

        this.$refs.calendarSelectorContainer
          .querySelector(`#${this.currentResizer.overlayId}`)
          .setAttribute("draggable", "true");

        this.currentResizer = {
          id: null,
          overlayId: null,
        };

        return;
      }

      this.$refs.calendarSelectorContainer
        .querySelectorAll("div[id^='overlay-']")
        .forEach((element) => {
          element.classList.remove("fhc-pointer-events-none");
          element.classList.add("fhc-pointer-events-all");
        });
    },
    isOverlayMinimallySized(overlay) {
      if (!overlay) return false;
      if (
        !overlay.startingTimeSlotElementNumber ||
        !overlay.endingTimeSlotElementNumber
      ) {
        return false;
      }

      return (
        overlay.startingTimeSlotElementNumber ===
        overlay.endingTimeSlotElementNumber
      );
    },
    getOverlayClassScheduleTypeTitle(overlayId) {
      let overlay = this.overlays.find((overlay) => overlay.id === overlayId);
      if (!overlay) return "";

      let typeDescriptions =
        this.classTimeSlotTypes.find(
          (type) => type.unterrichtszeitentyp_kurzbz === overlay.type,
        )?.bezeichnung_mehrsprachig || "";
      if (!typeDescriptions) return "";

      return typeDescriptions[0].value || "";
    },
    handleChangeClassTimeSlotTypeForOverlay(newType) {
      let classTimeSlotType = this.classTimeSlotTypes.find(
        (type) => type.unterrichtszeitentyp_kurzbz === newType,
      );
      if (!classTimeSlotType) {
        console.error(
          "Could not find class time slot type for newType: " + newType,
        );
        return;
      }

      this.overlays = this.overlays.map((overlay) => {
        if (overlay.id === this.currentlyEditedOverlayId) {
          return {
            ...overlay,
            type: classTimeSlotType.unterrichtszeitentyp_kurzbz,
            hexColor: classTimeSlotType.hintergrundfarbe,
          };
        }
        return overlay;
      });

      this.currentlyEditedOverlayId = null;
    },
    showOverlayClassTimeTypePopover(overlayId) {
      let overlayElement = this.$refs.calendarSelectorContainer.querySelector(
        `#${overlayId}`,
      );
      if (!overlayElement) return;

      if (this.visiblePopover) {
        this.visiblePopover.dispose();
        this.visiblePopover = null;
        return;
      }
      this.visiblePopover = new bootstrap.Popover(overlayElement, {
        title: this.$p.t("ui", "classTimeSlotType"),
        html: true,
        content: this.$refs.classScheduleTypeSelectorContainer.innerHTML,
        placement: "left",
      });
      this.visiblePopover.show();
      setTimeout(() => {
        document
          .querySelectorAll(".class-schedule-type-selector-option")
          .forEach((option) => {
            option.addEventListener("click", (event) => {
              let selectedTypeDescription = event.currentTarget.innerText;

              let selectedType = this.classTimeSlotTypes.find((type) =>
                type.bezeichnung_mehrsprachig.some(
                  (desc) => desc.value === selectedTypeDescription,
                ),
              );
              if (!selectedType) {
                console.error(
                  "Could not find class time slot type for selected description: " +
                    selectedTypeDescription,
                );
                return;
              }

              this.overlays = this.overlays.map((overlay) => {
                if (overlay.id === overlayId) {
                  return {
                    ...overlay,
                    type: selectedType.unterrichtszeitentyp_kurzbz,
                    hexColor: selectedType.hintergrundfarbe,
                  };
                }
                return overlay;
              });

              this.visiblePopover.dispose();
              this.visiblePopover = null;
            });
          });
      }, 10);
    },
    hideOverlayClassTimeTypePopover() {
      if (this.visiblePopover) {
        this.visiblePopover.dispose();
        this.visiblePopover = null;
      }
    },
    getClassTimeSlotTypeLabel(classTimeSlotType) {
      if (!classTimeSlotType) return "";
      return this.userLanguage?.value === 'English' ? 
        classTimeSlotType.bezeichnung_mehrsprachig[1].value : classTimeSlotType.bezeichnung_mehrsprachig[0].value;
    }
  },
  unmounted() {
    this.hideOverlayClassTimeTypePopover();
  },
  template: /*html*/ `
  <div ref="calendarSelectorContainer" >
  <div 
    ref="calendarContainer"
    @mousedown="handleMouseDown"
    @mousemove="(event) => { handleMouseMove(event); handleMouseMoveOnResizerColumn(event); }"
    @mouseup="(event) => { handleMouseUp(event); handleMouseUpOnResizerColumn(event); }"
    @mouseleave="handleMouseLeaveOnCalendar"
    class="h-100 w-100"
    style="height: 100%; width: 100%"
  >
      <div class="fhc-calendar-mode-week-view h-100">
        <div
          class="fhc-calendar-base-grid"
          style="
            display: grid;
            width: 100%;
            height: 100%;
            overflow: auto;
            grid-template-rows: auto auto 1fr;
            grid-template-columns: auto repeat(7, 1fr);
          "
        >
          <div
            class="grid-header"
            style="
              display: grid;
              grid-template-columns: subgrid;
              grid-column: 1 / -1;
            "
          >
            <div v-for="(day, index) in daysInWeek" :key="index" class="main-header" :style="'grid-column: ' + (index + 2)">
              <div class="">
                <div class="fhc-calendar-base-label-dow">
                  <b class="long">{{ day }}</b>
                </div>
              </div>
            </div>
          </div>
          <div
            class="grid-allday"
            style="
              display: grid;
              grid-template-columns: subgrid;
              grid-column: 1 / -1;
            "
          ></div>
          <div
            style="
              display: grid;
              overflow: auto;
              grid-column: 1 / -1;
              grid-template-columns: subgrid;
            "
          >
            <div
              class="grid-main"
              style="
                position: relative;
                grid-area: 1 / 1 / -1 / -1;
                display: grid;
                grid-template-columns: subgrid;
                grid-template-rows: [t_28800000 ps_0] 2700000fr [t_31500000 pe_0 ps_1] 2700000fr [t_34200000 pe_1] 600000fr [t_34800000 ps_2] 2700000fr [t_37500000 pe_2 ps_3] 2700000fr [t_40200000 pe_3] 600000fr [t_40800000 ps_4] 2700000fr [t_43500000 pe_4 ps_5] 2700000fr [t_46200000 pe_5 ps_6] 2700000fr [t_48900000 pe_6 ps_7] 2700000fr [t_51600000 pe_7] 600000fr [t_52200000 ps_8] 2700000fr [t_54900000 pe_8 ps_9] 2700000fr [t_57600000 pe_9] 600000fr [t_58200000 ps_10] 2700000fr [t_60900000 pe_10 ps_11] 2700000fr [t_63600000 pe_11] 600000fr [t_64200000 ps_12] 2700000fr [t_66900000 pe_12 ps_13] 2700000fr [t_69600000 pe_13] 600000fr [t_70200000 ps_14] 2700000fr [t_72900000 pe_14 ps_15] 2700000fr [t_75600000 pe_15 end];
              "
            >
              <div v-for="(timeSlot, index) in timeSlotsInDay" :key="index" class="part-header" :style="'grid-area: ps_' + index + ' / 1 / pe_' + index">
                <div
                  :class="$props.isPreviewMode ? 'py-0' : 'py-2'"
                  class="d-flex gap-1"
                >
                  <span>{{ timeSlot.split('-')[0] }}</span><span>-</span><span>{{ timeSlot.split('-')[1] }}</span>
                </div>
              </div>
              <div
                class="grid-body"
                style="
                  display: grid;
                  grid-template-rows: subgrid;
                  grid-template-columns: subgrid;
                  grid-area: 1 / 2 / -1 / -1;
                "
              >
                <div
                  v-drop:link-strict.calendar_selector_overlay-collection="handleOverlayDrop"
                  v-for="(timeSlot, index) in timeSlotsInDay"
                  :key="index"
                  :style="'position: relative; grid-area: ps_' + index + ' / 1 / pe_' + index"
                  :data-weekday="1"
                  :data-number="index"
                  :data-time="timeSlot"
                  @mouseleave="handleLeave"
                  class="part-body"
                  >
                </div>
                <div
                  v-drop:link-strict.calendar_selector_overlay-collection="handleOverlayDrop"
                  class="fhc-calendar-base-grid-line fhc-pointer-events-none"
                  style="
                    position: relative;
                    display: grid;
                    grid-auto-flow: dense;
                    grid-template-rows: subgrid;
                    grid-area: 1 / 1 / -1;
                  "
                ></div>
                <div
                  v-drop:link-strict.calendar_selector_overlay-collection="handleOverlayDrop"
                  v-for="(timeSlot, index) in timeSlotsInDay"
                  :key="index"
                  :style="'position: relative; grid-area: ps_' + index + ' / 2 / pe_' + index"
                  :data-weekday="2"
                  :data-number="index + timeSlotsInDay.length"
                  :data-time="timeSlot"
                  @mouseleave="handleLeave"
                  class="part-body"
                >
                </div>
                <div
                  v-drop:link-strict.calendar_selector_overlay-collection="handleOverlayDrop"
                  class="fhc-calendar-base-grid-line fhc-pointer-events-none"
                  style="
                    position: relative;
                    display: grid;
                    grid-auto-flow: dense;
                    grid-template-rows: subgrid;
                    grid-area: 1 / 2 / -1;
                  "
                ></div>
                <div
                  v-drop:link-strict.calendar_selector_overlay-collection="handleOverlayDrop"
                  v-for="(timeSlot, index) in timeSlotsInDay"
                  :key="index"
                  :style="'position: relative; grid-area: ps_' + index + ' / 3 / pe_' + index"
                  :data-weekday="3"
                  :data-number="index + timeSlotsInDay.length * 2"
                  :data-time="timeSlot"
                  @mouseleave="handleLeave"
                  class="part-body"
                >
                </div>
                <div
                  v-drop:link-strict.calendar_selector_overlay-collection="handleOverlayDrop"
                  class="fhc-calendar-base-grid-line fhc-pointer-events-none"
                  style="
                    position: relative;
                    display: grid;
                    grid-auto-flow: dense;
                    grid-template-rows: subgrid;
                    grid-area: 1 / 3 / -1;
                  "
                ></div>
                <div
                  v-drop:link-strict.calendar_selector_overlay-collection="handleOverlayDrop"
                  v-for="(timeSlot, index) in timeSlotsInDay"
                  :key="index"
                  :style="'position: relative; grid-area: ps_' + index + ' / 4 / pe_' + index"
                  :data-weekday="4"
                  :data-number="index + timeSlotsInDay.length * 3"
                  :data-time="timeSlot"
                  @mouseleave="handleLeave"
                  class="part-body"
                >
                </div>
                <div
                  v-drop:link-strict.calendar_selector_overlay-collection="handleOverlayDrop"
                  class="fhc-calendar-base-grid-line fhc-pointer-events-none"
                  style="
                    position: relative;
                    display: grid;
                    grid-auto-flow: dense;
                    grid-template-rows: subgrid;
                    grid-area: 1 / 4 / -1;
                  "
                ></div>
                <div
                  v-drop:link-strict.calendar_selector_overlay-collection="handleOverlayDrop"
                  v-for="(timeSlot, index) in timeSlotsInDay"
                  :key="index"
                  :style="'position: relative; grid-area: ps_' + index + ' / 5 / pe_' + index"
                  :data-weekday="5"
                  :data-number="index + timeSlotsInDay.length * 4"
                  :data-time="timeSlot"
                  @mouseleave="handleLeave"
                  class="part-body"
                >
                </div>
                <div
                  v-drop:link-strict.calendar_selector_overlay-collection="handleOverlayDrop"
                  class="fhc-calendar-base-grid-line fhc-pointer-events-none"
                  style="
                    position: relative;
                    display: grid;
                    grid-auto-flow: dense;
                    grid-template-rows: subgrid;
                    grid-area: 1 / 5 / -1;
                  "
                ></div>
                <div
                  v-drop:link-strict.calendar_selector_overlay-collection="handleOverlayDrop"
                  v-for="(timeSlot, index) in timeSlotsInDay"
                  :key="index"
                  :style="'position: relative; grid-area: ps_' + index + ' / 6 / pe_' + index"
                  :data-weekday="6"
                  :data-number="index + timeSlotsInDay.length * 5"
                  :data-time="timeSlot"
                  @mouseleave="handleLeave"
                  class="part-body"
                >
                </div>
                <div
                  v-drop:link-strict.calendar_selector_overlay-collection="handleOverlayDrop"
                  class="fhc-calendar-base-grid-line fhc-pointer-events-none"
                  style="
                    position: relative;
                    display: grid;
                    grid-auto-flow: dense;
                    grid-template-rows: subgrid;
                    grid-area: 1 / 6 / -1;
                  "
                ></div>
                <div
                  v-drop:link-strict.calendar_selector_overlay-collection="handleOverlayDrop"
                  v-for="(timeSlot, index) in timeSlotsInDay"
                  :key="index"
                  :style="'position: relative; grid-area: ps_' + index + ' / 7 / pe_' + index"
                  :data-weekday="7"
                  :data-number="index + timeSlotsInDay.length * 6"
                  :data-time="timeSlot"
                  @mouseleave="handleLeave"
                  class="part-body"
                >
                </div>
                <div
                  v-drop:link-strict.calendar_selector_overlay-collection="handleOverlayDrop"
                  class="fhc-calendar-base-grid-line fhc-pointer-events-none"
                  style="
                    position: relative;
                    display: grid;
                    grid-auto-flow: dense;
                    grid-template-rows: subgrid;
                    grid-area: 1 / 7 / -1;
                  "
                ></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div id="overlays-container" class='d-none'>
      <div 
        v-for="(index) in 50"
        v-draggable:copyLink.noimage="selectedDragObject" 
        @mousedown='overlaySelectionChanged($event, "overlay-item-" + index)'
        @mouseover="handleMouseOverOnOverlay('overlay-item-' + index)"
        :id="'overlay-item-' + index"
        :class="{
          'fhc-drag-handle': !$props.isPreviewMode,
        }"
        :style="{ backgroundColor: this.overlays.find(overlay => overlay.id === 'overlay-item-' + index)?.hexColor || this.defaultOverlayColor }"
        :title="getOverlayClassScheduleTypeTitle('overlay-item-' + index)"
        class="d-none fhc-pointer-events-all flex-column justify-content-between align-items-center shadow rounded-1"
        :draggable='!$props.isPreviewMode ? "true" : "false"'
      >
      <div 
        class="d-flex flex-column justify-content-center align-items-center gap-1 p-2 overflow-scroll"
        >
        <a 
          v-if="!$props.isPreviewMode"
          @mousedown.stop="showOverlayClassTimeTypePopover('overlay-item-' + index)"
          :title="$p.t('ui', 'bearbeiten')"
          class="position-absolute top-0 start-0 p-1 d-flex gap-1 fhc-cursor-pointer"
          >
          <i class="fa fa-edit text-primary fs-6"></i>
        </a>
        <a
          v-if="!$props.isPreviewMode"
          @mousedown.stop="deleteOverlay('overlay-item-' + index)"
          :title="$p.t('global', 'loeschen')"
          class="position-absolute top-0 end-0 p-1 d-flex gap-1 fhc-cursor-pointer"
          >
          <i class="fa fa-trash text-danger fs-6"></i>
        </a>
        <p 
          :style="'color: ' + this.overlayTextColor"
          class="bg-transparent rounded-1 py-0 m-0 "
          >
          {{ getOverlayTimeSlotSpan("overlay-item-" + index) }}
        </p>
        <span
          v-if="!isOverlayMinimallySized(this.overlays.find(overlay => overlay.id === 'overlay-item-' + index))" 
          class="badge badge-pill bg-light text-dark"
        >
            {{ this.getOverlayClassScheduleTypeTitle('overlay-item-' + index) }}
        </span>
      </div>
      <span
        v-if="!$props.isPreviewMode"
        @mousedown.stop="handleMouseDownOnResizer($event, 'overlay-item-resizer-' + index, 'overlay-item-' + index)"
        :id="'overlay-item-resizer-' + index"
        :class="{
              'position-absolute bottom-0 end-0': isOverlayMinimallySized(this.overlays.find(overlay => overlay.id === 'overlay-item-' + index)),
            }"
        class="d-flex justify-content-center p-1 fhc-resize-vertical fhc-w-fit"
      >
        <i class="fa-solid fa-grip-lines"></i>
      </span>
    </div>
  </div>
  <div ref="classScheduleTypeSelectorContainer" class="d-none">
    <div class='d-flex flex-column gap-2'>
      <span v-for="(type, index) in classTimeSlotTypes"
        :key="index"
        :data-type="type.unterrichtszeitentyp_kurzbz"
        class="btn btn-sm btn-outline-dark class-schedule-type-selector-option"
      >
        {{ getClassTimeSlotTypeLabel(type) }}
      </span>
    </div>
  </div>
</div>
  `,
};
