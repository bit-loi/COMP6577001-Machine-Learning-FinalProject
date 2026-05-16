"""
Shopmart Customer Retention Intelligence
=========================================
Batch Churn Scoring Script
--------------------------
Loads the trained model (.joblib) and generates churn_scores.csv
which is then imported to the PHP admin dashboard via import_churn.php.

Usage:
    python churn_batch_scoring.py
    python churn_batch_scoring.py --input customer_features.csv
    python churn_batch_scoring.py --input customer_features.csv --output my_scores.csv

Output:
    churn_scores.csv  →  upload this via admin/churn/import_churn.php
"""

import argparse
import os
import sys
from datetime import date

import joblib
import pandas as pd
import numpy as np

# ----------------------------------------------------------------
# Configuration
# ----------------------------------------------------------------
BASE_DIR      = os.path.dirname(os.path.abspath(__file__))
MODEL_PATH    = os.path.join(BASE_DIR, "online_retail_ii_churn_model.joblib")
SCALER_PATH   = os.path.join(BASE_DIR, "scaler.pkl")
DEFAULT_INPUT  = os.path.join(BASE_DIR, "latest_historical_customer_churn_scores.csv")
DEFAULT_OUTPUT = os.path.join(BASE_DIR, "churn_scores_export.csv")

FEATURE_COLS = [
    "orders_last_window",
    "revenue_last_window",
    "recency_days",
    "customer_age_days",
]

OUTPUT_COLS = [
    "customer_id",
    "snapshot_date",
    "country",
    "orders_last_window",
    "revenue_last_window",
    "recency_days",
    "customer_age_days",
    "predicted_churn_probability",
    "predicted_churn",
    "risk_level",
    "recommended_action",
]

# ----------------------------------------------------------------
# Risk level & recommended action logic
# (must match PHP logic in import_churn.php for consistency)
# ----------------------------------------------------------------
def get_risk_and_action(prob: float, revenue: float) -> tuple[str, str]:
    if prob >= 0.75 and revenue >= 100:
        return "High", "Send loyalty voucher"
    elif prob >= 0.75:
        return "High", "Send reactivation email"
    elif prob >= 0.50:
        return "Medium", "Send product recommendation"
    else:
        return "Low", "No immediate action"


# ----------------------------------------------------------------
# Main scoring pipeline
# ----------------------------------------------------------------
def run_batch_scoring(input_path: str, output_path: str) -> None:
    print("=" * 55)
    print("  Shopmart Batch Churn Scoring")
    print("=" * 55)

    # 1. Load model
    if not os.path.exists(MODEL_PATH):
        print(f"[ERROR] Model file not found: {MODEL_PATH}")
        print("        Run your training notebook first to generate churn_model.joblib")
        sys.exit(1)

    print(f"[1/5] Loading model from: {MODEL_PATH}")
    model = joblib.load(MODEL_PATH)

    # 2. Load scaler (optional — skip if not used during training)
    scaler = None
    if os.path.exists(SCALER_PATH):
        print(f"[2/5] Loading scaler from: {SCALER_PATH}")
        scaler = joblib.load(SCALER_PATH)
    else:
        print("[2/5] No scaler.joblib found — skipping feature scaling")

    # 3. Load customer features
    if not os.path.exists(input_path):
        print(f"[ERROR] Input file not found: {input_path}")
        print("        Generate customer_features.csv from your transaction database first.")
        sys.exit(1)

    print(f"[3/5] Loading customer data from: {input_path}")
    df = pd.read_csv(input_path)
    print(f"      → {len(df)} customers loaded")

    # Validate required columns
    missing = [c for c in FEATURE_COLS if c not in df.columns]
    if missing:
        print(f"[ERROR] Missing columns in input CSV: {missing}")
        sys.exit(1)

    # 4. Predict churn probability
    print("[4/5] Running churn prediction...")
    X = df[FEATURE_COLS].copy()

    if scaler:
        X_scaled = scaler.transform(X)
    else:
        X_scaled = X.values

    proba = model.predict_proba(X_scaled)[:, 1]
    pred  = (proba >= 0.5).astype(int)

    df["snapshot_date"]               = date.today().isoformat()
    df["predicted_churn_probability"] = np.round(proba, 5)
    df["predicted_churn"]             = pred

    # Apply risk + action logic
    risks, actions = zip(*[
        get_risk_and_action(p, r)
        for p, r in zip(df["predicted_churn_probability"], df["revenue_last_window"])
    ])
    df["risk_level"]        = risks
    df["recommended_action"] = actions

    # Ensure optional columns exist
    if "country" not in df.columns:
        df["country"] = ""

    # 5. Export CSV
    print(f"[5/5] Exporting results to: {output_path}")
    df[OUTPUT_COLS].to_csv(output_path, index=False)

    # Summary
    high   = (df["risk_level"] == "High").sum()
    medium = (df["risk_level"] == "Medium").sum()
    low    = (df["risk_level"] == "Low").sum()

    print()
    print("  ✓ Scoring complete!")
    print(f"  Total customers : {len(df)}")
    print(f"  High risk       : {high}")
    print(f"  Medium risk     : {medium}")
    print(f"  Low risk        : {low}")
    print(f"  Output file     : {output_path}")
    print()
    print("  Next step: Upload the CSV via")
    print("  → http://localhost/shopmart/admin/churn/import_churn.php")
    print("=" * 55)


# ----------------------------------------------------------------
# CLI Entry Point
# ----------------------------------------------------------------
if __name__ == "__main__":
    parser = argparse.ArgumentParser(
        description="Shopmart Batch Churn Scoring — generates churn_scores.csv from .joblib model"
    )
    parser.add_argument(
        "--input",
        default=DEFAULT_INPUT,
        help=f"Path to customer features CSV (default: {DEFAULT_INPUT})"
    )
    parser.add_argument(
        "--output",
        default=DEFAULT_OUTPUT,
        help=f"Path to output CSV (default: {DEFAULT_OUTPUT})"
    )
    args = parser.parse_args()

    run_batch_scoring(args.input, args.output)
