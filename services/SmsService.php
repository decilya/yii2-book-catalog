<?php
declare(strict_types=1);

namespace app\services;

use Yii;
use yii\httpclient\Client;

/**
 * Сервис отправки SMS через smspilot.ru
 *
 * Поддерживает эмуляцию (ключ 'emulator') для тестирования.
 * В случае ошибок логирует их и возвращает false.
 */
class SmsService
{
    /**
     * Отправляет SMS-сообщение на указанный номер
     *
     * @param string $phone Номер телефона (в международном формате)
     * @param string $message Текст сообщения
     * @return bool true – отправка выполнена успешно (или эмулирована), false – ошибка
     */
    public function send(string $phone, string $message): bool
    {
        Yii::info("📨 Отправка SMS на {$phone}: {$message}", 'sms');

        // Создаём HTTP-клиент с таймаутом 3 секунды, чтобы не зависать
        $client = new Client(['timeout' => 3]);

        try {
            $response = $client->post('https://smspilot.ru/api.php', [
                'send' => $message,
                'to' => $phone,
                'apikey' => Yii::$app->params['smsPilotApiKey'] ?? 'emulator',
                'format' => 'json',
            ])->send();

            if ($response->isOk) {
                $data = $response->getData();
                Yii::info("✅ Ответ от smspilot: " . json_encode($data, JSON_UNESCAPED_UNICODE), 'sms');

                // Проверяем успешность (для эмулятора приходит {"success":true})
                if (isset($data['success']) && $data['success'] === true) {
                    return true;
                }

                // Если пришла ошибка – логируем и возвращаем false
                if (isset($data['error'])) {
                    Yii::error("Ошибка smspilot: " . json_encode($data['error'], JSON_UNESCAPED_UNICODE), 'sms');
                }
                return false;
            }

            Yii::error("HTTP ошибка при отправке SMS: " . $response->getContent(), 'sms');
            return false;

        } catch (\Exception $e) {
            // Таймаут или другие исключения – логируем, но не падаем
            Yii::error("Исключение при отправке SMS: " . $e->getMessage(), 'sms');
            return false;
        }
    }
}
