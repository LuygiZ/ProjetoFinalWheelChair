<template>
  <div class="content">
    <h1>Welcome to Smart Wheelchairs</h1>
    <p>
      Discover the future of mobility with our advanced smart wheelchairs. Designed to provide independence and convenience for users with mobility challenges.
    </p>
    <div class="features">
      <div class="feature">
        <h2>Advanced Navigation</h2>
        <p>
          Our smart wheelchairs are equipped with advanced navigation systems that allow for easy and intuitive control.
        </p>
      </div>
      <div class="feature">
        <h2>Voice Control</h2>
        <p>
          Use voice commands to control your wheelchair, giving you more freedom and flexibility.
        </p>
      </div>
      <div class="feature">
        <h2>Real-time Monitoring</h2>
        <p>
          Monitor your health and wheelchair status in real-time, ensuring you always stay informed.
        </p>
      </div>
    </div>
  </div>
  <footer class="footer">
    <div class="footer-content">
      <p>&copy; 2024 Smart Wheelchairs. All rights reserved.</p>
      <p>Contact us: info@smartwheelchairs.com | +1 234 567 890</p>
      <div class="social-media">
        <a href="https://facebook.com" target="_blank">Facebook</a>
        <a href="https://twitter.com" target="_blank">Twitter</a>
        <a href="https://instagram.com" target="_blank">Instagram</a>
      </div>
    </div>
  </footer>
</template>

<style>
body, html {
  margin: 0;
  padding: 0;
  width: 100%;
  height: 100%;
  overflow-x: hidden;
}

.content {
  min-height: calc(100vh - 100px); /* Subtrair a altura do rodapé */
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  background-color: white;
  padding: 20px;
  text-align: center;
  box-sizing: border-box;
}

h1 {
  font-size: 2.5em;
  margin-bottom: 20px;
  color: #333;
}

p {
  font-size: 1.2em;
  color: #555;
  line-height: 1.6;
}

.features {
  margin-top: 40px;
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
}

.feature {
  flex: 1 1 300px;
  margin: 10px;
  padding: 20px;
  background-color: #fff;
  border-radius: 10px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  box-sizing: border-box;
}

.feature h2 {
  font-size: 1.5em;
  margin-bottom: 10px;
  color: #333;
}

.feature p {
  font-size: 1em;
  color: #666;
}

.footer {
  background-color: #333;
  color: #fff;
  text-align: center;
  padding: 20px;
  position: relative;
  bottom: 0;
  width: 100%;
  box-sizing: border-box;
}

.footer-content {
  max-width: 800px;
  margin: 0 auto;
}

.footer p {
  margin: 5px 0;
  color: #fff;
}

.social-media a {
  color: #fff;
  margin: 0 10px;
  text-decoration: none;
}

.social-media a:hover {
  text-decoration: underline;
}
</style>
