<?php

namespace Swissup\Navigationpro\Model\Menu\Source;

class Modifiers implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'group_themes',
                'label' => __('Theme'),
                'multiple' => true,
                'optgroup' => [
                    [
                        'value' => 'navpro-theme-air',
                        'label' => __('Air'),
                    ],
                    [
                        'value' => 'navpro-theme-compact',
                        'label' => __('Compact'),
                    ],
                    [
                        'value' => 'navpro-theme-dark',
                        'label' => __('Dark'),
                    ],
                    [
                        'value' => 'navpro-theme-dark-dropdown',
                        'label' => __('Dark Dropdowns'),
                    ],
                    [
                        'value' => 'navpro-theme-flat',
                        'label' => __('Flat'),
                    ],
                    [
                        'value' => 'navpro-slideout',
                        'label' => __('Slideout'),
                    ],
                ]
            ],
            [
                'value' => 'group_orientation',
                'label' => __('Orientation'),
                'multiple' => false,
                'optgroup' => [
                    [
                        'value' => 'navpro-horizontal',
                        'label' => __('Horizontal'),
                    ],
                    [
                        'value' => 'navpro-vertical',
                        'label' => __('Vertical'),
                    ],
                ]
            ],
            [
                'value' => 'group_misc',
                'label' => __('Miscellaneous'),
                'multiple' => true,
                'optgroup' => [
                    [
                        'value' => 'navpro-overlay',
                        'label' => __('Overlay'),
                    ],
                    [
                        'value' => 'navpro-nowrap',
                        'label' => __('Nowrap'),
                    ],
                    [
                        'value' => 'navpro-click',
                        'label' => __('Click'),
                    ],
                    [
                        'value' => 'navpro-sticky',
                        'label' => __('Sticky'),
                    ],
                    // [
                    //     'value' => 'navpro-closeable',
                    //     'label' => __('Closeable'),
                    // ],
                ]
            ],
            [
                'value' => 'group_level0_alignment',
                'label' => __('Top Level Items Justify'),
                'multiple' => false,
                'optgroup' => [
                    [
                        'value' => 'justify-start',
                        'label' => __('Left'),
                    ],
                    [
                        'value' => 'justify-end',
                        'label' => __('Right'),
                    ],
                    [
                        'value' => 'justify-center',
                        'label' => __('Center'),
                    ],
                    [
                        'value' => 'justify-between',
                        'label' => __('Space Between'),
                    ],
                    [
                        'value' => 'justify-around',
                        'label' => __('Space Around'),
                    ],
                    [
                        'value' => 'justify-evenly',
                        'label' => __('Space Evenly'),
                    ],
                ]
            ],
            [
                'value' => 'group_level0_caret',
                'label' => __('Top Level Items Caret Alignment'),
                'multiple' => false,
                'optgroup' => [
                    [
                        'value' => 'caret-hidden',
                        'label' => __('Hide caret icon'),
                    ],
                    [
                        'value' => 'caret-aside',
                        'label' => __('Caret aside name'),
                    ],
                    [
                        'value' => 'caret-bottom',
                        'label' => __('Caret below name'),
                    ],
                ]
            ],
            [
                'value' => 'group_dropdown_types',
                'label' => __('Dropdown Type'),
                'multiple' => false,
                'optgroup' => [
                    // [
                    //     'value' => 'navpro-simple',
                    //     'label' => __('Simple'),
                    // ],
                    [
                        'value' => 'navpro-accordion',
                        'label' => __('Accordion'),
                        'notice' => __('Forces to use vertical orientation'),
                    ],
                    // [
                    //     'value' => 'navpro-amazon',
                    //     'label' => __('Amazon'),
                    // ],
                    // [
                    //     'value' => 'navpro-iconic',
                    //     'label' => __('Iconic'),
                    // ],
                    [
                        'value' => 'navpro-ribbon',
                        'label' => __('Ribbon'),
                    ],
                    [
                        'value' => 'navpro-stacked',
                        'label' => __('Stacked'),
                    ],
                ]
            ],
            [
                'value' => 'group_dropdown_x_alignment',
                'label' => __('Dropdown Horizontal Alignment'),
                'multiple' => false,
                'optgroup' => [
                    [
                        'value' => 'dropdown-left',
                        'label' => __('Left'),
                    ],
                    [
                        'value' => 'dropdown-right',
                        'label' => __('Right'),
                    ],
                ]
            ],
            [
                'value' => 'group_dropdown_y_alignment',
                'label' => __('Dropdown Vertical Alignment'),
                'multiple' => false,
                'optgroup' => [
                    [
                        'value' => 'dropdown-top',
                        'label' => __('Top'),
                    ],
                    [
                        'value' => 'dropdown-bottom',
                        'label' => __('Bottom'),
                    ],
                ]
            ],
            [
                'value' => 'group_effects',
                'label' => __('Dropdown Appear Effect'),
                'multiple' => false,
                'optgroup' => [
                    [
                        'value' => 'navpro-effect-none',
                        'label' => __('None'),
                    ],
                    [
                        'value' => 'navpro-effect-default',
                        'label' => __('Default'),
                    ],
                    [
                        'value' => 'navpro-effect-fade',
                        'label' => __('Fade'),
                    ],
                    [
                        'value' => 'navpro-effect-slidein',
                        'label' => __('SlideIn'),
                    ],
                    [
                        'value' => 'navpro-effect-slideout',
                        'label' => __('SlideOut'),
                    ],
                ]
            ],
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $result = [];
        foreach ($this->toOptionArray() as $group) {
            foreach ($group['optgroup'] as $item) {
                $result[$item['value']] = $item['label'];
            }
        }
        return $result;
    }
}
