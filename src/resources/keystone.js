document.addEventListener('click', event => {
    if (typeof event.target.dataset.openKeystoneComponentEditor === 'undefined') {
        return;
    }

    event.preventDefault();
    event.stopPropagation();
    const id = event.target.dataset.keystoneComponentId;

    const slideout = new Craft.CpScreenSlideout('keystone/components/edit', {params: {id}});

    slideout.on('submit', ev => {
        //Craft.cp.$primaryForm.append(Object.assign(document.createElement('input'), {name: 'fields[myKeystoneField]', value: new Date().getTime()}))
        Craft.cp.$primaryForm.get(0).querySelector('[data-attribute="myKeystoneField"] .input').innerHTML = ev.response.data.fieldHtml
    });

    slideout.on('close', () => {
        // ...
    });
});

document.addEventListener('click', event => {
    if (typeof event.target.dataset.openKeystoneComponentSelector === 'undefined') {
        return;
    }

    event.preventDefault();
    event.stopPropagation();

    const params = JSON.parse(event.target.dataset.openKeystoneComponentSelector);
    const slideout = new Craft.CpScreenSlideout('keystone/components/add', {params});

    slideout.on('submit', ev => {
        Craft.cp.$primaryForm.get(0).querySelector('[data-attribute="myKeystoneField"] .input').innerHTML = ev.response.data.fieldHtml
    });

    slideout.on('close', () => {
        // ...
    });
});

const pointer = document.createElement('div');
pointer.id = 'pointer';
pointer.style.display = 'none';
pointer.style.position = 'absolute';
pointer.style.height = '5px';
pointer.style.width = '400px';
pointer.style.borderRadius = '5px';
pointer.style.background = 'linear-gradient(to right, rgba(31, 95, 234, 1), rgba(31, 95, 234, 0)';
pointer.style.transition = 'all 0.2s';
document.body.appendChild(pointer);
let target = undefined;
document.addEventListener('mousedown', event => {
    target = event.target;
});

document.addEventListener('dragstart', event => {
    const handle = event.target.querySelector('[data-draggable-handle]');
    if (handle.contains(target) || target === handle) {
        event.target.querySelector('.foo').style.display = 'none';
    }
    else {
        event.preventDefault();
    }
});

const dropTarget = {};
document.addEventListener('dragover', event => {
    const el = event.target.closest('[data-draggable]');
    if (!el) {
        return;
    }

    const row = el.querySelector('[data-draggable-row]');
    const mouse = event.pageY;
    const { top, height, left } = row.getBoundingClientRect();
    const position = (mouse < top + (height / 2)) ? 'above' : 'below';

    pointer.style.display = 'block';
    if (position === 'above') {
        pointer.style.top = top + 'px';
    }
    if (position === 'below') {
        pointer.style.top = top + height + 'px';
    }
    pointer.style.left = left + 'px';

    dropTarget.el = el;
    dropTarget.position = position;

    event.preventDefault();
});

document.addEventListener('dragend', async event => {
    const response = await Craft.postActionRequest('keystone/components/move', {
        source: event.target.dataset.draggable,
        target: dropTarget.el.dataset.draggable,
        position: dropTarget.position,
    });
    Craft.cp.$primaryForm.get(0).querySelector('[data-attribute="myKeystoneField"] .input').innerHTML = response.fieldHtml
    Craft.cp.displaySuccess(response.message);
    pointer.style.display = 'none';
});
