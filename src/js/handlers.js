/**
         * attach handler for browser 'back'- and 'next'-button
         */
window.onpopstate = function (event) {
    if (event.state) {
        state = event.state;
    }
    render(state);
};


document.DOMContentLoaded = function () {
    console.log('DOMContentLoaded');
};

document.onreadystatechange = function () {
    if (document.readyState != 'loading') {
        // console.log('loaded');

        // attach menu event handlers
        //  attach event handlers

        let a_up = document.querySelector('#menu-top');
        a_up.onclick = function (e) {
            // document.body.scrollTop = 0; // For Safari
            // document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
            let body = document.querySelector('body');
            body.scrollIntoView({ behavior: "smooth" });
        };


        let a_stories = document.querySelector('#menu-story-index');
        a_stories.onclick = function (e) {
            state = {
                section: "story_index"
            };
            window.history.pushState(state, null, "");
            render(state);
        };

        let a_cyot = document.querySelector('#menu-cyot-index');
        a_cyot.onclick = function (e) {
            state = {
                section: "cyot_index"
            };
            window.history.pushState(state, null, "");
            render(state);
        };


        let a_down = document.querySelector('#menu-bottom');
        a_down.onclick = function (e) {
            // document.body.scrollTop = 0; // For Safari
            // document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
            let body = document.querySelector('body');
            body.scrollIntoView({ behavior: "smooth", block: "end" });
        };
    }
};