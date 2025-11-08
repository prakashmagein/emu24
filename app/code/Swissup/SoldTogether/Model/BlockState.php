<?php

namespace Swissup\SoldTogether\Model;

class BlockState
{
    private $state = null;

    public function set($state)
    {
        $this->state = $state;

        return $this;
    }

    public function get()
    {
        return $this->state;
    }
}
