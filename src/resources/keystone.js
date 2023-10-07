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

let target = undefined;
document.addEventListener('mousedown', event => {
    target = event.target;
});

document.addEventListener('dragstart', event => {
    const handle = event.target.querySelector('[data-draggable-handle]');
    if (handle.contains(target) || target === handle) {

    }
    else {
        event.preventDefault();
    }
});
