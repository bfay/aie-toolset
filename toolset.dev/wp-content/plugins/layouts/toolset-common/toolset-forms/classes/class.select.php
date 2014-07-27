<?php
/**
 *
 * $HeadURL: https://www.onthegosystems.com/misc_svn/common/trunk/toolset-forms/classes/class.select.php $
 * $LastChangedDate: 2014-06-24 10:55:48 +0000 (Tue, 24 Jun 2014) $
 * $LastChangedRevision: 24192 $
 * $LastChangedBy: marcin $
 *
 */
require_once 'class.field_factory.php';

/**
 * Description of class
 *
 * @author Srdjan
 */
class WPToolset_Field_Select extends FieldFactory
{

    public function metaform() {
        $value = $this->getValue();
        $data = $this->getData();

        $form = array();
        $options = array();
        if (isset($data['options'])) {
            foreach ( $data['options'] as $option ) {
                $one_option_data = array(
                    '#value' => $option['value'],
                    '#title' => $option['title'],
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
            '#type' => 'select',
            '#title' => $this->getTitle(),
            '#description' => $this->getDescription(),
            '#name' => $this->getName(),
            '#options' => $options,
            '#default_value' => isset( $data['default_value'] ) ? $data['default_value'] : null,
            '#validate' => $this->getValidationData(),
            '#class' => 'form-inline',
            '#repetitive' => $this->isRepetitive(),
        );
        return $form;
    }

}
