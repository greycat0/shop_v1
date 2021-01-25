let current_request

$(document).ready(function () {
    $(".owl-carousel").owlCarousel({
        items: 1,
        loop: true,
        autoplay: true,
        margin: 20,

    });
    $('.ui.accordion')
        .accordion()
    const go = (new URLSearchParams(location.search.slice(1))).get('go')
    if (go) {
        $('html, body')
            .animate({
                scrollTop: $(document.evaluate(go, document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue).offset().top - 600
            }, 1000)
    }

});
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

$('.request-button').click(e => {
    $('#request-form').get(0).classList.add('request-form-active')
    document.body.style.overflow = 'hidden'
    current_request = $(e.currentTarget).find("[name=meta]").html()
})

$('#request-form').first().click(e => {
    e.target.classList.remove('request-form-active')
    if (e.target.id == 'request-form') {
        document.body.style.overflow = 'auto'
    }
})
function aaa(e) {
    e.preventDefault();

    let is_complete1 = false
    let is_complete2 = false
    let button = e.submitter

    button.classList.add('loading')

    let done = () => {
        let text = document.createElement('h1')
        text.innerHTML = "Спасибо за заявку!"
        text.classList.add('b-b')
        $(e.target).empty();
        e.target.appendChild(text)

    }
    setTimeout(() => {
        is_complete1 = true
        if (is_complete2) done()
    }, 1000)

    $.ajax({
        url: 'api.php',
        type: 'POST',
        data: {
            action: 'purchase_request',
            name: $("#request-form input[name=name]").val(),
            tel: $("#request-form input[name=tel]").val(),
            email: $("#request-form input[name=email]").val(),
            current_request
        },
        success: () => {
            is_complete2 = true
            if (is_complete1) done()
        }
    })
}