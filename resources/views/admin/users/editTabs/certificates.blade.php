<div class="tab-pane mt-3 fade" id="certificates" role="tabpanel" aria-labelledby="certificates-tab">
    <div class="row">
        <div class="col-12 col-md-6">
            <!-- Certificate Form -->
            <form action="/admin/users/webinar-certificates" method="POST">
                {{ csrf_field() }}
                <!-- Student ID (You can add a dropdown or input for student selection) -->
                <div class="form-group">
                    <input type="hidden" name="student_id" class="form-control" value="{{ $user->id }}" required>
                </div>

                <!-- Webinar Title -->
                <div class="form-group">
                    <label for="webinar_title">{{ trans('admin/main.webinar_title') }}</label>
                    <input type="text" name="webinar_title" class="form-control" maxlength="255">
                </div>

                <!-- Certificates (File input for certificates)  -->
                <div class="input-group">
                    <span class="input-group-btn">
                      <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-primary">
                        <i class="fa fa-picture-o"></i> Choose
                      </a>
                    </span>
                    <input id="thumbnail" class="form-control" type="text" name="certificates">
                  </div>
                  <div id="holder" style="margin-top:15px;max-height:100px;"></div>

                <div class="mt-4">
                    <button class="btn btn-primary" type="submit">{{ trans('admin/main.submit') }}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Table of Available Certificates -->
    <div class="row mt-5">
        <div class="col-12">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>{{ trans('admin/main.certificate_id') }}</th>
                        <th>{{ trans('admin/main.webinar_title') }}</th>
                        <th>{{ trans('admin/main.certificates') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($certificates as $certificate)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($certificate->created_at)->timestamp }}</td>
                        <td>{{ $certificate->webinar_title }}</td>
                        <td style="display:flex;justify-content:space-around;height:auto">
                            @php
                            $certs = explode(',', $certificate->certificates);
                            @endphp
                            @foreach($certs as $file)
                                <a href="{{ asset($file) }}" target="_blank"><img style="max-height:80px;margin:10px;border-radius:10px;border:2px solid orange" src="{{ asset($file) }}"/></a><br>
                            @endforeach
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
