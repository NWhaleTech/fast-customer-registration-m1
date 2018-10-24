<?php
/**
 * @author NWhale Team
 * @copyright Copyright (c) 2018 NWhale (https://www.nwhaletech.com)
 * @package NWhale_FastRegistration
 */

require_once(Mage::getModuleDir('controllers', 'Mage_Customer') . DS . 'AccountController.php');

class NWhale_FastRegistration_AccountController extends Mage_Customer_AccountController
{
    /**
     * Validate customer data and return errors if they are
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return array|string
     */
    protected function _getCustomerErrors($customer)
    {
        $errors = array();
        $request = $this->getRequest();
        if ($request->getPost('create_address')) {
            $errors = $this->_getErrorsOnCustomerAddress($customer);
        }
        $customerForm = $this->_getCustomerForm($customer);
        $customerData = $customerForm->extractData($request);
        $customerErrors = $customerForm->validateData($customerData);
        if ($customerErrors !== true) {
            $errors = array_merge($customerErrors, $errors);
        } else {
            $customerForm->compactData($customerData);
            $customer->setPassword($request->getPost('password'));
            $customer->setPasswordConfirmation($request->getPost('confirmation'));
            $customerErrors = $this->validateCustomer($customer, $customerData);
            if (is_array($customerErrors)) {
                $errors = array_merge($customerErrors, $errors);
            }
        }
        return $errors;
    }

    /**
     * Get Customer Form Initalized Model
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return NWhale_FastRegistration_Model_Customer_Form
     */
    protected function _getCustomerForm($customer)
    {
        /* @var $customerForm NWhale_FastRegistration_Model_Customer_Form */
        $customerForm = $this->_getModel('nwhale_fast_registration/customer_form');
        $customerForm->setFormCode('customer_account_create');
        $customerForm->setEntity($customer);
        $customerForm->setRequired(array('email'));

        return $customerForm;
    }

    /**
     * @param $customer
     * @param $customerData
     * @return array|bool
     */
    protected function validateCustomer($customer, $customerData)
    {
        $errors = array();
        if (isset($customerData['first_name'])) {
            if (!Zend_Validate::is(trim($customer->getFirstname()), 'NotEmpty')) {
                $errors[] = Mage::helper('customer')->__('The first name cannot be empty.');
            }
        }

        if (isset($customerData['last_name'])) {
            if (!Zend_Validate::is(trim($customer->getLastname()), 'NotEmpty')) {
                $errors[] = Mage::helper('customer')->__('The last name cannot be empty.');
            }
        }

        if (isset($customerData['email'])) {
            if (!Zend_Validate::is($customer->getEmail(), 'EmailAddress')) {
                $errors[] = Mage::helper('customer')->__('Invalid email address "%s".', $customer->getEmail());
            }
        }

        $password = $customer->getPassword();
        if (!$customer->getId() && !Zend_Validate::is($password , 'NotEmpty')) {
            $errors[] = Mage::helper('customer')->__('The password cannot be empty.');
        }
        if (strlen($password) && !Zend_Validate::is($password, 'StringLength', array(Mage_Customer_Model_Customer::MINIMUM_PASSWORD_LENGTH))) {
            $errors[] = Mage::helper('customer')
                ->__('The minimum password length is %s', Mage_Customer_Model_Customer::MINIMUM_PASSWORD_LENGTH);
        }
        $confirmation = $customer->getPasswordConfirmation();
        if ($password != $confirmation) {
            $errors[] = Mage::helper('customer')->__('Please make sure your passwords match.');
        }

        if (isset($customerData['dob'])) {
            $entityType = Mage::getSingleton('eav/config')->getEntityType('customer');
            $attribute = Mage::getModel('customer/attribute')->loadByCode($entityType, 'dob');
            if ($attribute->getIsRequired() && '' == trim($customer->getDob())) {
                $errors[] = Mage::helper('customer')->__('The Date of Birth is required.');
            }
        }

        if (isset($customerData['taxvat'])) {
            $attribute = Mage::getModel('customer/attribute')->loadByCode($entityType, 'taxvat');
            if ($attribute->getIsRequired() && '' == trim($customer->getTaxvat())) {
                $errors[] = Mage::helper('customer')->__('The TAX/VAT number is required.');
            }
        }

        if (isset($customerData['gender'])) {
            $attribute = Mage::getModel('customer/attribute')->loadByCode($entityType, 'gender');
            if ($attribute->getIsRequired() && '' == trim($customer->getGender())) {
                $errors[] = Mage::helper('customer')->__('Gender is required.');
            }
        }

        if (empty($errors)) {
            return true;
        }
        return $errors;
    }
}