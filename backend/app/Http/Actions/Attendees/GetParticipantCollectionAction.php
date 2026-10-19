<?php

namespace HiEvents\Http\Actions\Attendees;

use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\Exceptions\ParticipantCollectionUnavailableException;
use HiEvents\Http\Actions\BaseAction;
use HiEvents\Services\Application\Handlers\Attendee\GetParticipantCollectionHandler;
use Illuminate\Http\JsonResponse;

final class GetParticipantCollectionAction extends BaseAction
{
    public function __construct(private readonly GetParticipantCollectionHandler $handler) {}

    public function __invoke(int $eventId): JsonResponse
    {
        // Native account/event authorization precedes ALL collection I/O.
        $this->isActionAuthorized($eventId, EventDomainObject::class);
        try {
            $result = $this->jsonResponse($this->handler->handle($eventId)->toArray());
            $result->headers->set('X-Kampy-Participant-Collection','v1');
        } catch (ParticipantCollectionUnavailableException) {
            $result = $this->jsonResponse(['status'=>'feature_unavailable'],503);
        }
        $result->headers->set('Cache-Control','private, no-store, max-age=0');
        $result->headers->set('Referrer-Policy','no-referrer');
        $result->headers->set('X-Robots-Tag','noindex, nofollow, noarchive');
        return $result;
    }
}
