<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar {{ $contact->name }}</title>
</head>
<body>
    <h1>Editar contacto</h1>
    
    @if ($errors->any())
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
        </ul>
    @endif
    
    <form action="{{ route('contacts.update', $contact) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div>
            <label>Nombre</label>
            <input type="text" name="name" value="{{ old('name', $contact->name) }}">
        </div>
        
        <div>
            <label>Teléfono</label>
            <input type="text" name="phone" value="{{ old('phone', $contact->phone) }}">
        </div>
        
        <div>
            <label>Correo</label>
            <input type="email" name="email" value="{{ old('email', $contact->email) }}">
        </div>
        
        <div>
            <label>Nota</label>
            <textarea name="note">{{ old('note', $contact->note) }}</textarea>
        </div>
        
        <button type="submit">Actualizar</button>
    </form>
    <p><a href="{{ route('contacts.index') }}">Volver</a></p>
</body>
</html>