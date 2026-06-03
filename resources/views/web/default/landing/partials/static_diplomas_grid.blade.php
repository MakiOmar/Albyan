{{-- Static diploma programs grid (images: /store/1/diplomas-landing/{index}.webp) --}}
@php
    $waDigits = !empty($diplomaLandingWhatsapp) ? preg_replace('/\D/', '', $diplomaLandingWhatsapp) : '';
    $callNumber = $diplomaLandingCall ?? config('diploma_landing.call_number');

    $staticDiplomas = [
        [
            'index' => 1,
            'title' => 'البرنامج التدريبي في الامن السيبراني',
            'description' => 'يقدم البرنامج تدريباً متخصصاً في الأمن السيبراني، مع التركيز على المهارات العملية والتقنيات الحديثة لمواجهة التهديدات الرقمية، من خلال فهم الهجمات السيبرانية، التحليل الجنائي الرقمي، وأدوات الحماية الاحترافية، بما يؤهل المتدربين للعمل بكفاءة في البيئات التقنية الحساسة',
            'duration' => '60:00 ساعة',
            'price' => '4000 درهم اماراتي',
        ],
        [
            'index' => 2,
            'title' => 'البرنامج التدريبي في الذكاء الاصطناعي',
            'description' => 'يقدم البرنامج تدريباً عملياً في تطبيقات الذكاء الاصطناعي، مع التركيز على فهم التقنيات الحديثة وتوظيفها في تصميم حلول ذكية تخدم مختلف القطاعات، بما يساهم في تطوير المهارات المهنية ومواكبة التحول الرقمي',
            'duration' => '60:00 ساعة',
            'price' => '4000 درهم اماراتي',
        ],
        [
            'index' => 3,
            'title' => 'البرنامج التدريبي في البروتوكول الدبلوماسي',
            'description' => 'يستهدف هذا البرنامج العاملين والمهتمين بالمجالات الدبلوماسية والعلاقات الدولية، حيث يركز على تطوير مهارات التواصل الرسمي، وآداب السلوك، والتنظيم المهني، بما يؤهل المشاركين للتعامل بثقة واحترافية في البيئات الرسمية والدولية',
        ],
        [
            'index' => 4,
            'title' => 'البرنامج التدريبي في علم النفس الجنائي',
            'description' => 'يقدم البرنامج فهماً متخصصاً في علم النفس الجنائي وتحليل السلوك الإجرامي، مع التركيز على العوامل النفسية والاجتماعية المرتبطة بالجريمة، ويُناسب العاملين والمهتمين بمجالات التحقيقات، علم النفس، والعدالة الجنائية',
        ],
        [
            'index' => 5,
            'title' => 'البرنامج التدريبي في التصوير و المونتاج',
            'description' => 'برنامج تدريبي يهدف إلى تأهيل المشاركين بالمهارات الأساسية والمتقدمة في التصوير الفوتوغرافي وتصوير الفيديو والمونتاج باستخدام أحدث الأدوات والبرامج. يركز على الجانب التطبيقي لإنتاج محتوى بصري احترافي يخدم الأغراض الإعلامية والتسويقية والتعليمية، ويستهدف المبتدئين وطلاب الإعلام وصنّاع المحتوى وكل من يرغب في احتراف هذا المجال',
        ],
        [
            'index' => 6,
            'title' => 'البرنامج التدريبي في الصحة النفسية',
            'description' => 'برنامج تدريبي يهدف إلى تعزيز فهم الصحة النفسية وأهميتها، وتحليل اضطراباتها، وتطوير مهارات الدعم النفسي للأفراد. يركز على الوقاية والعلاج ودعم الصحة النفسية داخل المجتمع بأسلوب عملي وتطبيقي مناسب للمهتمين والممارسين في هذا المجال',
        ],
        [
            'index' => 7,
            'title' => 'البرنامج التدريبي في تخليص المعاملات الحكومية',
            'description' => 'برنامج تدريبي يهدف إلى تزويد المشاركين بالمعرفة والمهارات العملية في التعامل مع الجهات الحكومية، بما يشمل فهم الإجراءات الرسمية، تخليص المعاملات، خدمات الحكومة الذكية، والتأشيرات والإقامات وتصاريح العمل. كما يركز على إعداد وتقديم الملفات والتواصل الفعّال مع الجهات الرسمية بكفاءة واحترافية',
        ],
        [
            'index' => 8,
            'title' => 'البرنامج التدريبي في العلاج السلوكي المعرفي',
            'description' => 'برنامج تدريبي يهدف إلى تعريف المشاركين بأسس العلاج السلوكي المعرفي وتطبيقاته العملية، مع تطوير مهارات تعديل الأفكار السلبية والتعامل مع القلق والخوف. يركز على بناء علاقة علاجية فعّالة وتصميم خطط علاجية مخصصة بأسلوب علمي وتطبيقي',
        ],
        [
            'index' => 9,
            'title' => 'البرنامج التدريبي في ادارة الموارد البشرية',
            'description' => 'برنامج تدريبي يهدف إلى تأهيل المشاركين بالمهارات العملية في إدارة الموارد البشرية وفق متطلبات سوق العمل الإماراتي، بما يشمل التوظيف، الرواتب، شؤون الموظفين، والسياسات الداخلية، مع فهم متكامل لدورة حياة الموظف والالتزام المهني داخل المؤسسات',
        ],
        [
            'index' => 10,
            'title' => 'البرنامج التدريبي في ادارة الازمات و الكوارث',
            'description' => 'برنامج تدريبي يهدف إلى تطوير مهارات إدارة الأزمات والكوارث من خلال فهم أساليب التخطيط الاستباقي، تقييم المخاطر، واتخاذ القرارات الفعّالة أثناء الأزمات، بما يعزز الجاهزية المؤسسية والتعامل الاحترافي مع التحديات والطوارئ',
        ],
        [
            'index' => 11,
            'title' => 'البرنامج التدريبي في علم ادارة التداول',
            'description' => 'برنامج تدريبي يهدف إلى تزويد المشاركين بالمعرفة العملية ومهارات التحليل المالي وإدارة التداول في الأسواق المالية، مع التركيز على تحليل الأسواق، إدارة المخاطر، وبناء استراتيجيات استثمار وتداول تساعد على اتخاذ قرارات مالية أكثر كفاءة واحترافية',
        ],
        [
            'index' => 12,
            'title' => 'البرنامج التدريبي في تحليل البيانات بالذكاء الاصطناعي',
            'description' => 'برنامج تدريبي احترافي يركز على تحليل البيانات بالذكاء الاصطناعي لتحويل البيانات إلى رؤى واستراتيجيات ذكية تساعد على اتخاذ قرارات دقيقة، مع استخدام أحدث الأدوات والتقنيات المطلوبة في سوق العمل الرقمي',
        ],
        [
            'index' => 13,
            'title' => 'البرنامج التدريبي في تصميم الازياء',
            'description' => 'برنامج تدريبي احترافي يهدف إلى تطوير مهارات تصميم الأزياء من الفكرة حتى التنفيذ، من خلال تعلم الرسم، اختيار الأقمشة، والتصميم الإبداعي بأسلوب يجمع بين الفن والابتكار والمعرفة العملية، لتأهيل المشاركين لدخول عالم الموضة باحترافية.',
        ],
        [
            'index' => 14,
            'title' => 'البرنامج التدريبي في التصميم الداخلي',
            'description' => 'برنامج تدريبي يهدف إلى تأهيل المشاركين للعمل في مجال الديكور والتصميم الداخلي من خلال فهم أساسيات المخططات، الخامات، والتشطيبات الداخلية، مع تطوير مهارات التسعير والتواصل البيعي لزيادة القدرة على إقناع العملاء وإغلاق الصفقات باحترافية.',
        ],
        [
            'index' => 15,
            'title' => 'البرنامج التدريبي في تصميم العطور',
            'description' => 'برنامج تدريبي يعرّف المشاركين بأساسيات صناعة العطور وتركيب الروائح باحترافية، من خلال فهم المكونات العطرية وأساليب المزج والتقييم، مع تطوير مهارات بناء علامة عطرية مميزة واحتراف هذا المجال إبداعيًا وتجاريًا.',
        ],
    ];
@endphp

<section class="home-sections container category-courses-home-section" id="dl-courses">
    <div class="px-20 px-md-0">
        <h2 class="section-title">{{ trans('home.latest_classes') }}</h2>
    </div>

    <div class="row mt-10">
        @foreach($staticDiplomas as $diploma)
            @php
                $diplomaTitle = $diploma['title'];
                $waMessage = rawurlencode('مرحبًا، أرغب بالاستفسار عن دبلومة ' . $diplomaTitle);
                $imageUrl = '/store/1/diplomas-landing/' . $diploma['index'] . '.webp';
            @endphp
            <div class="col-12 col-md-6 col-lg-4 mt-20">
                <div class="diploma-static-card webinar-card {{ getCourseCardStyleClass() }} h-100 d-flex flex-column">
                    <figure class="flex-grow-1 d-flex flex-column mb-0">
                        <div class="image-box">
                            <img
                                src="{{ $imageUrl }}"
                                class="img-cover"
                                alt="{{ $diplomaTitle }}"
                                width="300"
                                height="200"
                                loading="lazy"
                                onerror="this.onerror=null;this.src='/assets/default/img/placeholder.svg';"
                            >
                        </div>
                        <figcaption class="webinar-card-body d-flex flex-column flex-grow-1">
                            <h3 class="mt-15 webinar-title font-weight-bold font-16 text-dark-blue">{{ $diplomaTitle }}</h3>
                            <p class="font-14 text-gray mt-10 mb-0 diploma-static-card__description">{{ $diploma['description'] }}</p>

                            @if(!empty($diploma['duration']) || !empty($diploma['price']))
                                <div class="d-flex flex-wrap align-items-center justify-content-between mt-15 gap-2">
                                    @if(!empty($diploma['duration']))
                                        <div class="d-flex align-items-center">
                                            <i data-feather="clock" width="20" height="20" class="webinar-icon ml-1"></i>
                                            <span class="duration font-14">{{ $diploma['duration'] }}</span>
                                        </div>
                                    @endif
                                    @if(!empty($diploma['price']))
                                        <div class="webinar-price-box">
                                            <span class="real font-14 font-weight-bold">{{ $diploma['price'] }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if(!empty($waDigits) || !empty($callNumber))
                                <div class="diploma-landing-course-actions d-flex justify-content-center flex-wrap gap-2 mt-auto pt-20">
                                    @if(!empty($callNumber))
                                        <a href="tel:{{ $callNumber }}" class="btn btn-outline-primary btn-sm">
                                            <i data-feather="phone" width="16" height="16"></i>
                                            {{ trans('update.call_us') }}
                                        </a>
                                    @endif
                                    @if(!empty($waDigits))
                                        <a href="https://wa.me/{{ $waDigits }}?text={{ $waMessage }}" target="_blank" rel="noopener" class="btn btn-success btn-sm">
                                            <i class="fab fa-whatsapp"></i>
                                            {{ trans('public.whatsapp') }}
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </figcaption>
                    </figure>
                </div>
            </div>
        @endforeach
    </div>
</section>

<style>
    .diploma-static-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }
    .diploma-static-card .image-box {
        position: relative;
        overflow: hidden;
    }
    .diploma-static-card .image-box img {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }
    .diploma-static-card__description {
        line-height: 1.7;
    }
</style>
