<template>
  <div>
    <JoystickControl @speedChange="updateSpeed" @command="handleCommand" />
    <ArrowSimulator ref="arrowSimulator" :speed="speed" />

    <!-- Seção para mostrar a orientação atual da seta -->
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

    <div id="speed-controls">
      <button @click="decreaseSpeed">-</button>
      <span>Speed: x{{ speed }}</span>
      <button @click="increaseSpeed">+</button>
    </div>
    <!-- Outros conteúdos do Dashboard -->
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { io } from 'socket.io-client';  // Importar socket.io-client
import axios from 'axios';
import JoystickControl from '../components/JoystickControl.vue';
import ArrowSimulator from '../components/ArrowSimulator.vue';

const socket = io('http://localhost:3000');  // Conectar ao servidor websocket

const workInProgressProjects = ref([]);
const speed = ref(1); // Velocidade inicial
const arrowSimulator = ref(null);
const activeKey = ref(null); // Estado para a tecla ativa
const currentOrientation = ref('Parado'); // Nova variável para armazenar a orientação atual

onMounted(async () => {
  try {
    const userId = 1;
    const response = await axios.get('users/' + userId + '/projects/inprogress');
    workInProgressProjects.value = response.data.data;
  } catch (error) {
    console.log(error);
  }
  window.addEventListener('keydown', handleKeyDown);
  window.addEventListener('keyup', handleKeyUp);

  // Ouvir os comandos do servidor
  socket.on('direcao', (data) => {
    handleCommand(data.direcao);
  });
});

onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleKeyDown);
  window.removeEventListener('keyup', handleKeyUp);
  socket.off('direcao');  // Remover o listener quando o componente é desmontado
});

const updateSpeed = (newSpeed) => {
  speed.value = newSpeed;
};

const handleCommand = (command) => {
  if (arrowSimulator.value) {
    arrowSimulator.value.updatePosition(command);
    // Atualizar a orientação atual com base no ArrowSimulator
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

const increaseSpeed = () => {
  speed.value = Math.min(speed.value + 0.5, 5); // Aumentar a velocidade em 0.5, limite máximo 5
};

const decreaseSpeed = () => {
  speed.value = Math.max(speed.value - 0.5, 0.5); // Diminuir a velocidade em 0.5, limite mínimo 0.5
};
</script>

<style>
#current-orientation {
  position: fixed;
  left: 50%;
  transform: translateX(-50%); /* Centraliza horizontalmente */
  background-color: white; /* Adiciona um fundo para destacar o texto */
  padding: 10px; /* Adiciona espaçamento ao redor do texto */
  border-radius: 5px; /* Adiciona bordas arredondadas */
  box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); /* Adiciona uma sombra para destaque */
  font-size: 18px; /* Ajusta o tamanho da fonte */
  font-weight: bold; /* Deixa o texto em negrito */
  z-index: 1000; /* Garante que fique sobre outros elementos */
}

#arrow-controls {
  display: flex;
  flex-direction: column;
  align-items: center;
  position: fixed;
  bottom: 80px; /* Ajuste conforme necessário */
  right: 20px; /* Ajuste conforme necessário */
  background-color: white; /* Adiciona um fundo para destacar os botões */
  padding: 10px; /* Adiciona espaçamento ao redor dos botões */
  border-radius: 5px; /* Adiciona bordas arredondadas */
  box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); /* Adiciona uma sombra para destaque */
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
  cursor: pointer; /* Adiciona um cursor de ponteiro para indicar que é clicável */
}

#arrow-controls .key.active {
  background-color: gray;
  color: white;
}

#speed-controls {
  display: flex;
  flex-direction: row; /* Alinha os botões horizontalmente */
  align-items: center;
  position: fixed; /* Fixa os botões em uma posição na tela */
  bottom: 20px; /* Ajuste conforme necessário */
  right: 20px; /* Ajuste conforme necessário */
  background-color: white; /* Adiciona um fundo para destacar os botões */
  padding: 10px; /* Adiciona espaçamento ao redor dos botões */
  border-radius: 5px; /* Adiciona bordas arredondadas */
  box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); /* Adiciona uma sombra para destaque */
}

#speed-controls button {
  width: 30px;
  height: 30px;
  font-size: 18px;
  cursor: pointer; /* Adiciona um cursor de ponteiro para indicar que é clicável */
  margin: 0 5px; /* Espaçamento entre os botões */
}

#speed-controls span {
  margin: 0 10px;
  font-size: 18px;
}
</style>
