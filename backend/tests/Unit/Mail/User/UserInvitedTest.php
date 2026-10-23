<?php

namespace Tests\Unit\Mail\User;

use HiEvents\DomainObjects\UserDomainObject;
use HiEvents\Mail\User\UserInvited;
use HiEvents\Services\Domain\Email\UniversityThemeResolver;
use Tests\TestCase;

class UserInvitedTest extends TestCase
{
    private const LINK = 'https://tickets.example.test/auth/accept-invitation/TOKEN-123';

    public function test_invite_is_a_standalone_operating_style_email_in_the_kamp_fallback_theme(): void
    {
        $mail = $this->mail();
        $html = $mail->render();
        $fallback = (new UniversityThemeResolver(base_path('../frontend/src/styles/universityThemes.json')))->resolveFallback();

        $this->assertSame('emails.user.user-invited', $mail->content()->view);
        $this->assertSame('emails.user.user-invited-text', $mail->content()->text);
        $this->assertNull($mail->content()->markdown);
        $this->assertStringContainsString("font-family:'DM Sans',Arial,Helvetica,sans-serif", $html);
        $this->assertStringContainsString('background-color:' . $fallback->primary, $html);
        $this->assertStringContainsString('bgcolor="' . $fallback->secondary . '"', $html);
        $this->assertStringContainsString('max-width:600px;table-layout:fixed', $html);
        $this->assertStringNotContainsString('#2f5147', $html);
    }

    public function test_invite_has_a_heading_button_backup_link_expiry_and_ignore_line(): void
    {
        $html = $this->mail()->render();

        $this->assertStringContainsString('You’re invited to Kamp Love', $html);
        $this->assertStringContainsString('Hi Jordan,', $html);
        $this->assertStringContainsString('tickets.example.test', $html);
        $this->assertStringContainsString('expires in 7 days', $html);
        $this->assertStringContainsString('Weren’t expecting this?', $html);

        $document = new \DOMDocument();
        @$document->loadHTML($html);
        $labels = [];
        foreach ((new \DOMXPath($document))->query('//a') as $link) {
            if ($link->getAttribute('href') === self::LINK) {
                $labels[] = trim($link->textContent);
            }
        }
        $this->assertSame(['Accept invitation', self::LINK], $labels);
    }

    public function test_invite_keeps_the_footer_and_licence_attribution_in_html_and_text(): void
    {
        config(['app.email_footer_text' => 'Kamp Love · Example footer']);
        $mail = $this->mail();
        $html = $mail->render();
        $text = view($mail->content()->text, $mail->buildViewData())->render();

        $this->assertStringContainsString('Kamp Love · Example footer', $html);
        $this->assertStringContainsString('Powered by <a href="https://hi.events?utm_source=app-email-footer"', $html);
        $this->assertStringContainsString('Accept invitation:', $text);
        $this->assertStringContainsString(self::LINK, $text);
        $this->assertStringContainsString('Kamp Love · Example footer', $text);
        $this->assertStringContainsString('Powered by Hi.Events (https://hi.events)', $text);
    }

    public function test_subject_is_unchanged(): void
    {
        $this->assertSame('You\'ve been invited to join Kamp Love', $this->mail()->envelope()->subject);
    }

    private function mail(): UserInvited
    {
        $user = new UserDomainObject();
        $user->setFirstName('Jordan');

        return new UserInvited($user, 'Kamp Love', self::LINK);
    }
}
