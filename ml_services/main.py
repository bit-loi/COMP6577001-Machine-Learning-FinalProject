"""
main.py — Shopmart Machine Learning API Entrypoint
==================================================
Consolidates the modularized blueprints (fraud, churn, and segmentation)
into a single Flask + Swagger application.
"""

from flask import Flask, jsonify
from flask_cors import CORS
from flasgger import Swagger

from models import iso_forest, churn_model
from routes_fraud import fraud_bp
from routes_churn import churn_bp
from routes_segmentation import segmentation_bp

app = Flask(__name__)
# Enable CORS so both React frontend and PHP backend can communicate with this API
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
        "title": "Shopmart Machine Learning API",
        "description": "API for Anomaly Detection and User Segmentation (COMP6577001 Final Project)",
        "version": "1.0.0"
    }
})

# Register Blueprints
app.register_blueprint(fraud_bp)
app.register_blueprint(churn_bp)
app.register_blueprint(segmentation_bp)

@app.route('/health', methods=['GET'])
def health_check():
    return jsonify({
        "status": "operational",
        "service": "Shopmart ML Engine (Final Project)",
        "fraud_model_loaded": iso_forest is not None,
        "churn_model_loaded": churn_model is not None
    })

if __name__ == '__main__':
    # Start the Flask development server on port 5000
    app.run(host='0.0.0.0', port=5000, debug=True)
