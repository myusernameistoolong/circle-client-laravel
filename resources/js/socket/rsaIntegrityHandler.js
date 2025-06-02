const crypto = require ('crypto-browserify');
const sha256 = require('crypto-js/sha256');
const replayTimeOutInMinutes = 1;

function signMessage(msg, stream){
    const timestamp = Date.now();

    const message = {
        msg: msg,
        username: stream.username,
        stream: stream.streamId,
        timestamp: timestamp
    }

    //Private key of user
    const privateKey = stream.privateKey
        .replace(/-----BEGIN RSA PRIVATE KEY-----/g, '-----BEGIN RSA PRIVATE KEY-----\n')
        .replace(/-----END RSA PRIVATE KEY-----/g, '\n-----END RSA PRIVATE KEY-----');

    const hash = sha256(message).toString();
    const encryptedHash = crypto.privateEncrypt(
        {
            key: privateKey
        },
        Buffer.from(hash)
    ).toString("base64");

    return {
        message: message,
        signature: encryptedHash,
        timestamp: timestamp,
    };
}

function verifyMessage(message){
    //Public key of chat backend
    let publicKey = "-----BEGIN PUBLIC KEY-----MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDoz6gkuDB9lPc36+9BBjnItfQE2yxCtAX4t/cDe5gcXi1cpJfg8hC8Sv6wAM7TBvSYv7bph5eYvxUcFmGWRgQkrsl8QSG/qwnOVYybtG2VSEEm1BxiuYbIyeIPdR2tAo0lEhCeXqRKK594OL05+0A+3vxJAQVbx0DHGXyTlx7jNQIDAQAB-----END PUBLIC KEY-----"
        .replace(/-----BEGIN PUBLIC KEY-----/g, '-----BEGIN PUBLIC KEY-----\n')
        .replace(/-----END PUBLIC KEY-----/g, '\n-----END PUBLIC KEY-----');

    const decryptedHashMessage = crypto.publicDecrypt(
        {
            key: publicKey
        },
        Buffer.from(message.signature, "base64")
    )

    //Replay attack prevention
    /*if (moment(message.message.timestamp).add(replayTimeOutInMinutes, 'm').toDate() < Date.now()) {
        console.log(username + ': possible replay attack')
        return false;
    }*/

    const hashMessage = sha256(message.message).toString();
    return decryptedHashMessage.toString() === hashMessage;
}

module.exports = {
    signMessage,
    verifyMessage
};
