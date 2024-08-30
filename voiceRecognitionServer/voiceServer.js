const express = require('express');
const { spawn, exec } = require('child_process'); 
const cors = require('cors');

const app = express();
const port = 4000;

let voiceRecognitionProcess = null;

app.use(cors());
app.use(express.json());

app.post('/start-voice-recognition', (req, res) => {
  if (voiceRecognitionProcess) {
    return res.status(400).send('Voice recognition is already running.');
  }

  const scriptPath = 'voiceCommands.py';
  voiceRecognitionProcess = spawn('python', [scriptPath], { shell: true });

  voiceRecognitionProcess.stdout.on('data', (data) => {
    console.log(`stdout: ${data}`);
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

  // Kill the Python process
  voiceRecognitionProcess.kill('SIGINT');
  voiceRecognitionProcess = null;

  // Automatically kill the process using port 5000
  const killCommand = process.platform === 'win32'
    ? `for /f "tokens=5" %a in ('netstat -ano ^| findstr :5000') do taskkill /F /PID %a`
    : `lsof -t -i :5000 | xargs kill -9`;

  exec(killCommand, (error, stdout, stderr) => {
    if (error) {
      console.error(`Error killing process on port 5000: ${error}`);
      return res.status(500).send('Error stopping voice recognition.');
    }
    console.log(`Process on port 5000 killed successfully.`);
    res.send('Voice recognition stopped and port 5000 cleared.');
  });
});

app.listen(port, () => {
  console.log(`API server running at http://localhost:${port}`);
});
