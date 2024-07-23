### Magento 2 Gmail File Downloader - Download Google Gmail Attachments To Magento Via API Integration & Custom Filters

# Authorization Flow & Configurations

1. Navigate https://console.cloud.google.com/apis/dashboard
2. Create New Project, Say "Gmail File Downloader"
3. After Creating New Project Switch To "Gmail File Downloader" from the top navigation.
4. On the left hand, Navigate to "Library" Section.
5. Search For "Gmail API" & Enable It.
6. Under the "Credentials" Tab, Click On "+ Create Credentials" => "OAuth client ID"
7. Click On "Configure Consent Screen"
8. If you're google workspace user, click on "Internal" Else "External" & Click on "Create"
9. Fill In The Details. App Name : "Gmail File Downloader" , User Support Email : "<Choose-Your-Email>", Developer Contact Information : "<Enter-Your-Email-Address>"
10. Click On "Save & Continue"
11. Again Click On "Save & Continue" (Skipping Scopes)
12. Click On "Add Users". Enter your email address out there.
13. Click On "Save & Continue"
14. Now, Again Go to "Library" => "Gmail API" => "Manage" => "Credentials" Tab => "Create Credentials" => "OAuth Client ID".
15. Select Application Type : Say "Web Application", Authorized redirect URIs : <your-website-url>/randomurl-we8d4fx1246a.html
16. Click On "Create"
17. Download Credentials As JSON & In Magento Backend Admin Under "Store" => "Configuration" => "Gmail File Downloader" Section => "Settings" Tab => "Credentials" => "Web" => Paste JSON Data There.

18. Hit below url in browser with the details filled from downloaded json file.
    https://accounts.google.com/o/oauth2/auth
    ?client_id=<CLIENT_ID>
    &redirect_uri=<your-website-url>/randomurl-we8d4fx1246a.html
    &scope=https://www.googleapis.com/auth/gmail.readonly
    &response_type=code
    &access_type=offline
    &prompt=consent

19. After successful authorization, a code will be received in the redicted url it self. So copy it from the url and keep it noted. (If your site is redirected and going to homepage, It's better to open browser debugger (Pressing F12) & Under Network Tab (You must enable "Preserve Log"), You can see an url(<your-website-url>/randomurl-we8d4fx1246a.html) with credentials. You can also keep it noted from there.)

20. Open Terminal & Paste below Fields with values

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

22. You must have received json data printed in the terminal. Copy It and paste under In Magento Backend Admin Under "Store" => "Configuration" => "Gmail File Downloader" Section => "Settings" Tab => "Credentials" => "Token" Field.

23. If not received refresh_token in the response, you need to remove access For the Gmail File Downloader App from the gmail account (https://myaccount.google.com/u/0/permissions) and again follow the authentication process from point 19. After which the refresh_token will be there.

24. "Store" => "Configuration" => "Gmail File Downloader" Section => "Settings" Tab => "Module Status" => Enable Module

25. "Store" => "Configuration" => "Gmail File Downloader" Section => "Settings" Tab => "Credentials" => "User ID" => Enter your email address out there.

26. Set Configuration Profiles
    "Store" => "Configuration" => "Gmail File Downloader" Section => "Settings" Tab => "Configuration Profiles"

Example Of Configuration Profile With Explanation

[
 {
  "from" : "",
  "to" : "",
  "subject" : "",
  "hasTheWords" : "",
  "doesnotHaveTheWords" : "",
  "size" : "",
  "within" : "",
  "after" : "",
  "before" : "",
  "withinWhichEntity" : "",
  "hasAttachment" : "",
  "notIncludeChats" : "",
  "outputFilePath" : "",
  "absoluteFilePath" : "",
  "attachedFileExtension" : "",
  "specificAttachedFileName" : "",
  "fileImporterClass":"",
  "fileImporterFunction" : ""
 }
]
You can add multiple json objects in above array. So you're not limited to download only one file at a time.

::: Fields Explanation :::
Most of the fields values are similar like you apply filter in https://mail.google.com/ => Search Mail => Filter

1. "from" : Comma Seperated From Emails
2. "to" : Comma Seperated From Emails
3. "subject" : Email Subject
4. "hasTheWords" : Has the words in an email text
5. "doesnotHaveTheWords" : Email Text excluding specific words
6. "size" : Attachment File Size
7. "within" : Within How Many Days? Set "0" for latest email.
8. "after" : Date After
9. "before" : Date Before
10. "withinWhichEntity" : Within Which Entity? All Mail, Inbox, Starred, ...
11. "hasAttachment" : Has Attachment ? True or False.
12. "notIncludeChats" : Not To Include Chats ?
13. "outputFilePath" : Output file path starting from magento root dir.
14. "absoluteFilePath" : Absolute file path. It will ignore output file path.
15. "attachedFileExtension" : e.g. xlsx,csv,docx, ...
16. "specificAttachedFileName" : Specific file name in email attachment
17. "fileImporterClass": File importer class (If you have specific class in magento under your custom customization, you can add your class here which processes the downloaded file)
18. "fileImporterFunction" : Executable Function of File Importer Class

# End Of Authorization Flow & Configurations

# Additional Notes

Also there's a cron set. Which Runs Every Hour
