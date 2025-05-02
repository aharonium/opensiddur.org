window.renderHeatmap = function () {
    const container = document.getElementById('contributor-activity-heatmap');
    if (!container) return;

/*    
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('contributor-activity-heatmap');
*/

    const jsonUrl = container.dataset.jsonUrl;
    const loadingEl = container.querySelector('.heatmap-loading');
    const yearLinkEl = document.getElementById('heatmap-year-link');
    const versionEl = document.getElementById('heatmap-version');
    let postData = {};
    let years = [];
    let currentYear = new Date().getUTCFullYear();

    function fetchData() {
        fetch(jsonUrl)
            .then(response => response.json())
            .then(data => {
                postData = data;
                years = [...new Set(Object.values(postData).map(p => new Date(p.post_date).getUTCFullYear()))].sort();
                if (!years.includes(currentYear)) currentYear = years[years.length - 1];
                renderHeatmap(currentYear);
                loadingEl.style.display = 'none';
            });
    }

    function renderHeatmap(year) {
        container.innerHTML = '';

        const svgNS = 'http://www.w3.org/2000/svg';
        const yearData = Object.values(postData).filter(p => {
            const [yyyy] = p.post_date.split('-').map(Number);
            return yyyy === year;
        });
        const dayCounts = {};
        const monthCounts = new Array(12).fill(0);
        let totalYearCount = 0;

        yearData.forEach(p => {
            let [yyyy, mm, dd] = p.post_date.split('-').map(Number);
            const dateObj = new Date(Date.UTC(yyyy, mm - 1, dd));
            const dateStr = `${yyyy}-${String(mm).padStart(2, '0')}-${String(dd).padStart(2, '0')}`;
            const month = dateObj.getUTCMonth();

            dayCounts[dateStr] = (dayCounts[dateStr] || 0) + 1;
            monthCounts[month]++;
            totalYearCount++;
        });

        const startDate = new Date(Date.UTC(year, 0, 1));
        const startDay = startDate.getUTCDay();
        const adjustedStartDate = new Date(Date.UTC(year, 0, 1 - startDay));
        const endDate = new Date(Date.UTC(year, 11, 31));
        const endDay = endDate.getUTCDay();
        const adjustedEndDate = new Date(Date.UTC(year, 11, 31 + (6 - endDay)));

        const svg = document.createElementNS(svgNS, 'svg');
        svg.classList.add('heatmap-svg');

        const tooltip = document.createElement('div');
        tooltip.className = 'heatmap-tooltip';
        document.body.appendChild(tooltip);

        const colorScale = [0, 1, 3, 5, 8];
        const getColor = count => {
            if (count >= colorScale[4] + 1) return '#196127';
            if (count >= colorScale[3] + 1) return '#239a3b';
            if (count >= colorScale[2] + 1) return '#7bc96f';
            if (count >= colorScale[1] + 1) return '#c6e48b';
            if (count >= 1) return '#c6e48b';
            return 'transparent';
        };

        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const daySize = 12;
        const leftOffset = 40;
        const rowGap = 40;
        const topOffset = 40;

        const weeksInRow = 18;
        const daysInWeek = 7;
        let dayIndex = 0;

        const todayStr = new Date().toISOString().slice(0, 10);

        const style = document.createElement('style');
        style.textContent = `
            @keyframes pulse-border {
                0% { stroke: black; }
                50% { stroke: white; }
                100% { stroke: black; }
            }
            .today-cell {
                stroke-width: 2;
                animation: pulse-border 2s infinite;
            }
        `;
        document.head.appendChild(style);

        for (let d = new Date(adjustedStartDate); d <= adjustedEndDate; d.setUTCDate(d.getUTCDate() + 1)) {
            const day = d.getUTCDay();
            const weekOfYear = Math.floor(dayIndex / 7);
            const row = Math.floor(weekOfYear / weeksInRow);
            const col = weekOfYear % weeksInRow;
            const y = topOffset + row * (daySize * daysInWeek + rowGap) + day * (daySize + 2);
            const x = leftOffset + col * (daySize + 2);

            const dateStr = d.toISOString().slice(0, 10);
            const count = dayCounts[dateStr] || 0;

            const rect = document.createElementNS(svgNS, 'rect');
            rect.setAttribute('x', x);
            rect.setAttribute('y', y);
            rect.setAttribute('width', daySize);
            rect.setAttribute('height', daySize);
            rect.setAttribute('fill', getColor(count));
            rect.setAttribute('data-date', dateStr);
            rect.setAttribute('data-count', count);
            rect.classList.add('day-cell');

            if (dateStr === todayStr) {
                rect.classList.add('today-cell');
            } else if (count === 0) {
                rect.setAttribute('stroke', '#666');
                rect.setAttribute('stroke-width', '0.20');
            }

            rect.addEventListener('mouseenter', e => {
                const { date, count } = e.target.dataset;
                tooltip.innerHTML = `${count} resource${count == 1 ? '' : 's'} on ${date}`;
                tooltip.style.display = 'block';
                tooltip.style.left = `${e.pageX + 10}px`;
                tooltip.style.top = `${e.pageY - 20}px`;
            });

            rect.addEventListener('mouseleave', () => {
                tooltip.style.display = 'none';
            });

            svg.appendChild(rect);
            dayIndex++;
        }

        for (let row = 0; row < 3; row++) {
            const startMonth = row * 4;
            const y = topOffset + row * (daySize * daysInWeek + rowGap) - 8;
            for (let i = 0; i < 4; i++) {
                const monthIndex = startMonth + i;
                const labelX = leftOffset + (i * (weeksInRow / 4) * (daySize + 2));

                const text = document.createElementNS(svgNS, 'text');
                text.setAttribute('x', labelX);
                text.setAttribute('y', y);
                text.textContent = months[monthIndex];
                text.setAttribute('class', 'month-label');

                const now = new Date();
                const isFutureMonth = (year > now.getUTCFullYear()) || (year === now.getUTCFullYear() && monthIndex > now.getUTCMonth());

                if (!isFutureMonth) {
                    text.style.cursor = 'pointer';
                    text.setAttribute('fill', '#0074D9');
                    const postInMonth = yearData.find(p => new Date(p.post_date).getUTCMonth() === monthIndex);
                    const actualMonthYear = postInMonth ? new Date(postInMonth.post_date).getUTCFullYear() : year;
                    const monthPath = `https://opensiddur.org/${actualMonthYear}/${String(monthIndex + 1).padStart(2, '0')}/`;

                    text.addEventListener('mouseenter', e => {
                        tooltip.innerHTML = `${monthCounts[monthIndex]} resource${monthCounts[monthIndex] == 1 ? '' : 's'} in ${months[monthIndex]} ${year}`;
                        tooltip.style.display = 'block';
                        tooltip.style.left = `${e.pageX + 10}px`;
                        tooltip.style.top = `${e.pageY - 20}px`;
                    });

                    text.addEventListener('mouseleave', () => {
                        tooltip.style.display = 'none';
                    });

                    text.addEventListener('click', () => {
                        window.location.href = monthPath;
                    });
                } else {
                    text.setAttribute('fill', '#ccc');
                    text.style.cursor = 'default';
                }

                svg.appendChild(text);
            }
        }

        yearLinkEl.innerHTML = `<a href="https://opensiddur.org/${year}/">${year}</a>`;

        const svgWidth = leftOffset + weeksInRow * (daySize + 2) + 20;
        const svgHeight = topOffset + 3 * (daySize * daysInWeek + rowGap);
        svg.setAttribute('width', svgWidth);
        svg.setAttribute('height', svgHeight);

        const leftArrowX = 20;
        const rightArrowX = svgWidth - 30;
        const yearTextX = svgWidth / 2;

        const navGroup = document.createElementNS(svgNS, 'g');
        navGroup.setAttribute('class', 'heatmap-nav');
        svg.appendChild(navGroup);

        const yearLink = document.createElementNS(svgNS, 'a');
        yearLink.setAttributeNS('http://www.w3.org/1999/xlink', 'xlink:href', `https://opensiddur.org/${year}/`);
        yearLink.setAttribute('target', '_blank');

        const yearText = document.createElementNS(svgNS, 'text');
        yearText.setAttribute('x', yearTextX);
        yearText.setAttribute('y', svgHeight - 5);
        yearText.setAttribute('text-anchor', 'middle');
        yearText.setAttribute('font-size', '14');
        yearText.setAttribute('font-weight', 'bold');
        yearText.setAttribute('fill', '#333');
        yearText.textContent = `${year} (${totalYearCount})`;

        yearLink.appendChild(yearText);
        navGroup.appendChild(yearLink);

        const leftArrow = document.createElementNS(svgNS, 'text');
        leftArrow.setAttribute('x', leftArrowX);
        leftArrow.setAttribute('y', svgHeight - 5);
        leftArrow.setAttribute('font-size', '16');
        leftArrow.setAttribute('fill', '#333');
        leftArrow.setAttribute('font-weight', 'bold');
        leftArrow.setAttribute('cursor', 'pointer');
        leftArrow.textContent = '←';
        leftArrow.addEventListener('click', () => {
            const prevIndex = years.indexOf(currentYear) - 1;
            if (prevIndex >= 0) {
                currentYear = years[prevIndex];
                renderHeatmap(currentYear);
            }
        });
        navGroup.appendChild(leftArrow);

        const rightArrow = document.createElementNS(svgNS, 'text');
        rightArrow.setAttribute('x', rightArrowX);
        rightArrow.setAttribute('y', svgHeight - 5);
        rightArrow.setAttribute('font-size', '16');
        rightArrow.setAttribute('fill', '#333');
        rightArrow.setAttribute('font-weight', 'bold');
        rightArrow.setAttribute('cursor', 'pointer');
        rightArrow.textContent = '→';
        rightArrow.addEventListener('click', () => {
            const nextIndex = years.indexOf(currentYear) + 1;
            if (nextIndex < years.length) {
                currentYear = years[nextIndex];
                renderHeatmap(currentYear);
            }
        });
        navGroup.appendChild(rightArrow);

        container.appendChild(svg);
    }

    fetchData();
// });
};
