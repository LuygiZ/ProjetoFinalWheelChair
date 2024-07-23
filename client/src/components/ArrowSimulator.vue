<template>
    <div id="arrow-container">
      <svg class="lines" :width="containerWidth" :height="containerHeight">
        <line v-for="(line, index) in lines" :key="index" :x1="line.x1" :y1="line.y1" :x2="line.x2" :y2="line.y2" stroke="blue" stroke-width="4"/>
      </svg>
      <div class="arrow" :style="arrowStyle"></div>
    </div>
  </template>
  
  <script>
  export default {
    name: 'ArrowSimulator',
    props: {
      speed: {
        type: Number,
        default: 1
      }
    },
    data() {
      return {
        x: 0,
        y: 0,
        angle: 0,
        lines: [],
        history: [], // Histórico das posições
        containerWidth: 0,
        containerHeight: 0,
      };
    },
    computed: {
      arrowStyle() {
        return {
          transform: `translate(${this.x}px, ${this.y}px) rotate(${this.angle}deg)`,
        };
      },
    },
    mounted() {
      this.updateContainerSize();
      window.addEventListener('resize', this.updateContainerSize);
    },
    beforeUnmount() {
      window.removeEventListener('resize', this.updateContainerSize);
    },
    watch: {
      speed(newSpeed) {
        console.log(`Speed changed to: ${newSpeed}`); // Para depuração
      }
    },
    methods: {
      updateContainerSize() {
        const container = this.$el;
        this.containerWidth = container.clientWidth;
        this.containerHeight = container.clientHeight;
      },
      updatePosition(command) {
        const step = 20 * this.speed; // Multiplicar o passo pela velocidade atual
        const previousX = this.x;
        const previousY = this.y;
        let newX = this.x;
        let newY = this.y;
  
        switch (command) {
          case 'up':
            newY -= step;
            this.angle = 0;
            break;
          case 'down':
            newY += step;
            this.angle = 180;
            break;
          case 'left':
            newX -= step;
            this.angle = -90;
            break;
          case 'right':
            newX += step;
            this.angle = 90;
            break;
          case 'stop':
            // No action needed for stop command
            return; // Return early to avoid adding a line
        }
  
        // Verificar se a nova posição está dentro dos limites
        if (newX >= -this.containerWidth / 2 && newX <= this.containerWidth / 2 && newY >= -this.containerHeight / 2 && newY <= this.containerHeight / 2) {
          if (this.history.length > 0) {
            const lastPosition = this.history[this.history.length - 1];
            if (lastPosition.x === newX && lastPosition.y === newY) {
              // Remover a última linha se a nova posição for a mesma que a anterior
              this.lines.pop();
              this.history.pop();
            } else {
              // Adicionar nova linha e posição ao histórico
              this.lines.push({
                x1: previousX + this.containerWidth / 2,
                y1: previousY + this.containerHeight / 2,
                x2: newX + this.containerWidth / 2,
                y2: newY + this.containerHeight / 2,
              });
              this.history.push({ x: previousX, y: previousY });
            }
          } else {
            // Adicionar nova linha e posição ao histórico
            this.lines.push({
              x1: previousX + this.containerWidth / 2,
              y1: previousY + this.containerHeight / 2,
              x2: newX + this.containerWidth / 2,
              y2: newY + this.containerHeight / 2,
            });
            this.history.push({ x: previousX, y: previousY });
          }
  
          // Atualizar a posição
          this.x = newX;
          this.y = newY;
        }
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
  </style>
  