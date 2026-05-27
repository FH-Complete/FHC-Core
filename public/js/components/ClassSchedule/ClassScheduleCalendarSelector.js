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
    classroomHours: {
      type: [Array, null],
      required: true,
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
    defaultClassTimeSlotType: {
      type: String,
      required: false,
      default: null,
    },
  },
  emits: ["overlaysChanged"],
  watch: {
    editedOverlays: {
      async handler(newVal) {
        this.removeAllDisplayedOverlayElements();

        await this.$nextTick();

        await newVal
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
          .forEach(async (overlay) => {
            let firstElementDataNumber =
              (overlay.weekday - 1) * this.timeSlotsInDay.length +
              this.timeSlotsInDay.indexOf(overlay.startingTimeSlot);
            let lastElementDataNumber =
              (overlay.weekday - 1) * this.timeSlotsInDay.length +
              this.timeSlotsInDay.indexOf(overlay.endingTimeSlot);

            let firstSelectedElement = this.getElementByDataNumber(
              firstElementDataNumber,
            );
            let lastSelectedElement = this.getElementByDataNumber(
              lastElementDataNumber,
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

            this.currentFirstSelectedElementNumber =
              this.getElementDataNumber(firstSelectedElement);
            this.currentLastSelectedElementNumber =
              this.getElementDataNumber(lastSelectedElement);

            await this.createOverlay();

            this.resetPartBodiesBackgroundColor();
            this.resetCurrentSelectedElementsNumbers();
          });

        this.overlays = this.overlays.map((existingOverlay, index) => {
          let existingOverlayInNewVal = newVal[index];
          let selectedClassTimeSlotType = this.$props.classTimeSlotTypes.find(
            (type) =>
              type.unterrichtszeitentyp_kurzbz === existingOverlayInNewVal.type,
          );

          existingOverlay.databaseId = existingOverlayInNewVal.databaseId;
          existingOverlay.type = existingOverlayInNewVal.type;
          existingOverlay.backgroundColor =
            selectedClassTimeSlotType?.hintergrundfarbe || null;

          return {
            ...existingOverlay,
          };
        });
      },
      deep: true,
      immediate: true,
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
      componentID: Math.random().toString(36).substring(2, 15),
      daysInWeek: [
        this.$p.t("ui", "monday"),
        this.$p.t("ui", "tuesday"),
        this.$p.t("ui", "wednesday"),
        this.$p.t("ui", "thursday"),
        this.$p.t("ui", "friday"),
        this.$p.t("ui", "saturday"),
        this.$p.t("ui", "sunday"),
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
      currentlyActiveOverlayDropzoneItemDataNumber: null,
      onOverlaySelectionPositionOffset: {
        x: null,
        y: null,
      },
      isTimeElementResizingInProgress: false,
      currentUpperResize: {
        id: null,
        overlayId: null,
      },
      currentLowerResize: {
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
        type: "calendar_selector_overlay",
        id: item.id,
      }));
    },
    currentlySelectedTimeSlotSpan() {
      if (
        !this.currentFirstSelectedElementNumber ||
        !this.currentLastSelectedElementNumber
      )
        return null;

      const firstTimeSlot = this.getTimeSlotItemByDataNumber(
        this.currentFirstSelectedElementNumber,
      );
      const lastTimeSlot = this.getTimeSlotItemByDataNumber(
        this.currentLastSelectedElementNumber,
      );

      let firstTimeSlotFragment = firstTimeSlot.split("-")[0];
      let lastTimeSlotFragment = lastTimeSlot.split("-")[1];

      return firstTimeSlotFragment + "-" + lastTimeSlotFragment;
    },
    userLanguage() {
      return Vue.ref(FHC_JS_DATA_STORAGE_OBJECT.user_language);
    },
    timeSlotsInDay() {
      if (!this.$props.classroomHours) return ["08:00-08:45"];

      return this.$props.classroomHours;
    },
  },
  methods: {
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

      let closestXPartBody =
        this.getXClosestPartBodyWithGridLineAdditionToMouseEvent(event.clientX);
      if (!closestXPartBody) return;

      let closestXPartBodyNumber = this.getElementDataNumber(closestXPartBody);

      let weekday = this.getWeekdayByElementDataNumber(closestXPartBodyNumber);
      if (!weekday) {
        this.$fhcAlert.alertError(
          this.$p.t("ui", "classTimeSlotSelectionErrorMessage"),
        );
        this.isTimeElementCreationInProgress = false;
        return;
      }

      let closestPartBody =
        this.getYClosestPartBodyWithGridLineAdditionToMouseEventPerWeekday(
          event.clientY,
          weekday,
        );
      if (!closestPartBody) return;

      this.currentFirstSelectedElementNumber =
        this.getElementDataNumber(closestPartBody);
      closestPartBody.style.backgroundColor = this.selectedTimeSlotLabelColor;
    },
    handleMouseMove(event) {
      if (this.$props.isPreviewMode) return;
      if (!this.isTimeElementCreationInProgress) return;
      let weekday = this.getWeekdayByElementDataNumber(
        this.currentFirstSelectedElementNumber,
      );

      let closestPartBody =
        this.getYClosestPartBodyWithGridLineAdditionToMouseEventPerWeekday(
          event.clientY,
          weekday,
        );
      if (!closestPartBody) return;

      this.getPartBodiesElementByWeekday(weekday).forEach((child) => {
        let itemNumber = this.getElementDataNumber(child);

        if (
          itemNumber >= this.currentFirstSelectedElementNumber &&
          itemNumber <= this.getElementDataNumber(closestPartBody)
        ) {
          child.style.backgroundColor = this.selectedTimeSlotLabelColor;
        } else if (
          itemNumber <= this.currentFirstSelectedElementNumber &&
          itemNumber >= this.getElementDataNumber(closestPartBody)
        ) {
          child.style.backgroundColor = this.selectedTimeSlotLabelColor;
        } else {
          child.style.backgroundColor = this.defaultTimeSlotLabelColor;
        }
      });
    },
    async handleMouseUp(event) {
      if (this.$props.isPreviewMode) return;

      let isLeftMouseButton = event.buttons === 0;
      if (!isLeftMouseButton) return;

      if (!this.isTimeElementCreationInProgress) {
        this.isTimeElementCreationInProgress = false;
        return;
      }

      if (this.isTimeElementResizingInProgress) {
        return;
      }

      this.isTimeElementCreationInProgress = false;

      let weekday = this.getWeekdayByElementDataNumber(
        this.currentFirstSelectedElementNumber,
      );

      let closestPartBody =
        this.getYClosestPartBodyWithGridLineAdditionToMouseEventPerWeekday(
          event.clientY,
          weekday,
        );
      if (!closestPartBody) return;

      this.getPartBodiesElementByWeekday(weekday).forEach((child) => {
        let itemNumber = this.getElementDataNumber(child);

        if (
          itemNumber >= this.currentFirstSelectedElementNumber &&
          itemNumber <= this.getElementDataNumber(closestPartBody)
        ) {
          child.style.backgroundColor = this.selectedTimeSlotLabelColor;
        } else if (
          itemNumber <= this.currentFirstSelectedElementNumber &&
          itemNumber >= this.getElementDataNumber(closestPartBody)
        ) {
          child.style.backgroundColor = this.selectedTimeSlotLabelColor;
        } else {
          child.style.backgroundColor = this.defaultTimeSlotLabelColor;
        }
      });

      await this.createOverlay();

      this.resetPartBodiesBackgroundColor();
      this.resetCurrentSelectedElementsNumbers();
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
      let number = this.getElementDataNumber(child);

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

      let overlay = this.overlays.find((overlay) => overlay.id === overlayId);
      if (!overlay) return;

      let overlayElement = this.getOverlayElementByOverlayId(overlayId);
      if (!overlayElement) return;

      this.selected = [
        {
          type: "calendar_selector_overlay",
          id: overlayId,
        },
      ];

      let startingElement = this.getElementByDataNumber(
        overlay.startingTimeSlotElementNumber,
      );
      if (!startingElement) return;

      let overlayElementRect = overlayElement.getBoundingClientRect();
      const calibrationOffset = 20;
      this.onOverlaySelectionPositionOffset.x =
        event.clientX - overlayElementRect.left - calibrationOffset;
      this.onOverlaySelectionPositionOffset.y =
        event.clientY - overlayElementRect.top - calibrationOffset;
    },
    handleMouseUpOnOverlay(overlayId) {
      if (this.$props.isPreviewMode) return;

      let overlayElement = this.getOverlayElementByOverlayId(overlayId);
      if (!overlayElement) return;

      this.selected = [];
    },
    handleOverlayDrop(event) {
      this.hideOverlayClassTimeTypePopover();
      this.resetCurrentSelectedElementsNumbers();
      this.currentlyActiveOverlayDropzoneItemDataNumber = null;

      if (this.$props.isPreviewMode) return;

      const draggedItemId =
        this.selected.length > 0 ? this.selected[0].id : null;
      if (!draggedItemId) return;

      const draggedItem = this.getOverlayElementByOverlayId(draggedItemId);
      if (!draggedItem) return;

      let overlay = this.overlays.find(
        (overlay) => overlay.id === draggedItemId,
      );
      if (!overlay) return;

      let mouseX = event.clientX;
      let mouseY = event.clientY;

      this.resetPartBodiesBackgroundColor();
      this.isTimeElementCreationInProgress = false;

      mouseX = mouseX - this.onOverlaySelectionPositionOffset.x;
      mouseY = mouseY - this.onOverlaySelectionPositionOffset.y;

      let closestXPartBody =
        this.getXClosestPartBodyWithGridLineAdditionToMouseEvent(mouseX);
      if (!closestXPartBody) {
        console.error("No closest X part body found");
        return;
      }
      let closestXPartBodyNumber = this.getElementDataNumber(closestXPartBody);

      let firstWeekday = this.getWeekdayByElementDataNumber(
        closestXPartBodyNumber,
      );
      if (!firstWeekday) {
        this.$fhcAlert.alertError(
          this.$p.t("ui", "classTimeSlotSelectionErrorMessage"),
        );
        this.isTimeElementCreationInProgress = false;
        return;
      }

      let dropzoneItem =
        this.getYClosestPartBodyWithGridLineAdditionToMouseEventPerWeekday(
          mouseY,
          firstWeekday,
        );
      if (!dropzoneItem) return;

      let weekday = this.getWeekdayByElementDataNumber(
        this.getElementDataNumber(dropzoneItem),
      );
      if (!weekday) {
        return;
      }

      let newStartTimeSlotItemNumber = this.getElementDataNumber(dropzoneItem);
      let newStartTimeSlot = this.getTimeSlotItemByDataNumber(
        newStartTimeSlotItemNumber,
      );

      let newEndingTimeSlotItemNumber =
        newStartTimeSlotItemNumber +
        overlay.endingTimeSlotElementNumber -
        overlay.startingTimeSlotElementNumber;

      let maxTimeSlotItemNumber = weekday * this.timeSlotsInDay.length - 1;
      if (newEndingTimeSlotItemNumber > maxTimeSlotItemNumber) {
        newEndingTimeSlotItemNumber = maxTimeSlotItemNumber;
      }

      let overlappedOverlay = this.overlays.find((overlay) => {
        if (overlay.id === this.selected[0].id) return false;
        if (overlay.weekday !== firstWeekday) return false;

        let isEnvelopingOtherOverlay =
          newStartTimeSlotItemNumber <= overlay.startingTimeSlotElementNumber &&
          overlay.endingTimeSlotElementNumber <= newEndingTimeSlotItemNumber;
        let isInsideOtherOverlay =
          overlay.startingTimeSlotElementNumber <= newStartTimeSlotItemNumber &&
          newEndingTimeSlotItemNumber <= overlay.endingTimeSlotElementNumber;
        let isIntersectingWithOtherOverlayFromTop =
          newStartTimeSlotItemNumber <= overlay.startingTimeSlotElementNumber &&
          overlay.startingTimeSlotElementNumber <=
            newEndingTimeSlotItemNumber &&
          newEndingTimeSlotItemNumber <= overlay.endingTimeSlotElementNumber;
        let isIntersectingWithOtherOverlayFromBottom =
          overlay.startingTimeSlotElementNumber <= newStartTimeSlotItemNumber &&
          newStartTimeSlotItemNumber <= overlay.endingTimeSlotElementNumber &&
          overlay.endingTimeSlotElementNumber <= newEndingTimeSlotItemNumber;

        return (
          isEnvelopingOtherOverlay ||
          isInsideOtherOverlay ||
          isIntersectingWithOtherOverlayFromTop ||
          isIntersectingWithOtherOverlayFromBottom
        );
      });
      if (overlappedOverlay) {
        this.$fhcAlert.alertError(
          this.$p.t("ui", "classTimeSlotOverlapErrorMessage"),
        );

        let overlayElement = this.getOverlayElementByOverlayId(overlay.id);
        if (!overlayElement) return;

        let originalStartingElement = this.getElementByDataNumber(
          overlay.startingTimeSlotElementNumber,
        );
        if (!originalStartingElement) return;

        let originalEndingElement = this.getElementByDataNumber(
          overlay.endingTimeSlotElementNumber,
        );
        if (!originalEndingElement) return;

        const startingRect = originalStartingElement.getBoundingClientRect();
        const endingRect = originalEndingElement.getBoundingClientRect();

        overlayElement.style.top = startingRect.offsetTop + "px";

        return;
      }

      let gridLineNumber;
      try {
        gridLineNumber = this.getLineNumberFromSelectedElementNumber(
          this.getElementDataNumber(dropzoneItem),
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

      let overlayElement = this.getOverlayElementByOverlayId(draggedItemId);
      gridLine.appendChild(overlayElement);

      draggedItem.style.top = dropzoneItem.offsetTop + "px";

      let newEndingTimeSlotElement = this.getElementByDataNumber(
        newEndingTimeSlotItemNumber,
      );
      if (!newEndingTimeSlotElement) return;

      draggedItem.style.height =
        newEndingTimeSlotElement.offsetTop +
        newEndingTimeSlotElement.getBoundingClientRect().height -
        dropzoneItem.offsetTop +
        "px";

      this.overlays = this.overlays.map((overlay) => {
        if (overlay.id === draggedItemId) {
          const timeSlot = this.getTimeSlotItemByDataNumber(
            newEndingTimeSlotItemNumber,
          );
          overlay.endingTimeSlotElementNumber = newEndingTimeSlotItemNumber;
          overlay.endingTimeSlot = timeSlot;

          overlay.weekday = gridLineNumber + 1;
          return overlay;
        }
        return overlay;
      });

      this.overlays = this.overlays.map((overlay) => {
        if (overlay.id === draggedItemId) {
          overlay.startingTimeSlotElementNumber =
            this.getElementDataNumber(dropzoneItem);
          overlay.startingTimeSlot = newStartTimeSlot;
          return overlay;
        }
        return overlay;
      });
    },
    handleMouseDownOnUpperResize(event, resizeId, overlayId) {
      if (this.$props.isPreviewMode) return;

      let isLeftMouseButton = event.buttons === 1;
      if (!isLeftMouseButton) return;

      this.isTimeElementResizingInProgress = true;

      this.currentUpperResize = {
        id: resizeId,
        overlayId: overlayId,
      };

      this.resetCurrentLowerResize();

      this.getOverlayElementByOverlayId(overlayId).setAttribute(
        "draggable",
        "false",
      );
    },
    handleMouseDownOnLowerResize(event, resizeId, overlayId) {
      if (this.$props.isPreviewMode) return;

      let isLeftMouseButton = event.buttons === 1;
      if (!isLeftMouseButton) return;

      this.isTimeElementResizingInProgress = true;

      this.currentLowerResize = {
        id: resizeId,
        overlayId: overlayId,
      };

      this.resetCurrentUpperResize();

      this.getOverlayElementByOverlayId(overlayId).setAttribute(
        "draggable",
        "false",
      );
    },
    handleMouseMoveOnResizeColumn(event) {
      if (this.$props.isPreviewMode) return;

      if (!this.isTimeElementResizingInProgress) {
        return;
      }

      if (
        this.currentUpperResize.id === null &&
        this.currentLowerResize.id === null
      ) {
        return;
      }

      let isUpperResizeMoving = this.currentUpperResize.id !== null;

      let overlayId = isUpperResizeMoving
        ? this.currentUpperResize.overlayId
        : this.currentLowerResize.overlayId;
      let overlay = this.overlays.find((overlay) => overlay.id === overlayId);
      if (!overlay) return;

      let overlayElement = this.getOverlayElementByOverlayId(overlayId);
      if (!overlayElement) return;

      let overlayTop = parseInt(overlayElement.getBoundingClientRect().top);
      let overlayBottom = parseInt(
        overlayElement.getBoundingClientRect().bottom,
      );

      let mouseY = event.clientY;

      let randomTimeSlotElement = this.getElementByDataNumber(
        overlay.startingTimeSlotElementNumber,
      );
      let randomTimeSlotElementHeight =
        randomTimeSlotElement.getBoundingClientRect().height;

      let overlayHeight = overlayBottom - overlayTop;

      if (overlayHeight < randomTimeSlotElementHeight) {
        this.isTimeElementResizingInProgress = false;

        this.oldMousePosition.x = null;
        this.oldMousePosition.y = null;
        this.$fhcAlert.alertError(
          this.$p.t("ui", "classTimeSlotMinimumSizeErrorMessage"),
        );
        this.handleMouseUpOnResizeColumn(event);

        let currentAnchorTimeSlotElement = isUpperResizeMoving
          ? this.getElementByDataNumber(overlay.endingTimeSlotElementNumber)
          : this.getElementByDataNumber(overlay.startingTimeSlotElementNumber);
        if (!currentAnchorTimeSlotElement) return;

        const rect = currentAnchorTimeSlotElement.getBoundingClientRect();
        const resizeRect = overlayElement.getBoundingClientRect();

        let deltaY = null;
        if (isUpperResizeMoving) {
          deltaY = rect.top - resizeRect.top;
          overlayElement.style.top =
            parseInt(overlayElement.style.top) + deltaY + "px";
          overlayElement.style.height =
            parseInt(overlayElement.style.height) - deltaY + "px";
        } else {
          deltaY = rect.bottom - resizeRect.bottom;
          overlayElement.style.height =
            parseInt(overlayElement.style.height) + deltaY + "px";
        }

        this.overlays = this.overlays.map((overlay) => {
          if (overlay.id !== overlayId) return overlay;

          if (isUpperResizeMoving) {
            overlay.startingTimeSlotElementNumber = this.getElementDataNumber(
              currentAnchorTimeSlotElement,
            );
            overlay.startingTimeSlot = this.getTimeSlotItemByDataNumber(
              this.getElementDataNumber(currentAnchorTimeSlotElement),
            );
          } else {
            overlay.endingTimeSlotElementNumber = this.getElementDataNumber(
              currentAnchorTimeSlotElement,
            );
            overlay.endingTimeSlot = this.getTimeSlotItemByDataNumber(
              this.getElementDataNumber(currentAnchorTimeSlotElement),
            );
          }

          return overlay;
        });

        if (isUpperResizeMoving) {
          this.resetCurrentUpperResize();
        } else {
          this.resetCurrentLowerResize();
        }

        return;
      }

      if (!this.oldMousePosition.x || !this.oldMousePosition.y) {
        this.oldMousePosition.x = event.clientX;
        this.oldMousePosition.y = event.clientY;
        return;
      }

      let resizeElementId = isUpperResizeMoving
        ? this.currentUpperResize.id
        : this.currentLowerResize.id;
      const resizeElement = this.getResizeElementById(resizeElementId);
      if (!resizeElement) return;

      const newMousePosition = {
        x: event.clientX,
        y: event.clientY,
      };

      const deltaY = newMousePosition.y - this.oldMousePosition.y;
      if (deltaY > 0) {
        resizeElement.style.bottom =
          parseInt(resizeElement.style.bottom || 0) - deltaY + "px";
        if (!isUpperResizeMoving) {
          overlayElement.style.height =
            parseInt(overlayElement.style.height) + deltaY + "px";
        } else {
          overlayElement.style.top =
            parseInt(overlayElement.style.top) + deltaY + "px";
          overlayElement.style.height =
            parseInt(overlayElement.style.height) - deltaY + "px";
        }
      } else {
        resizeElement.style.bottom =
          parseInt(resizeElement.style.bottom || 0) - deltaY + "px";
        if (!isUpperResizeMoving) {
          overlayElement.style.height =
            parseInt(overlayElement.style.height) + deltaY + "px";
        } else {
          overlayElement.style.top =
            parseInt(overlayElement.style.top) + deltaY + "px";
          overlayElement.style.height =
            parseInt(overlayElement.style.height) - deltaY + "px";
        }
      }

      this.oldMousePosition.x = newMousePosition.x;
      this.oldMousePosition.y = newMousePosition.y;
    },
    handleMouseUpOnResizeColumn(event) {
      if (this.$props.isPreviewMode) return;

      if (!this.isTimeElementResizingInProgress) {
        return;
      }

      let isLeftMouseButton = event.buttons === 0;
      if (!isLeftMouseButton) return;

      this.isTimeElementResizingInProgress = false;

      let isUpperResizeMoving = this.currentUpperResize.id !== null;

      let resizeElementId = isUpperResizeMoving
        ? this.currentUpperResize.id
        : this.currentLowerResize.id;
      const resizeElement = this.getResizeElementById(resizeElementId);
      if (!resizeElement) {
        this.oldMousePosition.x = null;
        this.oldMousePosition.y = null;
        return;
      }

      const editedOverlayId = isUpperResizeMoving
        ? this.currentUpperResize.overlayId
        : this.currentLowerResize.overlayId;
      const overlayElement = this.getOverlayElementByOverlayId(editedOverlayId);
      if (!overlayElement) {
        this.oldMousePosition.x = null;
        this.oldMousePosition.y = null;
        return;
      }

      const editedOverlay = this.overlays.find(
        (overlay) => overlay.id === editedOverlayId,
      );
      if (!editedOverlay) {
        this.oldMousePosition.x = null;
        this.oldMousePosition.y = null;
        return;
      }

      let closestPartBody =
        this.getYClosestPartBodyWithGridLineAdditionToMouseEventPerWeekday(
          event.clientY,
          editedOverlay.weekday,
        );
      if (closestPartBody) {
        const closestPartBodyNumber =
          this.getElementDataNumber(closestPartBody);

        let firstSkippedOverOverlay = this.overlays.find((overlay) => {
          if (overlay.id === editedOverlayId) return false;

          if (
            editedOverlay.startingTimeSlotElementNumber <=
              overlay.startingTimeSlotElementNumber &&
            closestPartBodyNumber >= overlay.startingTimeSlotElementNumber &&
            !isUpperResizeMoving
          ) {
            return true;
          }

          if (
            editedOverlay.startingTimeSlotElementNumber >=
              overlay.startingTimeSlotElementNumber &&
            closestPartBodyNumber <= overlay.endingTimeSlotElementNumber &&
            isUpperResizeMoving
          ) {
            return true;
          }

          return false;
        });

        if (firstSkippedOverOverlay) {
          this.$fhcAlert.alertError(
            this.$p.t("ui", "classTimeSlotOverlapErrorMessage"),
          );
          let elementBeforeFirstSkippedOverElement = isUpperResizeMoving
            ? this.getElementByDataNumber(
                firstSkippedOverOverlay.endingTimeSlotElementNumber + 1,
              )
            : this.getElementByDataNumber(
                firstSkippedOverOverlay.startingTimeSlotElementNumber - 1,
              );
          if (!elementBeforeFirstSkippedOverElement) return;

          let elementBeforeFirstSkippedOverElementNumber =
            this.getElementDataNumber(elementBeforeFirstSkippedOverElement);

          const rect =
            elementBeforeFirstSkippedOverElement.getBoundingClientRect();
          const resizeRect = resizeElement.getBoundingClientRect();

          let deltaY = null;
          if (isUpperResizeMoving) {
            deltaY = rect.top - resizeRect.top;
            overlayElement.style.top =
              parseInt(overlayElement.style.top) + deltaY + "px";
            overlayElement.style.height =
              parseInt(overlayElement.style.height) - deltaY + "px";
          } else {
            deltaY = rect.bottom - resizeRect.bottom;
            overlayElement.style.height =
              parseInt(overlayElement.style.height) + deltaY + "px";
          }

          this.overlays = this.overlays.map((overlay) => {
            if (overlay.id !== editedOverlayId) return overlay;

            if (isUpperResizeMoving) {
              overlay.startingTimeSlotElementNumber =
                elementBeforeFirstSkippedOverElementNumber;
              overlay.startingTimeSlot = this.getTimeSlotItemByDataNumber(
                elementBeforeFirstSkippedOverElementNumber,
              );
            } else {
              overlay.endingTimeSlotElementNumber =
                elementBeforeFirstSkippedOverElementNumber;
              overlay.endingTimeSlot = this.getTimeSlotItemByDataNumber(
                elementBeforeFirstSkippedOverElementNumber,
              );
            }

            return overlay;
          });

          this.oldMousePosition.x = null;
          this.oldMousePosition.y = null;

          return;
        }
        const rect = closestPartBody.getBoundingClientRect();
        const resizeRect = resizeElement.getBoundingClientRect();

        let deltaY = null;
        if (isUpperResizeMoving) {
          deltaY = rect.top - resizeRect.top;
        } else {
          deltaY = rect.bottom - resizeRect.bottom;
        }

        if (!isUpperResizeMoving) {
          overlayElement.style.height =
            parseInt(overlayElement.style.height) + deltaY + "px";
        } else {
          overlayElement.style.top =
            parseInt(overlayElement.style.top) + deltaY + "px";
          overlayElement.style.height =
            parseInt(overlayElement.style.height) - deltaY + "px";
        }

        const closestPartBodyTimeSlot = this.getTimeSlotItemByDataNumber(
          closestPartBodyNumber,
        );

        this.overlays = this.overlays.map((overlay) => {
          if (overlay.id !== editedOverlayId) return overlay;

          if (isUpperResizeMoving) {
            overlay.startingTimeSlotElementNumber = closestPartBodyNumber;
            overlay.startingTimeSlot = closestPartBodyTimeSlot;
          } else {
            overlay.endingTimeSlotElementNumber = closestPartBodyNumber;
            overlay.endingTimeSlot = closestPartBodyTimeSlot;
          }
          return overlay;
        });
      }

      this.oldMousePosition.x = null;
      this.oldMousePosition.y = null;

      overlayElement.setAttribute("draggable", "true");

      this.resetCurrentUpperResize();
      this.resetCurrentLowerResize();
    },
    async handleMouseOverOnOverlay(overlayId) {
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

      await this.createOverlay();

      this.isTimeElementCreationInProgress = false;

      this.resetPartBodiesBackgroundColor();
      this.resetCurrentSelectedElementsNumbers();
    },
    handleMouseLeaveOnCalendar(event) {
      if (this.$props.isPreviewMode) return;

      if (this.isTimeElementCreationInProgress) {
        this.isTimeElementCreationInProgress = false;

        this.resetCurrentSelectedElementsNumbers();
        this.resetPartBodiesBackgroundColor();
      }

      if (this.isTimeElementResizingInProgress) {
        let overlayElement = this.getOverlayElementByOverlayId(
          this.currentLowerResize.overlayId,
        );

        this.isTimeElementResizingInProgress = false;

        this.oldMousePosition.x = null;
        this.oldMousePosition.y = null;
        this.$fhcAlert.alertError(
          this.$p.t("ui", "classTimeSlotResizeOutOfScopeErrorMessage"),
          1000,
        );
        this.handleMouseUpOnResizeColumn(event);

        let previousEndingTimeSlotElement = this.getElementByDataNumber(
          this.overlays.find(
            (overlay) => overlay.id === this.currentLowerResize.overlayId,
          ).endingTimeSlotElementNumber,
        );
        if (!previousEndingTimeSlotElement) return;

        const rect = previousEndingTimeSlotElement.getBoundingClientRect();
        const resizeRect = overlayElement.getBoundingClientRect();
        const deltaY = rect.bottom - resizeRect.bottom;

        overlayElement.style.height =
          parseInt(overlayElement.style.height) + deltaY + "px";

        this.overlays = this.overlays.map((overlay) => {
          if (overlay.id === this.currentLowerResize.overlayId) {
            overlay.endingTimeSlotElementNumber = this.getElementDataNumber(
              previousEndingTimeSlotElement,
            );
            overlay.endingTimeSlot = this.getTimeSlotItemByDataNumber(
              this.getElementDataNumber(previousEndingTimeSlotElement),
            );
            return overlay;
          }
          return overlay;
        });

        this.getOverlayElementByOverlayId(
          this.currentLowerResize.overlayId,
        ).setAttribute("draggable", "true");

        this.currentLowerResize = {
          id: null,
          overlayId: null,
        };

        return;
      }
    },
    onDragOver(event) {
      const calendarContainerZone = document.getElementById(
        "calendarContainer" + this.componentID,
      );
      const rect = calendarContainerZone.getBoundingClientRect();

      const inside =
        event.clientX >= rect.left &&
        event.clientX <= rect.right &&
        event.clientY >= rect.top &&
        event.clientY <= rect.bottom;

      if (!inside) {
        this.resetPartBodiesBackgroundColor();
        this.currentlyActiveOverlayDropzoneItemDataNumber = null;

        return;
      }

      let mouseX = event.clientX - this.onOverlaySelectionPositionOffset.x;
      let mouseY = event.clientY - this.onOverlaySelectionPositionOffset.y;

      let closestXPartBody =
        this.getXClosestPartBodyWithGridLineAdditionToMouseEvent(mouseX);
      if (!closestXPartBody) {
        return;
      }

      let closestPartBody =
        this.getYClosestPartBodyWithGridLineAdditionToMouseEventPerWeekday(
          mouseY,
          this.getWeekdayByElementDataNumber(
            this.getElementDataNumber(closestXPartBody),
          ),
        );
      if (!closestPartBody) {
        return;
      }

      let dropzoneElementDataNumber =
        this.getElementDataNumber(closestPartBody);
      if (
        dropzoneElementDataNumber === null ||
        dropzoneElementDataNumber === undefined
      ) {
        return;
      }

      let weekday = this.getWeekdayByElementDataNumber(
        dropzoneElementDataNumber,
      );
      if (!weekday) {
        return;
      }

      let overlayItem = this.overlays.find(
        (overlay) => overlay.id === this.selected[0].id,
      );
      if (!overlayItem) {
        return;
      }

      if (
        this.currentlyActiveOverlayDropzoneItemDataNumber !==
        dropzoneElementDataNumber
      ) {
        this.resetPartBodiesBackgroundColor();
      }

      let overlayItemSlotSpan =
        overlayItem.endingTimeSlotElementNumber -
        overlayItem.startingTimeSlotElementNumber;
      this.currentlyActiveOverlayDropzoneItemDataNumber =
        dropzoneElementDataNumber;

      this.getPartBodiesElementByWeekday(weekday).forEach((child) => {
        let currentItemDataNumber = this.getElementDataNumber(child);
        if (
          currentItemDataNumber >= dropzoneElementDataNumber &&
          currentItemDataNumber <=
            dropzoneElementDataNumber + overlayItemSlotSpan
        ) {
          child.style.backgroundColor = this.selectedTimeSlotLabelColor;
        }
      });
    },
    async createOverlay() {
      this.hideOverlayClassTimeTypePopover();

      let overlayElement;

      overlayElement = this.$refs.overlaysContainer.children[0];
      if (!overlayElement) {
        return;
      }

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

      let firstSelectedElementNumber =
        this.getElementDataNumber(firstSelectedChild);
      let lastSelectedElementNumber =
        this.getElementDataNumber(lastSelectedChild);
      if (
        firstSelectedElementNumber === null ||
        lastSelectedElementNumber === null
      ) {
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
        this.resetCurrentSelectedElementsNumbers();
        return;
      }

      gridLine.appendChild(overlayElement);

      if (!this.overlays.some((overlay) => overlay.id === overlayId)) {
        this.overlays.push({
          id: overlayId,
          startingTimeSlotElementNumber: this.currentFirstSelectedElementNumber,
          endingTimeSlotElementNumber: this.currentLastSelectedElementNumber,
          startingTimeSlot: this.getTimeSlotItemByDataNumber(
            this.currentFirstSelectedElementNumber,
          ),
          endingTimeSlot: this.getTimeSlotItemByDataNumber(
            this.currentLastSelectedElementNumber,
          ),
          type:
            this.$props.defaultClassTimeSlotType?.unterrichtszeitentyp_kurzbz ||
            null,
          backgroundColor:
            this.$props.defaultClassTimeSlotType?.hintergrundfarbe || null,
          weekday: gridLineNumber + 1,
        });
      }

      this.resetPartBodiesBackgroundColor();
    },
    deleteOverlay(overlayId) {
      let confirm = window.confirm(
        this.$p.t("ui", "classTimeSlotDeletionConfirmationMessage"),
      );
      if (!confirm) return;

      this.overlays = this.overlays.filter(
        (overlay) => overlay.id !== overlayId,
      );

      this.getOverlayElementByOverlayId(overlayId).remove();
      this.hideOverlayClassTimeTypePopover();
    },
    isOverlayMinimallySized(overlay) {
      if (!overlay) return false;

      if (
        !overlay.startingTimeSlotElementNumber ||
        !overlay.endingTimeSlotElementNumber
      ) {
        return false;
      }

      let difference =
        overlay.endingTimeSlotElementNumber -
        overlay.startingTimeSlotElementNumber;

      return difference < 1;
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
    getOverlayClassScheduleTypeTitle(overlayId) {
      let overlay = this.overlays.find((overlay) => overlay.id === overlayId);
      if (!overlay) return "";

      let typeDescriptions =
        this.$props.classTimeSlotTypes.find(
          (type) => type.unterrichtszeitentyp_kurzbz === overlay.type,
        )?.bezeichnung_mehrsprachig || "";
      if (!typeDescriptions) return "";

      return this.userLanguage?.value === "English"
        ? typeDescriptions[1].value
        : typeDescriptions[0].value;
    },
    handleChangeClassTimeSlotTypeForOverlay(newType) {
      let classTimeSlotType = this.$props.classTimeSlotTypes.find(
        (type) => type.unterrichtszeitentyp_kurzbz === newType,
      );
      if (!classTimeSlotType) {
        return;
      }

      this.overlays = this.overlays.map((overlay) => {
        if (overlay.id === this.currentlyEditedOverlayId) {
          return {
            ...overlay,
            type: classTimeSlotType.unterrichtszeitentyp_kurzbz,
            backgroundColor: classTimeSlotType.hintergrundfarbe,
          };
        }
        return overlay;
      });

      this.currentlyEditedOverlayId = null;
    },
    showOverlayClassTimeTypePopover(overlayId) {
      if (this.$props.isPreviewMode) return;

      let overlayElement = this.getOverlayElementByOverlayId(overlayId);
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

              let selectedType = this.$props.classTimeSlotTypes.find((type) =>
                type.bezeichnung_mehrsprachig.some(
                  (desc) => desc.value === selectedTypeDescription,
                ),
              );
              if (!selectedType) {
                return;
              }

              this.overlays = this.overlays.map((overlay) => {
                if (overlay.id === overlayId) {
                  return {
                    ...overlay,
                    type: selectedType.unterrichtszeitentyp_kurzbz,
                    backgroundColor: selectedType.hintergrundfarbe,
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
    getTimeSlotItemByDataNumber(number) {
      let timeSlotIndex = number % this.timeSlotsInDay.length;

      if (timeSlotIndex < 0 || timeSlotIndex >= this.timeSlotsInDay.length) {
        return null;
      }

      return this.timeSlotsInDay[timeSlotIndex];
    },
    getElementDataNumber(element) {
      if (!element) return null;
      if (!element.hasAttribute("data-number")) return null;

      return parseInt(element.getAttribute("data-number"));
    },
    getElementByDataNumber(number) {
      return this.$refs.calendarSelectorContainer.querySelector(
        `div[data-number='${number}']`,
      );
    },
    getWeekdayByElementDataNumber(dataNumber) {
      let weekday = parseInt(dataNumber / this.timeSlotsInDay.length) + 1;

      return weekday;
    },
    getClassTimeSlotTypeLabel(classTimeSlotType) {
      if (!classTimeSlotType) return "";
      return this.userLanguage?.value === "English"
        ? classTimeSlotType.bezeichnung_mehrsprachig[1].value
        : classTimeSlotType.bezeichnung_mehrsprachig[0].value;
    },
    getOverlayElementByOverlayId(overlayId) {
      return this.$refs.calendarSelectorContainer.querySelector(
        `#${overlayId}`,
      );
    },
    getResizeElementById(resizeId) {
      return this.$refs.calendarSelectorContainer.querySelector(`#${resizeId}`);
    },
    resetPartBodiesBackgroundColor() {
      this.$refs.calendarSelectorContainer
        ?.querySelectorAll("div[class*='part-body']")
        .forEach((child) => {
          child.style.backgroundColor = this.defaultTimeSlotLabelColor;
        });
    },
    getPartBodiesElement() {
      return this.$refs.calendarSelectorContainer.querySelectorAll(
        `div[class*='part-body']`,
      );
    },
    getPartBodiesElementByWeekday(weekday) {
      return this.$refs.calendarSelectorContainer.querySelectorAll(
        `div[data-weekday='${weekday}']`,
      );
    },
    getYClosestPartBodyWithGridLineAdditionToMouseEventPerWeekday(
      targetYLocation,
      weekday,
    ) {
      let closestPartBody = null;
      let closestDistance = Infinity;

      const partBodies = this.getPartBodiesElementByWeekday(weekday);
      partBodies.forEach((partBody) => {
        const rect = partBody.getBoundingClientRect();
        const distance = Math.abs(targetYLocation - rect.top - rect.height / 2);

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

      return closestPartBody;
    },
    getXClosestPartBodyWithGridLineAdditionToMouseEvent(targetXLocation) {
      let closestXPartBody = null;
      let closestXDistance = Infinity;

      const partBodies = this.$refs.calendarSelectorContainer.querySelectorAll(
        `div[class*='part-body']`,
      );
      partBodies.forEach((partBody) => {
        const rect = partBody.getBoundingClientRect();
        const distanceX = Math.abs(targetXLocation - rect.left);
        const isLocationInPartBody =
          targetXLocation >= rect.left - 5 && targetXLocation <= rect.right + 5;

        if (isLocationInPartBody) {
          closestXDistance = distanceX;
          closestXPartBody = partBody;
          return;
        }
      });

      return closestXPartBody;
    },
    removeAllDisplayedOverlayElements() {
      this.overlays = [];

      this.$refs.calendarContainer
        ?.querySelectorAll("div[id^='overlay-']")
        .forEach((element) => {
          element.remove();
        });
    },
    resetCurrentSelectedElementsNumbers() {
      this.currentFirstSelectedElementNumber = null;
      this.currentLastSelectedElementNumber = null;
    },
    resetCurrentUpperResize() {
      this.currentUpperResize = {
        id: null,
        overlayId: null,
      };
    },
    resetCurrentLowerResize() {
      this.currentLowerResize = {
        id: null,
        overlayId: null,
      };
    },
    logError(error) {
      console.error(error);
    },
  },
  unmounted() {
    this.hideOverlayClassTimeTypePopover();
  },
  template: /*html*/ `
  <div ref="calendarSelectorContainer">
  <div 
    ref="calendarContainer"
    :id="'calendarContainer' + componentID"
    @mousedown="handleMouseDown"
    @mousemove="(event) => { handleMouseMove(event); handleMouseMoveOnResizeColumn(event); }"
    @mouseup="(event) => { handleMouseUp(event); handleMouseUpOnResizeColumn(event); }"
    @mouseleave="handleMouseLeaveOnCalendar"
    @dragover.prevent="onDragOver"
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
            overflow: visible;
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
              overflow: visible;
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
                  :class="$props.isPreviewMode ? 'py-2' : 'py-2'"
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
                  v-drop:move.calendar_selector_overlay-collection="handleOverlayDrop"
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
                  v-drop:move.calendar_selector_overlay-collection="handleOverlayDrop"
                  class="fhc-calendar-base-grid-line"
                  style="
                    position: relative;
                    display: grid;
                    grid-auto-flow: dense;
                    grid-template-rows: subgrid;
                    grid-area: 1 / 1 / -1;
                  "
                ></div>
                <div
                  v-drop:move.calendar_selector_overlay-collection="handleOverlayDrop"
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
                  v-drop:move.calendar_selector_overlay-collection="handleOverlayDrop"
                  class="fhc-calendar-base-grid-line"
                  style="
                    position: relative;
                    display: grid;
                    grid-auto-flow: dense;
                    grid-template-rows: subgrid;
                    grid-area: 1 / 2 / -1;
                  "
                ></div>
                <div
                  v-drop:move.calendar_selector_overlay-collection="handleOverlayDrop"
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
                  v-drop:move.calendar_selector_overlay-collection="handleOverlayDrop"
                  class="fhc-calendar-base-grid-line"
                  style="
                    position: relative;
                    display: grid;
                    grid-auto-flow: dense;
                    grid-template-rows: subgrid;
                    grid-area: 1 / 3 / -1;
                  "
                ></div>
                <div
                  v-drop:move.calendar_selector_overlay-collection="handleOverlayDrop"
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
                  v-drop:move.calendar_selector_overlay-collection="handleOverlayDrop"
                  class="fhc-calendar-base-grid-line"
                  style="
                    position: relative;
                    display: grid;
                    grid-auto-flow: dense;
                    grid-template-rows: subgrid;
                    grid-area: 1 / 4 / -1;
                  "
                ></div>
                <div
                  v-drop:move.calendar_selector_overlay-collection="handleOverlayDrop"
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
                  v-drop:move.calendar_selector_overlay-collection="handleOverlayDrop"
                  class="fhc-calendar-base-grid-line"
                  style="
                    position: relative;
                    display: grid;
                    grid-auto-flow: dense;
                    grid-template-rows: subgrid;
                    grid-area: 1 / 5 / -1;
                  "
                ></div>
                <div
                  v-drop:move.calendar_selector_overlay-collection="handleOverlayDrop"
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
                  v-drop:move.calendar_selector_overlay-collection="handleOverlayDrop"
                  class="fhc-calendar-base-grid-line"
                  style="
                    position: relative;
                    display: grid;
                    grid-auto-flow: dense;
                    grid-template-rows: subgrid;
                    grid-area: 1 / 6 / -1;
                  "
                ></div>
                <div
                  v-drop:move.calendar_selector_overlay-collection="handleOverlayDrop"
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
                  v-drop:move.calendar_selector_overlay-collection="handleOverlayDrop"
                  class="fhc-calendar-base-grid-line"
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
    <div ref="overlaysContainer" id="overlaysContainer" class='d-none'>
      <div 
        v-for="(index) in 100"
        v-draggable:move.noImage="selectedDragObject" 
        @mousedown='overlaySelectionChanged($event, "overlay-item-" + index)'
        @mouseover="handleMouseOverOnOverlay('overlay-item-' + index)"
        :id="'overlay-item-' + index"
        :class="{
          'fhc-drag-handle': !$props.isPreviewMode,
        }"
        :style="{
          backgroundColor: this.overlays.find(overlay => overlay.id === 'overlay-item-' + index)?.backgroundColor || this.defaultOverlayColor,
        }"
        :title="getOverlayClassScheduleTypeTitle('overlay-item-' + index)"
        class="d-none fhc-pointer-events-all flex-column justify-content-between align-items-center shadow rounded-1"
        :draggable='!$props.isPreviewMode ? "true" : "false"'
      >
      <span
        v-if="!$props.isPreviewMode"
        @mousedown.stop="handleMouseDownOnUpperResize($event, 'overlay-item-resize-upper-' + index, 'overlay-item-' + index)"
        :id="'overlay-item-resize-upper-' + index"
        :class="{
              'position-absolute top-0 start-0': isOverlayMinimallySized(this.overlays.find(overlay => overlay.id === 'overlay-item-' + index)),
            }"
        class="d-flex justify-content-center p-1 fhc-resize-vertical fhc-w-fit"
      >
        <i class="fa-solid fa-grip-lines"></i>
      </span>
      <div 
        class="d-flex flex-column justify-content-center align-items-center gap-1 p-0 overflow-auto scrollable h-100"
        >
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
          @mousedown.stop="showOverlayClassTimeTypePopover('overlay-item-' + index)"
          :title="$props.isPreviewMode ? '' : $p.t('ui', 'bearbeiten')"
          :class="$props.isPreviewMode ? '' : 'fhc-cursor-pointer'"
          class="badge badge-pill bg-light text-dark"
        >
            {{ this.getOverlayClassScheduleTypeTitle('overlay-item-' + index) }}
        </span>
      </div>
      <span
        v-if="!$props.isPreviewMode"
        @mousedown.stop="handleMouseDownOnLowerResize($event, 'overlay-item-resize-lower-' + index, 'overlay-item-' + index)"
        :id="'overlay-item-resize-lower-' + index"
        :class="{
              'position-absolute bottom-0 start-0': isOverlayMinimallySized(this.overlays.find(overlay => overlay.id === 'overlay-item-' + index)),
            }"
        class="d-flex justify-content-center p-1 fhc-resize-vertical fhc-w-fit"
      >
        <i class="fa-solid fa-grip-lines"></i>
      </span>
    </div>
  </div>
  <div ref="classScheduleTypeSelectorContainer" class="d-none">
    <div class='d-flex flex-column gap-2'>
      <span v-for="(type, index) in $props.classTimeSlotTypes"
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
