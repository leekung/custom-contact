<?php
require_once "Mage/Contacts/controllers/IndexController.php";
class IsAmAre_CustomContact_Contacts_IndexController extends Mage_Contacts_IndexController{
    public function postAction()
    {
        $post = $this->getRequest()->getPost();
        if ( $post ) {
            $translate = Mage::getSingleton('core/translate');
            /* @var $translate Mage_Core_Model_Translate */
            $translate->setTranslateInline(false);
            try {
                $postObject = new Varien_Object();
                $postObject->setData($post);

                $error = 0;

                if (!Zend_Validate::is(trim($post['name']) , 'NotEmpty')) {
                    $error = 1;
                }

                if (!Zend_Validate::is(trim($post['comment']) , 'NotEmpty')) {
                    $error = 2;
                }

                if (!Zend_Validate::is(trim($post['telephone']) , 'NotEmpty')) {
                    $error = 3;
                }

                if (!Zend_Validate::is(trim($post['email']), 'EmailAddress')) {
                    $error = 4;
                }

                if (Zend_Validate::is(trim($post['hideit']), 'NotEmpty')) {
                    $error = 5;
                }
                $attachmentFilePath = '';
                if (isset($_FILES['attachment']['name']) && $_FILES['attachment']['name'] != '') {
                    try {
                        $fileName = $_FILES['attachment']['name'];
                        $pathinfo = pathinfo($fileName);
                        $fileExt = $pathinfo['extension'];
                        $fileNameOnly    = $pathinfo['filename'];
                        $fileName       = preg_replace('/\s+/', '', $fileNameOnly) . time() . '.' . $fileExt;

                        $uploader       = new Varien_File_Uploader('attachment');
                        $uploader->setAllowedExtensions(array('jpg', 'png', 'pdf', 'doc', 'docx'));
                        $uploader->setAllowRenameFiles(false);
                        $uploader->setFilesDispersion(false);
                        $path = Mage::getBaseDir('media') . DS . 'contacts';
                        if(!is_dir($path)){
                            mkdir($path, 0777, true);
                        }
                        $uploader->save($path . DS, $fileName );
                        $attachmentFilePath = Mage::getBaseDir('media'). DS . 'contacts' . DS . $fileName;

                    } catch (Exception $e) {
                        $error = 6;
                    }
                }
                if ($error) {
                    throw new Exception();
                }

                $mailTemplate = Mage::getModel('core/email_template');

                if(!empty($attachmentFilePath) && file_exists($attachmentFilePath)){
                    $fileContents = file_get_contents($attachmentFilePath);
                    $attachment   = $mailTemplate->getMail()->createAttachment($fileContents);
                    $attachment->filename = $fileName;
                }
                //Hard code email
                $recipient = Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT);
                if($post['subject'] == Mage::helper('contacts')->__('Job apply')) {
                    $recipient = Mage::getStoreConfig('isamare/isamare_group/contact_email');
                }

                /* @var $mailTemplate Mage_Core_Model_Email_Template */
                $mailTemplate->setDesignConfig(array('area' => 'frontend'))
                    ->setReplyTo($post['email'])
                    ->sendTransactional(
                        Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE),
                        Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER),
                        $recipient,
                        null,
                        array('data' => $postObject)
                    );

                if (!$mailTemplate->getSentSuccess()) {
                    throw new Exception();
                }

                $translate->setTranslateInline(true);

                Mage::getSingleton('customer/session')->addSuccess(Mage::helper('contacts')->__('Your inquiry was submitted and will be responded to as soon as possible. Thank you for contacting us.'));
                $this->_redirect('*/*/');

                return;
            } catch (Exception $e) {
                $translate->setTranslateInline(true);

                Mage::getSingleton('customer/session')->addError(Mage::helper('contacts')->__('Unable to submit your request. Please, try again later.'));
                $this->_redirect('*/*/');
                return;
            }

        } else {
            $this->_redirect('*/*/');
        }
    }

}
				