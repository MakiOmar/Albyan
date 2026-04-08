<div class="d-none" id="sendMessageModal">
    <h3 class="section-title after-line font-20 text-dark-blue mb-25">{{ trans('site.send_message') }}</h3>

    <form action="/users/{{ $user->id }}/send-message" method="post">
        {{ csrf_field() }}

        <div class="form-group">
            <label class="input-label">{{ trans('public.title') }}</label>
            <input type="text" name="title" class="form-control" autocomplete="off"/>
            <div class="invalid-feedback"></div>
        </div>

        <div class="form-group">
            <label class="input-label">{{ trans('public.email') }}</label>
            <input type="email" name="email" class="form-control" autocomplete="email"/>
            <div class="invalid-feedback"></div>
        </div>

        <div class="form-group">
            <label class="input-label">{{ trans('public.description') }}</label>
            {{-- Minimum 100 characters enforced server-side --}}
            <textarea name="description" class="form-control" rows="6" minlength="100"></textarea>
            <div class="invalid-feedback"></div>
        </div>

        {{-- Turnstile is rendered into this host when the modal opens (see profile.js) --}}
        <div class="form-group js-send-message-turnstile-host-wrap">
            <div class="js-send-message-turnstile-host"></div>
            <div class="invalid-feedback"></div>
        </div>

        <div class="mt-30 d-flex align-items-center justify-content-end">
            <button type="button" class="js-send-message-submit btn btn-primary">{{ trans('site.send_message') }}</button>
            <button type="button" class="btn btn-danger ml-10 close-swl">{{ trans('public.close') }}</button>
        </div>
    </form>
</div>
