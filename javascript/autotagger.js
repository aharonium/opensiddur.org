document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll("table.toggleable-table").forEach((table, tableIndex) => {
    const tbody = table.querySelector("tbody");

    // Debugging: Check if tbody exists
    // console.log(`Table ${tableIndex}: tbody`, tbody);

    if (tbody) {  // Only proceed if tbody is found
      const rows = tbody.querySelectorAll("tr");
      rows.forEach((row, rowIndex) => {
        const cells = row.querySelectorAll("td");
        const sourceCell = cells[0];
        const sourceLines = sourceCell.querySelectorAll("div, span, p, br");

        sourceLines.forEach((line, lineIndex) => {
          if (line.textContent.trim()) {
            const lineId = `line-${tableIndex}-${rowIndex}-${lineIndex}`;
            line.setAttribute("id", lineId);
          }
        });

        // Assign matching refs to each translation line (assuming one-to-one for now)
        if (cells.length > 1) {
          const transCell = cells[1];
          const transLines = transCell.querySelectorAll("div, span, p, br");

          transLines.forEach((line, lineIndex) => {
            if (line.textContent.trim()) {
              const refId = `line-${tableIndex}-${rowIndex}-${lineIndex}`;
              line.setAttribute("data-ref", refId);
            }
          });
        }
      });
    } else {
      console.warn(`Table ${tableIndex} is missing a <tbody> element`);
    }
  });
});

document.querySelectorAll(".toggleable-table").forEach((table) => {
  const headerCells = table.querySelectorAll("thead th");

  // Add the header labels above the table content
  const labelContainer = document.createElement('div');
  labelContainer.classList.add('column-labels');
  table.insertBefore(labelContainer, table.querySelector("tbody"));

  headerCells.forEach((headerCell, index) => {
    const headerText = headerCell.textContent.trim();
    const textAlign = headerCell.style.textAlign || window.getComputedStyle(headerCell).textAlign;

    // Check if this is a "Contribute a translation" header
    const link = headerCell.querySelector("a");
    const isTranslationLink = link && link.href.includes("/translate/");

    if (isTranslationLink) {
      // Add just the link, no label formatting or arrow
       // labelContainer.innerHTML += `
        // <div class="align-center">
          // (<a href="${link.href}" target="_blank" rel="noopener">${link.textContent}</a>)
        // </div><div class="align-center">&nbsp;</div>`;
    } else {
      // let label = `${String.fromCharCode(65 + index)}) ${headerText}`;  // Optional A, B, C
      let label = `${headerText}`;

      // Add arrows for left and right alignment
      if (textAlign === "left") {
        // label += ` ⟵ on the left`;
        label += ``;
      } else if (textAlign === "right") {
        // label += ` ⟶ on the right`;
        label += ``;
      }

      // Add a line break after each label, except the last one
      if (index < headerCells.length - 1) {
        label += "<br />";
      }

      // Append the label with line break to the label container at the top
      const alignClass = textAlign === "right" ? "align-right" : "align-left";
      labelContainer.innerHTML += `<div class="${alignClass}"><span>${label}</span></div>`;
    }
  });
});


// Toggle between table view and stacked view
document.addEventListener("DOMContentLoaded", function () {
  const tables = document.querySelectorAll(".toggleable-table");

  const globalToggle = document.getElementById("stack-toggle-all");

  if (globalToggle) {
    globalToggle.addEventListener("click", () => {
      document.querySelectorAll(".toggleable-table").forEach((table) => {
        table.classList.toggle("stack-view");
      });
    });
  }
});
