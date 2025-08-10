<!DOCTYPE html>
<html>
<head>
    <title>Course Card Style Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .debug-info { background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .course-card { border: 1px solid #ddd; border-radius: 8px; overflow: hidden; width: 300px; margin: 20px 0; }
        .image-box { position: relative; height: 200px; background: #eee; }
        .image-box img { width: 100%; height: 100%; object-fit: cover; }
        .image-overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.3); z-index: 1; }
        .course-card-body { padding: 15px; }
        .course-card-gray-hover .image-box img { filter: grayscale(100%) brightness(0.8); transition: filter 0.3s ease; }
        .course-card-gray-hover .image-box:hover img { filter: grayscale(0%) brightness(1); }
        .course-card-dark-overlay .image-overlay { transition: opacity 0.3s ease; }
        .course-card-dark-overlay .image-box:hover .image-overlay { opacity: 0; }
        .course-card-white-overlay .image-overlay { transition: opacity 0.3s ease; }
        .course-card-white-overlay .image-box:hover .image-overlay { opacity: 0; }
    </style>
</head>
<body>
    <h1>Course Card Style Debug</h1>
    
    <div class="debug-info">
        <h3>Debug Information:</h3>
        <pre>{{ print_r(debugCourseCardStyle(), true) }}</pre>
    </div>
    
    <h3>Test Course Card (Current Style: {{ getCourseCardStyle() }})</h3>
    
    <div class="course-card {{ getCourseCardStyleClass() }}">
        <div class="image-box">
            @if(getCourseCardStyle() === 'dark_overlay')
                <div class="image-overlay"></div>
            @endif
            <img src="https://via.placeholder.com/300x200/4CAF50/FFFFFF?text=Course+Image" alt="Course Image">
        </div>
        <div class="course-card-body">
            <h4>Sample Course Title</h4>
            <p>This is a sample course description to test the card styling.</p>
        </div>
    </div>
    
    <h3>Test All Styles:</h3>
    
    <div style="display: flex; gap: 20px;">
        <div>
            <h4>Dark Overlay Style</h4>
            <div class="course-card course-card-dark-overlay">
                <div class="image-box">
                    <div class="image-overlay"></div>
                    <img src="https://via.placeholder.com/300x200/2196F3/FFFFFF?text=Dark+Overlay" alt="Course Image">
                </div>
                <div class="course-card-body">
                    <h4>Dark Overlay Course</h4>
                    <p>Hover to see overlay fade.</p>
                </div>
            </div>
        </div>
        
        <div>
            <h4>White Overlay Style</h4>
            <div class="course-card course-card-white-overlay">
                <div class="image-box">
                    <div class="image-overlay"></div>
                    <img src="https://via.placeholder.com/300x200/4CAF50/FFFFFF?text=White+Overlay" alt="Course Image">
                </div>
                <div class="course-card-body">
                    <h4>White Overlay Course</h4>
                    <p>Hover to see overlay fade.</p>
                </div>
            </div>
        </div>
        
        <div>
            <h4>Gray Hover Style</h4>
            <div class="course-card course-card-gray-hover">
                <div class="image-box">
                    <img src="https://via.placeholder.com/300x200/FF9800/FFFFFF?text=Gray+Hover" alt="Course Image">
                </div>
                <div class="course-card-body">
                    <h4>Gray Hover Course</h4>
                    <p>Hover to see color appear.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
