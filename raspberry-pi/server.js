const express = require('express');
const cors = require('cors');
const app = express();
const port = 3000;

app.use(express.json());
app.use(cors());

app.post('/direcao', (req, res) => {
    const directionData = req.body;
    console.log('Direção recebida:', directionData);
    res.send('Dados recebidos com sucesso');
});

app.listen(port, () => {
    console.log(`Servidor rodando em http://localhost:${port}`);
});
