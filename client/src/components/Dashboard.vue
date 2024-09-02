<template>
  <div>
    <JoystickControl @speedChange="updateSpeed" @command="handleCommand" />
    <ArrowSimulator ref="arrowSimulator" :speed="speed" @updateOrientation="currentOrientation = $event" />

    <div id="current-orientation">
      <span>Orientação Atual: {{ currentOrientation }}</span>
    </div>

    <div id="arrow-controls">
      <div class="row">
        <div class="key" :class="{ active: activeKey === 'ArrowUp' }" @mousedown="handleMouseDown('ArrowUp')"
          @mouseup="handleMouseUp">↑</div>
      </div>
      <div class="row">
        <div class="key" :class="{ active: activeKey === 'ArrowLeft' }" @mousedown="handleMouseDown('ArrowLeft')"
          @mouseup="handleMouseUp">←</div>
        <div class="key" :class="{ active: activeKey === 'ArrowDown' }" @mousedown="handleMouseDown('ArrowDown')"
          @mouseup="handleMouseUp">↓</div>
        <div class="key" :class="{ active: activeKey === 'ArrowRight' }" @mousedown="handleMouseDown('ArrowRight')"
          @mouseup="handleMouseUp">→</div>
      </div>
    </div>

    <div id="microphone-icon" @click="toggleVoiceRecognition">
      <span v-if="isVoiceRecognitionActive">🛑</span> <!-- Stop Icon -->
      <span v-else>🎤</span> <!-- Microphone Icon -->
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import io from 'socket.io-client';
import JoystickControl from '../components/JoystickControl.vue';
import ArrowSimulator from '../components/ArrowSimulator.vue';

const socket = io('http://localhost:3000/direcao'); // Certifique-se que o IP está correto

const speed = ref(1); // Velocidade inicial
const arrowSimulator = ref(null);
const activeKey = ref(null); // Estado para a tecla ativa
const currentOrientation = ref('Parado'); // Variável para armazenar a orientação atual
const isVoiceRecognitionActive = ref(false); // Estado para controlo do reconhecimento de voz

const updateSpeed = (newSpeed) => {
  speed.value = newSpeed;
};

const handleCommand = (command) => {
  console.log(`Enviando comando: ${command} com velocidade: ${speed.value}`);
  if (arrowSimulator.value) {
    arrowSimulator.value.updatePosition(command);
    currentOrientation.value = arrowSimulator.value.getOrientation(command);
  }
  // Emitir o comando para o servidor via socket
  socket.emit('command', { command, speed: speed.value });
};

const sendDirectionToServer = (data) => {
  console.log('Sending data:', data);
  axios.post('http://localhost:3000/direcao', data)
    .then(response => {
      console.log('Response from server:', response.data);
    })
    .catch(error => {
      console.error('Error sending data:', error);
    });
};

const handleMouseDown = (key) => {
  let command = null;
  activeKey.value = key;
  switch (key) {
    case 'ArrowUp':
      command = 'forward';
      break;
    case 'ArrowDown':
      command = 'backward';
      break;
    case 'ArrowLeft':
      command = 'left';
      break;
    case 'ArrowRight':
      command = 'right';
      break;
  }
  if (command) {
    handleCommand(command);
    sendDirectionToServer({ command }); // Send the command to the server
  }
};

const handleMouseUp = () => {
  handleCommand('stop');
  sendDirectionToServer({ command: 'stop' }); // Send the stop command to the server
  activeKey.value = null;
};

const toggleVoiceRecognition = () => {
  if (!isVoiceRecognitionActive.value) {
    axios.post('http://localhost:4000/start-voice-recognition')
      .then(response => {
        console.log('Voice recognition started:', response.data);
        isVoiceRecognitionActive.value = true;
      })
      .catch(error => {
        console.error('Error starting voice recognition:', error);
      });
  } else {
    axios.post('http://localhost:4000/stop-voice-recognition')
      .then(response => {
        console.log('Voice recognition stopped:', response.data);
        isVoiceRecognitionActive.value = false;
      })
      .catch(error => {
        console.error('Error stopping voice recognition:', error);
      });
  }
};

const handleVoiceRecognitionReady = (message) => {
  alert(message);
  socket.off('voice-recognition-ready', handleVoiceRecognitionReady);
};

onMounted(() => {
  socket.on('connect', () => {
    console.log('Conectado ao servidor via Socket.io');
  });

  socket.on('disconnect', () => {
    console.log('Desconectado do servidor');
  });

  socket.on('voice-recognition-ready', handleVoiceRecognitionReady);

  // Adicionando ouvinte para comandos de voz
  socket.on('voice-command', (command) => {
    console.log(`Comando de voz recebido: ${command}`);
    handleCommand(command); // Atualiza a orientação e envia o comando ao servidor
    sendDirectionToServer({ command }); // Envia o comando ao servidor
    currentOrientation.value = arrowSimulator.value.getOrientation(command); // Atualiza a orientação atual
  });
});

onUnmounted(() => {
  socket.off('voice-recognition-ready', handleVoiceRecognitionReady);
  socket.off('voice-command'); // Remover o ouvinte de comandos de voz
  socket.off('connect');
  socket.off('disconnect');
});
</script>

<style>
#current-orientation {
  position: fixed;
  left: 50%;
  transform: translateX(-50%);
  background-color: white;
  padding: 10px;
  border-radius: 5px;
  box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
  font-size: 18px;
  font-weight: bold;
  z-index: 1000;
}

#arrow-controls {
  display: flex;
  flex-direction: column;
  align-items: center;
  position: fixed;
  bottom: 80px;
  right: 20px;
  background-color: white;
  padding: 10px;
  border-radius: 5px;
  box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
}

#arrow-controls .row {
  display: flex;
  justify-content: center;
  margin-bottom: 5px;
}

#arrow-controls .key {
  width: 50px;
  height: 50px;
  background-color: lightgray;
  display: flex;
  justify-content: center;
  align-items: center;
  margin: 5px;
  border-radius: 10px;
  font-size: 24px;
  cursor: pointer;
}

#arrow-controls .key.active {
  background-color: gray;
  color: white;
}

#microphone-icon {
  position: fixed;
  bottom: 20px;
  right: 230px;
  background-color: white;
  padding: 10px;
  border-radius: 50%;
  box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
  font-size: 24px;
  cursor: pointer;
}
</style>
