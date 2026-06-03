"""
routes_churn.py — Churn Prediction endpoints

Routes:
    POST /predict/churn - Single-customer churn prediction
    POST /batch/churn   - Trigger batch scoring script
"""

import io
from contextlib import redirect_stderr, redirect_stdout
import pandas as pd
from flask import Blueprint, request, jsonify
from churn_batch_scoring import DEFAULT_INPUT, DEFAULT_OUTPUT, run_batch_scoring
from models import churn_model

churn_bp = Blueprint('churn', __name__)

@churn_bp.route('/predict/churn', methods=['POST'])
def predict_churn():
    """
    Endpoint for single-customer Churn Prediction
    ---
    tags:
      - ML Churn Predictor
    consumes:
      - application/json
    parameters:
      - in: body
        name: body
        required: true
        schema:
          type: object
          properties:
            orders_last_window:
              type: integer
              example: 5
            revenue_last_window:
              type: number
              example: 120.50
            recency_days:
              type: integer
              example: 15
            customer_age_days:
              type: integer
              example: 180
    responses:
      200:
        description: Successful prediction
    """
    if churn_model is None:
        return jsonify({'error': 'Churn model is not loaded'}), 500

    try:
        data = request.json
        
        # Build dictionary with default inputs
        input_data = {
            'orders_last_window': [float(data.get("orders_last_window", 0))],
            'revenue_last_window': [float(data.get("revenue_last_window", 0.0))],
            'recency_days': [float(data.get("recency_days", 0))],
            'customer_age_days': [float(data.get("customer_age_days", 0))]
        }

        # Pad with other required feature columns
        model_features = list(churn_model.feature_names_in_)
        for col in model_features:
            if col not in input_data:
                if col == 'country':
                    input_data[col] = [data.get("country", "United Kingdom")]
                else:
                    input_data[col] = [0.0]

        df = pd.DataFrame(input_data)
        # Ensure correct column ordering
        df = df[model_features]

        proba = float(churn_model.predict_proba(df)[0, 1])
        
        # Risk logic
        if proba >= 0.75:
            risk_level = "Critical"
            action = "Send urgent retention offer"
        elif proba >= 0.45:
            risk_level = "At Risk"
            action = "Send personalized promo"
        else:
            risk_level = "Loyal"
            action = "Maintain normal engagement"

        return jsonify({
            'predicted_churn_probability': proba,
            'predicted_churn': 1 if proba >= 0.5 else 0,
            'risk_level': risk_level,
            'recommended_action': action
        })

    except Exception as e:
        return jsonify({'error': 'Internal server error processing churn prediction', 'details': str(e)}), 500


@churn_bp.route('/batch/churn', methods=['POST'])
def batch_churn():
    """
    Endpoint to trigger the Batch Churn Scoring script
    """
    try:
        output = io.StringIO()
        with redirect_stdout(output), redirect_stderr(output):
            run_batch_scoring(DEFAULT_INPUT, DEFAULT_OUTPUT)

        return jsonify({
            "status": "success",
            "message": "Batch scoring completed successfully",
            "output": output.getvalue()
        })
    except SystemExit as e:
        output_text = output.getvalue() if 'output' in locals() else ''
        if e.code == 0:
            return jsonify({
                "status": "success",
                "message": "Batch scoring completed successfully",
                "output": output_text
            })

        return jsonify({
            "status": "error",
            "message": "Batch scoring failed",
            "error": output_text
        }), 500
    except Exception as e:
        return jsonify({'error': str(e)}), 500
