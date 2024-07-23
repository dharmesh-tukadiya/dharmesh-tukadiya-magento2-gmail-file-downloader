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
   php bin/magento module:enable Dharmesh_GmailFileDownloader
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

1. **Google Cloud Console:**
   - Visit [Google Cloud Console](https://console.cloud.google.com/apis/dashboard).
   - Create a new project named "Gmail File Downloader".
   - Enable the "Gmail API" from the "Library" section.
   - Create OAuth 2.0 credentials and download the JSON file.

2. **Magento Configuration:**
   - Navigate to Magento Admin: `Stores` > `Configuration` > `Gmail File Downloader` > `Settings`.
   - Paste the JSON data under the "Credentials" tab.

3. **Authorization URL:**
   - Use the following URL for authorization:
     ```
     https://accounts.google.com/o/oauth2/auth
     ?client_id=<CLIENT_ID>
     &redirect_uri=<your-website-url>/randomurl-we8d4fx1246a.html
     &scope=https://www.googleapis.com/auth/gmail.readonly
     &response_type=code
     &access_type=offline
     &prompt=consent
     ```

4. **Exchange Authorization Code for Access Token:**
   - Run the following command in the terminal:
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

5. **Store Access Token:**
   - Copy the JSON response and paste it in Magento Admin under `Stores` > `Configuration` > `Gmail File Downloader` > `Settings` > `Token`.

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