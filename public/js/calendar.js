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

function content(...nodes) {
    const root = document.getElementById('content');
    if (nodes.length) {
        root.replaceChildren(...nodes);
    }
    return root;
}

const Assignment = (assignment) => {
    return tooltip(
        create('div', ['assignment', assignment.major ? 'major' : undefined], `<span class="title">${assignment.name}</span>`),
        create('div', null, `
            <div class="title">${assignment.name}</div>
            <div class="description">
                <pre>${JSON.stringify(assignment, null, '  ')}</pre>
            </div>
        `)
    );
}

class Day {
    element;
    assignments;
    constructor(day) {
        this.element = create('td', 'day', `<div class="date">${day}</div>`);
        this.assignments = this.element.appendChild(create('div', 'assignments'));
    }
    add(assignment) {
        this.assignments.appendChild(assignment);
    }
}

class Month {
    element;
    days;

    constructor(year, m) {
        this.element = create('table', 'calendar');
        const days = new Date(year, m + 1, 0).getDate();
        this.days = [];
        const heading = create('thead', undefined, `
            <tr>
                <th colspan="7" class="month">${new Date(year, m, 1).toLocaleString('default', {month: 'long'})}</th>
            </tr>`
        );
        this.element.appendChild(heading);
        let week = document.createElement('tr');
        heading.appendChild(week);
        const padding = new Date(year, m, 1).getDay();
        for (let weekday = -1 * padding + 1; weekday < (-1 * padding + 1) + 7; weekday++) {
            const label = create('th', 'weekday', new Date(year, m, weekday, padding).toLocaleString('default', {weekday: "long"}));
            week.appendChild(label);
        }
        const grid = create('tbody');
        this.element.appendChild(grid);
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
            this.days[day] = new Day(day);
            week.appendChild(this.days[day].element);
        }
        for (let d = dayOfWeek; d < 6; d++) {
            week.appendChild(create('td', ['day', 'padding']));
        }
        grid.appendChild(week);
    }

    add(assignment, due) {
        this.days[due.getDate()].add(assignment);
    }
}

class Calendar {
    element;
    months;
    constructor(params) {
        this.element = create('div', 'calendars');
        this.months = [];
    }

    add(assignment) {
        const due = new Date(assignment.due_date);
        if (!this.months[due.getFullYear()]) {
            this.months[due.getFullYear()] = [];
        }
        if (!this.months[due.getFullYear()][due.getMonth()]) {
            this.months[due.getFullYear()][due.getMonth()] = new Month(due.getFullYear(), due.getMonth());
            this.element.appendChild(this.months[due.getFullYear()][due.getMonth()].element);
        }
        this.months[due.getFullYear()][due.getMonth()].add(Assignment(assignment), due);
    }
}

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

async function getData(calendar) {
    const today = new Date();
    let year = today.getFullYear();
    if (today.getMonth() <= 6) {
        year -= 1;
    }
    const data = {};
    const enrollments = await callSkyAPI({url: `school/v1/academics/enrollments/:user_id/?school_year=${year}-${year + 1}`});
    const promises = [];
    for (const enrollment of enrollments.value) {
        if (enrollment.id) {
            progress.max += 1;
            callSkyAPI({url: `school/v1/academics/sections/${enrollment.id}/assignments`})
                .then(assignments => {
                    for (const assignment of assignments.value) {
                        if (assignment.due_date) {
                            calendar.add(assignment);
                        }
                    }
                    progress.value += 1;
                })
        }
    }
    return data;
}

function toggle(selector) {
    let result = true;
    for (const elt of document.querySelectorAll(selector)) {
        elt.hidden = !elt.hidden;
        result = !elt.hidden;
    }
    return result;
}
const progress = create('progress', null, null, {max: 0, value: 0});
const calendar = new Calendar();
const majorAssignmentsHandler = function (event) {
    event.target.disabled = true;
    event.target.innerHTML =  toggle('.assignment:not(.major') ? 'Show Only Major Assignments' : 'Show All Assignments';
    event.target.disabled = false;
}
content(progress);
content().appendChild(calendar.element);
getData(calendar);
content().prepend(create('button', undefined, 'Show Only Major Assignments', {onclick: majorAssignmentsHandler.bind(null)}));
