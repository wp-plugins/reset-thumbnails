jQuery(document).ready(function ($) {
    $('#change_ID').on('click',function(){
        if($(this).is(':checked')){
            $(this).prop('checked',true);
        }else{
            $(this).prop('checked',false);
        }
    });

    /******* RESET ACTION ********/
    $('.js-reset-action').on('click',function(e){
        e.preventDefault();
        var type = $('#choose_post_type').val();
        var img_id = $('#kai_default_image').val();
        var change_id = $('#change_ID').is(':checked');
        $.ajax({
            type : 'post',
            data : {
                action : 'set_default_image',
                'type' : type,
                'img_id' : img_id,
                'change_id' : change_id
            },
            url : kai.url,
            async: true,
            beforeSend: function () {
                $('.p-notice').hide();
                $('.ajax-loading').fadeIn();
                $('body .p-result').hide();
                $('body .p-notice').hide();
            },
            success: function (response) {
                console.log(response);
                $('.ajax-loading').hide();
                if(response!=""){
                    var obj = JSON.parse(response);
                    $('.js-reset-action').before('<div class="p-result">'+obj+'</div>');
                    $('.js-reset-action').before('<p class="p-notice">Done for '+type+'!!!!</p>');
                }else{
                    $('.js-reset-action').before('<p class="p-notice error"><strong>ERROR :</strong> Upload your image first!</p>');
                }
            }
        });
    });

    /***** Choose Post Type *****/
    $('#choose_post_type').on('change', function (e) {
        if($(this).val()=='attachment'){
            $(this).parent().find('.description').text('*Note : Set default image to all media attachments');
        }else{
            $(this).parent().find('.description').text('');
        }
    })

    /***** Colour picker *****/
    $('.colorpicker').hide();
    $('.colorpicker').each(function () {
        $(this).farbtastic($(this).closest('.color-picker').find('.color'));
    });

    $('.color').click(function () {
        $(this).closest('.color-picker').find('.colorpicker').fadeIn();
    });

    $(document).mousedown(function () {
        $('.colorpicker').each(function () {
            var display = $(this).css('display');
            if (display == 'block')
                $(this).fadeOut();
        });
    });


    /***** Uploading images *****/

    var file_frame;

    jQuery.fn.uploadMediaFile = function (button, preview_media) {
        var button_id = button.attr('id');
        var field_id = button_id.replace('_button', '');
        var preview_id = button_id.replace('_button', '_preview');

        // If the media frame already exists, reopen it.
        if (file_frame) {
            file_frame.open();
            return;
        }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: jQuery(this).data('uploader_title'),
            button: {
                text: jQuery(this).data('uploader_button_text'),
            },
            multiple: false
        });

        // When an image is selected, run a callback.
        file_frame.on('select', function () {
            attachment = file_frame.state().get('selection').first().toJSON();
            jQuery("#" + field_id).val(attachment.id);
            if (preview_media) {
                jQuery("#" + preview_id).attr('src', attachment.sizes.thumbnail.url);
            }
            file_frame = false;
        });

        // Finally, open the modal
        file_frame.open();
    }

    jQuery('.image_upload_button').click(function () {
        jQuery.fn.uploadMediaFile(jQuery(this), true);
    });

    jQuery('.image_delete_button').click(function () {
        jQuery(this).closest('td').find('.image_data_field').val('');
        jQuery(this).closest('td').find('.image_preview').remove();
        return false;
    });

});