
var current_step = 0;
var current_site_id = 0;
var wpvdemo_download_check_num_posts = true;

jQuery(document).ready(function(){
    jQuery('.wpvdemo-download').click(function(){
        if (jQuery(this).attr('disabled') == 'disabled') {
            return false;
        }
        var answer = confirm(wpvdemo_confirm_download_txt);
        if (answer){
            wpvdemoDownloadStep(jQuery(this).attr('href'), 1);
            jQuery('.wpvdemo-download').attr('disabled', 'disabled');
        }
        return false;
    });
    
    setInterval(function() {
        if (current_step == 2) {
            if (wpvdemo_download_check_num_posts == false) {
                return false;
            }
            var updateDiv = jQuery('#wpvdemo-download-response-'+current_site_id);
            jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                data: 'action=wpvdemo_post_count&_wpnonce='+wpvdemo_nonce,
                cache: false,
                beforeSend: function () {
                    wpvdemo_download_check_num_posts = false;
                },
                success: function (data) {
                    
                    // check for string length. This is to avoid a bug where WP is creating cron errors
                    // https://icanlocalize.basecamphq.com/projects/7393061-wp-views/todo_items/147303129/comments
                    
                    if (data.length < 12) { 
                        updateDiv.find('#wpvdemo_step_' + current_step).find('.wpvdemo-post-count').show();
                        updateDiv.find('#wpvdemo_step_' + current_step).find('.wpvdemo-post-count').html(data);
                        wpvdemo_download_check_num_posts = true;
                    }
                }
            });
        }
    }, 2000);    
});

function wpvdemoDownloadStep(site_id, step) {
    var updateDiv = jQuery('#wpvdemo-download-response-'+site_id);
    
    current_site_id = site_id;
    
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: 'action=wpvdemo_download&_wpnonce='+wpvdemo_nonce+'&site_id='+site_id+'&step='+step,
        cache: false,
        beforeSend: function() {
            if (step == 1) {
                updateDiv.append(wpvdemo_download_step_one_txt);
            }
            //updateDiv.next().addClass('wpcf-ajax-loading-small');
            
            for (var i = 1; i <= 10; i++) {
                if (i == step) {
                    updateDiv.find('#wpvdemo_step_' + i).css('font-weight', 'bold');
                    updateDiv.find('#wpvdemo_step_' + i).find('.wpcf-ajax-loading-small').show();
                } else {
                    updateDiv.find('#wpvdemo_step_' + i).css('font-weight', 'normal');
                    updateDiv.find('#wpvdemo_step_' + i).find('.wpcf-ajax-loading-small').hide();
                }
            }
            
            current_step = step;
        },
        success: function(data) {
            updateDiv.find('#wpvdemo_step_' + step).css('font-weight', 'normal');
            updateDiv.find('#wpvdemo_step_' + step).find('.wpvdemo-green-check').show();
            updateDiv.find('#wpvdemo_step_' + step).find('.wpcf-ajax-loading-small').hide();
            
            updateDiv.append(data);
            
            if (step == 2) {
                    updateDiv.find('#wpvdemo_step_' + step).find('.wpvdemo-post-count').hide();
            }
        }
    });
}