<?php
/**
 * @author Ingo Schommer, SilverStripe Ltd. (<firstname>@silverstripe.com)
 * 
 * @package sapphire
 * @subpackage fields-formattedinput
 */
class MoneyField extends FormField {
	
	/**
	 * @var string $_locale
	 */
	protected $_locale;
	
	/**
	 * Limit the currencies
	 * @var array $allowedCurrencies
	 */
	protected $allowedCurrencies;
	
	/**
	 * @var FormField
	 */
	protected $fieldAmount = null;
	
	/**
	 * @var FormField
	 */
	protected $fieldCurrency = null;
	
	function __construct($name, $title = null, $value = "", $form = null) {
		parent::__construct($name, $title, $value, $form);
		
		// naming with underscores to prevent values from actually being saved somewhere
		$this->fieldAmount = new NumericField("{$name}[Amount]", _t('MoneyField.FIELDLABELAMOUNT', 'Amount'));
		$this->fieldCurrency = $this->FieldCurrency();
		$this->setValue($value);
	}
	
	/**
	 * @return string
	 */
	function Field() {
		return "<div class=\"fieldgroup\">" .
			"<div class=\"fieldgroupField\">" . $this->fieldCurrency->SmallFieldHolder() . "</div>" . 
			"<div class=\"fieldgroupField\">" . $this->fieldAmount->SmallFieldHolder() . "</div>" . 
		"</div>";
	}
	
	/**
	 * @return FormField
	 */
	protected function FieldCurrency() {
		$allowedCurrencies = $this->getAllowedCurrencies();
		if($allowedCurrencies) {
			$field = new DropdownField(
				"{$this->name}[Currency]", 
				_t('MoneyField.FIELDLABELCURRENCY', 'Currency'),
				array_combine($allowedCurrencies,$allowedCurrencies)
			);
		} else {
			$field = new TextField(
				"{$this->name}[Currency]", 
				_t('MoneyField.FIELDLABELCURRENCY', 'Currency')
			);
		}
		
		return $field;
	}
	
	function setValue($val) {
		$this->value = $val;

		if(is_array($val)) {
			$this->fieldCurrency->setValue($val['Currency']);
			$this->fieldAmount->setValue($val['Amount']);
		} elseif($val instanceof Money) {
			$this->fieldCurrency->setValue($val->getCurrency());
			$this->fieldAmount->setValue($val->getAmount());
		}
	}
	
	function saveInto($dataObject) {
		$fieldName = $this->name;
		$dataObject->$fieldName->setCurrency($this->fieldCurrency->Value()); 
		$dataObject->$fieldName->setAmount($this->fieldAmount->Value());
	}

	/**
	 * Returns a readonly version of this field.
	 */
	function performReadonlyTransformation() {
		$clone = clone $this;
		$clone->setReadonly(true);
		return $clone;
	}
	
	/**
	 * @todo Implement removal of readonly state with $bool=false
	 * @todo Set readonly state whenever field is recreated, e.g. in setAllowedCurrencies()
	 */
	function setReadonly($bool) {
		parent::setReadonly($bool);
		
		if($bool) {
			$this->fieldAmount = $this->fieldAmount->performReadonlyTransformation();
			$this->fieldCurrency = $this->fieldCurrency->performReadonlyTransformation();
		}
	}
	
	/**
	 * @param array $arr
	 */
	function setAllowedCurrencies($arr) {
		$this->allowedCurrencies = $arr;
		
		// @todo Has to be done twice in case allowed currencies changed since construction
		$oldVal = $this->fieldCurrency->Value();
		$this->fieldCurrency = $this->FieldCurrency();
		$this->fieldCurrency->setValue($oldVal);
	}
	
	/**
	 * @return array
	 */
	function getAllowedCurrencies() {
		return $this->allowedCurrencies;
	}
	
	function setLocale($locale) {
		$this->_locale = $locale;
		$this->fieldAmount->setLocale($locale);
	}
	
	function getLocale() {
		return $this->_locale;
	}
}
?>