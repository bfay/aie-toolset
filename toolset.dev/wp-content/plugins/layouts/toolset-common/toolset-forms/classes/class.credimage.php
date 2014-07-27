<?php
require_once 'class.credfile.php';

/**
 * Description of class
 *
 * @author Srdjan
 *
 * $HeadURL: https://www.onthegosystems.com/misc_svn/common/trunk/toolset-forms/classes/class.image.php $
 * $LastChangedDate: 2014-07-07 17:15:03 +0200 (lun, 07 lug 2014) $
 * $LastChangedRevision: 24685 $
 * $LastChangedBy: marcin $
 *
 */
class WPToolset_Field_Credimage extends WPToolset_Field_Credfile
{
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
                    'jpg|jpeg|gif|png',
                ),
                'message' => __( 'You can add only images.', 'wpv-views' ),
            );
        }
        return $form;
    }
}
