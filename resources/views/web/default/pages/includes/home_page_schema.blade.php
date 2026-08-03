{{-- Homepage Schema.org JSON-LD (@graph) --}}
@php
    $schemaService = app(\App\Services\SchemaOrgService::class);
    $schemaDocument = $schemaService->document(
        $schemaService->homeGraph($pageTitle ?? null, $pageDescription ?? null)
    );
@endphp
<script type="application/ld+json">{!! $schemaService->toJson($schemaDocument) !!}</script>
