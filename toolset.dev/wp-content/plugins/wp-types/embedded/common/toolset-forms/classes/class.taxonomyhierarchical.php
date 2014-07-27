<?php
/**
 *
 * $HeadURL: https://www.onthegosystems.com/misc_svn/common/tags/Types1.6b4-CRED1.3b4-Views1.6.2b2/toolset-forms/classes/class.taxonomyhierarchical.php $
 * $LastChangedDate: 2014-07-18 08:29:05 +0000 (Fri, 18 Jul 2014) $
 * $LastChangedRevision: 25084 $
 * $LastChangedBy: bruce $
 *
 */

include_once 'class.textfield.php';

class WPToolset_Field_Taxonomyhierarchical extends WPToolset_Field_Textfield
{
    protected $child;
    protected $names;
    protected $values = array();
    protected $valuesId = array();
    protected $objValues;

    public function init()
    {
        global $post;

        $this->objValues = array();
        if (isset($post)) {
            $terms = wp_get_post_terms($post->ID, $this->getName(), array("fields" => "all"));
            foreach ($terms as $n => $term) {
                $this->values[] = $term->slug;
                $this->valuesId[] = $term->term_id;
                $this->objValues[$term->slug] = $term;
            }
        }
        
        $all = $this->buildTerms(get_terms($this->getName(),array('hide_empty'=>0,'fields'=>'all')));

        $childs=array();
        $names=array();
        foreach ($all as $term) {
            $names[$term['term_id']]=$term['name'];
            if (!isset($childs[$term['parent']]) || !is_array($childs[$term['parent']]))
                $childs[$term['parent']]=array();
            $childs[$term['parent']][]=$term['term_id'];
        }

        $this->childs = $childs;
        $this->names = $names;
    }

    public function enqueueScripts()
    {
    }

    public function enqueueStyles()
    {
    }

    public function metaform()
    {
        $use_bootstrap = array_key_exists( 'use_bootstrap', $this->_data ) && $this->_data['use_bootstrap'];
        $attributes = $this->getAttr();
        $res = '';
        $metaform = array();
        
        if ( array_key_exists( 'display', $this->_data ) && 'select' == $this->_data['display'] ) {
            $metaform = $this->buildSelect();
        } else {
            $res = $this->buildCheckboxes(0, $this->childs, $this->names, $metaform);
            $this->set_metaform($res);
        }
        
        /**
         * "Add new" button
         */
        $metaform[] = array(
            '#type' => 'button',
            '#title' => '',
            '#description' => '',
            '#name' => "btn_".$this->getName(),
            '#value' => $attributes['add_new_text'],
            '#attributes' => array(
                'style' => 'float:left',
                'onclick' => 'toolsetForms.cred_tax.add_new_show_hide(\''.$this->getName().'\', this)',
                'data-taxonomy' => $this->getName()
            ),

            '#validate' => $this->getValidationData(),
            '#class' => $use_bootstrap? 'btn btn-default':'',
            '#after' => $use_bootstrap? '<br />':'',
        );

        // input for new taxonomy        
        $metaform[] = array(
            '#type' => 'textfield',
            '#title' => '',
            '#description' => '',
            '#name' => "new_tax_text_".$this->getName(),
            '#value' => '',
            '#attributes' => array(
                'style' => 'float:left;display:none',
                'data-taxonomy' => $this->getName()
            ),
            '#validate' => $this->getValidationData(),
            '#before' => $use_bootstrap? '<div class="form-group">':'',
            '#class' => $use_bootstrap? 'inline':'',
        );

        /**
         * The add button
         */
        $metaform[] = array(
            '#type' => 'button',
            '#title' => '',
            '#description' => '',
            '#name' => "new_tax_button_".$this->getName(),
            '#value' => $attributes['add_text'],
            '#attributes' => array(
                'style' => 'float:left;display:none',
                'onclick' => 'toolsetForms.cred_tax.add_taxonomy(\''.$this->getName().'\', this)',
                'data-taxonomy' => $this->getName()
            ),

            '#validate' => $this->getValidationData(),
            '#class' => $use_bootstrap? 'btn btn-default':'',
        );

        /**
         * The select for parent
         */
        $metaform[] = array(
            '#type' => 'select',
            '#title' => '',
            '#options' => array(array(
                    '#title' => $attributes['parent_text'],
                    '#value' => -1,
                )),
            '#default_value' => 0,
            '#description' => '',
            '#name' => "new_tax_select_".$this->getName(),
            '#attributes' => array(
                'style' => 'float:left;display:none',
                'data-parent-text' => $attributes['parent_text'],
                'data-taxonomy' => $this->getName(),
                'class' => 'js-taxonomy-parent'
            ),

            '#validate' => $this->getValidationData(),
            '#after' => $use_bootstrap? '</div>':'',
        );
        
        
        return $metaform;
        
    }

    private function buildTerms($obj_terms)
    {
        $tax_terms=array();
        foreach ($obj_terms as $term) {
            $tax_terms[]=array(
                'name'=>$term->name,
                'count'=>$term->count,
                'parent'=>$term->parent,
                'term_taxonomy_id'=>$term->term_taxonomy_id,
                'term_id'=>$term->term_id
            );
        }
        return $tax_terms;
    }

    private function buildSelect()
    {
        $attributes = $this->getAttr();
        
        $multiple = !isset($attributes['single_select']) || !$attributes['single_select'];
        
        $curr_options = $this->getOptions();
        $values = $this->valuesId;
        $options = array();
        foreach ($curr_options as $name=>$data) {
            $option = array(
                '#value' => $name,
                '#title' => $data['value'],
                '#attributes' => array('data-parent' => $data['parent'])
            );
            if ($multiple && in_array($name, $values)) {
                $option['#attributes']['selected'] = '';
            }
            
            $options[] = $option;
        }
        /**
         * default_value
         */
        $default_value = null;
        if ( count( $this->valuesId) ) {
            $default_value = $this->valuesId[0];
        }
        /**
         * form settings
         */
        $form = array();
        $select = array(
            '#type' => 'select',
            '#title' => $this->getTitle(),
            '#description' => $this->getDescription(),
            '#name' => $this->getName() . '[]',
            '#options' => $options,
            '#default_value' => isset( $data['default_value'] ) && !empty( $data['default_value'] ) ? $data['default_value'] : $default_value,
            '#validate' => $this->getValidationData(),
            '#class' => 'form-inline',
            '#repetitive' => $this->isRepetitive(),
        );
        
        if ($multiple) {
            $select['#attributes'] = array('multiple' => 'multiple');
        }
        
        $form[] = $select;

        return $form;
    }

    private function getOptions($index = 0, $level = 0, $parent = -1)
    {
        if ( !isset($this->childs[$index]) || empty( $this->childs[$index] ) ) {
            return;
        }
        $options = array();

        foreach( $this->childs[$index] as $one ) {
            $options[$one] = array('value' => sprintf('%s%s', str_repeat('&nbsp;', 2*$level ), $this->names[$one]),
                                   'parent' => $parent);
            if ( isset($this->childs[$one]) && count($this->childs[$one])) {
                foreach( $this->getOptions( $one, $level + 1, $one ) as $id => $data ) {
                    $options[$id] = $data;
                }
            }
        }
        return $options;
    }

    private function buildCheckboxes($index, &$childs, &$names, &$metaform, $level = 0, $parent = -1)
    {

        if (isset($childs[$index])) {
            foreach ($childs[$index] as $tid) {
                $name = $names[$tid];
                /**
                 * check for "checked"
                 */
                $default_value = false;
                if ( isset( $this->valuesId ) && is_array( $this->valuesId ) && !empty($this->valuesId)) {
                    $default_value = in_array( $tid, $this->valuesId );
                } else if ( is_array( $this->getValue() ) ) {
                    $default_value = in_array( $tid, $this->getValue() );
                }
                $item = array(
                            '#type' => 'checkbox',
                            '#title' => $names[$tid],
                            '#description' => '',
                            '#name' => $this->getName()."[]",
                            '#value' => $tid,
                            '#default_value' => $default_value,
                            '#validate' => $this->getValidationData(),
                            '#after' => '<br />',
                            '#attributes' => array(
                                'data-parent' => $parent
                            ),
                        );
                
                if ($level > 0) {
                    $margin = $level * 15;
                    $item['#attributes']['style'] = 'margin-left:' . $margin . 'px';
                }
                
                $metaform[] = $item;

                if (isset($childs[$tid])) {
                    $metaform = $this->buildCheckboxes($tid,$childs,$names, $metaform, $level+1, $tid);
                }

            }
        }
        return $metaform;
    }
}
