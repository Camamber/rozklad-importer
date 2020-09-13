<?php

namespace App\Services;

use App\Classes\HtmlHelper;
use App\Classes\Str;
use DOMDocument;
use GuzzleHttp\Client;


class RozkladParserService
{
    private $schedule;

    public function __construct()
    {
        $this->schedule = [];
    }

    public function parse($group)
    {
        $this->schedule = ['group' => $group];

        $body = $this->fetchRozklad($group);

        $doc = new DOMDocument('1.0', 'UTF-8');
        $internalErrors = libxml_use_internal_errors(true);
        $doc->loadHTML($body);
        libxml_use_internal_errors($internalErrors);

        $tables = $doc->getElementsByTagName('table');

        foreach ($tables as $table) {
            $this->schedule['weeks'][] = $this->parseWeek($table);
        }
        return $this->schedule;
    }

    private function fetchRozklad($group)
    {
        $client = new Client();
        $response = $client->request('POST', 'http://rozklad.kpi.ua/Schedules/ScheduleGroupSelection.aspx', [
            'headers' => [
                'Origin' => 'http://rozklad.kpi.ua',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.135 Safari/537.36',
                'Referer' => 'http://rozklad.kpi.ua/Schedules/ScheduleGroupSelection.aspx'
            ],
            'form_params' => [
                '__VIEWSTATE' => '/wEMDAwQAgAADgEMBQAMEAIAAA4BDAUDDBACAAAOAgwFBwwQAgwPAgEIQ3NzQ2xhc3MBD2J0biBidG4tcHJpbWFyeQEEXyFTQgUCAAAADAUNDBACAAAOAQwFAQwQAgAADgEMBQ0MEAIMDwEBBFRleHQBG9Cg0L7Qt9C60LvQsNC0INC30LDQvdGP0YLRjAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAALVdjzppTCyUtNVSyV7xykGQzHz2',
                '__EVENTTARGET' => '',
                '__EVENTARGUMENT' => '',
                'ctl00$MainContent$ctl00$txtboxGroup' => $group,
                'ctl00$MainContent$ctl00$btnShowSchedule' => 'Розклад+занять',
                '__EVENTVALIDATION' => '/wEdAAEAAAD/////AQAAAAAAAAAPAQAAAAUAAAAIsA3rWl3AM+6E94I5Tu9cRJoVjv0LAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAHfLZVQO6kVoZVPGurJN4JJIAuaU'
            ]
        ]);

        return $response->getBody()->getContents();
    }

    private function parseWeek($weekTable)
    {
        $week = [];

        $rows = $weekTable->childNodes;
        for ($i = 1; $i < count($rows); $i++) {
            $rowDays = $rows[$i]->childNodes;

            $rawTime = new Str(HtmlHelper::getInnerHtml($rowDays[0]));
            for ($j = 1; $j < count($rowDays); $j++) {
                $class = [];
                if (!count($rowDays[$j]->childNodes)) {
                    continue;
                }

                $class['time'] = $rawTime->br2nl()->explode("\n")[1];

                $tmp = new Str(HtmlHelper::getInnerHtml($rowDays[$j]));
                $tmp = $tmp->br2nl()->explode("\n");
                $class['type'] = trim(end($tmp));

                if (($tmp = $rowDays[$j]->getElementsByTagName('a')) && count($tmp)) {
                    if (isset($tmp[0])) {
                        $class['full_name'] =  $tmp[0]->getAttribute('title');
                        $class['short_name'] = $tmp[0]->textContent;
                    }

                    if (isset($tmp[1])) {
                        $class['teacher']['full_name'] =  $tmp[1]->getAttribute('title');
                        $class['teacher']['short_name'] = $tmp[1]->textContent;
                    }
                }

                $week[$j - 1][] = $class;
            }
        }
        return $week;
    }

    public function fetchGroups($query)
    {
        $client = new Client();
        $response = $client->request('POST', 'http://rozklad.kpi.ua/Schedules/ScheduleGroupSelection.aspx/GetGroups', [
            'json' => [
                'prefixText' => $query,
                'count' => 10
            ]
        ]);

        $body = json_decode($response->getBody());
        $json = [];
        if ($body->d) {
            $json = array_merge($json, $body->d);
        }
        return $json;
    }
}
