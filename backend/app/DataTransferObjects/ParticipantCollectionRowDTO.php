<?php

namespace HiEvents\DataTransferObjects;

final class ParticipantCollectionRowDTO extends BaseDataObject
{
    public function __construct(
        public readonly string $participantId,
        public readonly ?string $displayName,
        public readonly string $registeredAt,
    ) {}
}
