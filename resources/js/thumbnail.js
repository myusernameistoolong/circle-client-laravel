const flvjs = require("flv.js");

$( document ).ready(function() {
    if (flvjs.isSupported()) {
        $('[id^="stream-thumbnail-"]').each(function () {
            let id = $(this).attr('id').replace('stream-thumbnail-', '');
            let flvPlayer = flvjs.createPlayer({
                type: 'flv',
                url: 'http://localhost:7000/live/' + id + '.flv'
            });
            flvPlayer.attachMediaElement(this);
            flvPlayer.load();
        });
    }
});

