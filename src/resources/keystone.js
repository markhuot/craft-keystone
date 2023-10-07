document.addEventListener('click', event => {
   if (typeof event.target.dataset.openKeystoneComponentEditor === 'undefined') {
       return;
   }

   event.preventDefault();
   event.stopPropagation();
   const id = event.target.dataset.keystoneComponentId;

    const slideout = new Craft.CpScreenSlideout('keystone/components/edit', {params: {id}});

    slideout.on('submit', ev => {
        Craft.cp.$primaryForm.append(Object.assign(document.createElement('input'), {name: 'fields[myKeystoneField]', value: new Date().getTime()}))
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
        // ev.data ...
    });

    slideout.on('close', () => {
        // ...
    });
});
