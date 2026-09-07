<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo contacto</title>
</head>
<body>
    <h1>Nuevo contacto</h1>

    
    @if ($errors->any())
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    @endif
    
    <form action="{{ route('contacts.store') }}" method="POST">
        @csrf
        
        <div>
            <label>Nombre</label>
            <input type="text" name="name" value="{{ old('name') }}">
        </div>
        
        <div>
            <label>Teléfono</label>
            <input type="text" name="phone" value="{{ old('phone') }}">
        </div>
        
        <div>
            <label>Correo</label>
            <input type="email" name="email" value="{{ old('email') }}">
        </div>
        
        <div>
            <label>Nota</label>
            <textarea name="note">{{ old('note') }}</textarea>
        </div>
        
        <button type="submit">Guardar</button>
    </form>
    <p><a href="{{ route('contacts.index') }}">Volver sin guardar</a></p>
</body>
</html>