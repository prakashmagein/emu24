<?php

namespace Swissup\Navigationpro\Block\Adminhtml\Menu\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class Settings extends Generic
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Menu Settings'),
            'class' => 'action-secondary',
            'data_attribute' => [
                'mage-init' => [
                    'Magento_Ui/js/form/button-adapter' => [
                        'actions' => [
                            [
                                'targetName' => 'navigationpro_menu_item.navigationpro_menu_item.menu_settings_modal',
                                'actionName' => 'toggleModal'
                            ],
                            [
                                'targetName' => 'navigationpro_menu_item.navigationpro_menu_item.menu_settings_modal.menu_settings_form',
                                'actionName' => 'render'
                            ]
                        ]
                    ]
                ]
            ],
            'on_click' => '',
            'sort_order' => 30
        ];
    }
}
