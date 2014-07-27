<?php
class WPDDL_Messages{

       static $message_error = '<div class="alert-no-post-content toolset-alert toolset-alert-error">%s</div>';
       static $message_info = '<div class="alert-no-post-content toolset-alert">%s</div>';
       static $message_warning = '<div class="alert-no-post-content toolset-alert toolset-alert-warning">%s</div>';

       public static function views_missing_message()
       {
           return sprintf(self::$message_error, __('The Views plugin should be activated to display this layout.', 'ddl-layouts' ) );
       }

       public static function display_message( $type, $message )
       {
           switch( $type )
           {
               case 'error':
                   return sprintf(self::$message_error, $message );
               break;
               case 'warning':
                   return sprintf(self::$message_warning, $message );
               break;
               case 'info':
                   return sprintf(self::$message_info, $message );
               break;
               default:
                   return sprintf(self::$message_info, $message );
           }
       }
};