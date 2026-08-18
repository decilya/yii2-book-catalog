<?php
declare(strict_types=1);

namespace app\services;

use Yii;
use yii\httpclient\Client;

/**
 * Сервис для отправки SMS через smspilot.ru
 */
class SmsService
{
    /**
     * Отправляет SMS-сообщение
     *
     * @param string $phone   Номер телефона в международном формате
     * @param string $message Текст сообщения
     * @return bool
     */
    public function send(string $phone, string $message): bool
    {
        Yii::info("📨 Отправка SMS на {$phone}: {$message}", 'sms');

        $client = new Client();
        $response = $client->post('https://smspilot.ru/api.php', [
            'send' => $message,
            'to' => $phone,
            'apikey' => Yii::$app->params['smsPilotApiKey'] ?? 'emulator',
            'format' => 'json',
        ])->send();

        if ($response->isOk) {
            $data = $response->getData();
            Yii::info("✅ Ответ от smspilot: " . json_encode($data), 'sms');
            return isset($data['success']) && $data['success'] === true;
        }

        Yii::error("❌ SMS не отправлено: " . $response->getContent(), 'sms');
        return false;
    }
}
