jQuery(document).ready(function($){
    var custom_uploader;
    $('#qp_upload_stripe_image').click(function(e) {
        e.preventDefault();
        if (custom_uploader) {custom_uploader.open();return;}
        custom_uploader = wp.media.frames.file_frame = wp.media({
            title: 'Stripe Image',button: {text: 'Insert Image'},multiple: false});
        custom_uploader.on('select', function() {
            attachment = custom_uploader.state().get('selection').first().toJSON();
            $('#qp_stripe_image').val(attachment.url);
        });
        custom_uploader.open();
    });
    $('#qp_upload_background_image').click(function(e) {
        e.preventDefault();
        if (custom_uploader) {custom_uploader.open();return;}
        custom_uploader = wp.media.frames.file_frame = wp.media({
            title: 'Background Image',button: {text: 'Insert Image'},multiple: false});
        custom_uploader.on('select', function() {
            attachment = custom_uploader.state().get('selection').first().toJSON();
            $('#qp_background_image').val(attachment.url);
        });
        custom_uploader.open();
    });
    $('.qp-color').wpColorPicker();
});