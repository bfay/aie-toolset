<?php
require_once 'class.credfile.php';

/**
 * Description of class
 *
 * @author Srdjan
 *
 * $HeadURL: https://www.onthegosystems.com/misc_svn/common/trunk/toolset-forms/classes/class.audio.php $
 * $LastChangedDate: 2014-07-07 17:15:03 +0200 (lun, 07 lug 2014) $
 * $LastChangedRevision: 24685 $
 * $LastChangedBy: marcin $
 *
 */
class WPToolset_Field_Credaudio extends WPToolset_Field_Credfile
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
