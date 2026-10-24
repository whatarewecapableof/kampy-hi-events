<?php

namespace HiEvents\Services\Domain\Email;

use HiEvents\DomainObjects\AttendeeDomainObject;
use HiEvents\DomainObjects\Enums\EmailTemplateType;
use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\DomainObjects\EventSettingDomainObject;
use HiEvents\DomainObjects\InvoiceDomainObject;
use HiEvents\DomainObjects\OrderDomainObject;
use HiEvents\DomainObjects\OrganizerDomainObject;
use HiEvents\Mail\Attendee\AttendeeTicketMail;
use HiEvents\Mail\Order\OrderSummary;
use HiEvents\Services\Domain\Email\DTO\RenderedEmailTemplateDTO;

/**
 * Order confirmations and tickets use the one themed template for every event: the
 * school's colours when the event maps to a school, otherwise the shared Kamp
 * fallback. A GCU family and a GVSU family get the same email. The legacy Markdown
 * branch remains only for an unusable theme manifest.
 */
class MailBuilderService
{
    public function __construct(
        private readonly EmailTemplateService $emailTemplateService,
        private readonly EmailTokenContextBuilder $tokenContextBuilder,
        private readonly UniversityThemeResolver $universityThemeResolver,
    ) {
    }

    public function buildAttendeeTicketMail(
        AttendeeDomainObject $attendee,
        OrderDomainObject $order,
        EventDomainObject $event,
        EventSettingDomainObject $eventSettings,
        OrganizerDomainObject $organizer
    ): AttendeeTicketMail {
        $renderedTemplate = $this->renderAttendeeTicketTemplate(
            $attendee,
            $order,
            $event,
            $eventSettings,
            $organizer
        );

        return new AttendeeTicketMail(
            order: $order,
            attendee: $attendee,
            event: $event,
            eventSettings: $eventSettings,
            organizer: $organizer,
            renderedTemplate: $renderedTemplate,
            universityTheme: $this->universityThemeResolver->resolveForMail($event),
        );
    }

    public function buildOrderSummaryMail(
        OrderDomainObject $order,
        EventDomainObject $event,
        EventSettingDomainObject $eventSettings,
        OrganizerDomainObject $organizer,
        ?InvoiceDomainObject $invoice = null
    ): OrderSummary {
        $renderedTemplate = $this->renderOrderSummaryTemplate(
            $order,
            $event,
            $eventSettings,
            $organizer
        );

        return new OrderSummary(
            order: $order,
            event: $event,
            organizer: $organizer,
            eventSettings: $eventSettings,
            invoice: $invoice,
            renderedTemplate: $renderedTemplate,
            universityTheme: $this->universityThemeResolver->resolveForMail($event),
        );
    }

    private function renderAttendeeTicketTemplate(
        AttendeeDomainObject $attendee,
        OrderDomainObject $order,
        EventDomainObject $event,
        EventSettingDomainObject $eventSettings,
        OrganizerDomainObject $organizer
    ): ?RenderedEmailTemplateDTO {
        $template = $this->emailTemplateService->getTemplateByType(
            type: EmailTemplateType::ATTENDEE_TICKET,
            accountId: $event->getAccountId(),
            eventId: $event->getId(),
            organizerId: $organizer->getId()
        );

        if (!$template) {
            return null;
        }

        $context = $this->tokenContextBuilder->buildAttendeeTicketContext(
            $attendee,
            $order,
            $event,
            $organizer,
            $eventSettings
        );

        return $this->emailTemplateService->renderTemplate($template, $context);
    }

    private function renderOrderSummaryTemplate(
        OrderDomainObject $order,
        EventDomainObject $event,
        EventSettingDomainObject $eventSettings,
        OrganizerDomainObject $organizer
    ): ?RenderedEmailTemplateDTO {
        $template = $this->emailTemplateService->getTemplateByType(
            type: EmailTemplateType::ORDER_CONFIRMATION,
            accountId: $event->getAccountId(),
            eventId: $event->getId(),
            organizerId: $organizer->getId()
        );

        if (!$template) {
            return null;
        }

        $context = $this->tokenContextBuilder->buildOrderConfirmationContext(
            $order,
            $event,
            $organizer,
            $eventSettings
        );

        return $this->emailTemplateService->renderTemplate($template, $context);
    }
}
