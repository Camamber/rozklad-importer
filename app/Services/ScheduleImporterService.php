<?php

namespace App\Services;

use App\Classes\GoogleClient;
use App\Classes\Log;
use Carbon\Carbon;
use Google_Service_Calendar;
use Google_Service_Calendar_Calendar;
use Google_Service_Calendar_Event;


class ScheduleImporterService
{
    // $this->service->getClient()->setUseBatch(true);
    // $batch = $this->service->createBatch();
    private $service;

    public function __construct()
    {
        $this->service = new Google_Service_Calendar(GoogleClient::getInstance());
    }

    public function import($group)
    {
        $events = [];
        $schedule = $group->schedules->first()->schedule;

        $firstSeptember = Carbon::now()->timezone('Europe/Kiev')->startOfMonth()->month(9);
        if ($firstSeptember->greaterThan(Carbon::now())) {
            $firstSeptember = Carbon::now()->timezone('Europe/Kiev')->startOfMonth()->month(2);
        }

        foreach ($schedule as $weekNumber => $week) {
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
                        'recurrence' => ['RRULE:FREQ=WEEKLY;WKST=MO;;UNTIL=20210610;INTERVAL=2'],
                        'reminders' => [
                            'useDefault' => FALSE,
                            'overrides' => [
                                ['method' => 'popup', 'minutes' => 10],
                            ],
                        ],
                    ]);

                    $events[] = $event;
                }
            }
        }

        $time_start_calendar = microtime(true);
        $calendarId = $this->createCalendar('Розклад занять ' . $group->title);
        $time_elapsed_calendar = microtime(true) - $time_start_calendar;

        $time_start_events = microtime(true);
        foreach ($events as $event) {
            $this->service->events->insert($calendarId, $event);
        }
        $time_elapsed_events = microtime(true) - $time_start_events;

        $time_elapsed_total = $time_elapsed_calendar + $time_elapsed_events;
        $context = array_merge(['group' => $group->title], GoogleClient::user()->toArray());
        Log::info("Import schedule: calendar - $time_elapsed_calendar; events - $time_elapsed_events; total - $time_elapsed_total", $context);
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
