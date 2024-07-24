<template>
  <div>
    <JoystickControl @speedChange="updateSpeed" @command="handleCommand" />
    <ArrowSimulator ref="arrowSimulator" :speed="speed" />

    <div id="current-orientation">
      <span>Orientação Atual: {{ currentOrientation }}</span>
    </div>

    <div id="arrow-controls">
      <div class="row">
        <div class="key" :class="{ active: activeKey === 'ArrowUp' }" @mousedown="handleKeyDown({ key: 'ArrowUp' })" @mouseup="handleKeyUp">↑</div>
      </div>
      <div class="row">
        <div class="key" :class="{ active: activeKey === 'ArrowLeft' }" @mousedown="handleKeyDown({ key: 'ArrowLeft' })" @mouseup="handleKeyUp">←</div>
        <div class="key" :class="{ active: activeKey === 'ArrowDown' }" @mousedown="handleKeyDown({ key: 'ArrowDown' })" @mouseup="handleKeyUp">↓</div>
        <div class="key" :class="{ active: activeKey === 'ArrowRight' }" @mousedown="handleKeyDown({ key: 'ArrowRight' })" @mouseup="handleKeyUp">→</div>
      </div>
    </div>

    <!-- Ícone de microfone -->
    <div id="microphone-icon" @click="toggleVoiceRecognition">
      🎤
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import io from 'socket.io-client';
import JoystickControl from '../components/JoystickControl.vue';
import ArrowSimulator from '../components/ArrowSimulator.vue';

const socket = io('http://localhost:3000'); // Certifique-se de ajustar para o endereço correto do servidor

const speed = ref(1); // Velocidade inicial
const arrowSimulator = ref(null);
const activeKey = ref(null); // Estado para a tecla ativa
const currentOrientation = ref('Parado'); // Nova variável para armazenar a orientação atual
const isVoiceRecognitionActive = ref(false); // Estado para controle do reconhecimento de voz

const updateSpeed = (newSpeed) => {
  speed.value = newSpeed;
};

const handleCommand = (command) => {
  if (arrowSimulator.value) {
    arrowSimulator.value.updatePosition(command);
    currentOrientation.value = arrowSimulator.value.getOrientation();
  }
};

const handleKeyDown = (event) => {
  let command = null;
  activeKey.value = event.key;
  switch (event.key) {
    case 'ArrowUp':
      command = 'up';
      break;
    case 'ArrowDown':
      command = 'down';
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
  }
};

const handleKeyUp = () => {
  handleCommand('stop');
  activeKey.value = null;
};

const toggleVoiceRecognition = () => {
  const url = isVoiceRecognitionActive.value 
    ? 'http://localhost:3000/stop-voice-recognition' 
    : 'http://localhost:3000/start-voice-recognition';

  axios.post(url)
    .then(response => {
      console.log(`${isVoiceRecognitionActive.value ? 'Voice recognition stopped' : 'Voice recognition started'}:`, response.data);
      isVoiceRecognitionActive.value = !isVoiceRecognitionActive.value;
    })
    .catch(error => {
      console.error(`Error ${isVoiceRecognitionActive.value ? 'stopping' : 'starting'} voice recognition:`, error);
    });
};

const handleVoiceRecognitionReady = (message) => {
  alert(message);
  socket.off('voice-recognition-ready', handleVoiceRecognitionReady); // Desregistrar o evento
};

onMounted(() => {
  socket.on('voice-recognition-ready', handleVoiceRecognitionReady);
});

onUnmounted(() => {
  socket.off('voice-recognition-ready', handleVoiceRecognitionReady);
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
