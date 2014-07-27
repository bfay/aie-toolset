<?php
require_once 'class.file.php';

/**
 * Description of class
 *
 * @author Srdjan
 *
 * $HeadURL: https://www.onthegosystems.com/misc_svn/common/tags/Types1.6b4-CRED1.3b4-Views1.6.2b2/toolset-forms/classes/class.video.php $
 * $LastChangedDate: 2014-07-12 08:38:18 +0000 (Sat, 12 Jul 2014) $
 * $LastChangedRevision: 24908 $
 * $LastChangedBy: gen $
 *
 */
class WPToolset_Field_Video extends WPToolset_Field_File
{
    protected $_settings = array('min_wp_version' => '3.6');

    public function metaform()
    {
        $form = parent::metaform();
        if ( !isset( $form[0] ) || !is_array($form[0] ) ) {
            return $form;
        }
        if ( !array_key_exists( '#validate', $form[0] ) ) {
            $form[0]['#validate'] = array();
        }
        if ( !array_key_exists( 'extension', $form[0]['#validate'] ) ) {
            $form[0]['#validate']['extension'] = array(
                'args' => array(
                    'extension',
                    '3gp|aaf|asf|avchd|avi|cam|dat|dsh|fla|flr|flv|m1v|m2v|m4v|mng|mp4|mxf|nsv|ogg|rm|roq|smi|sol|svi|swf|wmv|wrap|mkv|mov|mpe|mpeg|mpg',
                ),
                'message' => __( 'You can add only video.', 'wpv-views' ),
            );
        }
        return $form;
    }
}
