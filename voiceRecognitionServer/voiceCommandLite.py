import os
import sys
import signal
import numpy as np
import pyaudio
import librosa
import requests
from tensorflow.lite.python.interpreter import Interpreter


# Definir a codificação padrão para UTF-8
sys.stdout.reconfigure(encoding='utf-8')
sys.stderr.reconfigure(encoding='utf-8')

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

# Carregar o modelo TensorFlow Lite
model_path = 'models/voice_command_modelV2.tflite'
interpreter = Interpreter(model_path)
interpreter.allocate_tensors()
input_details = interpreter.get_input_details()
output_details = interpreter.get_output_details()

# Mapeamento de índices das palavras
actions = ["frente", "tras", "esquerda", "direita", "mais", "menos", "parar", "rodar"]

# Função para fazer previsão dos comandos com base no modelo
def predict_action(audio, sr):
    mel_spectrogram = preprocess_audio(audio, sr)
    mel_spectrogram = np.expand_dims(mel_spectrogram, axis=0)
    mel_spectrogram = np.expand_dims(mel_spectrogram, axis=-1).astype(np.float32)
    
    interpreter.set_tensor(input_details[0]['index'], mel_spectrogram)
    interpreter.invoke()
    prediction = interpreter.get_tensor(output_details[0]['index'])
    predicted_index = np.argmax(prediction)
    predicted_action = actions[predicted_index]
    
    return predicted_action

# Função para enviar o comando ao servidor
def send_command_to_server(action):
    url = 'http://localhost:3000/direcao'  # URL do servidor (substituir pelo correto)
    data = {'direcao': action, 'source': 'voice'}
    try:
        response = requests.post(url, json=data)
        if response.status_code == 200:
            print(f"Comando '{action}' enviado com sucesso!")
        else:
            print(f"Falha ao enviar comando: {response.status_code}")
    except Exception as e:
        print(f"Erro ao enviar comando: {e}")

# Configurações de gravação
chunk = 1024
form = pyaudio.paInt16
channels = 1
rate = 44100
threshold = 500  # Ajustar conforme necessário, se detetar demasiado som deve se aumentar
silence_duration = 0.5
silence_chunks = int(silence_duration * rate / chunk)

# Inicializar PyAudio
audio = pyaudio.PyAudio()
stream = audio.open(format=form, channels=channels, rate=rate, input=True, frames_per_buffer=chunk)

print("Esperando detecção de som...")

frames = []
silent_chunks = 0
recording = False

try:
    while running:
        data = stream.read(chunk)
        if not is_silence(data, threshold):
            if not recording:
                print("Som detectado, gravando...")
                recording = True
                frames = []
            frames.append(data)
            silent_chunks = 0
        elif recording:
            frames.append(data)
            silent_chunks += 1
            if silent_chunks > silence_chunks:
                recording = False
                print("Processando...")
                audio_data = np.frombuffer(b''.join(frames), dtype=np.int16)
                audio_data = audio_data.astype(np.float32) / 32768  # Normalizar
                predicted_action = predict_action(audio_data, rate)
                print(f"Ação prevista: {predicted_action}")
                send_command_to_server(predicted_action)
                print("Esperando detecção de som...")

except KeyboardInterrupt:
    pass
except Exception as e:
    print(f"Erro durante a execução: {e}")

finally:
    print("Encerrando...")
    stream.stop_stream()
    stream.close()
    audio.terminate()
