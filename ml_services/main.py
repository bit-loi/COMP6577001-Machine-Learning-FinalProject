"""Shopmart ML Service Flask entrypoint."""

import os

from flask import Flask, jsonify
from flask_cors import CORS
from flask_restx import Api, Resource, fields

from models import iso_forest, churn_model
from routes_fraud import run_prediction, simulate_predict
from routes_churn import batch_churn, predict_churn
from routes_segmentation import segment_user

app = Flask(__name__)
# Enable CORS so both React frontend and PHP backend can communicate with this API
CORS(app)

api = Api(
    app,
    title="Shopmart ML Service API",
    version="1.0.1",
    description="A Flask-based ML inference service for anomaly detection, churn prediction, batch scoring, and segmentation.",
    doc="/docs",
)

predict_ns = api.namespace("prediction", path="/predict", description="ML prediction endpoints")
churn_ns = api.namespace("churn", path="/predict/churn", description="Customer churn prediction endpoints")
batch_ns = api.namespace("batch", path="/batch", description="Batch scoring endpoints")
segment_ns = api.namespace("segmentation", path="/segment", description="Customer segmentation endpoints")

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

simulate_request = api.model("SimulationRequest", {
    "amount": fields.Float(required=False, description="Transaction amount to simulate.", example=120.0),
})

churn_request = api.model("ChurnPredictionRequest", {
    "orders_last_window": fields.Integer(required=True, example=5),
    "revenue_last_window": fields.Float(required=True, example=120.50),
    "recency_days": fields.Integer(required=True, example=15),
    "customer_age_days": fields.Integer(required=True, example=180),
    "country": fields.String(required=False, example="United Kingdom"),
})


@app.route('/', methods=['GET'])
def index():
    return jsonify({
        "status": "ok",
        "message": "Shopmart ML Service is running",
        "docs": "/docs",
        "health": "/health",
        "predict": "/predict",
        "simulate": "/predict/simulate",
        "churn": "/predict/churn",
        "batch_churn": "/batch/churn",
        "segment": "/segment",
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


@predict_ns.route("/simulate")
class SimulatePredictionResource(Resource):
    @predict_ns.expect(simulate_request, validate=False)
    @predict_ns.response(200, "Successful simulation prediction")
    @predict_ns.response(500, "Simulation failed")
    def post(self):
        return simulate_predict()


@churn_ns.route("")
class ChurnPredictionResource(Resource):
    @churn_ns.expect(churn_request, validate=False)
    @churn_ns.response(200, "Successful churn prediction")
    @churn_ns.response(500, "Churn model unavailable or prediction failed")
    def post(self):
        return predict_churn()


@batch_ns.route("/churn")
class BatchChurnResource(Resource):
    @batch_ns.response(200, "Batch scoring completed")
    @batch_ns.response(500, "Batch scoring failed")
    def post(self):
        return batch_churn()


@segment_ns.route("")
class SegmentResource(Resource):
    @segment_ns.response(200, "Successful customer segmentation")
    def post(self):
        return segment_user()


if __name__ == '__main__':
    app.run(
        host="0.0.0.0",
        port=int(os.environ.get("PORT", 7860)),
        debug=os.environ.get('FLASK_DEBUG', '').lower() == 'true',
    )
