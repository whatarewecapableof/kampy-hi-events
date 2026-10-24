<?php

namespace Tests\Unit\Mail;

use HiEvents\DomainObjects\AttendeeDomainObject;
use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\DomainObjects\EventSettingDomainObject;
use HiEvents\DomainObjects\OrderDomainObject;
use HiEvents\DomainObjects\OrganizerDomainObject;
use HiEvents\DomainObjects\Status\OrderStatus;
use HiEvents\DomainObjects\UserDomainObject;
use HiEvents\Mail\Order\OrderCancelled;
use HiEvents\Mail\User\ForgotPassword;
use HiEvents\Services\Domain\Email\EmailTemplateService;
use HiEvents\Services\Domain\Email\EmailTokenContextBuilder;
use HiEvents\Services\Domain\Email\MailBuilderService;
use HiEvents\Services\Domain\Email\UniversityThemeResolver;
use Mockery;
use Tests\TestCase;

/**
 * Tier 2: every family email has the same shape. Order confirmations and tickets
 * use the themed template for every event, and the shared Markdown layout wears
 * the school's colours or the Kamp fallback.
 */
class KampEmailThemeTest extends TestCase
{
    private const GVSU = ['primary' => '#0032a0', 'secondary' => '#13155c', 'soft' => '#e7e7ed'];
    private const KAMP = ['primary' => '#2b663d', 'secondary' => '#1f4a2d', 'soft' => '#e7f0ea'];

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.frontend_url' => 'https://tickets.example.test', 'app.name' => 'Kamp Love', 'app.email_logo_url' => null]);
    }

    public function test_a_non_school_event_gets_the_same_themed_order_and_ticket_emails_in_kamp_colours(): void
    {
        $builder = $this->builder();
        $summary = $builder->buildOrderSummaryMail($this->order(), $this->event('GCU Kamp Love Fall 2026'), $this->settings(), $this->organizer());
        $ticket = $builder->buildAttendeeTicketMail($this->attendee(), $this->order(), $this->event('GCU Kamp Love Fall 2026'), $this->settings(), $this->organizer());

        $this->assertSame('emails.orders.university-summary', $summary->content()->view);
        $this->assertSame('emails.orders.university-attendee-ticket', $ticket->content()->view);
        foreach ([$summary->render(), $ticket->render()] as $html) {
            $this->assertStringContainsString('background-color:' . self::KAMP['primary'], $html);
            $this->assertStringContainsString('bgcolor="' . self::KAMP['secondary'] . '"', $html);
            $this->assertStringContainsString('background-color:' . self::KAMP['soft'], $html);
            $this->assertStringContainsString('Powered by <a href="https://hi.events?utm_source=app-email-footer"', $html);
            $this->assertStringContainsString('© ' . date('Y') . ' Kamp Love', $html);
        }
    }

    public function test_a_school_event_still_gets_its_school_colours_and_now_the_footer(): void
    {
        $html = $this->builder()
            ->buildOrderSummaryMail($this->order(), $this->event('Grand Valley State University'), $this->settings(), $this->organizer())
            ->render();

        $this->assertStringContainsString('background-color:' . self::GVSU['primary'], $html);
        $this->assertStringContainsString('bgcolor="' . self::GVSU['secondary'] . '"', $html);
        $this->assertStringContainsString('Powered by <a href="https://hi.events?utm_source=app-email-footer"', $html);
    }

    public function test_the_shared_layout_wears_school_colours_for_a_school_event(): void
    {
        $html = (new OrderCancelled($this->order(), $this->event('Grand Valley State University'), $this->organizer(), $this->settings()))->render();

        $this->assertMatchesRegularExpression('/class="card-bar"[^>]*background-color: ' . self::GVSU['primary'] . '/', $html);
        $this->assertMatchesRegularExpression('/class="brand-wordmark"[^>]*color: ' . self::GVSU['primary'] . '/', $html);
        $this->assertSharedLayoutShape($html);
    }

    public function test_the_shared_layout_uses_the_kamp_fallback_without_a_school(): void
    {
        $user = new UserDomainObject();
        $user->setFirstName('Jordan');
        $html = (new ForgotPassword($user, 'TOKEN'))->render();

        $this->assertMatchesRegularExpression('/class="card-bar"[^>]*background-color: ' . self::KAMP['primary'] . '/', $html);
        $this->assertMatchesRegularExpression('/class="button-cell button-cell-primary"[^>]*background-color: ' . self::KAMP['secondary'] . '/', $html);
        $this->assertStringContainsString('Reset your password', $html);
        $this->assertStringContainsString('https://tickets.example.test/auth/reset-password/TOKEN', $html);
        $this->assertSharedLayoutShape($html);
    }

    private function assertSharedLayoutShape(string $html): void
    {
        $this->assertStringContainsString("font-family: 'DM Sans', Arial, Helvetica, sans-serif", $html);
        $this->assertStringContainsString('background-color: #f4f5f7', $html);
        $this->assertStringContainsString('max-width: 600px', $html);
        $this->assertStringContainsString('Powered by', $html);
        $this->assertStringNotContainsString('#2f5147', $html);
        $this->assertStringNotContainsString('Georgia', $html);
        $this->assertStringNotContainsString('#f9f4f0', $html);
    }

    private function builder(): MailBuilderService
    {
        $templates = Mockery::mock(EmailTemplateService::class);
        $templates->shouldReceive('getTemplateByType')->andReturn(null);

        return new MailBuilderService(
            $templates,
            Mockery::mock(EmailTokenContextBuilder::class),
            new UniversityThemeResolver(base_path('../frontend/src/styles/universityThemes.json')),
        );
    }

    private function event(string $title): EventDomainObject
    {
        $event = new EventDomainObject();
        $event->setId(7);
        $event->setAccountId(1);
        $event->setTitle($title);
        $event->setStartDate('2026-11-07 00:00:00');
        $event->setTimezone('America/Phoenix');
        $event->setCurrency('USD');
        $event->setLocation('Example Campground');

        return $event;
    }

    private function settings(): EventSettingDomainObject
    {
        $settings = new EventSettingDomainObject();
        $settings->setSupportEmail('help@example.org');
        $settings->setIsOnlineEvent(false);
        $settings->setLocationDetails(null);

        return $settings;
    }

    private function organizer(): OrganizerDomainObject
    {
        $organizer = new OrganizerDomainObject();
        $organizer->setId(1);
        $organizer->setName('Kamp Love');
        $organizer->setEmail('help@example.org');

        return $organizer;
    }

    private function order(): OrderDomainObject
    {
        $order = new OrderDomainObject();
        $order->setId(1);
        $order->setStatus(OrderStatus::COMPLETED->name);
        $order->setPublicId('KL-EXAMPLE');
        $order->setShortId('o_EXAMPLE');
        $order->setTotalGross(150.00);

        return $order;
    }

    private function attendee(): AttendeeDomainObject
    {
        $attendee = new AttendeeDomainObject();
        $attendee->setId(1);
        $attendee->setShortId('a_EXAMPLE');

        return $attendee;
    }
}
