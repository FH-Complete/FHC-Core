export function tagFormatter(cell, tagComponent, onRendered) {
  const mappedData = tagComponent.tags.map((tag) => ({
    typ_kurzbz: tag.tag_typ_kurzbz,
    automatisiert: tag.automatisiert,
  }));

  let tags = cell.getValue();
  if (!tags) return;

  let container = document.createElement("div");
  container.className = "d-flex gap-1";

  let parsedTags = [];
  if (typeof tags === "string") {
    parsedTags = JSON.parse(tags);
  } else if (Array.isArray(tags)) {
    parsedTags = tags;
  }

  let maxVisibleTags = 3;

  const rowData = cell.getRow().getData();
  if (rowData._tagExpanded === undefined) {
    rowData._tagExpanded = false;
  }

  const renderTags = () => {
    container.innerHTML = "";
    parsedTags = parsedTags.filter((item) => item !== null);

    parsedTags.sort((a, b) => {
      let adone = a.done ? 1 : 0;
      let bbone = b.done ? 1 : 0;

      if (adone !== bbone) {
        return adone - bbone;
      }
      return a.prioritaet - b.prioritaet;
    });
    const tagsToShow = rowData._tagExpanded
      ? parsedTags
      : parsedTags.slice(0, maxVisibleTags);

    tagsToShow.forEach((tag) => {
      if (!tag) return;
      let tagElement = document.createElement("span");
      tagElement.innerText = tag.beschreibung;
      tagElement.title = tag.notiz;
      tagElement.className = "tag " + tag.style;
      if (tag.done) tagElement.className += " tag_done";

      const tagDef = mappedData.find((t) => t.typ_kurzbz === tag.typ_kurzbz);

      if (
        (!tagDef && tag.typ_kurzbz?.includes("_auto")) ||
        tagDef?.automatisiert
      ) {
        tagElement.className += " tag_auto";
        tagElement.innerHTML =
          "<i class='fa-solid fa-lock'></i> " + tag.beschreibung;
      }

      tagElement.addEventListener("click", (event) => {
        event.stopPropagation();
        event.preventDefault();
        tagComponent.editTag(tag.id);
      });

      container.appendChild(tagElement);
    });

    if (parsedTags.length > maxVisibleTags) {
      let toggle = document.createElement("button");
      toggle.innerText =
        (rowData._tagExpanded ? "- " : "+ ") +
        (parsedTags.length - maxVisibleTags);
      toggle.className = "display_all";
      toggle.title = rowData._tagExpanded
        ? "Tags ausblenden"
        : "Tags einblenden";

      toggle.addEventListener("click", () => {
        rowData._tagExpanded = !rowData._tagExpanded;
        renderTags();
      });

      container.appendChild(toggle);
    }
  };

  const fitTags = () => {
    if (rowData._tagExpanded) {
      renderTags();
      return;
    }

    let widthBuffer = 10;
    maxVisibleTags = parsedTags.length;
    renderTags();

    while (
      maxVisibleTags > 0 &&
      container.scrollWidth > (container.clientWidth + widthBuffer)
    ) {
      maxVisibleTags--;
      renderTags();
    }
  };

  let animationFrame = null;
  container.fitTags = () => {
    if (animationFrame !== null) return;

    animationFrame = requestAnimationFrame(() => {
      animationFrame = null;
      fitTags();
    });
  };

  if (onRendered) {
    onRendered(() => {
      container.fitTags();
    });
  }

  renderTags();
  return container;
}
