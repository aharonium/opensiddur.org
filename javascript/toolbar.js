document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".toggleable-table").forEach((table, index) => {
    const toolbar = document.createElement("div");
    toolbar.className = "table-toolbar";
    
    // Checkboxes based on table headers
    const headers = table.querySelectorAll("thead th");
    headers.forEach((th, i) => {
      const label = th.textContent.trim();
      const checkbox = document.createElement("input");
      checkbox.type = "checkbox";
      checkbox.checked = true;
      checkbox.dataset.col = i;

      const span = document.createElement("span");
      span.appendChild(checkbox);
      span.append(" " + label);
      
      toolbar.appendChild(span);

      // Hide/show logic
      checkbox.addEventListener("change", () => {
        table.querySelectorAll("tr").forEach(row => {
          const cell = row.children[i];
          if (cell) {
            cell.style.display = checkbox.checked ? "" : "none";
          }
        });
      });
    });

    // Toggle View Button
    const toggleButton = document.createElement("button");
    toggleButton.textContent = "Toggle View";
    toggleButton.className = "stack-toggle";
    toggleButton.addEventListener("click", () => {
      table.classList.toggle("stack-view");
    });
    toolbar.appendChild(toggleButton);

    table.parentNode.insertBefore(toolbar, table);
  });
});
