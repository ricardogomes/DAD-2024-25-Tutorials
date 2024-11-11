const httpServer = require("http").createServer();
const io = require("socket.io")(httpServer, {
  cors: {
    origin: "*",
    methods: ["GET", "POST"],
    credentials: true,
  },
});

const PORT = process.env.APP_PORT || 8086;

httpServer.listen(PORT, () => {
  console.log(`listening on localhost:${PORT}`);
});

io.on("connection", (socket) => {
  console.log(`client ${socket.id} has connected`);

  socket.on("echo", (message) => {
    socket.emit("echo", message);
  });
});
