<?php

namespace Swissup\Gdpr\Model\PersonalDataHandler;

use Swissup\Gdpr\Model\ClientRequest;
use Swissup\Gdpr\Model\Faker;

class HandlerHelper extends AbstractHandler
{
    public function getFaker(): Faker
    {
        return $this->faker;
    }
}
