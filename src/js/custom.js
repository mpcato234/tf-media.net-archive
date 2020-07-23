utilities = {};
T = {};


/**
         * set a cookie for later use
         */
utilities.setCookie = function (cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}




/**
         * return cookie by name
         */
utilities.getCookie = function (cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return null;
}





/**
 * render story/cyot terms
 */
function renderTerms(terms, domContainer) {
    // console.trace(domContainer);
    if (terms && (terms.length > 0)) {
        var t_term = document.querySelector('#template__term');
        for (var j in terms) {

            let term = terms[j];
            let term_item = document.importNode(t_term.content, true);

            let text = term_item.querySelector('.term');
            text.textContent = term.name;

            domContainer.appendChild(term_item);
        }

    } else {
        domContainer.parentNode.removeChild(domContainer);
    }
}



/**
         * render the current state
         */
function render(state) {
    console.trace(state);

    let main = document.querySelector('main');
    main.innerHTML = "";

    switch (state.section) {
        case "cyot_index":
            renderCYOTIndex(localstorage.nodemap_cyot, localstorage.rootlist_cyot);
            break;
        case "cyot_chapter":
            if (state.nid) {
                // render chapter using state.nid
                // renderCYOTNode(obj, state.nid);
            } else {
                // fallback to rendering the index probably
                //state.section = "cyot_index";
            }
            break;
        case "cyot_tree":
            // renderCYOTTree(obj, state.nid);
            break;
        case "story_index":
            renderStoryList(localstorage.nodelist_story);
            break;
        case "story":
            renderStory(localstorage.nodelist_story, state.nid);
            break;
        case "about":
            // renderAboutPage();
            break;
        default:
            // render main menu
            break;
    }



    // if (state.position) {
    //     //  console.log(state.position);
    //     switch (state.position) {
    //         case "chapterstart":
    //             // scroll to start of nid
    //             if (state.nid) {
    //                 let article = document.querySelector('#cyot_chapter__' + state.nid);
    //                 article.scrollIntoView(true);
    //             }
    //             break;
    //         case "chapterend":
    //             // scroll to start of nid
    //             if (state.nid) {
    //                 let article = document.querySelector('#cyot_chapter__' + state.nid);
    //                 article.scrollIntoView(false);
    //             }
    //             break;
    //         case "top":
    //             // scroll to top
    //             window.scrollTo(0, 0);
    //             break;
    //     }
    // }
    // save the state to a cookie for this page, lasting for 365 days
    utilities.setCookie('state', JSON.stringify(state), 356);

    // attach handlers
    // var elems = document.querySelectorAll('.collapsible');
    // var options = {};
    // var instances = M.Collapsible.init(elems, options);
    reloadCollapsible();


}



/**
         * render one story node
         */
function renderStory(nodelist, nid) {

    var node = nodelist.find(function (element) {
        return element.nid && (element.nid == nid);
    });
    console.trace(node);
    let t = document.querySelector('#template__story');
    let t_menu = document.querySelector('#template__menu');
    let page = document.importNode(t.content, true);
    let main = document.querySelector('main');
    let menu = document.importNode(t_menu.content, true);

    let title = page.querySelector('.template__story__title');
    console.trace(title);
    let body = page.querySelector('.template__story__body');
    let teaser = page.querySelector('.template__story__teaser');
    let author = page.querySelector('.template__story__author');
    var terms = page.querySelector('.template__story__terms');

    renderTerms(node.terms, terms);



    title.textContent = node.title;
    body.innerHTML = node.body;
    teaser.innerHTML = node.teaser;
    author.textContent = node.author;

    main.appendChild(page);
}




/**
 * render a list of all stories
 */
function renderStoryList(nodelist) {
    console.log('renderStoryList');

    let main = document.querySelector('#column-page');

    var t = document.querySelector('#template__toc');
    // let t_menu = document.querySelector('#template__menu');
    // let menu = document.importNode(t_menu.content, true);
    var page = document.importNode(t.content, true);
    var container = page.querySelector('.template__toc__ul');



    var t = document.querySelector('#template__story_short');
    for (var i in nodelist) {
        let node = nodelist[i];
        let item = document.importNode(t.content, true);

        let a = item.querySelector('.template__story_short__a');
        let title = item.querySelector('.template__story_short__title');
        let terms = item.querySelector('.template__story__terms');
        title.textContent = node.title;
        a.textContent = '... continue reading ';
        a.option = node.nid;
        a.onclick = function (e) {
            let state = {
                section: "story",
                nid: this.option,
                position: "chapterstart"
            };
            window.history.pushState(state, null, "");
            render(state);
        };



        let teaser = item.querySelector('.template__story_short__teaser');
        teaser.innerHTML = node.teaser;
        let author = item.querySelector('.template__story__author');
        author.textContent = (node.author ? node.author : 'an unknown author');

        renderTerms(node.terms, terms)

        container.appendChild(item);
    }



    // main.appendChild(menu);
    main.appendChild(page);
}




/**
 * render the CYOT index
 */
function renderCYOTIndex(nodelist, roots) {
    console.log('renderCYOTIndex');
    console.trace(nodelist);

    let main = document.querySelector('#column-page');

    var t_tree = document.querySelector('#template__tree');
    // let t_menu = document.querySelector('#template__menu');
    // let menu = document.importNode(t_menu.content, true);
    var page = document.importNode(t_tree.content, true);
    var container = page.querySelector('.template__tree__ul');


    var t = document.querySelector('#template__cyot_short');
    for (var i in roots) {
        let nid = roots[i];
        // console.trace(nid);
        let node = nodelist[nid];
        // console.trace(nodelist, node);
        let item = document.importNode(t.content, true);

        let a = item.querySelector('.template__cyot_short__a');
        let title = item.querySelector('.template__cyot__title');
        let terms = item.querySelector('.template__cyot__terms');
        title.textContent = node.title;
        a.textContent = node.title;
        a.option = node.nid;
        a.onclick = function (e) {
            let state = {
                section: "cyot_chapter",
                nid: this.option,
                position: "chapterstart"
            };
            window.history.pushState(state, null, "");
            render(state);
        };



        let teaser = item.querySelector('.template__cyot_short__teaser');
        teaser.innerHTML = node.teaser;
        let author = item.querySelector('.template__cyot__author');
        author.textContent = (node.author ? node.author : 'an unknown author');



        renderTerms(node.terms, terms)

        container.appendChild(item);
    }


    // main.appendChild(menu);
    main.appendChild(page);

    // var cyot = document.createElement('ul');
    // cyot.classList.add('cyot');
    // for (i = 0; i < roots.length; i++) {
    //     let rootnode = nodelist[roots[i]];
    //     // console.trace(rootnode);
    //     let li_node = document.createElement('li');
    //     let a_node = document.createElement('a');
    //     a_node.innerHTML = rootnode['title'];

    //     a_node.option = rootnode.nid;
    //     a_node.nodes = nodelist;
    //     a_node.onclick = function(e) {
    //         state = {
    //             section: "cyot_chapter",
    //             nid: this.option,
    //             position: 'chapterstart'
    //         };
    //         window.history.pushState(state, null, "");
    //         render(state);
    //     };

    //     var a_tree = document.createElement('a');
    //     a_tree.innerHTML = "view tree";
    //     a_tree.classList.add('link_secondary');
    //     a_tree.option = rootnode.nid;
    //     a_tree.nodes = nodelist;
    //     a_tree.onclick = function(e) {
    //         state = {
    //             section: "cyot_tree",
    //             nid: this.option
    //         };
    //         console.trace(a_tree.option);
    //         window.history.pushState(state, null, "");
    //         render(state);
    //         window.scrollTo(0, 0);
    //     };


    //     li_node.appendChild(a_node);
    //     var counter = document.createElement('span');
    //     counter.classList.add('cyot_depth');
    //     counter.innerHTML = rootnode['mindepth'] + "|" + rootnode['maxdepth'];
    //     li_node.appendChild(counter);
    //     li_node.appendChild(a_tree);
    //     cyot.appendChild(li_node);
    // }

    // var container = document.getElementById('container');
    // container.innerHTML = "";
    // container.appendChild(cyot);

    // // scroll to top
    // window.scrollTo(0, 0);
}





function reloadCollapsible() {
    // console.log('reloadCollaspsible');
    var coll = document.querySelectorAll(".collapsible");
    // console.trace(coll);

    for (var i = 0; i < coll.length; i++) {

        console.trace(coll[i]);
        var collapsible = coll[i];
        var items = collapsible.querySelectorAll('.collapsible > li');

        for (var itemindex = 0; itemindex < items.length; itemindex++) {
            // console.trace(itemindex);
            var item = items[itemindex];
            // console.trace(item);
            let head = item.querySelector('.collapsible-header');
            let body = item.querySelector('.collapsible-body');

            head.option = {
                item: item,
                collapsible: collapsible
            };

            head.onclick = function () {
                console.log('header clicked');
                console.trace(this.option);



                var items = this.option.collapsible.querySelectorAll('.collapsible > li.active');
                console.trace(items);
                for (var i = 0; i < items.length; i++) {
                    if (this.option.item !== items[i]) {
                        items[i].classList.toggle('active');
                    }
                }

                this.option.item.classList.toggle('active');

            }
        }



    }
}