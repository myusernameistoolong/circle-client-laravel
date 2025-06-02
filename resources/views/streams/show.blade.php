@extends('layouts.app')

@section('content')
    <input type="hidden" name="stream-id" value="{{ $stream->id }}">
    <div class="row">
        <div class="col-md-12">
            <div class="row justify-content-around">
                <div class="card col-8 cardshadow">
                    <div class="card-body">
                        <div class="row justify-content-between">
                            <h4>{{ $stream->name }}</h4>
                        <div>
                            <span class="badge badge-primary color-orange" id="viewers"><i class="fa-solid fa-eye"></i> 0</span>
                        </div>
                        <video class="mt-2" id="videoElement" controls autoplay muted width="100%"></video>
                        </div>
                    </div>
                </div>
                <div class="card col-3 cardshadow">
                    <div class="card-body">
                        <h4>Chat</h4>
                        <div class="bubbleWrapper">
                        <div class="inlineContainer">
                            <ul class="list-group scrollable" id="messages">
                            </ul>
                        </div>
                        </div>
                        <form id="chat-submit" onsubmit="window.sendMessage(); return false;">
                            <div class="input-group">
                                <input class="form-control" name="message" placeholder="Send a message"/>
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit"><i class="fa-solid fa-paper-plane"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script type="module" src="{{URL::asset('js/script.js')}}"></script>
@endsection
