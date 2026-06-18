@extends('web.default.layouts.cyber_security_landing')

@php
    $cslConfig = 'business_admin_landing';
    $cslWhatsapp = config("{$cslConfig}.whatsapp_number");
    $cslCall = config("{$cslConfig}.call_number");
    $cslWhatsappDigits = !empty($cslWhatsapp) ? preg_replace('/\D/', '', $cslWhatsapp) : '';
@endphp

@section('content')

    {{-- Hero --}}
    <section class="csl-hero" id="top">
        <div class="csl-container">
            <p class="csl-hero-lead mb-3 font-weight-bold" style="max-width:100%;">راتب أعلى؟ ترقية أقرب؟ مستقبل مهني أقوى؟</p>
            <h1>ابدأ من هنا.</h1>
            <p class="csl-hero-subtitle">مع دبلومة إدارة الأعمال المعتمدة من Qualifi البريطانية</p>
            <div class="csl-highlights">
                <div class="csl-highlight-item">مدة الدراسة سنتين</div>
                <div class="csl-highlight-item">إمكانية الدراسة المكثفة خلال 6 شهور</div>
                <div class="csl-highlight-item">شهادة معتمدة محلياً وعالمياً</div>
                <div class="csl-highlight-item">24 مادة احترافية تغطي أهم مجالات الإدارة الحديثة</div>
                <div class="csl-highlight-item">مناسبة للموظفين ورواد الأعمال والطامحين للتطوير المهني</div>
                <div class="csl-highlight-item">متوفر التقسيط عبر تابي وتمارا</div>
                <div class="csl-highlight-item">دراسة وفق معايير أكاديمية بريطانية معتمدة</div>
            </div>
            <p class="csl-hero-lead">اليوم المؤسسات تبحث عن الكفاءات القادرة على التخطيط وإدارة فرق العمل وتحليل المخاطر وتحسين الأداء المؤسسي.</p>
            <p class="csl-hero-lead mb-2">ومن خلال هالدبلومة بتكتسب مهارات عملية تساعدك على تطوير أدائك المهني وتعزيز فرصك الوظيفية.</p>
            <p class="font-14 mb-2" style="color: var(--csl-white);">شو بتتعلم خلال هالدبلومة؟</p>
            <div class="csl-tech-tags">
                <span class="csl-tech-tag">إدارة الأعمال</span>
                <span class="csl-tech-tag">التسويق</span>
                <span class="csl-tech-tag">إدارة المخاطر</span>
                <span class="csl-tech-tag">KPI</span>
                <span class="csl-tech-tag">إدارة الوقت</span>
                <span class="csl-tech-tag">الابتكار</span>
                <span class="csl-tech-tag">المسؤولية المجتمعية</span>
                <span class="csl-tech-tag">صياغة العقود</span>
                <span class="csl-tech-tag">خدمة المتعاملين</span>
            </div>
            <div class="mt-4 d-flex flex-wrap gap-2">
                <a href="#register" class="csl-btn csl-btn-primary">سجل الحين</a>
                @if(!empty($cslWhatsappDigits))
                    <a href="https://wa.me/{{ $cslWhatsappDigits }}" target="_blank" rel="noopener" class="csl-btn csl-btn-whatsapp">
                        تواصل مباشرة عبر الواتساب
                    </a>
                @endif
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
        </div>
    </section>

    {{-- Why choose --}}
    <section class="csl-section csl-section-light" id="why-choose">
        <div class="csl-container">
            <h2 class="csl-section-title">ليش تختار هالدبلومة؟</h2>
            <p class="mb-3">من خلال البرنامج ستتمكن من تطوير فهمك للمفاهيم الإدارية الحديثة، وتعزيز مهاراتك في التخطيط والتنظيم وإدارة فرق العمل وتحليل المخاطر وقياس الأداء وتحسين الإنتاجية داخل المؤسسات.</p>
            <p class="mb-0">كما يساعدك البرنامج على تطوير مهاراتك المهنية بما يتناسب مع متطلبات بيئات العمل الحديثة وفرص الترقية بالمؤسسات.</p>
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
                            <div class="card-body">{{ $faq['a'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Registration form --}}
    <section class="csl-section csl-form-section" id="register">
        <div class="csl-container">
            <h2 class="csl-section-title text-center text-white mb-2">احجز الآن وأضمن مستقبلك المهني</h2>
            <p class="text-center mb-4" style="color: var(--csl-muted);">املأ النموذج وسيتواصل معك فريق القبول في أقرب وقت</p>

            <div class="csl-form-wrap">
                @if(!empty($form->heading_title))
                    <h3 class="font-24 mb-2">{{ $form->heading_title }}</h3>
                @endif
                @if(!empty($form->description))
                    <div class="font-14 text-gray mb-3">{!! $form->description !!}</div>
                @endif

                @if(!empty($form->end_date))
                    <div class="alert alert-warning font-12 mb-3">
                        {{ trans('update.this_form_will_be_expired_on_date',['date' => dateTimeFormat($form->end_date, 'j M Y')]) }}
                    </div>
                @endif

                <form action="{{ url('/landing/business-admin/store') }}" method="post">
                    {{ csrf_field() }}
                    @include('web.default.forms.handle_field', ['fields' => $form->fields])
                    @include('web.default.includes.turnstile_widget')
                    <div class="d-flex flex-column flex-sm-row align-items-stretch gap-2 mt-4">
                        <button type="button" class="js-clear-form btn btn-outline-secondary flex-fill">{{ trans('update.clear_form') }}</button>
                        <button type="submit" class="btn btn-primary flex-fill font-weight-bold">سجل الحين</button>
                    </div>
                </form>

                <div class="mt-4 d-flex flex-column gap-2">
                    @if(!empty($cslWhatsappDigits))
                        <a href="https://wa.me/{{ $cslWhatsappDigits }}" target="_blank" rel="noopener" class="csl-btn csl-btn-whatsapp w-100">
                            تواصل مباشرة عبر الواتساب
                        </a>
                    @endif
                    @if(!empty($cslCall))
                        <a href="tel:{{ $cslCall }}" class="csl-btn csl-btn-outline w-100" style="color:#01477d !important;border-color:#01477d;">
                            {{ trans('update.call_us') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <footer class="csl-footer-note">
        &copy; {{ date('Y') }} {{ $generalSettings['site_name'] ?? '' }} — دبلومة إدارة الأعمال
    </footer>

@endsection

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
@endpush

@push('scripts_bottom')
    <script src="/assets/default/js/admin/form_submissions_details.min.js"></script>
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>
    <script src="/assets/default/js/parts/forms.min.js"></script>
@endpush
