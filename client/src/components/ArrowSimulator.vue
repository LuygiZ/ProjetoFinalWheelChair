<template>
  <div id="arrow-container">
    <svg class="lines" :width="containerWidth" :height="containerHeight">
      <line v-for="(line, index) in lines" :key="index"
            :x1="line.x1" :y1="line.y1" :x2="line.x2" :y2="line.y2"
            stroke="blue" stroke-width="4"/>
    </svg>
    <div class="arrow" :style="arrowStyle"></div>
    <div id="speed-controls">
      <button @click="decreaseSpeed">-</button>
      <span>Speed: x{{ speed }}</span>
      <button @click="increaseSpeed">+</button>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue';
import { io } from 'socket.io-client';

export default {
  name: 'ArrowSimulator',
  props: {
    speed: {
      type: Number,
      default: 1
    }
  },
  setup(props, { emit }) {
    const x = ref(0);
    const y = ref(0);
    const angle = ref(0);
    const lines = ref([]);
    const history = ref([]); // Histórico das posições
    const containerWidth = ref(0);
    const containerHeight = ref(0);
    const speed = ref(props.speed);

    const socket = io('http://localhost:3000');  // Substitua pelo IP do seu Raspberry Pi

    onMounted(() => {
      updateContainerSize();
      window.addEventListener('resize', updateContainerSize);

      // Ouvir os comandos do servidor
      socket.on('direcao_voice', (command) => {
        console.log(`Comando de voz recebido: ${command}`);
        handleVoiceCommand(command);
      });
    });

    onBeforeUnmount(() => {
      window.removeEventListener('resize', updateContainerSize);
      socket.off('direcao_voice');
    });

    watch(() => props.speed, (newSpeed) => {
      speed.value = newSpeed;
    });

    const updateContainerSize = () => {
      const container = document.getElementById('arrow-container');
      containerWidth.value = container.clientWidth;
      containerHeight.value = container.clientHeight;
    };

    const handleVoiceCommand = (command) => {
      switch (command) {
        case 'direita':
          updatePosition('right');
          break;
        case 'esquerda':
          updatePosition('left');
          break;
        case 'frente':
          updatePosition('up');
          break;
        case 'tras':
          updatePosition('down');
          break;
        case 'parar':
          updatePosition('stop');
          break;
        case 'mais':
          increaseSpeed();
          break;
        case 'menos':
          decreaseSpeed();
          break;
        default:
          console.log(`Comando desconhecido: ${command}`);
      }
    };

    const updatePosition = (command) => {
      const step = 20 * speed.value; // Multiplicar o passo pela velocidade atual
      const previousX = x.value;
      const previousY = y.value;
      let newX = x.value;
      let newY = y.value;

      switch (command) {
        case 'up':
          newY -= step;
          angle.value = 0;
          break;
        case 'down':
          newY += step;
          angle.value = 180;
          break;
        case 'left':
          newX -= step;
          angle.value = -90;
          break;
        case 'right':
          newX += step;
          angle.value = 90;
          break;
        case 'stop':
          return; // Return early to avoid adding a line
      }

      // Verificar se a nova posição está dentro dos limites
      if (newX >= -containerWidth.value / 2 && newX <= containerWidth.value / 2 && newY >= -containerHeight.value / 2 && newY <= containerHeight.value / 2) {
        if (history.value.length > 0) {
          const lastPosition = history.value[history.value.length - 1];
          if (lastPosition.x === newX && lastPosition.y === newY) {
            // Remover a última linha se a nova posição for a mesma que a anterior
            lines.value.pop();
            history.value.pop();
          } else {
            // Adicionar nova linha e posição ao histórico
            lines.value.push({
              x1: previousX + containerWidth.value / 2,
              y1: previousY + containerHeight.value / 2,
              x2: newX + containerWidth.value / 2,
              y2: newY + containerHeight.value / 2,
            });
            history.value.push({ x: previousX, y: previousY });
          }
        } else {
          // Adicionar nova linha e posição ao histórico
          lines.value.push({
            x1: previousX + containerWidth.value / 2,
            y1: previousY + containerHeight.value / 2,
            x2: newX + containerWidth.value / 2,
            y2: newY + containerHeight.value / 2,
          });
          history.value.push({ x: previousX, y: previousY });
        }

        // Atualizar a posição
        x.value = newX;
        y.value = newY;
      }
    };

    const getOrientation = () => {
      switch (angle.value) {
        case 0:
          return 'Para Cima';
        case 180:
          return 'Para Baixo';
        case -90:
          return 'Para Esquerda';
        case 90:
          return 'Para Direita';
        default:
          return 'Parado';
      }
    };

    const increaseSpeed = () => {
      const newSpeed = Math.min(speed.value + 0.5, 5); // Aumentar a velocidade em 0.5, limite máximo 5
      socket.emit('speed_change', newSpeed);  // Emitir evento de mudança de velocidade
      speed.value = newSpeed;
      emit('speedChange', newSpeed);
    };

    const decreaseSpeed = () => {
      const newSpeed = Math.max(speed.value - 0.5, 0.5); // Diminuir a velocidade em 0.5, limite mínimo 0.5
      socket.emit('speed_change', newSpeed);  // Emitir evento de mudança de velocidade
      speed.value = newSpeed;
      emit('speedChange', newSpeed);
    };

    return {
      x,
      y,
      angle,
      lines,
      containerWidth,
      containerHeight,
      speed,
      updatePosition,
      getOrientation,
      updateContainerSize,
      increaseSpeed,
      decreaseSpeed,
    };
  },
  computed: {
    arrowStyle() {
      return {
        transform: `translate(${this.x}px, ${this.y}px) rotate(${this.angle}deg)`,
      };
    },
  },
};
</script>

<style scoped>
#arrow-container {
  width: calc(100% - 220px); /* Ajustar para acomodar o menu lateral */
  height: calc(100vh - 56px); /* Ajustar para acomodar a altura da barra de navegação */
  border: 1px solid black;
  position: absolute;
  top: 56px; /* Ajustar para abaixo da barra de navegação */
  left: 220px; /* Ajustar para a direita do menu lateral */
  overflow: hidden;
}

.arrow {
  width: 20px;
  height: 20px;
  background-color: red;
  clip-path: polygon(50% 0%, 0% 100%, 100% 100%);
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  transition: transform 0.2s ease;
}

.lines {
  position: absolute;
  top: 0;
  left: 0;
}

#speed-controls {
  display: flex;
  flex-direction: row;
  align-items: center;
  position: absolute;
  bottom: 20px;
  right: 20px;
  background-color: white;
  padding: 10px;
  border-radius: 5px;
  box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
}

#speed-controls button {
  width: 30px;
  height: 30px;
  font-size: 18px;
  cursor: pointer;
  margin: 0 5px;
}

#speed-controls span {
  margin: 0 10px;
  font-size: 18px;
}
</style>
