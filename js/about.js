$(document).ready(() => {
    $('.ui.accordion')
    .accordion()
})


$(window).click(function (e) {
    let isAccordion = false
    e.originalEvent.path.forEach(el => {
        let classes = el.classList
        if (classes && classes.contains('ui') && classes.contains('accordion')) {
            isAccordion = true;
        }
    });
    if (!isAccordion) {
        $('header .ui.accordion').accordion('close', 0);
    }
})