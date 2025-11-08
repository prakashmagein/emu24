<?php

namespace Swissup\EasySlide\Pagebuilder\Plugin;

class Slider
{
    protected $slideFieldNames = [
        'image' => 'slide_%s_image',
        'url' => 'slide_%s_url',
        'title' => 'slide_%s_title',
        'description' => 'slide_%s_description',
        'desc_position' => 'slide_%s_desc_position',
        'desc_background' => 'slide_%s_desc_background',
    ];

    public function afterSave(
        \Swissup\Pagebuilder\Model\Section\SourceModel\Entity $subject,
        $result
    ) {
        if (!$subject->getModel() instanceof \Swissup\EasySlide\Model\Slider) {
            return $result;
        }

        $model = $subject->getModel();

        foreach ($model->getSlidesCollection() as $slide) {
            foreach ($this->slideFieldNames as $key => $template) {
                $modelKey = sprintf($template, $slide->getId());
                if ($model->hasData($modelKey)) {
                    $slide->setData($key, $model->getData($modelKey));
                }
            }

            $slide->save();
        }

        return $result;
    }

    public function afterGetFields(
        \Swissup\Pagebuilder\Model\Section\SourceModel\Entity $subject,
        $fields
    ) {
        if (!$subject->getModel() instanceof \Swissup\EasySlide\Model\Slider) {
            return $fields;
        }

        $fields['tabs']['children']['slides'] = array_merge([
            'label' => __('Slides'),
            'expanded' => true,
            'children' => [],
        ], $fields['tabs']['children']['slides'] ?? []);

        $fields['tabs']['children']['slides']['children']['slides'] = [
            'expanded' => true,
            'mode' => 'tabs',
            'children' => [],
        ];

        foreach ($subject->getModel()->getSlides() as $i => $slide) {
            $fields['tabs']['children']['slides']['children']['slides']['children']['slide_' . $slide['slide_id']] = [
                'label' => __('Slide') . ' ' . ($i + 1),
                'children' => [
                    [
                        'label' => __('Image'),
                        'name' => sprintf($this->slideFieldNames['image'], $slide['slide_id']),
                        'type' => 'image',
                        'value' => $slide['image'],
                        'uploaderConfig' => [
                            'basePath' => 'easyslide',
                        ],
                    ],
                    [
                        'mode' => 'grid',
                        'css' => 'grid-cols-2',
                        'children' => [
                            [
                                'label' => __('Link'),
                                'name' => sprintf($this->slideFieldNames['url'], $slide['slide_id']),
                                'value' => $slide['url'],
                            ],
                            [
                                'label' => __('Title'),
                                'name' => sprintf($this->slideFieldNames['title'], $slide['slide_id']),
                                'value' => $slide['title'],
                            ],
                        ],
                    ],
                    [
                        'label' => __('Description'),
                        'name' => sprintf($this->slideFieldNames['description'], $slide['slide_id']),
                        'value' => $slide['description'],
                        'type' => 'textarea',
                        'wysiwyg' => true,
                        'editorConfig' => [
                            'buttons' => [
                                'widget' => ['enabled' => false],
                            ],
                        ],
                        'rows' => 6,
                    ],
                    [
                        'mode' => 'grid',
                        'css' => 'grid-cols-2',
                        'children' => [
                            [
                                'label' => __('Description Position'),
                                'name' => sprintf($this->slideFieldNames['desc_position'], $slide['slide_id']),
                                'value' => $slide['desc_position'],
                                'source_model' => \Swissup\EasySlide\Model\Config\Source\DescriptionPosition::class,
                            ],
                            [
                                'label' => __('Description Background'),
                                'name' => sprintf($this->slideFieldNames['desc_background'], $slide['slide_id']),
                                'value' => $slide['desc_background'],
                                'source_model' => \Swissup\EasySlide\Model\Config\Source\DescriptionBackground::class,
                            ],
                        ],
                    ],
                ]
            ];
        }

        return $fields;
    }
}
