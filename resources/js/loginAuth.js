const axios = require('axios');
const crypto = require ('crypto-browserify');
const sha256 = require('crypto-js/sha256');
const cookies = require('js-cookie');

if(!document.URL.includes("login")) authenticate();

function authenticate()
{
    let username = localStorage.getItem('username');
    let privateKey = localStorage.getItem('private-key');

    if(username == null || privateKey == null) {
        logOut();
        $("#logout-form").submit();
        return;
    }

    rsaUserIntegrityCheck(username, privateKey).then((response) => {
        console.log(response);
        if(response) {
            console.log(localStorage.getItem("username") + " logged in!");
        }
        else
        {
            logOut();
            $("#logout-form").submit();
        }
    });
}

$("#login-form").submit(function(e){
    e.preventDefault()
    const form = e.target

    login(form);
});

function login(form)
{
    let username = $('input[name="username"]').val();
    let privateKey = $('#private_key').val();

    logOut();

    rsaUserIntegrityCheck(username, privateKey).then((response) => {
        console.log(response);
        if(response) {
            localStorage.setItem("username", username);
            localStorage.setItem("private-key", privateKey);
            cookies.set("username", username, {expires: 1})
        }

        form.submit();
    });
}

async function rsaUserIntegrityCheck(username, privateKey) {
    let url = "https://thecircle-thruyou.herokuapp.com/api/key/" + username;
    let res = false;

    try {
        res = await axios.get(url)
            .then(response => {
                //TRUYOU INTEGRITY
                let publicKeyTruYou = response.data.thruyou_public_key
                    .replace(/-----BEGIN PUBLIC KEY-----/g, '-----BEGIN PUBLIC KEY-----\n')
                    .replace(/-----END PUBLIC KEY-----/g, '\n-----END PUBLIC KEY-----');

                const encryptedHash = response.headers['x-hash'];
                const decryptedHash = crypto.publicDecrypt(
                    {
                        key: publicKeyTruYou
                    },
                    Buffer.from(encryptedHash, "base64")
                )

                const hash = sha256(response.data.username + response.data.public_key).toString();

                if (decryptedHash.toString() !== hash) {
                    console.log("Invalid signature on Message");
                    return false;
                }

                console.log("Integriteit van het bericht van thruyou is gewaarborgd!");

                //TRUYOU AUTHENTICATIE
                let publicKey = response.data.public_key
                    .replace(/-----BEGIN PUBLIC KEY-----/g, '-----BEGIN PUBLIC KEY-----\n')
                    .replace(/-----END PUBLIC KEY-----/g, '\n-----END PUBLIC KEY-----');

                privateKey
                    .replace(/-----BEGIN RSA PRIVATE KEY-----/g, '-----BEGIN RSA PRIVATE KEY-----\n')
                    .replace(/-----END RSA PRIVATE KEY-----/g, '\n-----END RSA PRIVATE KEY-----');

                const encryptedHashMessage = crypto.privateEncrypt(
                    {
                        key: privateKey
                    },
                    Buffer.from("check")
                ).toString("base64");

                let decryptedHashMessage = null;

                try {
                    decryptedHashMessage = crypto.publicDecrypt(
                        {
                            key: publicKey
                        },
                        Buffer.from(encryptedHashMessage, "base64")
                    )
                    console.log(decryptedHashMessage.toString());
                } catch (e) {
                    console.log("Private key doesn't match with username");
                    return false;
                }

                return decryptedHashMessage.toString() === "check";
            });
        return res;
    } catch (e) {
        return false;
    }
}

function logOut()
{
    localStorage.clear();
}

window.login = login;
window.logOut = logOut;
