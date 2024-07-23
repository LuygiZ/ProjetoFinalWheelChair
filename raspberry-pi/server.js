const express = require('express');
const cors = require('cors');
const http = require('http');
const { Server } = require('socket.io');

const app = express();
const port = 3000;

app.use(express.json());
app.use(cors());

const server = http.createServer(app);
const io = new Server(server, {
  cors: {
    origin: '*',
  },
});

app.post('/direcao', (req, res) => {
    const directionData = req.body;
    console.log('Direção recebida:', directionData);

    if (directionData.source === 'voice') {
        io.emit('direcao_voice', directionData);
    } else {
        io.emit('direcao_client', directionData);
    }

    res.send('Dados recebidos com sucesso');
});

server.listen(port, () => {
    console.log(`Servidor rodando em http://localhost:${port}`);
});
