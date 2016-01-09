<?php
$installer = $this;
$installer->startSetup();

$emailTemplate = Mage::getModel('core/email_template');
$emailTemplate
    ->setAddedAt(date('Y-m-d H:i:s'))
    ->setTemplateCode('PO Invoice Reminder Email')
    ->setTemplateStyles('body,td { color:#2f2f2f; font:11px/1.35em Verdana, Arial, Helvetica, sans-serif; }')
    ->setTemplateType(2)
    ->setTemplateSubject('{{var store.getFrontendName()}}: New Order # {{var order.increment_id}}')
    ->setOrigTemplateCode('invoice_reminder_email_template')
    ->setOrigTemplateVariables('{"store url=\"\"":"Store Url","skin url=\"images/logo_email.gif\" _area=\'frontend\'":"Email Logo Image"}')
    ->setTemplateText('<div style="font:11px/1.35em Verdana, Arial, Helvetica, sans-serif;">
            <table cellspacing="0" cellpadding="0" border="0" width="98%" style="margin-top:10px; font:11px/1.35em Verdana, Arial, Helvetica, sans-serif; margin-bottom:10px;">
            <tr>
                <td align="center" valign="top">
                    <!-- [ header starts here] -->
                    <table cellspacing="0" cellpadding="0" border="0" width="650">
                        <tr>
                            <td valign="top"><a href="{{store url=""}}"><img src="{{skin url="images/logo_email.gif" _area=\'frontend\'}}" alt="{{var store.getFrontendName()}}"  style="margin-bottom:10px;" border="0"/></a></td>
                        </tr>
                    </table>
                    <!-- [ middle starts here] -->
                    <table cellspacing="0" cellpadding="0" border="0" width="650">
                        <tr>
                            <td valign="top">
                                <p>
                                    <strong>Hello {{htmlescape var=$order.getCustomerName()}}</strong>,<br/>
                                    The attached invoice still shows unpaid from {{var store.getFrontendName()}}.
                                    Please check the status of payment for the attached order. If you have any questions about your order or PO Invoice please contact us at <a href="mailto:{{config path=\'trans_email/ident_support/email\'}}" style="color:#1E7EC8;">{{config path=\'trans_email/ident_support/email\'}}</a> or call us at <span class="nobr">{{config path=\'general/store_information/phone\'}}</span>.
                                </p>
                                <p>The order invoice is attached to this email as a PDF file.</p>
                                <p>Thank you again,<br/><strong>{{var store.getFrontendName()}}</strong></p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            </table>
        </div>')
    ->save();

$installer->setConfigData('accountreceivable/po_invoice_notification', $emailTemplate->getId());
$installer->endSetup();
