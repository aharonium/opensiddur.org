// toolbar.js
// Version: 1.4.0
// by Aharon Varady (for the Open Siddur Project)
//
// Builds the per-table toolbar: one visibility checkbox per column, a
// "Show Line Numbers" toggle, and a "Toggle View" button that switches
// between table and stacked layout. A table always keeps at least one
// column visible -- the checkbox for the last remaining visible column is
// disabled until another column's visibility is restored.
//
// Also builds each table's stacked-view support (formerly in
// autotagger.js, merged in here since it's really the same feature as the
// "Toggle View" button above -- everything to do with stacked view now
// lives in one place):
//   - A ".column-labels" block inserted above each table's <tbody>, with
//     one label per header column. In table view this sits above the
//     table unnoticed; in stacked view (once cells drop out of their
//     columns and no longer sit next to a visible header) it's the one
//     place on the page that still says what column A, B, C... meant.
//   - A page-wide "toggle every table into stacked view at once" button,
//     if the page has an element with id="stack-toggle-all" -- separate
//     from each table's own individual "Toggle View" button.
//
// IMPORTANT: column identity is tracked via a stable data-col-key attribute
// (derived from the header text), not by the column's position in the DOM.
// dragtable.js physically moves <td>/<th> nodes when a user drags a header,
// which changes their index but NOT their data-col-key -- so a checkbox
// keeps controlling the same logical column no matter where the user has
// dragged it. (Previously this used a numeric index captured once at page
// load, which went stale the moment a column was dragged.)

var toolbarVersion = "1.4.0";

document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".toggleable-table").forEach((table) => {
    const headerCells = Array.from(table.querySelectorAll("thead th"));
    if (headerCells.length === 0) return;

    const tbody = table.querySelector("tbody");
    if (!tbody) {
      console.warn("Toggleable table missing tbody:", table);
      return;
    }

    assignColumnKeys(table, headerCells);
    const toolbar = buildToolbar(table, headerCells);
    table.parentNode.insertBefore(toolbar, table);
    buildColumnLabels(table, headerCells, tbody);

    // If dragtable.js reorders columns, re-sync the toolbar's checkbox order
    // so it visually matches the table's new left-to-right column order.
    // (Purely cosmetic -- toggling already works correctly without this,
    // since it's keyed off data-col-key rather than position.)
    table.addEventListener("dragtable:columnMoved", () => {
      syncToolbarOrder(table, toolbar);
    });
  });

  setupGlobalStackToggle();
});

// Give every header cell a stable, human-readable key (e.g. "source-hebrew"),
// and stamp that same key onto every cell in that column, across every row
// (thead, tbody, tfoot). Duplicate/blank headers get a numeric suffix.
function assignColumnKeys(table, headerCells) {
  const usedKeys = new Set();

  headerCells.forEach((th, i) => {
    const base = slugify(th.textContent.trim());
    let key = base;
    let n = 2;
    while (usedKeys.has(key)) {
      key = base + "-" + n++;
    }
    usedKeys.add(key);
    th.dataset.colKey = key;

    table.querySelectorAll("tr").forEach((row) => {
      const cell = row.children[i];
      if (cell) cell.dataset.colKey = key;
    });
  });
}

function slugify(text) {
  const base = text
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "");
  return base || "col";
}

function buildToolbar(table, headerCells) {
  const toolbar = document.createElement("div");
  toolbar.className = "table-toolbar";
  const checkboxes = [];

  headerCells.forEach((th) => {
    const key = th.dataset.colKey;
    const label = th.textContent.trim();

    const checkbox = document.createElement("input");
    checkbox.type = "checkbox";
    checkbox.checked = true;
    checkbox.dataset.colKey = key;
    checkboxes.push(checkbox);

    const span = document.createElement("span");
    span.dataset.colKey = key;
    span.appendChild(checkbox);
    span.append(" " + label);
    toolbar.appendChild(span);

    checkbox.addEventListener("change", () => {
      table.querySelectorAll('[data-col-key="' + key + '"]').forEach((cell) => {
        cell.style.display = checkbox.checked ? "" : "none";
      });
      // Let colresize.js (if loaded) know, so it can collapse/restore the
      // matching <col> and avoid leaving an empty-width gap behind.
      table.dispatchEvent(
        new CustomEvent("table:columnVisibilityChanged", {
          detail: { colKey: key, visible: checkbox.checked },
        })
      );
      updateLastVisibleGuard(checkboxes);
    });
  });

  updateLastVisibleGuard(checkboxes);

  const toggleButton = document.createElement("button");
  toggleButton.textContent = "Toggle View";
  toggleButton.className = "stack-toggle";
  toggleButton.addEventListener("click", () => {
    table.classList.toggle("stack-view");
  });
  toolbar.appendChild(toggleButton);

  // Line numbers are off by default (see linenumbers.js); this just flips
  // a class, so it works regardless of whether linenumbers.js has run yet.
  const lineNumberButton = document.createElement("button");
  lineNumberButton.textContent = "Show Line Numbers";
  lineNumberButton.className = "line-number-toggle";
  lineNumberButton.addEventListener("click", () => {
    table.classList.toggle("show-line-numbers");
  });
  toolbar.appendChild(lineNumberButton);

  return toolbar;
}

// A table must always have at least one visible column. If exactly one
// checkbox is currently checked, disable it so it can't be unchecked --
// the person has to restore another column's visibility first. Every
// other checkbox stays (or becomes) enabled.
function updateLastVisibleGuard(checkboxes) {
  const checkedCount = checkboxes.filter((cb) => cb.checked).length;
  checkboxes.forEach((cb) => {
    const mustStayVisible = checkedCount === 1 && cb.checked;
    cb.disabled = mustStayVisible;
    cb.title = mustStayVisible ? "At least one column must stay visible" : "";
  });
}

// Reorder the toolbar's <span> checkboxes to match the table's current
// left-to-right header order, without touching checkbox state.
function syncToolbarOrder(table, toolbar) {
  const currentOrder = Array.from(table.querySelectorAll("thead th")).map(
    (th) => th.dataset.colKey
  );
  const toggleButton = toolbar.querySelector(".stack-toggle");

  currentOrder.forEach((key) => {
    const span = toolbar.querySelector('span[data-col-key="' + key + '"]');
    if (span) toolbar.insertBefore(span, toggleButton);
  });
}

// Build the ".column-labels" block above a table's <tbody>, used by
// stacked view (see the header comment above). One label per header
// column, aligned left/right to match that header's own text alignment.
// (Migrated from autotagger.js, unchanged.)
function buildColumnLabels(table, headerCells, tbody) {
  const labelContainer = document.createElement("div");
  labelContainer.classList.add("column-labels");
  table.insertBefore(labelContainer, tbody);

  headerCells.forEach((headerCell, index) => {
    const headerText = headerCell.textContent.trim();
    const textAlign =
      headerCell.style.textAlign || window.getComputedStyle(headerCell).textAlign;

    // Check if this is a "Contribute a translation" header
    const link = headerCell.querySelector("a");
    const isTranslationLink = link && link.href.includes("/translate/");

    if (isTranslationLink) {
      // Intentionally left blank: no label for this column.
    } else {
      let label = `${headerText}`;

      // Add a line break after each label, except the last one
      if (index < headerCells.length - 1) {
        label += "<br />";
      }

      const alignClass = textAlign === "right" ? "align-right" : "align-left";
      labelContainer.innerHTML += `<div class="${alignClass}"><span>${label}</span></div>`;
    }
  });
}

// Wire up a page-wide "toggle every table into stacked view" button, if
// the page has an element with id="stack-toggle-all".
// (Migrated from autotagger.js, unchanged.)
function setupGlobalStackToggle() {
  const globalToggle = document.getElementById("stack-toggle-all");
  if (!globalToggle) return;

  globalToggle.addEventListener("click", () => {
    document.querySelectorAll(".toggleable-table").forEach((table) => {
      table.classList.toggle("stack-view");
    });
  });
}