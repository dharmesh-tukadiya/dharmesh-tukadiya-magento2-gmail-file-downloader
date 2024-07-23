# Magento 2 Gmail File Downloader - Download Google Gmail Attachments To Magento Via API Integration & Custom Filters

## Authorization Flow & Configurations

### Step-by-Step Guide:

1. **Navigate to Google Cloud Console:**
   - Visit [Google Cloud Console](https://console.cloud.google.com/apis/dashboard).
   - Create a new project, e.g., "Gmail File Downloader".

2. **Switch to the New Project:**
   - From the top navigation, switch to "Gmail File Downloader".

3. **Enable Gmail API:**
   - Go to the "Library" section on the left-hand side.
   - Search for "Gmail API" and enable it.

4. **Create OAuth Credentials:**
   - Under the "Credentials" tab, click on "+ Create Credentials" => "OAuth client ID".
   - Click on "Configure Consent Screen".
   - If you're a Google Workspace user, select "Internal"; otherwise, select "External" and click "Create".

5. **Fill in the Consent Screen Details:**
   - App Name: "Gmail File Downloader"
   - User Support Email: `<Choose-Your-Email>`
   - Developer Contact Information: `<Enter-Your-Email-Address>`
   - Click on "Save & Continue".
   - Skip Scopes and click on "Save & Continue".
   - Click on "Add Users" and enter your email address.
   - Click on "Save & Continue".

6. **Create OAuth Client ID:**
   - Go to "Library" => "Gmail API" => "Manage" => "Credentials" tab => "Create Credentials" => "OAuth Client ID".
   - Select Application Type: "Web Application".
   - Authorized redirect URIs: `<your-website-url>/randomurl-we8d4fx1246a.html`.
   - Click on "Create".
   - Download credentials as JSON and paste the JSON data in Magento Backend Admin under "Store" => "Configuration" => "Gmail File Downloader" section => "Settings" tab => "Credentials" => "Web".

7. **Authorize and Get Access Token:**
   - Open the following URL in your browser with details from the downloaded JSON file:
     ```
     https://accounts.google.com/o/oauth2/auth
     ?client_id=<CLIENT_ID>
     &redirect_uri=<your-website-url>/randomurl-we8d4fx1246a.html
     &scope=https://www.googleapis.com/auth/gmail.readonly
     &response_type=code
     &access_type=offline
     &prompt=consent
     ```
   - After successful authorization, copy the received code from the redirected URL.

8. **Exchange Authorization Code for Access Token:**
   - Open the terminal and use the following command:
     ```bash
     CLIENT_ID="<CLIENT_ID>"
     CLIENT_SECRET="<CLIENT_SECRET>"
     REDIRECT_URI="<your-website-url>/randomurl-we8d4fx1246a.html"
     CODE="<CODE_RECEIVED_AFTER_AUTHORIZATION>"

     curl -X POST -H "Content-Type: application/x-www-form-urlencoded" \
     -d "code=$CODE" \
     -d "client_id=$CLIENT_ID" \
     -d "client_secret=$CLIENT_SECRET" \
     -d "redirect_uri=$REDIRECT_URI" \
     -d "grant_type=authorization_code" \
     -d "access_type=offline" \
     https://oauth2.googleapis.com/token
     ```

9. **Store the Access Token:**
   - Copy the JSON data from the terminal response.
   - Paste it in Magento Backend Admin under "Store" => "Configuration" => "Gmail File Downloader" section => "Settings" tab => "Credentials" => "Token".

10. **Handle Missing Refresh Token:**
    - If the refresh token is not received, remove access for the Gmail File Downloader app from the Gmail account ([Manage Permissions](https://myaccount.google.com/u/0/permissions)).
    - Repeat the authentication process from step 7.

11. **Enable the Module:**
    - Go to "Store" => "Configuration" => "Gmail File Downloader" section => "Settings" tab => "Module Status" and enable the module.

12. **Set User ID:**
    - Enter your email address in "Store" => "Configuration" => "Gmail File Downloader" section => "Settings" tab => "Credentials" => "User ID".

### Configuration Profiles:

Example configuration profile with explanation:
```json
[
  {
    "from": "",
    "to": "",
    "subject": "",
    "hasTheWords": "",
    "doesnotHaveTheWords": "",
    "size": "",
    "within": "",
    "after": "",
    "before": "",
    "withinWhichEntity": "",
    "hasAttachment": "",
    "notIncludeChats": "",
    "outputFilePath": "",
    "absoluteFilePath": "",
    "attachedFileExtension": "",
    "specificAttachedFileName": "",
    "fileImporterClass": "",
    "fileImporterFunction": ""
  }
]
```
You can add multiple JSON objects in the above array to download more than one file at a time.

#### Fields Explanation:
- **from:** Comma-separated sender emails.
- **to:** Comma-separated recipient emails.
- **subject:** Email subject.
- **hasTheWords:** Words in the email text.
- **doesnotHaveTheWords:** Exclude specific words in the email text.
- **size:** Attachment file size.
- **within:** Within how many days? Set "0" for the latest email.
- **after:** Date after.
- **before:** Date before.
- **withinWhichEntity:** Within which entity? (All Mail, Inbox, Starred, etc.)
- **hasAttachment:** Has attachment? (True or False)
- **notIncludeChats:** Exclude chats?
- **outputFilePath:** Output file path starting from the Magento root directory.
- **absoluteFilePath:** Absolute file path (overrides output file path).
- **attachedFileExtension:** File extensions (e.g., xlsx, csv, docx, etc.).
- **specificAttachedFileName:** Specific file name in the email attachment.
- **fileImporterClass:** File importer class (for custom file processing).
- **fileImporterFunction:** Executable function of the file importer class.

## Additional Notes:

- A cron job is set to run every hour.

Enhance your Magento 2 store with seamless Gmail attachment downloads using this comprehensive guide. Enjoy automated file imports directly into your Magento backend!