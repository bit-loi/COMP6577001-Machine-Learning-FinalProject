"""Shopmart ML Service Flask entrypoint."""

import os

from flask import Flask, jsonify
from flask_cors import CORS
from flask_restx import Api, Resource, fields

from models import iso_forest, churn_model
from routes_fraud import fraud_bp, run_prediction
from routes_churn import churn_bp
from routes_segmentation import segmentation_bp

app = Flask(__name__)
# Enable CORS so both React frontend and PHP backend can communicate with this API
CORS(app)

api = Api(
    app,
    title="Shopmart ML Service API",
    version="1.0.0",
    description="A Flask-based ML inference service for Shopmart predictions.",
    doc="/docs",
)

predict_ns = api.namespace("prediction", path="/predict", description="ML prediction endpoints")

predict_request = api.model("PredictionRequest", {
    "text": fields.String(
        required=False,
        description="Optional text input for clients that send text payloads.",
        example="produk ini bagus banget",
    ),
    "TransactionDT": fields.Float(required=False, example=86400),
    "TransactionAmt": fields.Float(required=False, example=150.50),
    "card1": fields.Integer(required=False, example=10486),
    "card2": fields.Integer(required=False, example=514),
    "card3": fields.Integer(required=False, example=150),
    "card4": fields.Integer(required=False, description="Encoded card network.", example=1),
    "card5": fields.Integer(required=False, example=226),
    "card6": fields.Integer(required=False, description="Encoded card type.", example=2),
    "ProductCD": fields.Integer(required=False, description="Encoded product code.", example=4),
    "addr1": fields.Float(required=False, example=315.0),
    "addr2": fields.Float(required=False, example=87.0),
    "P_emaildomain": fields.Integer(required=False, example=16),
    "R_emaildomain": fields.Integer(required=False, example=16),
})

# Register Blueprints
app.register_blueprint(fraud_bp)
app.register_blueprint(churn_bp)
app.register_blueprint(segmentation_bp)


@app.route('/', methods=['GET'])
def index():
    return jsonify({
        "status": "ok",
        "message": "Shopmart ML Service is running",
        "docs": "/docs",
        "health": "/health",
        "predict": "/predict"
    })


@app.route('/health', methods=['GET'])
def health_check():
    return jsonify({"status": "healthy"})


@predict_ns.route("")
class PredictResource(Resource):
    @predict_ns.expect(predict_request, validate=False)
    @predict_ns.response(200, "Successful prediction")
    @predict_ns.response(500, "Prediction failed")
    def post(self):
        payload, status_code = run_prediction(api.payload or {})
        return payload, status_code


if __name__ == '__main__':
    app.run(
        host="0.0.0.0",
        port=int(os.environ.get("PORT", 7860)),
        debug=os.environ.get('FLASK_DEBUG', '').lower() == 'true',
    )
