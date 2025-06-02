<?php

namespace App\Http\Controllers;

use App\Models\Stream;
use Exception;
use GuzzleHttp\Client;

class StreamController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['only' => ['index', 'show']]);
    }

    public function index()
    {
        //Retrieve streams
        $client = new Client();
        $url = "http://localhost:7000/api/streams";

        $streams = [];
        $results = [];

        try {
            $response = $client->get($url);
            $results = json_decode($response->getBody());
        } catch(Exception  $e) {  }

        foreach($results as $stream)
        {
            $viewers = count(reset($stream)->subscribers);
            $stream = reset($stream)->publisher;

            if($stream == null || $stream->stream == null) continue;

            $new_stream = new Stream();
            $new_stream->id = $stream->stream;
            $new_stream->name = "Stream " . $stream->stream;
            $new_stream->watchers = $viewers;

            $streams[count($streams)] = $new_stream;
        }

        return view('streams/index')->with('streams', $streams);
    }

    public function show($id)
    {
        $stream = new Stream();
        $stream->id = $id;
        $stream->name = "Stream " . $id;
        $stream->watchers = 0;

        return view('streams/show')->with('stream', $stream);
    }
}
