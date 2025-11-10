<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\FcmService;
use Illuminate\Console\Command;
use Kreait\Firebase\Exception\FirebaseException;
use Throwable;

class TestNotification extends Command
{
    protected $signature = 'app:test-notification {userId : ID del usuario destinatario}';

    protected $description = 'Envía una notificación de prueba a un usuario mediante FCM.';

    public function handle(FcmService $fcmService): int
    {
        $userId = (int) $this->argument('userId');
        $user = User::with('fcmTokens')->find($userId);

        if (! $user) {
            $this->error("Usuario con ID {$userId} no encontrado.");
            return self::FAILURE;
        }

        $token = optional($user->fcmTokens()->latest()->first())->token;

        if (! $token) {
            $this->error("El usuario '{$user->name}' no tiene tokens FCM registrados.");
            return self::FAILURE;
        }

        $this->info("Usuario '{$user->name}' encontrado.");
        $this->info('Token FCM (parcial): '.substr($token, 0, 30).'...');
        $this->info('Enviando notificación de prueba usando FcmService...');

        try {
            $fcmService->sendToUser($user, [
                'title' => 'Notificación de Prueba',
                'body' => 'Esta es una prueba desde el comando Artisan.',
            ]);
        } catch (FirebaseException $exception) {
            $this->error('Error al enviar la notificación: '.$exception->getMessage());
            return self::FAILURE;
        } catch (Throwable $exception) {
            $this->error('Error inesperado: '.$exception->getMessage());
            return self::FAILURE;
        }

        $this->info('✅ Comando ejecutado. Revisa el dispositivo y el log en storage/logs/laravel.log.');

        return self::SUCCESS;
    }
}

