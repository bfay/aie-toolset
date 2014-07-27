<?php
/**
 *
 * $HeadURL: https://www.onthegosystems.com/misc_svn/common/trunk/toolset-forms/classes/class.radios.php $
 * $LastChangedDate: 2014-07-04 04:22:37 +0000 (Fri, 04 Jul 2014) $
 * $LastChangedRevision: 24624 $
 * $LastChangedBy: bruce $
 *
 */
require_once 'class.field_factory.php';

/**
 * Description of class
 *
 * @author Srdjan
 */
class WPToolset_Field_Radios extends FieldFactory
{

    public function metaform()
    {
        $value = $this->getValue();
        $data = $this->getData();
        $form = array();
        $options = array();
        foreach ( $data['options'] as $option ) {
            $one_option_data = array(
                '#value' => $option['value'],
                '#title' => $option['title'],
                '#validate' => $this->getValidationData(),
                '#after' => '<br />',
            );
            /**
             * add default value if needed
             * issue: frontend, multiforms CRED
             */
            if ( array_key_exists( 'types-value', $option ) ) {
                $one_option_data['#types-value'] = $option['types-value'];
            }
            /**
             * add to options array
             */
            $options[] = $one_option_data;
        }
        $options = apply_filters( 'wpt_field_options', $options, $this->getTitle(), 'select' );
        /**
         * default_value
         */
        if ( !empty( $value ) || $value == '0' ) {
            $data['default_value'] = $value;
        }
        /**
         * metaform
         */
        $form[] = array(
            '#type' => 'radios',
            '#title' => $this->getTitle(),
            '#description' => $this->getDescription(),
            '#name' => $this->getName(),
            '#options' => $options,
            '#default_value' => isset( $data['default_value'] ) ? $data['default_value'] : false,
            '#repetitive' => $this->isRepetitive(),
            '#validate' => $this->getValidationData(),
        );

        return $form;
    }

}
