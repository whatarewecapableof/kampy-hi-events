<?php

namespace HiEvents\Repository\Interfaces;

use HiEvents\DataTransferObjects\ParticipantCollectionDTO;

interface ParticipantCollectionRepositoryInterface
{
    public function forEvent(int $eventId): ParticipantCollectionDTO;
}
