<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $contact->name }}</title>
</head>
<body>
    <h1>Contacto</h1>
    
    <p><a href="{{ route('contacts.edit', $contact) }}">Editar</a></p>
    
    <p><strong>Nombre:</strong> {{ $contact->name }}</p>
    <p><strong>Teléfono:</strong> {{ $contact->phone }}</p>
    <p><strong>Correo:</strong> {{ $contact->email }}</p>
    <p><strong>Nota:</strong> {{ $contact->note }}</p>
    
    <p><a href="{{ route('contacts.index') }}">Volver</a></p>
</body>
</html>