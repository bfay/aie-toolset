<?php
/**
 *
 * $HeadURL: https://www.onthegosystems.com/misc_svn/common/tags/Types1.6b4-CRED1.3b4-Views1.6.2b2/toolset-forms/classes/class.textarea.php $
 * $LastChangedDate: 2014-07-10 08:46:40 +0000 (Thu, 10 Jul 2014) $
 * $LastChangedRevision: 24820 $
 * $LastChangedBy: francesco $
 *
 */
require_once 'class.field_factory.php';

/**
 * Description of class
 *
 * @author Franko
 */
class WPToolset_Field_Textarea extends FieldFactory
{

    public function metaform() {
        $attributes = $this->getAttr();
        
        $metaform = array();
        $metaform[] = array(
            '#type' => 'textarea',
            '#title' => $this->getTitle(),
            '#description' => $this->getDescription(),
            '#name' => $this->getName(),
            '#value' => $this->getValue(),
            '#validate' => $this->getValidationData(),
            '#repetitive' => $this->isRepetitive(),
            '#attributes' => $attributes
        );
        return $metaform;
    }

}
