# Shopmart Marketplace: E-Commerce with Machine Learning Decision Support

**FINAL PROJECT – MACHINE LEARNING**  
**Group:** 5  
**BINUS University**  
**Academic Year:** 2025/2026  
**Faculty / Department:** School of Computer Science / Computer Science Department  
**Course Code:** COMP6577001 / Machine Learning  

---

## 1. Problem Statement & Motivation
In the rapidly growing e-commerce sector, customer retention and transactional integrity are paramount. Two significant challenges persist for online retailers: identifying customers who are likely to stop purchasing (customer churn) and detecting fraudulent or anomalous transactions. 

This project addresses these challenges by simulating an integrated **Machine Learning Dashboard** within **Shopmart**, a fully functional E-Commerce Marketplace platform. The motivation is to deploy an end-to-end, classical machine learning pipeline that predicts customer churn (retention) and highlights anomalous transactions, providing administrators with actionable, data-driven decision support systems.

## 2. Dataset Description
The machine learning models implemented in this project utilize the **UCI Online Retail II Database** to simulate the transaction and identity features of our e-commerce environment.

- **Source:** UCI Machine Learning Repository - [Online Retail II Dataset](https://archive.ics.uci.edu/dataset/502/online+retail+ii)
- **Characteristics:** The dataset contains all the transactions occurring for a UK-based and registered non-store online retail between 01/12/2009 and 09/12/2011.
- **Justification:** This real-world industry dataset provides excellent longitudinal data to calculate Recency, Frequency, and Monetary (RFM) features essential for predicting customer churn in a retail setting.

## 3. Machine Learning Problem Formulation
The project tackles two primary constraints:
1. **Customer Retention (Churn Prediction):** Formulated as a supervised binary classification problem. The model predicts whether a customer will churn (stop buying) based on their historical purchase behavior (Orders, Revenue, Recency, Age).
2. **Anomaly Detection (Fraud):** Formulated as an unsupervised outlier detection problem, where the vast majority of points are normal purchases, and anomalies lie in sparse regions.

## 4. Model Selection & Rationale
*Constraint Adherence: No Deep Learning techniques are used. Only Classical Machine Learning approaches are applied.*

1. **Churn Prediction: Random Forest Classifier**
   - **Method:** `sklearn.ensemble.RandomForestClassifier`
   - **Rationale:** Random Forest is robust to outliers, handles non-linear relationships well, and provides feature importance metrics, which is crucial for understanding why a customer might churn. It performs exceptionally well on tabular RFM data.

2. **Anomaly Detection: Isolation Forest**
   - **Method:** `sklearn.ensemble.IsolationForest`
   - **Rationale:** Isolation Forest is highly effective for high-dimensional datasets with heavy class imbalances. It explicitly isolates anomalies rather than profiling normal data points, making it significantly faster and more accurate.

## 5. Deployment Architecture (Decision Support System)
The machine learning solution is fully deployed as a usable web application conforming to project requirements. The system utilizes a **Batch Scoring Architecture** rather than real-time prediction, which perfectly mirrors real-world enterprise reporting.

- **Machine Learning Pipeline (Python):** A batch scoring script (`ml_services/churn_batch_scoring.py`) loads the pre-trained `.joblib` models, processes the latest customer features, and generates a CSV containing churn probabilities.
- **Backend / Admin Interface (PHP/MySQL):** Administrators upload the generated CSV via the `import_churn.php` interface. The PHP backend processes the probabilities, calculates risk levels, assigns recommended retention actions, and stores them in the database.
- **Interactive Dashboard:** A highly interactive admin console simulated using **Tailwind CSS**. It renders risk distribution charts, customer tables, and provides an actionable **Feedback Loop** (e.g., "Send Voucher", "Contacted") where admin actions are logged back into the database (`retention_actions`).
- **E-Commerce Core:** The underlying website operates on **PHP 7.4+** and **MySQL**, featuring a wallet balance system and a user-seller marketplace.

## 6. Real User Testing Design
To fulfill the evaluation requirement, the deployed dashboard is subjected to physical user testing with a minimum of 5 external participants.
- **Usage Scenario:** Participants act as Shopmart Administrators tasked with monitoring customer churn risk. They will utilize the dashboard to identify High-Risk customers and execute a retention action (e.g., sending a discount voucher).
- **Feedback Collection:** Metrics evaluated include Usability, Intuitiveness of the Result Interpretations, and System Stability. Data is collected via Likert scales and open-ended qualitative prompts.
- **Publication:** The detailed findings, respondent demographics, and user feedback analysis will be strictly presented in the Final PPT Report.

---

## 7. Manual Installation & Execution

### A. Environment Prerequisites
- Python 3.8+
- PHP 7.4+ and MySQL (XAMPP recommended)

### B. Machine Learning Batch Scoring Setup
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
   pip install pandas numpy scikit-learn joblib
   ```
4. Run the batch scoring script to generate the CSV:
   ```bash
   python churn_batch_scoring.py
   ```

### C. Web Application Setup
1. Clone the repository into your local web server root (e.g., `C:\xampp\htdocs\shopmart`).
2. Start the Apache and MySQL services in XAMPP.
3. Import the database schema `database/shopmart.sql` into phpMyAdmin.
4. Access the Storefront at: `http://localhost/shopmart/`
5. Access the Administration Dashboard at: `http://localhost/shopmart/admin/`
6. Access the Churn Prediction Dashboard at: `http://localhost/shopmart/admin/churn/`

---
*Disclaimer: This project repository utilizes continuous integration via Git. All theoretical formulations and dataset preprocessing scripts are maintained strictly within the project policies established by BINUS University.*
