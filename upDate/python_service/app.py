from flask import Flask, request, jsonify
from twilio.rest import Client
from twilio.twiml.messaging_response import MessagingResponse
import os

app = Flask(__name__)

# Twilio credentials (load from environment variables or configure here)
ACCOUNT_SID = os.environ.get("TWILIO_ACCOUNT_SID", "YOUR_TWILIO_ACCOUNT_SID")
AUTH_TOKEN = os.environ.get("TWILIO_AUTH_TOKEN", "YOUR_TWILIO_AUTH_TOKEN")
TWILIO_WHATSAPP = "whatsapp:+14155238886"  # Twilio sandbox number

client = Client(ACCOUNT_SID, AUTH_TOKEN) if ACCOUNT_SID != "YOUR_TWILIO_ACCOUNT_SID" else None

# Health check
@app.route("/health")
def health():
    return {"ok": True}

# Send WhatsApp (REAL Twilio)
@app.route("/send_otp", methods=["POST"])   # 🔹 OTP send karva
def send_whatsapp():
    try:
        data = request.json or {}
        to = data.get("to")
        message_text = data.get("message", "Hello from Flask!")

        if client:
            message = client.messages.create(
                from_=TWILIO_WHATSAPP,
                body=message_text,
                to=to
            )
            sid = message.sid
        else:
            sid = "SIMULATED_SID_1234"

        return jsonify({
            "to": to,
            "message": message_text,
            "status": "sent",
            "sid": sid
        })
    except Exception as e:
        return jsonify({"error": str(e)}), 400


# Send Gmail (placeholder)
@app.route("/send_gmail", methods=["POST"])
def send_gmail():
    data = request.json or {}
    return jsonify({
        "status": "queued",
        "to": data.get("to"),
        "subject": data.get("subject")
    })

# WhatsApp webhook (receive messages from Twilio)
@app.route("/whatsapp", methods=["POST"])   # 🔹 Webhook
def whatsapp_reply():
    incoming_msg = request.values.get('Body', '').lower()
    resp = MessagingResponse()
    msg = resp.message()

    if "hello" in incoming_msg:
        msg.body("Hey! 👋 How are you?")
    elif "otp" in incoming_msg:
        msg.body("Your OTP is: 123456")
    else:
        msg.body("I got your message: " + incoming_msg)

    return str(resp)


if __name__ == "__main__":
    app.run(host="127.0.0.1", port=5000, debug=True)
