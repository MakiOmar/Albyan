<?php

namespace App\Http\Controllers;

use App\Models\WebinarCertificate;
use Illuminate\Http\Request;
use App\User;

class WebinarCertificateController extends Controller
{
    // Show a list of all webinar certificates
    public function index()
    {
        $certificates = WebinarCertificate::all();
        return view('admin.users.editTabs.certificates', compact('certificates'));
    }

    // Store a new webinar certificate
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'webinar_title' => 'nullable|string|max:255',
            'certificates' => 'required|string',
        ]);

        $certificate = WebinarCertificate::updateOrCreate(
            ['student_id' => $request->student_id],  // Condition to check if a record exists for this student_id
            [
                'webinar_title' => $request->webinar_title,
                'certificates' => $request->certificates,
            ]
        );
        return redirect()->back()->with('success', 'Certificate created successfully.');
    }


    // Show a specific webinar certificate
    public function show($id)
    {
        $certificate = WebinarCertificate::findOrFail($id);
        return response()->json($certificate);
    }

    // Update an existing webinar certificate
    public function update(Request $request, $id)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'webinar_title' => 'nullable|string|max:255',
            'certificates' => 'required|array',
        ]);

        $certificate = WebinarCertificate::findOrFail($id);
        $certificate->update([
            'student_id' => $request->student_id,
            'webinar_title' => $request->webinar_title,
            'certificates' => $request->certificates,
        ]);

        return response()->json($certificate);
    }

    // Delete a specific webinar certificate
    public function destroy($id)
    {
        $certificate = WebinarCertificate::findOrFail($id);
        $certificate->delete();

        return response()->json(['message' => 'Certificate deleted successfully']);
    }

    // List certificates for a specific student
    public function listByStudent($studentId)
    {
        // Retrieve all certificates for the given student_id
        $certificates = WebinarCertificate::where('student_id', $studentId)->get();

        if ($certificates->isEmpty()) {
            return response()->json(['message' => 'No certificates found for this student'], 404);
        }

        return response()->json($certificates);
    }
}
