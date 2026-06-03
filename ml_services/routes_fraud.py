"""
routes_fraud.py — Fraud / Anomaly Detection endpoints

Routes:
    POST /predict          — Manual single-transaction fraud check
    POST /predict/simulate — Live simulation engine (dashboard widget)
"""

import numpy as np
from flask import Blueprint, request, jsonify
from models import iso_forest, scaler

fraud_bp = Blueprint('fraud', __name__)

# 13 features the retrained Isolation Forest was fitted on — order must match training
FEATURE_ORDER = [
    'TransactionDT', 'TransactionAmt',
    'card1', 'card2', 'card3', 'card4', 'card5', 'card6',
    'ProductCD',
    'addr1', 'addr2',
    'P_emaildomain', 'R_emaildomain',
]


def _simulation_baseline(n_feat: int, amount: float) -> np.ndarray:
    """Build a stable low-risk synthetic vector without pseudo-random generation."""
    vec = np.zeros(n_feat)
    if n_feat > 0:
        vec[0] = 86400 + min(max(amount, 0.0), 1000.0)
    if n_feat > 1:
        vec[1] = amount
    return vec


def _build_features(data: dict) -> np.ndarray:
    """Parse request JSON into a feature vector aligned with the trained model."""
    vec = [float(data.get(col, 0)) for col in FEATURE_ORDER]

    # Stay backward-compatible if the loaded model has more features than 13
    expected = getattr(iso_forest, 'n_features_in_', len(vec))
    if len(vec) < expected:
        vec.extend([0.0] * (expected - len(vec)))
    elif len(vec) > expected:
        vec = vec[:expected]

    return np.array(vec).reshape(1, -1)


def run_prediction(data: dict):
    """Run the existing fraud/anomaly model inference for a JSON payload."""
    if iso_forest is None or scaler is None:
        return {'error': 'Fraud models are not loaded'}, 500

    try:
        features_array  = _build_features(data or {})
        scaled          = scaler.transform(features_array)

        raw_pred        = iso_forest.predict(scaled)
        anomaly_score   = float(-iso_forest.score_samples(scaled)[0])
        prediction_val  = int(raw_pred[0])

        return {
            'prediction':              [prediction_val],
            'is_anomaly':              prediction_val == -1,
            'anomaly_score':           anomaly_score,
            'processed_features_count': features_array.shape[1],
        }, 200
    except Exception as e:
        return {'error': 'Prediction failed', 'details': str(e)}, 500


def predict():
    """Backward-compatible callable for the main /predict endpoint."""
    payload, status_code = run_prediction(request.json)
    return jsonify(payload), status_code


@fraud_bp.route('/predict/simulate', methods=['POST'])
def simulate_predict():
    """
    Helper endpoint for the Dashboard live simulation widget.
    Accepts {"amount": float} and returns a fraud prediction.
    ---
    tags:
      - ML Anomaly Detector
    parameters:
      - in: body
        name: body
        schema:
          type: object
          properties:
            amount:
              type: number
              example: 120.0
    responses:
      200:
        description: Successful simulation prediction
    """
    if iso_forest is None or scaler is None:
        return jsonify({'error': 'Fraud models are not loaded'}), 500

    try:
        amount = float((request.json or {}).get('amount', 50.0))

        # Build a synthetic 13-feature vector for the simulation
        n_feat = getattr(iso_forest, 'n_features_in_', 13)
        if amount > 1000:
            # Force obvious anomaly signals
            vec = np.zeros(n_feat)
            vec[0] = amount * 10   # TransactionDT extremity
            vec[1] = amount        # TransactionAmt
        else:
            vec = _simulation_baseline(n_feat, amount)

        scaled     = scaler.transform(vec.reshape(1, -1))
        prediction = iso_forest.predict(scaled)
        score      = float(-iso_forest.score_samples(scaled)[0])

        return jsonify({
            'prediction':  int(prediction[0]),
            'is_anomaly':  prediction[0] == -1,
            'anomaly_score': score,
        })
    except Exception as e:
        return jsonify({'error': str(e)}), 500
