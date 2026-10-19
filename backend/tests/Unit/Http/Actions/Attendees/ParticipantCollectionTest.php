<?php
namespace Tests\Unit\Http\Actions\Attendees;
use HiEvents\DataTransferObjects\ParticipantCollectionDTO;
use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\DomainObjects\AccountUserDomainObject;
use HiEvents\Repository\Interfaces\AccountUserRepositoryInterface;
use HiEvents\Repository\Interfaces\AccountUserEventAssignmentRepositoryInterface;
use HiEvents\Repository\Interfaces\EventRepositoryInterface;
use HiEvents\Services\Domain\Account\AccountUserEventAssignmentService;
use Illuminate\Auth\AuthManager;
use HiEvents\Models\User;
use HiEvents\DomainObjects\Enums\Role;
use HiEvents\DomainObjects\UserDomainObject;
use HiEvents\Exceptions\ParticipantCollectionUnavailableException;
use HiEvents\Http\Actions\Attendees\GetParticipantCollectionAction;
use HiEvents\Repository\Interfaces\ParticipantCollectionRepositoryInterface;
use HiEvents\Services\Application\Handlers\Attendee\GetParticipantCollectionHandler;
use HiEvents\Services\Domain\Auth\AuthUserService;
use HiEvents\Services\Infrastructure\Authorization\IsAuthorizedService;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Facade;
use Tests\TestCase;
final class ParticipantCollectionTest extends TestCase
{
    private Container $container;
    private AuthManager $realAuth;
    protected function setUp(): void
    {
        parent::setUp();$this->container=$this->app;
        $this->realAuth=$this->app->make('auth');
        Auth::swap(new class { public function check(): bool { return true; } });
        $user=$this->createMock(UserDomainObject::class);$service=$this->createMock(AuthUserService::class);
        $service->method('getUser')->willReturn($user);$service->method('getAuthenticatedAccountId')->willReturn(99);
        $this->container->instance(AuthUserService::class,$service);
    }
    protected function tearDown(): void {Facade::clearResolvedInstances();\Mockery::close();parent::tearDown();}
    public function testEventAuthorizationPrecedesAllCollectionIo(): void
    {
        $auth=$this->createMock(IsAuthorizedService::class);$auth->expects($this->once())->method('isActionAuthorized')
            ->with(7001,EventDomainObject::class,$this->anything(),99,Role::ORGANIZER)->willThrowException(new \RuntimeException('synthetic denied'));
        $this->container->instance(IsAuthorizedService::class,$auth);
        $repository=$this->createMock(ParticipantCollectionRepositoryInterface::class);$repository->expects($this->never())->method('forEvent');
        $this->expectException(\RuntimeException::class);(new GetParticipantCollectionAction(new GetParticipantCollectionHandler($repository)))(7001);
    }
    public function testAuthorizedMinimalUnwrappedContractAndPrivateHeaders(): void
    {
        $auth=$this->createMock(IsAuthorizedService::class);$auth->expects($this->once())->method('isActionAuthorized');$this->container->instance(IsAuthorizedService::class,$auth);
        $repository=$this->createMock(ParticipantCollectionRepositoryInterface::class);$repository->method('forEvent')->willReturn(new ParticipantCollectionDTO('7001',0,str_repeat('a',64),'2026-10-19T06:00:00.000Z',[]));
        $response=(new GetParticipantCollectionAction(new GetParticipantCollectionHandler($repository)))(7001);
        self::assertSame(200,$response->getStatusCode());self::assertSame('v1',$response->headers->get('X-Kampy-Participant-Collection'));
        $data=$response->getData(true);self::assertSame('native_participant_collection_v1',$data['sourceContract']);self::assertSame([],$data['participants']);self::assertArrayNotHasKey('data',$data);
        self::assertStringContainsString('no-store',$response->headers->get('Cache-Control'));
    }
    /** Real registered route, auth/account middleware, registry and account/event policy.
     * JWT uses a public fixture signing key and cached synthetic identity. Identity/provider DB
     * and repository ports remain synthetic; this is not installed account/JWT qualification. */
    private function registeredRoute(Role $role, bool $assigned=true, int $eventAccount=99, bool $signedIn=true, bool $active=true): void
    {
        config(['jwt.secret'=>str_repeat('public-fixture-',8),'cache.default'=>'array']);
        Auth::swap($this->realAuth);
        $guard=$this->realAuth->guard('api');
        if ($signedIn) {
            $identity=new class(['id'=>123,'locale'=>'en','first_name'=>'Synthetic','last_name'=>'Fixture']) extends User {
                public function getJWTCustomClaims(): array { return ['account_id'=>99]; }
            };
            $token=$guard->login($identity);
            $this->withHeader('Authorization','Bearer '.$token);
        } else $guard->forgetUser();
        $membership=$this->createMock(AccountUserDomainObject::class);
        $membership->method('getId')->willReturn(5);
        $membership->method('getRole')->willReturn($role->value);
        $membership->method('getStatus')->willReturn($active?'ACTIVE':'INACTIVE');
        $user=$this->createMock(UserDomainObject::class);$user->method('getCurrentAccountUser')->willReturn($membership);
        $identity=$this->createMock(AuthUserService::class);$identity->method('getUser')->willReturn($user);
        $identity->method('getAuthenticatedAccountId')->willReturn(99);$this->app->instance(AuthUserService::class,$identity);
        $events=$this->createMock(EventRepositoryInterface::class);
        $events->method('findById')->willReturnCallback(function(int $id) use($eventAccount) {
            $event=$this->createMock(EventDomainObject::class);$event->method('getId')->willReturn($id);
            $event->method('getAccountId')->willReturn($eventAccount);return $event;
        });
        $this->app->instance(EventRepositoryInterface::class,$events);
        $assignments=$this->createMock(AccountUserEventAssignmentRepositoryInterface::class);
        $assignments->method('isAssignedToEvent')->willReturnCallback(fn(int $member,int $event)=>$assigned&&$member===5&&$event===7001);
        $assignmentPolicy=new AccountUserEventAssignmentService($assignments,$events);
        $authorization=new IsAuthorizedService($this->app,$this->createMock(AccountUserRepositoryInterface::class),$this->realAuth,$assignmentPolicy);
        $this->app->instance(IsAuthorizedService::class,$authorization);
    }
    private function collectionPort(bool $allowed): void
    {
        $repository=$this->createMock(ParticipantCollectionRepositoryInterface::class);
        $repository->expects($allowed?$this->once():$this->never())->method('forEvent')
            ->with(7001)->willReturn(new ParticipantCollectionDTO('7001',0,str_repeat('a',64),'2026-10-19T06:00:00.000Z',[]));
        $this->app->instance(ParticipantCollectionRepositoryInterface::class,$repository);
    }
    public function testRegisteredRouteRunsPermissionAndAssignedEventPolicy(): void
    {
        $this->registeredRoute(Role::UNIVERSITY_DIRECTOR);$this->collectionPort(true);
        $response=$this->getJson('/events/7001/participant-collection');
        $response->assertStatus(200)->assertHeader('X-Kampy-Participant-Collection','v1');
        self::assertSame('native_participant_collection_v1',$response->json('sourceContract'));
        self::assertSame([],$response->json('participants'));
    }
    public function testRegisteredRouteDeniesRoleWithoutAttendeesViewBeforeIo(): void
    {
        $this->registeredRoute(Role::CHECK_IN);$this->collectionPort(false);
        $this->getJson('/events/7001/participant-collection')->assertStatus(403);
    }
    public function testRegisteredRouteDeniesUnassignedEventBeforeIo(): void
    {
        $this->registeredRoute(Role::UNIVERSITY_DIRECTOR,false);$this->collectionPort(false);
        $this->getJson('/events/7001/participant-collection')->assertStatus(403);
    }
    public function testRegisteredRouteDeniesOtherAccountBeforeIo(): void
    {
        $this->registeredRoute(Role::UNIVERSITY_DIRECTOR,true,100);$this->collectionPort(false);
        $this->getJson('/events/7001/participant-collection')->assertStatus(403);
    }
    public function testRegisteredRouteRejectsUnauthenticatedBeforeIo(): void
    {
        $this->registeredRoute(Role::UNIVERSITY_DIRECTOR,true,99,false);$this->collectionPort(false);
        $this->getJson('/events/7001/participant-collection')->assertStatus(401);
    }
    public function testRegisteredRouteDeniesInactiveMembershipBeforeIo(): void
    {
        $this->registeredRoute(Role::UNIVERSITY_DIRECTOR,true,99,true,false);$this->collectionPort(false);
        $this->getJson('/events/7001/participant-collection')->assertStatus(403);
    }
    public function testUnknownPermissionRuleStillDeniesBeforeCallback(): void
    {
        $this->registeredRoute(Role::ADMIN);$this->collectionPort(false);$called=false;
        $this->app['router']->get('/api/synthetic-reader-no-rule',function()use(&$called){$called=true;return response()->json([]);})
            ->middleware(['auth:api','route.permission']);
        $this->getJson('/api/synthetic-reader-no-rule')->assertStatus(403);self::assertFalse($called);
    }
    public function testUnavailableHasNoSuccessContractHeader(): void
    {
        $auth=$this->createMock(IsAuthorizedService::class);$auth->method('isActionAuthorized');$this->container->instance(IsAuthorizedService::class,$auth);
        $repository=$this->createMock(ParticipantCollectionRepositoryInterface::class);$repository->method('forEvent')->willThrowException(new ParticipantCollectionUnavailableException());
        $response=(new GetParticipantCollectionAction(new GetParticipantCollectionHandler($repository)))(7001);
        self::assertSame(503,$response->getStatusCode());self::assertFalse($response->headers->has('X-Kampy-Participant-Collection'));
    }
}
