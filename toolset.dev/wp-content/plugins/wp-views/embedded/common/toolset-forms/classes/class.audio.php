<?php
require_once 'class.file.php';

/**
 * Description of class
 *
 * @author Srdjan
 *
 * $HeadURL: https://www.onthegosystems.com/misc_svn/common/tags/Types1.6b4-CRED1.3b4-Views1.6.2b2/toolset-forms/classes/class.audio.php $
 * $LastChangedDate: 2014-07-12 08:38:18 +0000 (Sat, 12 Jul 2014) $
 * $LastChangedRevision: 24908 $
 * $LastChangedBy: gen $
 *
 */
class WPToolset_Field_Audio extends WPToolset_Field_File
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
                    '16svx|2sf|8svx|aac|aif|aifc|aiff|amr|ape|asf|ast|au|aup|band|brstm|bwf|cdda|cust|dsf|dwd|flac|gsf|gsm|gym|it|jam|la|ly|m4a|m4p|mid|minipsf|mng|mod|mp1|mp2|mp3|mp4|mpc|mscz|mt2|mus|niff|nsf|off|ofr|ofs|ots|pac|psf|psf2|psflib|ptb|qsf|ra|raw|rka|rm|rmj|s3m|shn|sib|sid|smp|spc|spx|ssf|swa|tta|txm|usf|vgm|voc|vox|vqf|wav|wma|wv|xm|ym',
                ),
                'message' => __( 'You can add only audio.', 'wpv-views' ),
            );
        }
        return $form;
    }
}
