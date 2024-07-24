const express = require('express');
const cors = require('cors');
const http = require('http');
const { Server } = require('socket.io');
const { spawn, exec } = require('child_process');

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

let voiceRecognitionProcess = null;

app.post('/direcao', (req, res) => {
  const directionData = req.body;
  console.log('Direção recebida:', directionData);

  if (directionData.source === 'voice') {
    io.emit('direcao_voice', directionData.direcao);
  }

  res.send('Dados recebidos com sucesso');
});

app.post('/start-voice-recognition', (req, res) => {
  if (voiceRecognitionProcess) {
    return res.status(400).send('Voice recognition is already running.');
  }

  const scriptPath = 'voiceCommands/voice_recognition.py';
  let command;
  if (process.platform === 'win32') {
    // Comando para Windows
    command = `cmd /k "venv\\Scripts\\activate && python ${scriptPath}"`;
  } else {
    // Comando para Unix (Linux, macOS)
    const venvActivatePath = 'venv/bin/activate';
    command = `source ${venvActivatePath} && python ${scriptPath}`;
  }

  voiceRecognitionProcess = spawn(command, { shell: true, stdio: 'inherit' });

  voiceRecognitionProcess.on('close', (code) => {
    console.log(`Voice recognition process exited with code ${code}`);
    voiceRecognitionProcess = null;
  });

  res.send('Reconhecimento de voz iniciado com sucesso');
});

app.post('/stop-voice-recognition', (req, res) => {
  if (!voiceRecognitionProcess) {
    return res.status(400).send('Voice recognition is not running.');
  }

  try {
    if (process.platform === 'win32') {
      // Para Windows
      exec(`taskkill /pid ${voiceRecognitionProcess.pid} /f /t`);
    } else {
      // Para Unix (Linux, macOS)
      process.kill(-voiceRecognitionProcess.pid);
    }
  } catch (err) {
    if (err.code === 'ESRCH') {
      console.log('Process already terminated');
    } else {
      console.error('Error killing process', err);
    }
  }

  voiceRecognitionProcess = null;
  res.send('Reconhecimento de voz parado com sucesso');
});

server.listen(port, () => {
  console.log(`Servidor rodando em http://localhost:${port}`);
});
