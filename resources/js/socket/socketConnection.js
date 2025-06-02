const io = require("socket.io-client");

const {
  signMessage,
  verifyMessage
} = require('./rsaIntegrityHandler');

let socket;
let exports = {};
let stream;

exports.establishConnection = (that) => {
  var connectionOptions = {
    "force new connection": true,
    reconnectionAttempts: "Infinity",
    timeout: 10000,
    transports: ["websocket"],
  };

    stream = that;
    RefreshMessages();

    socket = io('ws://localhost:3500', connectionOptions);
    socket.emit('joinStream', signMessage("ok", stream));

    while (stream.messages.length) {
      stream.messages.pop();
    }

    socket.on('message', (message) => {
        if (!verifyMessage(message)) return;
        message = message.message;

        if (message.stream === stream.streamId) {
          const lastMessage = stream.messages[stream.messages.length - 1];

          const messageToAdd =
          {
            id: stream.messages.length + 1,
            username: message.username,
            date: message.time,
            dateWithMilliSeconds: message.timeWithMilliSeconds,
            message: message.text,
            info: message.info
          };

          if (stream.messages.length === 0) {
            stream.messages.push(messageToAdd);
          } else if (lastMessage !== undefined) {
            //if (lastMessage.dateWithMilliSeconds !== message.timeWithMilliSeconds) {
              stream.messages.push(messageToAdd);
            //}
          }

          RefreshMessages();
        }
    });

    socket.on('streamUsers', (message) => {
      if (!verifyMessage(message)) return;

      message = message.message;
      stream.viewers = message.length.toString();
      RefreshViewers();
    });
};

exports.sendMessageToServer = (message, privateKey) => {
  socket.emit("chatMessage", signMessage(message, stream, privateKey));
};

exports.disconnect = () => {
  socket.emit('disconnectUserFromStream', signMessage(socket.id, stream));
};

function RefreshMessages()
{
    $(stream.messagesElement).empty();

    stream.messages.forEach(function(message) {
        if(message.username == "") message.username = "Server";
        $(stream.messagesElement).append("<i class='list-group-item-custom'><b class='ml-1'><div class='userbadge'>" + message.username + ":</div></b> " + message.message + "<div class='custom-time'>" + message.date + "</div></i>");

    });
    $(".scrollable").animate({ scrollTop: 9999 }, 'slow')
}

function RefreshViewers()
{
    $(stream.viewersElement).empty();
    $(stream.viewersElement).append("<i class='fa-solid fa-eye'></i> " + stream.viewers);
}

export default exports;
