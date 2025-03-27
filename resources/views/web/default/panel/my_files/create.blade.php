@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />    @endpush

@section('content')
    <div class="container">
        <h3>Upload File</h3>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('instructor-files.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" class="form-control" required>
                @error('title') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="form-group mt-15">
                <label class="input-label">حدد الملف</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <button type="button" class="input-group-text panel-file-manager" data-input="file" data-preview="holder">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-up text-white"><line x1="12" y1="19" x2="12" y2="5"></line><polyline points="5 12 12 5 19 12"></polyline></svg>
                        </button>
                    </div>
                    <input type="text" name="file" id="file" value="" class="form-control">
                                </div>
            </div>

            <!-- Optional webinar_id field -->
            <input type="hidden" name="webinar_id" value="">

            <button type="submit" class="btn btn-primary">Upload</button>
        </form>

        <hr>

        <h4>Your Uploaded Files</h4>
        <ul class="list-group mt-3">
            @foreach(\App\Models\InstructorFile::where('instructor_id', auth()->id())->get() as $file)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>{{ $file->title }}</strong> <br>
                        <small>{{ $file->path }}</small>
                    </div>
                    <form action="{{ route('instructor-files.destroy', $file->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </li>
            @endforeach
        </ul>
    </div>
@endsection

@push('scripts_bottom')


    <script src="/assets/default/js/panel/make_next_session.min.js"></script>
@endpush
