# Use an official, lightweight Python runtime as a parent image
FROM python:3.10-slim

# Set environment variables
# - PYTHONUNBUFFERED=1: prevents Python from buffering stdout/stderr (useful for real-time Docker logs)
# - PYTHONDONTWRITEBYTECODE=1: prevents Python from writing .pyc files to disc
ENV PYTHONUNBUFFERED=1 \
    PYTHONDONTWRITEBYTECODE=1 \
    PORT=7860

# Set the working directory in the container
WORKDIR /app

# Install system dependencies
# libgomp1 is required for OpenMP support in scikit-learn models
RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential \
    libgomp1 \
    && rm -rf /var/lib/apt/lists/*

# Copy the requirements file first to leverage Docker's build cache
COPY requirements.txt /app/

# Install the Python dependencies
RUN pip install --no-cache-dir -r requirements.txt

# Copy the rest of the application code and model artifacts into the container
COPY . /app/

RUN adduser --disabled-password --gecos "" appuser \
    && chown -R appuser:appuser /app

# Expose the Hugging Face Spaces port
EXPOSE 7860

USER appuser

# Run the Flask API using Gunicorn for production readiness
CMD ["gunicorn", "--bind", "0.0.0.0:7860", "--workers", "2", "--threads", "4", "--timeout", "120", "main:app"]
