const express = require('express');
const { spawn } = require('child_process');
const axios = require('axios');
const cors = require('cors'); // Adicione isso

const app = express();
const port = 4000; // Escolha uma porta que não esteja em uso

let voiceRecognitionProcess = null;

app.use(cors()); // Adicione isso para habilitar CORS para todas as origens
app.use(express.json());

app.post('/start-voice-recognition', (req, res) => {
  if (voiceRecognitionProcess) {
    return res.status(400).send('Voice recognition is already running.');
  }

  const scriptPath = 'voiceCommands.py';
  voiceRecognitionProcess = spawn('python', [scriptPath], { shell: true });

  voiceRecognitionProcess.stdout.on('data', (data) => {
    console.log(`stdout: ${data}`);
    if (data.toString().includes("Esperando detecção de som...")) {
      // Enviar uma notificação para o cliente via WebSocket ou outro método
    }
  });

  voiceRecognitionProcess.stderr.on('data', (data) => {
    console.error(`stderr: ${data}`);
  });

  voiceRecognitionProcess.on('close', (code) => {
    console.log(`Voice recognition process exited with code ${code}`);
    voiceRecognitionProcess = null;
  });

  res.send('Voice recognition started successfully.');
});

app.post('/stop-voice-recognition', (req, res) => {
  if (!voiceRecognitionProcess) {
    return res.status(400).send('Voice recognition is not running.');
  }

  voiceRecognitionProcess.kill('SIGINT');
  voiceRecognitionProcess = null;
  res.send('Voice recognition stopped.');
});

app.post('/pause-listening', (req, res) => {
  if (!voiceRecognitionProcess) {
    return res.status(400).send('Voice recognition is not running.');
  }

  axios.post('http://localhost:5000/pause-listening')
    .then(response => {
      res.send(response.data);
    })
    .catch(error => {
      console.error('Error toggling listening state:', error);
      res.status(500).send('Failed to toggle listening state.');
    });
});

app.listen(port, () => {
  console.log(`API server running at http://localhost:${port}`);
});
