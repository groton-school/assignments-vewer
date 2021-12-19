function calendar(data) {
    const content = document.getElementById('content');
    content.innerHTML = '';
    for (const [year, ydata] of Object.entries(data)) {
        for (const [month, mdata] of Object.entries(ydata)) {
            const days = new Date(year, month+1, 0).getDate();
            console.log(year, month, new Date(year, month, 0));
            console.log(year, month, new Date(year, month, 1));
            const calendar = document.createElement('table');
            content.appendChild(calendar);
            calendar.innerHTML = `
                <thead>
                    <tr>
                        <th colspan="7">${new Date(year, month, 1).toLocaleString('default', {month: 'long'})}</th>
                    </tr>
                </thead>`;
            grid = document.createElement('tbody');
            calendar.appendChild(grid);
            week = document.createElement('tr');
            padding = new Date(year, month, 1).getDay();
            if (padding) {
                for (let pad = 0; pad < padding; pad++) {
                    week.appendChild(document.createElement('td'));
                }
            }
            for (let day = 1; day <= days; day++) {
                if (new Date(year, month, day).getDay() === 0) {
                    grid.appendChild(week);
                    week = document.createElement('tr');
                }
                cell = document.createElement('td');
                cell.innerHTML = `<p class="date">${day}</p>`;
                list = document.createElement('div');
                cell.appendChild(list);
                week.appendChild(cell);
                if (data[year][month][`${day}`]) {
                    for (const assignment of data[year][month][`${day}`]) {
                        entry = document.createElement('div');
                        entry.classList.add('tooltip', 'assignment');
                        entry.innerHTML = `
                            <span class="assignment title">${assignment.name}</span>
                            <div class="tooltip text">
                                <div class="description">${assignment.description}</div>`;
                        list.appendChild(entry);
                    }
                }
            }
            grid.appendChild(week);
        }
    }
}