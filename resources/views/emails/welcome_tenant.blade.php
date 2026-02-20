<!DOCTYPE html>
<html>
<head>
    <title>Bienvenido a Facta SaaS</title>
</head>
<body>
    <h1>¡Hola, {{ $user->name }}!</h1>
    <p>Gracias por elegir <strong>Facta SaaS</strong> para la gestión de tu negocio.</p>
    
    <p>Tu espacio ha sido creado exitosamente bajo el nombre de: <strong>{{ $tenant->name }}</strong>.</p>
    
    <p>Puedes acceder a tu sistema desde el siguiente enlace:</p>
    <p><a href="{{ $url }}" style="padding: 10px 20px; background-color: #1c3faa; color: white; text-decoration: none; border-radius: 5px;">Ir a mi Panel de Control</a></p>
    
    <p>O copia y pega esta URL en tu navegador: <br> {{ $url }}</p>
    
    <hr>
    <p><strong>Tus credenciales de acceso:</strong></p>
    <ul>
        <li><strong>Usuario:</strong> {{ $user->email }}</li>
        <li><strong>Contraseña:</strong> (La que definiste en el registro)</li>
    </ul>
    
    <p>Si tienes alguna duda o necesitas soporte técnico, no dudes en contactarnos.</p>
    
    <p>Saludos,<br>El equipo de Facta SaaS</p>
</body>
</html>
