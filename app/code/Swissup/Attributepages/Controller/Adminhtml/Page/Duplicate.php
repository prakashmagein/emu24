<?php

namespace Swissup\Attributepages\Controller\Adminhtml\Page;

use Swissup\Attributepages\Model\ImageData;

class Duplicate extends Save
{
    public function execute()
    {
        return $this->duplicate();
    }
}
