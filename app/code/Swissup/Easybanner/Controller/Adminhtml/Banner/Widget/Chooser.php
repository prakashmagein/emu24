<?php
namespace Swissup\Easybanner\Controller\Adminhtml\Banner\Widget;

class Chooser extends \Swissup\Easybanner\Controller\Adminhtml\Banner\Widget
{
    /**
     * Prepare block for chooser
     *
     * @return void
     */
    public function execute()
    {
        $request = $this->getRequest();
        switch ($request->getParam('attribute')) {
            case 'product_ids':
                $ids = $request->getParam('selected', []);
                if (is_array($ids)) {
                    foreach ($ids as $key => &$id) {
                        $id = (int)$id;
                        if ($id <= 0) {
                            unset($ids[$key]);
                        }
                    }

                    $ids = array_unique($ids);
                } else {
                    $ids = [];
                }

                $block = $this->_view->getLayout()->createBlock(
                    'Swissup\Easybanner\Block\Adminhtml\Banner\Chooser\Product',
                    'promo_widget_chooser_product_ids',
                    ['data' => ['js_form_object' => $request->getParam('form')]]
                )->setSelected(
                    $ids
                );
                break;

            case 'customer_group':
                $ids = $request->getParam('selected', []);
                if (!is_array($ids)) {
                    $ids = [];
                }

                $block = $this->_view->getLayout()->createBlock(
                    'Swissup\Easybanner\Block\Adminhtml\Banner\Chooser\CustomerGroup',
                    'promo_widget_chooser_customer_group',
                    ['data' => ['js_form_object' => $request->getParam('form')]]
                )->setSelected(
                    $ids
                );
                break;
            case 'handle':
                $ids = $request->getParam('selected', []);
                if (is_array($ids)) {
                    foreach ($ids as $key => &$id) {
                        $id = (int)$id;
                        if ($id <= 0) {
                            unset($ids[$key]);
                        }
                    }

                    $ids = array_unique($ids);
                } else {
                    $ids = [];
                }

                $block = $this->_view->getLayout()->createBlock(
                    'Swissup\Easybanner\Block\Adminhtml\Banner\Chooser\Handle',
                    'promo_widget_chooser_handles',
                    ['data' => ['js_form_object' => $request->getParam('form')]]
                )->setSelected(
                    $ids
                );
                break;

            default:
                $block = false;
                break;
        }

        if ($block) {
            $this->getResponse()->setBody($block->toHtml());
        }
    }
}
