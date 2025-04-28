<?php

namespace App\Http\Controllers;

use App\Models\CourseGroup;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotifyGroupEndSoon;

class GroupNotificationController extends Controller
{
    public function notifyGroupsNearEnd()
    {
        CourseGroup::whereNull('notified_end_soon_at')
        ->where('meeting_end_time', '>=', now())
        ->chunk(100, function ($groups) {
            foreach ($groups as $group) {
                $meetingJson = json_decode($group->meeting_json, true);

                if (!empty($meetingJson['occurrences'])) {
                    $lastOccurrence = collect($meetingJson['occurrences'])->pluck('start_time')->sortDesc()->first();
                    $lastSessionDate = Carbon::parse($lastOccurrence);
                } else {
                    $lastSessionDate = Carbon::parse($group->meeting_end_time);
                }

                $daysLeft = now()->diffInDays($lastSessionDate, false);

                if ($daysLeft <= 7 && $daysLeft >= 0) {
                    try {
                        Mail::to(env('NOTIFY_GROUPS_EMAIL'))->send(new NotifyGroupEndSoon($group));

                        $group->update([
                            'notified_end_soon_at' => now(),
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('فشل إرسال إشعار قرب نهاية المجموعة: ' . $e->getMessage());
                    }
                }
            }
        });
    }
}
