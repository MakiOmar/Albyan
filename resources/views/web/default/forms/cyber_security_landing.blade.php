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
            <span class="csl-badge">Qualifi البريطانية · معتمدة محليًا وعالميًا</span>
            <h1>سواء كنت متخرج أو لا… اليوم تقدر تبدأ مجال من أقوى المجالات المطلوبة بالإمارات والخليج.</h1>
            <p class="csl-hero-subtitle">دبلومة الأمن السيبراني المعتمدة من Qualifi البريطانية</p>
            <div class="csl-highlights">
                <div class="csl-highlight-item">دراسة لمدة سنتين</div>
                <div class="csl-highlight-item">شهادة معتمدة محليًا وعالميًا</div>
                <div class="csl-highlight-item">مؤهل احترافي بمعايير بريطانية</div>
                <div class="csl-highlight-item">رواتب المجال قد تصل إلى 35,000 درهم</div>
                <div class="csl-highlight-item">متاح التقسيط عبر تابي وتمارا</div>
            </div>
            <p class="csl-hero-lead">ابدأ رحلتك في واحد من أسرع المجالات نموًا بالعالم وتعلم مهارات مطلوبة فعليًا بسوق العمل.</p>
            <p class="font-14 text-white mb-2">تشمل الدراسة:</p>
            <div class="csl-tech-tags">
                <span class="csl-tech-tag">Python</span>
                <span class="csl-tech-tag">Linux</span>
                <span class="csl-tech-tag">Ethical Hacking</span>
                <span class="csl-tech-tag">Network Security</span>
                <span class="csl-tech-tag">NMap &amp; Cyber Security Tools</span>
            </div>
            <div class="mt-4 d-flex flex-wrap gap-2">
                <a href="#register" class="csl-btn csl-btn-primary">سجل الآن</a>
                @if(!empty($cslWhatsappDigits))
                    <a href="https://wa.me/{{ $cslWhatsappDigits }}" target="_blank" rel="noopener" class="csl-btn csl-btn-whatsapp">
                        تواصل عبر الواتساب
                    </a>
                @endif
            </div>
        </div>
    </section>

    {{-- Curriculum --}}
    <section class="csl-section csl-section-alt" id="curriculum">
        <div class="csl-container">
            <h2 class="csl-section-title">محتوى الدبلومة</h2>
            <p class="csl-section-desc">منهج متكامل يجمع بين الأساسيات النظرية والتطبيق العملي على أدوات الأمن السيبراني الحديثة.</p>
            <div class="csl-cards-grid">
                <div class="csl-card">
                    <h3>أساسيات الأمن السيبراني والشبكات</h3>
                    <ul>
                        <li>حماية الشبكات والمعلومات</li>
                        <li>تحليل الهجمات الإلكترونية</li>
                        <li>إدارة الجدران النارية</li>
                        <li>Packet Analysis</li>
                        <li>Network Security</li>
                    </ul>
                </div>
                <div class="csl-card">
                    <h3>البرمجة والتطوير الأمني</h3>
                    <ul>
                        <li>أساسيات البرمجة</li>
                        <li>Python للأمن السيبراني</li>
                        <li>إنشاء سكربتات أمنية</li>
                        <li>HTML &amp; CSS Security</li>
                        <li>حماية المواقع من الثغرات</li>
                    </ul>
                </div>
                <div class="csl-card">
                    <h3>أنظمة التشغيل والحماية</h3>
                    <ul>
                        <li>Linux Security</li>
                        <li>إدارة الصلاحيات والمستخدمين</li>
                        <li>حماية الأنظمة واكتشاف التهديدات</li>
                        <li>مراقبة وتحليل العمليات الشبكية</li>
                    </ul>
                </div>
                <div class="csl-card">
                    <h3>Ethical Hacking &amp; Security Tools</h3>
                    <ul>
                        <li>NMap</li>
                        <li>أدوات فحص الشبكات</li>
                        <li>اختبار الاختراق الأخلاقي</li>
                        <li>تحليل الثغرات الأمنية</li>
                        <li>إعداد التقارير الأمنية</li>
                    </ul>
                </div>
            </div>
            <div class="csl-card mt-4 text-center">
                <h3 class="mb-2">مشروع تخرج عملي</h3>
                <p class="mb-0 text-white-50">تطبيق عملي متكامل لمحاكاة بيئة أمن سيبراني حقيقية لإعداد الطالب لسوق العمل.</p>
            </div>
        </div>
    </section>

    {{-- Careers --}}
    <section class="csl-section csl-section-light" id="careers">
        <div class="csl-container">
            <h2 class="csl-section-title">فرص العمل والرواتب</h2>
            <p class="csl-section-desc">بعد التخرج يمكن للطالب التقديم على وظائف مثل:</p>
            <div class="csl-jobs-grid">
                <div class="csl-job-pill">Cyber Security Specialist</div>
                <div class="csl-job-pill">SOC Analyst</div>
                <div class="csl-job-pill">Penetration Tester</div>
                <div class="csl-job-pill">Network Security Analyst</div>
                <div class="csl-job-pill">IT Security Support</div>
                <div class="csl-job-pill">Information Security Assistant</div>
            </div>
            <p class="mb-4">يُعد الأمن السيبراني من أكثر المجالات طلبًا بالإمارات والخليج والعالم.</p>
            <div class="csl-salary-box">
                <span>متوسط الرواتب بالمجال قد يصل إلى</span>
                <strong>35,000 درهم</strong>
                <span class="d-block mt-2 font-14">حسب الخبرة والمهارات والتخصص</span>
            </div>
        </div>
    </section>

    {{-- Who is it for --}}
    <section class="csl-section csl-section-alt" id="audience">
        <div class="csl-container">
            <h2 class="csl-section-title">الدبلومة مناسبة لمن؟</h2>
            <div class="row">
                <div class="col-lg-7">
                    <ul class="list-unstyled mb-0">
                        <li class="csl-highlight-item mb-2">خريجي الجامعات الراغبين في تطوير مسار مهني قوي</li>
                        <li class="csl-highlight-item mb-2">الموظفين الراغبين في تحسين الدخل والتخصص المهني</li>
                        <li class="csl-highlight-item mb-2">المبتدئين الراغبين في دخول المجال من الصفر</li>
                        <li class="csl-highlight-item mb-2">المهتمين بالتقنية والأمن السيبراني</li>
                        <li class="csl-highlight-item mb-2">الباحثين عن شهادة احترافية معتمدة عالميًا</li>
                        <li class="csl-highlight-item mb-2">الراغبين في دخول مجال مطلوب ومستقبله واعد</li>
                    </ul>
                </div>
                <div class="col-lg-5 mt-4 mt-lg-0">
                    <div class="csl-card h-100 d-flex align-items-center">
                        <p class="mb-0 font-weight-bold">لا يشترط وجود خبرة مسبقة، حيث تبدأ الدراسة من الأساسيات وحتى المستوى المتقدم.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Fees --}}
    <section class="csl-section csl-section-light" id="fees">
        <div class="csl-container">
            <h2 class="csl-section-title text-center">الرسوم والأقساط</h2>
            <div class="csl-pricing-card">
                <p class="mb-1">رسوم الدبلومة كاملة</p>
                <div class="csl-price">35,000 <span class="font-20">درهم</span></div>
                <p class="mt-4 mb-3 font-weight-bold">تشمل الرسوم:</p>
                <ul class="list-unstyled text-right mb-4">
                    <li class="mb-2">✓ الدراسة الأكاديمية</li>
                    <li class="mb-2">✓ التدريب العملي</li>
                    <li class="mb-2">✓ مشروع التخرج</li>
                    <li class="mb-2">✓ الشهادة المعتمدة</li>
                </ul>
                <p class="font-weight-bold mb-2">خيارات الدفع:</p>
                <p class="mb-0">متاح التقسيط عبر Tabby · متاح التقسيط عبر Tamara</p>
                <p class="mt-3 mb-0 font-14">للتسجيل ومعرفة تفاصيل الأقساط يرجى التواصل مع فريق القبول.</p>
            </div>
        </div>
    </section>

    {{-- Testimonials --}}
    <section class="csl-section csl-section-alt" id="testimonials">
        <div class="csl-container">
            <h2 class="csl-section-title">آراء الطلاب</h2>
            <div class="csl-testimonials">
                <blockquote class="csl-testimonial">المحتوى العملي ساعدني أفهم المجال بشكل احترافي وأطبق فعليًا على أدوات الأمن السيبراني.</blockquote>
                <blockquote class="csl-testimonial">الدبلومة كانت نقطة قوية في تطوير مهاراتي التقنية وفهم الشبكات والحماية.</blockquote>
                <blockquote class="csl-testimonial">أكثر شيء مميز هو الدمج بين الدراسة النظرية والتطبيق العملي.</blockquote>
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
                        ['q' => 'هل أحتاج خبرة مسبقة؟', 'a' => 'لا، الدبلومة مناسبة للمبتدئين وتبدأ من الأساسيات.'],
                        ['q' => 'هل الشهادة معتمدة؟', 'a' => 'نعم، الدبلومة معتمدة من Qualifi البريطانية ومعترف بها محليًا وعالميًا.'],
                        ['q' => 'كم مدة الدراسة؟', 'a' => 'مدة الدراسة سنتان.'],
                        ['q' => 'هل الدراسة أونلاين أم أوفلاين؟', 'a' => 'متاح الدراسة أونلاين وأوفلاين حسب النظام المناسب للطالب.'],
                        ['q' => 'هل يوجد تدريب عملي؟', 'a' => 'نعم، تتضمن الدبلومة تطبيقات عملية ومشروع تخرج متكامل.'],
                        ['q' => 'هل يوجد تقسيط؟', 'a' => 'نعم، متاح التقسيط عبر تابي وتمارا.'],
                        ['q' => 'هل المجال مطلوب فعلًا؟', 'a' => 'نعم، الأمن السيبراني من أكثر المجالات نموًا وطلبًا في الإمارات والخليج والعالم.'],
                        ['q' => 'كيف يمكنني التسجيل؟', 'a' => 'يمكن التسجيل عبر نموذج التسجيل أدناه أو التواصل المباشر مع فريق القبول.'],
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
            <h2 class="csl-section-title text-center text-white mb-2">سجل اهتمامك الآن</h2>
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
                        <button type="submit" class="btn btn-primary flex-fill font-weight-bold">{{ trans('update.submit_form') }}</button>
                    </div>
                </form>

                <div class="mt-4 d-flex flex-column gap-2">
                    @if(!empty($cslWhatsappDigits))
                        <a href="https://wa.me/{{ $cslWhatsappDigits }}" target="_blank" rel="noopener" class="csl-btn csl-btn-whatsapp w-100">
                            تواصل عبر الواتساب
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
