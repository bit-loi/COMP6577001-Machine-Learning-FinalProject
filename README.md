# Shopmart Marketplace: E-Commerce with Machine Learning Decision Support

**FINAL PROJECT – MACHINE LEARNING**  
**Group:** 5  
**BINUS University**  
**Academic Year:** 2025/2026  
**Faculty / Department:** School of Computer Science / Computer Science Department  
**Course Code:** COMP6577001 / Machine Learning  

---

## Live Deployment URLs

- **PHP Web Application (Railway):** https://shopmart-web-production.up.railway.app/
- **Machine Learning API (Hugging Face Spaces):** https://jasonloi-shopmart-ml-service.hf.space/
- **Machine Learning API Docs:** https://jasonloi-shopmart-ml-service.hf.space/docs

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

### D. Deploying the PHP Web Application to Railway
The PHP storefront/admin application is deployed to Railway using a Docker image, with managed MySQL and Redis services in the same Railway project.

#### Railway Services
- **Project:** `shopmart`
- **Web service:** `shopmart-web`
- **MySQL service:** `MySQL`
- **Redis service:** `Redis`
- **Public web URL:** https://shopmart-web-production.up.railway.app/
- **Docker image:** `jblloi03452/shopmart-web`
- **Pinned image digest with DB auto-bootstrap:** `sha256:10f9653d9bb92608fa5d4f3db42d7898d44859d9b16323f01c4fd665fb1adf1b`

#### Build and Push the Web Image
Build the PHP Apache image locally:

```powershell
docker build -t shopmart-web:latest -t jblloi03452/shopmart-web:latest .
```

Push the image to Docker Hub:

```powershell
docker push jblloi03452/shopmart-web:latest
```

If Docker Hub upload fails with an `EOF` error, retry after logging in again:

```powershell
docker logout
docker login -u jblloi03452
docker push jblloi03452/shopmart-web:latest
```

#### Link the Docker Image to Railway
Connect the image to the Railway web service:

```powershell
railway service source connect --image jblloi03452/shopmart-web:latest --service shopmart-web
```

For reproducible deployments, pin the known-good digest:

```powershell
railway service source connect --image jblloi03452/shopmart-web@sha256:10f9653d9bb92608fa5d4f3db42d7898d44859d9b16323f01c4fd665fb1adf1b --service shopmart-web
```

Redeploy the web service:

```powershell
railway service redeploy --service shopmart-web --yes
```

#### Required Railway Variables
Set these variables on the `shopmart-web` service. Use Railway variable references for MySQL and Redis values; do not hard-code passwords in the repository.

```text
APP_ENV=production
APP_DEBUG=false
APP_URL=https://shopmart-web-production.up.railway.app
PORT=8080

DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_NAME=railway
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

REDIS_HOST=${{Redis.REDISHOST}}
REDIS_PORT=${{Redis.REDISPORT}}
REDIS_PASSWORD=${{Redis.REDISPASSWORD}}
REDIS_DATABASE=0
```

Rate-limit variables can keep the local defaults:

```text
RATE_LIMIT_LOGIN_MAX=5
RATE_LIMIT_LOGIN_DECAY=15
RATE_LIMIT_REGISTER_MAX=10
RATE_LIMIT_REGISTER_DECAY=5
RATE_LIMIT_FORGOT_PASSWORD_MAX=3
RATE_LIMIT_FORGOT_PASSWORD_DECAY=30
RATE_LIMIT_CONTACT_MAX=5
RATE_LIMIT_CONTACT_DECAY=10
RATE_LIMIT_API_MAX=1000
RATE_LIMIT_API_DECAY=1
RATE_LIMIT_GENERAL_MAX=60
RATE_LIMIT_GENERAL_DECAY=1
```

Generate a fresh `SECRET_KEY` in Railway. Do not reuse or commit local `.env` secrets.

#### Generate the Public Domain
The container listens on port `8080`, so generate the Railway domain with:

```powershell
railway domain --service shopmart-web --port 8080
```

Then make sure `APP_URL` matches the generated domain:

```powershell
railway variable set --service shopmart-web APP_URL=https://shopmart-web-production.up.railway.app
railway service redeploy --service shopmart-web --yes
```

#### MySQL Schema and Seed Data
Railway creates an empty default MySQL database named `railway`. The Docker image includes an automatic database bootstrap step so the schema does not need to be pasted manually into the Railway console.

The bootstrap runs from `docker-entrypoint.sh` before Apache starts:

```text
php /var/www/html/scripts/railway-bootstrap-db.php
```

The bootstrap script:
- connects with the Railway `DB_*` or `MYSQL*` environment variables
- checks whether the `products` table already exists and contains data
- imports `database/shopmart_railway_import.sql` only when the database is empty
- skips import on later restarts after the seed data already exists

The Railway import file intentionally omits `CREATE DATABASE` and `USE` statements so it imports into Railway's default `railway` database:

```text
database/shopmart_railway_import.sql
```

Keep the web service database name set to:

```text
DB_NAME=railway
```

After deployment, verify the database from the Railway MySQL Data tab or Console:

```sql
SHOW TABLES;
SELECT COUNT(*) FROM products;
```

Manual fallback: if automatic bootstrap is disabled or the SQL file is not included in the image, import `database/shopmart_railway_import.sql` into the default `railway` database using the Railway MySQL Console or a modern MySQL client.

If the web app shows `503 Service Unavailable`, check the logs:

```powershell
railway logs --service shopmart-web --lines 120
```

Common database-related messages:
- `Unknown database 'shopmart'`: `DB_NAME` is still set to `shopmart`; change it to `railway`.
- `Table 'railway.products' doesn't exist`: the bootstrap has not run yet, the SQL file is missing from the image, or the import failed.
- `Base table or view not found`: the database exists, but the schema/data import is incomplete.

#### Deployment Verification
Check the service status:

```powershell
railway service status --service shopmart-web
```

Expected result:

```text
Status: SUCCESS
```

Verify the public URL:

```powershell
Invoke-WebRequest -Uri https://shopmart-web-production.up.railway.app -UseBasicParsing
```

Expected HTTP status: `200`.

### E. Deploying the ML Service to Hugging Face Spaces
The Flask-based ML API in `ml_services/` is deployed to Hugging Face Spaces using Docker.

#### Hugging Face Space Settings
- **Space SDK:** Docker
- **Space name:** `shopmart-ml-service`
- **Hardware:** CPU Basic
- **App port:** `7860`
- **Root app file:** `main.py`
- **Gunicorn entrypoint:** `main:app`

The required Space metadata should be present in the Space `README.md`:

```yaml
---
title: Shopmart ML Service
sdk: docker
app_port: 7860
license: mit
---
```

#### Required Docker Configuration
The `ml_services/Dockerfile` must expose and run the service on port `7860`:

```dockerfile
EXPOSE 7860
CMD ["gunicorn", "--bind", "0.0.0.0:7860", "--workers", "2", "--threads", "4", "--timeout", "120", "main:app"]
```

The Flask app also reads the port from the environment:

```python
app.run(host="0.0.0.0", port=int(os.environ.get("PORT", 7860)))
```

#### Manual Deployment
The project uses two Git repositories during manual Hugging Face deployment:

```text
shopmart/
  ml_services/          # Main project ML source. Edit files here first.
    main.py
    Dockerfile
    requirements.txt

    hf-space/           # Cloned Hugging Face Space repository. Push HF deploys here.
      main.py
      Dockerfile
      requirements.txt
```

Use this rule to avoid committing to the wrong repository:

```text
Edit ML source in:      C:\xampp\htdocs\shopmart\ml_services
Push Hugging Face from: C:\xampp\htdocs\shopmart\ml_services\hf-space
Push GitHub from:       C:\xampp\htdocs\shopmart
```

Check where you are before committing:

```powershell
pwd
git remote -v
```

If the remote is Hugging Face, you are in the deployment repository:

```text
https://huggingface.co/spaces/JasonLOi/shopmart-ml-service
```

If the remote is GitHub, you are in the main project repository:

```text
https://github.com/.../COMP6577001-Machine-Learning-FinalProject.git
```

Clone the Hugging Face Space only once:

```bash
cd ml_services
git clone https://huggingface.co/spaces/JasonLOi/shopmart-ml-service hf-space
```

If `hf-space` already exists, do not clone again. Reuse the existing folder:

```powershell
cd C:\xampp\htdocs\shopmart\ml_services\hf-space
git remote -v
```

Before deploying, copy the latest ML source files into the Hugging Face clone:

```bash
cp -r ./* hf-space/
```

On Windows PowerShell:

```powershell
cd C:\xampp\htdocs\shopmart\ml_services
Copy-Item -Path .\* -Destination .\hf-space\ -Recurse -Force -Exclude hf-space,venv,__pycache__,.env
```

Then commit and push from the Hugging Face clone:

```powershell
cd C:\xampp\htdocs\shopmart\ml_services\hf-space
git status
git add .
git commit -m "Deploy Shopmart ML service"
git push
```

If `git status` says the branch is ahead of `origin/main`, the local deployment commit has not reached Hugging Face yet:

```text
Your branch is ahead of 'origin/main' by 1 commit.
```

Run:

```powershell
git push
```

That push triggers the Hugging Face Docker rebuild.

#### Deployment Verification
After Hugging Face finishes building the Docker image, the service is available at:

```text
https://jasonloi-shopmart-ml-service.hf.space/
```

If the root URL returns a plain Flask `Not Found` page during deployment checks, open the Swagger documentation URL directly:

```text
https://jasonloi-shopmart-ml-service.hf.space/docs
```

Do not use the Hugging Face repository page URL for API endpoints:

```text
https://huggingface.co/spaces/JasonLOi/shopmart-ml-service/docs
```

That URL points to the Hugging Face Space repository UI, not the running Flask container.

The `/docs` page is the primary API access page for testing the deployed REST endpoints through Swagger UI.

To verify that the running container uses the latest code, check the Swagger version shown near the title. The expected version is defined in `ml_services/main.py`:

```python
version="1.0.1"
```

You can also inspect the generated Swagger JSON directly:

```text
https://jasonloi-shopmart-ml-service.hf.space/swagger.json
```

If the Swagger UI still shows an older version after `git push`, check these items:

```powershell
cd C:\xampp\htdocs\shopmart\ml_services\hf-space
Select-String -Path .\main.py -Pattern 'version|namespace'
git status
```

Expected markers:

```text
version="1.0.1"
namespace("prediction"
namespace("churn"
namespace("batch"
namespace("segmentation"
```

If `hf-space/main.py` is still old, sync it again:

```powershell
cd C:\xampp\htdocs\shopmart\ml_services\hf-space
Copy-Item ..\main.py .\main.py -Force
git add main.py
git commit -m "Fix Swagger documentation for all ML endpoints"
git push
```

If the Hugging Face `Files` tab shows the new `main.py` but the App still shows the old Swagger version, open the Space menu and choose **Restart Space**. Use **Factory reboot** only if a normal restart does not refresh the container.

Useful endpoints:

```text
GET  /
GET  /health
GET  /docs
POST /predict
POST /predict/simulate
POST /predict/churn
POST /batch/churn
POST /segment
```

Deployment scope:

- `POST /predict` and `POST /predict/simulate` run the deployed Isolation Forest anomaly/fraud detection model.
- `POST /predict/churn` uses the churn model when the churn `.joblib` artifact is available in the deployed Space.
- `POST /segment` is currently a lightweight segmentation placeholder endpoint.

#### CI/CD Deployment
The repository includes a GitHub Actions workflow at `.github/workflows/ci-cd.yml`.
To enable automatic Hugging Face deployment, configure these GitHub repository secrets:

```text
HF_TOKEN=your_huggingface_write_token
HF_SPACE_REPO=JasonLOi/shopmart-ml-service
```

When changes are pushed to `main` or `master`, the workflow runs quality checks, builds Docker images, and deploys `ml_services/` to the Hugging Face Space if both secrets are configured.

---
*Disclaimer: This project repository utilizes continuous integration via Git. All theoretical formulations and dataset preprocessing scripts are maintained strictly within the project policies established by BINUS University.*
