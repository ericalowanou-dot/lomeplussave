<?php
/**
 * Test simple d'envoi d'email
 * Accédez à: http://localhost:8000/test-email.php
 */

// Charger Laravel
require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Email - Lome+</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        button { background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin: 5px; }
        button:hover { background: #0056b3; }
        input { width: 100%; padding: 8px; margin: 5px 0; border: 1px solid #ddd; border-radius: 4px; }
        .code { background: #f8f9fa; padding: 10px; border-radius: 4px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📧 Test d'Envoi d'Email</h1>
        
        <?php
        // Afficher la configuration actuelle
        echo '<div class="info">';
        echo '<h3>Configuration actuelle :</h3>';
        echo '<div class="code">';
        echo 'MAIL_MAILER: ' . config('mail.default') . '<br>';
        echo 'MAIL_HOST: ' . config('mail.mailers.smtp.host') . '<br>';
        echo 'MAIL_PORT: ' . config('mail.mailers.smtp.port') . '<br>';
        echo 'MAIL_USERNAME: ' . config('mail.mailers.smtp.username') . '<br>';
        echo 'MAIL_ENCRYPTION: ' . config('mail.mailers.smtp.encryption') . '<br>';
        echo '</div>';
        echo '</div>';
        
        // Test d'envoi si demandé
        if (isset($_POST['test_email']) && !empty($_POST['test_email'])) {
            $email = $_POST['test_email'];
            
            try {
                Mail::raw('Test d\'envoi d\'email depuis Lome+', function($message) use ($email) {
                    $message->to($email)
                            ->subject('Test Email - Lome+');
                });
                
                echo '<div class="success">✅ Email envoyé avec succès à ' . $email . '</div>';
                
                if (config('mail.default') === 'log') {
                    echo '<div class="info">📝 Email sauvegardé dans storage/logs/laravel.log</div>';
                } else {
                    echo '<div class="info">📧 Vérifiez votre boîte email</div>';
                }
                
            } catch (Exception $e) {
                echo '<div class="error">❌ Erreur: ' . $e->getMessage() . '</div>';
            }
        }
        ?>
        
        <form method="POST">
            <h3>Test d'envoi d'email :</h3>
            <input type="email" name="test_email" placeholder="votre-email@gmail.com" required>
            <button type="submit">📧 Envoyer un email de test</button>
        </form>
        
        <div class="info">
            <h3>📋 Configuration requise :</h3>
            <p>Assurez-vous que votre fichier <code>.env</code> contient :</p>
            <div class="code">
MAIL_MAILER=smtp<br>
MAIL_HOST=smtp.gmail.com<br>
MAIL_PORT=587<br>
MAIL_USERNAME=ton-email@gmail.com<br>
MAIL_PASSWORD=ton-mot-de-passe-app<br>
MAIL_ENCRYPTION=tls<br>
MAIL_FROM_ADDRESS=ton-email@gmail.com
            </div>
        </div>
        
        <div class="info">
            <h3>🔗 Liens utiles :</h3>
            <p>
                <a href="/forgot-password" target="_blank">Mot de passe oublié</a> |
                <a href="/login" target="_blank">Connexion</a> |
                <a href="/register" target="_blank">Inscription</a>
            </p>
        </div>
    </div>
</body>
</html>
