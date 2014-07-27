jQuery(document).ready(function($){

    /* Titlediv placeholder for Layouts wizard */
    var $titleInput = $('#titlewrap input[type=text]');
    var $placeholder = $('#titlewrap label');

    $titleInput
        .on('blur',function(){
            if ( $titleInput.val() === '' ) {
                $placeholder.show();
            } else {
                $placeholder.hide();
            }
        })
        .on('focus',function(){
            $placeholder.hide();
        });
    /* Titlediv placeholder for Layouts wizard END */

    /* Generic function to display native WP Tooltip */
    $(document).on('click','.js-show-tooltip',function(){

        var $this = $(this);

        // default options
        var defaults = {
            edge : "left", // on which efge of the element tooltips should be shown: ( right, top, left, bottom )
            align : "middle", // how the pointer should be aligned on this edge, relative to the target (top, bottom, left, right, middle).
            offset: "15 0 " // pointer offset - relative to the edge
        };

        // custom options passed in HTML "data-" attributes
        var custom = {
            edge : $this.data('edge'),
            align : $this.data('align'),
            offset : $this.data('offset')
        };

        $this.pointer({
            content: '<h3>' + $this.data('header') + '</h3>' + '<p>' + $this.data('content') + '</p>',
            position: $.extend(defaults, custom) // merge defaults and custom attributes
        }).pointer('open');

    });
    /* Generic function to display native WP Tooltip END */

});