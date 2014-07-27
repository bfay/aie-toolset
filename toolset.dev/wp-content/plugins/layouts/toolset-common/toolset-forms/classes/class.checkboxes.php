<?php
/**
 * Description of class
 *
 * @author Srdjan
 *
 * $HeadURL: https://www.onthegosystems.com/misc_svn/common/trunk/toolset-forms/classes/class.checkboxes.php $
 * $LastChangedDate: 2014-07-17 04:03:56 +0000 (Thu, 17 Jul 2014) $
 * $LastChangedRevision: 25033 $
 * $LastChangedBy: bruce $
 *
 */

require_once 'class.field_factory.php';

class WPToolset_Field_Checkboxes extends FieldFactory
{
    public function metaform()
    {
        global $post;
        $value = $this->getValue();
        $data = $this->getData();
        $form = array();
        $_options = array();
        if (isset($data['options'])) {
            foreach ( $data['options'] as $option_key => $option ) {
                
                $checked = isset( $option['checked'] ) ? $option['checked'] : !empty( $value[$option_key] );
                
                if (isset($post) && 'auto-draft' == $post->post_status && array_key_exists( 'checked', $option ) && $option['checked']) {
                    $checked = true;
                }
                
                // Comment out broken code. This tries to set the previous state after validation fails
                //$_values=$this->getValue();
                //if (!$checked&&isset($value)&&!empty($value)&&is_array($value)&&in_array($option['value'],$value)) {
                //    $checked=true;
                //}
                
                $_options[$option_key] = array(
                    '#value' => $option['value'],
                    '#title' => $option['title'],
                    '#type' => 'checkbox',
                    '#default_value' => $checked,
                    '#name' => $option['name']."[]",
                    '#inline' => true,
                    '#after' => '<br />',
                );
            }
        }
        $metaform = array(
            '#type' => 'checkboxes',
            '#options' => $_options,
        );
        if ( is_admin() ) {
            $metaform['#title'] = $this->getTitle();
            $metaform['#after'] = '<input type="hidden" name="_wptoolset_checkbox[' . $this->getId() . ']" value="1" />';
        }
        return array($metaform);
    }
}
