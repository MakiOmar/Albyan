@extends('web.default.layouts.cyber_security_landing')

@php
    $cslConfig = 'business_admin_landing';
    $cslWhatsapp = config("{$cslConfig}.whatsapp_number");
    $cslCall = config("{$cslConfig}.call_number");
    $cslWhatsappDigits = !empty($cslWhatsapp) ? preg_replace('/\D/', '', $cslWhatsapp) : '';
@endphp

@section('content')

    {{-- Hero + registration form --}}
    <section class="csl-hero csl-hero-with-form" id="top">
        <div class="csl-container">
            <div class="csl-hero-grid">
                <div class="csl-hero-content">
                    <h1>احصل على دبلومة إدارة الأعمال البريطانية المعتمدة من Qualifi</h1>
                    <p class="csl-hero-lead">طوّر مهاراتك القيادية والإدارية واحصل على شهادة معتمدة محليًا ودوليًا تساعدك على تعزيز فرص الترقية والتقدم المهني.</p>
                    <div class="csl-highlights">
                        <div class="csl-highlight-item">شهادة بريطانية معتمدة محلياً ودولياً</div>
                        <div class="csl-highlight-item">متاح أونلاين أو حضوري</div>
                        <div class="csl-highlight-item">دبلومة سنتين مع إمكانية الدراسة المكثفة خلال 6 أشهر</div>
                        <div class="csl-highlight-item">ابدأ الآن بدفعة أولى 10,000 درهم وتقسيط عبر تابي وتمارا بدفعة أولى 2,500 درهم فقط</div>
                        <div class="csl-highlight-item">24 مادة احترافية تغطي أهم مجالات الإدارة الحديثة</div>
                        <div class="csl-highlight-item">مناسبة للموظفين، ورواد الأعمال، وأصحاب المشاريع، وكل من يطمح إلى التطوير المهني</div>
                    </div>
                </div>
                <div class="csl-hero-form-col" id="register">
                    @include('web.default.forms.partials.business_admin_landing_form')
                </div>
            </div>
        </div>
    </section>

    {{-- Curriculum --}}
    <section class="csl-section csl-section-alt" id="curriculum">
        <div class="csl-container">
            <h2 class="csl-section-title">محتوى الدبلومة</h2>
            <div class="csl-cards-grid">
                <div class="csl-card">
                    <h3>إدارة الأعمال والتخطيط المؤسسي</h3>
                    <ul>
                        <li>المفاهيم والتطبيقات العامة في إدارة الأعمال</li>
                        <li>التخطيط الاستراتيجي</li>
                        <li>تحليل بيئة العمل</li>
                        <li>القيادة والإدارة الحديثة</li>
                        <li>أساليب اتخاذ القرار</li>
                    </ul>
                </div>
                <div class="csl-card">
                    <h3>التسويق وتعزيز العلامة التجارية</h3>
                    <ul>
                        <li>التسويق الاستراتيجي</li>
                        <li>تحليل السوق والمنافسة</li>
                        <li>بناء الهوية المؤسسية</li>
                        <li>العلاقات العامة والترويج</li>
                        <li>تعزيز العلامة التجارية</li>
                    </ul>
                </div>
                <div class="csl-card">
                    <h3>إدارة العمل الجماعي وبناء فرق العمل</h3>
                    <ul>
                        <li>بناء فرق العمل الفعالة</li>
                        <li>إدارة الأفراد وتحفيز الموظفين</li>
                        <li>التواصل المؤسسي</li>
                        <li>حل النزاعات</li>
                        <li>اتخاذ القرار الجماعي</li>
                    </ul>
                </div>
                <div class="csl-card">
                    <h3>إدارة المخاطر والأزمات والكوارث</h3>
                    <ul>
                        <li>تحليل المخاطر</li>
                        <li>إدارة الأزمات</li>
                        <li>خطط الطوارئ</li>
                        <li>الأمن والسلامة المهنية</li>
                        <li>حوكمة المخاطر</li>
                    </ul>
                </div>
                <div class="csl-card">
                    <h3>إدارة مخاطر الأمن السيبراني</h3>
                    <ul>
                        <li>مفاهيم الأمن السيبراني</li>
                        <li>حماية البيانات والمعلومات</li>
                        <li>التهديدات الرقمية وأساليب الوقاية</li>
                        <li>سياسات الأمان المؤسسي</li>
                    </ul>
                </div>
                <div class="csl-card">
                    <h3>إدارة مخاطر الشائعات</h3>
                    <ul>
                        <li>مفهوم الشائعات وتأثيرها</li>
                        <li>استراتيجيات الحد من انتشارها</li>
                        <li>إدارة السمعة المؤسسية</li>
                        <li>تحليل المحتوى الإعلامي</li>
                    </ul>
                </div>
                <div class="csl-card">
                    <h3>إدارة الوقت وتحسين الإنتاجية</h3>
                    <ul>
                        <li>تنظيم الوقت</li>
                        <li>إدارة الأولويات</li>
                        <li>إدارة المهام</li>
                        <li>رفع الكفاءة التشغيلية</li>
                        <li>تحسين الإنتاجية</li>
                    </ul>
                </div>
                <div class="csl-card">
                    <h3>التفكير الإبداعي ومفاتيح الابتكار</h3>
                    <ul>
                        <li>التفكير الإبداعي</li>
                        <li>تنمية ثقافة الابتكار</li>
                        <li>تطوير الحلول الإبداعية</li>
                        <li>الابتكار في بيئة العمل</li>
                    </ul>
                </div>
                <div class="csl-card">
                    <h3>المسؤولية المجتمعية للشركات</h3>
                    <ul>
                        <li>مفهوم المسؤولية المجتمعية</li>
                        <li>المبادرات المجتمعية</li>
                        <li>التنمية المستدامة</li>
                        <li>إعداد التقارير المجتمعية</li>
                    </ul>
                </div>
                <div class="csl-card">
                    <h3>قياس مؤشرات الأداء الرئيسية KPI</h3>
                    <ul>
                        <li>تحديد مؤشرات الأداء</li>
                        <li>قياس النتائج</li>
                        <li>التحليل الإحصائي</li>
                        <li>إعداد التقارير الإدارية</li>
                    </ul>
                </div>
                <div class="csl-card">
                    <h3>صياغة العقود والمستندات</h3>
                    <ul>
                        <li>مبادئ الصياغة المهنية</li>
                        <li>مراجعة العقود</li>
                        <li>تحليل المخاطر القانونية</li>
                        <li>التوثيق الرقمي</li>
                    </ul>
                </div>
                <div class="csl-card">
                    <h3>استراتيجيات التميز في خدمة وإسعاد المتعاملين</h3>
                    <ul>
                        <li>تجربة المتعامل</li>
                        <li>قياس رضا المتعاملين</li>
                        <li>تحسين جودة الخدمات</li>
                        <li>بناء الولاء المؤسسي</li>
                    </ul>
                </div>
            </div>
            <div class="csl-card mt-4 text-center">
                <h3 class="mb-2">مشروع التخرج العملي</h3>
                <p class="mb-0 text-white-50">مشروع تطبيقي متكامل يساعد الدارس على توظيف المهارات والمعارف التي اكتسبها خلال البرنامج في بيئة عمل تحاكي الواقع العملي، بما يعزز فهمه للممارسات الإدارية الحديثة والتحديات المؤسسية المختلفة.</p>
            </div>
            <div class="csl-section-cta">
                <a href="#register" class="csl-btn csl-btn-primary">سجل الآن</a>
            </div>
        </div>
    </section>

    {{-- Why choose --}}
    <section class="csl-section csl-section-light" id="why-choose">
        <div class="csl-container">
            <h2 class="csl-section-title">ليش تختار هالدبلومة؟</h2>
            <p class="mb-3">من خلال البرنامج ستتمكن من تطوير فهمك للمفاهيم الإدارية الحديثة، وتعزيز مهاراتك في التخطيط والتنظيم وإدارة فرق العمل وتحليل المخاطر وقياس الأداء وتحسين الإنتاجية داخل المؤسسات.</p>
            <p class="mb-0">كما يساعدك البرنامج على تطوير مهاراتك المهنية بما يتناسب مع متطلبات بيئات العمل الحديثة وفرص الترقية بالمؤسسات.</p>
            <div class="csl-section-cta">
                <a href="#register" class="csl-btn csl-btn-primary">سجل الآن</a>
            </div>
        </div>
    </section>

    {{-- Who is it for --}}
    <section class="csl-section csl-section-alt" id="audience">
        <div class="csl-container">
            <h2 class="csl-section-title">الدبلومة مناسبة لك إذا كنت من؟</h2>
            <div class="csl-highlights">
                <div class="csl-highlight-item">الموظفين الراغبين في تطوير مسارهم المهني</div>
                <div class="csl-highlight-item">الباحثين عن فرص أكبر للترقية الوظيفية</div>
                <div class="csl-highlight-item">رواد الأعمال وأصحاب المشاريع</div>
                <div class="csl-highlight-item">موظفي القطاع الحكومي</div>
                <div class="csl-highlight-item">موظفي القطاع الخاص</div>
                <div class="csl-highlight-item">الراغبين في تطوير مهاراتهم الإدارية</div>
                <div class="csl-highlight-item">الباحثين عن شهادة معتمدة محلياً وعالمياً</div>
                <div class="csl-highlight-item">الطامحين لفهم أعمق لإدارة الأعمال الحديثة</div>
            </div>
            <div class="csl-section-cta">
                <a href="#register" class="csl-btn csl-btn-primary">سجل الآن</a>
            </div>
        </div>
    </section>

    {{-- Fees --}}
    <section class="csl-section csl-section-light" id="fees">
        <div class="csl-container">
            <h2 class="csl-section-title text-center">الرسوم وخيارات التقسيط</h2>
            <div class="csl-pricing-card">
                <p class="mb-1">رسوم الدبلومة كاملة</p>
                <div class="csl-price">40,000 <span class="font-20">درهم</span></div>
                <p class="mt-3 mb-4 text-left">يمكنك بدء الدبلومة بدفعة أولى <strong>10,000 درهم</strong>، ومتاح التقسيط عبر تابي أو تمارا بقيمة <strong>2,500 درهم</strong> للدفعة الأولى، أما المبلغ المتبقي فيتم تقسيطه على فترة الدراسة أو عبر تابي وتمارا.</p>
                <p class="mt-4 mb-3 font-weight-bold">الرسوم تشمل:</p>
                <ul class="list-unstyled text-left mb-4">
                    <li class="mb-2">✔️ الدراسة الأكاديمية</li>
                    <li class="mb-2">✔️ التطبيقات العملية</li>
                    <li class="mb-2">✔️ مشروع التخرج</li>
                    <li class="mb-2">✔️ الشهادة المعتمدة</li>
                </ul>
                <p class="font-weight-bold mb-2">خيارات الدفع:</p>
                <ul class="list-unstyled text-left mb-0">
                    <li class="mb-2">• التقسيط عبر Tabby</li>
                    <li class="mb-2">• التقسيط عبر Tamara</li>
                </ul>
            </div>
            <div class="csl-section-cta">
                <a href="#register" class="csl-btn csl-btn-primary">سجل الآن</a>
            </div>
        </div>
    </section>

    {{-- Testimonials --}}
    <section class="csl-section csl-section-alt" id="testimonials">
        <div class="csl-container">
            <h2 class="csl-section-title">آراء الطلاب</h2>
            <div class="csl-testimonials">
                <blockquote class="csl-testimonial">استفدت بشكل كبير من محتوى الدبلومة وطريقة طرح المفاهيم الإدارية بشكل عملي.</blockquote>
                <blockquote class="csl-testimonial">الدبلومة ساعدتني على تطوير مهاراتي في التخطيط وإدارة العمل بشكل أفضل.</blockquote>
                <blockquote class="csl-testimonial">من أكثر الأمور اللي استفدت منها تنوع المحاور وربطها بواقع العمل والمؤسسات.</blockquote>
            </div>
            <div class="csl-section-cta">
                <a href="#register" class="csl-btn csl-btn-primary">سجل الآن</a>
            </div>
        </div>
    </section>

    {{-- FAQ --}}
    <section class="csl-section csl-section-light" id="faq">
        <div class="csl-container">
            <h2 class="csl-section-title">الأسئلة الشائعة</h2>
            <div class="csl-faq" id="cslFaqAccordion">
                @php
                    $faqs = [
                        [
                            'q' => 'ما هي Qualifi؟',
                            'a' => 'Qualifi هي جهة بريطانية مانحة للمؤهلات (Awarding Organisation) معترف بها ومنظمة في المملكة المتحدة، وتقدم مؤهلات ودبلومات مهنية وأكاديمية وفق معايير جودة دولية.',
                        ],
                        [
                            'q' => 'لماذا تُعد مؤهلات Qualifi مميزة؟',
                            'a' => '<ul class="mb-0 pr-3"><li>جهة بريطانية معترف بها.</li><li>مناهج حديثة مرتبطة باحتياجات سوق العمل.</li><li>شهادات ذات قيمة مهنية وأكاديمية دولية.</li><li>إمكانية استكمال المسار الأكاديمي في العديد من الجامعات والمؤسسات التعليمية الشريكة.</li><li>تركيز على المهارات العملية والتطبيقية المطلوبة للتطور الوظيفي</li></ul>',
                            'html' => true,
                        ],
                        [
                            'q' => 'هل أحتاج خبرة مسبقة؟',
                            'a' => 'لا، الدبلومة مناسبة للراغبين في تطوير مهاراتهم الإدارية والمهنية، سواء كانوا في بداية مسيرتهم المهنية أو لديهم خبرة سابقة، لأننا نبدأ من الصفر إلى الاحتراف.',
                        ],
                        [
                            'q' => 'هل الشهادة معتمدة؟',
                            'a' => 'نعم، الدبلومة معتمدة من Qualifi البريطانية ومعترف بها محلياً وعالمياً، مع إمكانية الحصول على شهادة معتمدة من هيئة المعرفة والتنمية البشرية KHDA، ومتاح أيضاً التصديق من وزارة الخارجية الإماراتية.',
                        ],
                        [
                            'q' => 'كم مدة الدراسة؟',
                            'a' => 'مدة الدبلومة الأساسية سنتين وفق النظام البريطاني المعتمد، كما تتوفر إمكانية الدراسة المكثفة وإنجاز محتوى الدبلومة خلال 6 شهور حسب الخطة الدراسية المعتمدة.',
                        ],
                        [
                            'q' => 'هل الدراسة أونلاين أو حضوري؟',
                            'a' => 'متوفر أونلاين وحضوري حسب النظام المناسب لك.',
                        ],
                        [
                            'q' => 'هل يوجد مشروع تخرج؟',
                            'a' => 'نعم، البرنامج يتضمن مشروع تخرج تطبيقي متكامل.',
                        ],
                        [
                            'q' => 'هل يوجد تقسيط؟',
                            'a' => 'نعم، متوفر التقسيط عبر تابي وتمارا.',
                        ],
                        [
                            'q' => 'كيف أسجل؟',
                            'a' => 'تقدر تسجل عبر نموذج التسجيل أو التواصل مباشرة مع فريق القبول.',
                        ],
                    ];
                @endphp
                @foreach($faqs as $index => $faq)
                    <div class="card">
                        <div class="card-header" id="cslFaqHead{{ $index }}">
                            <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#cslFaq{{ $index }}" aria-expanded="false" aria-controls="cslFaq{{ $index }}">
                                {{ $faq['q'] }}
                                <span aria-hidden="true">+</span>
                            </button>
                        </div>
                        <div id="cslFaq{{ $index }}" class="collapse" aria-labelledby="cslFaqHead{{ $index }}" data-parent="#cslFaqAccordion">
                            <div class="card-body">
                                @if(!empty($faq['html']))
                                    {!! $faq['a'] !!}
                                @else
                                    {{ $faq['a'] }}
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="csl-section-cta">
                <a href="#register" class="csl-btn csl-btn-primary">سجل الآن</a>
            </div>
        </div>
    </section>

    <footer class="csl-footer-note">
        &copy; {{ date('Y') }} {{ $generalSettings['site_name'] ?? '' }} — دبلومة إدارة الأعمال
    </footer>

@endsection

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
    <style>
        .csl-hero-with-form .csl-hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr);
            gap: 36px;
            align-items: start;
        }
        .csl-hero-with-form .csl-hero-content h1 {
            margin-bottom: 20px;
        }
        .csl-hero-form-col .csl-form-wrap {
            max-width: none;
            margin: 0;
            padding: 28px;
        }
        .csl-hero-form-title {
            font-size: clamp(1.05rem, 2.2vw, 1.2rem);
            font-weight: 800;
            color: var(--csl-blue);
            line-height: 1.5;
        }
        .csl-section-cta {
            margin-top: 36px;
            text-align: center;
        }
        @media (max-width: 991px) {
            .csl-hero-with-form .csl-hero-grid {
                grid-template-columns: 1fr;
            }
            .csl-hero-form-col {
                order: 2;
            }
        }
    </style>
@endpush

@push('scripts_bottom')
    <script src="/assets/default/js/admin/form_submissions_details.min.js"></script>
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>
    <script src="/assets/default/js/parts/forms.min.js"></script>
@endpush
