<?php

class IsAmAre_CustomContact_Block_Adminhtml_Cms_Block_Grid extends Mage_Adminhtml_Block_Cms_Block_Grid
{
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('cms/block')->getCollection();
        /* @var $collection Mage_Cms_Model_Mysql4_Block_Collection */

        $admin_user_session = Mage::getSingleton('admin/session');
        $adminuserId = $admin_user_session->getUser()->getUserId();
        $role_data = Mage::getModel('admin/user')->load($adminuserId)->getRole()->getData();
        if ($role_data['role_id'] != 1) {
            $collection->addFieldToFilter('identifier', array(
                'in' => array(
                    'job-apply',
                    'note_less_3hours',
                    'note_less_3hours_tomorrow',
                    'note_after_18',
                    'contact-us'
                )
            ));

        }

        $this->setCollection($collection);

        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }
}
			