window.afp = {
    ready: function ready(fn) {
        if (document.readyState !== 'loading') {
            fn();
        } else {
            document.addEventListener('DOMContentLoaded', fn);
        }
    },
    addEventListener: function addEventListener(el, eventName, eventHandler, selector) {
        const wrappedHandler = (e) => {
            if (!e.target) return;
            const el = e.target.closest(selector);
            if (el) {
                eventHandler.call(el, e);
            }
        };
        el.addEventListener(eventName, wrappedHandler);
        return wrappedHandler;
    }
}

window.afp.ready(function () {
    window.afp.addEventListener(
        document,
        'submit',
        function (e) {
            // If we already have the element, we're done.
            if (e.target.querySelectorAll('input[name="as_dw_submission"]').length > 0) {
                return;
            }
            var newNode = document.createElement('input');
            newNode.setAttribute('type', 'hidden');
            newNode.setAttribute('name', 'as_dw_submission');
            newNode.setAttribute('value', 'asfp');
            e.target.append(newNode);
        },
        'form.afp-form'
    );
});
