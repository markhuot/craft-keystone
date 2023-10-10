document.addEventListener('click', event => {
    if (typeof event.target.dataset.openKeystoneComponentEditor === 'undefined') {
        return;
    }

    event.preventDefault();
    event.stopPropagation();

    const field = event.target.closest('.field[data-type]');
    if (field.dataset.type !== 'markhuot\\keystone\\fields\\Keystone') {
        throw Error('oh no');
    }
    const handle = field.dataset.attribute;

    const params = JSON.parse(event.target.dataset.openKeystoneComponentEditor);
    const slideout = new Craft.CpScreenSlideout('keystone/components/edit', {params});

    slideout.on('submit', event => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'fields[' + handle + '][action]';
        input.value = JSON.stringify({
            name: 'edit-component',
            id: event.response.data.id,
            elementId: event.response.data.elementId,
            fields: event.response.data.fields,
        });
        Craft.cp.$primaryForm.get(0).appendChild(input);
        Craft.cp.$primaryForm.get(0).click();
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

    const field = event.target.closest('.field[data-type]');
    if (field.dataset.type !== 'markhuot\\keystone\\fields\\Keystone') {
        throw Error('oh no');
    }
    const handle = field.dataset.attribute;

    const params = JSON.parse(event.target.dataset.openKeystoneComponentSelector);
    const slideout = new Craft.CpScreenSlideout('keystone/components/add', {params});

    slideout.on('submit', event => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'fields[' + handle + '][action]';
        input.value = JSON.stringify({
            name: 'add-component',
            sortOrder: event.response.data.sortOrder,
            path: event.response.data.path,
            slot: event.response.data.slot,
            type: event.response.data.type,
        });
        Craft.cp.$primaryForm.get(0).appendChild(input);
        Craft.cp.$primaryForm.get(0).click();
    });

    slideout.on('close', () => {
        // ...
    });
});

const pointer = document.createElement('div');
pointer.id = 'pointer';
pointer.style.display = 'none';
pointer.style.position = 'fixed';
pointer.style.height = '5px';
pointer.style.width = '400px';
pointer.style.borderRadius = '5px';
pointer.style.background = 'linear-gradient(to right, rgba(31, 95, 234, 1), rgba(31, 95, 234, 0)';
pointer.style.transition = 'all 0.2s';
pointer.style.zIndex = 110;
document.body.appendChild(pointer);
let target = undefined;
document.addEventListener('mousedown', event => {
    target = event.target;
});

document.addEventListener('dragstart', event => {
    const handle = event.target.querySelector('[data-draggable-handle]');
    if (handle.contains(target) || target === handle) {
        event.target.dataset.dragging = true;
        event.target.querySelector('.foo').style.display = 'none';
        event.dataTransfer.setData('keystone/id', event.target.dataset.draggable);
        event.dataTransfer.setData('keystone/id/' + event.target.dataset.draggable, event.target.dataset.draggable);
    }
    else {
        event.preventDefault();
    }
});

const dropTarget = {};
document.addEventListener('dragover', event => {
    const el = event.target.closest('[data-dragtarget]');
    if (!el) {
        return;
    }

    if (event.dataTransfer.types.includes('keystone/id/' + el.dataset.dragtarget)) {
        pointer.style.display = 'none';
        return;
    }

    if (event.target.closest('[data-dragging]')) {
        pointer.style.display = 'none';
        return
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
    delete event.target.dataset.dragging;
    const field = event.target.closest('.field[data-type]');
    if (field.dataset.type !== 'markhuot\\keystone\\fields\\Keystone') {
        throw Error('oh no');
    }
    const handle = field.dataset.attribute;
    const source = event.target.dataset.draggable;
    const target = dropTarget.el.dataset.dragtarget;

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'fields[' + handle + '][action]';
    input.value = JSON.stringify({
        name: 'move-component',
        source,
        target,
        position: dropTarget.el.dataset.dragtargetPosition || dropTarget.position,
    });
    Craft.cp.$primaryForm.get(0).appendChild(input);
    Craft.cp.$primaryForm.get(0).click();

    pointer.style.display = 'none';
});
