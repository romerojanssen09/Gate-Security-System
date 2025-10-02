from flask import Flask, request, jsonify
import serial

app = Flask(__name__)

# Change COM port accordingly (Windows: COM3, Linux: /dev/ttyUSB0)
try:
    arduino = serial.Serial("COM4", 9600, timeout=1)
    print("Arduino connected on COM4")
except:
    arduino = None
    print("Arduino not found - running in simulation mode")

@app.route("/send", methods=["POST", "OPTIONS"])
def send_command():
    # Handle preflight OPTIONS request
    if request.method == "OPTIONS":
        response = jsonify({"status": "ok"})
        response.headers.add("Access-Control-Allow-Origin", "*")
        response.headers.add("Access-Control-Allow-Headers", "Content-Type")
        response.headers.add("Access-Control-Allow-Methods", "POST, OPTIONS")
        return response
    
    cmd = request.form.get("cmd", "")
    if cmd:
        if arduino:
            arduino.write((cmd + "\n").encode())
            print(f"Arduino command sent: {cmd}")
            response = jsonify({"status": "sent", "command": cmd, "mode": "arduino"})
        else:
            # Simulation mode
            if cmd == "manualopen":
                print("SIMULATION: Manual gate opening command received")
            elif cmd == "manualclose":
                print("SIMULATION: Manual gate closing command received")
            elif cmd == "open":
                print("SIMULATION: Automatic gate opening (RFID granted)")
            elif cmd == "close":
                print("SIMULATION: Gate closing command")
            elif cmd == "unauthorized":
                print("SIMULATION: Unauthorized access attempt")
            else:
                print(f"SIMULATION: Unknown command - {cmd}")
            
            response = jsonify({"status": "sent", "command": cmd, "mode": "simulation"})
    else:
        response = jsonify({"status": "error", "message": "no command"})
    
    # Add CORS headers to response
    response.headers.add("Access-Control-Allow-Origin", "*")
    response.headers.add("Access-Control-Allow-Headers", "Content-Type")
    response.headers.add("Access-Control-Allow-Methods", "POST, OPTIONS")
    return response

if __name__ == "__main__":
    app.run(host="127.0.0.1", port=5000)
