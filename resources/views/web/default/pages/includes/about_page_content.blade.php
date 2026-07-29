{{-- About page main copy (Arabic) — semantic sections for SEO --}}
@php
    $classesUrl = url('/classes');
    $aboutPhoneDisplay = '971043931889+';
    $aboutPhoneHref = 'tel:+97143931889';
    $aboutEmail = getSiteContactEmail() ?? '';

    if (!empty($phoneLinks)) {
        $aboutPhoneDisplay = $phoneLinks[0]['label'];
        $aboutPhoneHref = $phoneLinks[0]['href'];
    }
    if (!empty($emailLinks)) {
        $aboutEmail = $emailLinks[0]['label'];
    }
@endphp

<article class="about-page-content contact-us-about col-12 py-4">
    <header>
        <h1 class="section-title-bg p-2 text-center mb-4">عن معهد البيان</h1>
        <p class="text-center mb-4">مرحباً بكم في معهد البيان، وجهتكم الاولى للتعليم المهني واكتساب المهارات العملية في دبي ودولة الإمارات العربية المتحدة.</p>
    </header>

    <p>في معهد البيان لا نؤمن بفكرة "احضر الدورة و احصل على الشهادة"، لأن هذا وحده لا يكفي، ما يهمنا هو أن يخرج المتدرب بمهارات يستخدمها في عمله في دراسته، أو في مشروعه، لـ هذا نبني كل برنامج على اساس تطبيقي بالمقام الاول، و نحرص أن يعرف المتدرب من اليوم الأول ما الذي سيتعلمه وإلى أين سيوصله.</p>

    <section class="about-page-section" aria-labelledby="about-vision-heading">
        <h2 id="about-vision-heading" class="section-title-bg p-2 mt-4 mb-3">رؤيتنا</h2>
        <p>أن يكون معهد البيان المرجع الأول لأي شخص في الإمارات يريد تطوير نفسه مهنياً، لأننا نربط التدريب بما يطلبه سوق العمل فعلاً، لا بما كان مطلوباً قبل خمس سنوات.</p>
    </section>

    <section class="about-page-section" aria-labelledby="about-mission-heading">
        <h2 id="about-mission-heading" class="section-title-bg p-2 mt-4 mb-3">رسالتنا</h2>
        <p>نسد الفجوة بين التعليم والعمل، نقدم برامج يقودها متخصصون من الميدان، وليس فقط في المعهد، حتى يخرج المتدرب بثقة حقيقية في مهارته سواء كان هدفه الترقية او التوظيف، او فتح مشروع خاص.</p>
    </section>

    <section class="about-page-section" aria-labelledby="about-goals-heading">
        <h2 id="about-goals-heading" class="section-title-bg p-2 mt-4 mb-3">مهمتنا</h2>
        <p>نجعل التعليم أبسط و أوضح لكل شخص، قبل التسجيل يتحدث المتدرب مع فريقنا ليفهم بالضبط ما يناسبه، أثناء الدراسة يجد دعماً مباشراً دون تعقيد، بعد الانتهاء يحمل شهادة معتمدة تضيف لسيرته الذاتية وزناً حقيقياً.</p>
    </section>

    <section class="about-page-section" aria-labelledby="about-programs-heading">
        <h2 id="about-programs-heading" class="section-title-bg p-2 mt-4 mb-3">ماذا نقدم في معهد البيان؟</h2>
        <p>في معهد البيان نقدم مجموعة متنوعة من البرامج التدريبية المصممة لتلبية احتياجات الأفراد و المهنيين والمؤسسات، وتشمل:</p>
        <ul class="about-page-list list-unstyled">
            <li class="mb-3">
                <h3 class="h5 font-weight-bold d-inline">دورات اللغات:</h3>
                <span> برامج متخصصة لتطوير اللغة الإنجليزية وغيرها، مع التركيز على المحادثة و الاستخدام العملي في بيئتي العمل و الدراسة.</span>
            </li>
            <li class="mb-3">
                <h3 class="h5 font-weight-bold d-inline">الدورات الإدارية و المهنية:</h3>
                <span> برامج تساعد الموظفين والباحثين عن عمل تطوير مهاراتهم في القيادة والإدارة وخدمة العملاء والتواصل الفعال وإدارة الوقت.</span>
            </li>
            <li class="mb-3">
                <h3 class="h5 font-weight-bold d-inline">دورات المحاسبة و المالية:</h3>
                <span> تدريب عملى يُمكن المتدربين من استيعاب الأسس المالية و المحاسبية وتوظيفها بكفاءة في بيئة العمل.</span>
            </li>
            <li class="mb-3">
                <h3 class="h5 font-weight-bold d-inline">دورات التسويق والتجارة الإلكترونية:</h3>
                <span> برامج تواكب متطلبات السوق الرقمي وتُزوّد المتدربين بالمهارات اللازمة لفهم التسويق والبيع وإدارة الأنشطة الرقمية.</span>
            </li>
            <li class="mb-3">
                <h3 class="h5 font-weight-bold d-inline">برامج التدريب المؤسسي:</h3>
                <span> حلول تدريبية مخصصة للشركات والفرق، تهدف إلى تطوير كفاءة الموظفين ورفع مستوى الأداء بصورة مستدامة.</span>
            </li>
        </ul>
        <p class="mb-0">
            <a href="{{ $classesUrl }}" class="font-weight-bold">{{ trans('site.about_browse_programs') }}</a>
        </p>
    </section>

    <section class="about-page-section" aria-labelledby="about-stats-heading">
        <h2 id="about-stats-heading" class="section-title-bg p-2 mt-4 mb-3">أرقام تعكس تطور معهد البيان</h2>
        <dl class="about-page-stats row text-center my-4">
            <div class="col-6 col-md-3 mb-3">
                <dt class="font-weight-bold d-block" style="font-size: 1.25rem; color: var(--primary, #01477d);">+300</dt>
                <dd class="mb-0">برنامج تدريبي احترافي</dd>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <dt class="font-weight-bold d-block" style="font-size: 1.25rem; color: var(--primary, #01477d);">اعتمادات</dt>
                <dd class="mb-0">محلية KHDA ودولية</dd>
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
        <h2 id="about-why-heading" class="section-title-bg p-2 mt-4 mb-3">لماذا تختار معهد البيان؟</h2>
        <p class="font-weight-bold">لأننا لا نقدم تدريبًا تقليديًا…</p>
        <ul class="about-page-features list-unstyled">
            <li class="mb-3">
                <h3 class="h5 font-weight-bold d-inline">تعلم عملي وليس نظريًا فقط:</h3>
                <span> نركز على المهارات التي يمكن استخدامها في العمل، الدراسة، والحياة اليومية.</span>
            </li>
            <li class="mb-3">
                <h3 class="h5 font-weight-bold d-inline">مرونة في التعلم:</h3>
                <span> يمكنك الالتحاق ببرامج حضورية أو أونلاين حسب ما يناسب وقتك وهدفك.</span>
            </li>
            <li class="mb-3">
                <h3 class="h5 font-weight-bold d-inline">شهادة تعزز مسارك:</h3>
                <span> نساعدك على الحصول على شهادة تدريبية تدعم سيرتك الذاتية وتوثق رحلتك التعليمية.</span>
            </li>
            <li class="mb-3">
                <h3 class="h5 font-weight-bold d-inline">دعم مباشر وسهل:</h3>
                <span> فريقنا يساعدك في اختيار البرنامج المناسب والتسجيل والمتابعة، مع إمكانية التواصل المباشر عبر واتساب.</span>
            </li>
            <li class="mb-3">
                <h3 class="h5 font-weight-bold d-inline">تجربة تعليمية منظمة وواضحة:</h3>
                <span> نعمل على تقديم تجربة تدريبية منظمة، واضحة، ومبنية على الثقة والجودة.</span>
            </li>
            <li class="mb-3">
                <h3 class="h5 font-weight-bold d-inline">قيمة مناسبة مقابل ما تتعلمه:</h3>
                <span> نقدم برامج تساعدك على تحقيق استفادة حقيقية دون تعقيد أو تكلفة مبالغ فيها.</span>
            </li>
        </ul>
    </section>

    <section class="about-page-section" aria-labelledby="about-values-heading">
        <h2 id="about-values-heading" class="section-title-bg p-2 mt-4 mb-3">قيم معهد البيان</h2>
        <ul class="about-page-values list-unstyled">
            <li class="mb-3">
                <h3 class="h5 font-weight-bold mb-1">الوضوح</h3>
                <p class="mb-0">نشرح للمتدرب البرنامج، الهدف، والمخرجات قبل التسجيل.</p>
            </li>
            <li class="mb-3">
                <h3 class="h5 font-weight-bold mb-1">الثقة والالتزام</h3>
                <p class="mb-0">نلتزم بتقديم تجربة تدريبية قائمة على الثقة والالتزام.</p>
            </li>
            <li class="mb-3">
                <h3 class="h5 font-weight-bold mb-1">المرونة</h3>
                <p class="mb-0">نوفر حلولًا تناسب الطالب، الموظف، وصاحب العمل.</p>
            </li>
            <li class="mb-3">
                <h3 class="h5 font-weight-bold mb-1">الجودة العملية</h3>
                <p class="mb-0">نركز على التدريب الذي يساعد المتدرب في الواقع، وليس فقط في القاعة.</p>
            </li>
            <li class="mb-3">
                <h3 class="h5 font-weight-bold mb-1">الاهتمام بالمتدرب</h3>
                <p class="mb-0">نساعد كل متدرب على اختيار المسار الأقرب لهدفه.</p>
            </li>
        </ul>
    </section>

    <section class="about-page-section" aria-labelledby="about-audience-heading">
        <h2 id="about-audience-heading" class="section-title-bg p-2 mt-4 mb-3">لمن يناسب معهد البيان؟</h2>
        <p>يناسب معهد البيان الطلاب الذين يريدون مهارات إضافية، للموظفين الذين يفكرون في الترقية، لمن يبحث عن عمل ويريد سيرة ذاتية أقوى، لرواد الأعمال الذين يحتاجون أدوات عملية، وللشركات التي تستثمر في فريقها.</p>
    </section>

    <section class="about-page-cta text-center my-5 p-4 rounded-lg" style="background: var(--secondary, #01477d); color: #fff;" aria-labelledby="about-cta-heading">
        <h2 id="about-cta-heading" class="h4 font-weight-bold mb-3">{{ trans('site.about_cta_heading') }}</h2>
        <p class="mb-3 font-weight-bold">ابدأ اليوم رحلتك نحو فرصة مهنية أفضل.</p>
        <p class="mb-0">تواصل مع فريق معهد البيان عبر واتساب وسنساعدك في تحديد الدورة الأقرب لهدفك.</p>
        @if(!empty($whatsappLink))
            <a href="{{ $whatsappLink }}" target="_blank" rel="noopener" class="btn btn-light mt-3">
                <i class="fab fa-whatsapp" aria-hidden="true"></i> {{ trans('site.about_whatsapp_cta_link') }}
            </a>
        @endif
    </section>

    <section class="about-page-section" aria-labelledby="about-contact-heading">
        <h2 id="about-contact-heading" class="section-title-bg p-2 mt-4 mb-3">تواصل مع معهد البيان</h2>
        <p>نحن هنا للإجابة على جميع استفساراتكم ومساعدتكم في اختيار البرنامج التدريبي الأنسب لكم.</p>
        <ul class="list-unstyled about-page-contact-links">
            <li class="mb-2">
                <i data-feather="phone" width="18" height="18" aria-hidden="true"></i>
                <strong>الهاتف:</strong>
                <a href="{{ $aboutPhoneHref }}">{{ $aboutPhoneDisplay }}</a>
            </li>
            @if(!empty($aboutEmail))
                <li class="mb-2">
                    <i data-feather="mail" width="18" height="18" aria-hidden="true"></i>
                    <strong>البريد الإلكتروني:</strong>
                    <a href="mailto:{{ $aboutEmail }}">{{ $aboutEmail }}</a>
                </li>
            @endif
        </ul>
    </section>
</article>
