# Simulation Anomaly Detector In Bookstore Website Transactions

**FINAL PROJECT – MACHINE LEARNING**  
**Group:** 5  
**BINUS University**  
**Academic Year:** 2025/2026  
**Faculty / Department:** School of Computer Science / Computer Science Department  
**Course Code:** COMP6577001 / Machine Learning  

---

## 1. Problem Statement & Motivation
In the rapidly growing e-commerce sector, transactional integrity and customer relationship management are paramount. Two significant challenges persist for online retailers: identifying fraudulent activities without disrupting the legitimate user experience, and effectively segmenting customers to optimize targeted marketing strategies. 

This project addresses these challenges by simulating an integrated Machine Learning Dashboard within a Bookstore E-Commerce Platform. The motivation is to deploy an end-to-end, classical machine learning pipeline that automatically highlights anomalous transactions (fraud detection) and classifies users based on purchasing behavior (user segmentation), providing administrators with actionable, data-driven insights.

## 2. Dataset Description
The machine learning models implemented in this project utilize the **IEEE-CIS Fraud Detection Database** to simulate the transaction and identity features of our e-commerce environment.

- **Source:** Kaggle - [IEEE-CIS Fraud Detection Dataset](https://www.kaggle.com/datasets/lnasiri007/ieeecis-fraud-detection)
- **Characteristics:** The dataset contains a massive array of transaction features (anonymized variables such as V1-V339, transaction amounts, card details) and identity features (device type, network indicators).
- **Justification:** This real-world industry dataset is highly relevant for testing classical anomaly detection models against complex, multi-dimensional transactional footprints.

## 3. Exploratory Data Analysis (EDA)
*(Note: Full EDA details and graphical representations are documented in the accompanying presentation slides).*
- **Class Imbalance:** Fraudulent transactions represent a very minor fraction of the dataset, necessitating anomaly detection over standard classification.
- **Feature Correlation:** Certain subsets of anonymized ('V') features display high correlation, requiring dimensionality reduction or tree-based algorithms capable of isolating significant splits.
- **Distribution Check:** Transaction amounts are heavily right-skewed; a log transformation and scaling (using StandardScaler) were essential before model training.

## 4. Machine Learning Problem Formulation
The project tackles two primary unsupervised constraints:
1. **Anomaly Detection (Fraud):** Formulated as an unsupervised outlier detection problem, where the vast majority of points are background (normal purchases), and anomalies lie in sparse regions.
2. **User Segmentation:** Formulated as a clustering problem to identify natural groupings among user transaction histories without pre-existing labels.

## 5. Model Selection & Rationale
*Constraint Adherence: No Deep Learning techniques are used. Only Classical Machine Learning approaches are applied.*

1. **Anomaly Detection: Isolation Forest**
   - **Method:** `sklearn.ensemble.IsolationForest`
   - **Rationale:** Isolation Forest is highly effective for high-dimensional datasets with heavy class imbalances. It explicitly isolates anomalies rather than profiling normal data points, making it significantly faster and more accurate for processing the IEEE-CIS dataset compared to density-based methods like DBSCAN.

2. **User Segmentation: K-Means Clustering**
   - **Method:** `sklearn.cluster.KMeans`
   - **Rationale:** As a distance-based classical clustering algorithm, K-Means is scalable and highly interpretable. By passing aggregated user metrics (e.g., total spend, transaction count), we can quickly partition users into distinct, actionable segments (e.g., High-Value Whales, Bargain Hunters, Occasional Readers).

## 6. Deployment Architecture
The machine learning solution is fully deployed as a usable web application conforming to project requirements.

- **Machine Learning API (Backend):** Developed in **Python** using the **Flask** framework. It serves the pre-trained `isolation_forest.pkl` and `scaler.pkl` models via RESTful endpoints.
- **Frontend / Client Interface:** A highly interactive admin console simulated using **React** and **Tailwind CSS**. It fetches data from the Flask API and renders real-time anomaly feeds and clustering distribution.
- **E-Commerce Core:** The underlying website operates on **PHP 7.4+** and **MySQL**, bridging standard database state constraints with advanced machine learning diagnostics.

## 7. Real User Testing Design
To fulfill the evaluation requirement, the deployed dashboard is subjected to physical user testing with a minimum of 5 external participants.
- **Usage Scenario:** Participants act as Bookstore Administrators tasked with monitoring live transactions and reviewing new customer segments. They will utilize the dashboard to flag a specific fraudulent scenario.
- **Feedback Collection:** Metrics evaluated include Usability, Intuitiveness of the Result Interpretations, and System Stability. Data is collected via Likert scales and open-ended qualitative prompts.
- **Publication:** The detailed findings, respondent demographics, and user feedback analysis will be strictly presented in the Final PPT Report.

---

## 8. Manual Installation & Execution

### A. Environment Prerequisites
- Python 3.8+
- PHP 7.4+ and MySQL (XAMPP recommended)
- Node.js (for Vite/React compilation)

### B. Machine Learning API Setup
1. Navigate to the Machine Learning directory:
   ```bash
   cd ml_services
   ```
2. Create and activate the virtual environment:
   ```bash
   python -m venv venv
   source venv/Scripts/activate # On Windows: venv\Scripts\activate
   ```
3. Install required Data Science libraries:
   ```bash
   pip install -r requirements.txt
   ```
4. Start the Flask server:
   ```bash
   python main.py
   ```

### C. Web Application Setup
1. Clone the repository into your local web server root (e.g., `C:\xampp\htdocs\bookstore`).
2. Start the Apache and MySQL services. Ensure the database matches `config/config.php`.
3. Install frontend dependencies and start the development server for the simulation dashboard:
   ```bash
   npm install
   npm run dev
   ```
4. Access the Administration Simulation Interface at: `http://localhost/bookstore/simulation.php`

---
*Disclaimer: This project repository utilizes continuous integration via Git. All theoretical formulations and dataset preprocessing scripts are maintained strictly within the project policies established by BINUS University.*
