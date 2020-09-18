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

    public function parse($groupId)
    {
        $body = $this->fetchRozklad($groupId);

        $doc = new DOMDocument('1.0', 'UTF-8');
        $internalErrors = libxml_use_internal_errors(true);
        $doc->loadHTML($body);
        libxml_use_internal_errors($internalErrors);

        $title = $doc->getElementById('ctl00_MainContent_lblHeader');
        $this->schedule = ['group' => trim(str_replace('Розклад занять для', '', $title->textContent))];

        $tables = $doc->getElementsByTagName('table');

        foreach ($tables as $table) {
            $this->schedule['weeks'][] = $this->parseWeek($table);
        }
        return $this->schedule;
    }

    public function fetchGroupIds($groupName)
    {
        $arr = [];

        $client = new Client();
        $response = $client->request('POST', 'http://rozklad.kpi.ua/Schedules/ScheduleGroupSelection.aspx', [
            'allow_redirects' => false,
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
                'ctl00$MainContent$ctl00$txtboxGroup' => $groupName,
                'ctl00$MainContent$ctl00$btnShowSchedule' => 'Розклад+занять',
                '__EVENTVALIDATION' => '/wEdAAEAAAD/////AQAAAAAAAAAPAQAAAAUAAAAIsA3rWl3AM+6E94I5Tu9cRJoVjv0LAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAHfLZVQO6kVoZVPGurJN4JJIAuaU'
            ]
        ]);

        if ($response->getStatusCode() == 302) {
            $groupUrl = $response->getHeaderLine('Location');
            $arr[] =  ['name' => $groupName, 'id' => str_replace('/Schedules/ViewSchedule.aspx?g=', '', $groupUrl)];
        } else {

            $body = $response->getBody()->getContents();

            $doc = new DOMDocument('1.0', 'UTF-8');
            $internalErrors = libxml_use_internal_errors(true);
            $doc->loadHTML($body);
            libxml_use_internal_errors($internalErrors);

            $table = $doc->getElementById('ctl00_MainContent_ctl00_GroupListPanel')->getElementsByTagName('table');
            foreach ($table[0]->getElementsByTagName('a') as $a) {
                $id = str_replace('ViewSchedule.aspx?g=', '', $a->getAttribute('href'));
                $arr[] = ['name' => $a->textContent, 'id' => $id];
            }
        }
        return $arr;
    }

    private function fetchRozklad($groupId)
    {
        $client = new Client();
        $response = $client->request('POST', 'http://rozklad.kpi.ua/Schedules/ViewSchedule.aspx', [
            'query' => ['g' =>  $groupId],
            'headers' => [
                'Origin' => 'http://rozklad.kpi.ua',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.135 Safari/537.36',
                'Referer' => 'http://rozklad.kpi.ua/Schedules/ScheduleGroupSelection.aspx'
            ],
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

                if (($tmp = $rowDays[$j]->getElementsByTagName('a')) && count($tmp)) {
                    for ($k = 0; $k < $tmp->count(); $k++) {
                        $href = $tmp->item($k)->getAttribute('href');
                        if (strpos($href, 'wiki.kpi.ua')) {
                            $class['full_name'][] = $tmp->item($k)->getAttribute('title');
                            $class['short_name'][] = $tmp->item($k)->textContent;
                        } else if (strpos($href, 'Schedules')) {
                            $class['teacher']['full_name'][] =  $tmp->item($k)->getAttribute('title');
                            $class['teacher']['short_name'][] = $tmp->item($k)->textContent;
                        } else if (strpos($href, 'maps.google.com')) {
                            $class['type'][] = $tmp->item($k)->textContent;
                        }
                    }

                    $class['full_name'] = implode(', ', $class['full_name']);
                    $class['short_name'] = implode(', ', $class['short_name']);
                    if (isset($class['teacher'])) {
                        $class['teacher']['full_name'] = implode(', ',  $class['teacher']['full_name']);
                        $class['teacher']['short_name'] = implode(', ', $class['teacher']['short_name']);
                    }

                    if (isset($class['type']) && count($class['type'])) {
                        $class['type'] =  implode(', ', $class['type']);
                    } else {
                        $type = new Str(HtmlHelper::getInnerHtml($rowDays[$j]));
                        $type = $type->br2nl()->explode("\n");
                        $class['type'] = trim(end($type));
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

    public function fetchGroupsLocal($query)
    {
        $groups = json_decode(file_get_contents('cache/groups.json'));
        $result = array_filter($groups, function ($item) use ($query) {
            return mb_stripos($item, $query, 0, 'UTF-8') !== false;
        });
        return array_values($result);
    }
}
