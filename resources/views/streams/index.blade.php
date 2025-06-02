@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card cardshadow">
                <div class="card-body">
                    <h2>Streams</h2>
                    <div class="row">
                        @foreach($streams as $stream)
                            <div class="col-md-3 mb-4">
                                <div class="card">
                                    <span class="badge badge-primary color-orange position-absolute m-1"><i class="fa-solid fa-eye"></i> {{ $stream->watchers }}</span>
                                    <video class="card-img-top" id="stream-thumbnail-{{ $stream->id }}" alt="Card image cap"></video>
                                    <div class="card-body">
                                        <h5 class="card-title">{{ ucfirst($stream->name) }}</h5>
                                        @if($stream->desc)<p class="card-text">{{ ucfirst($stream->desc) }}</p>@endif
                                        <a href="/streams/{{ $stream->id }}" class="btn btn-primary"><i class="fa-solid fa-circle-play"></i> Watch</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script type="module" src="{{URL::asset('js/thumbnail.js')}}"></script>
@endsection
