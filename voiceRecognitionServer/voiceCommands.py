import os
import sys
import signal
import numpy as np
import pyaudio
import librosa
import tensorflow as tf
from keras.models import load_model
import requests
from flask import Flask
import time
import logging

# Definir a codificação padrão para UTF-8
sys.stdout.reconfigure(encoding='utf-8')
sys.stderr.reconfigure(encoding='utf-8')

app = Flask(__name__)

# Variável global para controlar a execução do script
running = True

def signal_handler(sig, frame):
    global running
    print('Stopping voice recognition...')
    running = False

# Registrar o manipulador de sinal para SIGINT e SIGTERM
signal.signal(signal.SIGINT, signal_handler)
signal.signal(signal.SIGTERM, signal_handler)

# Função para verificar silêncio
def is_silence(data, threshold):
    return np.max(np.abs(np.frombuffer(data, np.int16))) < threshold

# Função para preprocessar o áudio
def preprocess_audio(audio, sr, max_len=128):
    mel_spectrogram = librosa.feature.melspectrogram(y=audio, sr=sr, n_mels=128)
    mel_spectrogram = librosa.power_to_db(mel_spectrogram, ref=np.max)
    
    if mel_spectrogram.shape[1] > max_len:
        mel_spectrogram = mel_spectrogram[:, :max_len]
    else:
        pad_width = max_len - mel_spectrogram.shape[1]
        mel_spectrogram = np.pad(mel_spectrogram, ((0, 0), (0, pad_width)), mode='constant')
    
    return mel_spectrogram

# Carregar o modelo
model_path = 'models/voice_command_model_final.h5'
model = load_model(model_path)

# Mapeamento de índices para palavras
actions = ["frente", "tras", "esquerda", "direita", "mais", "menos", "parar", "rodar"]

# Função para prever a ação
def predict_action(audio, sr, confidence_threshold=0.7):
    mel_spectrogram = preprocess_audio(audio, sr)
    mel_spectrogram = np.expand_dims(mel_spectrogram, axis=0)
    mel_spectrogram = np.expand_dims(mel_spectrogram, axis=-1)
    
    prediction = model.predict(mel_spectrogram)
    predicted_index = np.argmax(prediction)
    predicted_action = actions[predicted_index]
    confidence = prediction[0][predicted_index]
    
    # Verificar se a confiança na previsão é alta o suficiente
    if confidence >= confidence_threshold:
        return predicted_action, confidence
    else:
        return None, confidence

# Função para enviar o comando ao servidor
def send_command_to_server(action, confidence):
    if action is None:
        print(f"Ação prevista inválida (confiança {confidence:.2f}). Nenhum comando enviado.")
        return
    
    url = 'http://localhost:3000/direcao'  # Updated IP address
    data = {'direcao': action, 'source': 'voice'}
    max_retries = 3
    timeout = 60  # Increased timeout to 60 seconds

    for attempt in range(max_retries):
        try:
            logging.info(f"Sending command to server: {data}, attempt: {attempt + 1}")
            response = requests.post(url, json=data, timeout=timeout)
            if response.status_code == 200:
                logging.info(f"Comando '{action}' enviado com sucesso!")
                return
            else:
                logging.error(f"Falha ao enviar comando: {response.status_code}")
        except requests.exceptions.Timeout:
            logging.error("Erro ao enviar comando: Timeout")
        except requests.exceptions.RequestException as e:
            logging.error(f"Erro ao enviar comando: {e}")
        time.sleep(5)  # Wait before retrying

    logging.error(f"Falha ao enviar comando '{action}' após {max_retries} tentativas")

# Configurações de gravação
chunk = 2048  # Increased buffer size
form = pyaudio.paInt16
channels = 1
rate = 44100
threshold = 8000  # Adjust as needed
silence_duration = 0.5
silence_chunks = int(silence_duration * rate / chunk)

# Inicializar PyAudio
audio = pyaudio.PyAudio()
stream = audio.open(format=form, channels=channels, rate=rate, input=True, frames_per_buffer=chunk)

print("Esperando detecção de som...")

frames = []
silent_chunks = 0
recording = False

def main_loop():
    global frames, silent_chunks, recording
    try:
        while running:
            try:
                data = stream.read(chunk, exception_on_overflow=False)
                if not is_silence(data, threshold):
                    if not recording:
                        print("Som detectado, gravando...")
                        recording = True
                        frames = []
                    frames.append(data)
                    silent_chunks = 0
                else:
                    silent_chunks += 1
                    if recording and silent_chunks > silence_chunks:
                        recording = False
                        print("Processando...")
                        audio_data = np.frombuffer(b''.join(frames), dtype=np.int16)
                        audio_data = audio_data.astype(np.float32) / 32768  # Normalizar
                        predicted_action, confidence = predict_action(audio_data, rate)
                        if predicted_action is not None:
                            print(f"Ação prevista: {predicted_action} (Confiança: {confidence:.2f})")
                            # Implement an additional check if the confidence level is high enough
                            if confidence >= 0.85:  # Higher threshold for critical actions
                                send_command_to_server(predicted_action, confidence)
                            else:
                                print(f"Ação prevista não é suficientemente confiável (Confiança: {confidence:.2f}), ignorando comando.")
                        else:
                            print(f"Ação prevista não é válida (Confiança: {confidence:.2f}), ignorando comando.")
                        print("Esperando detecção de som...")
                        frames = []
                        silent_chunks = 0
            except Exception as e:
                print(f"Erro durante a execução: {e}")
                break
    except KeyboardInterrupt:
        pass
    except Exception as e:
        print(f"Erro durante a execução: {e}")

    finally:
        print("Encerrando...")
        try:
            stream.stop_stream()
            stream.close()
        except Exception as e:
            print(f"Erro ao encerrar o stream: {e}")
        audio.terminate()

if __name__ == '__main__':
    from threading import Thread
    Thread(target=main_loop).start()
    app.run(port=5000)
