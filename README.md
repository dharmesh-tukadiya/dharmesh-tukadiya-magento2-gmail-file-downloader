# Magento 2 Gmail File Downloader

**Download Google Gmail Attachments to Magento via API Integration & Custom Filters**

[![Magento 2](https://img.shields.io/badge/Magento-2-brightgreen.svg)](https://magento.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

## Introduction

The **Magento 2 Gmail File Downloader** extension allows you to seamlessly download Gmail attachments directly into your Magento store using API integration and custom filters. Automate file imports and enhance your Magento backend with this powerful tool.

## Features

- **OAuth 2.0 Integration:** Securely connect your Gmail account to Magento.
- **Custom Filters:** Set specific criteria for downloading email attachments.
- **Automated Imports:** Schedule cron jobs to run hourly.
- **Configuration Profiles:** Manage multiple download profiles with ease.

## Installation

1. **Download the Extension:**

2. **Enable the Module:**
   ```
   php bin/magento module:enable DnTukadiya_GmailFileDownloader
   ```

3. **Run Upgrade and Compile:**
   ```
   php bin/magento setup:upgrade
   php bin/magento setup:di:compile
   ```

4. **Deploy Static Content:**
   ```
   php bin/magento setup:static-content:deploy
   ```

## Authorization Flow & Configurations

### Step-by-Step Guide:

1. **Navigate to Google Cloud Console:**
   - Visit [Google Cloud Console](https://console.cloud.google.com/apis/dashboard).

2. **Create a New Project:**
   - Click on "Create New Project" and name it **Gmail File Downloader**.

3. **Switch to the New Project:**
   - After the project is created, switch to **Gmail File Downloader** from the top navigation.

4. **Enable Gmail API:**
   - On the left-hand menu, go to the **Library** section.
   - Search for **Gmail API** and click on **Enable**.

5. **Create OAuth Credentials:**
   - Under the **Credentials** tab, click on **+ Create Credentials** and select **OAuth client ID**.
   - You will be prompted to configure the consent screen.

6. **Configure Consent Screen:**
   - If you are a Google Workspace user, select **Internal**, otherwise, choose **External** and click **Create**.
   - Fill in the required details:
     - **App Name:** Gmail File Downloader
     - **User Support Email:** `<Choose-Your-Email>`
     - **Developer Contact Information:** `<Enter-Your-Email-Address>`
   - Click **Save & Continue** twice to skip the scopes section.

7. **Add Test Users:**
   - Click on **Add Users** and enter your email address.
   - Click **Save & Continue**.

8. **Generate OAuth Client ID:**
   - Navigate back to **Library** > **Gmail API** > **Manage** > **Credentials** tab > **Create Credentials** > **OAuth Client ID**.
   - Select **Web Application** as the application type.
   - For **Authorized redirect URIs**, enter `<your-website-url>/randomurl-we8d4fx1246a.html`.
   - Click **Create** and download the credentials as a JSON file.

9. **Configure Magento Backend:**
   - In the Magento Admin, navigate to **Stores** > **Configuration** > **Gmail File Downloader** > **Settings** tab > **Credentials** > **Web**.
   - Paste the JSON data from the downloaded file.

10. **Authorize the Application:**
    - Open the following URL in your browser, filling in the details from the downloaded JSON file:

      ```url
      https://accounts.google.com/o/oauth2/auth?client_id=<CLIENT_ID>&redirect_uri=<your-website-url>/randomurl-we8d4fx1246a.html&scope=https://www.googleapis.com/auth/gmail.readonly&response_type=code&access_type=offline&prompt=consent
      ```

11. **Retrieve Authorization Code:**
    - After successful authorization, you will be redirected to the specified URL with a code in the URL.
    - Copy the code from the URL. If the site redirects to the homepage, open the browser’s debugger (F12), go to the **Network** tab, and enable **Preserve Log** to retrieve the code.

12. **Exchange Code for Token:**
    - Open your terminal and use the following command to exchange the authorization code for an access token:

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

13. **Configure Access Token in Magento:**
    - Copy the JSON response from the terminal and paste it into the **Token** field under **Stores** > **Configuration** > **Gmail File Downloader** > **Settings** > **Credentials**.

14. **Handle Missing Refresh Token:**
    - If the response does not include a refresh token, remove access for the **Gmail File Downloader** app from your Google account (visit [Google Account Permissions](https://myaccount.google.com/u/0/permissions)) and redo the authentication process starting from step 10.

15. **Enable the Module:**
    - In Magento Admin, go to **Stores** > **Configuration** > **Gmail File Downloader** > **Settings** and set **Module Status** to **Enable**.

16. **Set Up User ID:**
    - Under **Stores** > **Configuration** > **Gmail File Downloader** > **Settings** > **Credentials**, enter your email address in the **User ID** field.

17. **Configure Profiles:**
    - Complete the setup by configuring the profiles under **Stores** > **Configuration** > **Gmail File Downloader** > **Settings** > **Configuration Profiles**.

## Configuration Profiles

### Example Profile

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

### Fields Explanation

- **from:** Comma-separated sender emails.
- **to:** Comma-separated recipient emails.
- **subject:** Email subject.
- **hasTheWords:** Words in the email text.
- **doesnotHaveTheWords:** Exclude specific words in the email text.
- **size:** Attachment file size.
- **within:** Within how many days? Set "0" for the latest email.
- **after:** Date after.
- **before:** Date before.
- **withinWhichEntity:** Email entity (All Mail, Inbox, Starred, etc.).
- **hasAttachment:** Has attachment? (True/False)
- **notIncludeChats:** Exclude chats? (True/False)
- **outputFilePath:** Output file path starting from the Magento root directory.
- **absoluteFilePath:** Absolute file path (overrides output file path).
- **attachedFileExtension:** File extensions (e.g., xlsx, csv, docx, etc.).
- **specificAttachedFileName:** Specific file name in the email attachment.
- **fileImporterClass:** File importer class for custom processing.
- **fileImporterFunction:** Function of the file importer class.

## Additional Notes

- A cron job is set to run every hour to automate the download process.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Contributing

Feel free to submit pull requests, create issues, or fork this repository. Contributions are always welcome!

## Support

For any questions or support, please open an issue or contact the [maintainer](https://github.com/dharmesh-tukadiya).

---

Enhance your Magento 2 store by integrating Gmail attachment downloads with the **Magento 2 Gmail File Downloader** extension. Automate your file import process and manage your emails efficiently!
