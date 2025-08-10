<div class="card border border-gray300 rounded-lg">
    <div class="card-header bg-gray50 border-0">
        <div class="d-flex align-items-center">
            <div class="course-card-preview gray-hover-preview mr-15">
                <div class="preview-image">
                    <img src="/assets/default/img/course-card-preview.jpg" alt="Gray Hover Preview">
                </div>
            </div>
            <div>
                <h4 class="font-16 font-weight-bold text-dark-blue mb-5">{{ trans('admin/main.gray_hover_style') }}</h4>
                <p class="font-14 text-gray">{{ trans('admin/main.gray_hover_description') }}</p>
            </div>
        </div>
    </div>
    
    <div class="card-body">
        <div class="form-group">
            <label class="input-label">{{ trans('admin/main.gray_filter_intensity') }}</label>
            <input type="range" name="value[course_card_gray_filter_intensity]" 
                   min="0" max="100" step="10"
                   value="{{ (!empty($itemValue) and !empty($itemValue['course_card_gray_filter_intensity'])) ? $itemValue['course_card_gray_filter_intensity'] : 100 }}" 
                   class="form-control" />
            <small class="form-text text-muted">{{ trans('admin/main.gray_filter_intensity_help') }}</small>
        </div>
        
        <div class="form-group">
            <label class="input-label">{{ trans('admin/main.brightness') }}</label>
            <input type="range" name="value[course_card_brightness]" 
                   min="0.1" max="2" step="0.1"
                   value="{{ (!empty($itemValue) and !empty($itemValue['course_card_brightness'])) ? $itemValue['course_card_brightness'] : 0.8 }}" 
                   class="form-control" />
            <small class="form-text text-muted">{{ trans('admin/main.brightness_help') }}</small>
        </div>
        
        <div class="form-group">
            <label class="input-label">{{ trans('admin/main.transition_duration') }}</label>
            <input type="number" name="value[course_card_gray_hover_duration]" 
                   min="0.1" max="2" step="0.1"
                   value="{{ (!empty($itemValue) and !empty($itemValue['course_card_gray_hover_duration'])) ? $itemValue['course_card_gray_hover_duration'] : 0.3 }}" 
                   class="form-control" />
            <small class="form-text text-muted">{{ trans('admin/main.transition_duration_help') }}</small>
        </div>
        
        <div class="form-group">
            <label class="input-label d-block">{{ trans('admin/main.enable_style') }}</label>
            <div class="custom-control custom-switch">
                <input type="checkbox" name="value[course_card_gray_hover_enabled]" 
                       value="1" 
                       {{ (!empty($itemValue) and !empty($itemValue['course_card_gray_hover_enabled'])) ? 'checked' : '' }}
                       class="custom-control-input" id="grayHoverEnabled">
                <label class="custom-control-label" for="grayHoverEnabled">{{ trans('admin/main.enable_gray_hover') }}</label>
            </div>
        </div>
    </div>
</div>

<style>
.gray-hover-preview {
    width: 80px;
    height: 60px;
    border-radius: 8px;
    overflow: hidden;
    position: relative;
}

.gray-hover-preview .preview-image {
    width: 100%;
    height: 100%;
    position: relative;
}

.gray-hover-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    position: relative;
    z-index: 0;
    filter: grayscale(100%);
    transition: filter 0.3s ease;
}

.gray-hover-preview:hover img {
    filter: grayscale(0%);
}
</style>
