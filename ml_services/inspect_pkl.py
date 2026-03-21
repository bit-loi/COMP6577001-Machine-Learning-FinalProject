import pickle
import warnings
warnings.filterwarnings('ignore')

try:
    with open('c:/xampp/htdocs/bookstore/ml_services/isolation_forest.pkl', 'rb') as f:
        model = pickle.load(f)
    print("=== MODEL INFO ===")
    print(f"Type: {type(model)}")
    print(f"Features in: {getattr(model, 'n_features_in_', 'Unknown')}")
    if hasattr(model, 'feature_names_in_'):
        print(f"Feature Names: {model.feature_names_in_}")
    if hasattr(model, 'get_params'):
        print(f"Params: {model.get_params()}")
except Exception as e:
    print(f"Error loading model: {e}")

try:
    with open('c:/xampp/htdocs/bookstore/ml_services/scaler.pkl', 'rb') as f:
        scaler = pickle.load(f)
    print("\n=== SCALER INFO ===")
    print(f"Type: {type(scaler)}")
    if hasattr(scaler, 'feature_names_in_'):
        print(f"Feature Names: {scaler.feature_names_in_}")
except Exception as e:
    print(f"Error loading scaler: {e}")
