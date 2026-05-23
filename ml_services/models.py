"""
models.py — Model loading for Shopmart ML Engine

Loads all trained ML models once at startup.
All route modules import from here via:
    from models import iso_forest, scaler, churn_model
"""

import os
import pickle
import joblib

_BASE = os.path.dirname(os.path.abspath(__file__))

# ── Isolation Forest (Fraud / Anomaly Detection) ──────────────────────────────
iso_forest = None
scaler     = None

try:
    with open(os.path.join(_BASE, 'isolation_forest.pkl'), 'rb') as f:
        iso_forest = pickle.load(f)
    with open(os.path.join(_BASE, 'scaler.pkl'), 'rb') as f:
        scaler = pickle.load(f)
    print("- Isolation Forest + Scaler loaded successfully")
except Exception as e:
    print(f"[WARN] Could not load fraud models: {e}")

# ── Churn Prediction ──────────────────────────────────────────────────────────
churn_model = None

try:
    churn_model = joblib.load(os.path.join(_BASE, 'online_retail_ii_churn_model.joblib'))
    print("- Churn model loaded successfully")
except Exception as e:
    print(f"[WARN] Could not load churn model: {e}")
