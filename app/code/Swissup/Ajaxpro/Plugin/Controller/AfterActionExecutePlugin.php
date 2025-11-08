<?php
namespace Swissup\Ajaxpro\Plugin\Controller;

use Magento\Framework\Controller\AbstractResult;

/**
 * Plugin for changing ResultInterface
 */
class AfterActionExecutePlugin
{

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DataObjectFactory $dataObjectFactory
    ) {
        $this->eventManager = $eventManager;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Replace no json result with json when request is ajax
     * @see \Magento\Theme\Controller\Result\MessagePlugin::afterRenderResult
     *
     * @param mixed $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterExecute(
        $subject,
        $result
    ) {
        $request = $subject->getRequest();
        $eventName = 'swissup_ajaxpro_controller_action_after_execute_' . $request->getFullActionName();
        $resultObject = $this->dataObjectFactory->create(['data' => ['result' => $result]]);
        $eventParameters = [
            'controller_action' => $subject,
            'request' => $request,
            'result_object' => $resultObject
        ];

        $this->eventManager->dispatch($eventName, $eventParameters);

        $newResult = $resultObject->getData('result');
        if ($newResult instanceof AbstractResult) {
            $result = $newResult;
        }
        return $result;
    }
}
