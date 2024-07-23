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
        activeKey: null,
        speed: 1, // Velocidade inicial
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
                command = 'up';
                break;
              case 'down':
                command = 'down';
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
        axios.post('http://localhost:3000/direcao', data)
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
          case 'up':
            nippleElement.style.transform = 'translateY(-50px)';
            break;
          case 'down':
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
            command = 'up';
            this.moveJoystickVisual('up');
            break;
          case 'ArrowDown':
            command = 'down';
            this.moveJoystickVisual('down');
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
        this.$emit('command', 'stop'); // Emitir evento de parada
        this.resetJoystickVisual();
        this.activeKey = null;
      },
      increaseSpeed() {
        this.speed = Math.min(this.speed + 0.5, 5); // Aumentar a velocidade em 0.5, limite máximo 5
        this.$emit('speedChange', this.speed); // Emitir evento de mudança de velocidade
      },
      decreaseSpeed() {
        this.speed = Math.max(this.speed - 0.5, 0.5); // Diminuir a velocidade em 0.5, limite mínimo 0.5
        this.$emit('speedChange', this.speed); // Emitir evento de mudança de velocidade
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
    cursor: pointer; /* Adiciona um cursor de ponteiro para indicar que é clicável */
  }
  
  .key.active {
    background-color: gray;
    color: white;
  }
  
  #speed-controls {
    display: flex;
    align-items: center;
    margin-top: 10px;
  }
  
  #speed-controls button {
    width: 30px;
    height: 30px;
    font-size: 18px;
    cursor: pointer; /* Adiciona um cursor de ponteiro para indicar que é clicável */
  }
  
  #speed-controls span {
    margin: 0 10px;
    font-size: 18px;
  }
  </style>
  