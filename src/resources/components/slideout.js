window.slideout = function (action, params={}) {
    const thens = [];

    async function doSlideout(event) {
        event.preventDefault();

        let form = event.target.closest('form');
        let editor = $.data(form, 'elementEditor');

        // There might not be an editor if we're in live preview so we need to look around
        // in the DOM for the real editor behind the scenes.
        if (! editor && form.classList.contains('lp-editor')) {
            editor = $.data(document.getElementById('main-form'), 'elementEditor');
        }

        await editor.ensureIsDraftOrRevision();

        params.elementId = editor.settings.elementId;
        const slideout = new Craft.CpScreenSlideout(action, {params});

        slideout.on('submit', event => {
            for (const then of thens) {
                then(event.response, form);
            }
        });
    }

    doSlideout.then = function (callback) {
        thens.push(callback);

        return doSlideout;
    }

    doSlideout.swap = function (selector) {
        thens.push((response, form) => {
            const fragment = document.createElement('template');
            fragment.innerHTML = response.data.fieldHtml;
            form.querySelector(selector).replaceWith(fragment.content.querySelector(selector));
        });

        return doSlideout;
    }

    return doSlideout;
}
