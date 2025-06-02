import socketConnection from "./socket/socketConnection.js";
import crypto from "crypto-browserify";
import sha256 from "crypto-js/sha256";
const flvjs = require("flv.js");
const axios = require('axios');

$( document ).ready(function() {
    console.log('http://localhost:7000/live/' + $('input[name="stream-id"]').val() + '.flv');
    if (flvjs.isSupported()) {
        var videoElement = document.getElementById('videoElement');
        var flvPlayer = flvjs.createPlayer({
            type: 'flv',
            url: 'http://localhost:7000/live/' + $('input[name="stream-id"]').val() + '.flv'
        });
        flvPlayer.attachMediaElement(videoElement);
        flvPlayer.load();
        flvPlayer.play();
    }

    //handleStreamData();

    const stream = {
        streamId: $('input[name="stream-id"]').val(),
        username:  localStorage.getItem('username'),
        privateKey:  localStorage.getItem('private-key'),
        messages: [],
        viewers: 0,
        messagesElement: document.getElementById("messages"),
        viewersElement: document.getElementById("viewers")
    };

    socketConnection.establishConnection(stream);
});

function sendMessage()
{
    const message = this.$('input[name="message"]').val();
    if(message === "") return;

    //alert("Username: " + username + "\nPrivate key: " + privateKey + "\nMessage: " + message);
    socketConnection.sendMessageToServer(message);
    this.$('input[name="message"]').val("");
}

async function handleStreamData()
{
    console.log("Handle stream data")

    //Establish connection with streaming backend
    const response = await axios.get('http://localhost:7000/live/' + $('input[name="stream-id"]').val() + '.flv', {
        responseType: 'blob' // Retrieve as blob
    });

    const streamData = response.data;

    streamData.on('data', data => {
        console.log(data);

        //Check signature
        if(verifyStream(data)) // Each 2 seconds
        {
            //Display video in HTML player
        }
    });

    streamData.on('end', () => {
        console.log("Stream done");
    });
}

function verifyStream(streamData) {
    //Public key of streaming backend
    let publicKey = "-----BEGIN PUBLIC KEY-----MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDRqFrzPnBui9/3c/lDVbv7OnwfvlVa5OdECljpyo+o81dE3GtoUzOgPID9jKFq366tOjtiVVJEG6TGvbdbShai8x84MZnmt8BDE0V2wZMBef5G3rM4zW1p4sXEldtsq447UL7cVtZLGo+sL9AFrBxszUOfgWzd9YfuqkdLgieEzQIDAQAB-----END PUBLIC KEY-----"
        .replace(/-----BEGIN PUBLIC KEY-----/g, '-----BEGIN PUBLIC KEY-----\n')
        .replace(/-----END PUBLIC KEY-----/g, '\n-----END PUBLIC KEY-----');

    const decryptedHashMessage = crypto.publicDecrypt(
        {
            key: publicKey
        },
        Buffer.from(streamData.signature, "base64")
    )

    const hashMessage = sha256(streamData.data).toString();
    return decryptedHashMessage.toString() === hashMessage;
}

window.sendMessage = sendMessage;

