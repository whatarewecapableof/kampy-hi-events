<?php

namespace HiEvents\Repository\Eloquent;

use HiEvents\DataTransferObjects\ParticipantCollectionDTO;
use HiEvents\DataTransferObjects\ParticipantCollectionRowDTO;
use HiEvents\Exceptions\ParticipantCollectionUnavailableException;
use HiEvents\Repository\Interfaces\ParticipantCollectionRepositoryInterface;
use Illuminate\Database\ConnectionInterface;
use Throwable;

/** Narrow current-membership projection. No model hydration, eager relationships or finance intake. */
final class ParticipantCollectionRepository implements ParticipantCollectionRepositoryInterface
{
    public function __construct(private readonly ConnectionInterface $connection) {}

    public function forEvent(int $eventId): ParticipantCollectionDTO
    {
        if ($eventId < 1) {
            throw new ParticipantCollectionUnavailableException();
        }
        try {
            // Exactly ONE SELECT: statement MVCC snapshot covers members, cap and revision.
            // cap+1 refuses oversized membership. This is not pagination or a truncated census.
            $row = $this->connection->selectOne(<<<'SQL'
WITH params AS (SELECT CAST(:event_id AS bigint) AS event_id), members AS MATERIALIZED (
 SELECT a.id::text AS id,
        NULLIF(btrim(concat_ws(' ',a.first_name,a.last_name)),'') AS name,
        to_char(to_timestamp(extract(epoch FROM a.created_at)) AT TIME ZONE 'UTC',
                'YYYY-MM-DD"T"HH24:MI:SS.MS"Z"') AS registered
 FROM attendees a JOIN orders o ON o.id=a.order_id
 JOIN params p ON p.event_id=a.event_id AND p.event_id=o.event_id
 WHERE a.deleted_at IS NULL AND o.deleted_at IS NULL
   AND a.status='ACTIVE' AND o.status='COMPLETED'
 ORDER BY a.id LIMIT 10001
), snapshot AS (
 SELECT count(*)::integer AS total,
        COALESCE(jsonb_agg(jsonb_build_object('participantId',id,'displayName',name,'registeredAt',registered)
                          ORDER BY id::bigint),'[]'::jsonb) AS participants FROM members
)
SELECT total, participants::text AS participants,
       encode(sha256(convert_to(participants::text,'UTF8')),'hex') AS revision,
       to_char(statement_timestamp() AT TIME ZONE 'UTC','YYYY-MM-DD"T"HH24:MI:SS.MS"Z"') AS observed
FROM snapshot
SQL, ['event_id' => $eventId]);
            if (!$row || !is_numeric($row->total) || (int)$row->total < 0 || (int)$row->total > 10000
                || !preg_match('/^[0-9a-f]{64}$/D', $row->revision ?? '')) {
                throw new ParticipantCollectionUnavailableException();
            }
            $rows = json_decode($row->participants, true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($rows) || count($rows) !== (int)$row->total) throw new ParticipantCollectionUnavailableException();
            $participants = []; $seen = [];
            foreach ($rows as $value) {
                if (!is_array($value) || count($value) !== 3
                    || !isset($value['participantId'], $value['registeredAt']) || !array_key_exists('displayName',$value)
                    || !is_string($value['participantId']) || !preg_match('/^[1-9][0-9]{0,18}$/D',$value['participantId'])
                    || isset($seen[$value['participantId']]) || !is_string($value['registeredAt'])
                    || !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}Z$/D',$value['registeredAt'])) {
                    throw new ParticipantCollectionUnavailableException();
                }
                $name = $value['displayName'];
                if ($name !== null && (!is_string($name) || trim($name) !== $name || $name === ''
                    || strlen(mb_convert_encoding($name,'UTF-16LE','UTF-8')) > 240 || preg_match('/\p{Cc}/u',$name))) {
                    throw new ParticipantCollectionUnavailableException();
                }
                $seen[$value['participantId']] = true;
                $participants[] = new ParticipantCollectionRowDTO($value['participantId'],$name,$value['registeredAt']);
            }
            return new ParticipantCollectionDTO((string)$eventId,(int)$row->total,$row->revision,$row->observed,$participants);
        } catch (Throwable) {
            // No SQL, bound operand or private row is copied into an application error.
            throw new ParticipantCollectionUnavailableException();
        }
    }
}
