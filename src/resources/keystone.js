document.addEventListener('click', event => {
   if (typeof event.target.dataset.openKeystoneComponentEditor === 'undefined') {
       return;
   }

   event.preventDefault();
   event.stopPropagation();
   const id = event.target.dataset.keystoneComponentId;

    const slideout = new Craft.CpScreenSlideout('keystone/components/edit', {params: {id}});

    slideout.on('submit', ev => {
        // ev.data ...
    });

    slideout.on('close', () => {
        // ...
    });
});
