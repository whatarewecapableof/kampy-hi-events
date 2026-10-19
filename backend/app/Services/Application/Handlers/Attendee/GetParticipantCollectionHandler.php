<?php

namespace HiEvents\Services\Application\Handlers\Attendee;

use HiEvents\DataTransferObjects\ParticipantCollectionDTO;
use HiEvents\Repository\Interfaces\ParticipantCollectionRepositoryInterface;

final class GetParticipantCollectionHandler
{
    public function __construct(private readonly ParticipantCollectionRepositoryInterface $repository) {}

    public function handle(int $eventId): ParticipantCollectionDTO
    {
        return $this->repository->forEvent($eventId);
    }
}
