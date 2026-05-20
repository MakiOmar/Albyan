{{-- About page main copy (Arabic) — semantic sections for SEO --}}
@php
    $classesUrl = url('/classes');
    $contactUrl = url('/contact');
@endphp

<article class="about-page-content contact-us-about col-12 py-4">
    <header>
        <h1 class="section-title-bg p-2 text-center mb-4">عن أكاديمية البيان</h1>
        <p class="text-center mb-4">مرحباً بكم في أكاديمية البيان، وجهتكم الأولى للتعليم المهني واكتساب المهارات العملية في دبي ودولة الإمارات العربية المتحدة.</p>
    </header>

    <p>في أكاديمية البيان لا نؤمن بفكرة "احضر الدورة و احصل على الشهادة"، لأن هذا وحده لا يكفي، ما يهمنا هو أن يخرج المتدرب بمهارات يستخدمها في عمله في دراسته، أو في مشروعه، لـ هذا نبني كل برنامج على أساس تطبيقي بالمقام الأول، و نحرص أن يعرف المتدرب من اليوم الأول ما الذي سيتعلمه وإلى أين سيوصله.</p>

    <section class="about-page-section" aria-labelledby="about-vision-heading">
        <h2 id="about-vision-heading" class="section-title-bg p-2 mt-4 mb-3">رؤيتنا</h2>
        <p>أن تكون أكاديمية البيان المرجع الأول لأي شخص في الإمارات يريد تطوير نفسه مهنياً، لأننا نربط التدريب بما يطلبه سوق العمل فعلاً، لا بما كان مطلوباً قبل خمس سنوات.</p>
    </section>

    <section class="about-page-section" aria-labelledby="about-mission-heading">
        <h2 id="about-mission-heading" class="section-title-bg p-2 mt-4 mb-3">رسالتنا</h2>
        <p>نسد الفجوة بين التعليم والعمل، نقدم برامج يقودها متخصصون من الميدان، وليس فقط في الأكاديمية، حتى يخرج المتدرب بثقة حقيقية في مهارته سواء كان هدفه الترقية او التوظيف، او فتح مشروع خاص.</p>
    </section>

    <section class="about-page-section" aria-labelledby="about-goals-heading">
        <h2 id="about-goals-heading" class="section-title-bg p-2 mt-4 mb-3">مهمتنا</h2>
        <p>نجعل التعليم أبسط و أوضح لكل شخص، قبل التسجيل يتحدث المتدرب مع فريقنا ليفهم بالضبط ما يناسبه، أثناء الدراسة يجد دعماً مباشراً دون تعقيد، بعد الانتهاء يحمل شهادة معتمدة تضيف لسيرته الذاتية وزناً حقيقياً.</p>
    </section>

    <section class="about-page-section" aria-labelledby="about-programs-heading">
        <h2 id="about-programs-heading" class="section-title-bg p-2 mt-4 mb-3">ماذا نقدم في أكاديمية البيان؟</h2>
        <p>نقدم مجموعة من البرامج التدريبية المصممة بعناية لتلبية احتياجات الأفراد والمهنيين، وتشمل:</p>
        <ul class="about-page-list list-unstyled">
            <li class="mb-3">
                <h3 class="h5 font-weight-bold">دورات اللغات</h3>
                <p class="mb-0">برامج تطوير اللغة الإنجليزية وغيرها من اللغات، مع التركيز على المحادثة، الاستخدام العملي، الدراسة، والعمل.</p>
            </li>
            <li class="mb-3">
                <h3 class="h5 font-weight-bold">الدورات الإدارية والمهنية</h3>
                <p class="mb-0">برامج تساعد الموظفين والباحثين عن عمل على تطوير مهارات الإدارة، القيادة، خدمة العملاء، التواصل، وإدارة الوقت.</p>
            </li>
            <li class="mb-3">
                <h3 class="h5 font-weight-bold">دورات المحاسبة والمالية</h3>
                <p class="mb-0">تدريب عملي يساعد المتدربين على فهم الأساسيات المالية والمحاسبية واستخدامها في بيئة العمل.</p>
            </li>
            <li class="mb-3">
                <h3 class="h5 font-weight-bold">دورات التسويق والتجارة الإلكترونية</h3>
                <p class="mb-0">برامج مصممة لمواكبة احتياجات السوق الرقمي، وتساعد المتدربين على فهم التسويق، البيع، وإدارة الأنشطة الرقمية.</p>
            </li>
            <li class="mb-3">
                <h3 class="h5 font-weight-bold">برامج التدريب المؤسسي</h3>
                <p class="mb-0">حلول تدريب للشركات والفرق التي ترغب في تطوير مهارات موظفيها ورفع كفاءة الأداء.</p>
            </li>
        </ul>
        <p class="mb-0">
            <a href="{{ $classesUrl }}" class="font-weight-bold">{{ trans('site.about_browse_programs') }}</a>
        </p>
    </section>

    <section class="about-page-section" aria-labelledby="about-stats-heading">
        <h2 id="about-stats-heading" class="section-title-bg p-2 mt-4 mb-3">أرقام تعكس تطور أكاديمية البيان</h2>
        <dl class="about-page-stats row text-center my-4">
            <div class="col-6 col-md-3 mb-3">
                <dt class="font-weight-bold d-block" style="font-size: 1.25rem; color: var(--primary, #01477d);">+300</dt>
                <dd class="mb-0">برنامج تدريبي احترافي</dd>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <dt class="font-weight-bold d-block" style="font-size: 1.25rem; color: var(--primary, #01477d);">اعتمادات</dt>
                <dd class="mb-0">محلية ودولية</dd>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <dt class="font-weight-bold d-block" style="font-size: 1.25rem; color: var(--primary, #01477d);">حفل تخرج</dt>
                <dd class="mb-0">سنوي مميز</dd>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <dt class="font-weight-bold d-block" style="font-size: 1.25rem; color: var(--primary, #01477d);">برامج</dt>
                <dd class="mb-0">تعليمية مرنة</dd>
            </div>
        </dl>
    </section>

    <section class="about-page-section" aria-labelledby="about-why-heading">
        <h2 id="about-why-heading" class="section-title-bg p-2 mt-4 mb-3">لماذا تختار أكاديمية البيان؟</h2>
        <p class="font-weight-bold">لأننا لا نقدم تدريبًا تقليديًا…</p>
        <ul class="about-page-features list-unstyled">
            <li class="mb-3">
                <h3 class="h5 font-weight-bold">تعلم عملي وليس نظريًا فقط</h3>
                <p class="mb-0">نركز على المهارات التي يمكن استخدامها في العمل، الدراسة، والحياة اليومية.</p>
            </li>
            <li class="mb-3">
                <h3 class="h5 font-weight-bold">مرونة في التعلم</h3>
                <p class="mb-0">يمكنك الالتحاق ببرامج حضورية أو أونلاين حسب ما يناسب وقتك وهدفك.</p>
            </li>
            <li class="mb-3">
                <h3 class="h5 font-weight-bold">شهادة تعزز مسارك</h3>
                <p class="mb-0">نساعدك على الحصول على شهادة تدريبية تدعم سيرتك الذاتية وتوثق رحلتك التعليمية.</p>
            </li>
            <li class="mb-3">
                <h3 class="h5 font-weight-bold">دعم مباشر وسهل</h3>
                <p class="mb-0">فريقنا يساعدك في اختيار البرنامج المناسب والتسجيل والمتابعة، مع إمكانية التواصل المباشر عبر واتساب.</p>
            </li>
            <li class="mb-3">
                <h3 class="h5 font-weight-bold">تجربة تعليمية منظمة وواضحة</h3>
                <p class="mb-0">نعمل على تقديم تجربة تدريبية منظمة، واضحة، ومبنية على الثقة والجودة.</p>
            </li>
            <li class="mb-3">
                <h3 class="h5 font-weight-bold">قيمة مناسبة مقابل ما تتعلمه</h3>
                <p class="mb-0">نقدم برامج تساعدك على تحقيق استفادة حقيقية دون تعقيد أو تكلفة مبالغ فيها.</p>
            </li>
        </ul>
    </section>

    <section class="about-page-section" aria-labelledby="about-values-heading">
        <h2 id="about-values-heading" class="section-title-bg p-2 mt-4 mb-3">قيم أكاديمية البيان</h2>
        <ul class="about-page-values list-unstyled">
            <li class="mb-3">
                <h3 class="h5 font-weight-bold">الوضوح</h3>
                <p class="mb-0">نشرح للمتدرب البرنامج، الهدف، والمخرجات قبل التسجيل.</p>
            </li>
            <li class="mb-3">
                <h3 class="h5 font-weight-bold">الثقة والالتزام</h3>
                <p class="mb-0">نلتزم بتقديم تجربة تدريبية قائمة على الثقة والالتزام.</p>
            </li>
            <li class="mb-3">
                <h3 class="h5 font-weight-bold">المرونة</h3>
                <p class="mb-0">نوفر حلولًا تناسب الطالب، الموظف، وصاحب العمل.</p>
            </li>
            <li class="mb-3">
                <h3 class="h5 font-weight-bold">الجودة العملية</h3>
                <p class="mb-0">نركز على التدريب الذي يساعد المتدرب في الواقع، وليس فقط في القاعة.</p>
            </li>
            <li class="mb-3">
                <h3 class="h5 font-weight-bold">الاهتمام بالمتدرب</h3>
                <p class="mb-0">نساعد كل متدرب على اختيار المسار الأقرب لهدفه.</p>
            </li>
        </ul>
    </section>

    <section class="about-page-section" aria-labelledby="about-audience-heading">
        <h2 id="about-audience-heading" class="section-title-bg p-2 mt-4 mb-3">لمن تناسب أكاديمية البيان؟</h2>
        <p>تناسب أكاديمية البيان للطلاب الذين يريدون مهارات إضافية، للموظفين الذين يفكرون في الترقية، لمن يبحث عن عمل ويريد سيرة ذاتية أقوى، لرواد الأعمال الذين يحتاجون أدوات عملية، وللشركات التي تستثمر في فريقها.</p>
    </section>

    <section class="about-page-cta text-center my-5 p-4 rounded-lg" style="background: var(--secondary, #01477d); color: #fff;" aria-labelledby="about-cta-heading">
        <h2 id="about-cta-heading" class="h4 font-weight-bold mb-3">{{ trans('site.about_cta_heading') }}</h2>
        <p class="mb-3 font-weight-bold">ابدأ اليوم رحلتك نحو فرصة مهنية أفضل.</p>
        <p class="mb-0">تواصل مع فريق أكاديمية البيان عبر واتساب وسنساعدك في تحديد الدورة الأقرب لهدفك.</p>
        @if(!empty($whatsappLink))
            <a href="{{ $whatsappLink }}" target="_blank" rel="noopener" class="btn btn-light mt-3">
                <i class="fab fa-whatsapp" aria-hidden="true"></i> {{ trans('site.about_whatsapp_cta_link') }}
            </a>
        @endif
    </section>

    <section class="about-page-section" aria-labelledby="about-contact-heading">
        <h2 id="about-contact-heading" class="section-title-bg p-2 mt-4 mb-3">تواصل مع أكاديمية البيان</h2>
        <p>نحن هنا للإجابة على جميع استفساراتكم ومساعدتكم في اختيار البرنامج التدريبي الأنسب لكم. يمكنكم أيضاً زيارة <a href="{{ $contactUrl }}">{{ trans('site.contact_page_title') }}</a>.</p>
        <ul class="list-unstyled about-page-contact-links">
            @if(!empty($whatsappLink))
                <li class="mb-2">
                    <i class="fab fa-whatsapp" aria-hidden="true"></i>
                    <strong>واتساب:</strong>
                    <a href="{{ $whatsappLink }}" target="_blank" rel="noopener">{{ trans('site.about_whatsapp_cta_link') }}</a>
                </li>
            @endif
            @foreach($phoneLinks ?? [] as $phone)
                <li class="mb-2">
                    <i data-feather="phone" width="18" height="18" aria-hidden="true"></i>
                    <strong>الهاتف:</strong>
                    <a href="{{ $phone['href'] }}">{{ $phone['label'] }}</a>
                </li>
            @endforeach
            @foreach($emailLinks ?? [] as $email)
                <li class="mb-2">
                    <i data-feather="mail" width="18" height="18" aria-hidden="true"></i>
                    <strong>البريد الإلكتروني:</strong>
                    <a href="{{ $email['href'] }}">{{ $email['label'] }}</a>
                </li>
            @endforeach
        </ul>
    </section>
</article>
