<?php
namespace TYPO3\Fluid\ViewHelpers\Form;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */


/**
 * View Helper which creates a simple checkbox (<input type="checkbox">).
 *
 * = Examples =
 *
 * <code title="Example">
 * <f:form.checkbox name="myCheckBox" value="someValue" />
 * </code>
 * <output>
 * <input type="checkbox" name="myCheckBox" value="someValue" />
 * </output>
 *
 * <code title="Preselect">
 * <f:form.checkbox name="myCheckBox" value="someValue" checked="{object.value} == 5" />
 * </code>
 * <output>
 * <input type="checkbox" name="myCheckBox" value="someValue" checked="checked" />
 * (depending on $object)
 * </output>
 *
 * <code title="Bind to object property">
 * <f:form.checkbox property="interests" value="TYPO3" />
 * </code>
 * <output>
 * <input type="checkbox" name="user[interests][]" value="TYPO3" checked="checked" />
 * (depending on property "interests")
 * </output>
 *
 * @api
 */
class CheckboxViewHelper extends \TYPO3\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper {

	/**
	 * @var string
	 */
	protected $tagName = 'input';

	/**
	 * Initialize the arguments.
	 *
	 * @return void
	 * @api
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerTagAttribute('disabled', 'string', 'Specifies that the input element should be disabled when the page loads');
		$this->registerArgument('errorClass', 'string', 'CSS class to set if there are errors for this view helper', FALSE, 'f3-form-error');
		$this->overrideArgument('value', 'string', 'Value of input tag. Required for checkboxes', TRUE);
		$this->registerUniversalTagAttributes();
	}

	/**
	 * Renders the checkbox.
	 *
	 * @param boolean $checked Specifies that the input element should be preselected
	 * @param boolean $multiple Specifies whether this checkbox belongs to a multivalue (is part of a checkbox group)
	 *
	 * @return string
	 * @api
	 */
	public function render($checked = NULL, $multiple = NULL) {
		$this->tag->addAttribute('type', 'checkbox');

		$nameAttribute = $this->getName();
		$valueAttribute = $this->getValue();
		if ($this->isObjectAccessorMode()) {
			$selectedValue = $this->getSelectedValues();
			if ($checked === NULL) {
				if (is_array($selectedValue)) {
					$checked = in_array($valueAttribute, $selectedValue);
				} elseif ($selectedValue !== NULL) {
					$checked = ((boolean)$selectedValue === (boolean)$valueAttribute || $selectedValue == $valueAttribute);
				}
			}
			
			if (is_array($selectedValue) || $multiple === TRUE) {
				$nameAttribute .= '[]';
				$nameAttribute = preg_replace('/\[__identity\]\[\]$/', '[][__identity]', $nameAttribute);
			}
		}

		$this->registerFieldNameForFormTokenGeneration($nameAttribute);
		$this->tag->addAttribute('name', $nameAttribute);
		$this->tag->addAttribute('value', $valueAttribute);
		if ($checked) {
			$this->tag->addAttribute('checked', 'checked');
		}

		$this->setErrorClassAttribute();

		//$hiddenField = $this->renderHiddenFieldForEmptyValue();
		return $this->tag->render();
	}

	/**
	 * Retrieves the selected value(s)
	 *
	 * @return mixed value string or an array of strings
	 */
	protected function getSelectedValues() {
		$value = $this->getPropertyValue();
		if (!$value) {
			return NULL;
		}
		
		if (!is_array($value) && !($value instanceof  \Traversable)) {
			return $this->getOptionValueScalar($value);
		}
		$selectedValues = array();
		foreach($value as $selectedValueElement) {
			$selectedValues[] = $this->getOptionValueScalar($selectedValueElement);
		}
		return $selectedValues;
	}

	/**
	 * Get the option value for an object
	 *
	 * @param mixed $valueElement
	 * @return string
	 */
	protected function getOptionValueScalar($valueElement) {
		if (is_object($valueElement)) {
			if ($this->hasArgument('optionValueField')) {
				return \TYPO3\FLOW3\Reflection\ObjectAccess::getPropertyPath($valueElement, $this->arguments['optionValueField']);
			} else if ($this->persistenceManager->getIdentifierByObject($valueElement) !== NULL){
				return $this->persistenceManager->getIdentifierByObject($valueElement);
			} else {
				return (string)$valueElement;
			}
		} else {
			return $valueElement;
		}
	}
}

?>