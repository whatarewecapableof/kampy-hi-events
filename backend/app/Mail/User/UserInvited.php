<?php

namespace HiEvents\Mail\User;

use HiEvents\DomainObjects\UserDomainObject;
use HiEvents\Mail\BaseMail;
use HiEvents\Services\Domain\Email\UniversityThemeResolver;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * @uses /backend/resources/views/emails/user/user-invited.blade.php
 * @uses /backend/resources/views/emails/user/user-invited-text.blade.php
 */
class UserInvited extends BaseMail
{
    private UserDomainObject $invitedUser;

    private string $appName;

    private string $inviteLink;

    public function __construct(
        UserDomainObject $invitedUser,
        string $appName,
        string $inviteLink
    )
    {
        parent::__construct();

        $this->invitedUser = $invitedUser;
        $this->appName = $appName;
        $this->inviteLink = $inviteLink;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('You\'ve been invited to join :appName', ['appName' => $this->appName]),
        );
    }

    public function content(): Content
    {
        // Standalone HTML in the operating-product style (same structure as the
        // university order emails). Staff invitations carry no school, so they use
        // the shared Kamp fallback theme.
        return new Content(
            view: 'emails.user.user-invited',
            text: 'emails.user.user-invited-text',
            with: [
                'invitedUser' => $this->invitedUser,
                'appName' => $this->appName,
                'inviteLink' => $this->inviteLink,
                'theme' => app(UniversityThemeResolver::class)->resolveForMail(),
            ]
        );
    }
}
