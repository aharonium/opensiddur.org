// colresize.js
// Version: 2.0.0
// by Aharon Varady (for the Open Siddur Project)
//
// Gives every column in a .toggleable-table an initial width based on its
// natural (auto-layout) content width, then locks the table into
// table-layout: fixed with an explicit <colgroup> so that width becomes
// individually adjustable. Adds a drag handle on the right edge of each
// header cell for manual resizing.
//
// RESPONSIVENESS: each <col> carries its "preferred" width in
// data-preferred-width -- the width it would use if there were room:
// either the natural-content measurement (see below) or whatever the user
// dragged it to. The width actually rendered (col.style.width) is the
// preferred width scaled down, proportionally across every visible
// column, whenever the sum of preferred widths doesn't fit inside the
// table's container. This is done in JS (applyResponsiveWidths) rather
// than left to the browser, because table-layout:fixed's own behavior
// when a table's specified width exceeds its container is inconsistent
// across browsers -- some overflow instead of shrinking. Recomputed on
// load, on window resize (which also fires on browser zoom changes), on
// column visibility change, and during a manual resize drag.
//
// DEPENDS ON toolbar.js running first (needs th.dataset.colKey, which
// toolbar.js assigns) -- load this script AFTER toolbar.js in Body Bottom.
//
// IMPORTANT: the resize handle's positioning context is an inner <span>
// wrapper we insert inside each <th>, NOT the <th> itself. dragtable.js's
// own position math has a special case that assumes the header cell it's
// dragging is never itself position:relative, so setting that directly on
// the <th> breaks dragtable.js's drag-ghost placement. Don't "simplify"
// this by moving position:relative back onto the <th>.
//
// WIDTH MEASUREMENT: a column's initial preferred width is the widest
// single LINE among its header cell and every body cell -- not just the
// header -- measured with white-space temporarily forced to nowrap so an
// already-wrapped line doesn't under-report its true natural width. Then,
// if there's unused horizontal room next to the table at that moment, a
// small per-column buffer is added on top of that, to absorb (1) general
// wrap-safety margin, and (2) the width "Show Line Numbers" badges add to
// the start of each line, which isn't present in the DOM at
// initial-measurement time and would otherwise cause new wrapping the
// moment line numbers are toggled on.
//
// Plays along with the other scripts:
//   - dragtable.js: the resize handle stops the mousedown event from
//     bubbling to the <th>, so dragging the handle never triggers a
//     column-reorder drag. When dragtable.js DOES reorder a column (fires
//     "dragtable:columnMoved"), this script reorders the matching <col>
//     elements to match, so widths stay attached to the right column.
//   - toolbar.js: hiding a column via its checkbox only hides the cells,
//     not the <col>, so this script also listens for
//     "table:columnVisibilityChanged" (dispatched by toolbar.js) and
//     collapses/restores the matching <col> so no empty gap is left
//     behind in fixed-layout mode, then re-applies responsive widths.

var MIN_COLUMN_WIDTH = 40; // px
var MAX_COLUMN_BUFFER = 32; // px -- roughly enough for a "A12" line-number badge
var colresizeVersion = "2.0.0";

var trackedTables = []; // { table, colgroup } for every table this script manages

document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".toggleable-table").forEach((table) => {
    const headerCells = Array.from(table.querySelectorAll("thead th"));
    if (headerCells.length === 0) return;

    // Make sure we're measuring natural, content-driven widths, regardless
    // of any table-layout the stylesheet may already be forcing.
    table.style.tableLayout = "auto";

    const naturalWidths = measureNaturalColumnWidths(table, headerCells);
    const buffer = computeColumnBuffer(table, naturalWidths);

    const colgroup = document.createElement("colgroup");
    headerCells.forEach((th, i) => {
      const width = Math.max(naturalWidths[i] + buffer, MIN_COLUMN_WIDTH);
      const col = document.createElement("col");
      col.dataset.colKey = th.dataset.colKey || "";
      col.dataset.preferredWidth = String(width);
      colgroup.appendChild(col);

      // Wrap the header cell's existing content in a positioning context
      // for the resize handle, rather than positioning the <th> itself.
      const context = document.createElement("span");
      context.className = "col-resize-context";
      while (th.firstChild) context.appendChild(th.firstChild);
      th.appendChild(context);
      context.appendChild(buildResizeHandle(table, colgroup, th, col));
    });

    table.insertBefore(colgroup, table.firstChild);
    table.style.tableLayout = "fixed";
    table.style.maxWidth = "100%";

    table.addEventListener("dragtable:columnMoved", () => {
      syncColumnOrder(table, colgroup);
    });

    table.addEventListener("table:columnVisibilityChanged", (event) => {
      const { colKey, visible } = event.detail || {};
      const col = colgroup.querySelector('col[data-col-key="' + colKey + '"]');
      if (col) col.style.visibility = visible ? "" : "collapse";
      applyResponsiveWidths(table, colgroup);
    });

    trackedTables.push({ table, colgroup });
    applyResponsiveWidths(table, colgroup);
  });
});

var resizeDebounce = null;
window.addEventListener("resize", () => {
  clearTimeout(resizeDebounce);
  resizeDebounce = setTimeout(() => {
    trackedTables.forEach(({ table, colgroup }) => applyResponsiveWidths(table, colgroup));
  }, 100);
});

// Measure each column's true natural width: the widest single rendered
// line among the header cell and every body cell in that column. Forces
// white-space: nowrap during measurement so a line that's currently
// wrapped (e.g. due to a too-narrow ambient width) doesn't under-report
// its real, unwrapped width; restores each cell's original inline
// white-space afterward.
function measureNaturalColumnWidths(table, headerCells) {
  const columnCount = headerCells.length;
  const widths = new Array(columnCount).fill(0);
  const allCells = Array.from(table.querySelectorAll("th, td"));
  const originalWhiteSpace = allCells.map((cell) => cell.style.whiteSpace);

  allCells.forEach((cell) => {
    cell.style.whiteSpace = "nowrap";
  });

  headerCells.forEach((th, i) => {
    widths[i] = Math.max(widths[i], th.getBoundingClientRect().width);
  });

  const tbody = table.querySelector("tbody");
  if (tbody) {
    Array.from(tbody.rows).forEach((row) => {
      const rowCells = Array.from(row.children).filter((el) => el.tagName === "TD");
      rowCells.forEach((td, i) => {
        if (i < columnCount) {
          widths[i] = Math.max(widths[i], td.getBoundingClientRect().width);
        }
      });
    });
  }

  allCells.forEach((cell, i) => {
    cell.style.whiteSpace = originalWhiteSpace[i];
  });

  return widths;
}

// How much extra width can each column afford, given how much unused
// horizontal room the table's container currently has? Capped at
// MAX_COLUMN_BUFFER per column; zero if the table is already using all
// the room it has.
function computeColumnBuffer(table, naturalWidths) {
  const naturalTotal = naturalWidths.reduce((sum, w) => sum + w, 0);
  const containerWidth = getContainerWidth(table);
  const availableSlack = Math.max(0, containerWidth - naturalTotal);
  return Math.min(MAX_COLUMN_BUFFER, availableSlack / naturalWidths.length);
}

function getContainerWidth(table) {
  const container = table.parentNode;
  return container && container.clientWidth ? container.clientWidth : Infinity;
}

// The core of the responsive behavior: scale every visible column's
// preferred width down by the same factor, just enough that the total
// fits the container -- or use the preferred widths as-is if they already
// fit. Never scales up past 1 (a table narrower than its container isn't
// stretched to fill it).
function applyResponsiveWidths(table, colgroup) {
  const cols = Array.from(colgroup.querySelectorAll("col"));
  const visibleCols = cols.filter((col) => col.style.visibility !== "collapse");
  const preferredTotal = visibleCols.reduce(
    (sum, col) => sum + parseFloat(col.dataset.preferredWidth || 0),
    0
  );
  const containerWidth = getContainerWidth(table);
  const scale = preferredTotal > 0 ? Math.min(1, containerWidth / preferredTotal) : 1;

  visibleCols.forEach((col) => {
    const preferred = parseFloat(col.dataset.preferredWidth || 0);
    col.style.width = Math.max(preferred * scale, MIN_COLUMN_WIDTH * scale) + "px";
  });

  table.style.width = preferredTotal * scale + "px";
  table.dataset.colScale = String(scale);
}

function buildResizeHandle(table, colgroup, th, col) {
  const handle = document.createElement("span");
  handle.className = "col-resize-handle";

  handle.addEventListener("mousedown", (event) => {
    // Stop dragtable.js from seeing this as the start of a reorder drag.
    event.stopPropagation();
    event.preventDefault();

    const startX = event.clientX;
    const startPreferredWidth = parseFloat(col.dataset.preferredWidth) || 0;
    // Convert on-screen mouse movement into "preferred width" terms, so
    // dragging feels 1:1 with the cursor even while the table is
    // currently scaled down to fit a narrow viewport.
    const scale = parseFloat(table.dataset.colScale) || 1;
    const previousUserSelect = document.body.style.userSelect;
    document.body.style.userSelect = "none";
    table.classList.add("col-resizing");

    function onMouseMove(moveEvent) {
      const rawDelta = moveEvent.clientX - startX;
      const preferredDelta = scale > 0 ? rawDelta / scale : rawDelta;
      const newPreferredWidth = Math.max(MIN_COLUMN_WIDTH, startPreferredWidth + preferredDelta);
      col.dataset.preferredWidth = String(newPreferredWidth);
      applyResponsiveWidths(table, colgroup);
    }

    function onMouseUp() {
      document.removeEventListener("mousemove", onMouseMove);
      document.removeEventListener("mouseup", onMouseUp);
      document.body.style.userSelect = previousUserSelect;
      table.classList.remove("col-resizing");
    }

    document.addEventListener("mousemove", onMouseMove);
    document.addEventListener("mouseup", onMouseUp);
  });

  return handle;
}

// Reorder <col> elements to match the table's current left-to-right
// header order after a dragtable.js column move.
function syncColumnOrder(table, colgroup) {
  const currentOrder = Array.from(table.querySelectorAll("thead th")).map(
    (th) => th.dataset.colKey
  );
  currentOrder.forEach((key) => {
    const col = colgroup.querySelector('col[data-col-key="' + key + '"]');
    if (col) colgroup.appendChild(col);
  });
}