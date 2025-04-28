<?php

namespace App\Mail;

use App\Models\CourseGroup;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotifyGroupEndSoon extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $group;

    public function __construct(CourseGroup $group)
    {
        $this->group = $group;
    }

    public function build()
    {
        return $this->from('info@albyaninstitute.com', 'معهد البيان')
                ->subject('تنبيه: مجموعة اقتربت على الانتهاء')
                ->view('emails.notify_group_end_soon')
                ->with([
                    'group' => $this->group,
                ]);
    }
}
