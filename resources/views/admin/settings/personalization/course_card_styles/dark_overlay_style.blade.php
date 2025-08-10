<div class="card border border-gray300 rounded-lg">
    <div class="card-header bg-gray50 border-0">
        <div class="d-flex align-items-center">
            <div class="course-card-preview dark-overlay-preview mr-15">
                <div class="preview-image">
                    <div class="image-overlay"></div>
                    <img src="/assets/default/img/course-card-preview.jpg" alt="Dark Overlay Preview">
                </div>
            </div>
            <div>
                <h4 class="font-16 font-weight-bold text-dark-blue mb-5">{{ trans('admin/main.dark_overlay_style') }}</h4>
                <p class="font-14 text-gray">{{ trans('admin/main.dark_overlay_description') }}</p>
            </div>
        </div>
    </div>
    
    <div class="card-body">
        <div class="form-group">
            <label class="input-label">{{ trans('admin/main.overlay_color') }}</label>
            <input type="color" name="value[course_card_dark_overlay_color]" 
                   value="{{ (!empty($itemValue) and !empty($itemValue['course_card_dark_overlay_color'])) ? $itemValue['course_card_dark_overlay_color'] : '#000000' }}" 
                   class="form-control" />
        </div>
        
        <div class="form-group">
            <label class="input-label">{{ trans('admin/main.overlay_opacity') }}</label>
            <input type="range" name="value[course_card_dark_overlay_opacity]" 
                   min="0" max="100" step="5"
                   value="{{ (!empty($itemValue) and !empty($itemValue['course_card_dark_overlay_opacity'])) ? $itemValue['course_card_dark_overlay_opacity'] : 30 }}" 
                   class="form-control" />
            <small class="form-text text-muted">{{ trans('admin/main.overlay_opacity_help') }}</small>
        </div>
        
        <div class="form-group">
            <label class="input-label">{{ trans('admin/main.transition_duration') }}</label>
            <input type="number" name="value[course_card_dark_overlay_duration]" 
                   min="0.1" max="2" step="0.1"
                   value="{{ (!empty($itemValue) and !empty($itemValue['course_card_dark_overlay_duration'])) ? $itemValue['course_card_dark_overlay_duration'] : 0.3 }}" 
                   class="form-control" />
            <small class="form-text text-muted">{{ trans('admin/main.transition_duration_help') }}</small>
        </div>
        
        <div class="form-group">
            <label class="input-label d-block">{{ trans('admin/main.enable_style') }}</label>
            <div class="custom-control custom-switch">
                <input type="checkbox" name="value[course_card_dark_overlay_enabled]" 
                       value="1" 
                       {{ (!empty($itemValue) and !empty($itemValue['course_card_dark_overlay_enabled'])) ? 'checked' : '' }}
                       class="custom-control-input" id="darkOverlayEnabled">
                <label class="custom-control-label" for="darkOverlayEnabled">{{ trans('admin/main.enable_dark_overlay') }}</label>
            </div>
        </div>
    </div>
</div>

<style>
.dark-overlay-preview {
    width: 80px;
    height: 60px;
    border-radius: 8px;
    overflow: hidden;
    position: relative;
}

.dark-overlay-preview .preview-image {
    width: 100%;
    height: 100%;
    position: relative;
}

.dark-overlay-preview .image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.3);
    z-index: 1;
}

.dark-overlay-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    position: relative;
    z-index: 0;
}
</style>
