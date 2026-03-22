from flask import Flask, request, jsonify
from flask_cors import CORS
from flasgger import Swagger
import pickle
import numpy as np

app = Flask(__name__)
# Enable CORS so both React frontend and PHP backend can easily communicate with this API
CORS(app)

# Initialize Swagger UI
swagger_config = {
    "headers": [],
    "specs": [
        {
            "endpoint": 'apispec_1',
            "route": '/apispec_1.json',
            "rule_filter": lambda rule: True,
            "model_filter": lambda tag: True,
        }
    ],
    "static_url_path": "/flasgger_static",
    "swagger_ui": True,
    "specs_route": "/docs"  # Swagger will be available at http://localhost:5000/docs
}
swagger = Swagger(app, config=swagger_config, template={
    "info": {
        "title": "Bookstore Machine Learning API",
        "description": "API for Anomaly Detection and User Segmentation (COMP6577001 Final Project)",
        "version": "1.0.0"
    }
})

# Load the trained machine learning models
# Since the Isolation Forest was trained on numeric scaled data, we load both the model and the scaler
print("Loading Machine Learning Models...")
try:
    with open('isolation_forest.pkl', 'rb') as f:
        iso_forest = pickle.load(f)
    print("- Isolation Forest Loaded Successfully")

    with open('scaler.pkl', 'rb') as f:
        scaler = pickle.load(f)
    print("- Scaler Loaded Successfully")
    
except Exception as e:
    print(f"Error loading models: {e}")
    iso_forest = None
    scaler = None

@app.route('/health', methods=['GET'])
def health_check():
    return jsonify({
        "status": "operational",
        "service": "Bookstore ML Engine (Final Project)",
        "models_loaded": iso_forest is not None
    })

@app.route('/predict', methods=['POST'])
def predict():
    """
    Endpoint for Anomaly Detection (Fraud)
    ---
    tags:
      - ML Anomaly Detector
    consumes:
      - application/json
    produces:
      - application/json
    parameters:
      - in: body
        name: body
        required: true
        description: JSON containing the IEEE-CIS transaction fields
        schema:
          type: object
          properties:
            TransactionDT:
              type: number
              example: 86400
            TransactionAmt:
              type: number
              example: 150.50
            card1:
              type: integer
              example: 10486
            card2:
              type: integer
              example: 514
            card3:
              type: integer
              example: 150
            card4:
              type: integer
              description: "Encoded (e.g., 1=visa, 2=mastercard)"
              example: 1
            card5:
              type: integer
              example: 226
            card6:
              type: integer
              description: "Encoded (e.g., 1=credit, 2=debit)"
              example: 2
            ProductCD:
              type: integer
              example: 4
            addr1:
              type: number
              example: 315.0
            addr2:
              type: number
              example: 87.0
            P_emaildomain:
              type: integer
              example: 16
            R_emaildomain:
              type: integer
              example: 16
    responses:
      200:
        description: Successful prediction
    """
    if iso_forest is None or scaler is None:
        return jsonify({'error': 'Machine learning models are not loaded'}), 500

    try:
        data = request.json
        
        # 1. Parse Key Transaction Features
        tx_dt = float(data.get('TransactionDT', 0))
        tx_amt = float(data.get('TransactionAmt', 0.0))
        card1 = float(data.get('card1', 0))
        card2 = float(data.get('card2', 0))
        card3 = float(data.get('card3', 0))
        card4 = float(data.get('card4', 0))
        card5 = float(data.get('card5', 0))
        card6 = float(data.get('card6', 0))
        prod_cd = float(data.get('ProductCD', 0))
        addr1 = float(data.get('addr1', 0))
        addr2 = float(data.get('addr2', 0))
        p_email = float(data.get('P_emaildomain', 0))
        r_email = float(data.get('R_emaildomain', 0))

        # Assemble the structured elements into the starting features list
        features_list = [
            tx_dt, tx_amt, card1, card2, card3, 
            card4, card5, card6, prod_cd, addr1, 
            addr2, p_email, r_email
        ]

        # 2. Handle V-Features (Padding with Defaults/Zeros)
        # We fill the remaining specific V-columns (~210 remaining up to 224 total) with 0.0
        expected_features = getattr(iso_forest, 'n_features_in_', 224)
        
        if len(features_list) < expected_features:
            features_list.extend([0.0] * (expected_features - len(features_list)))
        elif len(features_list) > expected_features:
            features_list = features_list[:expected_features]

        # 3. Transform data using the pre-fitted standard scaler
        features_array = np.array(features_list).reshape(1, -1)
        scaled_features = scaler.transform(features_array)

        # 1 means Normal transaction, -1 means Anomaly / Outlier
        raw_pred = iso_forest.predict(scaled_features)
        
        # Get score via score_samples (returns negative anomaly score)
        raw_score = iso_forest.score_samples(scaled_features)
        
        # Normalize score (using negative so higher = more abnormal)
        anomaly_score = float(-raw_score[0])

        # Explicit Python casting
        prediction_val = int(raw_pred[0])
        is_anomaly = bool(prediction_val == -1)

        return jsonify({
            'prediction': [prediction_val],
            'is_anomaly': is_anomaly,
            'anomaly_score': anomaly_score,
            'processed_features_count': len(features_list)
        })

    except Exception as e:
        return jsonify({'error': 'Internal server error processing the prediction', 'details': str(e)}), 500

@app.route('/predict/simulate', methods=['POST'])
def simulate_predict():
    """
    Helper Endpoint strictly for the Dashboard UI simulation.
    Since we don't have all 321 IEEE-CIS features from the UI, 
    we generate a dummy valid array to ping the real model and return the result.
    Expects JSON: {"amount": float, "qty": int}
    """
    if iso_forest is None or scaler is None:
         return jsonify({'error': 'Models not loaded'}), 500

    try:
        data = request.json
        amount = data.get('amount', 50.0)
        
        # We need 321 features. Let's create an array of zeros
        # and inject some values to simulate a transaction footprint
        dummy_features = np.zeros(321)
        
        # For the sake of the simulation, we manipulate random features to trigger isolation
        if amount > 1000:
            # Inject extreme values to force an anomaly (-1)
            dummy_features[0] = amount * 10
            dummy_features[15] = 9999
            dummy_features[120] = -5000
        else:
            # Normal distribution of tiny values
            dummy_features = np.random.normal(0, 0.1, 321)

        scaled_dummy = scaler.transform([dummy_features])
        prediction = iso_forest.predict(scaled_dummy)
        score = iso_forest.decision_function(scaled_dummy)

        return jsonify({
            'prediction': int(prediction[0]),
            'is_anomaly': True if prediction[0] == -1 else False,
            'anomaly_score': float(score[0])
        })
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/segment', methods=['POST'])
def segment_user():
    """
    Endpoint for Clustering-based User Segmentation
    Placeholder for K-Means logic returning 'Whale', 'Regular', 'Bargain Hunter'
    """
    return jsonify({
        'segment': 'Regular Readers',
        'confidence': 0.85
    })

if __name__ == '__main__':
    # Start the Flask development server on port 5000
    app.run(host='0.0.0.0', port=5000, debug=True)
