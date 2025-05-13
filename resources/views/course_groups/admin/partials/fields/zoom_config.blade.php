@php
    $participantVideo = old('participant_video', $isEdit ? ($meetingJson['settings']['participant_video'] ?? '0') : '0');
    $hostVideo = old('host_video', $isEdit ? ($meetingJson['settings']['host_video'] ?? '0') : '0');
    $audioOption = old('audio_option', $isEdit ? ($meetingJson['settings']['audio'] ?? 'both') : 'both');
@endphp

<div class="row d-none">
    <!-- Participant Video -->
    <div class="form-group col-md-4 col-12">
        <label for="participant_video">Enable Participant Video</label>
        <small class="form-text text-muted">Choose whether participants' videos should be enabled when they join the meeting.</small>
        <select name="participant_video" id="participant_video" class="form-control">
            <option value="0" {{ $participantVideo == '0' ? 'selected' : '' }}>No</option>
            <option value="1" {{ $participantVideo == '1' ? 'selected' : '' }}>Yes</option>
        </select>
    </div>

    <!-- Host Video -->
    <div class="form-group col-md-4 col-12">
        <label for="host_video">Enable Host Video</label>
        <small class="form-text text-muted">Choose whether the host's video should be enabled when the meeting starts.</small>
        <select name="host_video" id="host_video" class="form-control">
            <option value="0" {{ $hostVideo == '0' ? 'selected' : '' }}>No</option>
            <option value="1" {{ $hostVideo == '1' ? 'selected' : '' }}>Yes</option>
        </select>
    </div>

    <!-- Audio Option -->
    <div class="form-group col-md-4 col-12">
        <label for="audio_option">Audio Option</label>
        <small class="form-text text-muted">Select how participants can connect to audio: by computer, telephone, or both.</small>
        <select name="audio_option" id="audio_option" class="form-control">
            <option value="both" {{ $audioOption == 'both' ? 'selected' : '' }}>Both</option>
            <option value="voip" {{ $audioOption == 'voip' ? 'selected' : '' }}>Computer Audio Only</option>
            <option value="telephony" {{ $audioOption == 'telephony' ? 'selected' : '' }}>Telephone Only</option>
        </select>
    </div>
</div>