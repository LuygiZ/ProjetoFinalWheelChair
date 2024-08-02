<template>
  <div id="joystick-wrapper">
    <div id="joystick-container"></div>
  </div>
</template>

<script>
import nipplejs from 'nipplejs';
import axios from 'axios';

export default {
  name: 'JoystickControl',
  data() {
    return {
      joystick: null,
      activeKey: null
    };
  },
  mounted() {
    this.initializeJoystick();
    window.addEventListener('keydown', this.handleKeyDown);
    window.addEventListener('keyup', this.handleKeyUp);
  },
  beforeUnmount() {
    window.removeEventListener('keydown', this.handleKeyDown);
    window.removeEventListener('keyup', this.handleKeyUp);
  },
  methods: {
    initializeJoystick() {
      const joystickContainer = document.getElementById('joystick-container');
      const options = {
        zone: joystickContainer,
        mode: 'static',
        position: { left: '50%', top: '50%' },
        color: 'black',
      };

      this.joystick = nipplejs.create(options);

      this.joystick.on('move', (evt, data) => {
        const distance = Math.min(data.distance, 50); // Limitar a distância do movimento
        const angle = data.angle.radian;
        const x = Math.cos(angle) * distance;
        const y = -Math.sin(angle) * distance; // Inverter o valor de y

        // Aplicar transform diretamente no joystick usando nipplejs
        const nippleElement = this.joystick[0].ui.front;
        nippleElement.style.transform = `translate(${x}px, ${y}px)`;

        const direction = data.direction;
        if (direction) {
          let command = null;
          switch (direction.angle) {
            case 'up':
              command = 'forward';
              break;
            case 'down':
              command = 'backward';
              break;
            case 'left':
              command = 'left';
              break;
            case 'right':
              command = 'right';
              break;
          }
          if (command) {
            this.sendDirectionToServer({ command });
            this.$emit('command', command); // Emitir evento com o comando
          }
        }
      });

      this.joystick.on('end', () => {
        this.sendDirectionToServer({ command: 'stop' });
        this.$emit('command', 'stop'); // Emitir evento de parada
        this.resetJoystickVisual();
      });
    },
    sendDirectionToServer(data) {
      console.log('Sending data:', data); // Log de depuração
      axios.post('http://192.168.50.236:3000/direcao', data)
        .then(response => {
          console.log('Response from server:', response.data);
        })
        .catch(error => {
          console.error('Error sending data:', error);
        });
    },
    resetJoystickVisual() {
      // Resetar a posição do joystick ao centro
      const nippleElement = this.joystick[0].ui.front;
      nippleElement.style.transform = 'translate(0px, 0px)';
    },
    moveJoystickVisual(direction) {
      const nippleElement = this.joystick[0].ui.front;
      switch (direction) {
        case 'forward':
          nippleElement.style.transform = 'translateY(-50px)';
          break;
        case 'backward':
          nippleElement.style.transform = 'translateY(50px)';
          break;
        case 'left':
          nippleElement.style.transform = 'translateX(-50px)';
          break;
        case 'right':
          nippleElement.style.transform = 'translateX(50px)';
          break;
      }
    },
    handleKeyDown(event) {
      let command = null;
      this.activeKey = event.key;
      switch (event.key) {
        case 'ArrowUp':
          command = 'forward';
          this.moveJoystickVisual('forward');
          break;
        case 'ArrowDown':
          command = 'backward';
          this.moveJoystickVisual('backward');
          break;
        case 'ArrowLeft':
          command = 'left';
          this.moveJoystickVisual('left');
          break;
        case 'ArrowRight':
          command = 'right';
          this.moveJoystickVisual('right');
          break;
      }
      if (command) {
        this.sendDirectionToServer({ command });
        this.$emit('command', command); // Emitir evento com o comando
      }
    },
    handleKeyUp() {
      this.sendDirectionToServer({ command: 'stop' });
      this.$emit('command', 'stop'); // Emitir evento "parar"
      this.resetJoystickVisual();
      this.activeKey = null;
    },
  },
};
</script>

<style scoped>
#joystick-wrapper {
  position: absolute;
  bottom: 200px;
  right: 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
}

#joystick-container {
  width: 200px;
  height: 200px;
  position: relative;
  margin-bottom: 10px;
}

#keys-container {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.row {
  display: flex;
  justify-content: center;
  margin-bottom: 5px;
}

.key {
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
  /* Adiciona um cursor de ponteiro para indicar que é clicável */
}

.key.active {
  background-color: gray;
  color: white;
}
</style>