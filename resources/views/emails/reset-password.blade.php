<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation de votre mot de passe - Lome+</title>
    <style>
        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #FF9900 0%, #E68900 100%);
            padding: 30px 20px;
            text-align: center;
        }
        .email-logo {
            max-width: 150px;
            height: auto;
            margin-bottom: 15px;
        }
        .email-body {
            padding: 40px 30px;
        }
        .email-title {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin: 0 0 20px 0;
            text-align: center;
        }
        .email-content {
            font-size: 16px;
            color: #555;
            margin-bottom: 30px;
        }
        .email-button {
            display: inline-block;
            background: linear-gradient(135deg, #FF9900 0%, #E68900 100%);
            color: #ffffff !important;
            padding: 14px 32px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            text-align: center;
            margin: 20px 0;
            font-size: 16px;
        }
        .email-button-container {
            text-align: center;
            margin: 30px 0;
        }
        .email-footer {
            background-color: #f9f9f9;
            padding: 25px 30px;
            text-align: center;
            font-size: 14px;
            color: #777;
            border-top: 1px solid #eee;
        }
        .email-footer p {
            margin: 8px 0;
        }
        .email-warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            font-size: 14px;
            color: #856404;
        }
        .email-link {
            color: #FF9900;
            text-decoration: none;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <img src="{{ $logoUrl }}" alt="Lome+" class="email-logo">
        </div>
        
        <div class="email-body">
            <h1 class="email-title">Bonjour ! 👋</h1>
            
            <div class="email-content">
                <p>Vous recevez cet email car nous avons reçu une demande de réinitialisation de mot de passe pour votre compte sur <strong>Lome+</strong>.</p>
                
                <p>Cliquez sur le bouton ci-dessous pour créer un nouveau mot de passe :</p>
            </div>
            
            <div class="email-button-container">
                <a href="{{ $url }}" class="email-button">Réinitialiser mon mot de passe</a>
            </div>
            
            <div class="email-warning">
                <strong>⏰ Important :</strong> Ce lien de réinitialisation expirera dans {{ $count }} minutes.
            </div>
            
            <div class="email-content" style="font-size: 14px; color: #777; margin-top: 30px;">
                <p>Si le bouton ne fonctionne pas, copiez et collez ce lien dans votre navigateur :</p>
                <p style="word-break: break-all;">
                    <a href="{{ $url }}" class="email-link">{{ $url }}</a>
                </p>
            </div>
            
            <div class="email-content" style="font-size: 14px; color: #999; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
                <p><strong>⚠️ Vous n'avez pas demandé de réinitialisation ?</strong></p>
                <p>Si vous n'avez pas demandé de réinitialisation de mot de passe, aucune action n'est requise. Votre mot de passe restera inchangé.</p>
            </div>
        </div>
        
        <div class="email-footer">
            <p><strong>Lome+</strong> - Votre plateforme de vente et d'achat au Togo</p>
            <p style="font-size: 12px; color: #999;">
                Cet email a été envoyé à <strong>{{ $email }}</strong>
            </p>
            <p style="font-size: 12px; color: #999; margin-top: 15px;">
                © {{ date('Y') }} Lome+. Tous droits réservés.
            </p>
        </div>
    </div>
</body>
</html>
