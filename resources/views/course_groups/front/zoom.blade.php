@extends(getTemplate().'.layouts.app')

@push('styles_top')
    <style>
        #zmmtg-root{
            position: relative;
        }
    </style>
    <link rel="stylesheet" href="/assets/default/css/css-stars.css">
@endpush


@section('content')
    <div id="zoom-container" style="width: 800px; height: 600px; position: relative;">
        <div id="zmmtg-root"></div>
    </div>
    <div id="zoomMeeting"></div>
@endsection

@push('scripts_bottom')
    <!-- Zoom Web SDK Dependencies -->
    <script src="https://source.zoom.us/3.1.0/lib/vendor/react.min.js"></script>
    <script src="https://source.zoom.us/3.1.0/lib/vendor/react-dom.min.js"></script>
    <script src="https://source.zoom.us/3.1.0/lib/vendor/redux.min.js"></script>
    <script src="https://source.zoom.us/3.1.0/lib/vendor/redux-thunk.min.js"></script>
    <script src="https://source.zoom.us/3.1.0/lib/vendor/lodash.min.js"></script>

    <!-- Zoom Web SDK -->
    <script src="https://source.zoom.us/3.1.0/zoom-meeting-3.1.0.min.js"></script>
    <script src="https://source.zoom.us/zoom-meeting-embedded-3.1.0.min.js"></script>
    <script>
        ZoomMtg.setZoomJSLib('https://source.zoom.us/3.1.0/lib', '/av');
        ZoomMtg.preLoadWasm();
        ZoomMtg.prepareWebSDK()
        document.addEventListener('DOMContentLoaded', function () {
            const meetingConfig = {
                sdkKey: '{{ $zoomSdkKey  }}',
                meetingNumber: '{{ $meetingNumber }}',
                role: '{{ $role }}',
                leaveUrl: '{{ url('/') }}', // Redirect URL after leaving
                userName: '{{ $userName }}',
                userEmail: '{{ $userEmail }}',
                passWord: '',
                signature: '{{ $zoomSignature }}', // Generated in backend
            };

            ZoomMtg.init({
                leaveUrl: meetingConfig.leaveUrl,
                patchJsMedia: true,
                success: function () {
                    setTimeout(() => {
                        // Preload Zoom Web SDK
                        ZoomMtg.join({
                            signature: meetingConfig.signature,
                            meetingNumber: meetingConfig.meetingNumber,
                            userName: meetingConfig.userName,
                            sdkKey: meetingConfig.sdkKey,
                            userEmail: meetingConfig.userEmail,
                            passWord: meetingConfig.passWord,
                            success: function () {
                                console.log('Join meeting success');
                            },
                            error: function (error) {
                                console.error(error);
                            },
                        });
                    }, 10);
                   
                },
                error: function (error) {
                    console.error(error);
                },
            });
        });
        
        
    </script>


@endpush
