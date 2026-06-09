<?php

namespace App\Domain\Workflows\Exceptions;

use App\Exceptions\BaseApiException;

class WorkflowCommunicationException extends BaseApiException
{
    // Hereda de BaseApiException para que HandlesProcess lo atrape y registre
    // adecuadamente sin envolverlo en un Error 500 genérico.
}