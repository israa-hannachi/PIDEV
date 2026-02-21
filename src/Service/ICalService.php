<?php

namespace App\Service;

use App\Entity\Event;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Uid\Uuid;
use Psr\Log\LoggerInterface;

class ICalService
{
    private const APYHUB_API_URL = 'https://api.apyhub.com/generate/ical/file';
    private const APY_TOKEN = 'APY0EOJvEb0QAHYU5Ppkv9HtbFtnVE2VP5x316xDJVKFWu6CcTig6AB4yETsIQAfoT0Y';

    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Generate an iCal file from an Event
     * 
     * @return array with keys: 'success' (bool), 'data' (string|null), 'error' (string|null)
     */
    public function generateICalFile(Event $event, string $eventType = 'request'): array
    {
        try {
            // Generate a UUID if not already set
            $icalId = $event->getIcalId() ?? Uuid::v4()->toRfc4122();

            // Format dates according to API requirements (DD-MM-YYYY)
            $meetingDate = $event->getDateDebut()->format('d-m-Y');
            $startTime = $event->getDateDebut()->format('H:i');
            $endTime = $event->getDateFin()->format('H:i');

            // Prepare request payload
            $payload = [
                'id' => $icalId,
                'summary' => $event->getTitre(),
                'description' => $event->getDescription(),
                'organizer_email' => $event->getOrganizerEmail() ?? 'noreply@naja7ni-edu.com',
                'attendees_emails' => $event->getAttendeesEmailsAsArray(),
                'location' => $event->getLieu(),
                'time_zone' => $event->getTimeZone() ?? 'UTC',
                'start_time' => $startTime,
                'end_time' => $endTime,
                'meeting_date' => $meetingDate,
            ];

            // Add recurrence info if applicable
            if ($event->isRecurring()) {
                $payload['recurring'] = true;
                $payload['recurrence'] = [
                    'frequency' => $event->getRecurrenceFrequency() ?? 'WEEKLY',
                    'count' => $event->getRecurrenceCount() ?? 1,
                ];
            } else {
                $payload['recurring'] = false;
            }

            // Make API request
            $response = $this->httpClient->request('POST', self::APYHUB_API_URL, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'apy-token' => self::APY_TOKEN,
                ],
                'json' => $payload,
                'query' => [
                    'output' => $this->sanitizeFileName($event->getTitre()) . '.ics',
                    'event_type' => $eventType,
                ],
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode !== 200) {
                $errorContent = $response->getContent(false);
                $this->logger->error('ApyHub API error', [
                    'status_code' => $statusCode,
                    'event_id' => $event->getId(),
                    'response' => $errorContent,
                ]);

                return [
                    'success' => false,
                    'data' => null,
                    'error' => "API error: HTTP $statusCode",
                ];
            }

            // Get the iCal content
            $icalContent = $response->getContent();

            // Save the UUID back to the event (in the controller, you'll need to persist this)
            if (!$event->getIcalId()) {
                $event->setIcalId($icalId);
            }

            $this->logger->info('iCal file generated successfully', [
                'event_id' => $event->getId(),
                'ical_id' => $icalId,
            ]);

            return [
                'success' => true,
                'data' => $icalContent,
                'error' => null,
                'ical_id' => $icalId,
            ];
        } catch (\Exception $e) {
            $this->logger->error('Exception in generateICalFile', [
                'event_id' => $event->getId(),
                'exception' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Tokenize the filename to remove special characters
     */
    private function sanitizeFileName(string $filename): string
    {
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);
        return substr($filename, 0, 50);
    }

    /**
     * Update an existing event (cancellation or reschedule)
     */
    public function updateICalEvent(Event $event, string $eventType = 'request'): array
    {
        // For updates, we need to preserve the original ID
        if (!$event->getIcalId()) {
            $event->setIcalId(Uuid::v4()->toRfc4122());
        }

        return $this->generateICalFile($event, $eventType);
    }

    /**
     * Create a cancellation event
     */
    public function cancelEvent(Event $event): array
    {
        return $this->generateICalFile($event, 'cancel');
    }

    /**
     * Get timezone options for form select
     */
    public static function getTimeZoneOptions(): array
    {
        return [
            'UTC' => 'UTC',
            'Europe/Paris' => 'Europe/Paris',
            'Europe/London' => 'Europe/London',
            'Europe/Berlin' => 'Europe/Berlin',
            'Europe/Helsinki' => 'Europe/Helsinki',
            'Asia/Tokyo' => 'Asia/Tokyo',
            'Asia/Shanghai' => 'Asia/Shanghai',
            'America/New_York' => 'America/New_York',
            'America/Los_Angeles' => 'America/Los_Angeles',
            'America/Chicago' => 'America/Chicago',
            'Australia/Sydney' => 'Australia/Sydney',
            'Africa/Cairo' => 'Africa/Cairo',
            'Africa/Johannesburg' => 'Africa/Johannesburg',
        ];
    }

    /**
     * Get recurrence frequency options
     */
    public static function getRecurrenceFrequencies(): array
    {
        return [
            'DAILY' => 'Daily',
            'WEEKLY' => 'Weekly',
            'MONTHLY' => 'Monthly',
            'YEARLY' => 'Yearly',
        ];
    }
}
