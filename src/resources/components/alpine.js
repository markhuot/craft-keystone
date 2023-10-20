window.post = function (action, config={}) {
    const thens = [];

    async function doPost(event) {
        const el = event.target;
        const form = el && el.closest('form');
        const data = form ? new FormData(form) : null;
        for (const [k,v] of Object.entries(config)) {
            data.append(k, v);
        }
        const headers = {
            'X-CSRF-Token': Craft.csrfTokenValue,
            'X-Craft-Namespace': form ? $(form).data('cpScreen').namespace : null,
        };
        const response = await axios({
            method: 'post',
            url: Craft.getActionUrl(action),
            headers,
            data
        });

        for (const then of thens) {
            then(response);
        }
    }

    doPost.swap = function (selector) {
        thens.push(response => {
            const el = document.querySelector(selector);
            if (el && response.data.html) {
                const isHidden = el.classList.contains('hidden');
                el.outerHTML = response.data.html;
                if (! isHidden) {
                    document.querySelector(selector).classList.remove('hidden');
                }
            }

            Craft.appendHeadHtml(response.data.headHtml);
            Craft.appendBodyHtml(response.data.bodyHtml);
            Craft.initUiElements(el);
        });

        return doPost;
    }

    return doPost;
}

// window.post = function (action, config={}) {
//     return {
//         swap: function () {
//             function post(event) {
//                 const el = event.target;
//                 const form = el && el.closest('form');
//                 const data = form ? new FormData(form) : null;
//                 for (const [k,v] of Object.entries(config)) {
//                     data.append(k, v);
//                 }
//                 const headers = {
//                     'X-CSRF-Token': Craft.csrfTokenValue,
//                     'X-Craft-Namespace': form ? $(form).data('cpScreen').namespace : null,
//                 };
//                 const request = axios({
//                     method: 'post',
//                     url: Craft.getActionUrl(action),
//                     headers,
//                     data
//                 });
//             }
//
//             return post;
//         }
//     }
// }
