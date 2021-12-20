function calendar(data) {
    const content = document.getElementById('content');
    content.innerHTML = '';
    for (let [year, ydata] of Object.entries(data)) {
        year = parseInt(year);
        for (let [month, mdata] of Object.entries(ydata)) {
            const m = parseInt(month) - 1;
            const days = new Date(year, m + 1, 0).getDate();
            const calendar = document.createElement('table');
            calendar.classList.add('calendar')
            content.appendChild(calendar);
            const heading = document.createElement('thead');
            heading.innerHTML = `
                <tr>
                    <th colspan="7" class="month">${new Date(year, m, 1).toLocaleString('default', {month: 'long'})}</th>
                </tr>`;
                calendar.appendChild(heading);
            let week = document.createElement('tr');
            heading.appendChild(week);
            const padding = new Date(year, m, 1).getDay();
            for (let weekday = -1 * padding + 1; weekday < (-1 * padding + 1) + 7; weekday++) {
                const label = document.createElement('th');
                label.classList.add('weekday');
                label.innerHTML = new Date(year, m, weekday, padding).toLocaleString('default', {weekday: "long"});
                week.appendChild(label);
            }
            grid = document.createElement('tbody');
            calendar.appendChild(grid);
            week = document.createElement('tr');
            week.classList.add('week');
            if (padding) {
                for (let pad = 0; pad < padding; pad++) {
                    const day = document.createElement('td');
                    day.classList.add('day','padding');
                    week.appendChild(day);
                }
            }
            for (let day = 1; day <= days; day++) {
                if (new Date(year, m, day).getDay() === 0) {
                    grid.appendChild(week);
                    week = document.createElement('tr');
                }
                cell = document.createElement('td');
                cell.classList.add('day');
                cell.innerHTML = `<div class="date">${day}</div>`;
                list = document.createElement('div');
                list.classList.add('assignments');
                cell.appendChild(list);
                week.appendChild(cell);
                if (data[`${year}`][`${month}`][`${day}`]) {
                    for (const assignment of data[year][month][`${day}`]) {
                        entry = document.createElement('div');
                        entry.classList.add('tooltip', 'assignment');
                        entry.innerHTML = `
                            <span class="assignment title">${assignment.name}</span>
                            <div class="tooltip text">
                                <div class="description">${assignment.description}</div>`;
                        list.appendChild(entry);
                        entry['data-assignment'] = assignment;
                    }
                }
            }
            grid.appendChild(week);
        }
    }
}