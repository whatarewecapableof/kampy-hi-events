<?php
namespace Tests\Unit\Services\Application\Handlers\Attendee;
use HiEvents\DataTransferObjects\ParticipantCollectionDTO;
use HiEvents\Repository\Interfaces\ParticipantCollectionRepositoryInterface;
use HiEvents\Services\Application\Handlers\Attendee\GetParticipantCollectionHandler;
use PHPUnit\Framework\TestCase;
final class ParticipantCollectionTest extends TestCase
{
    public function testHandlerPreservesExactEventAndTypedSnapshot(): void
    {
        $result=new ParticipantCollectionDTO('7001',0,str_repeat('a',64),'2026-10-19T06:00:00.000Z',[]);
        $repository=$this->createMock(ParticipantCollectionRepositoryInterface::class);
        $repository->expects($this->once())->method('forEvent')->with(7001)->willReturn($result);
        self::assertSame($result,(new GetParticipantCollectionHandler($repository))->handle(7001));
    }
}
