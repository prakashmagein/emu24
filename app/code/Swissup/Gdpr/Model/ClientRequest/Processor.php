<?php

namespace Swissup\Gdpr\Model\ClientRequest;

use Swissup\Gdpr\Model\ClientRequest;
use Magento\Framework\Exception\LocalizedException;

class Processor
{
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Swissup\Gdpr\Helper\Data
     */
    private $helper;

    /**
     * @var \Swissup\Gdpr\Model\PersonalDataHandler\HandlerHelper
     */
    private $handlerHelper;

    /**
     * @var \Swissup\Gdpr\Model\ResourceModel\PersonalDataHandler\Collection
     */
    private $handlers;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    private $dateTime;

    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Swissup\Gdpr\Helper\Data $helper,
        \Swissup\Gdpr\Model\PersonalDataHandler\HandlerHelper $handlerHelper,
        \Swissup\Gdpr\Model\ResourceModel\PersonalDataHandler\CollectionFactory $handlers,
        \Magento\Framework\Stdlib\DateTime $dateTime
    ) {
        $this->eventManager = $eventManager;
        $this->helper = $helper;
        $this->handlerHelper = $handlerHelper;
        $this->handlers = $handlers->create();
        $this->dateTime = $dateTime;
    }

    /**
     * Checks if request could be processed
     *
     * @param  ClientRequest $request
     * @return boolean
     */
    public function canProcess(ClientRequest $request)
    {
        $statuses = [
            ClientRequest::STATUS_CONFIRMED,
            ClientRequest::STATUS_FAILED,
            ClientRequest::STATUS_PROCESSED,
        ];
        return in_array($request->getStatus(), $statuses);
    }

    /**
     * Process client's request
     *
     * @param  ClientRequest $request
     * @return boolean
     */
    public function process(ClientRequest $request)
    {
        $request->setStatus(ClientRequest::STATUS_RUNNING)->save();

        $request->setStatus(ClientRequest::STATUS_PROCESSED);
        try {
            switch ($request->getType()) {
                case ClientRequest::TYPE_DATA_DELETE:
                    $this->delete($request);
                    break;
                case ClientRequest::TYPE_DATA_EXPORT:
                    $this->export($request);
                    break;
                default:
                    throw new LocalizedException(
                        __("Unsupported request type: '%1'", $request->getType())
                    );
            }
        } catch (\Exception $e) {
            $request
                ->setStatus(ClientRequest::STATUS_FAILED)
                ->addError($e->getMessage());
        }

        $request
            ->setExecutedAt($this->dateTime->formatDate(new \DateTime()))
            ->save();

        return true;
    }

    /**
     * Delete personal data
     *
     * @param  ClientRequest $request
     * @return void
     * @throws \Exception
     */
    private function delete(ClientRequest $request)
    {
        $this->eventManager->dispatch(
            'swissup_gdpr_personal_data_delete_before',
            ['request' => $request, 'handlers' => $this->handlers]
        );

        foreach ($this->handlers as $handler) {
            if (!method_exists($handler, 'beforeDelete')) {
                continue;
            }
            $handler->beforeDelete($request, $this->handlerHelper);
        }

        $method = $this->getDeleteMethodName();
        foreach ($this->handlers as $handler) {
            try {
                $handler->{$method}($request, $this->handlerHelper);
            } catch (\Exception $e) {
                $request->addError(
                    $e->getMessage()
                    . ' in ' . $e->getFile()
                    . ' at line ' . $e->getLine()
                    . ' (Triggered by ' . get_class($handler) . ')'
                );
            }
        }

        $this->eventManager->dispatch(
            'swissup_gdpr_personal_data_delete_after',
            ['request' => $request, 'handlers' => $this->handlers]
        );
    }

    /**
     * @todo: Export personal data (and send it via email?)
     *
     * @param  ClientRequest $request
     * @return void
     * @throws \Exception
     */
    private function export(ClientRequest $request)
    {
        $this->eventManager->dispatch(
            'swissup_gdpr_personal_data_export_before',
            ['request' => $request, 'handlers' => $this->handlers]
        );

        $data = [];
        foreach ($this->handlers as $handler) {
            try {
                $data = array_replace_recursive($data, $handler->export($request, $this->handlerHelper));
            } catch (\Exception $e) {
                $request->addError($e->getMessage());
            }
        }

        $this->eventManager->dispatch(
            'swissup_gdpr_personal_data_export_after',
            ['request' => $request, 'handlers' => $this->handlers, 'data' => $data]
        );

        // send email with link to generated file?
    }

    /**
     * Get method to use while deleting customer data
     *
     * @return string
     */
    private function getDeleteMethodName()
    {
        $method = $this->helper->getConfigValue(
            'swissup_gdpr/request/delete_data/method'
        );
        $allowedMethods = [
            'anonymize',
            'delete',
        ];

        if (in_array($method, $allowedMethods)) {
            return $method;
        }

        return $allowedMethods[0];
    }
}
