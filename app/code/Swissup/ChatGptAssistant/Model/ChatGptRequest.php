<?php
namespace Swissup\ChatGptAssistant\Model;

use Orhanerday\OpenAi\OpenAi;

class ChatGptRequest
{
    private \Psr\Log\LoggerInterface $logger;

    private \Swissup\ChatGptAssistant\Helper\Config $configHelper;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Swissup\ChatGptAssistant\Helper\Config $configHelper
    ) {
        $this->logger = $logger;
        $this->configHelper = $configHelper;
    }

    /**
     * @param string $prompt
     * @return array
     */
    public function sendRequest($prompt)
    {
        $messages = [];
        $messages[] = [
            "role" => "system",
            "content" => "You are an online store owner's helpful assistant."
        ];
        if (is_array($prompt)) {
            $messages = array_merge($messages, $prompt);
        } else {
            $messages[] = ["role" => "user", "content" => $prompt];
        }

        $openAiKey = $this->configHelper->getOpenaiApiKey();
        $openAiModel = $this->configHelper->getOpenaiModel();
        $openAI = new OpenAi($openAiKey);

        $chatConfig = [
            'model' => $openAiModel,
            'messages' => $messages,
            'temperature' => 1.0,
            //'max_tokens' => 4000,
            'frequency_penalty' => 0,
            'presence_penalty' => 0,
        ];
        $chat = $openAI->chat($chatConfig);
        $result = json_decode($chat);

        if (isset($result->error)) {
            $this->logger->error(json_encode($result->error));
            if ($result->error->type == 'invalid_request_error') {
                $errorMessage = __('The request to OpenAI API was invalid. Ensure the API key used is correct.');
                $errorCode = 401;
            } else if ($result->error->type == 'requests') {
                $errorMessage = $result->error->message;
                $errorCode = 429;
            } else {
                $errorMessage = $result->error->message ?? __('An unknown error occurred. Please try again.');
                $errorCode = 500;
            }

            return ['success' => false, 'result' => $errorMessage, 'code' => $errorCode];
        }

        return ['success' => true, 'result' => $result->choices[0]->message->content, 'code' => 200];
    }
}
