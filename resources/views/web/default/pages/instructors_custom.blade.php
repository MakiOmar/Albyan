@extends(getTemplate().'.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/vendors/leaflet/leaflet.css">
    <link rel="stylesheet" href="/assets/default/vendors/select2/select2.min.css">
    <style>
        .instructors-filters {
            background: #f8f9fa;
            padding: 15px 0;
            margin: 20px 0;
            border-radius: 8px;
        }
        
        .filter-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .filter-dropdown {
            min-width: 180px;
        }
        
        .instructors-pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 20px 0;
        }
        
        .pagination {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 0;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .pagination li {
            border-right: 1px solid #dee2e6;
        }
        
        .pagination li:last-child {
            border-right: none;
        }
        
        .pagination a, .pagination span {
            display: block;
            padding: 8px 12px;
            text-decoration: none;
            color: #007bff;
            background: white;
            transition: all 0.3s ease;
        }
        
        .pagination a:hover {
            background: #e9ecef;
        }
        
        .pagination .active span {
            background: #01477d;
            color: white;
        }
        
        .pagination .disabled span {
            color: #6c757d;
            background: #f8f9fa;
            cursor: not-allowed;
        }
        
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .results-info {
            color: #6c757d;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .filter-controls {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-dropdown {
                min-width: auto;
            }
        }
    </style>
@endpush

@push('styles')
    <style>
        .instructors-filters {
            background: #f8f9fa;
            padding: 20px 0;
            margin-bottom: 30px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .filter-controls {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .filter-dropdown {
            min-width: 150px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 8px 12px;
            background: white;
            font-size: 14px;
            margin-right: 10px;
        }
        
        .filter-dropdown:last-child {
            margin-right: 0;
        }
        
        .filter-dropdown:focus {
            border-color: #01477d;
            box-shadow: 0 0 0 0.2rem rgba(1, 71, 125, 0.25);
        }
        
        .instructors-pagination {
            margin-top: 30px;
            padding: 20px 0;
        }
        
        .instructors-pagination .d-flex {
            display: flex !important;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }
        
        .results-info {
            color: #666;
            font-size: 14px;
        }
        
        .pagination {
            display: flex;
            align-items: center;
            gap: 5px;
            margin: 0;
            padding: 0;
            list-style: none;
        }
        
        .pagination li {
            margin: 0;
        }
        
        .pagination a,
        .pagination span {
            display: inline-block;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
            background: white;
            min-width: 40px;
            text-align: center;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        
        .pagination a:hover {
            background: #01477d;
            color: white;
            border-color: #01477d;
            text-decoration: none;
        }
        
        .pagination .active span {
            background: #01477d !important;
            color: white !important;
            border-color: #01477d !important;
        }
        
        .pagination .disabled span {
            color: #999;
            background: #f5f5f5;
            border-color: #ddd;
            cursor: not-allowed;
        }
        
        /* Ensure active pagination always has the correct background */
        .pagination li.active span {
            background: #01477d !important;
            color: white !important;
            border-color: #01477d !important;
        }
        
        .loading {
            opacity: 0.6;
            pointer-events: none;
            position: relative;
        }
        
        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #01477d;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            z-index: 1000;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Search input styling */
        #searchInput {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 8px 12px;
            font-size: 14px;
        }
        
        #searchInput:focus {
            border-color: #01477d;
            box-shadow: 0 0 0 0.2rem rgba(1, 71, 125, 0.25);
        }
        
        /* Results info styling */
        .results-info {
            color: #666;
            font-size: 14px;
            font-weight: 500;
        }
        
        /* Search and results row styling */
        .search-results-row {
            background: #f8f9fa;
            padding: 15px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        /* Mobile-specific styles */
        @media (max-width: 768px) {
            .instructors-filters {
                padding: 15px 0;
                margin: 15px 0;
            }
            
            .filters-section {
                background: white;
                padding: 20px;
                border-radius: 8px;
                border: 1px solid #e9ecef;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            
            .filter-item {
                margin-bottom: 10px;
            }
            
            .filter-item:last-child {
                margin-bottom: 0;
            }
            
            .filter-dropdown {
                width: 100%;
                min-width: auto;
                margin: 0;
                padding: 12px 15px;
                font-size: 16px; /* Prevents zoom on iOS */
                border: 1px solid #ddd;
                border-radius: 6px;
                background: white;
                -webkit-appearance: none;
                -moz-appearance: none;
                appearance: none;
                background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6,9 12,15 18,9'%3e%3c/polyline%3e%3c/svg%3e");
                background-repeat: no-repeat;
                background-position: right 12px center;
                background-size: 16px;
                padding-right: 40px;
            }
            
            .results-section {
                background: #f8f9fa;
                padding: 15px;
                border-radius: 6px;
                border: 1px solid #e9ecef;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            
            .results-info {
                font-size: 14px;
                color: #666;
                font-weight: 500;
            }
            
            .pagination-section {
                background: white;
                padding: 20px;
                border-radius: 8px;
                border: 1px solid #e9ecef;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            
            .pagination {
                justify-content: center;
                flex-wrap: wrap;
                gap: 5px;
            }
            
            .pagination li {
                margin: 0 2px;
            }
            
            .pagination a,
            .pagination span {
                padding: 8px 12px;
                font-size: 14px;
                min-width: 40px;
                text-align: center;
            }
            
            .search-results-row .row {
                flex-direction: column;
                gap: 10px;
            }
            
            .search-results-row .text-end {
                text-align: center !important;
            }
        }
        
        /* Desktop responsive adjustments */
        @media (min-width: 769px) {
            .instructors-filters .d-flex {
                flex-direction: row;
                align-items: center;
                gap: 15px;
            }
            
            .filter-dropdown {
                min-width: auto;
                margin-right: 0;
                margin-bottom: 0;
            }
        }
    </style>
@endpush

@section('content')

<div class="container text-center mt-5">
    <svg width="100" height="170" style="position: absolute;right: 0; top: 100px" viewBox="0 0 148 270" fill="none" xmlns="http://www.w3.org/2000/svg">
        <g filter="url(#filter0_f_167_3760)">
        <path d="M52.1411 230.757C25.347 227.929 11.6628 197.155 27.5096 175.365L129.483 35.148C145.33 13.358 178.822 16.8939 189.77 41.5126L260.215 199.933C271.162 224.551 251.353 251.789 224.559 248.96L52.1411 230.757Z" fill="#BFE3C6" fill-opacity="0.5"/>
        </g>
        <defs>
        <filter id="filter0_f_167_3760" x="0.772705" y="0.729614" width="282.514" height="268.434" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
        <feFlood flood-opacity="0" result="BackgroundImageFix"/>
        <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>
        <feGaussianBlur stdDeviation="10" result="effect1_foregroundBlur_167_3760"/>
        </filter>
        </defs>
    </svg>

    <svg width="100" height="170" style="position: absolute;left: 0; top: 300px"  viewBox="0 0 142 280" fill="none" xmlns="http://www.w3.org/2000/svg">
        <g filter="url(#filter0_f_167_3759)">
        <path d="M-113.227 62.6108C-118.944 36.2813 -94.0145 13.6367 -68.3541 21.8506L96.7692 74.7063C122.43 82.9202 129.576 115.832 109.632 133.948L-18.704 250.521C-38.6477 268.636 -70.7232 258.369 -76.44 232.04L-113.227 62.6108Z" fill="#BFE3C6" fill-opacity="0.5"/>
        </g>
        <defs>
        <filter id="filter0_f_167_3759" x="-134.053" y="0.140381" width="275.167" height="279.501" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
        <feFlood flood-opacity="0" result="BackgroundImageFix"/>
        <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>
        <feGaussianBlur stdDeviation="10" result="effect1_foregroundBlur_167_3759"/>
        </filter>
        </defs>
    </svg>
    
    <svg width="90" height="180" style="position: absolute;right: 0; top: 80%" viewBox="0 0 106 284" fill="none" xmlns="http://www.w3.org/2000/svg">
    <g filter="url(#filter0_f_167_3758)">
    <path d="M21.8445 54.8684C22.0543 27.9262 51.3513 11.3145 74.579 24.9673L224.048 112.822C247.275 126.475 247.013 160.153 223.575 173.442L72.7564 258.959C49.3188 272.248 20.2842 255.182 20.494 228.24L21.8445 54.8684Z" fill="#BFE3C6" fill-opacity="0.5"/>
    </g>
    <defs>
    <filter id="filter0_f_167_3758" x="0.492676" y="0.0892334" width="260.819" height="283.476" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
    <feFlood flood-opacity="0" result="BackgroundImageFix"/>
    <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>
    <feGaussianBlur stdDeviation="10" result="effect1_foregroundBlur_167_3758"/>
    </filter>
    </defs>
    </svg>
    <svg width="100" height="170" style="position: absolute;left: 0; top: 100%" viewBox="0 0 148 270" fill="none" xmlns="http://www.w3.org/2000/svg">
        <g filter="url(#filter0_f_167_3761)">
        <path d="M-83.899 228.35C-110.64 225.055 -123.786 194.048 -107.563 172.537L-3.16485 34.1157C13.0588 12.6048 46.4848 16.723 57.002 41.5285L124.68 201.15C135.197 225.956 114.917 252.845 88.1764 249.55L-83.899 228.35Z" fill="#BFE3C6" fill-opacity="0.5"/>
        </g>
        <defs>
        <filter id="filter0_f_167_3761" x="-134.66" y="0.185478" width="282.168" height="269.64" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
        <feFlood flood-opacity="0" result="BackgroundImageFix"/>
        <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>
        <feGaussianBlur stdDeviation="10" result="effect1_foregroundBlur_167_3761"/>
        </filter>
        </defs>
    </svg>
    
    <!-- CEO Section -->
    <h2 class="fw-bold section-title-bg p-2 m-2">{{ trans('instructors.ceo_section') }}</h2>
    <br>
    <div class="row justify-content-center">
        @forelse($ceoUsers as $ceoUser)
            <div class="col-6 col-md-3">
                <a href="{{ $ceoUser->getProfileUrl() }}">
                    <img src="{{ $ceoUser->getAvatar(120) }}" class="rounded-circle border border-warning p-1" width="120" height="120" alt="{{ $ceoUser->full_name }}">
                    <center><strong>{{ $ceoUser->full_name }}</strong></center>
                </a>
            </div>
        @empty
            <div class="col-12 text-center">
                <p class="text-muted">{{ trans('instructors.no_ceo_found') }}</p>
            </div>
        @endforelse
    </div>

    <hr class="my-4">

    <!-- Instructors Section -->
    <h3 class="fw-bold section-title-bg p-2 m-2">{{ trans('instructors.instructors_section') }}</h3>
    <br>
    
    <!-- Filters and Pagination Row -->
    <div class="instructors-filters">
        <div class="container">
            <!-- Mobile Layout -->
            <div class="d-block d-md-none">
                <!-- Filters Section -->
                <div class="filters-section mb-3">
                    <div class="d-flex flex-column gap-2">
                        <div class="filter-item">
                            <select id="categoryFilter" class="form-control filter-dropdown w-100">
                                <option value="">{{ trans('instructors.all_categories') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request()->get('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->title }}
                                    </option>
                                    @if($category->subCategories->count() > 0)
                                        @foreach($category->subCategories as $subCategory)
                                            <option value="{{ $subCategory->id }}" {{ request()->get('category_id') == $subCategory->id ? 'selected' : '' }}>
                                                &nbsp;&nbsp;&nbsp;{{ $subCategory->title }}
                                            </option>
                                        @endforeach
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-item">
                            <select id="perPageFilter" class="form-control filter-dropdown w-100">
                                <option value="12" {{ request()->get('per_page', 20) == 12 ? 'selected' : '' }}>{{ trans('instructors.results_12') }}</option>
                                <option value="20" {{ request()->get('per_page', 20) == 20 ? 'selected' : '' }}>{{ trans('instructors.results_20') }}</option>
                                <option value="30" {{ request()->get('per_page', 20) == 30 ? 'selected' : '' }}>{{ trans('instructors.results_30') }}</option>
                                <option value="50" {{ request()->get('per_page', 20) == 50 ? 'selected' : '' }}>{{ trans('instructors.results_50') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Results Info Section -->
                <div class="results-section mb-3 text-center">
                    <div class="results-info">
                        {{ trans('instructors.showing_results', [
                            'from' => $instructors->firstItem() ?? 0,
                            'to' => $instructors->lastItem() ?? 0,
                            'total' => $instructors->total()
                        ]) }}
                    </div>
                </div>
                
                <!-- Pagination Section -->
                <div class="pagination-section">
                    @if($instructors->hasPages())
                        <div class="d-flex justify-content-center">
                            {{ $instructors->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Desktop Layout -->
            <div class="d-none d-md-flex align-items-center justify-content-between">
                <!-- Filters on the right -->
                <div class="d-flex align-items-center gap-4">
                    <div class="m-1">
                    <select id="categoryFilterDesktop" class="form-control filter-dropdown">
                        <option value="">{{ trans('instructors.all_categories') }}</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request()->get('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->title }}
                            </option>
                            @if($category->subCategories->count() > 0)
                                @foreach($category->subCategories as $subCategory)
                                    <option value="{{ $subCategory->id }}" {{ request()->get('category_id') == $subCategory->id ? 'selected' : '' }}>
                                        &nbsp;&nbsp;&nbsp;{{ $subCategory->title }}
                                    </option>
                                @endforeach
                            @endif
                        @endforeach
                    </select>
                    </div>
                    <div class="m-1">
                    <select id="perPageFilterDesktop" class="form-control filter-dropdown">
                        <option value="12" {{ request()->get('per_page', 20) == 12 ? 'selected' : '' }}>{{ trans('instructors.results_12') }}</option>
                        <option value="20" {{ request()->get('per_page', 20) == 20 ? 'selected' : '' }}>{{ trans('instructors.results_20') }}</option>
                        <option value="30" {{ request()->get('per_page', 20) == 30 ? 'selected' : '' }}>{{ trans('instructors.results_30') }}</option>
                        <option value="50" {{ request()->get('per_page', 20) == 50 ? 'selected' : '' }}>{{ trans('instructors.results_50') }}</option>
                    </select>
                    </div>
                </div>

                <div class="results-info">
                    {{ trans('instructors.showing_results', [
                        'from' => $instructors->firstItem() ?? 0,
                        'to' => $instructors->lastItem() ?? 0,
                        'total' => $instructors->total()
                    ]) }}
                </div>

                <!-- Pagination on the left -->
                <div class="pagination-container">
                    @if($instructors->hasPages())
                        {{ $instructors->appends(request()->query())->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
    {{--
    <!-- Search Bar and Results Info -->
    <div class="container mb-3 search-results-row">
        <div class="row align-items-center">
        
            <div class="col-md-6">
                <input type="text" id="searchInput" class="form-control" placeholder="{{ trans('instructors.search_placeholder') }}" value="{{ request()->get('search') }}">
            </div>
            
            <div class="col-md-6 text-end">
                
            </div>
        </div>
    </div>
    --}}
    <!-- Instructors List Container -->
    <div id="instructorsContainer">
        <div id="instructorsList" class="row justify-content-center">
            @forelse($instructors as $instructor)
                <div class="col-4 col-md-3 mt-2">
                    <a href="{{ $instructor->getProfileUrl() }}">
                        <img src="{{ $instructor->getAvatar(100) }}" class="rounded-circle bg-dark p-1" width="100" height="100" alt="{{ $instructor->full_name }}">
                    </a>
                    <center><strong>{{ $instructor->full_name }}</strong></center>
                </div>
            @empty
                <div class="col-12 text-center">
                    <p class="text-muted">{{ trans('instructors.no_instructors_found') }}</p>
                </div>
            @endforelse
        </div>
    </div>

    <hr class="my-4">

    <!-- Albayan Team Section -->
    <h3 class="fw-bold section-title-bg p-2 m-2">{{ trans('instructors.team_section') }}</h3>
    <br>
    <div class="row justify-content-center">
        @forelse($teamMembers as $teamMember)
            <div class="col-4 col-md-3">
                <a href="{{ $teamMember->getProfileUrl() }}">
                    <img src="{{ $teamMember->getAvatar(100) }}" class="rounded-circle bg-dark p-1" width="100" height="100" alt="{{ $teamMember->full_name }}">
                </a>
                <center><strong>{{ $teamMember->full_name }}</strong></center>
            </div>
        @empty
            <div class="col-12 text-center">
                <p class="text-muted">{{ trans('instructors.no_team_members_found') }}</p>
            </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts_bottom')
    <script src="/assets/vendors/leaflet/leaflet.min.js"></script>
    <script src="/assets/default/vendors/select2/select2.min.js"></script>
    <script>
        var leafletApiPath = '{{ getLeafletApiPath() }}';
        
        $(document).ready(function() {
            let currentPage = 1;
            let isLoading = false;
            
            // Sync mobile and desktop filters
            function syncFilters() {
                const categoryId = $('#categoryFilter').val() || $('#categoryFilterDesktop').val();
                const perPage = $('#perPageFilter').val() || $('#perPageFilterDesktop').val();
                
                $('#categoryFilter, #categoryFilterDesktop').val(categoryId);
                $('#perPageFilter, #perPageFilterDesktop').val(perPage);
            }
            
            // Handle filter changes for both mobile and desktop
            $('#categoryFilter, #categoryFilterDesktop, #perPageFilter, #perPageFilterDesktop').on('change', function() {
                syncFilters();
                
                const categoryId = $('#categoryFilter').val();
                const perPage = $('#perPageFilter').val();
                
                // Update URL parameters
                const url = new URL(window.location);
                if (categoryId) {
                    url.searchParams.set('category_id', categoryId);
                } else {
                    url.searchParams.delete('category_id');
                }
                if (perPage) {
                    url.searchParams.set('per_page', perPage);
                }
                url.searchParams.delete('page'); // Reset to first page
                
                window.location.href = url.toString();
            });
            
            // Initial sync
            syncFilters();
            
            // Initialize select2 for better dropdowns (desktop only)
            $('#categoryFilterDesktop, #perPageFilterDesktop').select2({
                minimumResultsForSearch: -1,
                width: 'auto'
            });
            
            // Initialize pagination active state on page load
            initializePaginationActiveState();
            
            // Handle filter changes (legacy for AJAX functionality)
            $('#categoryFilter, #perPageFilter').on('change', function() {
                currentPage = 1;
                loadInstructors();
            });
            
            // Handle search input with debounce (only if search input exists)
            let searchTimeout;
            if ($('#searchInput').length) {
                $('#searchInput').on('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(function() {
                        currentPage = 1;
                        loadInstructors();
                    }, 500);
                });
            }
            
            // Handle pagination clicks - use event delegation for dynamically created elements
            $(document).on('click', '.pagination a', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                console.log('Pagination link clicked:', $(this).attr('href'), $(this).data('page'));
                
                const href = $(this).attr('href');
                let page = 1;
                
                // Check if it's a data-page attribute (our custom pagination)
                if ($(this).data('page')) {
                    page = $(this).data('page');
                } else if (href) {
                    // Extract page from href (Laravel's pagination)
                    page = getParameterByName('page', href) || 1;
                }
                
                console.log('Page to load:', page);
                currentPage = parseInt(page);
                
                // Update active state immediately
                updatePaginationActiveState(currentPage);
                
                // If it's Laravel's pagination, we might want to do a full page reload
                // or handle it via AJAX. For now, let's use AJAX
                loadInstructors();
            });
            
            function loadInstructors() {
                if (isLoading) return;
                
                isLoading = true;
                $('#instructorsContainer').addClass('loading');
                
                const categoryId = $('#categoryFilter').val() || $('#categoryFilterDesktop').val();
                const perPage = $('#perPageFilter').val() || $('#perPageFilterDesktop').val();
                const searchTerm = $('#searchInput').length ? $('#searchInput').val().trim() : '';
                
                const params = {
                    section: 'instructors',
                    page: currentPage
                };
                
                // Add category_id only if it's not empty
                if (categoryId && categoryId !== '') {
                    params.category_id = categoryId;
                }
                
                // Add per_page only if it's not empty
                if (perPage && perPage !== '') {
                    params.per_page = perPage;
                }
                
                // Add search only if it's not empty
                if (searchTerm && searchTerm !== '') {
                    params.search = searchTerm;
                }
                
                $.ajax({
                    url: '/our-instructors',
                    method: 'GET',
                    data: params,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        $('#instructorsList').html(response.html);
                        
                        // Update pagination
                        updatePagination(response.pagination);
                        
                        // Update URL without page reload
                        updateURL(params);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading instructors:', error);
                        $('#instructorsList').html('<div class="col-12 text-center"><p class="text-danger">{{ trans("instructors.error_loading") }}</p></div>');
                    },
                    complete: function() {
                        isLoading = false;
                        $('#instructorsContainer').removeClass('loading');
                    }
                });
            }
            
            function updatePagination(pagination) {
                let paginationHtml = '';
                
                if (pagination.last_page > 1) {
                    paginationHtml = '<ul class="pagination">';
                    
                    // Previous button
                    if (pagination.current_page > 1) {
                        paginationHtml += `<li><a href="#" data-page="${pagination.current_page - 1}">&lt;</a></li>`;
                    } else {
                        paginationHtml += '<li class="disabled"><span>&lt;</span></li>';
                    }
                    
                    // Page numbers
                    const startPage = Math.max(1, pagination.current_page - 2);
                    const endPage = Math.min(pagination.last_page, pagination.current_page + 2);
                    
                    if (startPage > 1) {
                        paginationHtml += '<li><a href="#" data-page="1">1</a></li>';
                        if (startPage > 2) {
                            paginationHtml += '<li class="disabled"><span>...</span></li>';
                        }
                    }
                    
                    for (let i = startPage; i <= endPage; i++) {
                        if (i === pagination.current_page) {
                            paginationHtml += `<li class="active"><span>${i}</span></li>`;
                        } else {
                            paginationHtml += `<li><a href="#" data-page="${i}">${i}</a></li>`;
                        }
                    }
                    
                    if (endPage < pagination.last_page) {
                        if (endPage < pagination.last_page - 1) {
                            paginationHtml += '<li class="disabled"><span>...</span></li>';
                        }
                        paginationHtml += `<li><a href="#" data-page="${pagination.last_page}">${pagination.last_page}</a></li>`;
                    }
                    
                    // Next button
                    if (pagination.current_page < pagination.last_page) {
                        paginationHtml += `<li><a href="#" data-page="${pagination.current_page + 1}">&gt;</a></li>`;
                    } else {
                        paginationHtml += '<li class="disabled"><span>&gt;</span></li>';
                    }
                    
                    paginationHtml += '</ul>';
                }
                
                $('.pagination-container').html(paginationHtml);
                
                // Update results info in the search row
                $('.results-info').text(`{{ trans('instructors.showing_results', ['from' => ':from', 'to' => ':to', 'total' => ':total']) }}`
                    .replace(':from', pagination.from || 0)
                    .replace(':to', pagination.to || 0)
                    .replace(':total', pagination.total));
            }
            
            function updatePaginationActiveState(page) {
                // Remove active class from all pagination items
                $('.pagination li').removeClass('active');
                
                // First try to find our custom pagination with data-page attribute
                let $activeLink = $('.pagination li a[data-page="' + page + '"]');
                
                if ($activeLink.length > 0) {
                    // Our custom pagination
                    $activeLink.parent().addClass('active');
                    // Convert link to span for active state
                    const linkText = $activeLink.text();
                    $activeLink.replaceWith('<span>' + linkText + '</span>');
                } else {
                    // Laravel's pagination - find by href
                    $('.pagination li a').each(function() {
                        const href = $(this).attr('href');
                        if (href) {
                            const pageFromHref = getParameterByName('page', href);
                            if (pageFromHref == page) {
                                $(this).parent().addClass('active');
                                const linkText = $(this).text();
                                $(this).replaceWith('<span>' + linkText + '</span>');
                                return false; // break the loop
                            }
                        }
                    });
                }
            }
            
            function initializePaginationActiveState() {
                // Get current page from URL
                const currentPage = getParameterByName('page') || 1;
                
                // Handle Laravel's pagination links (they don't have data-page attributes)
                $('.pagination li').each(function() {
                    const $li = $(this);
                    const $link = $li.find('a');
                    const $span = $li.find('span');
                    
                    // Check if this is the active page (Laravel adds 'active' class to the li)
                    if ($li.hasClass('active')) {
                        // This is already marked as active by Laravel
                        return;
                    }
                    
                    // Check if this link points to the current page
                    if ($link.length > 0) {
                        const href = $link.attr('href');
                        if (href) {
                            const pageFromHref = getParameterByName('page', href);
                            if (pageFromHref == currentPage) {
                                $li.addClass('active');
                                $link.remove();
                                $li.append('<span>' + currentPage + '</span>');
                            }
                        }
                    }
                });
            }
            
            function updateURL(params) {
                const url = new URL(window.location);
                
                // Clear all existing parameters first
                url.search = '';
                
                // Add only non-empty parameters
                Object.keys(params).forEach(key => {
                    if (params[key] && params[key] !== '' && params[key] !== null && params[key] !== undefined) {
                        url.searchParams.set(key, params[key]);
                    }
                });
                
                window.history.pushState({}, '', url);
            }
            
            function getParameterByName(name, url) {
                if (!url) url = window.location.href;
                name = name.replace(/[\[\]]/g, '\\$&');
                const regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)');
                const results = regex.exec(url);
                if (!results) return null;
                if (!results[2]) return '';
                return decodeURIComponent(results[2].replace(/\+/g, ' '));
            }
        });
    </script>
    <script src="/assets/default/js/parts/contact.min.js"></script>
@endpush