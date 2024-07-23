<?php

namespace DnTukadiya\GmailFileDownloader\GmailAPI;

use DnTukadiya\GmailFileDownloader\CURL\Request as CurlRequest;
use function Safe\base64_decode;

class GetEmailAttachments
{
    protected $curlRequest;
    protected $userId;
    protected $messageId;
    protected $storeManager;
    protected $scopeConfig;
    protected $logger;
    protected $isModuleEnabled;
    protected $directoryList;
    protected $configurationProfiles;
    protected $objectManager;
    public function __construct(
        CurlRequest $curlRequest,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\ObjectManagerInterface $objectmanager
    ) {
        $this->curlRequest = $curlRequest;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->directoryList = $directoryList;
        $this->logger = $logger;
        $this->userId = $this->getConfigValue('gmail_file_importer_settings/credentials/user_id');
        $this->isModuleEnabled = $this->getConfigValue('gmail_file_importer_settings/module/status');
        $this->configurationProfiles = json_decode($this->getConfigValue('gmail_file_importer_settings/configuration/profiles'), true);
        $this->objectManager = $objectmanager;
    }
    public function getConfigValue($path)
    {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->storeManager->getStore()->getId());
    }
    public function getEndPointURLData($params)
    {
        try {
            $definitionArray = [
                /**
                 * https://developers.google.com/gmail/api/reference/rest/v1/users.messages/list
                 * GET https://gmail.googleapis.com/gmail/v1/users/{userId}/messages
                 */
                "USER_MESSAGES_LIST" => ["url" => "https://gmail.googleapis.com/gmail/v1/users/" . urlencode($params['userId'] ?? '') . "/messages", "method" => "GET"],
                /**
                 * https://developers.google.com/gmail/api/reference/rest/v1/users.messages/get
                 * GET GET https://gmail.googleapis.com/gmail/v1/users/{userId}/messages/{id}
                 */
                "USER_MESSAGES_GET" => ["url" => "https://gmail.googleapis.com/gmail/v1/users/" . urlencode($params['userId'] ?? '') . "/messages/" . urlencode($params['id'] ?? ''), "method" => "GET"],
                /**
                 * https://developers.google.com/gmail/api/reference/rest/v1/users.messages.attachments/get
                 * GET https://gmail.googleapis.com/gmail/v1/users/{userId}/messages/{messageId}/attachments/{id}
                 */
                "USER_MESSAGES_ATTACHMENTS_GET" => ["url" => "https://gmail.googleapis.com/gmail/v1/users/" . urlencode($params['userId'] ?? '') . "/messages/" . urlencode($params['userId'] ?? '') . "/attachments/" . urlencode($params['id'] ?? ''), "method" => "GET"],
                /**
                 * POST https://oauth2.googleapis.com/token
                 */
                "OAUTH_REFRESH_TOKEN" => ['url' => 'https://oauth2.googleapis.com/token', 'method' => "POST"]
            ];
            return $definitionArray[$params['end_point']];
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            return ['url' => '', 'method' => 'GET'];
        }
    }
    public function execute()
    {
        /**
         * Flow For Getting Attachments.
         * Get user.messages.list
         * Get user.messages.get
         * Get user.messages.attachments.get
         */
        if ($this->isModuleEnabled) {
            $this->logger->info("---------------Execute Method Start!---------------");
            foreach ($this->configurationProfiles as $profile) {
                try {
                    $userMessagesList = $this->getUserMessagesList($profile, []);
                    if (!($userMessagesList['resultSizeEstimate'])) {
                        $userMessagesList = $this->getUserMessagesList($profile, ['within' => 1]);
                    }
                    $csvFileUrlsForDownload = [];
                    if ($userMessagesList['resultSizeEstimate']) {
                        $userMessagesIds = $this->getUserMessageIds($userMessagesList['messages']);
                        if (!empty($userMessagesIds)) {
                            foreach ($userMessagesIds as $userMessagesId) {
                                $userMessage = $this->getUserMessage($userMessagesId);
                                $processedOneEmail = false;
                                if (!empty($userMessage['payload']['parts']) ?? false) {
                                    foreach ($userMessage['payload']['parts'] as $part) {
                                        if (isset($part['filename'])) {
                                            $found = false;
                                            if (!empty($profile['specificAttachedFileName'])) {
                                                if ($part['filename'] ==  $profile['specificAttachedFileName']) {
                                                    $found = true;
                                                }
                                            } else {
                                                if (pathinfo($part['filename'] ?? "", PATHINFO_EXTENSION) == $profile['attachedFileExtension']) {
                                                    $found = true;
                                                }
                                            }
                                            if ($found) {
                                                $csvFileUrlsForDownload[$userMessagesId] = $this->getEndPointURLData(['end_point' => 'USER_MESSAGES_ATTACHMENTS_GET', 'userId' => $this->userId, 'messageId' => $userMessagesId, 'id' => $part['body']['attachmentId']])['url'];
                                                $processedOneEmail = true;
                                                break;
                                            }
                                        }
                                    }
                                }
                                if ($processedOneEmail) {
                                    $this->logger->info("Downloaded Data Info : " . json_encode($csvFileUrlsForDownload));
                                    $this->putContentsToFile((!empty($profile['absoluteFilePath']) ? $profile['absoluteFilePath'] : $this->directoryList->getroot() . $profile['outputFilePath']), $this->base64url_decode((string)$this->getUserMessagesAttachment($csvFileUrlsForDownload[$userMessagesId])['data']));
                                    $this->logger->info("---File Import Start!---");
                                    try {
                                        $this->logger->info("Function Return : " . $this->importFile($profile['fileImporterClass'], $profile['fileImporterFunction']));
                                    } catch (\Exception $e) {
                                        $this->logger->critical($e->getMessage());
                                    }
                                    $this->logger->info("---File Import End!---");
                                    break;
                                }
                            }
                        } else {
                            $this->logger->info("No Email/Attachment Found!");
                        }
                    }
                } catch (\Exception $e) {
                    $this->logger->critical($e->getMessage());
                }
            }
            $this->logger->info("---------------Execute Method End!---------------");
        } else {
            $this->logger->info("---------------Module Is Disabled!---------------");
        }
    }

    /** For Triggering Import File Function */
    public function importFile($class, $function)
    {
        if (!empty($class) && !empty($function)) {
            $importer = $this->objectManager->get($class);
            return $importer->$function();
        } else {
            return "File Importer Class or Function Not Defined in Configuration Profile!!";
        }
    }
    /** */

    /** Section For Storing Data into File */
    public function putContentsToFile($absoluteFilePath, $base64DecodedContent)
    {
        try {
            file_put_contents($absoluteFilePath, $base64DecodedContent);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }
    /** */
    /** Section For USER_MESSAGES_ATTACHMENTS_GET */
    public function getUserMessagesAttachment($url): array
    {
        try {
            $response = $this->curlRequest->send(
                [
                    'method' => 'GET',
                    'url' => $url,
                    'headers' => $this->getHeadersForUserMessagesAttachments()
                ]
            );
            return json_decode($response, true);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            return [];
        }
    }
    public function getHeadersForUserMessagesAttachments()
    {
        $headers = [...$this->getBearerTokenHeader()];
        return $headers;
    }
    /** */
    /** Section For USER_MESSAGES_GET */
    public function getUserMessage($userMessagesId)
    {
        try {
            $urlData = $this->getEndPointURLData(['end_point' => 'USER_MESSAGES_GET', 'userId' => $this->userId, 'id' => $userMessagesId]);
            $response = $this->curlRequest->send(
                [
                    "method" => $urlData['method'],
                    "url" => $urlData['url'],
                    "headers" => $this->getHeadersForUserMessage()
                ]
            );
            return json_decode($response, true);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            return [];
        }
    }
    public function getHeadersForUserMessage(): array
    {
        $headers = [...$this->getBearerTokenHeader()];
        return $headers;
    }
    public function getUserMessageIds(array $arr): array
    {
        $result = [];
        foreach ($arr as $item) {
            $result[] = $item['id'];
        }
        return $result;
    }
    /** */

    /** Section For USER_MESSAGES_LIST */
    public function getUserMessagesList($profile, $params = [])
    {
        $response = [];
        try {
            $urlData = $this->getEndPointURLData(['end_point' => 'USER_MESSAGES_LIST', 'userId' => $this->userId]);
            $response =
                $this->curlRequest->send(
                    [
                        "method" => $urlData['method'],
                        "url" => $urlData['url'],
                        "headers" => $this->getHeadersForUserMessagesList(),
                        "params" => (isset($params['within']) && (strlen($params['within'] ?? "") > 0)) ? $this->getPostDataForUserMessagesList($profile, $params) : $this->getPostDataForUserMessagesList($profile, [])
                    ]
                );
            $response =  json_decode($response, true);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $response =  [];
        }
        return $response;
    }
    public function getHeadersForUserMessagesList(): array
    {
        $headers = [...$this->getBearerTokenHeader()];
        return $headers;
    }
    public function getPostDataForUserMessagesList($profile, $params): array
    {
        try {
            $finalQuery =  "";
            $from = $profile['from'] ?? "";
            if (!empty($from)) {
                $finalQuery .= "from:({$from}) ";
            }
            $to = $profile['to'] ?? "";
            if (!empty($to)) {
                $finalQuery .= "to:({$from}) ";
            }
            $subject = $profile['subject'] ?? "";
            if (!empty($subject)) {
                $finalQuery .= "subject:({$subject}) ";
            }
            $hasTheWords = $profile['hasTheWords'] ?? "";
            if (!empty($hasTheWords)) {
                $finalQuery .= "{" . $hasTheWords . "} ";
            }
            $doesnotHaveTheWords = $profile['doesnotHaveTheWords'] ?? "";
            if (!empty($doesnotHaveTheWords)) {
                $finalQuery .= "-{" . $doesnotHaveTheWords . "} ";
            }
            $size = $profile['size'] ?? "";
            if (!empty($size)) {
                $finalQuery .= "{$size} ";
            }
            $within = $profile['within'] ?? "";
            if (isset($params['within']) && (strlen(trim($within ?? "")) > 0)) {
                $within = $params['within'];
            }
            if (strlen(trim($within ?? "")) > 0) {
                $beforeDate = date('Y/m/d', strtotime("+2 day"));
                $within = intval($within);
                $afterDate = date('Y/m/d', strtotime("-{$within} day"));
                $finalQuery .= "after:{$afterDate} before:{$beforeDate} ";
            } else {
                $after = $profile['after'] ?? "";
                if (!empty($after)) {
                    $finalQuery .= "after:{$after} ";
                }
                $before = $profile['before'] ?? "";
                if (!empty($before)) {
                    $finalQuery .= "after:{$before} ";
                }
            }
            $withinWhichEntity = $profile['withinWhichEntity'] ?? "";
            if (!empty($withinWhichEntity)) {
                $finalQuery .= "in:{$withinWhichEntity} ";
            }
            $hasAttachment = $profile['hasAttachment'] ?? "";
            if ($hasAttachment) {
                $finalQuery .= "has:attachment ";
            }
            $notIncludeChats = $profile['notIncludeChats'] ?? "";
            if ($notIncludeChats) {
                $finalQuery .= "-in:chats ";
            }
            $postData = [
                'q' => $finalQuery
            ];
            return $postData;
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            return [];
        }
    }
    /** End */

    public function getBearerTokenHeader(): array
    {
        return ['Authorization' => "Bearer {$this->getBearerToken()}"];
    }
    public function getBearerToken(): string
    {
        try {
            $credentials = json_decode($this->getConfigValue('gmail_file_importer_settings/credentials/web'), true);
            $token = json_decode($this->getConfigValue('gmail_file_importer_settings/credentials/token'), true);
            $accessToken = $this->getAccessTokenFromRefreshToken($credentials['web']['client_id'], $credentials['web']['client_secret'], $token['refresh_token']);
            return $accessToken;
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            return '';
        }
    }
    public function getAccessTokenFromRefreshToken($clientId, $clientSecret, $refreshToken): string
    {
        try {
            $urlData = $this->getEndPointURLData(['end_point' => 'OAUTH_REFRESH_TOKEN']);
            $params = [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ];
            $response = $this->curlRequest->send(
                [
                    "method" => $urlData['method'],
                    "url" => $urlData['url'],
                    "params" => $params
                ]
            );
            return (json_decode($response, true)['access_token']) ?? "";
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            return "";
        }
    }
    public function base64url_decode(string $string)
    {
        return base64_decode(strtr($string, '-_', '+/'));
    }
}
