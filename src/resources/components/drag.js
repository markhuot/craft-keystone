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
    const mouse = event.clientY;
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
    const source = event.target.closest('[data-draggable]')
    const field = event.target.closest('.field[data-type]');
    const fieldId = field.querySelector('[data-field-id]').dataset.fieldId;
    const fieldHandle = field.dataset.attribute;
    const layoutElementUid = field.dataset.layoutElement;
    if (field.dataset.type !== 'markhuot\\keystone\\fields\\Keystone') {
        throw Error('oh no');
    }

    let form = field.closest('form');
    let editor = $.data(form, 'elementEditor');

    // There might not be an editor if we're in live preview so we need to look around
    // in the DOM for the real editor behind the scenes.
    if (! editor && form.classList.contains('lp-editor')) {
        form = document.getElementById('main-form');
        editor = $.data(form, 'elementEditor');
    }

    await editor.ensureIsDraftOrRevision();
    const elementId = editor.settings.elementId;

    const sourceId = source.dataset.draggable;
    const targetId = dropTarget.el.dataset.dragtarget;
    const position = dropTarget.el.dataset.dragtargetPosition || dropTarget.position;

    const response = await Craft.postActionRequest('keystone/components/move', {
        source: {id: sourceId, elementId, fieldId},
        target: {id: targetId, elementId, fieldId},
        position,
    });

    if (response.message) {
        Craft.cp.displaySuccess(response.message);
    }

    if (response.fieldHtml) {
        const template = document.createElement('div');
        template.innerHTML = response.fieldHtml;

        document.querySelectorAll(`[data-layout-element="${layoutElementUid}"]`).forEach(el => {
            el.innerHTML = template.firstElementChild.innerHTML;
        })
        form.click();
    }

    pointer.style.display = 'none';
});
