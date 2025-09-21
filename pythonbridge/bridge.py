from flask import Flask, request, jsonify
import serial

app = Flask(__name__)

# Change COM port accordingly (Windows: COM3, Linux: /dev/ttyUSB0)
try:
    arduino = serial.Serial("COM3", 9600, timeout=1)
    print("Arduino connected on COM3")
except:
    arduino = None
    print("Arduino not found - running in simulation mode")

@app.route("/send", methods=["POST"])
def send_command():
    cmd = request.form.get("cmd", "")
    if cmd:
        if arduino:
            arduino.write((cmd + "\n").encode())
            return jsonify({"status": "sent", "command": cmd, "mode": "arduino"})
        else:
            # Simulation mode
            print(f"SIMULATION: {cmd} command")
            return jsonify({"status": "sent", "command": cmd, "mode": "simulation"})
    return jsonify({"status": "error", "message": "no command"})

if __name__ == "__main__":
    app.run(host="127.0.0.1", port=5000)
