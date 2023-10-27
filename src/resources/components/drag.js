const blueBackground = 'linear-gradient(to right, rgba(31, 95, 234, 1), rgba(31, 95, 234, 0)';
const redBackground = 'linear-gradient(to right, rgba(255, 0, 0, 1), rgba(255, 0, 0, 0)';
const pointer = document.createElement('div');
pointer.id = 'pointer';
pointer.style.display = 'none';
pointer.style.position = 'fixed';
pointer.style.height = '5px';
pointer.style.width = '300px';
pointer.style.borderRadius = '5px';
pointer.style.background = blueBackground;
pointer.style.transition = 'all 0.2s';
pointer.style.zIndex = 110;
pointer.style.pointerEvents = 'none';
pointer.innerHTML = '<span style="font-size: 8px; text-transform: uppercase; color: red; margin-left: 100px;"></span>';
const pointerMessage = pointer.querySelector('span');
document.body.appendChild(pointer);

document.addEventListener('dragstart', event => {
    const el = event.target.closest('[data-draggable]')
    const typeHandle = el.dataset.draggableType;
    event.target.dataset.dragging = true;
    event.dataTransfer.setData('keystone/id', el.dataset.draggable);
    event.dataTransfer.setData('keystone/id/' + el.dataset.draggable, el.dataset.draggable);
    event.dataTransfer.setData('k:'+typeHandle, '');
});

const dropTarget = {};
document.addEventListener('dragover', event => {
    let blocked = false;
    const field = event.target.closest('.field[data-type]');
    const sourceId = event.dataTransfer.types
        .find(type => type.substring(0, 12) === 'keystone/id/')
        .substring(12);
    const sourceEl = field.querySelector('[data-draggable="' + sourceId + '"]');
    const sourceTypeName = sourceEl.dataset.draggableTypeName;
    const targetEl = event.target.closest('[data-dragtarget]');
    if (!targetEl) {
        return;
    }

    if (event.dataTransfer.types.includes('keystone/id/' + targetEl.dataset.dragtarget)) {
        pointer.style.display = 'none';
        return;
    }

    if (event.target.closest('[data-dragging]')) {
        pointer.style.display = 'none';
        return;
    }

    const typeHandle = event.dataTransfer.types
        .find(type => type.substring(0, 2) === 'k:')
        .substring(2);

    const whitelist = targetEl.dataset.dragtargetWhitelist?.split(',').filter(Boolean);
    const blacklist = targetEl.dataset.dragtargetBlacklist?.split(',').filter(Boolean);
    if (whitelist?.length > 0) {
        if (! whitelist.includes(typeHandle)) {
            blocked = true;
        }
    }
    if (blacklist?.length > 0) {
        if (blacklist.includes(typeHandle)) {
            blocked = true;
        }
    }

    const row = targetEl.querySelector('[data-draggable-row]');
    const mouse = event.clientY;
    const { top, height, left } = row.getBoundingClientRect();
    const strictPosition = targetEl.dataset.dragtargetPosition;
    const calculatedPosition = (mouse < top + (height / 2)) ? 'before' : 'after';
    const position = strictPosition || calculatedPosition;

    pointer.style.display = 'block';
    if (position === 'before' || position === 'beforeend') {
        pointer.style.top = top + 'px';
    }
    else if (position === 'after') {
        pointer.style.top = top + height + 'px';
    }
    pointer.style.left = left + 'px';

    if (! blocked) {
        pointer.style.background = blueBackground;
        pointerMessage.innerHTML = '';
        dropTarget.position = position;
        event.preventDefault();
    }
    else {
        pointer.style.background = redBackground;
        pointerMessage.innerHTML = 'You can\'t place ' + sourceTypeName + ' here';
        return false;
    }
});

document.addEventListener('drop', async event => {
    delete event.target.dataset.dragging;
    const field = event.target.closest('.field[data-type]');
    const target = event.target.closest('[data-dragtarget]');
    const targetId = target.dataset.dragtarget;
    const sourceId = event.dataTransfer.getData('keystone/id');
    const fieldId = field.querySelector('[data-field-id]').dataset.fieldId;
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

    const position = target.dataset.dragtargetPosition || dropTarget.position;
    const slot = target.dataset.dragtargetSlot;

    const response = await Craft.postActionRequest('keystone/components/move', {
        source: {id: sourceId, elementId, fieldId},
        target: {id: targetId, elementId, fieldId},
        position,
        slot,
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
});

document.addEventListener('dragend', async event => {
    pointer.style.display = 'none';
});
