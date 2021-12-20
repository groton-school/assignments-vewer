async function callSkyAPI(body = undefined, options = {}) {
    options = {
        method: 'POST',
        ...options
    }
    
    if (body && typeof body === 'object') {
        body = {
            method: 'GET',
            options: [],
            ...body
        }
        data = new FormData();
        for(const prop in body) {
            data.append(prop, body[prop]);
        }
        options.body = data;
    }

    return await (await fetch('sky-api', options)).json();
}

async function getData() {
    const today = new Date();
    let year = today.getFullYear();
    if (today.getMonth() <= 6) {
        year -= 1;
    }
    const data = {};
    const enrollments = await callSkyAPI({url: `school/v1/academics/enrollments/:user_id/?school_year=${year}-${year + 1}`});
    for (const enrollment of enrollments.value) {
        if (enrollment.id) {
            assignments = await callSkyAPI({url: `school/v1/academics/sections/${enrollment.id}/assignments`});
            for (const assignment of assignments.value) {
                if (assignment.due_date) {
                    const due = new Date(assignment.due_date);
                    assignment.section = enrollment;
                    if (!data[due.getFullYear()]) data[due.getFullYear()] = {};
                    if (!data[due.getFullYear()][due.getMonth()]) data[due.getFullYear()][due.getMonth()] = {};
                    if (!data[due.getFullYear()][due.getMonth()][due.getDate()]) data[due.getFullYear()][due.getMonth()][due.getDate()] = [];
                    data[due.getFullYear()][due.getMonth()][due.getDate()].push(assignment);
                }
            }
        }
    }
    return data;
}

function create(tag, className = undefined, innerHTML = '', attributes = {}) {
    const elt = document.createElement(tag);
    if (Array.isArray(className)) {
        elt.classList.add(...className.filter(c => c && c.length));
    } else if (className) {
        elt.classList = className;
    }
    elt.innerHTML = innerHTML;
    for(const attr in attributes) {
        elt[attr] = attributes[attr];
    }
    return elt;
}

function content(...nodes) {
    const root = document.getElementById('content');
    if (nodes.length) {
        root.replaceChildren(...nodes);
    }
    return root;
}

function toggle(selector) {
    let result = true;
    for (const elt of document.querySelectorAll(selector)) {
        elt.hidden = !elt.hidden;
        result = !elt.hidden;
    }
    return result;
}

function loading() {
    content(create('div', ['loading', 'big'], '&#129427;'));
}

function tooltip (trigger, tooltip) {
    const hoverHandler = function(event) {
        const margin = 20;
        const tooltip = event.target.querySelector('.tooltip');
        const base = event.target.getBoundingClientRect();
        const popup = tooltip.getBoundingClientRect();
        const width = window.innerWidth || document.documentElement.clientWidth;
        const height = window.innerHeight || document.documentElement.clientHeight;
        const offset = {
            left: base.left + base.width / 2 + popup.width / -2,
            top: base.top + base.height / 2 + popup.height / -2,
            right: width - (base.right + base.width / -2 + popup.width / 2),
            bottom: height - (base.bottom + base.height / -2 + popup.height / 2)
        }
        const opposite = {left: 'right', right: 'left', top: 'bottom', bottom: 'top'}
        for (const side in offset) {
            if (offset[side] < margin) {
                const shift = margin - offset[side];
                offset[side] += shift;
                offset[opposite[side]] -= shift;
            }
        }
        for (const side in offset) {
            tooltip.style[side] = `${offset[side]}px`
        }
    }
    const elt = create ('span', 'tooltip', null, {onmouseenter: hoverHandler.bind(null)});
    elt.appendChild(trigger);
    const tt = create('span', ['tooltip', 'text']);
    elt.appendChild(tt);
    tt.appendChild(tooltip);
    return elt;
}

function calendar(data) {
    const calendars = create('div', 'calendars');
    for (let [year, ydata] of Object.entries(data)) {
        year = parseInt(year);
        for (let [month, mdata] of Object.entries(ydata)) {
            const m = parseInt(month);
            const days = new Date(year, m + 1, 0).getDate();
            const calendar = create('table', 'calendar')
            calendars.appendChild(calendar);
            const heading = create('thead', undefined, `
                <tr>
                    <th colspan="7" class="month">${new Date(year, m, 1).toLocaleString('default', {month: 'long'})}</th>
                </tr>`
            );
            calendar.appendChild(heading);
            let week = document.createElement('tr');
            heading.appendChild(week);
            const padding = new Date(year, m, 1).getDay();
            for (let weekday = -1 * padding + 1; weekday < (-1 * padding + 1) + 7; weekday++) {
                const label = create('th', 'weekday', new Date(year, m, weekday, padding).toLocaleString('default', {weekday: "long"}));
                week.appendChild(label);
            }
            grid = create('tbody');
            calendar.appendChild(grid);
            week = create('tr', 'week');
            if (padding) {
                for (let pad = 0; pad < padding; pad++) {
                    const day = create('td', ['day','padding']);
                    week.appendChild(day);
                }
            }
            let dayOfWeek;
            for (let day = 1; day <= days; day++) {
                dayOfWeek = new Date(year, m, day).getDay();
                if (dayOfWeek === 0) {
                    grid.appendChild(week);
                    week = create('tr', 'week');
                }
                cell = create('td', 'day', `<div class="date">${day}</div>`);
                list = create('div', 'assignments');
                cell.appendChild(list);
                week.appendChild(cell);
                if (data[year][month][day]) {
                    for (const assignment of data[year][month][day]) {
                        entry = tooltip(
                            create('div', ['assignment', assignment.major ? 'major' : undefined], `<span class="title">${assignment.name}</span>`),
                            create('div', null, `
                                <div class="title">${assignment.name}</div>
                                <div class="description">
                                    <pre>${JSON.stringify(assignment, null, '  ')}</pre>
                                </div>
                            `)
                        );
                        list.appendChild(entry);
                    }
                }
            }
            for (let d = dayOfWeek; d < 6; d++) {
                week.appendChild(create('td', ['day', 'padding']));
            }
            grid.appendChild(week);
        }
    }
    content(calendars);
    const majorAssignmentsHandler = function (event) {
        event.target.disabled = true;
        event.target.innerHTML =  toggle('.assignment:not(.major') ? 'Show Only Major Assignments' : 'Show All Assignments';
        event.target.disabled = false;
    }
    content().prepend(create('button', undefined, 'Show Only Major Assignments', {onclick: majorAssignmentsHandler.bind(null)}));
}

loading();
getData().then(data => calendar(data));
