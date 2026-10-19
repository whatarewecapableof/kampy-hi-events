<?php

namespace HiEvents\Exceptions;

use RuntimeException;

final class ParticipantCollectionUnavailableException extends RuntimeException
{
    public function __construct() { parent::__construct('Participant collection unavailable'); }
}
