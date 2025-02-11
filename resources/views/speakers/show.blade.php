@extends('layouts.app')
@section('content')
<div class="card">
    <div class="card-body text-center">
        <img src="{{ $speaker['speaker']['photo_url'] }}" alt="{{ $speaker['speaker']['name'] }}" 
             class="rounded-circle mb-3" style="width: 200px; height: 200px; object-fit: cover;">
        <h1>{{ $speaker['speaker']['name'] }}</h1>
        <p class="lead">{{ $speaker['speaker']['expertise_areas'] }}</p>

        <div class="row mt-4">
            <div class="col-md-8 mx-auto">
                <h2 class="h4">Biografía</h2>
                

                <h2 class="h4 mt-4">Próximos eventos</h2>
                <ul class="list-group">
                   {{ $speaker['speaker']['social_links'] }}
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection