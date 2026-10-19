<?php
namespace Tests\Unit\Repository\Eloquent;

use HiEvents\Exceptions\ParticipantCollectionUnavailableException;
use HiEvents\Repository\Eloquent\ParticipantCollectionRepository;
use Illuminate\Database\ConnectionInterface;
use PHPUnit\Framework\TestCase;

final class ParticipantCollectionTest extends TestCase
{
    private function row(array $participants = []): object
    {
        return (object)['total'=>count($participants),'participants'=>json_encode($participants),'revision'=>str_repeat('a',64),'observed'=>'2026-10-19T06:00:00.000Z'];
    }
    public function testOneNarrowStatementAndLegitimateEmpty(): void
    {
        $c=$this->createMock(ConnectionInterface::class);
        $c->expects($this->once())->method('selectOne')->with($this->callback(function($sql){
            self::assertStringContainsString('members AS MATERIALIZED',$sql);self::assertStringContainsString('LIMIT 10001',$sql);
            self::assertStringContainsString('a.deleted_at IS NULL AND o.deleted_at IS NULL',$sql);
            self::assertStringContainsString("a.status='ACTIVE' AND o.status='COMPLETED'",$sql);
            self::assertStringContainsString('p.event_id=a.event_id AND p.event_id=o.event_id',$sql);
            self::assertDoesNotMatchRegularExpression('/payer|email|phone|amount|price|refund|is_test|SELECT\s+\*/i',$sql);
            return true;
        }),['event_id'=>7001])->willReturn($this->row());
        $dto=(new ParticipantCollectionRepository($c))->forEvent(7001);
        self::assertSame(0,$dto->total);self::assertSame([],$dto->participants);self::assertTrue($dto->complete);
    }
    public function testScopedMinimalNamesOnly(): void
    {
        $c=$this->createMock(ConnectionInterface::class);
        $c->method('selectOne')->willReturn($this->row([['participantId'=>'11','displayName'=>'Synthetic Attendee','registeredAt'=>'2026-10-19T06:00:00.000Z']]));
        $dto=(new ParticipantCollectionRepository($c))->forEvent(7001);
        self::assertSame('11',$dto->participants[0]->participantId);self::assertSame('Synthetic Attendee',$dto->participants[0]->displayName);
    }
    public function testOversizeIsNotTruncated(): void
    {
        $c=$this->createMock(ConnectionInterface::class);
        $r=$this->row();$r->total=10001;$c->method('selectOne')->willReturn($r);
        $this->expectException(ParticipantCollectionUnavailableException::class);(new ParticipantCollectionRepository($c))->forEvent(7001);
    }
    public function testInvalidEventDoesNotQuery(): void
    {
        $c=$this->createMock(ConnectionInterface::class);$c->expects($this->never())->method('selectOne');
        $this->expectException(ParticipantCollectionUnavailableException::class);(new ParticipantCollectionRepository($c))->forEvent(0);
    }
    public function testDuplicateIdentityFailsClosed(): void
    {
        $c=$this->createMock(ConnectionInterface::class);
        $r=['participantId'=>'11','displayName'=>null,'registeredAt'=>'2026-10-19T06:00:00.000Z'];$c->method('selectOne')->willReturn($this->row([$r,$r]));
        $this->expectException(ParticipantCollectionUnavailableException::class);(new ParticipantCollectionRepository($c))->forEvent(7001);
    }
    public function testDriverFailureDoesNotDiscloseSqlOrOperand(): void
    {
        $c=$this->createMock(ConnectionInterface::class);$c->method('selectOne')->willThrowException(new \RuntimeException('synthetic private SQL must not escape'));
        try {(new ParticipantCollectionRepository($c))->forEvent(7001);self::fail('expected unavailable');}
        catch(ParticipantCollectionUnavailableException $e){self::assertStringNotContainsString('synthetic private SQL',$e->getMessage());self::assertNull($e->getPrevious());}
    }
}
