(function ($) {
    $(document).ready(function () {
        $('.vsr-reactions-table').on('click', '.rahularyan-vsr-open-media', function (e) {
            e.preventDefault();
            var $button = $(this);


            // Create the media frame.
            var file_frame = wp.media.frames.file_frame = wp.media({
                title: 'Upload image',
                library: { // remove these to show all
                    type: 'image' // specific mime
                },
                button: {
                    text: 'Use this image'
                },
                multiple: false  // Set to true to allow multiple files to be selected
            });

            // When an image is selected, run a callback.
            file_frame.on('select', function () {
                // We set multiple to false so only get one image from the uploader

                var attachment = file_frame.state().get('selection').first().toJSON();

                $button.parent().find('input[type="hidden"]').val(attachment.id)
                $button.parent().find('img').remove()
                $button.parent().prepend('<img src="' + attachment.url + '" />')
                $button.parent().addClass('has-image')
            });

            // Finally, open the modal
            file_frame.open();
        });

        $('.rahularyan-vsr-add-reaction-type').on('click', function (e) {
            e.preventDefault();

            var elm = $(this)
            $.ajax({
                url: ajaxurl,
                data: {
                    action: 'rahularyan_vsr',
                    vsr_action: 'get_reaction_type_row',
                    args: elm.attr('data-vsr'),
                    counter: $('tr[data-vsr-row]').length
                },
                success: function (data) {
                    $('.vsr-reaction-type-no-row').remove()
                    $('.vsr-reactions-table tbody').append(data);
                }
            })
        })

        $('.vsr-reactions-table').on('click', '.rahularyan-vsr-delete-reaction-type', function (e) {
            e.preventDefault();
            var elm = $(this)
            var slug = elm.closest('[data-vsr-row]').find('.vsr-reaction-type-field-slug').val()

            // If this does not have slug then probably its new and is not saved to DB yet.
            if ('' === slug) {
                elm.closest('tr').remove()
                return;
            }

            $.ajax({
                url: ajaxurl,
                data: {
                    action: 'rahularyan_vsr',
                    vsr_action: 'delete_reaction_type_row',
                    args: elm.attr('data-vsr'),
                    slug: slug
                },
                success: function (data) {
                    elm.closest('tr').remove()
                }
            })
        })

        $('.rahularyan-vsr-rest-to-defaults').on('click', function (e) {
            e.preventDefault();
            var elm = $(this)
            $.ajax({
                url: ajaxurl,
                data: {
                    action: 'rahularyan_vsr',
                    vsr_action: 'reset_reaction_types',
                    args: elm.attr('data-vsr'),
                },
                success: function () {
                    location.reload()
                }
            })
        })
    })
})(jQuery)