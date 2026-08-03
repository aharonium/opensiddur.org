// linenumbers.js
// Version: 2.1.0
//
// Assigns a line label to each phrase/line inside every column of a
// .toggleable-table, numbered per row as a letter + number (row A's lines
// are A1, A2, A3...; row B's lines are B1, B2, B3...), gives each line a
// stable-for-this-pageload anchor id, and supports linking to (and
// highlighting) a single line or a range of lines -- including ranges that
// span multiple rows -- via the URL hash, e.g.:
//
//   #t0-A3      -> table 0 (first .toggleable-table on the page), row A, line 3
//   #t0-A1-4    -> table 0, row A, lines 1 through 4
//   #t0-A2-B1   -> table 0, from row A line 2 through row B line 1
//
// Only the FIRST line of each row displays its row letter (e.g. "A1");
// the rest of that row's lines just show their number ("2", "3", "4"...).
// The underlying anchor id always includes the letter, so linking still
// works correctly for any line, not just the first in its row.
//
// Line-number visibility is OFF by default. Toolbar.js adds a
// "Show Line Numbers" button per table that toggles the "show-line-numbers"
// class on the <table>; custom-styles.css controls visibility from there.
//
// NOTE ON STABILITY: table numbers (t0, t1, ...) are based on a table's
// position among .toggleable-table elements on the page, and row/line
// labels are based on position within the table. All of this is
// recomputed fresh on every page load. That means an anchor link is
// stable as long as nobody adds/removes a whole table above this one, or
// adds/removes a row or line above this one within the same table. If you
// need permanently stable anchors regardless of future edits, that
// requires a manual, editor-assigned id -- not something this
// auto-detection can guarantee.
//
// LINE-SPLITTING RULES (a "line" inside one table cell is detected as):
//   1. If the cell contains 2+ sibling <p>/<div> elements, each one IS a
//      line (this is what a plain Enter produces in the classic editor).
//   2. Otherwise, if the cell (or its single wrapping <div>/<p>) contains
//      <br> tags, each <br>-separated segment is a line (Shift+Enter).
//   3. Inline formatting elements (span, em, strong, a, sup, sub, etc.)
//      are NEVER treated as line boundaries -- they stay part of whichever
//      line they're inside.

// Exposed so the loaded version can be checked from DevTools:
// document.querySelector('script[src*="linenumbers"]') to find the file,
// or just type `linenumbersVersion` in the Console on a page that has it.
var linenumbersVersion = "2.1.0";

document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".toggleable-table").forEach((table, tableIndex) => {
    const tbody = table.querySelector("tbody");
    if (!tbody) return;

    const tableSlug = "t" + tableIndex;
    let order = 0;

    Array.from(tbody.rows).forEach((row, rowIndex) => {
      const cells = Array.from(row.children).filter((el) => el.tagName === "TD");
      if (cells.length === 0) return;

      // Work out every cell's lines *before* assigning numbers, so every
      // column in this row agrees on how many lines it has.
      const perCellLines = cells.map((cell) => linesForCell(cell));
      const maxLines = Math.max(0, ...perCellLines.map((lines) => lines.length));
      const rowLetter = toRowLetter(rowIndex);

      for (let lineIdx = 0; lineIdx < maxLines; lineIdx++) {
        const lineNumber = lineIdx + 1;
        const lineOrder = order++;
        const lineId = tableSlug + "-" + rowLetter + lineNumber;
        // Only the first line of a row shows its row letter (e.g. "A1");
        // subsequent lines in the same row just show "2", "3", "4"...
        const displayLabel = lineIdx === 0 ? rowLetter + lineNumber : String(lineNumber);
        let idAssigned = false;

        perCellLines.forEach((lines) => {
          const descriptor = lines[lineIdx];
          if (!descriptor) return;
          markLine(
            descriptor,
            tableSlug,
            rowLetter,
            lineNumber,
            lineOrder,
            lineId,
            displayLabel,
            !idAssigned
          );
          idAssigned = true;
        });
      }
    });
  });

  applyHighlightFromHash();
});

window.addEventListener("hashchange", applyHighlightFromHash);

// Convert a 0-based row index into a spreadsheet-style letter label:
// 0 -> A, 1 -> B, ... 25 -> Z, 26 -> AA, 27 -> AB, ...
function toRowLetter(index) {
  let n = index;
  let label = "";
  do {
    label = String.fromCharCode(65 + (n % 26)) + label;
    n = Math.floor(n / 26) - 1;
  } while (n >= 0);
  return label;
}

// ---- line detection --------------------------------------------------

const BLOCK_LINE_TAGS = new Set(["P", "DIV"]);

function linesForCell(cell) {
  let root = cell;
  let blocks = directBlockChildren(root);

  // If this element's ONLY meaningful content is a single wrapping
  // <div>/<p>, descend into it and look there instead (handles
  // <td><div class="liturgy">...). Whitespace-only text nodes (e.g. the
  // newline/indentation between <td> and <div> in typical WordPress
  // output) are ignored when deciding "only content" -- otherwise this
  // check would never trigger on real-world markup.
  while (blocks.length === 1 && meaningfulChildNodes(root).length === 1) {
    root = blocks[0];
    blocks = directBlockChildren(root);
  }

  if (blocks.length >= 2) {
    return blocks
      .map((el) => ({ mode: "block", el }))
      .filter((d) => d.el.textContent.trim());
  }

  return splitByBr(root);
}

function meaningfulChildNodes(el) {
  return Array.from(el.childNodes).filter(
    (n) => !(n.nodeType === 3 && !n.textContent.trim())
  );
}

function directBlockChildren(el) {
  return Array.from(el.children).filter((c) => BLOCK_LINE_TAGS.has(c.tagName));
}

function splitByBr(root) {
  const lines = [];
  const brs = [];
  let current = [];

  Array.from(root.childNodes).forEach((node) => {
    if (node.nodeType === 1 && node.tagName === "BR") {
      lines.push(current);
      brs.push(node);
      current = [];
    } else {
      current.push(node);
    }
  });
  lines.push(current);

  const isBlank = (nodes) => nodes.every((n) => !n.textContent || !n.textContent.trim());

  const result = lines
    .map((nodes) => ({ mode: "br-segment", nodes }))
    .filter((d) => !isBlank(d.nodes));

  // The <br> tags were only boundaries, not content -- remove them now that
  // we've read the segments they defined, so wrapping doesn't leave stray
  // line breaks behind.
  brs.forEach((br) => br.parentNode && br.parentNode.removeChild(br));

  return result;
}

// ---- marking / wrapping ------------------------------------------------

function markLine(
  descriptor,
  tableSlug,
  rowLetter,
  lineNumber,
  lineOrder,
  lineId,
  displayLabel,
  isCanonical
) {
  let wrapper;

  if (descriptor.mode === "block") {
    // Already a block element (one line per <p>/<div>) -- annotate in place.
    wrapper = descriptor.el;
  } else {
    // Synthesize a wrapper around this <br>-delimited segment.
    const nodes = descriptor.nodes;
    if (!nodes.length || !nodes[0].parentNode) return;
    wrapper = document.createElement("span");
    wrapper.className = "tagged-line-inline";
    nodes[0].parentNode.insertBefore(wrapper, nodes[0]);
    nodes.forEach((n) => wrapper.appendChild(n));
  }

  wrapper.classList.add("tagged-line");
  wrapper.dataset.table = tableSlug;
  wrapper.dataset.row = rowLetter;
  wrapper.dataset.lineNumber = String(lineNumber);
  wrapper.dataset.order = String(lineOrder);
  if (isCanonical) wrapper.id = lineId;

  const badge = document.createElement("a");
  badge.className = "line-number-badge";
  badge.href = "#" + lineId;
  badge.textContent = displayLabel;
  wrapper.insertBefore(badge, wrapper.firstChild);
}

// ---- linking / highlighting ---------------------------------------------

function applyHighlightFromHash() {
  document.querySelectorAll(".tagged-line.line-highlight").forEach((el) => {
    el.classList.remove("line-highlight");
  });

  // #t0-A3        -> table 0, row A, line 3
  // #t0-A1-4      -> table 0, row A, lines 1 through 4 (end assumed same row)
  // #t0-A2-B1     -> table 0, from row A line 2 through row B line 1
  const match = /^#?(t\d+)-([A-Za-z]+)(\d+)(?:-([A-Za-z]*)(\d+))?$/.exec(
    window.location.hash
  );
  if (!match) return;

  const tableSlug = match[1];
  const startRow = match[2].toUpperCase();
  const startNum = parseInt(match[3], 10);
  const hasEnd = match[5] !== undefined;
  const endRow = hasEnd ? (match[4] ? match[4].toUpperCase() : startRow) : startRow;
  const endNum = hasEnd ? parseInt(match[5], 10) : startNum;

  const startOrder = orderOf(tableSlug, startRow, startNum);
  const endOrder = orderOf(tableSlug, endRow, endNum);
  if (startOrder === null || endOrder === null) return;

  const lo = Math.min(startOrder, endOrder);
  const hi = Math.max(startOrder, endOrder);

  let firstEl = null;
  document
    .querySelectorAll('.tagged-line[data-table="' + tableSlug + '"]')
    .forEach((el) => {
      const n = parseInt(el.dataset.order, 10);
      if (n >= lo && n <= hi) {
        el.classList.add("line-highlight");
        if (!firstEl) firstEl = el;
      }
    });

  if (firstEl) {
    firstEl.scrollIntoView({ behavior: "smooth", block: "center" });
  }
}

// Look up the sequential order value for a given (table, row, lineNumber).
// Returns null if no such line exists.
function orderOf(tableSlug, rowLetter, lineNumber) {
  const el = document.querySelector(
    '.tagged-line[data-table="' +
      tableSlug +
      '"][data-row="' +
      rowLetter +
      '"][data-line-number="' +
      lineNumber +
      '"]'
  );
  return el ? parseInt(el.dataset.order, 10) : null;
}