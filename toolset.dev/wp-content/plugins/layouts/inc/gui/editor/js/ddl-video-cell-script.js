var DDLayout = DDLayout || {};

DDLayout.VideoCell = function($)
{
    var self = this;

        self.$input = '';
        self.$button = '' ;
        self.$message = $('#js-video-message');


    self.init = function()
    {
        $(document).on('video-cell.dialog-open', self.dialog_open);
        WPV_Toolset.Utils.eventDispatcher.listenTo(WPV_Toolset.Utils.eventDispatcher, 'color_box_closed', self.clean_up )
    };

    self.dialog_open = function(event)
    {
        self.$input = $('input[name="ddl-layout-video_url"]')
        self.$button = $('.js-dialog-edit-save');

        if( self.$input.val() == '' )
        {
            self.$button.prop('disabled', true );
        }

        $(document).on('change', self.$input.selector, self.handle_change);

    };

    self.handle_change = function(event)
    {
        if( $(this).val() == '' )
        {
            self.$message.wpvToolsetMessage({
                text:  DDLayout_settings.DDL_JS.strings.video_message_text,
                tag:'p',
                stay: true,
                close: false,
                type: 'notice'
            });
            self.$button.prop('disabled', true );
        }
        else
        {
            self.$message.wpvToolsetMessage('destroy');
            self.$button.prop('disabled', false );
        }
    };

    self.clean_up = function(event)
    {
        $(document).off('change', self.handle_change);
    };

    self.init();

};


(function($){
     $(function(){
            var video_cell =  new DDLayout.VideoCell($);
     });
}(jQuery));