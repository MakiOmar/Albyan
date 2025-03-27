<?php

// app/Http/Controllers/InstructorFileController.php

namespace App\Http\Controllers;

use App\Models\InstructorFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InstructorFileController extends Controller
{
    public function create()
    {
        return view(getTemplate() . '.panel.my_files.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'file'  => 'required|string',
            'webinar_id' => 'nullable|exists:webinars,id',
            'group_id'  => 'required|exists:course_groups,id', // تحقق من وجود المجموعة
        ]);

        InstructorFile::create([
            'title'         => $request->title,
            'path'          => $request->file, // هذا مجرد رابط URL أو path
            'instructor_id' => auth()->id(),
            'webinar_id'    => $request->webinar_id,
            'group_id'      => $request->group_id,
        ]);

        return back()->with('success', 'File uploaded successfully.');
    }

    public function destroy($id)
    {
        $file = InstructorFile::findOrFail($id);

        Storage::disk('public')->delete($file->path);
        $file->delete();

        return back()->with('success', 'File deleted successfully.');
    }
}
