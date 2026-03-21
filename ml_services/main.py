from flask import Flask, jsonify, request
from abc import ABC, abstractmethod
from datetime import datetime
import os

# --- Interfaces (Abstraction) ---
class IHealthDataProvider(ABC):
    @abstractmethod
    def fetch_data(self) -> dict:
        pass

# --- Services (Single Responsibility) ---
class SystemHealthService(IHealthDataProvider):
    def fetch_data(self) -> dict:
        return {
            "status": "operational",
            "service": "Flask-ML-Core",
            "uptime": "healthy",
            "timestamp": datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        }

class BookAnalysisService:
    def process_recommendation(self, user_id: int):
        # SOLID Logic for AI recommendation
        return {"user_id": user_id, "recommendations": ["The Great Gatsby", "1984"]}

# --- Controllers (Dependency Injection) ---
class APIController:
    def __init__(self, health_provider: IHealthDataProvider):
        self.health_provider = health_provider

    def get_status(self):
        return jsonify(self.health_provider.fetch_data())

# --- Routing Setup ---
def create_app():
    app = Flask(__name__)
    
    # Wiring Dependencies
    health_service = SystemHealthService()
    analysis_service = BookAnalysisService()
    controller = APIController(health_service)

    @app.route('/health', methods=['GET'])
    def health_check():
        return controller.get_status()

    @app.route('/api/analyze/<int:user_id>', methods=['GET'])
    def analyze(user_id):
        return jsonify(analysis_service.process_recommendation(user_id))

    @app.route('/', methods=['GET'])
    def index():
        return jsonify({"message": "Welcome to Bookstore ML Microservice"})

    return app

if __name__ == "__main__":
    app = create_app()
    app.run(host="0.0.0.0", port=5000, debug=True)
