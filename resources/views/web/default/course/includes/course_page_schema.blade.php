{{-- Single course Schema.org JSON-LD (@graph) --}}
@if(!empty($course))
@php
    $schemaService = app(\App\Services\SchemaOrgService::class);
    $schemaDocument = $schemaService->document($schemaService->courseGraph($course));
@endphp
<script type="application/ld+json">{!! $schemaService->toJson($schemaDocument) !!}</script>
@endif
