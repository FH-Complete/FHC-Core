let activeTagDropdownCleanup = null;
const TAG_FILTER_CONNECTORS = {
  AND: {
    key: "AND",
    label: "AND",
    operator: "&&",
  },
  OR: {
    key: "OR",
    label: "OR",
    operator: "||",
  },
  NOT: {
    key: "NOT",
    label: "NOT",
    operator: "!",
  },
};
const TAG_FILTER_CONNECTOR_ORDER = [
  TAG_FILTER_CONNECTORS.AND,
  TAG_FILTER_CONNECTORS.OR,
  TAG_FILTER_CONNECTORS.NOT,
];

function getConnectorLabel(connector, labels) {
  return labels?.connectors?.[connector.key] || connector.label;
}

export function setTagHeaderFilterValue(tabulator, value) {
  tabulator?.setHeaderFilterValue("tags", value);

  const input = tabulator
    ?.getColumn("tags")
    ?.getElement()
    ?.querySelector(".tabulator-header-filter input");

  if (input) {
    input.value = value ?? "";
  }
}

export function buildTagHeaderFilterExpression(selectedTagOptions) {
  let expression = null;

  selectedTagOptions.forEach(({ value, connector }) => {
    const connectorDef = TAG_FILTER_CONNECTORS[connector];
    if (!connectorDef) return;

    if (connectorDef.key === TAG_FILTER_CONNECTORS.NOT.key) {
      expression = expression
        ? `${expression} ${TAG_FILTER_CONNECTORS.AND.operator} ${connectorDef.operator}${value}`
        : `${connectorDef.operator}${value}`;
    } else {
      expression = expression
        ? `${expression} ${connectorDef.operator} ${value}`
        : value;
    }
  });

  return expression;
}

export function buildTagOptionsFromRows(rows = []) {
  const options = new Map();

  rows.forEach((row) => {
    let tags = row.tags;

    if (typeof tags === "string") {
      try {
        tags = JSON.parse(tags);
      } catch {
        return;
      }
    }

    if (!Array.isArray(tags)) return;

    tags
      .filter((tag) => tag && tag.done !== true)
      .forEach((tag) => {
        options.set(tag.beschreibung, {
          label: tag.beschreibung,
          value: tag.beschreibung,
          style: tag.style,
        });
      });
  });

  return [...options.values()];
}

export function customTagFilter(cell, onRendered, success, cancel, params) {
  const container = document.createElement("div");
  container.style.width = "100%";

  const input = document.createElement("input");
  input.style.width = "100%";
  input.type = "text";
  let dropdownMenu = null;

  Object.defineProperty(container, "value", {
    get: () => input.value,
    set: (value) => {
      input.value = value ?? "";
    },
  });

  const positionDropdown = () => {
    if (!dropdownMenu) return;

    const rect = input.getBoundingClientRect();
    const width = Math.min(Math.max(rect.width, 360), window.innerWidth - 16);
    const left = Math.min(
      Math.max(rect.left, 8),
      window.innerWidth - width - 8,
    );

    dropdownMenu.style.position = "fixed";
    dropdownMenu.style.left = `${left}px`;
    dropdownMenu.style.top = `${rect.bottom + 2}px`;
    dropdownMenu.style.width = `${width}px`;
    dropdownMenu.style.maxHeight = "50vh";
    dropdownMenu.style.overflowY = "auto";
    dropdownMenu.style.zIndex = "2000";
  };

  const closeDropdown = () => {
    if (dropdownMenu) {
      dropdownMenu.remove();
      dropdownMenu = null;
    }

    if (activeTagDropdownCleanup === closeDropdown) {
      activeTagDropdownCleanup = null;
    }

    window.removeEventListener("scroll", positionDropdown, true);
    window.removeEventListener("resize", positionDropdown);
    document.removeEventListener("mousedown", handleOutsideClick, true);
  };

  const handleOutsideClick = (event) => {
    if (
      dropdownMenu?.contains(event.target) ||
      container.contains(event.target)
    ) {
      return;
    }

    closeDropdown();
  };

  const clearFilter = ({ closeDropdownAfterClear = true } = {}) => {
    input.value = "";

    if (Array.isArray(params.selectedOptions)) {
      params.selectedOptions.splice(
        0,
        params.selectedOptions.length,
      );
    }

    success("");

    if (closeDropdownAfterClear) {
      closeDropdown();
    }
  };

  const openDropdown = () => {
    if (activeTagDropdownCleanup) {
      activeTagDropdownCleanup();
    }

    dropdownMenu = getRebuiltDropdown(
      params.initialOptions,
      params.selectedOptions,
      params.labels,
      clearFilter,
    );
    dropdownMenu.addEventListener("mousedown", (event) => {
      event.stopPropagation();
    });

    document.body.appendChild(dropdownMenu);
    activeTagDropdownCleanup = closeDropdown;

    positionDropdown();

    window.addEventListener("scroll", positionDropdown, true);
    window.addEventListener("resize", positionDropdown);
    document.addEventListener("mousedown", handleOutsideClick, true);
  };

  input.addEventListener("input", () => {
    success(input.value);
  });
  input.addEventListener("change", () => {
    success(input.value);
  });
  input.addEventListener("keydown", (event) => {
    if (event.key !== "Escape") return;

    event.preventDefault();
    event.stopPropagation();

    clearFilter({closeDropdownAfterClear: false});

    openDropdown();
  });
  input.addEventListener("focus", (e) => {
    e.stopPropagation();

    openDropdown();
  });

  container.appendChild(input);

  return container;
}

function getRebuiltDropdown(
  initialOptions,
  selectedOptions,
  labels,
  clearFilter,
) {
  const dropdownTable = generateDropdownTable(
    initialOptions || [],
    selectedOptions || [],
    labels,
  );

  const menu = document.createElement("div");
  menu.className = "dropdown-menu show py-3 px-1";

  const clearButton = document.createElement("button");
  clearButton.type = "button";
  clearButton.className =
    "btn btn-link btn-sm p-0 position-absolute top-0 end-0 m-1";
  clearButton.title = labels?.clear || "Clear";
  clearButton.setAttribute("aria-label", labels?.clear || "Clear");

  const clearIcon = document.createElement("i");
  clearIcon.className = "fa-solid fa-filter-circle-xmark";
  clearButton.appendChild(clearIcon);

  clearButton.addEventListener("click", (event) => {
    event.preventDefault();
    event.stopPropagation();
    clearFilter({ closeDropdownAfterClear: false });
    menu.querySelectorAll("input[type='checkbox']").forEach((checkbox) => {
      checkbox.checked = false;
    });
  });

  menu.appendChild(clearButton);
  menu.appendChild(dropdownTable);

  return menu;
}

function generateDropdownTable(options, selectedTagOptions, labels) {
  const table = document.createElement("table");
  table.className = "table table-sm mb-0";

  // HEADER
  const thead = document.createElement("thead");
  const headRow = document.createElement("tr");

  const headers = [
    labels?.tag || "Tag",
    ...TAG_FILTER_CONNECTOR_ORDER.map((connector) =>
      getConnectorLabel(connector, labels),
    ),
  ];

  headers.forEach((h, index) => {
    const th = document.createElement("th");
    if (index > 0) {
      th.className = "text-center";
    }

    const strong = document.createElement("strong");
    strong.textContent = h;
    th.appendChild(strong);
    headRow.appendChild(th);
  });

  thead.appendChild(headRow);
  table.appendChild(thead);

  // BODY
  const tbody = document.createElement("tbody");
  options.forEach((option) => {
    const row = document.createElement("tr");

    // LABEL
    const labelTd = document.createElement("td");
    const label = document.createElement("span");
    label.className = "tag " + option.style;
    label.innerText = option.label;
    labelTd.appendChild(label);
    row.appendChild(labelTd);

    function makeCheckbox(connector) {
      const td = document.createElement("td");
      const cb = document.createElement("input");

      td.className = "text-center align-middle";

      cb.type = "checkbox";
      cb.style.cursor = "pointer";

      cb.checked = selectedTagOptions.some(
        (t) => t.value === option.value && t.connector === connector.key,
      );

      cb.addEventListener("click", (event) => {
        event.stopPropagation();

        if (event.target.checked) {
          selectedTagOptions.push({
            label: option.label,
            value: option.value,
            connector: connector.key,
          });
        } else {
          const idx = selectedTagOptions.findIndex(
            (t) => t.value === option.value && t.connector === connector.key,
          );

          if (idx !== -1) {
            selectedTagOptions.splice(idx, 1);
          }
        }

      });

      td.appendChild(cb);
      return td;
    }

    TAG_FILTER_CONNECTOR_ORDER.forEach((connector) => {
      row.appendChild(makeCheckbox(connector));
    });

    tbody.appendChild(row);
  });

  table.appendChild(tbody);

  return table;
}

function parseFilterExpression(expression) {
  const collections = [];

  try {
    const orParts = expression.split("||").map((part) => part.trim());

    orParts.forEach((part) => {
      const andParts = part.split("&&").map((p) => p.trim());

      const collection = { positives: [], negatives: [] };

      andParts.forEach((term) => {
        const comparisonMatch = term.match(
          /^(<=|>=|<|>|=|!=)\s*(-?\d+(?:[.,]\d+)?)$/,
        );

        if (comparisonMatch) {
          const operator = comparisonMatch[1];
          const numberStr = comparisonMatch[2].replace(",", ".");
          const number = parseFloat(numberStr);
          collection.positives.push({ type: "comparison", operator, number });
        } else if (term.startsWith("!")) {
          const excludeTerm = term.substring(1).trim().replace(/\*/g, ".*");
          collection.negatives.push({
            type: "regex",
            regex: new RegExp(excludeTerm, "i"),
          });
        } else {
          const includeTerm = term.replace(/\*/g, ".*");
          collection.positives.push({
            type: "regex",
            regex: new RegExp(includeTerm, "i"),
          });
        }
      });
      collections.push(collection);
    });
  } catch (e) {}
  return collections;
}

export function extendedHeaderFilter(
  headerValue,
  rowValue,
  rowData,
  filterParams,
) {
  const fields = Array.isArray(filterParams?.field)
    ? filterParams.field
    : [filterParams?.field];

  if (fields.length > 1 && rowData) {
    rowValue = fields
      .map((f) => rowData[f] ?? "")
      .filter(Boolean)
      .join(" ");
  }
  if (typeof headerValue === "boolean") {
    return rowValue === headerValue;
  }

  const collections = parseFilterExpression(headerValue);

  function matchValue(value) {
    try {
      return collections.some((collection) => {
        let positives =
          collection.positives.length === 0 ||
          collection.positives.every((condition) => {
            if (condition.type === "comparison") {
              let value = parseFloat(rowValue);
              if (isNaN(value)) return false;

              switch (condition.operator) {
                case "<":
                  return value < condition.number;
                case ">":
                  return value > condition.number;
                case "<=":
                  return value <= condition.number;
                case ">=":
                  return value >= condition.number;
                case "=":
                  return value === condition.number;
                case "!=":
                  return value !== condition.number;
                default:
                  return false;
              }
            } else if (condition.type === "regex") {
              return condition.regex.test(rowValue);
            }
            return false;
          });

        let negatives = collection.negatives.every((condition) => {
          return !condition.regex.test(rowValue);
        });

        return positives && negatives;
      });
    } catch (e) {}
  }

  if (matchValue(rowValue)) return true;

  if (rowData && filterParams) {
    const childrenField = filterParams?.children || "_children";
    const field = filterParams?.field;

    const children = rowData[childrenField];
    if (Array.isArray(children)) {
      for (let child of children) {
        let childValue = child[field];
        if (
          extendedHeaderFilter(headerValue, childValue, child, filterParams)
        ) {
          return true;
        }
      }
    }
  }

  return false;
}
export function tagHeaderFilter(headerValue, rowValue, rowData, filterParams) {
  let data;

  try {
    data = typeof rowValue === "string" ? JSON.parse(rowValue) : rowValue;
  } catch (error) {
    return false;
  }

  let combinedText;

  if (Array.isArray(data)) {
    combinedText = data
      .filter((item) => item?.done !== true)
      .map((item) => `${item?.beschreibung} ${item?.notiz}`)
      .join(" ");
  } else if (typeof data === "object" && data !== null) {
    combinedText =
      data?.erledigt === false ? `${data?.beschreibung} ${data?.notiz}` : "";
  } else {
    combinedText = String(data);
  }

  return extendedHeaderFilter(headerValue, combinedText, rowData, filterParams);
}
