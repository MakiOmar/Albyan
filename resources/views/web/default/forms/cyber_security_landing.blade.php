@extends('web.default.layouts.cyber_security_landing')

@php
    $cslConfig = 'cyber_security_landing';
    $cslWhatsapp = config("{$cslConfig}.whatsapp_number");
    $cslCall = config("{$cslConfig}.call_number");
    $cslWhatsappDigits = !empty($cslWhatsapp) ? preg_replace('/\D/', '', $cslWhatsapp) : '';
@endphp

@section('content')

    {{-- Hero --}}
    <section class="csl-hero" id="top">
        <div class="csl-container">
            <p class="csl-hero-lead mb-3 font-weight-bold" style="max-width:100%;">ابدأ دبلوم احترافي لمدة سنتين يمنحك مؤهل أعلى من الثانوية من الصفر للاحتراف.</p>
            <h1>الحين عندك فرصة تدخل واحد من أقوى وأكثر المجالات المطلوبة بالإمارات والخليج.</h1>
            <p class="csl-hero-subtitle">دبلومة الأمن السيبراني المعتمدة من Qualifi البريطانية</p>
            <div class="csl-highlights">
                <div class="csl-highlight-item">مدة الدراسة سنتين</div>
                <div class="csl-highlight-item">شهادة معتمدة محليًا وعالميًا</div>
                <div class="csl-highlight-item">تأهيل احترافي بمعايير بريطانية</div>
                <div class="csl-highlight-item">رواتب المجال ممكن توصل إلى 35,000 درهم</div>
                <div class="csl-highlight-item">متوفر التقسيط عبر تابي وتمارا</div>
                <div class="csl-highlight-item">24 مادة احترافية تغطي أهم مهارات الأمن السيبراني</div>
                <div class="csl-highlight-item">دراسة مكثفة خلال 6 شهور بدل سنتين</div>
            </div>
            <p class="csl-hero-lead">ابدأ رحلتك بمجال يعتبر من أسرع المجالات نموًا بالعالم، واكتسب مهارات مطلوبة فعليًا بسوق العمل الخليجي والعالمي.</p>
            <p class="font-14 mb-2" style="color: var(--csl-white);">خلال الدبلومة بتتعلم:</p>
            <div class="csl-tech-tags">
                <span class="csl-tech-tag">Python</span>
                <span class="csl-tech-tag">Linux</span>
                <span class="csl-tech-tag">Ethical Hacking</span>
                <span class="csl-tech-tag">Network Security</span>
                <span class="csl-tech-tag">NMap &amp; Cyber Security Tools</span>
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
                    <h3>أساسيات الأمن السيبراني والشبكات</h3>
                    <ul>
                        <li>حماية الشبكات والأنظمة</li>
                        <li>تأمين المعلومات والبيانات</li>
                        <li>تحليل الهجمات الإلكترونية</li>
                        <li>إدارة الجدران النارية</li>
                        <li>Packet Analysis</li>
                        <li>أساسيات Network Security</li>
                    </ul>
                </div>
                <div class="csl-card">
                    <h3>البرمجة والتطوير الأمني</h3>
                    <ul>
                        <li>أساسيات البرمجة</li>
                        <li>Python للأمن السيبراني</li>
                        <li>إنشاء سكربتات أمنية</li>
                        <li>HTML &amp; CSS Security</li>
                        <li>حماية المواقع واكتشاف الثغرات</li>
                    </ul>
                </div>
                <div class="csl-card">
                    <h3>أنظمة التشغيل والحماية</h3>
                    <ul>
                        <li>Linux Security</li>
                        <li>إدارة الصلاحيات والمستخدمين</li>
                        <li>حماية الأنظمة من التهديدات</li>
                        <li>مراقبة وتحليل العمليات الشبكية</li>
                    </ul>
                </div>
                <div class="csl-card">
                    <h3>Ethical Hacking &amp; Security Tools</h3>
                    <ul>
                        <li>استخدام NMap</li>
                        <li>أدوات فحص وتحليل الشبكات</li>
                        <li>اختبار الاختراق الأخلاقي</li>
                        <li>تحليل الثغرات الأمنية</li>
                        <li>كتابة وإعداد التقارير الأمنية</li>
                    </ul>
                </div>
            </div>
            <div class="csl-card mt-4 text-center">
                <h3 class="mb-2">مشروع التخرج العملي</h3>
                <p class="mb-0 text-white-50">تطبيق عملي متكامل يحاكي بيئة أمن سيبراني حقيقية، حتى يكون الطالب جاهز فعليًا لدخول سوق العمل بثقة وخبرة عملية.</p>
            </div>
        </div>
    </section>

    {{-- Careers --}}
    <section class="csl-section csl-section-light" id="careers">
        <div class="csl-container">
            <h2 class="csl-section-title">فرص العمل والرواتب</h2>
            <p class="csl-section-desc">بعد التخرج تقدر تقدم على وظائف مثل:</p>
            <div class="csl-jobs-grid">
                <div class="csl-job-pill">Cyber Security Specialist</div>
                <div class="csl-job-pill">SOC Analyst</div>
                <div class="csl-job-pill">Penetration Tester</div>
                <div class="csl-job-pill">Network Security Analyst</div>
                <div class="csl-job-pill">IT Security Support</div>
                <div class="csl-job-pill">Information Security Assistant</div>
            </div>
            <p class="mb-0">الأمن السيبراني اليوم يعتبر من أكثر المجالات المطلوبة بالإمارات والخليج والعالم، ومع تطور خبرتك ومهاراتك ممكن توصل الرواتب إلى <strong style="color:#01477d;">35,000 درهم</strong> وأكثر حسب التخصص والخبرة.</p>
        </div>
    </section>

    {{-- Who is it for --}}
    <section class="csl-section csl-section-alt" id="audience">
        <div class="csl-container">
            <h2 class="csl-section-title">الدبلومة مناسبة لـ مين؟</h2>
            <div class="row">
                <div class="col-lg-7">
                    <ul class="list-unstyled mb-0">
                        <li class="csl-highlight-item mb-2">خريجي الجامعات اللي يبغون يبنون مسار مهني قوي</li>
                        <li class="csl-highlight-item mb-2">الموظفين اللي يبغون يطورون دخلهم ومهاراتهم</li>
                        <li class="csl-highlight-item mb-2">المبتدئين اللي حابين يدخلون المجال من الصفر</li>
                        <li class="csl-highlight-item mb-2">المهتمين بالتقنية والأمن السيبراني</li>
                        <li class="csl-highlight-item mb-2">الباحثين عن شهادة احترافية معتمدة عالميًا</li>
                        <li class="csl-highlight-item mb-2">أي شخص يبغي يدخل مجال مستقبله قوي ومطلوب جدًا</li>
                    </ul>
                </div>
                <div class="col-lg-5 mt-4 mt-lg-0">
                    <div class="csl-card h-100 d-flex align-items-center">
                        <p class="mb-0 font-weight-bold">ولا يشترط يكون عندك خبرة مسبقة، لأن الدراسة تبدأ معك من الأساسيات إلى الاحتراف.</p>
                    </div>
                </div>
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
                    <li class="mb-2">✔️ التدريب العملي</li>
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
                <blockquote class="csl-testimonial">المحتوى العملي خلاني أفهم المجال بشكل احترافي وأطبق فعليًا على أدوات الأمن السيبراني.</blockquote>
                <blockquote class="csl-testimonial">الدبلومة كانت خطوة قوية بتطوير مهاراتي التقنية وفهم الشبكات والحماية.</blockquote>
                <blockquote class="csl-testimonial">أكثر شيء عجبني الدمج بين الدراسة النظرية والتطبيق العملي.</blockquote>
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
                            'a' => 'لا، الدبلومة مناسبة للمبتدئين وتبدأ من الأساسيات إلى الاحتراف.',
                        ],
                        [
                            'q' => 'هل الشهادة معتمدة؟',
                            'a' => 'نعم، الدبلومة معتمدة من Qualifi البريطانية ومعترف فيها محليًا وعالميًا، مع إمكانية الحصول على شهادة معتمدة من هيئة المعرفة والتنمية البشرية KHDA، ومتاح أيضًا التصديق من وزارة الخارجية الإماراتية.',
                        ],
                        [
                            'q' => 'كم مدة الدراسة؟',
                            'a' => 'مدة الدبلومة الأساسية سنتين، لكن لأن النظام البريطاني يعتمد على الساعات الدراسية، تقدر تدرس بنظام مكثف وتنجز محتوى الدبلومة خلال 6 شهور فقط.',
                        ],
                        [
                            'q' => 'هل الدراسة أونلاين أو حضوري؟',
                            'a' => 'متوفر أونلاين وحضوري حسب النظام المناسب لك.',
                        ],
                        [
                            'q' => 'هل يوجد تدريب عملي؟',
                            'a' => 'نعم، الدبلومة تشمل تطبيقات عملية ومشروع تخرج متكامل.',
                        ],
                        [
                            'q' => 'هل يوجد تقسيط؟',
                            'a' => 'نعم، متوفر التقسيط عبر تابي وتمارا.',
                        ],
                        [
                            'q' => 'هل المجال مطلوب فعلًا؟',
                            'a' => 'أكيد، الأمن السيبراني من أكثر المجالات نموًا وطلبًا بالإمارات والخليج والعالم.',
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

                <form action="{{ url('/landing/cyber-security/store') }}" method="post">
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
        &copy; {{ date('Y') }} {{ $generalSettings['site_name'] ?? '' }} — دبلومة الأمن السيبراني
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
