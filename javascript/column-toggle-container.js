document.addEventListener("DOMContentLoaded", function () {
  const columnToggleContainer = document.getElementById("column-toggle-container");
  const tables = document.querySelectorAll(".toggleable-table");

  if (tables.length > 0) {
    const headerRow = tables[0].querySelector("thead tr");
    const headerCells = headerRow.querySelectorAll("th");

    headerCells.forEach((headerCell, columnIndex) => {
      const label = document.createElement("label");
      const checkbox = document.createElement("input");
      checkbox.type = "checkbox";
      checkbox.className = "column-toggle";
      checkbox.setAttribute("data-column-index", columnIndex);
      checkbox.checked = true; // Default checked

      label.appendChild(checkbox);
      label.appendChild(document.createTextNode(headerCell.textContent));
      columnToggleContainer.appendChild(label);
    });

    const toggleCheckboxes = document.querySelectorAll(".column-toggle");

    function updateColumnVisibility(columnVisibility) {
      tables.forEach((table) => {
        const headerRow = table.querySelector("thead tr");
        const headerCells = headerRow.querySelectorAll("th");

        headerCells.forEach((headerCell, columnIndex) => {
          const cells = table.querySelectorAll(`tr:not(:first-child) td:nth-child(${columnIndex + 1})`);

          // const isVisible = columnVisibility[columnIndex];
          // headerCell.style.display = isVisible ? "table-cell" : "none";
          if (isVisible) {
            headerCell.classList.remove("hidden-column");
            cell.classList.remove("hidden-column");
          } else {
            headerCell.classList.add("hidden-column");
            cell.classList.add("hidden-column");
          }

          cells.forEach((cell) => {
            cell.style.display = isVisible ? "table-cell" : "none";
          });
        });
      });
    }

    toggleCheckboxes.forEach((checkbox) => {
      checkbox.addEventListener("change", function () {
        const columnIndex = parseInt(checkbox.getAttribute("data-column-index"));
        const columnVisibility = Array.from(toggleCheckboxes, checkbox => checkbox.checked);
        updateColumnVisibility(columnVisibility);
      });
    });
  }
});