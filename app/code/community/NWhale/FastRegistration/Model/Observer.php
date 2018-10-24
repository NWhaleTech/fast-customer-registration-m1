<?php
/**
 * @author NWhale Team
 * @copyright Copyright (c) 2018 NWhale (https://www.nwhaletech.com)
 * @package NWhale_FastRegistration
 */

class NWhale_FastRegistration_Model_Observer
{
    public function removeExtraFields($observer)
    {
        if ($observer->getBlock() instanceof Mage_Customer_Block_Form_Register) {
            $html = $observer->getTransport()->getHtml();
            $dom = new DOMDocument();
            $dom->loadHTML($html);
            $xPath = new DOMXPath($dom);

            $nodes = $xPath->query('//input[@type!="password"]');
            for($i = 0; $i < $nodes->length; $i++) {
                $item = $nodes->item($i);
                if (!in_array($item->getAttribute('name'), array('persistent_remember_me', 'success_url', 'error_url', 'form_key', 'email'))) {
                    if (!in_array($item->getAttribute('rel'), array('trattamento_dati2'))) {
                        $item->parentNode->removeChild($nodes->item($i));
                    }
                }
            }

            $nodes = $xPath->query('//label');
            for($i = 0; $i < $nodes->length; $i++) {
                $item = $nodes->item($i);
                if (!in_array($item->getAttribute('for'), array('trattamento_dati2', 'email_address', 'password', 'confirmation'))) {
                    if (strpos($item->getAttribute('for'), 'remember_me') === false) {
                        $item->parentNode->removeChild($item);
                    }
                }
            }

            $nodes = $xPath->query('//select');
            for($i = 0; $i < $nodes->length; $i++) {
                $item = $nodes->item($i);
                if (!in_array($item->getAttribute('id'), array('email_address', 'password', 'confirmation'))) {
                    $item->parentNode->removeChild($item);
                }
            }

            $item = $xPath->query('//div[@class="customer-name-middlename"]')->item(0);
            if ($item) {
                $item->parentNode->removeChild($item);
            }

            $style = '<style>.account-create form>.fieldset:nth-child(2) {display:none}</style>';

            $observer->getTransport()->setHtml($dom->saveHTML() . $style);
        }
    }
}