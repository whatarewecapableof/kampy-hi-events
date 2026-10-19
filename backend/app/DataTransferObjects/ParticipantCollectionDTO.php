<?php

namespace HiEvents\DataTransferObjects;

final class ParticipantCollectionDTO extends BaseDataObject
{
    /** @param array<ParticipantCollectionRowDTO> $participants */
    public function __construct(
        public readonly string $nativeEventId,
        public readonly int $total,
        public readonly string $revision,
        public readonly string $observedAt,
        public readonly array $participants,
        public readonly string $sourceContract = 'native_participant_collection_v1',
        public readonly string $membership = 'completed_order_active_attendee_v1',
        public readonly string $consistency = 'single_statement_readonly',
        public readonly string $deletions = 'attendee_and_order_tombstones',
        public readonly bool $complete = true,
    ) {}
}
