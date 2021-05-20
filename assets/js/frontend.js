(function ($) {
    $('body').on('click', '#rahularyan-vsr > a', function (e) {
        e.preventDefault()
        // showToolTip($(this), 'This is a sample tooltip', true);
        var elm = $(this)
        // return;
        $.ajax({
            method: 'POST',
            url: rahularyanVsr.ajaxurl,
            data: {
                action: 'rahularyan_vsr',
                vsr_action: 'add_reaction',
                args: elm.attr('data-vsr')
            },
            success: function (data) {
                afterAjax(data, elm)
            },
            error: function (err) {
                afterAjax(err.responseJSON, elm)
            }
        })
    });

    $(document).on('click', function () {
        $('.rahularyan-vsr-tooltip').fadeOut(200)
    })

    function afterAjax(data, elm) {
        if (!data || data.data) showToolTip(elm, 'Did not received a valid response from server.')

        if (data.data.msg) showToolTip(elm, data.data.msg, data.success)

        if ('added_reaction' === data.data.user_action)
            elm.addClass('rahularyan-vsr-active')
        else {
            elm.removeClass('rahularyan-vsr-active')
        }

        var countElm = elm.find('[data-vsr-count]')
        if (countElm) {
            countElm.attr('data-vsr-count', data.data.count)
            countElm.text(data.data.count)
        }
    }

    function showToolTip(elm, txt, isSuccess) {
        $('.rahularyan-vsr-tooltip').remove()
        var style = {}
        var div = $('<div class="rahularyan-vsr-tooltip">' + txt + '</div>')
        $('body').append(div)

        var posT = $(elm).position().top
        var posL = $(elm).position().left

        style.top = posT - (div.height() + 10)
        style.left = posL - (div.width() / 2)

        div.css(style)
        div.fadeIn(200)
        if (!isSuccess)
            div.addClass('error')
    }
})(jQuery)