(function() {
    const basePath = document.currentScript.src.replace(/\/[^\/]+$/, '/');
    const width = window.innerWidth || document.documentElement.clientWidth;

    const script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = basePath + (width >= 768 ? 'heatmap-multi.js' : 'heatmap-multi.js');
    script.onload = function () {
        if (typeof renderHeatmap === 'function') {
            renderHeatmap(); // trigger once script is loaded
        } else {
            console.warn('renderHeatmap is not defined');
        }
    };
    document.head.appendChild(script);
})();
