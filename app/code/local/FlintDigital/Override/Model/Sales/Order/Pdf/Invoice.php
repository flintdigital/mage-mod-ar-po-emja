<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sales Order Invoice PDF model
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class FlintDigital_Override_Model_Sales_Order_Pdf_Invoice extends Mage_Sales_Model_Order_Pdf_Invoice {

    protected function insertLogo(&$page, $store = null) {
        $this->y = $this->y ? $this->y : 815;
//		$image = Mage::getSingleton('core/design_package')->getSkinBaseDir(array('_area' => 'frontend')).'/images/logo_print.png';
        $image = Mage::getBaseDir('media') . '/flintdigital/override/logo_print.png';
//                var_dump($image);die();

        if ($image && is_file($image)) {
            $image = Zend_Pdf_Image::imageWithPath($image);
            $top = 830; //top border of the page
            $widthLimit = 270; //half of the page width
            $heightLimit = 270; //assuming the image is not a "skyscraper"
            $width = $image->getPixelWidth();
            $height = $image->getPixelHeight();

            $y1 = $top - $height;
            $y2 = $top;
            $x1 = 25;
            $x2 = $x1 + $width;

            //coordinates after transformation are rounded by Zend
            $page->drawImage($image, $x1, $y1, $x2, $y2);

            $this->y = $y1 - 10;
        }
    }

    protected function insertHeader(&$page, $order, $invoice, $addComments) {
        $this->y -= 35;

//     	$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
//     	$page->setLineWidth(0);
        ///////////////////////
        //Adds Hello Customer
        ///////////////////////
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
        $_value = "Hello " . $customer->getData('business');
        $this->_echo($page, $_value, $this->y, 20, 20);

        ///////////////////////
        //Adds Invoice and Order Id
        ///////////////////////
        $this->y -= 23;
        $_value = "Invoice #" . $invoice->getIncrementId() . " for Order #" . $order->getIncrementId();
        $this->_echo($page, $_value, $this->y, 20, 15);


        ///////////////////////
        //Adds Invoice Comments if any
        ///////////////////////
        if ($addComments) {
            $comments = Mage::getResourceModel('sales/order_invoice_comment_collection')
                    ->addAttributeToSelect('comment')
                    ->setInvoiceFilter($invoice->getId())
                    ->load();

            if (!empty($comments)) {
                foreach ($comments as $comment) {
                    $this->y -= 18;
                    $_value = $comment->getComment();
                    $this->_echo($page, $_value, $this->y, 20);
                }
            }
        }


        ///////////////////////
        //Adds M7 Address Line
        ///////////////////////
        $this->y -= 20;
        $_value = "Please Remit Payment To: ";
        $this->_echo($page, $_value, $this->y, 20, 11, TRUE);
        $_value = Mage::getStoreConfig('general/store_information/address');
        $this->_echo($page, $_value, $this->y, 150, 11);


        ///////////////////////
        //Adds M7 Email
        ///////////////////////
        $this->y -= 23;
        $email = Mage::getStoreConfig('trans_email/ident_support/email');
        $_value = "If you have any questions regarding your order or this invoice contact Method Seven at $email or call";
        $this->_echo($page, $_value, $this->y, 20, 10);


        ///////////////////////
        //Adds M7 Phone
        ///////////////////////
        $this->y -= 12;
        $phone = Mage::getStoreConfig('general/store_information/phone');
        $_value = "us at $phone Monday - Friday, 8am - 5pm PST.";

        $this->_echo($page, $_value, $this->y, 20);
    }

    protected function insertOrderData($page, $order) {
        $billingAddress = $order->getBillingAddress();
        $bData = $billingAddress->getData();
        $data = array(
            $billingAddress->getFirstname() . ' ' . $billingAddress->getLastname(),
            ucfirst($bData['street']),
            $billingAddress->getCity() . ', ' . $billingAddress->getRegion() . ', ' . $billingAddress->getPostcode(),
            Mage::app()->getLocale()->getCountryTranslation($billingAddress->getCountry()),
            'T: ' . $billingAddress->getTelephone(),
        );
        $this->insertInfoDiv($page, "Billing Information", $data, $this->y, 20);

        $paymentMethod = $order->getPayment()->getMethod();
        $data = array($order->getPayment()->getMethodInstance()->getTitle());
        if ($paymentMethod = 'purchaseorder') {
            $data[] = 'Purchase Order Number: ' . $order->getPayment()->getPoNumber();
        }

        $this->insertInfoDiv($page, "Payment Method", $data, $this->y, 300);

        $this->y -= 125;

        $shippingAddress = $order->getShippingAddress();
        $sData = $shippingAddress->getData();
        $data = array(
            $shippingAddress->getFirstname() . ' ' . $shippingAddress->getLastname(),
            ucfirst($sData['street']),
            $shippingAddress->getCity() . ', ' . $shippingAddress->getRegion() . ', ' . $shippingAddress->getPostcode(),
            Mage::app()->getLocale()->getCountryTranslation($shippingAddress->getCountry()),
            'T: ' . $shippingAddress->getTelephone(),
        );
        $this->insertInfoDiv($page, "Shipping Information", $data, $this->y, 20);

        $data = array($order->getShippingDescription());
        $this->insertInfoDiv($page, "Shipping Method", $data, $this->y, 300);

        $this->y -= 125;
    }

    protected function insertInfoDiv($page, $title, $bodyLines, $top, $left) {
        $width = 250;
        $bodyLineHeight = 15;
        $textPadding = 13;
        $headerHeight = 20;
        $height = 100;

        //Draws Border
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.9));
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle($left, $top, $left + $width, $top - $height);

        //Fills table header
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.9));
        $page->drawRectangle($left, $top, $left + $width, $top - $headerHeight);

        //Set text color
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));

        //Adds Title to table header
        $this->_echo($page, $title, $top - $textPadding - 2, $left + $textPadding, 13, TRUE);
        $top -= $headerHeight;

        //Adds the body lines
        $bodyLines = is_array($bodyLines) ? $bodyLines : array($bodyLines);
        foreach ($bodyLines as $k => $bodyLine) {
            $topLine = ($k + 1) * $bodyLineHeight;
            $this->_echo($page, $bodyLine, $top - $topLine, $left + $textPadding, 10);
        }
    }

    protected function addOrderItems($page, $invoice, $order) {
        $items = array();
        $width = 530;
        $lineHeight = 25;
        $subLineHeight = 15;
        $headerHeight = 20;
        $totalsHeight = 50;
        $top = $this->y;
        $textPadding = 13;
        $left = 20;
        $colPaddings = array(0, 175, 350, 425);
        $headers = array('Item', 'Sku', 'Qty', 'Subtotal');

        $itemQtys = array();

        foreach ($invoice->getAllItems() as $invoiceItem) {
            if ($invoiceItem->getParentItem()) {
                continue;
            }

            $itemQtys[$invoiceItem->getSku()] = $invoiceItem->getQty();
        }

        //Gets the items data needed. Done at this point to get the table height
        foreach ($order->getAllItems() as $orderItem) {
            if ($orderItem->getParentItem()) {
                continue;
            }



            $options = $orderItem->getProductOptions();
            $options = $options['attributes_info'];

            $name = array($orderItem->getName(),);

            foreach ($options as $option) {
                $name[] = $option['label'];
                $name[] = array_key_exists('print_value', $option) ? $option['print_value'] : $option['value'];
            }


            $item = array(
                $name,
                $orderItem->getSku(),
                number_format($itemQtys[$orderItem->getSku()], 0),
                '$' . number_format($orderItem->getRowTotalInclTax(), 2)
            );

            $items[] = array_combine($headers, $item);
        }

        $itemsHeight = 0;
        foreach ($items as $item) {
            $itemsHeight += count($item['Item']) == 1 ? $lineHeight : $subLineHeight * count($item['Item']);
        }
        $height = $itemsHeight + $headerHeight + $totalsHeight;

        //Table border
//     	$page->setLineColor(new Zend_Pdf_Color_GrayScale(0.9));
//     	$page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
//     	$page->drawRectangle($left, $top, $left+$width, $top-$height);

        $left++;
        $width -= 2;

        //Fills Header
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.9));
        $page->drawRectangle($left, $top, $left + $width, $top - $headerHeight);

        //Sets Font Color
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));

        //Adds Header Items
        foreach ($headers as $k => $header) {
            $leftPadding = $colPaddings[$k];
            $this->_echo($page, $header, $top - $textPadding, $left + 10 + $leftPadding, 12, TRUE);
        }

        $top -= $lineHeight - 5;

        foreach ($items as $k => $item) {
            $_height = count($item['Item']) == 1 ? $lineHeight : $subLineHeight * count($item['Item']);

            //New page: 25 is the footer size, 1 more to add a little space
            if ($top - $_height < 26) {
                $page = $this->newPage();
                $top = $this->y;

                $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.9));
                $page->setLineDashingPattern(array(1, 1), 1.6);
                $page->drawLine($left, $top + 1, $left + $width, $top + 1);
                $page->setLineDashingPattern(array());

                $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.9));
                $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
                $page->drawLine($left, $top + 1, $left, $top);
                $page->drawLine($left + $width, $top + 1, $left + $width, $top);
            }

            if ($k % 2 == 0) {
                //Fills Header
                $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.98));
                $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.98));

                $page->setLineDashingPattern(array());
                $page->drawRectangle($left + 1, $top, $left + $width - 1, $top - $_height);
            }

            //Sets Font Color
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));

            foreach ($item['Item'] as $k => $namePart) {
                $this->_echo($page, $namePart, $top - $textPadding - 2 - $k * $lineHeight / 2, $left + 10, 10);
            }

            $this->_echo($page, $item['Sku'], $top - $textPadding - 2, $left + 10 + $colPaddings[1]);
            $this->_echo($page, $item['Qty'], $top - $textPadding - 2, $left + 10 + $colPaddings[2]);
            $this->_echo($page, $item['Subtotal'], $top - $textPadding - 2, $left + 10 + $colPaddings[3]);
            $top -= $_height;

            //Table border
            $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.9));
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
            $page->drawLine($left, $top + $_height, $left, $top);
            $page->drawLine($left + $width, $top + $_height, $left + $width, $top);


            //Dotted line between items
            $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.9));
            $page->setLineDashingPattern(array(1, 1), 1.6);
            $page->drawLine($left, $top, $left + $width, $top);
            $page->setLineDashingPattern(array());
        }

//     	die('dead');
        //If totals will overlap footer, new page
        if ($top - 45 < 26) {
            $page = $this->newPage();
            $top = $this->y;
        }


        //TOTALS
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));

//     	//Subtotal
        $this->_echo($page, 'Subtotal', $top - $textPadding - 2, $left + 10 + $colPaddings[2]);
        $this->_echo($page, '$' . number_format($invoice->getSubtotal(), 2), $top - $textPadding - 2, $left + 10 + $colPaddings[3]);
        $top -=15;

//     	//Shipping
        $this->_echo($page, 'Shipping', $top - $textPadding - 2, $left + 10 + $colPaddings[2]);
        $this->_echo($page, '$' . number_format($invoice->getShippingAmount(), 2), $top - $textPadding - 2, $left + 10 + $colPaddings[3]);
        $top -=15;

        //Grand Total
        $this->_echo($page, 'Grand Total', $top - $textPadding - 2, $left + 10 + $colPaddings[2], 10, TRUE);
        $this->_echo($page, '$' . number_format($invoice->getGrandTotal(), 2), $top - $textPadding - 2, $left + 10 + $colPaddings[3]);

        return $page;
    }

    protected function insertFooter($page) {
        $top = 25;
        $left = 225;

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.9));
        $page->drawRectangle(0, $top, $page->getWidth(), 0);

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_echo($page, "Thank you again, ", $top - 15, $left, 10);
        $this->_echo($page, "Method Seven", $top - 15, $left + 73, 10, TRUE);
    }

    protected function _echo(&$page, $message, $top, $left, $size = FALSE, $bold = FALSE) {
        if ($size) {
            $font = $bold ? Zend_Pdf_Font::FONT_TIMES_BOLD : Zend_Pdf_Font::FONT_TIMES;

            $font = Zend_Pdf_Font::fontWithName($font, $size);
            $page->setFont($font, $size);
        }

        $page->drawText(trim(strip_tags($message)), $left, $top, 'UTF-8');
    }

    /**
     * Return PDF document
     *
     * @param  array $invoices
     * @return Zend_Pdf
     */
    public function getPdf($invoices = array()) {
        $addComments = FALSE;
        if (array_key_exists('comments', $invoices)) {
            $addComments = $invoices['comments'] === '1';
            unset($invoices['comments']);
        }

        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        foreach ($invoices as $invoice) {
            if ($invoice->getStoreId()) {
                Mage::app()->getLocale()->emulate($invoice->getStoreId());
                Mage::app()->setCurrentStore($invoice->getStoreId());
            }
            $page = $this->newPage();
            $order = $invoice->getOrder();

            /* Add image */
            $this->insertLogo($page, $invoice->getStore());

            /* From "Hello {Customer}" to "If you have any questions..." */
            $this->insertHeader($page, $order, $invoice, $addComments);
            $this->y -= 30;

            /* Billing & Shipping info, Payment Shipping method */
            $this->insertOrderData($page, $order);

            /* Order Items and Total */
            $page = $this->addOrderItems($page, $invoice, $order);

            /* Footer */
            $this->insertFooter($page);
        }
        $this->_afterGetPdf();
        return $pdf;
    }

}
