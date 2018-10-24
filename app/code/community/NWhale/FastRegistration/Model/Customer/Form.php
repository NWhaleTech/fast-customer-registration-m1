<?php
/**
 * @author NWhale Team
 * @copyright Copyright (c) 2018 NWhale (https://www.nwhaletech.com)
 * @package NWhale_FastRegistration
 */

class NWhale_FastRegistration_Model_Customer_Form extends Mage_Customer_Model_Form
{
    /** @var array  */
    protected $_required = array();

    /**
     * @param array $value
     */
    public function setRequired($value)
    {
        $this->_required = $value;
    }

    /**
     * @return array
     */
    public function getRequired()
    {
        return $this->_required;
    }
    /**
     * @param Zend_Controller_Request_Http $request
     * @param null $scope
     * @param bool $scopeOnly
     * @return array
     */
    public function extractData(Zend_Controller_Request_Http $request, $scope = null, $scopeOnly = true)
    {
        $result = parent::extractData($request, $scope, $scopeOnly);
        $result = array_intersect_key($result, array_flip($this->getRequired()));
        return $result;
    }

    /**
     * Validate data array and return true or array of errors
     *
     * @param array $data
     * @return boolean|array
     */
    public function validateData(array $data)
    {
        $errors = array();
        foreach ($this->getAttributes() as $attribute) {
            if (!in_array($attribute->getAttributeCode(), $this->getRequired()) || $this->_isAttributeOmitted($attribute)) {
                continue;
            }
            $dataModel = $this->_getAttributeDataModel($attribute);
            $dataModel->setExtractedData($data);
            if (!isset($data[$attribute->getAttributeCode()])) {
                $data[$attribute->getAttributeCode()] = null;
            }
            $result = $dataModel->validateValue($data[$attribute->getAttributeCode()]);
            if ($result !== true) {
                $errors = array_merge($errors, $result);
            }
        }

        if (count($errors) == 0) {
            return true;
        }

        return $errors;
    }
}