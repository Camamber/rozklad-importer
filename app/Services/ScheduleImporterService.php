<?php

namespace App\Services;

use App\Classes\Log;
use Carbon\Carbon;
use Google_Client;
use Google_Http_Batch;
use Google_Service_Calendar;
use Google_Service_Calendar_Calendar;
use Google_Service_Calendar_Event;


class ScheduleImporterService
{
    private $service;

    public function __construct(Google_Client $client)
    {
        $this->service = new Google_Service_Calendar($client);
    }

    public function import($schedule)
    {
        $time_start = microtime(true);
        $calendarId = $this->createCalendar('Розклад занять ' . $schedule['group']);
        $time_elapsed = microtime(true) - $time_start;
        Log::info('Create calndar: ' . $time_elapsed );

        $firstSeptember = Carbon::now()->startOfMonth()->month(9);
        if ($firstSeptember->greaterThan(Carbon::now())) {
            $firstSeptember = $firstSeptember->subYear();
        }

        $time_start = microtime(true);
        // $this->service->getClient()->setUseBatch(true);
        // $batch = $this->service->createBatch();
        $events = [];

        foreach ($schedule['weeks'] as $weekNumber => $week) {
            $dateTime = $firstSeptember->clone()->startOfWeek();
            if ($weekNumber) {
                $dateTime = $dateTime->addWeek();
            }

            foreach ($week as $dayNumber => $day) {
                foreach ($day as $class) {
                    $startDate = $dateTime->clone()->addDays($dayNumber)->setTimeFromTimeString($class['time']);
                    $endDate = $startDate->clone()->addMinutes(95);

                    $summary = $this->formatSummary($class);
                    $description = $this->formatDescription($class);

                    $event = new Google_Service_Calendar_Event([
                        'summary' => $summary,
                        // 'location' => '800 Howard St., San Francisco, CA 94103',
                        'description' => $description,
                        'start' => ['dateTime' => $startDate->toRfc3339String(), 'timeZone' => 'Europe/Kiev'],
                        'end' => ['dateTime' => $endDate->toRfc3339String(), 'timeZone' => 'Europe/Kiev'],
                        'recurrence' => ['RRULE:FREQ=WEEKLY;WKST=MO;INTERVAL=2'],
                        'reminders' => [
                            'useDefault' => FALSE,
                            'overrides' => [
                                ['method' => 'popup', 'minutes' => 10],
                            ],
                        ],
                    ]);

                    $events[] = $this->service->events->insert($calendarId, $event);
                }
            }
        }

        // foreach ($events as $event) {
        //     $batch->add($event);
        // }

        // $events = $batch->execute();
        $time_elapsed = microtime(true) - $time_start;
        Log::info('Insert events: ' . $time_elapsed );
    }

    private function formatSummary($class)
    {
        $name = $class['short_name'];
        $type = $class['type'];
        $teacher =  isset($class['teacher']) ? $class['teacher']['short_name'] : '';

        return sprintf('%s [%s] (%s)', $name, $type, $teacher);
    }

    private function formatDescription($class)
    {
        $name = $class['full_name'];
        $type = $class['type'];
        $teacher =  isset($class['teacher']) ? $class['teacher']['full_name'] : '';

        return sprintf('%s з %s, викладач: %s', $type, $name, $teacher);
    }

    private function createCalendar(string $summary)
    {
        $calendar = new Google_Service_Calendar_Calendar();
        $calendar->setSummary($summary);
        $calendar->setTimeZone('Europe/Kiev');

        $createdCalendar = $this->service->calendars->insert($calendar);
        return $createdCalendar->getId();
    }
}
