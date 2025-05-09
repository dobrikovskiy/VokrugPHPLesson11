<?php

namespace App\Telegram;

class TelegramApiImpl implements TelegramApi {
    const ENDPOINT = 'https://api.telegram.org/bot';
    private int $offset;
    private string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function getMessages(int $offset): array
    {
        $url = self::ENDPOINT . $this->token . '/getUpdates?timeout=1';
        $result = [];

        while (true) {
            $ch = curl_init("{$url}&offset={$offset}");

            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json')); // Установить тип контента на application/json
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Возвращает ответ вместо того, чтобы печатать его

            $response = json_decode(curl_exec($ch), true);

            if (!$response['ok'] || empty($response['result'])) break;
            foreach ($response['result'] as $data) {
                $result[$data['message']['chat']['id']] = [...$result[$data['message']['chat']['id']] ?? [], $data['message']['text']];
                $offset = $data['update_id'] + 1;
            }
            curl_close($ch);

            if (count($response['result']) < 100) break;
        }

        return [
            'offset' => $offset,
            'result' => $result,
        ];
    }

    public function sendMessage(string $chatId, string $text): void
    {
        $url = self::ENDPOINT . $this->token . '/sendMessage';

        $data = [
            'chat_id' => $chatId,
            'text' => $text,
        ];

        $ch = curl_init($url);

        $jsonData = json_encode($data);

        curl_setopt($ch, CURLOPT_POST, true); // Указываем метод запроса как POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData); // Прикрепляем закодированные данные в формате JSON
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json')); // Устанавливаем тип контента на application/json
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Возвращает ответ вместо того, чтобы печатать его

        curl_exec($ch);

        curl_close($ch);
    }
}