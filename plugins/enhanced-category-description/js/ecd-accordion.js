function initializeAccordions(scope = document) {
    var accordions = scope.querySelectorAll('.accordion');
    accordions.forEach(function (accordion) {
        accordion.addEventListener('click', function () {
            console.log('Accordion clicked'); // Add this
            this.classList.toggle('active');
            var panel = this.nextElementSibling;
            if (panel.style.display === 'block') {
                panel.style.display = 'none';
            } else {
                panel.style.display = 'block';
            }
        });
    });
}

// Automatically initialize accordions on page load
document.addEventListener('DOMContentLoaded', function () {
    initializeAccordions();
});
