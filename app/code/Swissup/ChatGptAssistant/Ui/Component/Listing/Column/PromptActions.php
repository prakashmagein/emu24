<?php
namespace Swissup\ChatGptAssistant\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class PromptActions extends Column
{
    const URL_PATH_EDIT = 'swissup_assistant/prompt/edit';
    const URL_PATH_DELETE = 'swissup_assistant/prompt/delete';

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            if (!isset($item['entity_id'])) {
                continue;
            }

            $item[$this->getData('name')] = [
                'edit' => [
                    'href' => $this->getContext()->getUrl(
                        static::URL_PATH_EDIT,
                        [
                            'entity_id' => $item['entity_id']
                        ]
                    ),
                    'label' => __('Edit')
                ],
                'delete' => [
                    'href' => $this->getContext()->getUrl(
                        static::URL_PATH_DELETE,
                        [
                            'entity_id' => $item['entity_id']
                        ]
                    ),
                    'label' => __('Delete'),
                    'confirm' => [
                        'title' => __('Delete'),
                        'message' => __('Are you sure you want to delete a record?')
                    ]
                ]
            ];
        }

        return $dataSource;
    }
}
