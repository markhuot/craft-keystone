document.addEventListener('click', async event => {
    const anchor = event.target.closest && event.target.closest('button[data-open-keystone-component-selector]')
    if (!anchor) {
        return;
    }

    event.preventDefault();
    event.stopPropagation();

    const field = anchor.closest('.field[data-type]');
    const layoutElementUid = field.dataset.layoutElement;
    if (field.dataset.type !== 'markhuot\\keystone\\fields\\Keystone') {
        throw Error('oh no');
    }
    const handle = field.dataset.attribute;

    let form = field.closest('form');
    let editor = $.data(form, 'elementEditor');

    // There might not be an editor if we're in live preview so we need to look around
    // in the DOM for the real editor behind the scenes.
    if (! editor && form.classList.contains('lp-editor')) {
        form = document.getElementById('main-form');
        editor = $.data(form, 'elementEditor');
    }

    await editor.ensureIsDraftOrRevision();

    const params = JSON.parse(anchor.dataset.openKeystoneComponentSelector);
    params.elementId = editor.settings.elementId;
    const slideout = new Craft.CpScreenSlideout('keystone/components/add', {params});

    slideout.on('submit', event => {
        const input = form.querySelector('.keystone-pulse') || document.createElement('input');
        input.setAttribute('class', 'keystone-pulse');
        input.setAttribute('type', 'hidden');
        input.setAttribute('name', 'keystone[pulse]');
        input.setAttribute('value', new Date().getTime());
        form.appendChild(input);

        if (event.response.data.fieldHtml) {
            const template = document.createElement('div');
            template.innerHTML = event.response.data.fieldHtml;

            document.querySelectorAll(`[data-layout-element="${layoutElementUid}"]`).forEach(el => {
                el.innerHTML = template.firstElementChild.innerHTML;
            })
            form.click();
        }

        form.click();
    });

    slideout.on('close', () => {
        // ...
    });
});

document.addEventListener('dblclick', event => {
    const label = event.target.closest && event.target.closest('[data-keystone-select-type]');
    if (! label) {
        return;
    }

    const form = label.closest('form');
    $(form).data('cpScreen').submit();
});
