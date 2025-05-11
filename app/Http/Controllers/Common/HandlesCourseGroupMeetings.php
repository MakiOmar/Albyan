<?php

namespace App\Http\Controllers\Common;

use Carbon\Carbon;

trait HandlesCourseGroupMeetings
{
    public function groupNextTime($courseGroup, &$joinUrl, &$meetingID, $role = 'teacher')
    {
        $joinURLKey = $role === 'teacher' ? 'start_url' : 'join_url';
        $joinUrl = null;
        $meetingID = null;
        $nextStartTime = null;

        $userTimezone = auth()->user()->timezone ?? 'Asia/Dubai';
        if (!in_array($userTimezone, ['Asia/Dubai', 'Africa/Cairo'])) {
            $userTimezone = 'Asia/Dubai';
        }

        $now = Carbon::now($userTimezone);

        $meetingJson = json_decode($courseGroup->meeting_json, true);
        $occurrences = [];

        if (!empty($meetingJson['occurrences'])) {
            $occurrences = collect($meetingJson['occurrences'])->filter(function ($occurrence) use ($now, $userTimezone) {
                return Carbon::parse($occurrence['start_time'])->setTimezone($userTimezone)->greaterThan($now);
            })->sortBy('start_time');

            $next = $occurrences->first();
            if ($next) {
                $nextStartTime = Carbon::parse($next['start_time'])->setTimezone($userTimezone)->toIso8601String();
                $joinUrl = $next[$joinURLKey] ?? null;
                $meetingID = $next['occurrence_id'] ?? ($next['id'] ?? null);
            }
        } elseif ($courseGroup->session_type === 'offline' && !empty($meetingJson['recurrence'])) {
            $startDate = Carbon::parse($courseGroup->meeting_start_time, $userTimezone);
            $endDate = Carbon::parse($courseGroup->meeting_end_time, $userTimezone);
            $durationMinutes = $courseGroup->meeting_duration ?? 60;

            $type = $meetingJson['recurrence']['type'] ?? 1;
            $interval = $meetingJson['recurrence']['repeat_interval'] ?? 1;
            $weeklyDays = explode(',', $meetingJson['recurrence']['weekly_days'] ?? '');
            $monthlyDay = $meetingJson['recurrence']['monthly_day'] ?? null;

            $current = $startDate->copy();
            while ($current <= $endDate) {
                $isValid = false;

                if ($type == 1) {
                    $isValid = true;
                    $current->addDays($interval - 1);
                } elseif ($type == 2 && in_array($current->dayOfWeekIso, $weeklyDays)) {
                    $isValid = true;
                } elseif ($type == 3 && $current->day == $monthlyDay) {
                    $isValid = true;
                }

                if ($isValid && $current->greaterThan($now)) {
                    $nextStartTime = $current->toIso8601String();
                    break;
                }

                $current->addDay();
            }
        } elseif ($courseGroup->session_type === 'offline' && $courseGroup->meeting_start_time) {
            $sessionTime = Carbon::parse($courseGroup->meeting_start_time)->setTimezone($userTimezone);
            if ($sessionTime->greaterThan($now)) {
                $nextStartTime = $sessionTime->toIso8601String();
            }
        }

        return $nextStartTime;
    }
}
