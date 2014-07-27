<?php
require_once 'class.credfile.php';

/**
 * Description of class
 *
 * @author Srdjan
 *
 * $HeadURL: https://www.onthegosystems.com/misc_svn/common/trunk/toolset-forms/classes/class.video.php $
 * $LastChangedDate: 2014-07-07 17:15:03 +0200 (lun, 07 lug 2014) $
 * $LastChangedRevision: 24685 $
 * $LastChangedBy: marcin $
 *
 */
class WPToolset_Field_Credvideo extends WPToolset_Field_Credfile
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
