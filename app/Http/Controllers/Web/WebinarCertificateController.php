<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WebinarCertificate;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class WebinarCertificateController extends Controller
{
    public function search(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'certificate_id' => 'required|numeric',
            'captcha' => 'required|captcha',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }
        $dateTime = Carbon::createFromTimestamp($data['certificate_id']);

        // البحث الدقيق عن الشهادات التي تم إنشاؤها بنفس التوقيت
        $certificates = WebinarCertificate::whereBetween('created_at', [
            $dateTime->copy()->subSeconds(2),
            $dateTime->copy()->addSeconds(2),
        ])->get();

        return response()->json([
            'certificates' => $certificates->map(function ($c) {
                return [
                    'student_name' => optional($c->student)->full_name ?? 'N/A',
                    'created_at' => $c->created_at->format('Y-m-d H:i'),
                    'webinar_title' => $c->webinar_title,
                ];
            }),
        ]);
    }
}
