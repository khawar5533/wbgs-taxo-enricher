jQuery(document).ready(function ($) {
    function initGalleryButton(button) {
        button.on('click', function (e) {
            e.preventDefault();

            var $button = $(this);
            var $container = $button.closest('td, .form-field'); // Add/Edit screen support
            var $input = $container.find('input.wbgs-gallery');
            var $preview = $container.find('ul.wbgs-gallery-preview');

            // If frame already exists, reopen it
            if (typeof wp === 'undefined' || !wp.media) {
                console.error('wp.media is not available.');
                return;
            }

            var frame = wp.media({
                title: 'Select Gallery Images',
                multiple: true,
                library: { type: 'image' },
                button: { text: 'Use these images' }
            });

            frame.on('select', function () {
                var attachments = frame.state().get('selection').toJSON();
                var ids = attachments.map(function (img) {
                    return img.id;
                });

                var previews = attachments.map(function (img) {
                    var thumb = img.sizes && img.sizes.thumbnail ? img.sizes.thumbnail.url : img.url;
                    return '<li><img src="' + thumb + '" /></li>';
                });

                $input.val(ids.join(','));
                $preview.html(previews.join(''));
            });

            frame.open();
        });
    }

    // Initialize all gallery buttons
    $('.wbgs-add-gallery').each(function () {
        initGalleryButton($(this));
    });
});
