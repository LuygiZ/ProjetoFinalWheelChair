import os
import numpy as np
import pyaudio
import wave

def cria_pastas():
    for i in range(8):
        pasta = os.path.join("samples", str(i))
        os.makedirs(pasta, exist_ok=True)

def is_silence(data, threshold):
    # Verificar se o nível de som é menor que o threshold
    return np.max(np.abs(np.frombuffer(data, np.int16))) < threshold

def grava_audio(filepath, threshold=500, chunk=1024, form=pyaudio.paInt16, channels=1, rate=44100, silence_duration=0.5):
    audio = pyaudio.PyAudio()
    stream = audio.open(format=form, channels=channels, rate=rate, input=True, frames_per_buffer=chunk)
    
    print("Esperando detecção de som...")
    frames = []
    gravando = False
    silence_chunks = int(silence_duration * rate / chunk)
    silent_chunks = 0

    try:
        while True:
            data = stream.read(chunk)
            if not is_silence(data, threshold):
                gravando = True
                silent_chunks = 0
                frames.append(data)
                print("Gravando...")
            elif gravando:
                frames.append(data)
                silent_chunks += 1
                if silent_chunks > silence_chunks:
                    break
    except KeyboardInterrupt:
        pass

    print("Gravação concluída.")
    
    stream.stop_stream()
    stream.close()
    audio.terminate()
    
    with wave.open(filepath, 'wb') as wf:
        wf.setnchannels(channels)
        wf.setsampwidth(audio.get_sample_size(form))
        wf.setframerate(rate)
        wf.writeframes(b''.join(frames))

def main():
    cria_pastas()
    acoes = ["frente", "tras", "esquerda", "direita", "mais", "menos", "parar"]

    while True:
        print("\nMenu:")
        for i, acao in enumerate(acoes):
            print(f"{i}: {acao}")
        pasta = input("Selecione a pasta (0-6) ou 'sair' para encerrar: ")
        if pasta.lower() == 'sair':
            break
        if pasta.isdigit() and 0 <= int(pasta) <= 7:
            pasta = int(pasta)
            while True:
                num_arquivos = len([name for name in os.listdir(os.path.join("samples", str(pasta))) if os.path.isfile(os.path.join("samples", str(pasta), name))])
                audio_filepath = os.path.join("samples", str(pasta), f"{pasta}_{num_arquivos + 1}.wav")
                grava_audio(audio_filepath)
                print(f"Áudio gravado e salvo como {audio_filepath}")
                comando = input("Digite 'sair' para voltar ao menu ou pressione Enter para continuar gravando: ")
                if comando.lower() == 'sair':
                    break
        else:
            print("Entrada inválida. Por favor, selecione um número entre 0 e 7 ou 'sair' para encerrar.")

if __name__ == "__main__":
    main()
